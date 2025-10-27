<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TelegramBot;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramBotService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработка webhook от Telegram
     */
    public function handle(Request $request, TelegramBot $bot)
    {
        $update = $request->all();
        
        Log::info('Telegram webhook received', [
            'bot_id' => $bot->id,
            'update' => $update
        ]);

        // Обработка callback query (нажатия кнопок)
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query'], $bot);
        }

        // Обработка обычных сообщений
        if (isset($update['message'])) {
            return $this->handleMessage($update['message'], $bot);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Обработка нажатий на кнопки (callback query)
     */
    private function handleCallbackQuery(array $callbackQuery, TelegramBot $bot)
    {
        $callbackData = $callbackQuery['data'] ?? '';
        $callbackQueryId = $callbackQuery['id'] ?? '';
        $chatId = $callbackQuery['from']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;

        Log::info('Processing callback query', [
            'callback_data' => $callbackData,
            'chat_id' => $chatId,
            'bot_id' => $bot->id
        ]);

        // Подтверждение оплаты
        if (str_starts_with($callbackData, 'confirm_payment_')) {
            $orderId = (int) str_replace('confirm_payment_', '', $callbackData);
            return $this->confirmPayment($orderId, $bot, $chatId, $messageId, $callbackQueryId);
        }

        // Отмена заказа
        if (str_starts_with($callbackData, 'cancel_order_')) {
            $orderId = (int) str_replace('cancel_order_', '', $callbackData);
            return $this->cancelOrder($orderId, $bot, $chatId, $messageId, $callbackQueryId);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Подтвердить оплату заказа
     */
    private function confirmPayment(int $orderId, TelegramBot $bot, int $chatId, int $messageId, string $callbackQueryId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ не найден', true);
            return response()->json(['ok' => true]);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ уже обработан', true);
            return response()->json(['ok' => true]);
        }

        try {
            // Используем метод модели для подтверждения оплаты
            // Он автоматически списывает товары и обновляет статус
            $success = $order->confirmPayment();

            if (!$success) {
                $this->answerCallbackQuery($bot, $callbackQueryId, 'Ошибка при подтверждении оплаты', true);
                return response()->json(['ok' => true]);
            }

            // Обновляем сообщение администратору
            $this->editMessage($bot, $chatId, $messageId, 
                "✅ <b>ОПЛАТА ПОДТВЕРЖДЕНА!</b>\n\n" .
                "📋 Заказ #{$order->order_number} успешно оплачен\n" .
                "💰 Сумма: {$order->formatted_total}\n\n" .
                "🎉 Товары списаны со склада\n" .
                "⏰ Подтверждено: " . now()->format('d.m.Y в H:i')
            );

            // Уведомляем клиента об успешной оплате
            if ($order->telegram_chat_id) {
                $this->telegramService->sendMessage($bot, $order->telegram_chat_id,
                    "🎉 <b>Оплата подтверждена!</b>\n\n" .
                    "Ваш заказ #{$order->order_number} успешно оплачен.\n" .
                    "Спасибо за покупку! 🙏"
                );
            }

            $this->answerCallbackQuery($bot, $callbackQueryId, 'Оплата подтверждена!');

            Log::info('Payment confirmed successfully', [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'bot_id' => $bot->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to confirm payment', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->answerCallbackQuery($bot, $callbackQueryId, 'Ошибка при подтверждении оплаты', true);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Отменить заказ
     */
    private function cancelOrder(int $orderId, TelegramBot $bot, int $chatId, int $messageId, string $callbackQueryId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ не найден', true);
            return response()->json(['ok' => true]);
        }

        if (!$order->canBeCancelled()) {
            $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ нельзя отменить', true);
            return response()->json(['ok' => true]);
        }

        try {
            // Отменяем заказ и снимаем резерв
            $order->cancelAndUnreserve();

            // Обновляем сообщение администратору
            $this->editMessage($bot, $chatId, $messageId, 
                "❌ <b>ЗАКАЗ ОТМЕНЕН</b>\n\n" .
                "📋 Заказ #{$order->order_number} отменен администратором\n" .
                "💰 Сумма: {$order->formatted_total}\n\n" .
                "🔄 Товары возвращены на склад"
            );

            // Уведомляем клиента об отмене
            if ($order->telegram_chat_id) {
                $this->telegramService->sendMessage($bot, $order->telegram_chat_id,
                    "❌ <b>Заказ отменен</b>\n\n" .
                    "К сожалению, ваш заказ #{$order->order_number} был отменен.\n" .
                    "По всем вопросам обращайтесь к администратору."
                );
            }

            $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ отменен');

            Log::info('Order cancelled successfully', [
                'order_id' => $orderId,
                'bot_id' => $bot->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel order', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            $this->answerCallbackQuery($bot, $callbackQueryId, 'Ошибка при отмене заказа', true);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Обработка обычных сообщений
     */
    private function handleMessage(array $message, TelegramBot $bot)
    {
        $text = $message['text'] ?? '';
        $chatId = $message['from']['id'] ?? null;

        // Обработка команды /start
        if ($text === '/start') {
            $this->telegramService->sendMessage($bot, $chatId,
                "👋 Добро пожаловать в {$bot->bot_name}!\n\n" .
                "Используйте меню для работы с приложением."
            );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Ответить на callback query
     */
    private function answerCallbackQuery(TelegramBot $bot, string $callbackQueryId, string $text, bool $showAlert = false)
    {
        $this->telegramService->answerCallbackQuery($bot, $callbackQueryId, $text, $showAlert);
    }

    /**
     * Редактировать сообщение
     */
    private function editMessage(TelegramBot $bot, int $chatId, int $messageId, string $text)
    {
        // Сначала редактируем текст сообщения
        $this->telegramService->editMessageText($bot, $chatId, $messageId, $text);
        // Затем удаляем кнопки
        $this->telegramService->editMessageReplyMarkup($bot, $chatId, $messageId);
    }
}
