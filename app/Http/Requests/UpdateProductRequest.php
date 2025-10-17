<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для этого запроса
     */
    public function authorize(): bool
    {
        $product = $this->route('product');
        
        // Проверяем, что товар принадлежит текущему пользователю
        return Auth::check() && $product && $product->user_id === Auth::id();
    }

    /**
     * Правила валидации для запроса
     */
    public function rules(): array
    {
        $product = $this->route('product');
        
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'article' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'article')->ignore($product->id ?? null)
            ],
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
                'max:500',
                // Улучшенная валидация URL изображений
                'regex:/^https?:\/\/[\w\-\._~:\/?#\[\]@!$&\'()*+,;=]+\.(?:jpg|jpeg|png|gif|webp|bmp)$/i'
            ],
            'specifications' => 'nullable|array',
            'specifications.*' => 'string|max:255',
            'quantity' => 'required|integer|min:0|max:999999',
            'price' => 'required|numeric|min:0|max:999999.99',
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
            'photo_url.regex' => 'Ссылка должна указывать на изображение (jpg, jpeg, png, gif, webp, bmp).',
            
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
        ]);

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
    }
}