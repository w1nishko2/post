<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TelegramBot;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Exports\ProductsTemplateExport;
use App\Exports\ProductsDataExport;
use App\Imports\ProductsImportQueue;
use App\Services\ImageUploadService;

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
    public function store(StoreProductRequest $request, TelegramBot $telegramBot, ImageUploadService $imageService)
    {
        // Проверка владения теперь выполняется middleware
        
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['telegram_bot_id'] = $telegramBot->id;

        // Обработка загруженных изображений
        if ($request->hasFile('images')) {
            try {
                $uploadedImages = $imageService->uploadMultiple($request->file('images'), 'products');
                
                // Собираем публичные URL изображений
                $photosGallery = [];
                foreach ($uploadedImages as $imageData) {
                    $photosGallery[] = $imageData['file_path'];
                }
                
                $validated['photos_gallery'] = $photosGallery;
                $validated['main_photo_index'] = $request->input('main_photo_index', 0);
                
                // Устанавливаем главное фото в photo_url
                if (!empty($photosGallery)) {
                    $mainIndex = min($validated['main_photo_index'], count($photosGallery) - 1);
                    $validated['photo_url'] = $photosGallery[$mainIndex];
                }
                
                Log::info('Images uploaded for new product', [
                    'count' => count($photosGallery),
                    'photos_gallery' => $photosGallery,
                    'main_photo_index' => $validated['main_photo_index']
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error uploading images: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ошибка при загрузке изображений: ' . $e->getMessage());
            }
        }

        $product = Product::create($validated);

        Log::info('Product created successfully', [
            'product_id' => $product->id,
            'photos_gallery' => $product->photos_gallery,
            'main_photo_index' => $product->main_photo_index
        ]);

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
    public function update(UpdateProductRequest $request, TelegramBot $telegramBot, Product $product, ImageUploadService $imageService)
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

        // Обработка загруженных изображений
        if ($request->hasFile('images')) {
            try {
                $uploadedImages = $imageService->uploadMultiple($request->file('images'), 'products');
                
                // Собираем публичные URL изображений
                $photosGallery = [];
                foreach ($uploadedImages as $imageData) {
                    $photosGallery[] = $imageData['file_path'];
                }
                
                $validated['photos_gallery'] = $photosGallery;
                $validated['main_photo_index'] = $request->input('main_photo_index', 0);
                
                // Устанавливаем главное фото в photo_url
                if (!empty($photosGallery)) {
                    $mainIndex = min($validated['main_photo_index'], count($photosGallery) - 1);
                    $validated['photo_url'] = $photosGallery[$mainIndex];
                }
                
                Log::info('Images uploaded for product update', [
                    'product_id' => $product->id,
                    'count' => count($photosGallery),
                    'photos_gallery' => $photosGallery,
                    'main_photo_index' => $validated['main_photo_index']
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error uploading images: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ошибка при загрузке изображений: ' . $e->getMessage());
            }
        }

        $product->update($validated);

        Log::info('Product updated successfully', [
            'product_id' => $product->id,
            'photos_gallery' => $product->photos_gallery,
            'main_photo_index' => $product->main_photo_index
        ]);

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
            'markup_percentage' => 'sometimes|nullable|numeric|min:0|max:1000',
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
     * Update single field of product from inline editing
     */
    public function updateField(Request $request, TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'id' => 'required|integer|exists:products,id',
            'field' => 'required|string|in:name,price,quantity,category_id,is_active,description,article,markup_percentage',
            'value' => 'required'
        ]);

        $product = Product::where('id', $request->id)
            ->where('telegram_bot_id', $telegramBot->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Валидируем значение в зависимости от поля
        $fieldRules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'quantity' => 'required|integer|min:0|max:999999',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string|max:2000',
            'article' => 'nullable|string|max:100',
            'markup_percentage' => 'nullable|numeric|min:0|max:1000',
        ];

        $fieldValidator = $request->validate([
            'value' => $fieldRules[$request->field] ?? 'required'
        ]);

        // Дополнительная проверка для категории
        if ($request->field === 'category_id' && $request->value) {
            $category = Category::find($request->value);
            if (!$category || $category->user_id !== Auth::id() || $category->telegram_bot_id !== $telegramBot->id) {
                return response()->json(['error' => 'Категория не найдена'], 404);
            }
        }

        $product->update([$request->field => $fieldValidator['value']]);
        
        // Получаем обновленный продукт с отношениями
        $product = $product->fresh(['category']);
        
        // Форматируем значение для UI
        $formattedValue = null;
        switch ($request->field) {
            case 'price':
                $formattedValue = number_format((float)$product->price, 0, ',', ' ') . ' ₽';
                break;
            case 'category_id':
                $formattedValue = $product->category ? $product->category->name : null;
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Поле обновлено',
            'product' => $product,
            'formatted_value' => $formattedValue
        ]);
    }

    /**
     * Массовое применение наценки к товарам
     */
    public function bulkMarkup(Request $request, TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:1000',
            'only_without_markup' => 'boolean'
        ]);

        $markupPercentage = $request->input('markup_percentage');
        $onlyWithoutMarkup = $request->input('only_without_markup', true);

        try {
            DB::beginTransaction();

            // Строим запрос для товаров этого бота
            $query = Product::where('telegram_bot_id', $telegramBot->id)
                          ->where('user_id', Auth::id());

            // Если нужно применить только к товарам без наценки
            if ($onlyWithoutMarkup) {
                $query->where(function($q) {
                    $q->whereNull('markup_percentage')
                      ->orWhere('markup_percentage', 0);
                });
            }

            $affectedCount = $query->update(['markup_percentage' => $markupPercentage]);

            DB::commit();

            $message = $affectedCount > 0 
                ? "Наценка {$markupPercentage}% успешно применена к {$affectedCount} товарам"
                : "Не найдено товаров для применения наценки";

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $affectedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Ошибка при массовом применении наценки: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при применении наценки'
            ], 500);
        }
    }

    /**
     * Массовое изменение статуса товаров
     */
    public function bulkStatus(Request $request, TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|exists:products,id',
            'status' => 'required|in:active,inactive'
        ]);

        $productIds = $request->input('product_ids');
        $status = $request->input('status');
        $isActive = $status === 'active' ? 1 : 0;

        try {
            DB::beginTransaction();

            // Проверяем, что все товары принадлежат этому боту и пользователю
            $validProducts = Product::where('telegram_bot_id', $telegramBot->id)
                                  ->where('user_id', Auth::id())
                                  ->whereIn('id', $productIds)
                                  ->pluck('id')
                                  ->toArray();

            if (empty($validProducts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не найдено товаров для изменения статуса'
                ], 400);
            }

            // Обновляем статус товаров
            $affectedCount = Product::whereIn('id', $validProducts)
                                  ->update(['is_active' => $isActive]);

            DB::commit();

            $statusText = $isActive ? 'активированы' : 'деактивированы';
            $message = "Успешно {$statusText} {$affectedCount} товаров";

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $affectedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Ошибка при массовом изменении статуса: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при изменении статуса товаров'
            ], 500);
        }
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
     * Импорт товаров из Excel файла (v3.0 Ultra-Fast Queue Import)
     * Использует очередь в БД для мгновенного ответа пользователю
     */
    public function importFromExcel(Request $request, TelegramBot $telegramBot)
    {
        // Убираем ограничения
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');
        
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:102400', // 100MB
            'update_existing' => 'boolean',
            'download_images' => 'boolean',
        ], [
            'file.required' => 'Файл обязателен для загрузки.',
            'file.mimes' => 'Файл должен быть в формате Excel (xlsx, xls) или CSV.',
            'file.max' => 'Размер файла не должен превышать 100 МБ.',
        ]);

        try {
            $updateExisting = $request->boolean('update_existing');
            $downloadImages = $request->boolean('download_images');
            
            Log::info('🚀 Starting ULTRA-FAST QUEUE import', [
                'user_id' => Auth::id(),
                'bot_id' => $telegramBot->id,
                'update_existing' => $updateExisting,
                'download_images' => $downloadImages,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize()
            ]);
            
            // v3.0: Сначала ВСЁ в БД, потом CRON обрабатывает
            $import = new ProductsImportQueue(
                Auth::id(),
                $telegramBot->id,
                $updateExisting,
                $downloadImages
            );

            // Сброс счётчика перед импортом
            ProductsImportQueue::resetCounter();

            // Импортируем (только запись в БД - БЫСТРО!)
            Excel::import($import, $request->file('file'));

            $totalImported = ProductsImportQueue::getTotalImported();
            $sessionId = $import->getImportSessionId();

            Log::info('✅ ULTRA-FAST import completed', [
                'session' => $sessionId,
                'imported_to_queue' => $totalImported
            ]);

            $message = "Импорт завершён! {$totalImported} товаров добавлено в очередь обработки. CRON начнёт обработку автоматически (каждую минуту 50 товаров).";

            return redirect()->route('bot.products.index', $telegramBot)
                ->with('success', $message)
                ->with('import_session_id', $sessionId);

        } catch (\Exception $e) {
            Log::error('QUEUE Import error: ' . $e->getMessage(), [
                'file' => $request->file('file')->getClientOriginalName(),
                'user_id' => Auth::id(),
                'bot_id' => $telegramBot->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('bot.products.index', $telegramBot)
                ->with('error', 'Ошибка при импорте файла: ' . $e->getMessage());
        }
    }

    /**
     * AJAX импорт товаров с возвратом JSON
     */
    public function ajaxImport(Request $request, TelegramBot $telegramBot)
    {
        // ПОЛНОСТЬЮ убираем ограничения времени и памяти
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('memory_limit', '-1');
        @ini_set('display_errors', '0'); // Не выводить ошибки в браузер
        
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет доступа к этому боту.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:102400', // 100MB для ОЧЕНЬ больших файлов
            'update_existing' => 'boolean',
            'download_images' => 'boolean',
        ]);

        try {
            $updateExisting = $request->boolean('update_existing');
            $downloadImages = $request->boolean('download_images');
            
            Log::info('🚀🚀🚀 Starting ULTRA-FAST QUEUE import', [
                'user_id' => Auth::id(),
                'bot_id' => $telegramBot->id,
                'update_existing' => $updateExisting,
                'download_images' => $downloadImages,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize()
            ]);
            
            // НОВАЯ СТРАТЕГИЯ: Сначала ВСЁ в БД, потом CRON обрабатывает
            $import = new \App\Imports\ProductsImportQueue(
                Auth::id(),
                $telegramBot->id,
                $updateExisting,
                $downloadImages
            );

            // Сброс счётчика перед импортом
            \App\Imports\ProductsImportQueue::resetCounter();

            // Импортируем (только запись в БД - БЫСТРО!)
            Excel::import($import, $request->file('file'));

            $totalImported = \App\Imports\ProductsImportQueue::getTotalImported();
            $sessionId = $import->getImportSessionId();

            Log::info('✅✅✅ ULTRA-FAST import completed', [
                'session' => $sessionId,
                'imported_to_queue' => $totalImported
            ]);

            return response()->json([
                'success' => true,
                'message' => "Импорт завершён! {$totalImported} товаров добавлено в очередь обработки. CRON начнёт обработку автоматически.",
                'import_session_id' => $sessionId,
                'stats' => [
                    'queued' => $totalImported,
                    'type' => 'queue_table',
                    'status' => 'queued',
                    'info' => 'Товары сохранены в очередь. CRON обрабатывает их в фоне (каждую минуту).'
                ]
            ]);

        } catch (\Throwable $e) {
            // Логируем полную информацию об ошибке
            Log::error('❌ QUEUE Import error: ' . $e->getMessage(), [
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'unknown',
                'user_id' => Auth::id(),
                'bot_id' => $telegramBot->id,
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file_path' => $e->getFile()
            ]);

            // Всегда возвращаем JSON
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при импорте: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ], 500);
        }
    }



    /**
     * Проверка, является ли файл изображением по расширению
     */
    private function isImageFile($filename)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'tif', 'heic', 'heif', 'avif', 'ico', 'raw', 'dng', 'cr2', 'nef', 'arw', 'psd', 'ai', 'eps'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }
}