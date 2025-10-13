<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SetupMiniAppRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для этого запроса
     */
    public function authorize(): bool
    {
        $bot = $this->route('telegramBot') ?? $this->route('bot');
        
        // Если бот не найден, разрешаем (будет обработано в контроллере)
        if (!$bot) {
            return true;
        }
        
        // Проверяем, что бот принадлежит текущему пользователю
        return Auth::check() && $bot->user_id === Auth::id();
    }

    /**
     * Правила валидации для запроса
     */
    public function rules(): array
    {
        return [
            'mini_app_url' => 'required|string|max:255',
            'mini_app_short_name' => 'required|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
            'menu_button_text' => 'nullable|string|max:16',
        ];
    }

    /**
     * Настроить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'mini_app_url.required' => 'URL Mini App обязателен для заполнения.',
            'mini_app_url.url' => 'URL Mini App должен быть корректным URL адресом.',
            'mini_app_url.max' => 'URL Mini App не должен превышать 255 символов.',
            
            'mini_app_short_name.required' => 'Короткое имя Mini App обязательно для заполнения.',
            'mini_app_short_name.max' => 'Короткое имя Mini App не должно превышать 64 символа.',
            'mini_app_short_name.regex' => 'Короткое имя может содержать только буквы, цифры и подчеркивания.',
            
            'menu_button_text.max' => 'Текст кнопки меню не должен превышать 16 символов.',
        ];
    }

    /**
     * Подготовить данные для валидации
     */
    protected function prepareForValidation(): void
    {
        // Устанавливаем значение по умолчанию для текста кнопки
        if (!$this->has('menu_button_text') || empty($this->menu_button_text)) {
            $this->merge([
                'menu_button_text' => 'Открыть приложение'
            ]);
        }

        // Автоматически формируем mini_app_url из mini_app_short_name
        if ($this->has('mini_app_short_name') && !empty($this->mini_app_short_name)) {
            $shortName = trim($this->mini_app_short_name);
            if (!empty($shortName)) {
                $this->merge([
                    'mini_app_url' => config('app.url') . '/' . $shortName
                ]);
            }
        }
    }
}