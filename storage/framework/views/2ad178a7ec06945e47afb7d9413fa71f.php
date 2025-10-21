

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php if(session('success')): ?>
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle admin-me-2"></i>
            <?php echo e(session('success')); ?>

            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            <?php echo e(session('error')); ?>

            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('orders.index')); ?>">
            <i class="fas fa-shopping-cart"></i> Заказы
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('statistics.index')); ?>">
            <i class="fas fa-chart-line"></i> Статистика
        </a>
    </div>

    <!-- Хлебные крошки -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between">
                <nav class="admin-breadcrumb">
                    <a href="<?php echo e(route('orders.index')); ?>" class="admin-breadcrumb-link">
                        <i class="fas fa-list admin-me-1"></i>
                        Заказы
                    </a>
                    <span class="admin-breadcrumb-separator">/</span>
                    <span class="admin-breadcrumb-current"><?php echo e($order->order_number); ?></span>
                </nav>
                <a href="<?php echo e(route('orders.index')); ?>" class="admin-btn admin-btn-sm">
                    <i class="fas fa-arrow-left admin-me-2"></i>
                    К списку заказов
                </a>
            </div>
        </div>
    </div>

    <!-- Основная информация о заказе -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-shopping-cart admin-me-2"></i>
                Заказ <?php echo e($order->order_number); ?>

            </h5>
            <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                <?php switch($order->status):
                    case ('pending'): ?>
                        <span class="admin-badge admin-badge-warning">Ожидает обработки</span>
                        <?php break; ?>
                    <?php case ('processing'): ?>
                        <span class="admin-badge admin-badge-info">В обработке</span>
                        <?php break; ?>
                    <?php case ('completed'): ?>
                        <span class="admin-badge admin-badge-success">Выполнен</span>
                        <?php break; ?>
                    <?php case ('cancelled'): ?>
                        <span class="admin-badge admin-badge-danger">Отменен</span>
                        <?php break; ?>
                    <?php default: ?>
                        <span class="admin-badge"><?php echo e($order->status); ?></span>
                <?php endswitch; ?>

                <?php if($order->canBeCancelled()): ?>
                    <button class="admin-btn admin-btn-sm admin-btn-outline-danger" onclick="cancelOrder()">
                        <i class="fas fa-times admin-me-1"></i>
                        Отменить
                    </button>
                <?php endif; ?>
                
                <?php if($order->status === 'pending'): ?>
                    <button class="admin-btn admin-btn-sm admin-btn-success" onclick="completeOrder()">
                        <i class="fas fa-check admin-me-1"></i>
                        Выполнить
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="admin-card-body">
            <div class="admin-row">
                <div class="admin-col admin-col-6">
                    <!-- Информация о заказе -->
                    <div class="admin-order-info">
                        <h6 class="admin-mb-3">
                            <i class="fas fa-info-circle admin-me-2"></i>
                            Информация о заказе
                        </h6>
                        
                        <div class="admin-info-group admin-mb-3">
                            <div class="admin-info-label">Дата создания:</div>
                            <div class="admin-info-value"><?php echo e($order->created_at->format('d.m.Y H:i:s')); ?></div>
                        </div>
                        
                        <?php if($order->updated_at != $order->created_at): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Последнее обновление:</div>
                                <div class="admin-info-value"><?php echo e($order->updated_at->format('d.m.Y H:i:s')); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="admin-info-group admin-mb-3">
                            <div class="admin-info-label">Общая стоимость:</div>
                            <div class="admin-info-value admin-order-total-big"><?php echo e($order->formatted_total); ?></div>
                        </div>
                        
                        <div class="admin-info-group admin-mb-3">
                            <div class="admin-info-label">Общее количество товаров:</div>
                            <div class="admin-info-value"><?php echo e($order->total_items); ?> шт.</div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-col admin-col-6">
                    <!-- Информация о клиенте -->
                    <div class="admin-customer-info">
                        <h6 class="admin-mb-3">
                            <i class="fas fa-user admin-me-2"></i>
                            Информация о клиенте
                        </h6>
                        
                        <?php if($order->customer_name): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Имя:</div>
                                <div class="admin-info-value"><?php echo e($order->customer_name); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($order->customer_phone): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Телефон:</div>
                                <div class="admin-info-value">
                                    <a href="tel:<?php echo e($order->customer_phone); ?>"><?php echo e($order->customer_phone); ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($order->customer_telegram_id): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Telegram ID:</div>
                                <div class="admin-info-value admin-text-mono"><?php echo e($order->customer_telegram_id); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($order->telegramBot): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Бот:</div>
                                <div class="admin-info-value">
                                    <div class="admin-bot-info-inline">
                                        <div class="admin-bot-avatar-sm <?php echo e($order->telegramBot->is_active ? '' : 'inactive'); ?>">
                                            <i class="fas fa-robot"></i>
                                        </div>
                                        <div class="admin-bot-details">
                                            <div class="admin-bot-name"><?php echo e($order->telegramBot->bot_name); ?></div>
                                            <div class="admin-text-muted admin-small">{{ $order->telegramBot->bot_username }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($order->delivery_address): ?>
                            <div class="admin-info-group admin-mb-3">
                                <div class="admin-info-label">Адрес доставки:</div>
                                <div class="admin-info-value"><?php echo e($order->delivery_address); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Товары в заказе -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-header">
            <h5 class="admin-mb-0">
                <i class="fas fa-box admin-me-2"></i>
                Товары в заказе (<?php echo e($order->items->count()); ?>)
            </h5>
        </div>
        <div class="admin-card-body admin-p-0">
            <?php if($order->items->count() > 0): ?>
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th style="width: 100px;">Цена за шт.</th>
                                <th style="width: 80px;">Количество</th>
                                <th style="width: 120px;">Стоимость</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="admin-d-flex admin-align-items-center">
                                            <?php if($item->product && $item->product->photo_url): ?>
                                                <img src="<?php echo e($item->product->photo_url); ?>" alt="<?php echo e($item->product_name); ?>" 
                                                     class="admin-me-3" style="width: 48px; height: 48px; object-fit: cover; border-radius: var(--radius-sm);">
                                            <?php else: ?>
                                                <div class="admin-product-placeholder admin-me-3">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="admin-product-name"><?php echo e($item->product_name); ?></div>
                                                <?php if($item->product): ?>
                                                    <div class="admin-text-muted admin-small">
                                                        Артикул: <?php echo e($item->product->article ?? 'Не указан'); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo e(number_format($item->price, 0, ',', ' ')); ?> ₽</strong>
                                    </td>
                                    <td class="admin-text-center">
                                        <span class="admin-badge"><?php echo e($item->quantity); ?> шт.</span>
                                    </td>
                                    <td>
                                        <strong><?php echo e(number_format($item->total_price, 0, ',', ' ')); ?> ₽</strong>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="admin-text-right">Итого:</th>
                                <th>
                                    <strong class="admin-order-total-big"><?php echo e($order->formatted_total); ?></strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-empty-state">
                    <div class="admin-empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="admin-text-muted">В заказе нет товаров</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- История изменений заказа -->
    <?php if($order->status_history && $order->status_history->count() > 0): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-mb-0">
                    <i class="fas fa-history admin-me-2"></i>
                    История изменений
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="admin-timeline">
                    <?php $__currentLoopData = $order->status_history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="admin-timeline-item">
                            <div class="admin-timeline-marker"></div>
                            <div class="admin-timeline-content">
                                <div class="admin-timeline-title"><?php echo e($history->status_label); ?></div>
                                <div class="admin-timeline-time"><?php echo e($history->created_at->format('d.m.Y H:i:s')); ?></div>
                                <?php if($history->comment): ?>
                                    <div class="admin-timeline-comment"><?php echo e($history->comment); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function cancelOrder() {
    if (confirm('Вы уверены, что хотите отменить этот заказ?\n\nТовары будут возвращены на склад.')) {
        fetch(`/orders/<?php echo e($order->id); ?>/cancel`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка при отмене заказа');
        });
    }
}

function completeOrder() {
    if (confirm('Отметить заказ как выполненный?')) {
        fetch(`/orders/<?php echo e($order->id); ?>/complete`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка при выполнении заказа');
        });
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'admin-alert-success' : 'admin-alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    
    const alertHTML = `
        <div class="admin-alert ${alertClass}">
            <i class="${iconClass} admin-me-2"></i>
            ${message}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    const container = document.querySelector('.admin-container');
    container.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        const alert = container.querySelector('.admin-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/orders/show.blade.php ENDPATH**/ ?>