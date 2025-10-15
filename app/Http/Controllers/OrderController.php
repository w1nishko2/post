<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // Проверяем, что заказ принадлежит текущему пользователю
        if ($order->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому заказу');
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Этот заказ нельзя отменить'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Возвращаем товары на склад
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }

            // Обновляем статус заказа
            $order->update(['status' => Order::STATUS_CANCELLED]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Заказ отменен, товары возвращены на склад',
                'new_status' => $order->status_label,
                'new_status_class' => $order->status_class,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отмене заказа'
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