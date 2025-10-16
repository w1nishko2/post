

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Навигационная панель -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" href="<?php echo e(route('home')); ?>">
                                <i class="fas fa-robot me-2"></i>Боты
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" href="<?php echo e(route('products.select-bot')); ?>">
                                <i class="fas fa-boxes me-2"></i>Товары
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" href="<?php echo e(route('orders.index')); ?>">
                                <i class="fas fa-shopping-cart me-2"></i>Заказы
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Контент заказов -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Мои заказы
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i>Фильтр
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo e(route('orders.index')); ?>">Все заказы</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('orders.index')); ?>?status=pending">Ожидает обработки</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('orders.index')); ?>?status=processing">В обработке</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('orders.index')); ?>?status=completed">Выполнен</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('orders.index')); ?>?status=cancelled">Отменен</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <?php if($orders->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>№ Заказа</th>
                                        <th>Дата</th>
                                        <th>Товары</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Бот</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($order->order_number); ?></strong>
                                            </td>
                                            <td>
                                                <small><?php echo e($order->created_at->format('d.m.Y H:i')); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo e($order->total_items); ?> шт.</small>
                                                <div class="small text-muted">
                                                    <?php $__currentLoopData = $order->items->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div><?php echo e($item->product_name); ?></div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($order->items->count() > 2): ?>
                                                        <div class="text-muted">и ещё <?php echo e($order->items->count() - 2); ?>...</div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo e($order->formatted_total); ?></strong>
                                            </td>
                                            <td>
                                                <span class="<?php echo e($order->status_class); ?>">
                                                    <?php echo e($order->status_label); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php if($order->telegramBot): ?>
                                                    <small class="text-muted"><?php echo e($order->telegramBot->bot_name); ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-outline-primary btn-sm" title="Просмотр">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($order->canBeCancelled()): ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo e($order->id); ?>)" title="Отменить">
                                                            <i class="fas fa-times"></i>
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
                        <div class="d-flex justify-content-center">
                            <?php echo e($orders->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">У вас пока нет заказов</h5>
                            <p class="text-muted">Заказы, сделанные через ваши Telegram боты, будут отображаться здесь</p>
                            <a href="<?php echo e(route('products.select-bot')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Добавить товары
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
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
                // Обновить статус в таблице
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

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container .row .col-md-12');
    container.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\orders\index.blade.php ENDPATH**/ ?>