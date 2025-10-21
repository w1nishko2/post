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

    @if (session('warning'))
        <div class="admin-alert admin-alert-warning">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            {{ session('warning') }}
            @if (session('import_errors'))
                <div class="admin-mt-2 admin-pt-2" style="border-top: 1px solid var(--color-border);">
                    <strong>Детали ошибок:</strong><br>
                    <small>{!! nl2br(e(session('import_errors'))) !!}</small>
                </div>
            @endif
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

    <!-- Информация о боте -->
    @if(isset($telegramBot))
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
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
                    <a href="{{ route('products.select-bot') }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-exchange-alt admin-me-2"></i>
                        Сменить бота
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Блоки управления -->
    @if(isset($telegramBot))
    <div class="admin-row admin-mb-4">
        <!-- Блок управления категориями -->
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-tags admin-me-2"></i>
                        Управление категориями
                    </h6>
                </div>
                <div class="admin-card-body">
                    <p class="admin-text-muted admin-mb-3">
                        Создавайте и управляйте категориями товаров для лучшей организации каталога
                    </p>
                    <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                        <a href="{{ route('bot.categories.index', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-list admin-me-1"></i>
                            Все категории
                        </a>
                        <a href="{{ route('bot.categories.create', $telegramBot) }}" class="admin-btn admin-btn-sm admin-btn-primary">
                            <i class="fas fa-plus admin-me-1"></i>
                            Добавить
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Блок массового управления товарами -->
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-boxes admin-me-2"></i>
                        Массовые операции
                    </h6>
                </div>
                <div class="admin-card-body">
                    <p class="admin-text-muted admin-mb-3">
                        Импорт товаров из Excel файлов и экспорт данных для массового редактирования
                    </p>
                    <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                        <button class="admin-btn admin-btn-sm admin-btn-success" onclick="showModal('importModal')">
                            <i class="fas fa-upload admin-me-1"></i>
                            Импорт
                        </button>
                        <a href="{{ route('bot.products.export-data', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-download admin-me-1"></i>
                            Экспорт
                        </a>
                        <a href="{{ route('bot.products.download-template', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-file-excel admin-me-1"></i>
                            Шаблон
                        </a>
                        <a href="{{ route('bot.products.table', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-table admin-me-1"></i>
                            Таблица
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body admin-text-center">
            <i class="fas fa-info-circle admin-text-info" style="font-size: 32px;"></i>
            <h5 class="admin-mt-3">Выберите бота для управления товарами</h5>
            <p class="admin-text-muted">Сначала выберите бота из списка выше</p>
        </div>
    </div>
    @endif

    <!-- Контент товаров -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">Управление товарами</h5>
            @if(isset($telegramBot))
                <div class="admin-d-flex admin-gap-sm">
                    <a href="{{ route('bot.products.create', $telegramBot) }}" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить товар
                    </a>
                </div>
            @else
                <span class="admin-text-muted">Выберите бота для добавления товаров</span>
            @endif
        </div>

        <div class="admin-card-body">
            @if($products->count() > 0)
                <div class="admin-products-grid">
                    @foreach($products as $product)
                        <div class="admin-product-card">
                            <div class="admin-product-image">
                                @if($product->photo_url)
                                    <img src="{{ $product->photo_url }}" alt="{{ $product->name }}">
                                @else
                                    <div class="admin-d-flex admin-align-items-center admin-justify-content-center admin-h-100 admin-text-muted">
                                        <i class="fas fa-image" style="font-size: 32px;"></i>
                                    </div>
                                @endif
                                <div class="admin-product-badge">
                                    @if($product->quantity > 0)
                                        <span class="admin-badge admin-badge-success">{{ $product->quantity }} шт</span>
                                    @else
                                        <span class="admin-badge admin-badge-danger">Нет в наличии</span>
                                    @endif
                                </div>
                            </div>
                            <div class="admin-product-info">
                                <h6 class="admin-product-name">{{ $product->name }}</h6>
                                @if($product->description)
                                    <p class="admin-product-description">{{ $product->description }}</p>
                                @endif
                                <div class="admin-product-footer">
                                    <div class="admin-product-price">{{ number_format($product->price, 0, ',', ' ') }} ₽</div>
                                    <div class="admin-product-actions">
                                        <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" 
                                           class="admin-btn admin-btn-sm" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" 
                                           class="admin-btn admin-btn-sm" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" 
                                              class="admin-d-inline" onsubmit="return confirm('Удалить товар {{ $product->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline-danger" title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Пагинация -->
                @if($products->hasPages())
                <div class="admin-pagination admin-mt-4">
                    @if($products->onFirstPage())
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="admin-page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if($page == $products->currentPage())
                            <span class="admin-page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="admin-page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="admin-page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
                @endif
            @else
                <div class="admin-empty-state">
                    <i class="fas fa-boxes"></i>
                    <h3>Товары не найдены</h3>
                    @if(isset($telegramBot))
                        <p class="admin-mb-4">Создайте первый товар для этого бота</p>
                        <a href="{{ route('bot.products.create', $telegramBot) }}" class="admin-btn admin-btn-primary">
                            <i class="fas fa-plus admin-me-2"></i>
                            Добавить первый товар
                        </a>
                    @else
                        <p>Сначала выберите бота для просмотра товаров</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно импорта -->
@if(isset($telegramBot))
<div class="admin-modal" id="importModal">
    <div class="admin-modal-dialog">
        <div class="admin-modal-content">
            <form method="POST" action="{{ route('bot.products.import', $telegramBot) }}" enctype="multipart/form-data">
                @csrf
                <div class="admin-modal-header">
                <h5 class="admin-modal-title">
                    <i class="fas fa-upload admin-me-2"></i>
                    Импорт товаров
                </h5>
                <button type="button" class="admin-modal-close" onclick="hideModal('importModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="import_file" class="admin-form-label required">Файл Excel</label>
                    <input type="file" class="admin-form-control" id="import_file" name="file" 
                           accept=".xlsx,.xls,.csv" required>
                    <div class="admin-form-text">Поддерживаемые форматы: Excel (.xlsx, .xls), CSV</div>
                </div>

                <div class="admin-form-group admin-mb-0">
                    <div class="admin-form-check">
                        <input type="checkbox" class="admin-form-check-input" id="update_existing" name="update_existing" value="1">
                        <label for="update_existing" class="admin-form-check-label">
                            Обновлять существующие товары по артикулу
                        </label>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn" onclick="hideModal('importModal')">Отмена</button>
                <button type="submit" class="admin-btn admin-btn-success">
                    <i class="fas fa-upload admin-me-2"></i>
                    Импортировать
                </button>
            </div>
        </form>
    </div>
    </div>
</div>
@endif

<script>
// Функции для работы с модальными окнами
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Закрытие модальных окон при клике на фон
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal(modal.id);
            }
        });
    });
});
</script>
@endsection