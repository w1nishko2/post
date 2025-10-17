<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TelegramBot;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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