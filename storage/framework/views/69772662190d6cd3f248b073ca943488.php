<?php $__env->startSection('content'); ?>
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Навигационная панель -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill" style="padding: 0.5rem;">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>" 
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-robot me-2"></i>Мои боты
                        </a>
                        <a class="nav-link active" href="<?php echo e(route('products.select-bot')); ?>"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-boxes me-2"></i>Мои магазины
                        </a>
                    </nav>
                </div>
            </div>

            <div class="card shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-bottom: 2px solid #e2e8f0; padding: 1.5rem;">
                    <h5 class="mb-0" style="color: #1e293b; font-weight: 700;">
                        <i class="fas fa-store text-primary me-2"></i>Выберите магазин (бота) для управления товарами
                    </h5>
                </div>

                <div class="card-body">
                    <?php if($bots->count() > 0): ?>
                        <div class="row">
                            <?php $__currentLoopData = $bots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 bot-card">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    <?php if($bot->is_active): ?>
                                                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-robot text-white"></i>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-robot text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?php echo e($bot->bot_name); ?></h6>
                                                    <small class="text-muted">{{ $bot->bot_username }}</small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Товаров:</span>
                                                    <span class="badge bg-primary"><?php echo e($bot->products_count ?? $bot->products()->count()); ?></span>
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                <a href="<?php echo e(route('bot.products.index', $bot)); ?>" class="btn btn-primary w-100">
                                                    <i class="fas fa-boxes"></i> Управлять товарами
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                            <h5>У вас пока нет ботов</h5>
                            <p class="text-muted">Создайте первого бота, чтобы начать управлять товарами</p>
                            <a href="<?php echo e(route('telegram-bots.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Создать бота
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.bot-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.bot-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/select-bot.blade.php ENDPATH**/ ?>