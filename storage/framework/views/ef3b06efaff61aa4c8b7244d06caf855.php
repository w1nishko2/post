<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
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

            <!-- Навигация -->
            <div class="card mb-4">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('orders.index')); ?>">Заказы</a></li>
                            <li class="breadcrumb-item active"><?php echo e($order->order_number); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Информация о заказе -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Заказ <?php echo e($order->order_number); ?>

                    </h5>
                    <div>
                        <span class="<?php echo e($order->status_class); ?>"><?php echo e($order->status_label); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-calendar me-2"></i>Дата создания</h6>
                            <p class="mb-3"><?php echo e($order->created_at->format('d.m.Y H:i:s')); ?></p>

                            <h6><i class="fas fa-user me-2"></i>Информация о клиенте</h6>
                            <?php if($order->customer_name): ?>
                                <p class="mb-1"><strong>Имя:</strong> <?php echo e($order->customer_name); ?></p>
                            <?php endif; ?>
                            <?php if($order->customer_phone): ?>
                                <p class="mb-1"><strong>Телефон:</strong> <?php echo e($order->customer_phone); ?></p>
                            <?php endif; ?>
                            <?php if($order->customer_email): ?>
                                <p class="mb-1"><strong>Email:</strong> <?php echo e($order->customer_email); ?></p>
                            <?php endif; ?>
                            <?php if($order->customer_address): ?>
                                <p class="mb-1"><strong>Адрес:</strong> <?php echo e($order->customer_address); ?></p>
                            <?php endif; ?>
                            <p class="mb-3"><strong>Telegram ID:</strong> <?php echo e($order->telegram_chat_id); ?></p>

                            <?php if($order->notes): ?>
                                <h6><i class="fas fa-comment-dots me-2"></i>Комментарий</h6>
                                <p class="mb-3"><?php echo e($order->notes); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-robot me-2"></i>Telegram бот</h6>
                            <?php if($order->telegramBot): ?>
                                <p class="mb-3"><?php echo e($order->telegramBot->bot_name); ?></p>
                            <?php else: ?>
                                <p class="mb-3 text-muted">Не указан</p>
                            <?php endif; ?>

                            <h6><i class="fas fa-dollar-sign me-2"></i>Сумма заказа</h6>
                            <p class="mb-3"><strong class="h4 text-success"><?php echo e($order->formatted_total); ?></strong></p>

                            <h6><i class="fas fa-boxes me-2"></i>Количество товаров</h6>
                            <p class="mb-3"><?php echo e($order->total_items); ?> шт.</p>
                        </div>
                    </div>

                    <!-- Действия с заказом -->
                    <?php if($order->canBeCancelled()): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Внимание!</strong> При отмене заказа товары будут возвращены на склад.
                                </div>
                                <button type="button" class="btn btn-danger" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                    <i class="fas fa-times me-2"></i>Отменить заказ
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Товары в заказе -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Товары в заказе
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Фото</th>
                                    <th>Товар</th>
                                    <th>Артикул</th>
                                    <th>Цена</th>
                                    <th>Количество</th>
                                    <th>Сумма</th>
                                    <th>Статус товара</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php if($item->product_photo_url): ?>
                                                <img src="<?php echo e($item->product_photo_url); ?>" alt="<?php echo e($item->product_name); ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($item->product_name); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($item->product_article): ?>
                                                <code><?php echo e($item->product_article); ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($item->formatted_price); ?></td>
                                        <td><?php echo e($item->quantity); ?> шт.</td>
                                        <td><strong><?php echo e($item->formatted_total_price); ?></strong></td>
                                        <td>
                                            <?php if($item->product && $item->product->is_active): ?>
                                                <span class="badge bg-success">Доступен</span>
                                            <?php elseif($item->product): ?>
                                                <span class="badge bg-warning">Неактивен</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Удален</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="5" class="text-end"><strong>Итого:</strong></td>
                                    <td><strong><?php echo e($order->formatted_total); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
                // Обновить страницу через 2 секунды
                setTimeout(() => {
                    location.reload();
                }, 2000);
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
    
    const container = document.querySelector('.container .row .col-md-10');
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\orders\show.blade.php ENDPATH**/ ?>