@extends('layouts.app')

@section('content')
<div class="admin-container">
    @if ($errors->any())
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            <strong>Пожалуйста, исправьте ошибки:</strong>
            <ul class="admin-mb-0 admin-mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                <div class="admin-d-flex admin-gap-sm">
                    <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-eye admin-me-2"></i>
                        Просмотр
                    </a>
                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        К списку
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Форма редактирования товара -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-edit admin-me-2"></i>
                Редактировать товар: {{ $product->name }}
            </h5>
            <button class="admin-btn admin-btn-sm admin-btn-outline-danger" onclick="deleteProduct()">
                <i class="fas fa-trash admin-me-1"></i>
                Удалить товар
            </button>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('bot.products.update', [$telegramBot, $product]) }}">
                @csrf
                @method('PUT')
                
                <div class="admin-row">
                    <div class="admin-col admin-col-8">
                        <!-- Основная информация -->
                        <div class="admin-form-group">
                            <label for="name" class="admin-form-label required">Название товара</label>
                            <input type="text" class="admin-form-control @error('name') admin-border-danger @enderror" 
                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">Описание товара</label>
                            <textarea class="admin-form-control admin-textarea @error('description') admin-border-danger @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-row">
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-group">
                                    <label for="article" class="admin-form-label">Артикул</label>
                                    <input type="text" class="admin-form-control @error('article') admin-border-danger @enderror" 
                                           id="article" name="article" value="{{ old('article', $product->article) }}">
                                    @error('article')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-group">
                                    <label for="category_id" class="admin-form-label">Категория</label>
                                    <select class="admin-form-control admin-select @error('category_id') admin-border-danger @enderror" 
                                            id="category_id" name="category_id">
                                        <option value="">Без категории</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-col admin-col-4">
                        <!-- Изображение товара -->
                        <div class="admin-form-group">
                            <label class="admin-form-label">Изображение товара</label>
                            
                            @if($product->photo_url)
                                <div class="admin-mb-3">
                                    <div style="width: 100%; height: 200px; border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden;">
                                        <img src="{{ $product->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            @endif
                            
                            <div id="photo-preview" class="admin-mb-3" style="display: none;">
                                <div style="width: 100%; height: 200px; border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden;">
                                    <img id="preview-image" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="photo_url" class="admin-form-label">URL изображения</label>
                                <input type="url" class="admin-form-control @error('photo_url') admin-border-danger @enderror" 
                                       id="photo_url" name="photo_url" value="{{ old('photo_url', $product->photo_url) }}">
                                @error('photo_url')
                                    <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Цена и количество -->
                <div class="admin-row">
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="price" class="admin-form-label required">Цена (₽)</label>
                            <input type="number" class="admin-form-control @error('price') admin-border-danger @enderror" 
                                   id="price" name="price" value="{{ old('price', $product->price) }}" required 
                                   min="0" step="0.01">
                            @error('price')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="markup_percentage" class="admin-form-label">Наценка (%)</label>
                            <input type="number" class="admin-form-control @error('markup_percentage') admin-border-danger @enderror" 
                                   id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', $product->markup_percentage) }}" 
                                   min="0" step="0.1">
                            @error('markup_percentage')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="quantity" class="admin-form-label required">Количество</label>
                            <input type="number" class="admin-form-control @error('quantity') admin-border-danger @enderror" 
                                   id="quantity" name="quantity" value="{{ old('quantity', $product->quantity) }}" required 
                                   min="0">
                            @error('quantity')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Дополнительные настройки -->
                <div class="admin-form-group">
                    <div class="admin-form-check">
                        <input type="checkbox" class="admin-form-check-input" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="admin-form-check-label">Товар активен (доступен для продажи)</label>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                    <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" class="admin-btn">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        Отмена
                    </a>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" class="admin-d-none">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предварительный просмотр изображения
    const photoUrlInput = document.getElementById('photo_url');
    
    if (photoUrlInput) {
        photoUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            let previewContainer = document.getElementById('photo-preview');
            let previewImage = document.getElementById('preview-image');
            
            if (!previewContainer || !previewImage) return;
            
            if (url && url !== '{{ $product->photo_url }}') {
                previewImage.src = url;
                previewContainer.style.display = 'block';
                
                previewImage.onerror = function() {
                    previewContainer.style.display = 'none';
                };
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

    // Функция удаления товара
    window.deleteProduct = function() {
        if (confirm('Вы уверены, что хотите удалить товар "{{ $product->name }}"?\n\nЭто действие нельзя отменить!')) {
            document.getElementById('delete-form').submit();
        }
    };
});
</script>
@endsection