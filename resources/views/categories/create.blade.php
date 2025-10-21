@extends('layouts.app')

@section('content')
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="{{ route('home') }}">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="{{ route('bot.products.index', $telegramBot) }}">
            <i class="fas fa-boxes"></i> Товары
        </a>
        <a class="admin-nav-pill active" href="{{ route('bot.categories.index', $telegramBot) }}">
            <i class="fas fa-folder"></i> Категории
        </a>
    </div>

    <!-- Хлебные крошки -->
    <div class="admin-breadcrumb admin-mb-4">
        <a href="{{ route('bot.categories.index', $telegramBot) }}" class="admin-breadcrumb-link">
            <i class="fas fa-folder admin-me-1"></i> Категории
        </a>
        <span class="admin-breadcrumb-separator">></span>
        <span class="admin-breadcrumb-current">Создание категории</span>
    </div>

    <!-- Контент в адаптивной сетке -->
    <div class="admin-row">
        <div class="admin-col admin-col-12">
            <!-- Форма создания категории -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создание новой категории
                    </h5>
                    <p class="admin-text-muted admin-mb-0 admin-mt-2">
                        Создайте категорию для группировки товаров в вашем магазине
                    </p>
                </div>

                <div class="admin-card-body">
                    <form action="{{ route('bot.categories.store', $telegramBot) }}" method="POST" id="categoryForm">
                        @csrf

                        <!-- Основная информация в адаптивной сетке -->
                        <div class="admin-row">
                            <div class="admin-col admin-col-12 admin-col-md-6">
                                <!-- Название категории -->
                                <div class="admin-form-group">
                                    <label for="name" class="admin-form-label required">
                                        Название категории
                                    </label>
                                    <input type="text" 
                                           class="admin-form-control @error('name') admin-form-error @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Например: Электроника, Одежда, Книги..."
                                           maxlength="100"
                                           required>
                                    @error('name')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                    <div class="admin-form-text">
                                        Краткое и понятное название для ваших покупателей
                                    </div>
                                </div>
                            </div>
                            
                            <div class="admin-col admin-col-12 admin-col-md-6">
                                <!-- Фотография категории -->
                                <div class="admin-form-group">
                                    <label for="photo_url" class="admin-form-label">
                                        Ссылка на фотографию
                                    </label>
                                    <input type="url" 
                                           class="admin-form-control @error('photo_url') admin-form-error @enderror" 
                                           id="photo_url" 
                                           name="photo_url" 
                                           value="{{ old('photo_url') }}" 
                                           placeholder="https://example.com/category-image.jpg">
                                    @error('photo_url')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                    <div class="admin-form-text">
                                        Рекомендуемый размер: 300x300px. JPG, PNG
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Описание категории -->
                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">
                                Описание категории
                            </label>
                            <textarea class="admin-form-control admin-textarea @error('description') admin-form-error @enderror" 
                                      id="description" 
                                      name="description" 
                                      placeholder="Краткое описание категории (необязательно)"
                                      maxlength="500">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                            <div class="admin-form-text">
                                Дополнительная информация о товарах в этой категории
                            </div>
                        </div>

                        <!-- Предварительный просмотр фото -->
                        <div class="admin-form-group" id="photoPreview" style="display: none;">
                            <label class="admin-form-label">
                                Предварительный просмотр
                            </label>
                            <div class="admin-card admin-bg-light">
                                <div class="admin-card-body">
                                    <div class="admin-d-flex admin-align-items-center admin-gap-md">
                                        <img id="previewImage" 
                                             src="" 
                                             alt="Предварительный просмотр" 
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md);">
                                        <div class="admin-flex-1">
                                            <h6 class="admin-mb-1" id="previewName">Название категории</h6>
                                            <p class="admin-text-muted admin-mb-0" id="previewDescription">Описание категории</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Статус активности -->
                        <div class="admin-form-group">
                            <div class="admin-form-check">
                                <input class="admin-form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="admin-form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on admin-text-success admin-me-1"></i> 
                                    Категория активна
                                </label>
                            </div>
                            <div class="admin-form-text">
                                Неактивные категории не отображаются в мини-приложении
                            </div>
                        </div>

                        <!-- Кнопки управления -->
                        <div class="admin-d-flex admin-gap-md admin-flex-wrap">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-plus admin-me-2"></i>
                                Создать категорию
                            </button>
                            <a href="{{ route('bot.categories.index', $telegramBot) }}" class="admin-btn">
                                <i class="fas fa-arrow-left admin-me-2"></i>
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Подсказки -->
            <div class="admin-card admin-mt-4">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-lightbulb admin-me-2"></i>
                        Полезные советы
                    </h6>
                </div>
                <div class="admin-card-body">
                    <div class="admin-row">
                        <div class="admin-col admin-col-12 admin-col-md-6">
                            <h6 class="admin-text-info admin-mb-2">
                                <i class="fas fa-tag admin-me-1"></i>
                                Названия категорий
                            </h6>
                            <ul class="admin-text-muted admin-mb-3" style="padding-left: 20px;">
                                <li>Используйте простые и понятные названия</li>
                                <li>Избегайте слишком длинных названий</li>
                                <li>Думайте о том, как клиент будет искать товар</li>
                            </ul>
                        </div>
                        <div class="admin-col admin-col-12 admin-col-md-6">
                            <h6 class="admin-text-success admin-mb-2">
                                <i class="fas fa-image admin-me-1"></i>
                                Изображения категорий
                            </h6>
                            <ul class="admin-text-muted admin-mb-0" style="padding-left: 20px;">
                                <li>Рекомендуемый размер: 300x300 пикселей</li>
                                <li>Используйте качественные изображения</li>
                                <li>Изображение должно отражать суть категории</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

    function updatePreview() {
        const photoUrl = photoUrlInput.value.trim();
        const name = nameInput.value.trim() || 'Название категории';
        const description = descriptionInput.value.trim() || 'Описание категории';

        previewName.textContent = name;
        previewDescription.textContent = description;

        if (photoUrl && isValidUrl(photoUrl)) {
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
});
</script>
@endpush