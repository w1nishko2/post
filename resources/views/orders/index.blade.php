@extends('layouts.app')

@section('content')
<div class="admin-container">
    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle admin-me-2"></i>
            {{ session('success') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            {{ session('error') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="{{ route('home') }}">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="{{ route('products.select-bot') }}">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
        <a class="admin-nav-pill active" href="{{ route('orders.index') }}">
            <i class="fas fa-shopping-cart"></i> Заказы
        </a>
        <a class="admin-nav-pill" href="{{ route('statistics.index') }}">
            <i class="fas fa-chart-line"></i> Статистика
        </a>
    </div>

    <!-- Фильтры и статистика заказов -->
    <div class="admin-row admin-mb-4">
        <div class="admin-col admin-col-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-filter admin-me-2"></i>
                        Фильтры заказов
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="GET" action="{{ route('orders.index') }}">
                        <div class="admin-row">
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="status" class="admin-form-label">Статус заказа</label>
                                    <select class="admin-form-control admin-select" id="status" name="status">
                                        <option value="">Все заказы</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает обработки</option>
                                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>В обработке</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Выполнен</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Отменен</option>
                                    </select>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="bot_id" class="admin-form-label">Бот</label>
                                    <select class="admin-form-control admin-select" id="bot_id" name="bot_id">
                                        <option value="">Все боты</option>
                                        @foreach(auth()->user()->telegramBots as $bot)
                                            <option value="{{ $bot->id }}" {{ request('bot_id') == $bot->id ? 'selected' : '' }}>
                                                {{ $bot->bot_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="date_from" class="admin-form-label">Дата с</label>
                                    <input type="date" class="admin-form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}" max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-form-group">
                                    <label for="date_to" class="admin-form-label">Дата до</label>
                                    <input type="date" class="admin-form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}" max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-filter admin-me-1"></i>
                                Применить фильтры
                            </button>
                            @if(request()->hasAny(['status', 'bot_id', 'date_from', 'date_to']))
                                <a href="{{ route('orders.index') }}" class="admin-btn admin-btn-sm">
                                    <i class="fas fa-times admin-me-1"></i>
                                    Сбросить
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="admin-col admin-col-4">
            <div class="admin-card admin-stats-card">
                <div class="admin-card-body">
                    <div class="admin-stats-content">
                        <div class="admin-stats-info">
                            <h3 class="admin-stats-number">{{ $orders->total() }}</h3>
                            <div class="admin-stats-label">Всего заказов</div>
                            @if($orders->where('status', 'completed')->count() > 0)
                                <div class="admin-stats-sub">
                                    Выполнено: {{ $orders->where('status', 'completed')->count() }}
                                </div>
                            @endif
                        </div>
                        <div class="admin-stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список заказов -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-list admin-me-2"></i>
                Мои заказы
            </h5>
            <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                <span class="admin-text-muted">Показать по:</span>
                <select class="admin-form-control admin-form-control-sm" id="per-page" style="width: auto;">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>

        <div class="admin-card-body admin-p-0">
            @if($orders->count() > 0)
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">№ Заказа</th>
                                <th style="width: 140px;">Дата</th>
                                <th>Товары</th>
                                <th style="width: 100px;">Сумма</th>
                                <th style="width: 120px;">Статус</th>
                                <th style="width: 150px;">Бот</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <div class="admin-order-number">
                                            <strong>{{ $order->order_number }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-date">
                                            <div>{{ $order->created_at->format('d.m.Y') }}</div>
                                            <div class="admin-text-muted admin-small">{{ $order->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-items">
                                            <div class="admin-mb-1">
                                                <strong>{{ $order->total_items }} шт.</strong>
                                            </div>
                                            <div class="admin-order-products">
                                                @foreach($order->items->take(2) as $item)
                                                    <div class="admin-product-line">
                                                        {{ Str::limit($item->product_name, 30) }}
                                                        <span class="admin-text-muted">({{ $item->quantity }} шт.)</span>
                                                    </div>
                                                @endforeach
                                                @if($order->items->count() > 2)
                                                    <div class="admin-text-muted admin-small">
                                                        и ещё {{ $order->items->count() - 2 }} товаров...
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-order-total">
                                            <strong>{{ $order->formatted_total }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="admin-badge admin-badge-warning">Ожидает</span>
                                                    @break
                                                @case('processing')
                                                    <span class="admin-badge admin-badge-info">В обработке</span>
                                                    @break
                                                @case('completed')
                                                    <span class="admin-badge admin-badge-success">Выполнен</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="admin-badge admin-badge-danger">Отменен</span>
                                                    @break
                                                @default
                                                    <span class="admin-badge">{{ $order->status }}</span>
                                            @endswitch
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->telegramBot)
                                            <div class="admin-bot-info-compact">
                                                <div class="admin-bot-name">{{ Str::limit($order->telegramBot->bot_name, 20) }}</div>
                                                <div class="admin-text-muted admin-small">@{{ $order->telegramBot->bot_username }}</div>
                                            </div>
                                        @else
                                            <span class="admin-text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="{{ route('orders.show', $order) }}" 
                                               class="admin-btn admin-btn-xs" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order->canBeCancelled())
                                                <button class="admin-btn admin-btn-xs admin-btn-danger" 
                                                        onclick="cancelOrder({{ $order->id }})" 
                                                        title="Отменить">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            @if($order->status === 'pending')
                                                <button class="admin-btn admin-btn-xs admin-btn-success" 
                                                        onclick="completeOrder({{ $order->id }})" 
                                                        title="Выполнить">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                @if($orders->hasPages())
                    <div class="admin-card-footer">
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <div class="admin-text-muted">
                                Показаны записи {{ $orders->firstItem() }}-{{ $orders->lastItem() }} 
                                из {{ $orders->total() }}
                            </div>
                            <div class="admin-pagination">
                                @if($orders->onFirstPage())
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                @else
                                    <a href="{{ $orders->previousPageUrl() }}" class="admin-page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif

                                @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                    @if($page == $orders->currentPage())
                                        <span class="admin-page-link active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="admin-page-link">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if($orders->hasMorePages())
                                    <a href="{{ $orders->nextPageUrl() }}" class="admin-page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="admin-empty-state">
                    <div class="admin-empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h6>У вас пока нет заказов</h6>
                    <p class="admin-text-muted">Заказы, сделанные через ваши Telegram боты, будут отображаться здесь</p>
                    <a href="{{ route('products.select-bot') }}" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить товары
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Изменение количества записей на странице
    const perPageSelect = document.getElementById('per-page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            window.location.href = url.toString();
        });
    }

    // Валидация дат
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            if (dateToInput.value && this.value > dateToInput.value) {
                dateToInput.value = this.value;
            }
            dateToInput.min = this.value;
        });
        
        dateToInput.addEventListener('change', function() {
            if (dateFromInput.value && this.value < dateFromInput.value) {
                dateFromInput.value = this.value;
            }
            dateFromInput.max = this.value;
        });
    }
});

// Отмена заказа
function cancelOrder(orderId) {
    if (confirm('Вы уверены, что хотите отменить этот заказ?\n\nТовары будут возвращены на склад.')) {
        fetch(`/orders/${orderId}/cancel`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка при отмене заказа');
        });
    }
}

// Выполнение заказа
function completeOrder(orderId) {
    if (confirm('Отметить заказ как выполненный?')) {
        fetch(`/orders/${orderId}/complete`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка при выполнении заказа');
        });
    }
}

// Показ уведомлений
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'admin-alert-success' : 'admin-alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    
    const alertHTML = `
        <div class="admin-alert ${alertClass}">
            <i class="${iconClass} admin-me-2"></i>
            ${message}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    const container = document.querySelector('.admin-container');
    container.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        const alert = container.querySelector('.admin-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection