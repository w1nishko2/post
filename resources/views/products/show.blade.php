@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Навигационная панель -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill" style="padding: 0.5rem;">
                        <a class="nav-link" href="{{ route('home') }}"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-robot me-2"></i>Мои боты
                        </a>
                        <a class="nav-link active" href="{{ route('products.select-bot') }}"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-boxes me-2"></i>Мои товары
                        </a>
                    </nav>
                </div>
            </div>

            @if(isset($telegramBot))
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
                                    <h6 class="mb-1">{{ $telegramBot->bot_name }}</h6>
                                    <small class="text-muted">Просмотр товара в магазине</small>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> К товарам магазина
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <!-- Фотография товара -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            @if($product->photo_url)
                                <img src="{{ $product->photo_url }}" class="img-fluid rounded" 
                                     alt="{{ $product->name }}" style="max-height: 400px;"
                                     onerror="this.src='https://via.placeholder.com/400x300?text=Нет+фото'">
                            @else
                                <img src="https://via.placeholder.com/400x300?text=Нет+фото" 
                                     class="img-fluid rounded" alt="Нет фото">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Информация о товаре -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ $product->name }}</h4>
                            @if(isset($telegramBot))
                                <div class="btn-group">
                                    <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Артикул:</strong></div>
                                <div class="col-sm-8"><code>{{ $product->article }}</code></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Цена:</strong></div>
                                <div class="col-sm-8">
                                    <span class="h4 text-success">{{ $product->formatted_price_with_markup }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Количество:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-{{ $product->quantity > 5 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger') }} fs-6">
                                        {{ $product->quantity }} шт.
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Статус:</strong></div>
                                <div class="col-sm-8">
                                    @php
                                        $status = $product->availability_status;
                                        $statusClass = 'secondary';
                                        if($status === 'В наличии') $statusClass = 'success';
                                        elseif($status === 'Заканчивается') $statusClass = 'warning';
                                        elseif($status === 'Нет в наличии') $statusClass = 'danger';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }} fs-6">{{ $status }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Активность:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }} fs-6">
                                        {{ $product->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </div>
                            </div>

                            @if($product->description)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Описание:</strong></div>
                                <div class="col-sm-8">{{ $product->description }}</div>
                            </div>
                            @endif

                            @if($product->specifications && count($product->specifications) > 0)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Характеристики:</strong></div>
                                <div class="col-sm-8">
                                    <ul class="list-unstyled mb-0">
                                        @foreach($product->specifications as $spec)
                                            <li>• {{ $spec }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Создан:</strong></div>
                                <div class="col-sm-8">{{ $product->created_at->format('d.m.Y H:i') }}</div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-4"><strong>Обновлен:</strong></div>
                                <div class="col-sm-8">{{ $product->updated_at->format('d.m.Y H:i') }}</div>
                            </div>

                            <!-- Действия -->
                            <div class="d-grid gap-2">
                              
                                
                                @if(isset($telegramBot))
                                    <div class="btn-group">
                                        <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i> Редактировать
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                                            <i class="fas fa-trash"></i> Удалить
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика товара -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Статистика товара</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-primary">{{ $product->formatted_price_with_markup }}</h4>
                                        <small class="text-muted">Цена за штуку</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-info">{{ $product->quantity }}</h4>
                                        <small class="text-muted">Количество в наличии</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-success">{{ number_format($product->quantity * $product->price, 0, ',', ' ') }} ₽</h4>
                                        <small class="text-muted">Общая стоимость</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning">0</h4>
                                    <small class="text-muted">В корзинах</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($telegramBot))
    <!-- Скрытая форма для удаления -->
    <form id="delete-form" method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция добавления в корзину
    window.addToCart = function(productId) {
        // AJAX запрос для добавления в корзину
        fetch(`/cart/add/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Товар добавлен в корзину!');
                updateCartCounter();
            } else {
                showAlert('error', data.message || 'Произошла ошибка при добавлении товара');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showAlert('error', 'Произошла ошибка при добавлении товара');
        });
    };

    // Функция удаления товара
    window.deleteProduct = function() {
        if (confirm('Вы уверены, что хотите удалить товар "{{ $product->name }}"?\n\nЭто действие нельзя отменить!')) {
            document.getElementById('delete-form').submit();
        }
    };

    // Функция показа уведомлений
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.container .row .col-md-10');
        container.insertAdjacentHTML('afterbegin', alertHTML);
        
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Обновление счетчика корзины
    function updateCartCounter() {
        fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const counter = document.querySelector('.cart-counter');
            if (counter) {
                counter.textContent = data.count;
                counter.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => console.error('Ошибка при обновлении счетчика корзины:', error));
    }
});
</script>
@endpush