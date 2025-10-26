<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i>
            Мои боты
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('bot.products.index', $telegramBot)); ?>">
            <i class="fas fa-box"></i>
            Товары
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
            <i class="fas fa-folder"></i>
            Категории
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="admin-alert admin-alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Информация о категории -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-md-8">
            <div class="admin-card" style="height: 100%;">
                <div class="admin-card-body">
                    <div class="admin-d-flex" style="align-items: flex-start;">
                        <?php if($category->photo_url): ?>
                            <img src="<?php echo e(asset('storage/' . ltrim($category->photo_url, '/'))); ?>" 
                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-md); margin-right: var(--space-lg);"
                                 alt="<?php echo e($category->name); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="width: 120px; height: 120px; border-radius: var(--radius-md); margin-right: var(--space-lg); background-color: var(--color-warning); display: none; align-items: center; justify-content: center; font-size: 40px; color: white;">
                                <i class="fas fa-folder"></i>
                            </div>
                        <?php else: ?>
                            <div style="width: 120px; height: 120px; border-radius: var(--radius-md); margin-right: var(--space-lg); background-color: var(--color-warning); display: flex; align-items: center; justify-content: center; font-size: 40px; color: white;">
                                <i class="fas fa-folder"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex-grow: 1;">
                            <div class="admin-d-flex admin-align-items-center admin-mb-2">
                                <h3 class="admin-mb-0" style="margin-right: var(--space-lg);"><?php echo e($category->name); ?></h3>
                                <?php if($category->is_active): ?>
                                    <span class="admin-badge admin-badge-success">Активна</span>
                                <?php else: ?>
                                    <span class="admin-badge admin-badge-secondary">Не активна</span>
                                <?php endif; ?>
                            </div>

                            <?php if($category->description): ?>
                                <p class="admin-text-muted admin-mb-3"><?php echo e($category->description); ?></p>
                            <?php endif; ?>

                            <div class="admin-row" style="text-align: center;">
                                <div class="admin-col admin-col-4">
                                    <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-sm);">
                                        <h5 class="admin-text-primary admin-mb-1"><?php echo e($category->products->count()); ?></h5>
                                        <small class="admin-text-muted">Всего товаров</small>
                                    </div>
                                </div>
                                <div class="admin-col admin-col-4">
                                    <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-sm);">
                                        <h5 class="admin-text-success admin-mb-1"><?php echo e($category->activeProducts->count()); ?></h5>
                                        <small class="admin-text-muted">Активных товаров</small>
                                    </div>
                                </div>
                                <div class="admin-col admin-col-4">
                                    <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-sm);">
                                        <h5 class="admin-text-info admin-mb-1"><?php echo e($category->created_at->format('d.m.Y')); ?></h5>
                                        <small class="admin-text-muted">Дата создания</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-col admin-col-md-4">
            <div class="admin-card" style="height: 100%;">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">Управление категорией</h6>
                </div>
                <div class="admin-card-body" style="display: flex; flex-direction: column;">
                    <div style="display: grid; gap: var(--space-md); flex-grow: 1;">
                        <a href="<?php echo e(route('bot.categories.edit', [$telegramBot, $category])); ?>" class="admin-btn admin-btn-primary">
                            <i class="fas fa-edit"></i>
                            Редактировать
                        </a>
                        <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="admin-btn admin-btn-outline-primary">
                            <i class="fas fa-arrow-left"></i>
                            К списку категорий
                        </a>
                        <?php if($category->products->count() == 0): ?>
                            <button class="admin-btn admin-btn-outline-danger" style="margin-top: auto;" onclick="deleteCategory()">
                                <i class="fas fa-trash"></i>
                                Удалить категорию
                            </button>
                        <?php else: ?>
                            <button class="admin-btn admin-btn-outline-secondary" style="margin-top: auto;" disabled title="Нельзя удалить категорию с товарами">
                                <i class="fas fa-ban"></i>
                                Нельзя удалить
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Товары в категории -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-box"></i>
                Товары в категории
                <span class="admin-badge admin-badge-secondary" style="margin-left: var(--space-sm);"><?php echo e($category->products->count()); ?></span>
            </h5>
            <div>
                <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>?category_id=<?php echo e($category->id); ?>" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fas fa-plus"></i>
                    Добавить товар
                </a>
            </div>
        </div>

        <div class="admin-card-body">
            <?php if($category->products->count() > 0): ?>
                <div class="admin-row">
                    <?php $__currentLoopData = $category->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="admin-col admin-col-md-6 admin-col-lg-4 admin-mb-4">
                        <div class="admin-card product-card" style="height: 100%;">
                            <div class="admin-card-body">
                                <div class="admin-d-flex admin-align-items-start admin-mb-3">
                                    <?php if($product->main_image_url): ?>
                                        <img src="<?php echo e($product->main_image_url); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-md); margin-right: var(--space-md);"
                                             alt="<?php echo e($product->name); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="width: 60px; height: 60px; border-radius: var(--radius-md); margin-right: var(--space-md); background-color: var(--color-accent); display: none; align-items: center; justify-content: center; font-size: 20px; color: white;">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; border-radius: var(--radius-md); margin-right: var(--space-md); background-color: var(--color-accent); display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="flex-grow: 1;">
                                        <h6 class="admin-mb-1"><?php echo e($product->name); ?></h6>
                                        <p class="admin-text-success admin-mb-1" style="font-weight: bold;"><?php echo e(number_format($product->price, 0, ',', ' ')); ?> ₽</p>
                                        <div class="admin-d-flex admin-align-items-center admin-text-muted" style="font-size: 0.875rem;">
                                            <span style="margin-right: var(--space-sm);">Остаток: <?php echo e($product->quantity ?? 'Неограничен'); ?></span>
                                            <?php if(!$product->is_active): ?>
                                                <span class="admin-badge admin-badge-secondary">Неактивен</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="admin-d-flex admin-gap-2">
                                    <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-outline-primary" style="flex: 1;">
                                        <i class="fas fa-eye"></i> Просмотр
                                    </a>
                                    <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: var(--space-xxl) 0;">
                    <div style="margin-bottom: var(--space-lg);">
                        <i class="fas fa-cube" style="font-size: 4rem; color: var(--color-text-muted); opacity: 0.5;"></i>
                    </div>
                    <h5 class="admin-text-muted admin-mb-3">В категории пока нет товаров</h5>
                    <p class="admin-text-muted admin-mb-4">
                        Добавьте первый товар в эту категорию или назначьте категорию существующим товарам.
                    </p>
                    <div class="admin-d-flex admin-gap-2 admin-justify-content-center">
                        <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>?category_id=<?php echo e($category->id); ?>" class="admin-btn admin-btn-success">
                            <i class="fas fa-plus"></i> Создать товар
                        </a>
                        <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn admin-btn-outline-primary">
                            <i class="fas fa-list"></i> Все товары
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" action="<?php echo e(route('bot.categories.destroy', [$telegramBot, $category])); ?>" class="admin-d-none">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
<?php $__env->stopSection(); ?>

<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

<script>
function deleteCategory() {
    if (confirm(`Вы уверены, что хотите удалить категорию "<?php echo e($category->name); ?>"?\n\nЭто действие необратимо!`)) {
        document.getElementById('delete-form').submit();
    }
}

// Автоматическое скрытие алертов
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/categories/show.blade.php ENDPATH**/ ?>