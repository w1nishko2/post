<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Статистический отчет</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #007bff;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        
        .stats-cell h3 {
            margin: 0;
            font-size: 18px;
            color: #007bff;
        }
        
        .stats-cell p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .number {
            text-align: right;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .small {
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding: 10px 0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Статистический отчет магазина</h1>
        <p><strong>Пользователь:</strong> <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</p>
        <?php if($bot): ?>
            <p><strong>Telegram бот:</strong> <?php echo e($bot->bot_name ?? $bot->name); ?></p>
        <?php else: ?>
            <p><strong>Данные:</strong> Все боты пользователя</p>
        <?php endif; ?>
        <p><strong>Период:</strong> <?php echo e($dates['start']->format('d.m.Y')); ?> - <?php echo e($dates['end']->format('d.m.Y')); ?></p>
        <p><strong>Дата создания отчета:</strong> <?php echo e(now()->format('d.m.Y H:i')); ?></p>
    </div>

    <div class="section">
        <div class="section-title">Общая статистика</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <h3><?php echo e(number_format($generalStats['total_visits'])); ?></h3>
                    <p>Всего посещений</p>
                </div>
                <div class="stats-cell">
                    <h3><?php echo e(number_format($generalStats['unique_visitors'])); ?></h3>
                    <p>Уникальные посетители</p>
                </div>
                <div class="stats-cell">
                    <h3><?php echo e(number_format($generalStats['completed_orders'])); ?></h3>
                    <p>Выполненные заказы</p>
                </div>
                <div class="stats-cell">
                    <h3><?php echo e(number_format($generalStats['total_revenue'], 0, ',', ' ')); ?> ₽</h3>
                    <p>Общая выручка</p>
                </div>
            </div>
        </div>
        
        <table>
            <tr>
                <td><strong>Конверсия:</strong></td>
                <td class="text-right"><?php echo e($generalStats['conversion_rate']); ?>%</td>
                <td><strong>Средний чек:</strong></td>
                <td class="text-right"><?php echo e(number_format($generalStats['average_order_value'], 0, ',', ' ')); ?> ₽</td>
            </tr>
            <tr>
                <td><strong>Всего заказов:</strong></td>
                <td class="text-right"><?php echo e(number_format($generalStats['total_orders'])); ?></td>
                <td><strong>Среднее время обработки:</strong></td>
                <td class="text-right"><?php echo e($orderStats['average_processing_time'] ?? 0); ?> ч</td>
            </tr>
        </table>
    </div>

    <?php if(isset($detailedVisits['daily_breakdown']) && $detailedVisits['daily_breakdown']->count() > 0): ?>
    <div class="section">
        <div class="section-title">Посещения по дням</div>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th class="number">Всего посещений</th>
                    <th class="number">Уникальные посетители</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $detailedVisits['daily_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(\Carbon\Carbon::parse($day->date)->format('d.m.Y')); ?></td>
                    <td class="number"><?php echo e(number_format($day->total_visits)); ?></td>
                    <td class="number"><?php echo e(number_format($day->unique_visitors)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(isset($detailedOrders['status_breakdown']) && $detailedOrders['status_breakdown']->count() > 0): ?>
    <div class="section">
        <div class="section-title">Заказы по статусам</div>
        <table>
            <thead>
                <tr>
                    <th>Статус</th>
                    <th class="number">Количество</th>
                    <th class="number">Сумма</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $detailedOrders['status_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <?php switch($status->status):
                            case ('pending'): ?> Ожидание <?php break; ?>
                            <?php case ('processing'): ?> В обработке <?php break; ?>
                            <?php case ('completed'): ?> Выполнен <?php break; ?>
                            <?php case ('cancelled'): ?> Отменен <?php break; ?>
                            <?php default: ?> <?php echo e($status->status); ?>

                        <?php endswitch; ?>
                    </td>
                    <td class="number"><?php echo e(number_format($status->count)); ?></td>
                    <td class="number"><?php echo e(number_format($status->total_amount, 0, ',', ' ')); ?> ₽</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(isset($productStats['best_selling_products']) && $productStats['best_selling_products']->count() > 0): ?>
    <div class="section page-break">
        <div class="section-title">Самые покупаемые товары</div>
        <table>
            <thead>
                <tr>
                    <th>Товар</th>
                    <th class="number">Продано</th>
                    <th class="number">Заказов</th>
                    <th class="number">Выручка</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $productStats['best_selling_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td class="number"><?php echo e(number_format($product->total_sold)); ?></td>
                    <td class="number"><?php echo e(number_format($product->orders_count)); ?></td>
                    <td class="number"><?php echo e(number_format($product->total_revenue, 0, ',', ' ')); ?> ₽</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(isset($visitStats['top_pages']) && $visitStats['top_pages']->count() > 0): ?>
    <div class="section">
        <div class="section-title">Популярные страницы</div>
        <table>
            <thead>
                <tr>
                    <th>Страница</th>
                    <th class="number">Посещения</th>
                    <th class="number">Уникальные посетители</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $visitStats['top_pages']->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-size: 10px;"><?php echo e($page->page_url); ?></td>
                    <td class="number"><?php echo e(number_format($page->visits)); ?></td>
                    <td class="number"><?php echo e(number_format($page->unique_visitors)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(isset($detailedVisits['top_referrers']) && $detailedVisits['top_referrers']->count() > 0): ?>
    <div class="section">
        <div class="section-title">Основные источники трафика</div>
        <table>
            <thead>
                <tr>
                    <th>Источник</th>
                    <th class="number">Посещения</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $detailedVisits['top_referrers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referrer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-size: 10px;"><?php echo e($referrer->referer); ?></td>
                    <td class="number"><?php echo e(number_format($referrer->visits)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(isset($detailedOrders['recent_orders']) && $detailedOrders['recent_orders']->count() > 0): ?>
    <div class="section page-break">
        <div class="section-title">Последние заказы</div>
        <table>
            <thead>
                <tr>
                    <th>№ заказа</th>
                    <th>Дата</th>
                    <th>Клиент</th>
                    <th>Статус</th>
                    <th class="number">Сумма</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $detailedOrders['recent_orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($order->order_number); ?></td>
                    <td><?php echo e($order->created_at->format('d.m.Y H:i')); ?></td>
                    <td><?php echo e($order->customer_name ?? 'Не указан'); ?></td>
                    <td>
                        <?php switch($order->status):
                            case ('pending'): ?> Ожидание <?php break; ?>
                            <?php case ('processing'): ?> В обработке <?php break; ?>
                            <?php case ('completed'): ?> Выполнен <?php break; ?>
                            <?php case ('cancelled'): ?> Отменен <?php break; ?>
                            <?php default: ?> <?php echo e($order->status); ?>

                        <?php endswitch; ?>
                    </td>
                    <td class="number"><?php echo e(number_format($order->total_amount, 0, ',', ' ')); ?> ₽</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">Сводка по устройствам</div>
        <?php if(isset($detailedVisits['device_stats']) && $detailedVisits['device_stats']->count() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Тип устройства</th>
                    <th class="number">Посещения</th>
                    <th class="number">Процент</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalDeviceVisits = $detailedVisits['device_stats']->sum('visits'); ?>
                <?php $__currentLoopData = $detailedVisits['device_stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($device->device_type); ?></td>
                    <td class="number"><?php echo e(number_format($device->visits)); ?></td>
                    <td class="number"><?php echo e($totalDeviceVisits > 0 ? round(($device->visits / $totalDeviceVisits) * 100, 1) : 0); ?>%</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Нет данных об устройствах за выбранный период.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Отчет сгенерирован <?php echo e(now()->format('d.m.Y в H:i')); ?> | Система аналитики интернет-магазина</p>
    </div>
</body>
</html><?php /**PATH C:\OSPanel\domains\post\resources\views/statistics/report.blade.php ENDPATH**/ ?>