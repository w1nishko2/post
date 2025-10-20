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
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>">
                            Мои боты
                        </a>
                        <a class="nav-link" href="<?php echo e(route('bot.products.index', $telegramBot)); ?>">
                            Товары
                        </a>
                        <a class="nav-link active" href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
                            Категории
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Информация о боте -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo e($telegramBot->bot_name); ?></h6>
                                <small class="text-muted">Управление категориями товаров</small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo e(route('bot.categories.create', $telegramBot)); ?>" class="btn btn-primary">
                                Создать категорию
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Список категорий -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Категории товаров
                        <span class="badge bg-secondary ms-2"><?php echo e($categories->total()); ?></span>
                    </h5>
                </div>

                <div class="card-body">
                    <?php if($categories->count() > 0): ?>
                        <div class="row">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 category-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if($category->photo_url): ?>
                                                <img src="<?php echo e($category->photo_url); ?>" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 50px; object-fit: cover;"
                                                     alt="<?php echo e($category->name); ?>"
                                                     onerror="this.style.display='none';">
                                            <?php endif; ?>
                                            
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1"><?php echo e($category->name); ?></h6>
                                                <?php if($category->description): ?>
                                                    <p class="card-text text-muted small mb-2"><?php echo e(Str::limit($category->description, 80)); ?></p>
                                                <?php endif; ?>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span><?php echo e($category->activeProducts->count()); ?> товаров</span>
                                                    <?php if(!$category->is_active): ?>
                                                        <span class="badge bg-secondary ms-2">Неактивна</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="<?php echo e(route('bot.categories.show', [$telegramBot, $category])); ?>" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                Просмотр
                                            </a>
                                            <a href="<?php echo e(route('bot.categories.edit', [$telegramBot, $category])); ?>" 
                                               class="btn btn-sm btn-outline-warning">
                                                Изменить
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteCategory(<?php echo e($category->id); ?>, '<?php echo e($category->name); ?>')">
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <!-- Пагинация -->
                        <div class="d-flex justify-content-center">
                            <?php echo e($categories->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h5 class="text-muted mb-3">Категории не созданы</h5>
                            <p class="text-muted mb-4">
                                Создайте первую категорию для организации товаров в вашем магазине.
                                Категории помогают покупателям легче находить нужные товары.
                            </p>
                            <a href="<?php echo e(route('bot.categories.create', $telegramBot)); ?>" class="btn btn-primary">
                                Создать первую категорию
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" class="d-none">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.category-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\categories\index.blade.php ENDPATH**/ ?>