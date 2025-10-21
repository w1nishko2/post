

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
        <a class="admin-nav-pill" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('orders.index')); ?>">
            <i class="fas fa-shopping-cart"></i> Заказы
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('statistics.index')); ?>">
            <i class="fas fa-chart-line"></i> Статистика
        </a>
    </div>

    <!-- Фильтры -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-header">
            <h5 class="admin-mb-0">
                <i class="fas fa-filter admin-me-2"></i>
                <span class="admin-d-none-xs">Фильтры и период анализа</span>
                <span class="admin-d-block-xs">Фильтры</span>
            </h5>
        </div>
        <div class="admin-card-body">
            <?php if($errors->any()): ?>
                <div class="admin-alert admin-alert-danger admin-mb-3">
                    <strong>Ошибки:</strong>
                    <ul class="admin-mb-0 admin-mt-2">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="GET" action="<?php echo e(route('statistics.index')); ?>">
                <div class="admin-filters-row">
                    <!-- Телеграм бот -->
                    <div class="admin-filter-group">
                        <label for="bot_id" class="admin-form-label">
                            <span class="admin-d-none-xs">Телеграм бот</span>
                            <span class="admin-d-block-xs">Бот</span>
                        </label>
                        <select class="admin-form-control admin-select" id="bot_id" name="bot_id">
                            <option value="">Все боты</option>
                            <?php $__currentLoopData = $userBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($bot->id); ?>" <?php echo e($botId == $bot->id ? 'selected' : ''); ?>>
                                    <?php echo e($bot->bot_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Период -->
                    <div class="admin-filter-group">
                        <label for="period" class="admin-form-label">Период</label>
                        <select class="admin-form-control admin-select" id="period" name="period">
                            <option value="today" <?php echo e($period == 'today' ? 'selected' : ''); ?>>Сегодня</option>
                            <option value="yesterday" <?php echo e($period == 'yesterday' ? 'selected' : ''); ?>>Вчера</option>
                            <option value="last_7_days" <?php echo e($period == 'last_7_days' ? 'selected' : ''); ?>>7 дней</option>
                            <option value="last_30_days" <?php echo e($period == 'last_30_days' ? 'selected' : ''); ?>>30 дней</option>
                            <option value="this_month" <?php echo e($period == 'this_month' ? 'selected' : ''); ?>>Этот месяц</option>
                            <option value="last_month" <?php echo e($period == 'last_month' ? 'selected' : ''); ?>>Прошлый месяц</option>
                            <option value="custom" <?php echo e($period == 'custom' ? 'selected' : ''); ?>>Произвольный</option>
                        </select>
                    </div>
                    
                    <!-- Диапазон дат (для произвольного периода) -->
                    <div class="admin-date-range-group" id="date-range-col" style="<?php echo e($period != 'custom' ? 'display: none;' : ''); ?>">
                        <div class="admin-filter-group">
                            <label for="start_date" class="admin-form-label required">Начало</label>
                            <input type="date" 
                                   class="admin-form-control <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="<?php echo e(old('start_date', $startDate)); ?>"
                                   max="<?php echo e(date('Y-m-d')); ?>">
                            <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-filter-group">
                            <label for="end_date" class="admin-form-label required">Конец</label>
                            <input type="date" 
                                   class="admin-form-control <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="<?php echo e(old('end_date', $endDate)); ?>"
                                   max="<?php echo e(date('Y-m-d')); ?>">
                            <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <!-- Действия -->
                    <div class="admin-filter-actions">
                        <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-filter admin-me-1"></i>
                                <span class="admin-d-none-xs">Применить</span>
                            </button>
                            <a href="<?php echo e(route('statistics.index')); ?>" class="admin-btn admin-btn-sm">
                                <i class="fas fa-times admin-me-1"></i>
                                <span class="admin-d-none-xs">Сбросить</span>
                            </a>
                            <button type="button" class="admin-btn admin-btn-success admin-btn-sm" id="generateReportBtn">
                                <i class="fas fa-file-pdf admin-me-1"></i>
                                <span class="admin-d-none-xs">Отчет</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-6 admin-col-md-3">
            <div class="admin-card admin-stats-card admin-stats-primary">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number"><?php echo e(number_format($generalStats['total_visits'] ?? 0)); ?></h3>
                            <div class="admin-stats-label">
                                <span class="admin-d-none-xs">Всего посещений</span>
                                <span class="admin-d-block-xs">Посещений</span>
                            </div>
                        </div>
                        <div class="admin-stats-icon admin-d-none-xs">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-6 admin-col-md-3">
            <div class="admin-card admin-stats-card admin-stats-success">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number"><?php echo e(number_format($generalStats['unique_visitors'] ?? 0)); ?></h3>
                            <div class="admin-stats-label">
                                <span class="admin-d-none-xs">Уникальные посетители</span>
                                <span class="admin-d-block-xs">Уникальные</span>
                            </div>
                        </div>
                        <div class="admin-stats-icon admin-d-none-xs">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-6 admin-col-md-3">
            <div class="admin-card admin-stats-card admin-stats-info">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number"><?php echo e(number_format($generalStats['completed_orders'] ?? 0)); ?></h3>
                            <div class="admin-stats-label">Заказы</div>
                            <div class="admin-stats-sub admin-d-none-xs">из <?php echo e(number_format($generalStats['total_orders'] ?? 0)); ?> всего</div>
                        </div>
                        <div class="admin-stats-icon admin-d-none-xs">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-6 admin-col-md-3">
            <div class="admin-card admin-stats-card admin-stats-warning">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number"><?php echo e(number_format($generalStats['total_revenue'] ?? 0, 0, ',', ' ')); ?> ₽</h3>
                            <div class="admin-stats-label">Выручка</div>
                            <?php if(($generalStats['completed_orders'] ?? 0) > 0): ?>
                                <div class="admin-stats-sub admin-d-none-xs">Средний чек: <?php echo e(number_format($generalStats['average_order_value'] ?? 0, 0, ',', ' ')); ?> ₽</div>
                            <?php endif; ?>
                        </div>
                        <div class="admin-stats-icon admin-d-none-xs">
                            <i class="fas fa-ruble-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительные метрики -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-body">
                    <h6 class="admin-mb-3">
                        <i class="fas fa-percentage admin-me-2"></i>
                        Конверсия
                    </h6>
                    <div class="admin-d-flex admin-align-items-center admin-mb-2">
                        <h3 class="admin-mb-0 admin-me-3"><?php echo e($generalStats['conversion_rate'] ?? 0); ?>%</h3>
                        <div class="admin-progress admin-flex-1">
                            <div class="admin-progress-bar" style="width: <?php echo e(min($generalStats['conversion_rate'] ?? 0, 100)); ?>%"></div>
                        </div>
                    </div>
                    <div class="admin-text-muted admin-small">
                        Из <?php echo e(number_format($generalStats['unique_visitors'] ?? 0)); ?> посетителей <?php echo e(number_format($generalStats['completed_orders'] ?? 0)); ?> совершили покупку
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-body">
                    <h6 class="admin-mb-3">
                        <i class="fas fa-clock admin-me-2"></i>
                        Время обработки заказов
                    </h6>
                    <h3 class="admin-mb-2"><?php echo e($orderStats['average_processing_time'] ?? 0); ?> ч</h3>
                    <div class="admin-text-muted admin-small">Среднее время от создания до выполнения заказа</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-12 admin-col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-nav-pills admin-nav-pills-sm">
                        <button class="admin-nav-pill admin-nav-pill-sm active" data-chart="visits">
                            <span class="admin-d-none-xs">Посещения</span>
                            <span class="admin-d-block-xs">Визиты</span>
                        </button>
                        <button class="admin-nav-pill admin-nav-pill-sm" data-chart="orders">Заказы</button>
                        <button class="admin-nav-pill admin-nav-pill-sm" data-chart="revenue">
                            <span class="admin-d-none-xs">Выручка</span>
                            <span class="admin-d-block-xs">₽</span>
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="admin-chart-container">
                        <canvas id="mainChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-12 admin-col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <span class="admin-d-none-xs">Источники трафика</span>
                        <span class="admin-d-block-xs">Трафик</span>
                    </h6>
                </div>
                <div class="admin-card-body">
                    <div class="admin-chart-container">
                        <canvas id="trafficChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Детальная статистика -->
    <div class="admin-row">
        <div class="admin-col admin-col-12 admin-col-lg-6 admin-mb-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-fire admin-me-2"></i>
                        <span class="admin-d-none-xs">Популярные товары</span>
                        <span class="admin-d-block-xs">Популярные</span>
                    </h6>
                </div>
                <div class="admin-card-body">
                    <?php if(isset($productStats['popular_products']) && $productStats['popular_products']->count() > 0): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th class="admin-d-none-xs">Просмотры</th>
                                        <th class="admin-d-block-xs">Всего</th>
                                        <th class="admin-d-none-xs">Уникальные</th>
                                        <th class="admin-d-block-xs">Ун.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $productStats['popular_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="admin-d-flex admin-align-items-center">
                                                    <?php if(isset($item['product']) && $item['product']->photo_url): ?>
                                                        <img src="<?php echo e($item['product']->photo_url); ?>" alt="<?php echo e($item['product']->name); ?>" 
                                                             class="admin-me-2 admin-d-none-xs" style="width: 32px; height: 32px; object-fit: cover; border-radius: var(--radius-sm);">
                                                    <?php endif; ?>
                                                    <span><?php echo e(isset($item['product']) ? Str::limit($item['product']->name, 20) : 'Неизвестный товар'); ?></span>
                                                </div>
                                            </td>
                                            <td><strong><?php echo e($item['views'] ?? 0); ?></strong></td>
                                            <td><?php echo e($item['unique_views'] ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <div class="admin-empty-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <p class="admin-text-muted">Нет данных о просмотрах товаров</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-12 admin-col-lg-6 admin-mb-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-trophy admin-me-2"></i>
                        <span class="admin-d-none-xs">Самые покупаемые товары</span>
                        <span class="admin-d-block-xs">Покупаемые</span>
                    </h6>
                </div>
                <div class="admin-card-body">
                    <?php if(isset($productStats['best_selling_products']) && $productStats['best_selling_products']->count() > 0): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th class="admin-d-none-xs">Продано</th>
                                        <th class="admin-d-block-xs">Шт.</th>
                                        <th>Выручка</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $productStats['best_selling_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(Str::limit($product->name, 20)); ?></td>
                                            <td><strong><?php echo e($product->total_sold); ?></strong></td>
                                            <td>
                                                <span class="admin-d-none-xs"><?php echo e(number_format($product->total_revenue, 0, ',', ' ')); ?> ₽</span>
                                                <span class="admin-d-block-xs"><?php echo e(number_format($product->total_revenue / 1000, 0)); ?>к</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <div class="admin-empty-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <p class="admin-text-muted">Нет данных о продажах</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение периода
    const periodSelect = document.getElementById('period');
    const dateRangeCol = document.getElementById('date-range-col');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    function toggleCustomDateFields() {
        if (periodSelect.value === 'custom') {
            dateRangeCol.style.display = 'flex';
            startDateInput.required = true;
            endDateInput.required = true;
        } else {
            dateRangeCol.style.display = 'none';
            startDateInput.required = false;
            endDateInput.required = false;
        }
    }
    
    toggleCustomDateFields();
    periodSelect.addEventListener('change', toggleCustomDateFields);

    // Переключение графиков
    const chartButtons = document.querySelectorAll('[data-chart]');
    const mainChart = document.getElementById('mainChart').getContext('2d');
    let currentChart = null;

    function loadChart(type) {
        if (currentChart) {
            currentChart.destroy();
        }

        // Обновляем активную кнопку
        chartButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-chart="${type}"]`).classList.add('active');

        // Загружаем данные
        fetch(`<?php echo e(route('statistics.chart-data')); ?>?bot_id=<?php echo e($botId); ?>&period=<?php echo e($period); ?>&start_date=<?php echo e($startDate); ?>&end_date=<?php echo e($endDate); ?>&chart_type=${type}`)
            .then(response => response.json())
            .then(data => {
                const config = {
                    type: type === 'orders' ? 'bar' : 'line',
                    data: {
                        labels: data.labels || [],
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };

                if (type === 'visits') {
                    config.data.datasets = [
                        {
                            label: 'Посещения',
                            data: data.visits || [],
                            borderColor: 'var(--color-primary)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Уникальные',
                            data: data.unique_visitors || [],
                            borderColor: 'var(--color-success)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            tension: 0.4
                        }
                    ];
                } else if (type === 'orders') {
                    config.data.datasets = [{
                        label: 'Заказы',
                        data: data.orders || [],
                        backgroundColor: 'var(--color-info)',
                        borderColor: 'var(--color-info)'
                    }];
                } else if (type === 'revenue') {
                    config.data.datasets = [{
                        label: 'Выручка (₽)',
                        data: data.revenue || [],
                        borderColor: 'var(--color-warning)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true,
                        tension: 0.4
                    }];
                }

                currentChart = new Chart(mainChart, config);
            })
            .catch(error => console.error('Ошибка загрузки графика:', error));
    }

    // Инициализация графиков
    loadChart('visits');

    chartButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            loadChart(this.dataset.chart);
        });
    });

    // График источников трафика
    const trafficCtx = document.getElementById('trafficChart').getContext('2d');
    const trafficSources = <?php echo json_encode($visitStats['traffic_sources'] ?? [], 15, 512) ?>;
    
    if (trafficSources && trafficSources.length > 0) {
        new Chart(trafficCtx, {
            type: 'doughnut',
            data: {
                labels: trafficSources.map(source => source.source),
                datasets: [{
                    data: trafficSources.map(source => source.visits),
                    backgroundColor: [
                        'var(--color-primary)',
                        'var(--color-success)', 
                        'var(--color-warning)',
                        'var(--color-info)',
                        'var(--color-danger)',
                        '#FF9F40',
                        '#FF6B6B',
                        '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Генерация отчета
    document.getElementById('generateReportBtn').addEventListener('click', function() {
        const form = this.closest('form');
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin admin-me-1"></i>Генерация...';
        this.disabled = true;
        
        const reportUrl = `<?php echo e(route('statistics.generate-report')); ?>?${params.toString()}`;
        
        fetch(reportUrl)
            .then(response => {
                if (response.ok) {
                    window.location.href = reportUrl;
                } else {
                    throw new Error('Ошибка генерации отчета');
                }
            })
            .catch(error => {
                alert('Произошла ошибка при генерации отчета');
                console.error('Error:', error);
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/statistics/index.blade.php ENDPATH**/ ?>