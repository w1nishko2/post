<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TelegramBot;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Exports\ProductsTemplateExport;
use App\Exports\ProductsDataExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Показать страницу выбора бота для управления товарами
     */
    public function selectBot()
    {
        $bots = Auth::user()->telegramBots()->get();
        return view('products.select-bot', compact('bots'));
    }

    /**
     * Перенаправить старые ссылки на выбор бота
     */
    public function redirectToBot()
    {
        return redirect()->route('products.select-bot');
    }

    /**
     * Display a listing of the bot's products.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(\App\Models\TelegramBot $telegramBot)
    {
        // Проверка владения теперь выполняется middleware
    $products = $telegramBot->products()->with(['category'])->orderedForListing()->paginate(12);

        return view('products.index', compact('products', 'telegramBot'));
    }

    /**
     * Display products in table format for bulk editing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function table(Request $request, \App\Models\TelegramBot $telegramBot)
    {
        $query = $telegramBot->products()->with(['category']);
        
        // Отладочная информация
        Log::info('Search request', [
            'search' => $request->get('search'),
            'search_raw' => $request->input('search'),
            'search_decoded' => urldecode($request->get('search', '')),
            'all_params' => $request->all(),
            'bot_id' => $telegramBot->id,
            'total_products' => $telegramBot->products()->count()
        ]);
        
        // Поиск по различным полям
        if ($search = $request->get('search')) {
            // Декодируем URL-кодированную строку и убираем лишние пробелы
            $searchTerm = trim(urldecode($search));
            Log::info('Searching for decoded: ' . $searchTerm);
            Log::info('Search term length: ' . strlen($searchTerm));
            Log::info('Search term bytes: ' . bin2hex($searchTerm));
            
            // Логируем первые несколько товаров для сравнения
            $allProducts = $telegramBot->products()->limit(5)->get(['id', 'name']);
            Log::info('Sample products:', $allProducts->pluck('name', 'id')->toArray());
            
            // Логируем SQL запрос
            DB::enableQueryLog();
            
            $query->where(function($q) use ($searchTerm) {
                // Основной поиск - простой и надежный
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('article', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('specifications', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('price', 'LIKE', "%{$searchTerm}%");
                
                // Поиск по названию категории
                $q->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                    $categoryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                });
                
                // Поиск по ID если введено число
                if (is_numeric($searchTerm)) {
                    $q->orWhere('id', $searchTerm)
                      ->orWhere('quantity', $searchTerm);
                }
                
                // Поиск по статусу товара
                $lowerSearch = mb_strtolower($searchTerm);
                if (in_array($lowerSearch, ['активен', 'активный', 'active', 'да', 'yes', '1'])) {
                    $q->orWhere('is_active', 1);
                } elseif (in_array($lowerSearch, ['неактивен', 'неактивный', 'inactive', 'нет', 'no', '0'])) {
                    $q->orWhere('is_active', 0);
                }
            });
        }

        // Фильтр по категории
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Фильтр по статусу - применяется только если значение явно указано (0 или 1)
        $isActiveParam = $request->get('is_active');
        if ($isActiveParam !== null && $isActiveParam !== '') {
            // Дополнительная проверка что значение валидное (0 или 1)
            if (in_array($isActiveParam, ['0', '1', 0, 1], true)) {
                $query->where('is_active', (int)$isActiveParam);
            }
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = ['id', 'name', 'price', 'quantity', 'created_at', 'article'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(20)->appends($request->all());
        $categories = $telegramBot->categories()->active()->get();

        // Логируем выполненные SQL запросы
        if ($request->get('search')) {
            $queries = DB::getQueryLog();
            Log::info('SQL queries:', $queries);
        }

        // Отладочная информация о результатах
        Log::info('Search results', [
            'found_products' => count($products->items()),
            'total_found' => $products->total(),
            'search_term' => $request->get('search')
        ]);

        return view('products.table', compact('products', 'telegramBot', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(TelegramBot $telegramBot)
    {
        // Проверка владения теперь выполняется middleware
        // Получаем активные категории для этого бота
        $categories = $telegramBot->activeCategories()->orderBy('name')->get();

        return view('products.create', compact('telegramBot', 'categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request, TelegramBot $telegramBot)
    {
        // Проверка владения теперь выполняется middleware
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['telegram_bot_id'] = $telegramBot->id;

        Product::create($validated);

        return redirect()->route('bot.products.index', $telegramBot)->with('success', 'Товар успешно добавлен!');
    }

    /**
     * Display the specified product.
     */
    public function show(TelegramBot $telegramBot, Product $product)
    {
        // Проверяем, что бот и товар принадлежат текущему пользователю
        if ($telegramBot->user_id !== Auth::id() || $product->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому ресурсу.');
        }

        // Проверяем, что товар принадлежит этому боту
        if ($product->telegram_bot_id !== $telegramBot->id) {
            abort(404, 'Товар не найден в этом магазине.');
        }

        return view('products.show', compact('product', 'telegramBot'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(TelegramBot $telegramBot, Product $product)
    {
        // Проверяем, что бот и товар принадлежат текущему пользователю
        if ($telegramBot->user_id !== Auth::id() || $product->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому ресурсу.');
        }

        // Проверяем, что товар принадлежит этому боту
        if ($product->telegram_bot_id !== $telegramBot->id) {
            abort(404, 'Товар не найден в этом магазине.');
        }

        // Получаем активные категории для этого бота
        $categories = $telegramBot->activeCategories()->orderBy('name')->get();

        return view('products.edit', compact('product', 'telegramBot', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, TelegramBot $telegramBot, Product $product)
    {
        // Проверяем, что бот и товар принадлежат текущему пользователю
        if ($telegramBot->user_id !== Auth::id() || $product->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому ресурсу.');
        }

        // Проверяем, что товар принадлежит этому боту
        if ($product->telegram_bot_id !== $telegramBot->id) {
            abort(404, 'Товар не найден в этом магазине.');
        }

        $validated = $request->validated();
        $product->update($validated);

        return redirect()->route('bot.products.index', $telegramBot)->with('success', 'Товар успешно обновлен!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(TelegramBot $telegramBot, Product $product)
    {
        // Проверяем, что бот и товар принадлежат текущему пользователю
        if ($telegramBot->user_id !== Auth::id() || $product->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому ресурсу.');
        }

        // Проверяем, что товар принадлежит этому боту
        if ($product->telegram_bot_id !== $telegramBot->id) {
            abort(404, 'Товар не найден в этом магазине.');
        }

        $product->delete();

        return redirect()->route('bot.products.index', $telegramBot)->with('success', 'Товар успешно удален!');
    }

    /**
     * Quick update product from table view.
     */
    public function quickUpdate(Request $request, TelegramBot $telegramBot, Product $product)
    {
        // Проверяем, что бот и товар принадлежат текущему пользователю
        if ($telegramBot->user_id !== Auth::id() || $product->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что товар принадлежит этому боту
        if ($product->telegram_bot_id !== $telegramBot->id) {
            return response()->json(['error' => 'Товар не найден'], 404);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string',
            'article' => 'sometimes|nullable|string|max:100',
            'specifications' => 'sometimes|nullable|string',
        ];

        $validated = $request->validate($rules);
        
        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Товар обновлен',
            'product' => $product->fresh(['category'])
        ]);
    }

    /**
     * Скачать шаблон Excel для импорта товаров
     */
    public function downloadTemplate(TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        return Excel::download(new ProductsTemplateExport, 'template_products.xlsx');
    }

    /**
     * Экспорт всех товаров магазина в Excel
     */
    public function exportData(TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        $fileName = 'products_' . $telegramBot->bot_username . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Настройка правильной кодировки для русских символов
        return Excel::download(new ProductsDataExport($telegramBot), $fileName, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Импорт товаров из Excel файла
     */
    public function importFromExcel(Request $request, TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'excel_file.required' => 'Файл обязателен для загрузки.',
            'excel_file.mimes' => 'Файл должен быть в формате Excel (xlsx, xls) или CSV.',
            'excel_file.max' => 'Размер файла не должен превышать 2 МБ.',
        ]);

        try {
            $import = new ProductsImport($telegramBot->id);
            Excel::import($import, $request->file('excel_file'));

            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getImportErrors();

            // Формируем сообщение о результатах импорта
            $message = "Импорт завершен! Добавлено товаров: {$importedCount}";
            
            if ($skippedCount > 0) {
                $message .= ", пропущено записей: {$skippedCount}";
            }

            if (!empty($errors)) {
                $errorMessage = "Обнаружены ошибки:\n" . implode("\n", $errors);
                
                return redirect()->route('bot.products.index', $telegramBot)
                    ->with('warning', $message)
                    ->with('import_errors', $errorMessage);
            }

            if ($importedCount == 0 && $skippedCount > 0) {
                return redirect()->route('bot.products.index', $telegramBot)
                    ->with('error', 'Не удалось импортировать ни одного товара. Проверьте формат файла и заголовки столбцов.');
            }

            return redirect()->route('bot.products.index', $telegramBot)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage(), [
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'user_id' => Auth::id(),
                'bot_id' => $telegramBot->id
            ]);
            
            return redirect()->route('bot.products.index', $telegramBot)
                ->with('error', 'Ошибка при импорте файла: ' . $e->getMessage());
        }
    }
}