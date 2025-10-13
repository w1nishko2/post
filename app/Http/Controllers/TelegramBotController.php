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
        if (!$this->telegramBotService->validateBotToken($validated['bot_token'])) {
            return back()->withErrors(['bot_token' => 'Неверный токен бота или бот недоступен.'])
                        ->withInput();
        }

        // Получаем информацию о боте
        $botInfo = $this->telegramBotService->getBotInfo($validated['bot_token']);
        if (!$botInfo) {
            return back()->withErrors(['bot_token' => 'Не удалось получить информацию о боте.'])
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
        $validated = $request->validated();

        // Настраиваем кнопку меню
        $menuButton = [
            'type' => 'web_app',
            'text' => $validated['menu_button_text'] ?? 'Открыть приложение',
            'web_app' => [
                'url' => $validated['mini_app_url']
            ]
        ];

        $telegramBot->update([
            'mini_app_url' => $validated['mini_app_url'],
            'mini_app_short_name' => $validated['mini_app_short_name'],
            'menu_button' => $menuButton,
            'last_updated_at' => now()
        ]);

        // Настраиваем Mini App через сервис
        $this->telegramBotService->setupMiniApp($telegramBot, $validated);

        return redirect()->route('home')
                        ->with('success', 'Mini App настроен!');
    }

    /**
     * Настроить Forum-Auto API для бота
     */
    public function setupForumAuto(Request $request, TelegramBot $telegramBot)
    {
        $this->authorize('update', $telegramBot);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'forum_auto_login' => 'required|string|max:255',
            'forum_auto_pass' => 'nullable|string|max:255',
            'forum_auto_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('home')
                            ->withErrors($validator)
                            ->withInput();
        }

        $validated = $validator->validated();

        // Подготавливаем данные для обновления
        $updateData = [
            'forum_auto_login' => $validated['forum_auto_login'],
            'forum_auto_enabled' => $request->has('forum_auto_enabled'),
            'forum_auto_last_check' => now()
        ];

        // Если передан новый пароль, шифруем его
        if (!empty($validated['forum_auto_pass'])) {
            $updateData['forum_auto_pass'] = encrypt($validated['forum_auto_pass']);
        }

        // Если включаем API и есть все необходимые данные - тестируем подключение
        if ($updateData['forum_auto_enabled'] && ($telegramBot->forum_auto_pass || !empty($validated['forum_auto_pass']))) {
            try {
                $testPass = !empty($validated['forum_auto_pass']) ? 
                           $validated['forum_auto_pass'] : 
                           decrypt($telegramBot->forum_auto_pass);

                $response = Http::timeout(10)
                    ->withoutVerifying() // Игнорируем SSL сертификаты
                    ->get('https://api.forum-auto.ru/v2/clientinfo', [
                        'login' => $validated['forum_auto_login'],
                        'pass' => $testPass
                    ]);

                $data = $response->json();
                if (!$response->successful() || !is_array($data) || empty($data)) {
                    return redirect()->route('home')
                                    ->withErrors(['forum_auto_login' => 'Не удалось подключиться к Forum-Auto API. Проверьте логин и пароль.'])
                                    ->withInput();
                }
            } catch (\Exception $e) {
                return redirect()->route('home')
                                ->withErrors(['forum_auto_login' => 'Ошибка соединения с Forum-Auto API.'])
                                ->withInput();
            }
        }

        // Обновляем бота
        $telegramBot->update($updateData);

        $message = $updateData['forum_auto_enabled'] ? 
                  'Forum-Auto API успешно настроен и активирован!' : 
                  'Настройки Forum-Auto API сохранены!';

        return redirect()->route('home')->with('success', $message);
    }

    /**
     * Тестировать подключение к Forum-Auto API
     */
    public function testForumAutoConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'pass' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => 'Неверные данные']);
        }
        
        try {
            $login = $request->get('login');
            $pass = $request->get('pass');
            
            // Тестируем подключение напрямую к API
            $response = Http::timeout(10)
                ->withoutVerifying() // Игнорируем SSL сертификаты
                ->get('https://api.forum-auto.ru/v2/clientinfo', [
                    'login' => $login,
                    'pass' => $pass
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && !empty($data)) {
                    // Преобразуем массив в более удобный формат
                    $clientInfo = [];
                    foreach ($data as $item) {
                        if (isset($item['name']) && isset($item['value'])) {
                            $clientInfo[$item['name']] = $item['value'];
                        }
                    }
                    
                    return response()->json([
                        'success' => true,
                        'client_info' => $clientInfo
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Неверные данные для входа или ошибка API'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка соединения с сервером Forum-Auto: ' . $e->getMessage()
            ]);
        }
    }


}