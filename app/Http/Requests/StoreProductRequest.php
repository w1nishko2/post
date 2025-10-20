<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для этого запроса
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для запроса
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'article' => 'required|string|max:100|unique:products,article',
            'category_id' => [
                'nullable',
                'exists:categories,id',
                // Проверяем, что категория принадлежит пользователю
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $category = \App\Models\Category::find($value);
                        if (!$category || $category->user_id !== Auth::id()) {
                            $fail('Выбранная категория не существует или не принадлежит вам.');
                        }
                    }
                },
            ],
            'photo_url' => [
                'nullable',
                'url',
                'max:500'
            ],
            'yandex_disk_folder_url' => [
                'nullable',
                'url',
                'max:500',
                'regex:/^https:\/\/disk\.yandex\.(ru|com)\/d\/[a-zA-Z0-9_-]+$/i'
            ],
            'photos_gallery' => [
                'nullable',
                'array',
                'max:20'
            ],
            'photos_gallery.*' => [
                'url',
                'max:1000'
            ],
            'main_photo_index' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $this->has('photos_gallery')) {
                        $photosGallery = $this->input('photos_gallery');
                        
                        // Если photos_gallery еще строка (JSON), декодируем
                        if (is_string($photosGallery)) {
                            $photosGallery = json_decode($photosGallery, true);
                        }
                        
                        if (is_array($photosGallery) && $value >= count($photosGallery)) {
                            $fail('Индекс главной фотографии не может быть больше количества фотографий в галерее.');
                        }
                    }
                }
            ],
            'specifications' => 'nullable|array',
            'specifications.*' => 'string|max:255',
            'quantity' => 'required|integer|min:0|max:999999',
            'price' => 'required|numeric|min:0|max:999999.99',
            'markup_percentage' => 'nullable|numeric|min:0|max:1000',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Настроить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название товара обязательно для заполнения.',
            'name.max' => 'Название товара не должно превышать 255 символов.',
            
            'description.max' => 'Описание не должно превышать 2000 символов.',
            
            'article.required' => 'Артикул товара обязателен для заполнения.',
            'article.unique' => 'Товар с таким артикулом уже существует.',
            'article.max' => 'Артикул не должен превышать 100 символов.',
            
            'category_id.exists' => 'Выбранная категория не существует.',
            
            'photo_url.url' => 'Ссылка на фото должна быть корректным URL.',
            'photo_url.max' => 'Ссылка на фото не должна превышать 500 символов.',
            
            'yandex_disk_folder_url.url' => 'Ссылка на папку Яндекс.Диска должна быть корректным URL.',
            'yandex_disk_folder_url.max' => 'Ссылка на папку не должна превышать 500 символов.',
            'yandex_disk_folder_url.regex' => 'Ссылка должна быть корректной ссылкой на публичную папку Яндекс.Диска.',
            
            'photos_gallery.array' => 'Галерея фотографий должна быть массивом.',
            'photos_gallery.max' => 'Галерея может содержать максимум 20 фотографий.',
            'photos_gallery.*.url' => 'Все элементы галереи должны быть корректными URL.',
            'photos_gallery.*.max' => 'URL фотографии не должен превышать 1000 символов.',
            
            'main_photo_index.integer' => 'Индекс главной фотографии должен быть числом.',
            'main_photo_index.min' => 'Индекс главной фотографии не может быть отрицательным.',
            
            'specifications.array' => 'Характеристики должны быть в виде списка.',
            'specifications.*.string' => 'Каждая характеристика должна быть строкой.',
            'specifications.*.max' => 'Характеристика не должна превышать 255 символов.',
            
            'quantity.required' => 'Количество товара обязательно для заполнения.',
            'quantity.integer' => 'Количество должно быть целым числом.',
            'quantity.min' => 'Количество не может быть отрицательным.',
            'quantity.max' => 'Количество не может превышать 999999.',
            
            'price.required' => 'Цена товара обязательна для заполнения.',
            'price.numeric' => 'Цена должна быть числом.',
            'price.min' => 'Цена не может быть отрицательной.',
            'price.max' => 'Цена не может превышать 999999.99.',
            
            'markup_percentage.numeric' => 'Наценка должна быть числом.',
            'markup_percentage.min' => 'Наценка не может быть отрицательной.',
            'markup_percentage.max' => 'Наценка не может превышать 1000%.',
            
            'is_active.boolean' => 'Статус активности должен быть true или false.',
        ];
    }

    /**
     * Подготовить данные для валидации
     */
    protected function prepareForValidation(): void
    {
        // Преобразуем checkbox в boolean
        $this->merge([
            'is_active' => $this->has('is_active') && $this->is_active,
            'user_id' => Auth::id(), // Автоматически добавляем ID пользователя
        ]);

        // Обработка ссылки на Яндекс.Диск - очистка от лишних пробелов
        if ($this->has('yandex_disk_folder_url')) {
            $yandexUrl = trim($this->input('yandex_disk_folder_url'));
            $this->merge(['yandex_disk_folder_url' => empty($yandexUrl) ? null : $yandexUrl]);
        }

        // Обработка характеристик - если они переданы как строки, разделенные переводом строки
        if ($this->has('specifications_text') && !empty($this->specifications_text)) {
            $specifications = [];
            $lines = explode("\n", $this->specifications_text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $specifications[] = $line;
                }
            }
            $this->merge(['specifications' => $specifications]);
        }

        // Обработка галереи фотографий из JSON
        if ($this->has('photos_gallery') && !empty($this->photos_gallery)) {
            $photosGallery = $this->photos_gallery;
            
            // Если это уже JSON строка, проверяем её корректность
            if (is_string($photosGallery)) {
                $decoded = json_decode($photosGallery, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Фильтруем пустые и некорректные URL
                    $validUrls = array_filter($decoded, function($url) {
                        return is_string($url) && !empty(trim($url)) && filter_var(trim($url), FILTER_VALIDATE_URL);
                    });
                    
                    // Переиндексируем массив и обрезаем до разумного лимита
                    $validUrls = array_values(array_slice($validUrls, 0, 20));
                    
                    // Сохраняем как массив, а не JSON строку
                    $this->merge(['photos_gallery' => $validUrls]);
                    
                    // Корректируем индекс главной фотографии
                    $mainPhotoIndex = (int) $this->input('main_photo_index', 0);
                    if ($mainPhotoIndex >= count($validUrls)) {
                        $this->merge(['main_photo_index' => count($validUrls) > 0 ? 0 : null]);
                    }
                }
            }
            // Если это уже массив (что не должно происходить через форму, но на всякий случай)
            elseif (is_array($photosGallery)) {
                $validUrls = array_filter($photosGallery, function($url) {
                    return is_string($url) && !empty(trim($url)) && filter_var(trim($url), FILTER_VALIDATE_URL);
                });
                $validUrls = array_values(array_slice($validUrls, 0, 20));
                $this->merge(['photos_gallery' => $validUrls]);
            }
        }
    }
}