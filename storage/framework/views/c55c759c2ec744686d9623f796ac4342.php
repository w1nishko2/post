<?php $__env->startSection('content'); ?>
<div class="container-xl">
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
                                    <i class="fas fa-robot "></i>
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
                            <?php if($categories->hasPages()): ?>
                                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
                                    <span class="relative z-0 inline-flex rtl:flex-row-reverse shadow-sm rounded-md">
                                        <?php if($categories->onFirstPage()): ?>
                                            <span aria-disabled="true" aria-label="&laquo; Previous">
                                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5 dark:bg-gray-800 dark:border-gray-600" aria-hidden="true">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </span>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?php echo e($categories->previousPageUrl()); ?>" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="&laquo; Previous">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php $__currentLoopData = $categories->getUrlRange(1, $categories->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($page == $categories->currentPage()): ?>
                                                <span aria-current="page">
                                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600"><?php echo e($page); ?></span>
                                                </span>
                                            <?php else: ?>
                                                <a href="<?php echo e($url); ?>" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="Go to page <?php echo e($page); ?>">
                                                    <?php echo e($page); ?>

                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php if($categories->hasMorePages()): ?>
                                            <a href="<?php echo e($categories->nextPageUrl()); ?>" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="Next &raquo;">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <span aria-disabled="true" aria-label="Next &raquo;">
                                                <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5 dark:bg-gray-800 dark:border-gray-600" aria-hidden="true">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </nav>
                            <?php endif; ?>
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/categories/index.blade.php ENDPATH**/ ?>