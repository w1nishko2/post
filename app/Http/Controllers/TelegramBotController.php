<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use App\Http\Requests\StoreTelegramBotRequest;
use App\Http\Requests\UpdateTelegramBotRequest;
use App\Http\Requests\SetupMiniAppRequest;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TelegramBotController extends Controller
{
    protected $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        $this->telegramBotService = $telegramBotService;
    }
    /**
     * Отобразить список ботов пользователя
     */
    public function index()
    {
        $bots = Auth::user()->telegramBots()->latest()->get();
        return view('home', compact('bots'));
    }

    /**
     * Показать конкретного бота
     */
    public function show(TelegramBot $telegramBot)
    {
        $this->authorize('view', $telegramBot);
        return response()->json($telegramBot->load('user'));
    }

    /**
     * Показать форму создания нового бота
     */
    public function create()
    {
        return view('home');
    }

    /**
     * Сохранить нового бота
     */
    public function store(StoreTelegramBotRequest $request)
    {
        $validated = $request->validated();

        // Проверяем валидность токена бота через Telegram API
        try {
            if (!$this->telegramBotService->validateBotToken($validated['bot_token'])) {
                return back()->withErrors(['bot_token' => 'Неверный токен бота или бот недоступен. Проверьте токен и убедитесь, что бот создан в @BotFather.'])
                            ->withInput();
            }

            // Получаем информацию о боте
            $botInfo = $this->telegramBotService->getBotInfo($validated['bot_token']);
            if (!$botInfo) {
                return back()->withErrors(['bot_token' => 'Не удалось получить информацию о боте. Проверьте подключение к интернету.'])
                            ->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['bot_token' => 'Произошла ошибка при проверке токена: ' . $e->getMessage()])
                        ->withInput();
        }

        $validated['user_id'] = Auth::id();
        $validated['bot_username'] = $botInfo['username'] ?? $validated['bot_username'];
        $validated['last_updated_at'] = now();

        $bot = TelegramBot::create($validated);

        // Устанавливаем webhook если нужно
        $webhookUrl = $this->telegramBotService->getWebhookUrl($bot);
        $this->telegramBotService->setWebhook($bot, $webhookUrl);

        return redirect()->route('home')
                        ->with('success', 'Telegram бот успешно добавлен!');
    }

    /**
     * Показать форму редактирования бота
     */
    public function edit(TelegramBot $telegramBot)
    {
        $this->authorize('update', $telegramBot);
        return view('home', ['bot' => $telegramBot]);
    }

    /**
     * Обновить данные бота
     */
    public function update(UpdateTelegramBotRequest $request, TelegramBot $telegramBot)
    {
        $validated = $request->validated();

        $validated['last_updated_at'] = now();

        // Обработка загрузки логотипа
        if ($request->hasFile('logo')) {
            $imageService = app(\App\Services\ImageUploadService::class);
            
            try {
                // Удаляем старый логотип если есть
                if ($telegramBot->logo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($telegramBot->logo);
                }
                
                // Загружаем новый логотип
                $result = $imageService->upload($request->file('logo'), 'logos');
                $validated['logo'] = $result['file_path'];
            } catch (\Exception $e) {
                return back()->withErrors(['logo' => 'Ошибка при загрузке логотипа: ' . $e->getMessage()])
                            ->withInput();
            }
        }
        
        // Удаление логотипа если установлен флаг
        if ($request->has('remove_logo') && $request->remove_logo) {
            if ($telegramBot->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($telegramBot->logo);
            }
            $validated['logo'] = null;
        }

        $telegramBot->update($validated);

        // Обновляем webhook если нужно
        if ($telegramBot->is_active) {
            $webhookUrl = $this->telegramBotService->getWebhookUrl($telegramBot);
            $this->telegramBotService->setWebhook($telegramBot, $webhookUrl);
        }

        return redirect()->route('home')
                        ->with('success', 'Настройки бота обновлены!');
    }

    /**
     * Удалить бота
     */
    public function destroy(TelegramBot $telegramBot)
    {
        $this->authorize('delete', $telegramBot);

        // Удаляем webhook
        $this->telegramBotService->deleteWebhook($telegramBot);

        $telegramBot->delete();

        return redirect()->route('home')
                        ->with('success', 'Telegram бот удален!');
    }

    /**
     * Переключить статус активности бота
     */
    public function toggle(TelegramBot $telegramBot)
    {
        $this->authorize('update', $telegramBot);

        $telegramBot->update([
            'is_active' => !$telegramBot->is_active,
            'last_updated_at' => now()
        ]);

        if ($telegramBot->is_active) {
            $webhookUrl = $this->telegramBotService->getWebhookUrl($telegramBot);
            $this->telegramBotService->setWebhook($telegramBot, $webhookUrl);
        } else {
            $this->telegramBotService->deleteWebhook($telegramBot);
        }

        $status = $telegramBot->is_active ? 'активирован' : 'деактивирован';
        return redirect()->route('home')
                        ->with('success', "Бот {$status}!");
    }

    /**
     * Настроить Mini App для бота
     */
    public function setupMiniApp(SetupMiniAppRequest $request, TelegramBot $telegramBot)
    {
        try {
            $validated = $request->validated();

            Log::info('Setup Mini App request', [
                'bot_id' => $telegramBot->id,
                'validated_data' => $validated,
                'raw_request' => $request->all()
            ]);

            // Настраиваем кнопку меню
            $menuButton = [
                'type' => 'web_app',
                'text' => $validated['menu_button_text'] ?? 'Открыть', // Максимум 16 символов
                'web_app' => [
                    'url' => $validated['mini_app_url']
                ]
            ];

            $updated = $telegramBot->update([
                'mini_app_url' => $validated['mini_app_url'],
                'mini_app_short_name' => $validated['mini_app_short_name'],
                'menu_button' => $menuButton,
                'last_updated_at' => now()
            ]);

            Log::info('Mini App setup completed', [
                'bot_id' => $telegramBot->id,
                'updated' => $updated,
                'mini_app_url' => $telegramBot->fresh()->mini_app_url,
                'mini_app_short_name' => $telegramBot->fresh()->mini_app_short_name
            ]);

            // Настраиваем Mini App через сервис
            $this->telegramBotService->setupMiniApp($telegramBot, $validated);

            return redirect()->route('home')
                            ->with('success', 'Mini App успешно настроен! URL: ' . $validated['mini_app_url']);
        } catch (\Exception $e) {
            Log::error('Setup Mini App failed', [
                'bot_id' => $telegramBot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('home')
                            ->with('error', 'Ошибка при настройке Mini App: ' . $e->getMessage());
        }
    }




}