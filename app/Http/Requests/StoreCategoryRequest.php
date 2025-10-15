<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCategoryRequest extends FormRequest
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
            'description' => 'nullable|string|max:1000',
            'photo_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Настроить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название категории обязательно для заполнения.',
            'name.string' => 'Название категории должно быть строкой.',
            'name.max' => 'Название категории не должно превышать 255 символов.',
            'description.string' => 'Описание должно быть строкой.',
            'description.max' => 'Описание не должно превышать 1000 символов.',
            'photo_url.url' => 'URL фотографии должен быть корректным URL.',
            'photo_url.max' => 'URL фотографии не должен превышать 500 символов.',
            'is_active.boolean' => 'Статус активности должен быть булевым значением.',
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
    }
}
