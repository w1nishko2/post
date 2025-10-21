

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
        <a class="admin-nav-pill active" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <?php if(isset($telegramBot)): ?>
    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between">
                <div class="admin-d-flex admin-align-items-center">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar <?php echo e($telegramBot->is_active ? '' : 'inactive'); ?>">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="admin-mb-1"><?php echo e($telegramBot->bot_name); ?></h6>
                        <div class="admin-text-muted">{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div>
                    <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn admin-btn-sm">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        К списку товаров
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-row">
        <div class="admin-col admin-col-6">
            <!-- Фотография товара -->
            <div class="admin-card admin-mb-4">
                <div class="admin-card-body admin-text-center">
                    <?php if($product->photo_url): ?>
                        <img src="<?php echo e($product->photo_url); ?>" alt="<?php echo e($product->name); ?>" 
                             style="width: 100%; max-height: 400px; object-fit: contain; border-radius: var(--radius-md);">
                    <?php else: ?>
                        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background-color: var(--color-light-gray); border-radius: var(--radius-md); color: var(--color-gray);">
                            <div class="admin-text-center">
                                <i class="fas fa-image" style="font-size: 48px; margin-bottom: 16px;"></i>
                                <div>Изображение отсутствует</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-col admin-col-6">
            <!-- Информация о товаре -->
            <div class="admin-card">
                <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
                    <h5 class="admin-mb-0"><?php echo e($product->name); ?></h5>
                    <div class="admin-d-flex admin-gap-sm">
                        <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                           class="admin-btn admin-btn-sm">
                            <i class="fas fa-edit admin-me-1"></i>
                            Редактировать
                        </a>
                        <button class="admin-btn admin-btn-sm admin-btn-outline-danger" onclick="deleteProduct()">
                            <i class="fas fa-trash admin-me-1"></i>
                            Удалить
                        </button>
                    </div>
                </div>
                
                <div class="admin-card-body">
                    <div class="admin-row admin-mb-4">
                        <div class="admin-col admin-col-6">
                            <div class="admin-form-label">Цена:</div>
                            <div class="admin-fw-bold" style="font-size: 24px; color: var(--color-success);">
                                <?php echo e(number_format($product->price, 0, ',', ' ')); ?> ₽
                            </div>
                        </div>
                        <div class="admin-col admin-col-6">
                            <div class="admin-form-label">Количество:</div>
                            <div class="admin-fw-bold">
                                <?php if($product->quantity > 0): ?>
                                    <span class="admin-badge admin-badge-success"><?php echo e($product->quantity); ?> шт</span>
                                <?php else: ?>
                                    <span class="admin-badge admin-badge-danger">Нет в наличии</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($product->description): ?>
                        <div class="admin-form-group">
                            <div class="admin-form-label">Описание:</div>
                            <div style="padding: 16px; background-color: var(--color-light-gray); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                                <?php echo e($product->description); ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="admin-row">
                        <?php if($product->article): ?>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Артикул:</div>
                                <div class="admin-fw-bold"><?php echo e($product->article); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if($product->category): ?>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Категория:</div>
                                <div class="admin-fw-bold"><?php echo e($product->category->name); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if($product->markup_percentage > 0): ?>
                        <div class="admin-row admin-mb-3">
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Наценка:</div>
                                <div class="admin-fw-bold"><?php echo e($product->markup_percentage); ?>%</div>
                            </div>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Базовая цена:</div>
                                <div class="admin-fw-bold">
                                    <?php echo e(number_format($product->price / (1 + $product->markup_percentage / 100), 0, ',', ' ')); ?> ₽
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                        <div>
                            <div class="admin-form-label">Статус:</div>
                            <?php if($product->is_active): ?>
                                <span class="admin-badge admin-badge-success">Активен</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge-warning">Неактивен</span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-text-muted admin-text-right">
                            <small>
                                Создан: <?php echo e($product->created_at->format('d.m.Y H:i')); ?><br>
                                Обновлен: <?php echo e($product->updated_at->format('d.m.Y H:i')); ?>

                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($telegramBot)): ?>
    <!-- Скрытая форма для удаления -->
    <form id="delete-form" method="POST" action="<?php echo e(route('bot.products.destroy', [$telegramBot, $product])); ?>" class="admin-d-none">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
    </form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция удаления товара
    window.deleteProduct = function() {
        if (confirm('Вы уверены, что хотите удалить товар "<?php echo e($product->name); ?>"?\n\nЭто действие нельзя отменить!')) {
            document.getElementById('delete-form').submit();
        }
    };
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/show.blade.php ENDPATH**/ ?>