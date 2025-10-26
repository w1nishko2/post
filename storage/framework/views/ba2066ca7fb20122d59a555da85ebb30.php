

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

    <!-- Фильтры и статистика заказов -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-filter admin-me-2"></i>
                        Фильтры заказов
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="GET" action="<?php echo e(route('orders.index')); ?>">
                        <div class="admin-row">
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="status" class="admin-form-label">Статус заказа</label>
                                    <select class="admin-form-control admin-select" id="status" name="status">
                                        <option value="">Все заказы</option>
                                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Ожидает обработки</option>
                                        <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>В обработке</option>
                                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Выполнен</option>
                                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Отменен</option>
                                    </select>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="bot_id" class="admin-form-label">Бот</label>
                                    <select class="admin-form-control admin-select" id="bot_id" name="bot_id">
                                        <option value="">Все боты</option>
                                        <?php $__currentLoopData = auth()->user()->telegramBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bot->id); ?>" <?php echo e(request('bot_id') == $bot->id ? 'selected' : ''); ?>>
                                                <?php echo e($bot->bot_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="date_from" class="admin-form-label">Дата с</label>
                                    <input type="date" class="admin-form-control" id="date_from" name="date_from" 
                                           value="<?php echo e(request('date_from')); ?>" max="<?php echo e(date('Y-m-d')); ?>">
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="date_to" class="admin-form-label">Дата до</label>
                                    <input type="date" class="admin-form-control" id="date_to" name="date_to" 
                                           value="<?php echo e(request('date_to')); ?>" max="<?php echo e(date('Y-m-d')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-filter admin-me-1"></i>
                                Применить фильтры
                            </button>
                            <?php if(request()->hasAny(['status', 'bot_id', 'date_from', 'date_to'])): ?>
                                <a href="<?php echo e(route('orders.index')); ?>" class="admin-btn admin-btn-sm">
                                    <i class="fas fa-times admin-me-1"></i>
                                    Сбросить
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-4">
            <div class="admin-card admin-stats-card">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number"><?php echo e($orders->total()); ?></h3>
                            <div class="admin-stats-label">Всего заказов</div>
                            <?php if($orders->where('status', 'completed')->count() > 0): ?>
                                <div class="admin-stats-sub">
                                    Выполнено: <?php echo e($orders->where('status', 'completed')->count()); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="admin-stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список заказов -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-list admin-me-2"></i>
                Мои заказы
            </h5>
            <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                <span class="admin-text-muted">Показать по:</span>
                <select class="admin-form-control admin-form-control-sm" id="per-page" style="width: auto;">
                    <option value="15" <?php echo e(request('per_page', 15) == 15 ? 'selected' : ''); ?>>15</option>
                    <option value="25" <?php echo e(request('per_page', 15) == 25 ? 'selected' : ''); ?>>25</option>
                    <option value="50" <?php echo e(request('per_page', 15) == 50 ? 'selected' : ''); ?>>50</option>
                </select>
            </div>
        </div>

        <div class="admin-card-body admin-p-0">
            <?php if($orders->count() > 0): ?>
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">№ Заказа</th>
                                <th style="width: 140px;">Дата</th>
                                <th>Товары</th>
                                <th style="width: 100px;">Сумма</th>
                                <th style="width: 120px;">Статус</th>
                                <th style="width: 150px;">Бот</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="admin-order-number">
                                            <strong><?php echo e($order->order_number); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-date">
                                            <div><?php echo e($order->created_at->format('d.m.Y')); ?></div>
                                            <div class="admin-text-muted admin-small"><?php echo e($order->created_at->format('H:i')); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-items">
                                            <div class="admin-mb-1">
                                                <strong><?php echo e($order->total_items); ?> шт.</strong>
                                            </div>
                                            <div class="admin-order-products">
                                                <?php $__currentLoopData = $order->items->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="admin-product-line">
                                                        <?php echo e(Str::limit($item->product_name, 30)); ?>

                                                        <span class="admin-text-muted">(<?php echo e($item->quantity); ?> шт.)</span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($order->items->count() > 2): ?>
                                                    <div class="admin-text-muted admin-small">
                                                        и ещё <?php echo e($order->items->count() - 2); ?> товаров...
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-total">
                                            <strong><?php echo e($order->formatted_total); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            <?php switch($order->status):
                                                case ('pending'): ?>
                                                    <span class="admin-badge admin-badge-warning">Ожидает</span>
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
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($order->telegramBot): ?>
                                            <div class="admin-bot-info-compact">
                                                <div class="admin-bot-name"><?php echo e(Str::limit($order->telegramBot->bot_name, 20)); ?></div>
                                                <div class="admin-text-muted admin-small">{{ $order->telegramBot->bot_username }}</div>
                                            </div>
                                        <?php else: ?>
                                            <span class="admin-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="<?php echo e(route('orders.show', $order)); ?>" 
                                               class="admin-btn admin-btn-xs" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($order->canBeCancelled()): ?>
                                                <button class="admin-btn admin-btn-xs admin-btn-danger" 
                                                        onclick="cancelOrder(<?php echo e($order->id); ?>)" 
                                                        title="Отменить">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if($order->status === 'pending'): ?>
                                                <button class="admin-btn admin-btn-xs admin-btn-success" 
                                                        onclick="completeOrder(<?php echo e($order->id); ?>)" 
                                                        title="Выполнить">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <?php if($orders->hasPages()): ?>
                    <div class="admin-card-footer">
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <div class="admin-text-muted">
                                Показаны записи <?php echo e($orders->firstItem()); ?>-<?php echo e($orders->lastItem()); ?> 
                                из <?php echo e($orders->total()); ?>

                            </div>
                            <div class="admin-pagination">
                                <?php if($orders->onFirstPage()): ?>
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e($orders->previousPageUrl()); ?>" class="admin-page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php $__currentLoopData = $orders->getUrlRange(1, $orders->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($page == $orders->currentPage()): ?>
                                        <span class="admin-page-link active"><?php echo e($page); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($url); ?>" class="admin-page-link"><?php echo e($page); ?></a>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($orders->hasMorePages()): ?>
                                    <a href="<?php echo e($orders->nextPageUrl()); ?>" class="admin-page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="admin-empty-state">
                    <div class="admin-empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h6>У вас пока нет заказов</h6>
                    <p class="admin-text-muted">Заказы, сделанные через ваши Telegram боты, будут отображаться здесь</p>
                    <a href="<?php echo e(route('products.select-bot')); ?>" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить товары
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Изменение количества записей на странице
    const perPageSelect = document.getElementById('per-page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            window.location.href = url.toString();
        });
    }

    // Валидация дат
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            if (dateToInput.value && this.value > dateToInput.value) {
                dateToInput.value = this.value;
            }
            dateToInput.min = this.value;
        });
        
        dateToInput.addEventListener('change', function() {
            if (dateFromInput.value && this.value < dateFromInput.value) {
                dateFromInput.value = this.value;
            }
            dateFromInput.max = this.value;
        });
    }
});

// Отмена заказа
function cancelOrder(orderId) {
    if (confirm('Вы уверены, что хотите отменить этот заказ?\n\nТовары будут возвращены на склад.')) {
        fetch(`/orders/${orderId}/cancel`, {
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
                location.reload();
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

// Выполнение заказа
function completeOrder(orderId) {
    if (confirm('Отметить заказ как выполненный?')) {
        fetch(`/orders/${orderId}/complete`, {
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
                location.reload();
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

// Показ уведомлений
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/orders/index.blade.php ENDPATH**/ ?>