<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTelegramBotRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для этого запроса
     */
    public function authorize(): bool
    {
        $bot = $this->route('telegramBot') ?? $this->route('telegram_bot') ?? $this->route('bot');
        
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
            'bot_name' => 'required|string|max:255',
            'admin_telegram_id' => 'nullable|string|max:20|regex:/^\d+$/',
            'api_id' => 'nullable|string|max:255',
            'api_hash' => 'nullable|string|max:255',
            'mini_app_url' => 'nullable|string|max:255',
            'mini_app_short_name' => 'nullable|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
            'is_active' => 'boolean',
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
            
            'admin_telegram_id.max' => 'ID администратора не должен превышать 20 символов.',
            'admin_telegram_id.regex' => 'ID администратора должен содержать только цифры.',
            
            'api_id.max' => 'API ID не должен превышать 255 символов.',
            'api_hash.max' => 'API Hash не должен превышать 255 символов.',
            
            'mini_app_url.url' => 'URL Mini App должен быть корректным URL адресом.',
            'mini_app_url.max' => 'URL Mini App не должен превышать 255 символов.',
            
            'mini_app_short_name.max' => 'Короткое имя Mini App не должно превышать 64 символа.',
            'mini_app_short_name.regex' => 'Короткое имя может содержать только буквы, цифры и подчеркивания.',
            
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
            'is_active' => $this->has('is_active') && $this->is_active
        ]);

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