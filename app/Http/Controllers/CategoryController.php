<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отобразить список категорий для конкретного бота
     */
    public function index(TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        $categories = $telegramBot->categories()
            ->with('activeProducts')
            ->latest()
            ->paginate(15);

        return view('categories.index', compact('telegramBot', 'categories'));
    }

    /**
     * Показать форму создания новой категории
     */
    public function create(TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        return view('categories.create', compact('telegramBot'));
    }

    /**
     * Сохранить новую категорию
     */
    public function store(Request $request, TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому боту.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|file|image|max:10240|mimes:jpeg,jpg,png,gif,webp,bmp,tiff,tif,avif,heic,heif',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Название категории обязательно для заполнения.',
            'photo.image' => 'Файл должен быть изображением.',
            'photo.max' => 'Размер изображения не должен превышать 10MB.',
            'photo.mimes' => 'Поддерживаемые форматы: JPEG, PNG, GIF, WebP, BMP, TIFF, AVIF, HEIC/HEIF',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'telegram_bot_id' => $telegramBot->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        // Обработка загрузки фотографии
        if ($request->hasFile('photo')) {
            try {
                $imageService = app(\App\Services\ImageUploadService::class);
                $uploadResult = $imageService->upload($request->file('photo'), 'categories');
                $data['photo_url'] = $uploadResult['file_path']; // Сохраняем только путь без /storage/
            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ошибка при загрузке изображения: ' . $e->getMessage());
            }
        }

        Category::create($data);

        return redirect()
            ->route('bot.categories.index', $telegramBot)
            ->with('success', 'Категория успешно создана!');
    }

    /**
     * Показать конкретную категорию
     */
    public function show(TelegramBot $telegramBot, Category $category)
    {
        // Проверяем права доступа
        if ($telegramBot->user_id !== Auth::id() || $category->telegram_bot_id !== $telegramBot->id) {
            abort(403, 'У вас нет доступа к этой категории.');
        }

        $products = $category->products()
            ->active()
            ->orderedForListing()
            ->paginate(12);

        return view('categories.show', compact('telegramBot', 'category', 'products'));
    }

    /**
     * Показать форму редактирования категории
     */
    public function edit(TelegramBot $telegramBot, Category $category)
    {
        // Проверяем права доступа
        if ($telegramBot->user_id !== Auth::id() || $category->telegram_bot_id !== $telegramBot->id) {
            abort(403, 'У вас нет доступа к этой категории.');
        }

        return view('categories.edit', compact('telegramBot', 'category'));
    }

    /**
     * Обновить категорию
     */
    public function update(Request $request, TelegramBot $telegramBot, Category $category)
    {
        // Проверяем права доступа
        if ($telegramBot->user_id !== Auth::id() || $category->telegram_bot_id !== $telegramBot->id) {
            abort(403, 'У вас нет доступа к этой категории.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|file|image|max:10240|mimes:jpeg,jpg,png,gif,webp,bmp,tiff,tif,avif,heic,heif',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Название категории обязательно для заполнения.',
            'photo.image' => 'Файл должен быть изображением.',
            'photo.max' => 'Размер изображения не должен превышать 10MB.',
            'photo.mimes' => 'Поддерживаемые форматы: JPEG, PNG, GIF, WebP, BMP, TIFF, AVIF, HEIC/HEIF',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        // Обработка загрузки новой фотографии
        if ($request->hasFile('photo')) {
            try {
                $imageService = app(\App\Services\ImageUploadService::class);
                
                // Удаляем старую фотографию, если она есть
                if ($category->photo_url) {
                    $imageService->delete($category->photo_url, '');
                }
                
                $uploadResult = $imageService->upload($request->file('photo'), 'categories');
                $data['photo_url'] = $uploadResult['file_path'];
            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ошибка при загрузке изображения: ' . $e->getMessage());
            }
        }

        $category->update($data);

        return redirect()
            ->route('bot.categories.index', $telegramBot)
            ->with('success', 'Категория успешно обновлена!');
    }

    /**
     * Удалить категорию
     */
    public function destroy(TelegramBot $telegramBot, Category $category)
    {
        // Проверяем права доступа
        if ($telegramBot->user_id !== Auth::id() || $category->telegram_bot_id !== $telegramBot->id) {
            abort(403, 'У вас нет доступа к этой категории.');
        }

        // Проверяем, есть ли товары в этой категории
        $productsCount = $category->products()->count();
        
        if ($productsCount > 0) {
            return redirect()
                ->route('bot.categories.index', $telegramBot)
                ->with('error', "Невозможно удалить категорию, в которой есть товары ({$productsCount} шт.). Сначала переместите товары в другие категории или удалите их.");
        }

        $category->delete();

        return redirect()
            ->route('bot.categories.index', $telegramBot)
            ->with('success', 'Категория успешно удалена!');
    }

    /**
     * API: Получить категории для конкретного бота (для селекта)
     */
    public function apiIndex(TelegramBot $telegramBot)
    {
        // Проверяем, что бот принадлежит текущему пользователю
        if ($telegramBot->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $categories = $telegramBot->activeCategories()
            ->select('id', 'name')
            ->get();

        return response()->json($categories);
    }
}
