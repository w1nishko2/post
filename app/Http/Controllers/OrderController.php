<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отобразить список заказов пользователя
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'telegramBot'])
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Показать конкретный заказ
     */
    public function show(Order $order)
    {
        // Проверяем, что заказ принадлежит текущему пользователю
        if ($order->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому заказу');
        }

        $order->load(['items.product', 'telegramBot']);

        return view('orders.show', compact('order'));
    }

    /**
     * Обновить статус заказа (только для владельца бота)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::getStatuses())),
        ]);

        // Проверяем, что заказ принадлежит текущему пользователю или пользователь - владелец бота
        if ($order->user_id !== Auth::id() && $order->telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому заказу');
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Логируем изменение статуса можно добавить позже через пакет spatie/laravel-activitylog

        return response()->json([
            'success' => true,
            'message' => 'Статус заказа обновлен',
            'new_status' => $order->status_label,
            'new_status_class' => $order->status_class,
        ]);
    }

    /**
     * Отменить заказ
     */
    public function cancel(Order $order)
    {
        // Проверяем, что заказ принадлежит текущему пользователю или владельцу бота
        if ($order->user_id !== Auth::id() && $order->telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому заказу');
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Этот заказ нельзя отменить'
            ], 400);
        }

        try {
            // Используем метод модели для отмены заказа и снятия резерва
            $success = $order->cancelAndUnreserve();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось отменить заказ'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Заказ отменен, товары возвращены на склад',
                'new_status' => $order->status_label,
                'new_status_class' => $order->status_class,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отмене заказа'
            ], 500);
        }
    }

    /**
     * Подтвердить оплату заказа (для владельца бота)
     */
    public function confirmPayment(Order $order)
    {
        // Проверяем, что пользователь - владелец бота
        if ($order->telegramBot->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому заказу');
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Этот заказ уже обработан'
            ], 400);
        }

        try {
            // Используем метод модели для подтверждения оплаты
            $success = $order->confirmPayment();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось подтвердить оплату'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Оплата подтверждена! Товары списаны со склада.',
                'new_status' => $order->status_label,
                'new_status_class' => $order->status_class,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to confirm payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при подтверждении оплаты'
            ], 500);
        }
    }

    /**
     * Получить заказы для конкретного бота (для владельца бота)
     */
    public function botOrders(Request $request)
    {
        $botId = $request->get('bot_id');
        
        if (!$botId) {
            return response()->json(['error' => 'Bot ID is required'], 400);
        }

        // Проверяем, что бот принадлежит текущему пользователю
        $bot = \App\Models\TelegramBot::where('id', $botId)
                                      ->where('user_id', Auth::id())
                                      ->first();
        
        if (!$bot) {
            return response()->json(['error' => 'Bot not found or access denied'], 403);
        }

        $orders = $bot->orders()
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Получить статистику заказов для дашборда
     */
    public function stats()
    {
        $userId = Auth::id();
        
        // Статистика для товаров пользователя
        $userOrdersStats = Order::where('user_id', $userId)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Статистика для заказов через ботов пользователя
        $botOrdersStats = Order::whereHas('telegramBot', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return response()->json([
            'user_orders' => $userOrdersStats,
            'bot_orders' => $botOrdersStats,
        ]);
    }
}