<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php if(session('success')): ?>
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-home admin-me-1"></i>
            Мои боты
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('bot.products.index', $telegramBot)); ?>">
            <i class="fas fa-box admin-me-1"></i>
            Товары
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
            <i class="fas fa-tags admin-me-1"></i>
            Категории
        </a>
    </div>

    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <div class="admin-d-flex admin-align-items-center">
                    <div class="admin-me-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-robot" style="font-size: 24px; color: white;"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="admin-mb-1"><?php echo e($telegramBot->bot_name); ?></h5>
                        <p class="admin-text-muted admin-mb-0">
                            <i class="fas fa-at admin-me-1"></i><?php echo e($telegramBot->bot_username); ?>

                        </p>
                        <small class="admin-text-muted">Управление категориями товаров</small>
                    </div>
                </div>
                <div>
                    <a href="<?php echo e(route('bot.categories.create', $telegramBot)); ?>" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создать категорию
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Список категорий -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="fas fa-tags admin-me-2"></i>
                Категории товаров
                <span class="admin-badge admin-badge-secondary admin-ms-2"><?php echo e($categories->total()); ?></span>
            </h3>
        </div>

        <div class="admin-card-body">
            <?php if($categories->count() > 0): ?>
                <div class="admin-row">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="admin-col admin-col-12 admin-col-md-6 admin-col-lg-4 admin-mb-4">
                        <div class="admin-card category-card" style="height: 100%;">
                            <div class="admin-card-body">
                                <div class="admin-d-flex admin-align-items-start admin-mb-3">
                                    <?php if($category->photo_url): ?>
                                        <img src="<?php echo e(asset('storage/' . ltrim($category->photo_url, '/'))); ?>" 
                                             class="admin-me-3" 
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0;"
                                             alt="<?php echo e($category->name); ?>"
                                             onerror="this.src='<?php echo e(asset('images/no-image.png')); ?>'; this.style.borderColor='#cbd5e0';">
                                    <?php else: ?>
                                        <div class="admin-me-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px solid #e2e8f0;">
                                            <i class="fas fa-folder" style="font-size: 32px; color: #cbd5e0;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1;">
                                        <h5 class="admin-mb-1"><?php echo e($category->name); ?></h5>
                                        <?php if($category->description): ?>
                                            <p class="admin-text-muted admin-mb-2" style="font-size: 0.875rem;"><?php echo e(Str::limit($category->description, 80)); ?></p>
                                        <?php endif; ?>
                                        <div class="admin-d-flex admin-align-items-center admin-text-muted" style="font-size: 0.875rem;">
                                            <i class="fas fa-box admin-me-1"></i>
                                            <span><?php echo e($category->activeProducts->count()); ?> товаров</span>
                                            <?php if(!$category->is_active): ?>
                                                <span class="admin-badge admin-badge-secondary admin-ms-2">Неактивна</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="admin-d-flex" style="gap: 8px;">
                                    <a href="<?php echo e(route('bot.categories.show', [$telegramBot, $category])); ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-outline-primary" style="flex: 1;">
                                        <i class="fas fa-eye admin-me-1"></i>
                                        Просмотр
                                    </a>
                                    <a href="<?php echo e(route('bot.categories.edit', [$telegramBot, $category])); ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="admin-btn admin-btn-sm admin-btn-outline-danger" 
                                            onclick="deleteCategory(<?php echo e($category->id); ?>, '<?php echo e(addslashes($category->name)); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Пагинация -->
                <?php if($categories->hasPages()): ?>
                <div class="admin-pagination admin-mt-4">
                    <?php if($categories->onFirstPage()): ?>
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($categories->previousPageUrl()); ?>" class="admin-page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php $__currentLoopData = $categories->getUrlRange(1, $categories->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $categories->currentPage()): ?>
                            <span class="admin-page-link active"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" class="admin-page-link"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if($categories->hasMorePages()): ?>
                        <a href="<?php echo e($categories->nextPageUrl()); ?>" class="admin-page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 1rem;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tags" style="font-size: 32px; color: #cbd5e0;"></i>
                    </div>
                    <h4 class="admin-text-muted admin-mb-3">Категории не созданы</h4>
                    <p class="admin-text-muted admin-mb-4">
                        Создайте первую категорию для организации товаров в вашем магазине.<br>
                        Категории помогают покупателям легче находить нужные товары.
                    </p>
                   
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" class="admin-d-none">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
<?php $__env->stopSection(); ?>


<style>
.category-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.admin-alert {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
function deleteCategory(categoryId, categoryName) {
    if (confirm(`Вы уверены, что хотите удалить категорию "${categoryName}"?\n\nВнимание: Если в категории есть товары, удаление будет невозможно.`)) {
        const form = document.getElementById('delete-form');
        form.action = `<?php echo e(route('bot.categories.index', $telegramBot)); ?>/${categoryId}`;
        form.submit();
    }
}

// Автоматическое скрытие алертов
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/categories/index.blade.php ENDPATH**/ ?>