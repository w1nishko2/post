@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
             
                
                @if($generalStats['total_visits'] == 0 && $generalStats['total_orders'] == 0)
                    <div class="alert alert-info alert-sm mb-0" role="alert">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Подсказка:</strong> Пока данных мало. Активность будет накапливаться по мере использования магазина.
                    </div>
                @endif
            </div>

            <!-- Фильтры -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Фильтры и период анализа
                    </h6>
                    <small class="text-muted">
                        Период: 
                        @switch($period)
                            @case('today') Сегодня @break
                            @case('yesterday') Вчера @break
                            @case('last_7_days') Последние 7 дней @break
                            @case('last_30_days') Последние 30 дней @break
                            @case('this_month') Этот месяц @break
                            @case('last_month') Прошлый месяц @break
                            @case('custom') 
                                Произвольный период
                                @if($startDate && $endDate)
                                    ({{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }})
                                @endif
                                @break
                            @default Последние 30 дней
                        @endswitch
                        @if($botId && $userBots->where('id', $botId)->first())
                            • Бот: {{ $userBots->where('id', $botId)->first()->bot_name }}
                        @elseif(!$botId)
                            • Все боты
                        @endif
                    </small>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="GET" action="{{ route('statistics.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="bot_id" class="form-label">Телеграм бот</label>
                            <select class="form-select" id="bot_id" name="bot_id">
                                <option value="">Все боты</option>
                                @foreach($userBots as $bot)
                                    <option value="{{ $bot->id }}" {{ $botId == $bot->id ? 'selected' : '' }}>
                                        {{ $bot->bot_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(config('app.debug') && auth()->user()->email === 'admin@example.com')
                                <small class="text-muted">
                                    Debug: Найдено {{ $userBots->count() }} ботов. 
                                    Выбран ID: {{ $botId ?? 'null' }}
                                </small>
                            @endif
                        </div>
                        
                        <div class="col-md-3">
                            <label for="period" class="form-label">Период</label>
                            <select class="form-select" id="period" name="period">
                                <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Сегодня</option>
                                <option value="yesterday" {{ $period == 'yesterday' ? 'selected' : '' }}>Вчера</option>
                                <option value="last_7_days" {{ $period == 'last_7_days' ? 'selected' : '' }}>Последние 7 дней</option>
                                <option value="last_30_days" {{ $period == 'last_30_days' ? 'selected' : '' }}>Последние 30 дней</option>
                                <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>Этот месяц</option>
                                <option value="last_month" {{ $period == 'last_month' ? 'selected' : '' }}>Прошлый месяц</option>
                                <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Произвольный период</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2" id="start-date-col" style="{{ $period != 'custom' ? 'display: none;' : '' }}">
                            <label for="start_date" class="form-label">Дата начала <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ old('start_date', $startDate) }}"
                                   max="{{ date('Y-m-d') }}"
                                   {{ $period == 'custom' ? 'required' : '' }}>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-2" id="end-date-col" style="{{ $period != 'custom' ? 'display: none;' : '' }}">
                            <label for="end_date" class="form-label">Дата окончания <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="{{ old('end_date', $endDate) }}"
                                   max="{{ date('Y-m-d') }}"
                                   {{ $period == 'custom' ? 'required' : '' }}>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="date-help" style="{{ $period != 'custom' ? 'display: none;' : '' }}">
                                Выберите период для анализа статистики
                            </small>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                    Применить
                                </button>
                                <button type="button" class="btn btn-success" id="generateReportBtn">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    Отчет
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Общая статистика -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card  bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Всего посещений</h5>
                                    <h2 class="mb-0">{{ number_format($generalStats['total_visits'] ?? 0) }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-eye fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card  bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Уникальные посетители</h5>
                                    <h2 class="mb-0">{{ number_format($generalStats['unique_visitors'] ?? 0) }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card  bg-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Заказы</h5>
                                    <h2 class="mb-0">{{ number_format($generalStats['completed_orders'] ?? 0) }}</h2>
                                    <small>из {{ number_format($generalStats['total_orders'] ?? 0) }} всего</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card  bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Выручка</h5>
                                    <h2 class="mb-0">{{ number_format($generalStats['total_revenue'] ?? 0, 0, ',', ' ') }} ₽</h2>
                                    @if(($generalStats['completed_orders'] ?? 0) > 0)
                                        <small>Средний чек: {{ number_format($generalStats['average_order_value'] ?? 0, 0, ',', ' ') }} ₽</small>
                                    @endif
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-ruble-sign fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Дополнительные метрики -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-percentage me-2"></i>
                                Конверсия
                            </h5>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-3">{{ $generalStats['conversion_rate'] ?? 0 }}%</h3>
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ min($generalStats['conversion_rate'] ?? 0, 100) }}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">Из {{ number_format($generalStats['unique_visitors'] ?? 0) }} посетителей {{ number_format($generalStats['completed_orders'] ?? 0) }} совершили покупку</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>
                                Время обработки заказов
                            </h5>
                            <h3 class="mb-0">{{ $orderStats['average_processing_time'] ?? 0 }} ч</h3>
                            <small class="text-muted">Среднее время от создания до выполнения заказа</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Графики -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="chartsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visits" type="button" role="tab">Посещения</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Заказы</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue" type="button" role="tab">Выручка</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="visits" role="tabpanel">
                                    <canvas id="visitsChart" height="300"></canvas>
                                </div>
                                <div class="tab-pane fade" id="orders" role="tabpanel">
                                    <canvas id="ordersChart" height="300"></canvas>
                                </div>
                                <div class="tab-pane fade" id="revenue" role="tabpanel">
                                    <canvas id="revenueChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Источники трафика</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trafficSourcesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детальная статистика -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-fire me-2"></i>
                                Популярные товары (по просмотрам)
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($productStats['popular_products']) && $productStats['popular_products']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Просмотры</th>
                                            <th>Уникальные</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($productStats['popular_products'] as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if(isset($item['product']) && $item['product']->photo_url)
                                                            <img src="{{ $item['product']->photo_url }}" alt="{{ $item['product']->name }}" class="me-2" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                        @endif
                                                        <span>{{ isset($item['product']) ? Str::limit($item['product']->name, 30) : 'Неизвестный товар' }}</span>
                                                    </div>
                                                </td>
                                                <td><strong>{{ $item['views'] ?? 0 }}</strong></td>
                                                <td>{{ $item['unique_views'] ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Нет данных о просмотрах товаров за выбранный период.
                                    @if(!$botId)
                                        <br><small>Попробуйте выбрать конкретного бота или расширить период.</small>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-trophy me-2"></i>
                                Самые покупаемые товары
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($productStats['best_selling_products']) && $productStats['best_selling_products']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Продано</th>
                                            <th>Выручка</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($productStats['best_selling_products'] as $product)
                                            <tr>
                                                <td>{{ Str::limit($product->name, 30) }}</td>
                                                <td><strong>{{ $product->total_sold }}</strong></td>
                                                <td>{{ number_format($product->total_revenue, 0, ',', ' ') }} ₽</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Нет данных о продажах за выбранный период.
                                    @if(!$botId)
                                        <br><small>Продажи учитываются только по завершенным заказам.</small>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Популярные страницы -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Популярные страницы
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($visitStats['top_pages']) && $visitStats['top_pages']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Страница</th>
                                            <th>Посещения</th>
                                            <th>Уникальные посетители</th>
                                            <th>Популярность</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($visitStats['top_pages'] as $page)
                                            <tr>
                                                <td>
                                                    <code>{{ Str::limit($page->page_url, 60) }}</code>
                                                </td>
                                                <td><strong>{{ $page->visits }}</strong></td>
                                                <td>{{ $page->unique_visitors }}</td>
                                                <td>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $visitStats['top_pages']->first() ? (($page->visits / $visitStats['top_pages']->first()->visits) * 100) : 0 }}%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Нет данных о посещениях страниц за выбранный период.
                                    @if(!$botId)
                                        <br><small>Данные собираются только с активных посещений сайта.</small>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Информация о данных -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-shield-alt fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        100% реальные данные
                                    </h6>
                                    <p class="card-text mb-0 text-muted">
                                        Вся статистика формируется исключительно из реальных данных вашего магазина: 
                                        посещения отслеживаются автоматически, заказы берутся из базы данных, товары — из вашего каталога.
                                        Никаких демо-данных или заглушек.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Показать/скрыть поля для произвольного периода
    const periodSelect = document.getElementById('period');
    const startDateCol = document.getElementById('start-date-col');
    const endDateCol = document.getElementById('end-date-col');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const dateHelp = document.getElementById('date-help');
    
    function toggleCustomDateFields() {
        if (periodSelect.value === 'custom') {
            startDateCol.style.display = 'block';
            endDateCol.style.display = 'block';
            if (dateHelp) dateHelp.style.display = 'block';
            startDateInput.required = true;
            endDateInput.required = true;
            
            // Если поля пустые, устанавливаем дефолтные значения
            if (!startDateInput.value) {
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
            }
            if (!endDateInput.value) {
                const today = new Date();
                endDateInput.value = today.toISOString().split('T')[0];
            }
        } else {
            startDateCol.style.display = 'none';
            endDateCol.style.display = 'none';
            if (dateHelp) dateHelp.style.display = 'none';
            startDateInput.required = false;
            endDateInput.required = false;
        }
    }
    
    // Инициализация при загрузке страницы
    toggleCustomDateFields();
    
    periodSelect.addEventListener('change', toggleCustomDateFields);
    
    // Валидация дат
    startDateInput.addEventListener('change', function() {
        if (endDateInput.value && this.value > endDateInput.value) {
            endDateInput.value = this.value;
        }
        endDateInput.min = this.value;
    });
    
    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && this.value < startDateInput.value) {
            startDateInput.value = this.value;
        }
        startDateInput.max = this.value;
    });

    // Обработчик для кнопки генерации отчета
    document.getElementById('generateReportBtn').addEventListener('click', function() {
        const form = this.closest('form');
        const formData = new FormData(form);
        
        // Создаем URL для генерации отчета
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        // Показываем индикатор загрузки
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Генерация...';
        this.disabled = true;
        
        // Открываем новое окно для скачивания отчета
        const reportUrl = `{{ route('statistics.generate-report') }}?${params.toString()}`;
        
        // Используем fetch для проверки статуса ответа
        fetch(reportUrl)
            .then(response => {
                if (response.ok) {
                    // Если успешно, открываем ссылку для скачивания
                    window.location.href = reportUrl;
                } else {
                    throw new Error('Ошибка генерации отчета');
                }
            })
            .catch(error => {
                alert('Произошла ошибка при генерации отчета. Попробуйте еще раз.');
                console.error('Error:', error);
            })
            .finally(() => {
                // Восстанавливаем кнопку
                this.innerHTML = originalText;
                this.disabled = false;
            });
    });

    // Конфигурация графиков
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return Number.isInteger(value) ? value : '';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: true
            }
        }
    };

    // График посещений
    const visitsCtx = document.getElementById('visitsChart').getContext('2d');
    fetch(`{{ route('statistics.chart-data') }}?bot_id={{ $botId }}&period={{ $period }}&start_date={{ $startDate }}&end_date={{ $endDate }}&chart_type=visits`)
        .then(response => response.json())
        .then(data => {
            if (data.labels && data.labels.length > 0) {
                new Chart(visitsCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Посещения',
                            data: data.visits,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Уникальные посетители',
                            data: data.unique_visitors,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: chartOptions
                });
            } else {
                // Показываем сообщение об отсутствии данных
                visitsCtx.font = '16px Arial';
                visitsCtx.fillStyle = '#6c757d';
                visitsCtx.textAlign = 'center';
                visitsCtx.fillText('Нет данных о посещениях за выбранный период', visitsCtx.canvas.width / 2, visitsCtx.canvas.height / 2);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных посещений:', error);
        });

    // График заказов
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    fetch(`{{ route('statistics.chart-data') }}?bot_id={{ $botId }}&period={{ $period }}&start_date={{ $startDate }}&end_date={{ $endDate }}&chart_type=orders`)
        .then(response => response.json())
        .then(data => {
            if (data.labels && data.labels.length > 0) {
                new Chart(ordersCtx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Заказы',
                            data: data.orders,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgb(75, 192, 192)',
                            borderWidth: 1
                        }]
                    },
                    options: chartOptions
                });
            } else {
                ordersCtx.font = '16px Arial';
                ordersCtx.fillStyle = '#6c757d';
                ordersCtx.textAlign = 'center';
                ordersCtx.fillText('Нет данных о заказах за выбранный период', ordersCtx.canvas.width / 2, ordersCtx.canvas.height / 2);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных заказов:', error);
        });

    // График выручки
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    fetch(`{{ route('statistics.chart-data') }}?bot_id={{ $botId }}&period={{ $period }}&start_date={{ $startDate }}&end_date={{ $endDate }}&chart_type=revenue`)
        .then(response => response.json())
        .then(data => {
            if (data.labels && data.labels.length > 0) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Выручка (₽)',
                            data: data.revenue,
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        ...chartOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('ru-RU', {
                                            style: 'currency',
                                            currency: 'RUB',
                                            maximumFractionDigits: 0
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                revenueCtx.font = '16px Arial';
                revenueCtx.fillStyle = '#6c757d';
                revenueCtx.textAlign = 'center';
                revenueCtx.fillText('Нет данных о выручке за выбранный период', revenueCtx.canvas.width / 2, revenueCtx.canvas.height / 2);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных выручки:', error);
        });

    // График источников трафика
    const trafficSourcesCtx = document.getElementById('trafficSourcesChart').getContext('2d');
    const trafficSources = @json($visitStats['traffic_sources'] ?? []);
    
    if (trafficSources && trafficSources.length > 0) {
        new Chart(trafficSourcesCtx, {
            type: 'doughnut',
            data: {
                labels: trafficSources.map(source => source.source),
                datasets: [{
                    data: trafficSources.map(source => source.visits),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB', 
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
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
    } else {
        // Показываем сообщение, если нет данных
        const canvas = document.getElementById('trafficSourcesChart');
        const ctx = canvas.getContext('2d');
        ctx.font = '16px Arial';
        ctx.fillStyle = '#6c757d';
        ctx.textAlign = 'center';
        ctx.fillText('Нет данных о источниках трафика', canvas.width / 2, canvas.height / 2);
    }
});
</script>
@endsection