@extends('layouts.app')

@section('content')
<div class="admin-container">
    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="{{ route('home') }}">
            <i class="fas fa-robot"></i>
            Мои боты
        </a>
        <a class="admin-nav-pill" href="{{ route('bot.products.index', $telegramBot) }}">
            <i class="fas fa-box"></i>
            Товары
        </a>
        <a class="admin-nav-pill active" href="{{ route('bot.categories.index', $telegramBot) }}">
            <i class="fas fa-folder"></i>
            Категории
        </a>
    </div>

    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <div>
                    <h5 class="admin-mb-1">
                        <i class="fas fa-robot admin-text-primary"></i>
                        {{ $telegramBot->bot_name }}
                    </h5>
                    <p class="admin-text-muted admin-mb-0">
                        <i class="fas fa-at"></i>
                        @{{ $telegramBot->bot_username }}
                    </p>
                </div>
                @if($telegramBot->hasMiniApp())
                <div>
                    <a href="{{ $telegramBot->getMiniAppUrl() }}" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                        Открыть Mini App
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Форма редактирования категории -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-mb-1">
                <i class="fas fa-edit"></i>
                Редактирование категории "{{ $category->name }}"
            </h5>
            <small class="admin-text-muted">Внесите изменения в информацию о категории</small>
        </div>

        <div class="admin-card-body">
            <form action="{{ route('bot.categories.update', [$telegramBot, $category]) }}" method="POST" id="categoryForm">
                @csrf
                @method('PUT')

                <!-- Название категории -->
                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">
                        <i class="fas fa-tag admin-me-1"></i>
                        Название категории <span class="admin-text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="admin-form-control @error('name') admin-is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $category->name) }}" 
                           placeholder="Например: Электроника, Одежда, Книги..."
                           maxlength="100"
                           required>
                    @error('name')
                        <div class="admin-form-error">{{ $message }}</div>
                    @enderror
                    <small class="admin-text-muted">
                        <i class="fas fa-info-circle"></i>
                        Краткое и понятное название для ваших покупателей
                    </small>
                </div>

                <!-- Описание категории -->
                <div class="admin-form-group">
                    <label for="description" class="admin-form-label">
                        <i class="fas fa-align-left admin-me-1"></i>
                        Описание категории
                    </label>
                    <textarea class="admin-form-control @error('description') admin-is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3"
                              placeholder="Краткое описание категории (необязательно)"
                              maxlength="500">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="admin-form-error">{{ $message }}</div>
                    @enderror
                    <small class="admin-text-muted">
                        <i class="fas fa-info-circle"></i>
                        Дополнительная информация о товарах в этой категории
                    </small>
                </div>

                        <!-- Фотография категории -->
                        <div class="admin-form-group">
                            <label for="photo" class="admin-form-label">
                                <i class="fas fa-image admin-me-1"></i>
                                Фотография категории
                            </label>
                            
                            <!-- Текущее изображение -->
                            @if($category->photo_url)
                            <div id="current-category-photo" style="margin-bottom: 1rem;">
                                <div style="position: relative; width: 200px; height: 200px; border-radius: 8px; overflow: hidden; border: 2px solid #e2e8f0;">
                                    <img src="{{ asset('storage/' . ltrim($category->photo_url, '/')) }}" 
                                         style="width: 100%; height: 100%; object-fit: cover;" 
                                         alt="{{ $category->name }}"
                                         onerror="this.parentElement.innerHTML='<div style=\'width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; background: #f7fafc;\'>Изображение недоступно</div>'">
                                    <button type="button" onclick="removeCurrentCategoryPhoto()" style="position: absolute; top: 8px; right: 8px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 8px; font-size: 0.75rem;">
                                        <i class="fas fa-check-circle"></i> Текущее фото
                                    </div>
                                </div>
                                <p class="admin-text-muted admin-mt-2" style="font-size: 0.875rem;">
                                    <i class="fas fa-info-circle"></i> Чтобы заменить, загрузите новое изображение ниже
                                </p>
                            </div>
                            @endif
                            
                            <!-- Зона загрузки нового фото -->
                            <div id="category-photo-dropzone" style="border: 2px dashed #cbd5e0; border-radius: 8px; padding: 1.5rem; text-align: center; background-color: #f7fafc; transition: all 0.3s ease; cursor: pointer; {{ $category->photo_url ? '' : '' }}">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #4299e1; margin-bottom: 0.5rem;"></i>
                                <p style="margin-bottom: 0.5rem;"><strong>{{ $category->photo_url ? 'Загрузить новое изображение' : 'Перетащите изображение сюда' }}</strong></p>
                                <p class="admin-text-muted" style="font-size: 0.875rem;">или</p>
                                <button type="button" class="admin-btn admin-btn-outline-primary admin-btn-sm" id="select-category-photo-btn">
                                    <i class="fas fa-folder-open admin-me-1"></i>
                                    Выбрать файл
                                </button>
                                <input type="file" id="photo" name="photo" accept="image/*,.heic,.heif" hidden>
                                <p class="admin-text-muted admin-mt-2" style="font-size: 0.75rem;">
                                    Максимальный размер: 10MB. Поддерживаемые форматы: JPEG, PNG, GIF, WebP, HEIC, HEIF
                                </p>
                            </div>
                            
                            @error('photo')
                                <div class="admin-form-error admin-mt-2">{{ $message }}</div>
                            @enderror
                            
                            <!-- Превью нового изображения -->
                            <div id="category-photo-preview" style="display: none; margin-top: 1rem;">
                                <div style="position: relative; width: 200px; height: 200px; border-radius: 8px; overflow: hidden; border: 2px solid #4299e1;">
                                    <img id="category-preview-img" src="" style="width: 100%; height: 100%; object-fit: cover;" alt="Превью">
                                    <button type="button" onclick="removeCategoryPhoto()" style="position: absolute; top: 8px; right: 8px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(135deg, #4299e1 0%, #667eea 100%); color: white; padding: 8px; font-size: 0.75rem;">
                                        <i class="fas fa-upload"></i> Новое фото (будет загружено)
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
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="admin-form-check-label" for="is_active">
                            <i class="fas fa-toggle-on admin-text-success"></i> Категория активна
                        </label>
                    </div>
                    <small class="admin-text-muted">
                        <i class="fas fa-info-circle"></i> Неактивные категории не отображаются в мини-приложении
                    </small>
                </div>

                <!-- Информация о товарах в категории -->
                @if($category->products->count() > 0)
                <div class="admin-alert admin-alert-info">
                    <h6><i class="fas fa-box"></i> Информация о товарах</h6>
                    <p class="admin-mb-2">
                        В этой категории находится <strong>{{ $category->products->count() }}</strong> товаров.
                        Активных товаров: <strong>{{ $category->activeProducts->count() }}</strong>.
                    </p>
                    @if($category->activeProducts->count() > 0)
                    <small class="admin-text-muted">
                        При деактивации категории товары останутся, но категория не будет отображаться в мини-приложении.
                    </small>
                    @endif
                </div>
                @endif

                <!-- Кнопки управления -->
                <div class="admin-d-flex admin-gap-3">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save"></i>
                        Сохранить изменения
                    </button>
                    <a href="{{ route('bot.categories.index', $telegramBot) }}" class="admin-btn admin-btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Опасная зона -->
    <div class="admin-card admin-mt-4" style="border: 2px solid #ef4444;">
        <div class="admin-card-header" style="background: #fef2f2; border-bottom: 2px solid #ef4444;">
            <h6 class="admin-mb-0 admin-text-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Опасная зона
            </h6>
        </div>
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <div>
                    <h6 class="admin-text-danger admin-mb-1">Удалить категорию</h6>
                    <p class="admin-text-muted admin-mb-0" style="font-size: 0.875rem;">
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
                        <button class="admin-btn admin-btn-outline-danger" disabled>
                            <i class="fas fa-ban"></i>
                            Нельзя удалить
                        </button>
                    @else
                        <button class="admin-btn admin-btn-outline-danger" onclick="deleteCategory()">
                            <i class="fas fa-trash"></i>
                            Удалить категорию
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" action="{{ route('bot.categories.destroy', [$telegramBot, $category]) }}" class="admin-d-none">
    @csrf
    @method('DELETE')
