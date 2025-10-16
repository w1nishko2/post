

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

            <?php if(session('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?php echo e(session('warning')); ?>

                    <?php if(session('import_errors')): ?>
                        <hr>
                        <small>
                            <strong>Детали ошибок:</strong><br>
                            <?php echo nl2br(e(session('import_errors'))); ?>

                        </small>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

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
                            <i class="fas fa-boxes me-2"></i>Мои товары
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Информация о боте -->
            <?php if(isset($telegramBot)): ?>
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
                                <small class="text-muted">Управление товарами магазина</small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo e(route('products.select-bot')); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Выбрать другой магазин
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Блок для управления категориями -->
            <?php if(isset($telegramBot)): ?>
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 20%); border-bottom: 2px solid #f59e0b;">
                    <h6 class="mb-0" style="color: #92400e; font-weight: 700;">
                        <i class="fas fa-folder me-2"></i> Управление категориями
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-2 text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Создавайте категории для организации товаров в магазине. Категории помогают покупателям легче находить нужные товары.
                            </p>
                            <small class="text-muted">
                                Товары можно привязывать к категориям при создании/редактировании или через импорт Excel.
                            </small>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="btn btn-warning btn-sm me-2">
                                <i class="fas fa-list"></i> Все категории
                            </a>
                            <a href="<?php echo e(route('bot.categories.create', $telegramBot)); ?>" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-plus"></i> Новая категория
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Блок для импорта/экспорта товаров -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 20%); border-bottom: 2px solid #10b981;">
                    <h6 class="mb-0" style="color: #065f46; font-weight: 700;">
                        <i class="fas fa-file-excel me-2"></i> Массовое управление товарами
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Скачать шаблон -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-download"></i> Скачать шаблон
                                </h6>
                                <p class="text-muted small mb-3">
                                    Скачайте Excel шаблон с примерами заполнения для массового добавления товаров.
                                </p>
                                <?php if(isset($telegramBot)): ?>
                                    <a href="<?php echo e(route('bot.products.download-template', $telegramBot)); ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-download"></i> Скачать template_products.xlsx
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Выберите бота для скачивания шаблона</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Загрузить файл -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-info mb-2">
                                    <i class="fas fa-upload"></i> Загрузить товары
                                </h6>
                                <p class="text-muted small mb-3">
                                    Заполните шаблон и загрузите файл для массового добавления товаров.
                                </p>
                                <?php if(isset($telegramBot)): ?>
                                    <form action="<?php echo e(route('bot.products.import', $telegramBot)); ?>" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="file" name="excel_file" class="form-control form-control-sm" 
                                               accept=".xlsx,.xls,.csv" required>
                                        <button type="submit" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-file-upload"></i> Импортировать
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Выберите бота для импорта</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Контент товаров -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Управление товарами</h5>
                    <?php if(isset($telegramBot)): ?>
                        <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Добавить товар
                        </a>
                    <?php else: ?>
                        <span class="text-muted">Выберите бота для добавления товаров</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if($products->count() > 0): ?>
                        <div class="row">
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 product-card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-truncate"><?php echo e($product->name); ?></h6>
                                            <?php if(isset($telegramBot)): ?>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" class="btn btn-outline-primary btn-sm" 
                                                       title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" class="btn btn-outline-info btn-sm" 
                                                       title="Подробнее">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form method="POST" action="<?php echo e(route('bot.products.destroy', [$telegramBot, $product])); ?>" 
                                                          class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить товар <?php echo e($product->name); ?>?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Артикул:</strong> 
                                                <code><?php echo e($product->article); ?></code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Бренд:</strong> <?php echo e($product->brand); ?>

                                            </div>
                                            <div class="mb-2">
                                                <strong>Цена:</strong>
                                                <span class="text-success fw-bold"><?php echo e($product->formatted_price); ?></span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Количество:</strong>
                                                <span class="badge bg-<?php echo e($product->quantity > 5 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger')); ?>">
                                                    <?php echo e($product->quantity); ?> шт.
                                                </span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Статус:</strong>
                                                <?php
                                                    $status = $product->availability_status;
                                                    $statusClass = 'secondary';
                                                    if($status === 'В наличии') $statusClass = 'success';
                                                    elseif($status === 'Заканчивается') $statusClass = 'warning';
                                                    elseif($status === 'Нет в наличии') $statusClass = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e($status); ?></span>
                                            </div>
                                            <?php if($product->description): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted"><?php echo e(Str::limit($product->description, 80)); ?></small>
                                                </div>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                Добавлен: <?php echo e($product->created_at->format('d.m.Y')); ?>

                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        
                        <!-- Пагинация -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Показано <?php echo e($products->count()); ?> из <?php echo e($products->total()); ?> товаров
                            </div>
                            <div>
                                <?php echo e($products->links()); ?>

                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <?php if(isset($telegramBot)): ?>
                                <h5>В этом магазине пока нет товаров</h5>
                                <p class="text-muted">Добавьте первый товар для управления ассортиментом</p>
                                <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Добавить первый товар
                                </a>
                            <?php else: ?>
                                <h5>Выберите магазин для управления товарами</h5>
                                <p class="text-muted">Сначала выберите бота (магазин) для работы с товарами</p>
                                <a href="<?php echo e(route('products.select-bot')); ?>" class="btn btn-primary">
                                    <i class="fas fa-robot"></i> Выбрать магазин
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\products\index.blade.php ENDPATH**/ ?>