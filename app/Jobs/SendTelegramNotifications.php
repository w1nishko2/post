<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\TelegramBot;
use App\Services\TelegramBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Количество попыток
    public $timeout = 30; // Таймаут выполнения задачи

    protected $order;
    protected $bot;

    /**
     * Создать новую задачу
     */
    public function __construct(Order $order, TelegramBot $bot)
    {
        $this->order = $order;
        $this->bot = $bot;
    }

    /**
     * Выполнить задачу
     */
    public function handle(TelegramBotService $telegramService)
    {
        Log::info('Starting Telegram notifications job', [
            'order_id' => $this->order->id,
            'bot_id' => $this->bot->id
        ]);

        $adminNotificationSent = false;
        $customerNotificationSent = false;

        // Отправляем уведомления параллельно с таймаутом
        try {
            // Уведомление администратору
            if ($this->bot->admin_telegram_id) {
                $adminNotificationSent = $telegramService->sendOrderNotificationToAdmin($this->bot, $this->order);
            }

            // Уведомление клиенту
            if ($this->order->telegram_chat_id) {
                $customerNotificationSent = $telegramService->sendOrderConfirmationToCustomer($this->bot, $this->order);
            }

            Log::info('Telegram notifications job completed', [
                'order_id' => $this->order->id,
                'admin_sent' => $adminNotificationSent,
                'customer_sent' => $customerNotificationSent
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram notifications job failed', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage()
            ]);
            
            // Повторить попытку, если не исчерпаны все попытки
            if ($this->attempts() < $this->tries) {
                $this->release(60); // Повторить через 60 секунд
            }
            
            throw $e;
        }
    }

    /**
     * Обработка неудачи задачи
     */
    public function failed(\Exception $exception)
    {
        Log::error('Telegram notifications job failed permanently', [
            'order_id' => $this->order->id,
            'bot_id' => $this->bot->id,
            'error' => $exception->getMessage()
        ]);
    }
}