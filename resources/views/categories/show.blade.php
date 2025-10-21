@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Навигационная панель -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link" href="{{ route('home') }}">
                            Мои боты
                        </a>
                        <a class="nav-link" href="{{ route('bot.products.index', $telegramBot) }}">
                            Товары
                        </a>
                        <a class="nav-link active" href="{{ route('bot.categories.index', $telegramBot) }}">
                            Категории
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Хлебные крошки -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('bot.categories.index', $telegramBot) }}">
                            Категории
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $category->name }}
                    </li>
                </ol>
            </nav>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Информация о категории -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                @if($category->photo_url)
                                    <img src="{{ $category->photo_url }}" 
                                         class="rounded me-4" 
                                         style="width: 120px; height: 120px; object-fit: cover;"
                                         alt="{{ $category->name }}"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="rounded me-4 bg-warning d-none align-items-center justify-content-center " 
                                         style="width: 120px; height: 120px; font-size: 40px;">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                @else
                                    <div class="rounded me-4 bg-warning d-flex align-items-center justify-content-center " 
                                         style="width: 120px; height: 120px; font-size: 40px;">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                @endif
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h3 class="mb-0 me-3">{{ $category->name }}</h3>
                                        @if($category->is_active)
                                            <span class="badge bg-success">Активна</span>
                                        @else
                                            <span class="badge bg-secondary">Не активна</span>
                                        @endif
                                    </div>

                                    @if($category->description)
                                        <p class="text-muted mb-3">{{ $category->description }}</p>
                                    @endif

                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-primary mb-1">{{ $category->products->count() }}</h5>
                                                <small class="text-muted">Всего товаров</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-success mb-1">{{ $category->activeProducts->count() }}</h5>
                                                <small class="text-muted">Активных товаров</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h5 class="text-info mb-1">{{ $category->created_at->format('d.m.Y') }}</h5>
                                                <small class="text-muted">Дата создания</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Управление категорией</h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-grid gap-2 flex-grow-1">
                                <a href="{{ route('bot.categories.edit', [$telegramBot, $category]) }}" class="btn btn-primary">
                                    Редактировать
                                </a>
                                <a href="{{ route('bot.categories.index', $telegramBot) }}" class="btn btn-outline-primary">
                                    К списку категорий
                                </a>
                                @if($category->products->count() == 0)
                                    <button class="btn btn-outline-danger mt-auto" onclick="deleteCategory()">
                                        Удалить категорию
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary mt-auto" disabled title="Нельзя удалить категорию с товарами">
                                        Нельзя удалить
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Товары в категории -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Товары в категории
                        <span class="badge bg-secondary ms-2">{{ $category->products->count() }}</span>
                    </h5>
                    <div>
                        <a href="{{ route('bot.products.create', $telegramBot) }}?category_id={{ $category->id }}" class="btn btn-primary btn-sm">
                            Добавить товар
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($category->products->count() > 0)
                        <div class="row">
                            @foreach($category->products as $product)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 product-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            @if($product->photo_url)
                                                <img src="{{ $product->photo_url }}" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 50px; object-fit: cover;"
                                                     alt="{{ $product->name }}"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="rounded me-3 bg-primary d-none align-items-center justify-content-center " 
                                                     style="width: 60px; height: 50px; font-size: 20px;">
                                                    <i class="fas fa-cube"></i>
                                                </div>
                                            @else
                                                <div class="rounded me-3 bg-primary d-flex align-items-center justify-content-center " 
                                                     style="width: 60px; height: 50px; font-size: 20px;">
                                                    <i class="fas fa-cube"></i>
                                                </div>
                                            @endif
                                            
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1">{{ $product->name }}</h6>
                                                <p class="text-success fw-bold mb-1">{{ number_format($product->price, 0, ',', ' ') }} ₽</p>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span class="me-2">Остаток: {{ $product->quantity ?? 'Неограничен' }}</span>
                                                    @if(!$product->is_active)
                                                        <span class="badge bg-secondary">Неактивен</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-eye"></i> Просмотр
                                            </a>
                                            <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-cube fa-4x text-muted opacity-50"></i>
                            </div>
                            <h5 class="text-muted mb-3">В категории пока нет товаров</h5>
                            <p class="text-muted mb-4">
                                Добавьте первый товар в эту категорию или назначьте категорию существующим товарам.
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('bot.products.create', $telegramBot) }}?category_id={{ $category->id }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Создать товар
                                </a>
                                <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Все товары
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" action="{{ route('bot.categories.destroy', [$telegramBot, $category]) }}" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
function deleteCategory() {
    if (confirm(`Вы уверены, что хотите удалить категорию "{{ $category->name }}"?\n\nЭто действие необратимо!`)) {
        document.getElementById('delete-form').submit();
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
@endpush