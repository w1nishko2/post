@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('bot.categories.show', [$telegramBot, $category]) }}">
                            {{ $category->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Редактирование
                    </li>
                </ol>
            </nav>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Форма редактирования категории -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактирование категории "{{ $category->name }}"</h5>
                    <small class="text-muted">Внесите изменения в информацию о категории</small>
                </div>

                <div class="card-body">
                    <form action="{{ route('bot.categories.update', [$telegramBot, $category]) }}" method="POST" id="categoryForm">
                        @csrf
                        @method('PUT')

                        <!-- Название категории -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                Название категории <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $category->name) }}" 
                                   placeholder="Например: Электроника, Одежда, Книги..."
                                   maxlength="100"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Краткое и понятное название для ваших покупателей
                            </div>
                        </div>

                        <!-- Описание категории -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Описание категории
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Краткое описание категории (необязательно)"
                                      maxlength="500">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Дополнительная информация о товарах в этой категории
                            </div>
                        </div>

                        <!-- Фотография категории -->
                        <div class="mb-4">
                            <label for="photo_url" class="form-label">
                                Ссылка на фотографию
                            </label>
                            <input type="url" 
                                   class="form-control @error('photo_url') is-invalid @enderror" 
                                   id="photo_url" 
                                   name="photo_url" 
                                   value="{{ old('photo_url', $category->photo_url) }}" 
                                   placeholder="https://example.com/category-image.jpg">
                            @error('photo_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Рекомендуемый размер: 300x300px. Поддерживаются форматы: JPG, PNG
                            </div>
                        </div>

                        <!-- Текущее изображение -->
                        @if($category->photo_url)
                        <div class="mb-4">
                            <label class="form-label">
                                Текущее изображение
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <img src="{{ $category->photo_url }}" 
                                     alt="{{ $category->name }}" 
                                     class="rounded"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="this.parentElement.innerHTML='<div class=\'text-muted\'>Изображение недоступно</div>'">
                            </div>
                        </div>
                        @endif

                        <!-- Предварительный просмотр фото -->
                        <div class="mb-4" id="photoPreview" style="display: none;">
                            <label class="form-label">
                                Предварительный просмотр изменений
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex align-items-center">
                                    <img id="previewImage" 
                                         src="" 
                                         alt="Предварительный просмотр" 
                                         class="rounded me-3"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1" id="previewName">{{ $category->name }}</h6>
                                        <p class="text-muted small mb-0" id="previewDescription">{{ $category->description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Статус активности -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on text-success"></i> Категория активна
                                </label>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Неактивные категории не отображаются в мини-приложении
                            </div>
                        </div>

                        <!-- Информация о товарах в категории -->
                        @if($category->products->count() > 0)
                        <div class="alert alert-info">
                            <h6>Информация о товарах</h6>
                            <p class="mb-2">
                                В этой категории находится <strong>{{ $category->products->count() }}</strong> товаров.
                                Активных товаров: <strong>{{ $category->activeProducts->count() }}</strong>.
                            </p>
                            @if($category->activeProducts->count() > 0)
                            <small class="text-muted">
                                При деактивации категории товары останутся, но категория не будет отображаться в мини-приложении.
                            </small>
                            @endif
                        </div>
                        @endif

                        <!-- Кнопки управления -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                Сохранить изменения
                            </button>
                            <a href="{{ route('bot.categories.show', [$telegramBot, $category]) }}" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <a href="{{ route('bot.categories.index', $telegramBot) }}" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> К списку категорий
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Опасная зона -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">Опасная зона</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Удалить категорию</h6>
                            <p class="text-muted small mb-0">
                                Удаление категории необратимо. 
                                @if($category->products->count() > 0)
                                    <strong>Внимание:</strong> В категории есть товары, удаление невозможно.
                                @else
                                    Категория будет удалена безвозвратно.
                                @endif
                            </p>
                        </div>
                        <div>
                            @if($category->products->count() > 0)
                                <button class="btn btn-outline-danger" disabled>
                                    Нельзя удалить
                                </button>
                            @else
                                <button class="btn btn-outline-danger" onclick="deleteCategory()">
                                    Удалить категорию
                                </button>
                            @endif
                        </div>
                    </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoUrlInput = document.getElementById('photo_url');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    const previewName = document.getElementById('previewName');
    const previewDescription = document.getElementById('previewDescription');

    const originalPhotoUrl = '{{ $category->photo_url }}';
    const originalName = '{{ $category->name }}';
    const originalDescription = '{{ $category->description }}';

    function updatePreview() {
        const photoUrl = photoUrlInput.value.trim();
        const name = nameInput.value.trim() || 'Название категории';
        const description = descriptionInput.value.trim() || 'Описание категории';

        // Проверяем, есть ли изменения
        const hasChanges = photoUrl !== originalPhotoUrl || 
                          name !== originalName || 
                          description !== originalDescription;

        previewName.textContent = name;
        previewDescription.textContent = description;

        if (hasChanges && photoUrl && isValidUrl(photoUrl)) {
            previewImage.src = photoUrl;
            previewImage.onerror = function() {
                photoPreview.style.display = 'none';
            };
            previewImage.onload = function() {
                photoPreview.style.display = 'block';
            };
        } else {
            photoPreview.style.display = 'none';
        }
    }

    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Обновление превью при изменении полей
    photoUrlInput.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    // Валидация формы
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            nameInput.focus();
            alert('Пожалуйста, укажите название категории');
            return false;
        }

        const photoUrl = photoUrlInput.value.trim();
        if (photoUrl && !isValidUrl(photoUrl)) {
            e.preventDefault();
            photoUrlInput.focus();
            alert('Пожалуйста, укажите корректную ссылку на изображение');
            return false;
        }
    });

    // Автоматическое скрытие алертов
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

function deleteCategory() {
    if (confirm(`Вы уверены, что хотите удалить категорию "{{ $category->name }}"?\n\nЭто действие необратимо!`)) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush