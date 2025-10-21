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
        <a class="admin-nav-pill active" href="{{ route('products.select-bot') }}">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    @if(isset($telegramBot))
    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between">
                <div class="admin-d-flex admin-align-items-center">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar {{ $telegramBot->is_active ? '' : 'inactive' }}">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="admin-mb-1">{{ $telegramBot->bot_name }}</h6>
                        <div class="admin-text-muted">@{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        К списку товаров
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="admin-row">
        <div class="admin-col admin-col-6">
            <!-- Фотография товара -->
            <div class="admin-card admin-mb-4">
                <div class="admin-card-body admin-text-center">
                    @if($product->photo_url)
                        <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" 
                             style="width: 100%; max-height: 400px; object-fit: contain; border-radius: var(--radius-md);">
                    @else
                        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background-color: var(--color-light-gray); border-radius: var(--radius-md); color: var(--color-gray);">
                            <div class="admin-text-center">
                                <i class="fas fa-image" style="font-size: 48px; margin-bottom: 16px;"></i>
                                <div>Изображение отсутствует</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-col admin-col-6">
            <!-- Информация о товаре -->
            <div class="admin-card">
                <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
                    <h5 class="admin-mb-0">{{ $product->name }}</h5>
                    <div class="admin-d-flex admin-gap-sm">
                        <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" 
                           class="admin-btn admin-btn-sm">
                            <i class="fas fa-edit admin-me-1"></i>
                            Редактировать
                        </a>
                        <button class="admin-btn admin-btn-sm admin-btn-outline-danger" onclick="deleteProduct()">
                            <i class="fas fa-trash admin-me-1"></i>
                            Удалить
                        </button>
                    </div>
                </div>
                
                <div class="admin-card-body">
                    <div class="admin-row admin-mb-4">
                        <div class="admin-col admin-col-6">
                            <div class="admin-form-label">Цена:</div>
                            <div class="admin-fw-bold" style="font-size: 24px; color: var(--color-success);">
                                {{ number_format($product->price, 0, ',', ' ') }} ₽
                            </div>
                        </div>
                        <div class="admin-col admin-col-6">
                            <div class="admin-form-label">Количество:</div>
                            <div class="admin-fw-bold">
                                @if($product->quantity > 0)
                                    <span class="admin-badge admin-badge-success">{{ $product->quantity }} шт</span>
                                @else
                                    <span class="admin-badge admin-badge-danger">Нет в наличии</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($product->description)
                        <div class="admin-form-group">
                            <div class="admin-form-label">Описание:</div>
                            <div style="padding: 16px; background-color: var(--color-light-gray); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                                {{ $product->description }}
                            </div>
                        </div>
                    @endif

                    <div class="admin-row">
                        @if($product->article)
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Артикул:</div>
                                <div class="admin-fw-bold">{{ $product->article }}</div>
                            </div>
                        @endif
                        @if($product->category)
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Категория:</div>
                                <div class="admin-fw-bold">{{ $product->category->name }}</div>
                            </div>
                        @endif
                    </div>

                    @if($product->markup_percentage > 0)
                        <div class="admin-row admin-mb-3">
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Наценка:</div>
                                <div class="admin-fw-bold">{{ $product->markup_percentage }}%</div>
                            </div>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-label">Базовая цена:</div>
                                <div class="admin-fw-bold">
                                    {{ number_format($product->price / (1 + $product->markup_percentage / 100), 0, ',', ' ') }} ₽
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                        <div>
                            <div class="admin-form-label">Статус:</div>
                            @if($product->is_active)
                                <span class="admin-badge admin-badge-success">Активен</span>
                            @else
                                <span class="admin-badge admin-badge-warning">Неактивен</span>
                            @endif
                        </div>
                        <div class="admin-text-muted admin-text-right">
                            <small>
                                Создан: {{ $product->created_at->format('d.m.Y H:i') }}<br>
                                Обновлен: {{ $product->updated_at->format('d.m.Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($telegramBot))
    <!-- Скрытая форма для удаления -->
    <form id="delete-form" method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" class="admin-d-none">
        @csrf
        @method('DELETE')
    </form>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция удаления товара
    window.deleteProduct = function() {
        if (confirm('Вы уверены, что хотите удалить товар "{{ $product->name }}"?\n\nЭто действие нельзя отменить!')) {
            document.getElementById('delete-form').submit();
        }
    };
});
</script>
@endsection