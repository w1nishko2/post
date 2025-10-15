<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTelegramBotRequest extends FormRequest
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
            'bot_name' => 'required|string|max:255',
            'bot_username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('telegram_bots')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'bot_token' => [
                'required',
                'string',
                'regex:/^\d+:[a-zA-Z0-9_-]+$/',
                Rule::unique('telegram_bots')
            ],
            'admin_telegram_id' => 'nullable|string|max:20|regex:/^\d+$/',
            'api_id' => 'nullable|string|max:255',
            'api_hash' => 'nullable|string|max:255',
            'mini_app_url' => 'nullable|string|max:255',
            'mini_app_short_name' => 'nullable|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
        ];
    }

    /**
     * Настроить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'bot_name.required' => 'Название бота обязательно для заполнения.',
            'bot_name.max' => 'Название бота не должно превышать 255 символов.',
            
            'bot_username.required' => 'Username бота обязателен для заполнения.',
            'bot_username.unique' => 'У вас уже есть бот с таким username.',
            'bot_username.regex' => 'Username может содержать только буквы, цифры и подчеркивания.',
            
            'bot_token.required' => 'Токен бота обязателен для заполнения.',
            'bot_token.unique' => 'Этот токен уже используется.',
            'bot_token.regex' => 'Неверный формат токена. Токен должен быть в формате: 123456789:ABCdefGHijklMNOpqrsTUvwxyz',
            
            'admin_telegram_id.max' => 'ID администратора не должен превышать 20 символов.',
            'admin_telegram_id.regex' => 'ID администратора должен содержать только цифры.',
            
            'mini_app_url.url' => 'URL Mini App должен быть корректным URL адресом.',
            'mini_app_url.max' => 'URL Mini App не должен превышать 255 символов.',
            
            'mini_app_short_name.max' => 'Короткое имя Mini App не должно превышать 64 символа.',
            'mini_app_short_name.regex' => 'Короткое имя может содержать только буквы, цифры и подчеркивания.',
        ];
    }

    /**
     * Подготовить данные для валидации
     */
    protected function prepareForValidation(): void
    {
        // Очищаем bot_username от символа @
        if ($this->has('bot_username')) {
            $this->merge([
                'bot_username' => ltrim($this->bot_username, '@')
            ]);
        }

        // Очищаем bot_token от лишних пробелов
        if ($this->has('bot_token')) {
            $this->merge([
                'bot_token' => trim($this->bot_token)
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