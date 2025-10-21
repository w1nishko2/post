<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-mb-0">
                <i class="fas fa-store admin-me-2"></i>
                Выберите магазин (бота) для управления товарами
            </h5>
        </div>

        <div class="admin-card-body">
            <?php if($bots->count() > 0): ?>
                <div class="admin-bot-grid">
                    <?php $__currentLoopData = $bots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="admin-bot-card">
                            <div class="admin-bot-header">
                                <div class="admin-bot-avatar <?php echo e($bot->is_active ? '' : 'inactive'); ?>">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="admin-bot-info">
                                    <h6 class="admin-mb-1"><?php echo e($bot->bot_name); ?></h6>
                                    <div class="admin-bot-username admin-text-muted">{{ $bot->bot_username }}</div>
                                </div>
                            </div>

                            <div class="admin-bot-stats admin-mb-3">
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Статус</div>
                                    <div class="admin-bot-stat-value">
                                        <?php if($bot->is_active): ?>
                                            <span class="admin-badge admin-badge-success">Активен</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-warning">Неактивен</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Товаров</div>
                                    <div class="admin-bot-stat-value"><?php echo e($bot->products_count ?? $bot->products()->count()); ?></div>
                                </div>
                            </div>

                            <div class="admin-d-flex admin-justify-content-center">
                                <a href="<?php echo e(route('bot.products.index', $bot)); ?>" class="admin-btn admin-btn-primary admin-btn-full">
                                    <i class="fas fa-boxes admin-me-2"></i>
                                    Управлять товарами
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="admin-empty-state">
                    <i class="fas fa-robot"></i>
                    <h3>У вас пока нет ботов</h3>
                    <p class="admin-mb-4">Создайте первого бота, чтобы начать управлять товарами</p>
                    <a href="<?php echo e(route('home')); ?>" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создать бота
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/select-bot.blade.php ENDPATH**/ ?>