</form>

<script>
// Функция удаления текущего фото категории
window.removeCurrentCategoryPhoto = function() {
    if (confirm('Вы действительно хотите удалить текущее изображение?')) {
        const currentPhoto = document.getElementById('current-category-photo');
        if (currentPhoto) {
            currentPhoto.remove();
        }
        // Можно добавить скрытое поле для отправки запроса на удаление
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_photo';
        input.value = '1';
        document.getElementById('categoryForm').appendChild(input);
    }
};

// Функция удаления нового фото категории
window.removeCategoryPhoto = function() {
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('category-photo-preview');
    const dropzone = document.getElementById('category-photo-dropzone');
    
    photoInput.value = '';
    preview.style.display = 'none';
    dropzone.style.display = 'block';
};

document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const selectBtn = document.getElementById('select-category-photo-btn');
    const dropzone = document.getElementById('category-photo-dropzone');
    const preview = document.getElementById('category-photo-preview');
    const previewImg = document.getElementById('category-preview-img');
    const nameInput = document.getElementById('name');
    const form = document.getElementById('categoryForm');

    // Изменяем enctype формы для загрузки файлов
    form.setAttribute('enctype', 'multipart/form-data');

    // Обработка клика на кнопку выбора файла
    selectBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Предотвращаем всплытие события к dropzone
        photoInput.click();
    });

    // Обработка клика на зону dropzone
    dropzone.addEventListener('click', function(e) {
        // Проверяем, что клик был именно на dropzone, а не на кнопке внутри
        if (e.target === dropzone || e.target.closest('#category-photo-dropzone') === dropzone) {
            photoInput.click();
        }
    });

    // Обработка выбора файла
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFile(file);
        }
    });

    // Drag & Drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#4299e1';
        this.style.backgroundColor = '#ebf8ff';
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#cbd5e0';
        this.style.backgroundColor = '#f7fafc';
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#cbd5e0';
        this.style.backgroundColor = '#f7fafc';
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;
            handleFile(file);
        } else {
            alert('Пожалуйста, загрузите изображение');
        }
    });

    // Обработка файла
    function handleFile(file) {
        // Проверка размера (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('Файл слишком большой. Максимальный размер: 10MB');
            photoInput.value = '';
            return;
        }

        // Проверка типа файла
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif'];
        if (!validTypes.includes(file.type) && !file.name.match(/\.(heic|heif)$/i)) {
            alert('Неподдерживаемый формат файла. Используйте: JPEG, PNG, GIF, WebP, HEIC, HEIF');
            photoInput.value = '';
            return;
        }

        // Показываем превью
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            // Можно скрыть текущее фото для большей ясности
            const currentPhoto = document.getElementById('current-category-photo');
            if (currentPhoto) {
                currentPhoto.style.opacity = '0.5';
            }
        };
        reader.readAsDataURL(file);
    }

    // Валидация формы
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            nameInput.focus();
            alert('Пожалуйста, укажите название категории');
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

// Автоматическое скрытие алертов
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>

<style>
/* Анимации для уведомлений */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

.admin-alert {
    animation: slideDown 0.3s ease;
}
</style>
@endsection