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

    <!-- Форма создания товара -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-mb-0">
                <i class="fas fa-plus admin-me-2"></i>
                Добавить новый товар
            </h5>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('bot.products.store', $telegramBot) }}">
                @csrf
                
                <div class="admin-row">
                    <div class="admin-col admin-col-8">
                        <!-- Основная информация -->
                        <div class="admin-form-group">
                            <label for="name" class="admin-form-label required">Название товара</label>
                            <input type="text" class="admin-form-control @error('name') admin-border-danger @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required 
                                   placeholder="Например: iPhone 14 Pro Max 256GB">
                            @error('name')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">Описание товара</label>
                            <textarea class="admin-form-control admin-textarea @error('description') admin-border-danger @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="Подробное описание товара, его характеристики и особенности">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-row">
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-group">
                                    <label for="article" class="admin-form-label">Артикул</label>
                                    <input type="text" class="admin-form-control @error('article') admin-border-danger @enderror" 
                                           id="article" name="article" value="{{ old('article') }}" 
                                           placeholder="ART-001">
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
                                        <option value="">Выберите категорию</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <div id="photo-preview" class="admin-mb-3" style="display: none;">
                                <div style="width: 100%; height: 200px; border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden;">
                                    <img id="preview-image" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="photo_url" class="admin-form-label">URL изображения</label>
                                <input type="url" class="admin-form-control @error('photo_url') admin-border-danger @enderror" 
                                       id="photo_url" name="photo_url" value="{{ old('photo_url') }}" 
                                       placeholder="https://example.com/image.jpg">
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
                                   id="price" name="price" value="{{ old('price') }}" required 
                                   min="0" step="0.01" placeholder="0.00">
                            @error('price')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="markup_percentage" class="admin-form-label">Наценка (%)</label>
                            <input type="number" class="admin-form-control @error('markup_percentage') admin-border-danger @enderror" 
                                   id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', 0) }}" 
                                   min="0" step="0.1" placeholder="0">
                            @error('markup_percentage')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="quantity" class="admin-form-label required">Количество</label>
                            <input type="number" class="admin-form-control @error('quantity') admin-border-danger @enderror" 
                                   id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required 
                                   min="0" placeholder="1">
                            @error('quantity')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Расчет цены -->
                <div class="admin-card admin-mb-4" style="background-color: var(--color-light-gray);">
                    <div class="admin-card-body">
                        <h6 class="admin-mb-3">Расчет стоимости:</h6>
                        <div class="admin-row">
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Базовая цена:</div>
                                    <div class="admin-fw-bold" id="base-price-display">0 ₽</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Наценка:</div>
                                    <div class="admin-fw-bold" id="markup-display">0% (0 ₽)</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Итоговая цена:</div>
                                    <div class="admin-fw-bold admin-text-success" id="final-price-display">0 ₽</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Общая стоимость:</div>
                                    <div class="admin-fw-bold admin-text-info" id="total-value-display">0 ₽</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Дополнительные настройки -->
                <div class="admin-form-group">
                    <div class="admin-form-check">
                        <input type="checkbox" class="admin-form-check-input" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label for="is_active" class="admin-form-check-label">Товар активен (доступен для продажи)</label>
                    </div>
                </div>

                <!-- Скрытые поля для галереи -->
                <input type="hidden" id="photos_gallery" name="photos_gallery" value="{{ old('photos_gallery', '[]') }}">
                <input type="hidden" id="main_photo_index" name="main_photo_index" value="{{ old('main_photo_index', 0) }}">

                <!-- Кнопки действий -->
                <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="admin-btn">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        Отмена
                    </a>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Создать товар
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предварительный просмотр изображения
    const photoUrlInput = document.getElementById('photo_url');
    const photosPreview = document.getElementById('photos-preview');
    
    if (photoUrlInput) {
        photoUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            let previewContainer = document.getElementById('photo-preview');
            let previewImage = document.getElementById('preview-image');
            
            if (!previewContainer || !previewImage) return;
            
            if (url) {
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

    // Автоматическое форматирование цены и расчёт
    const priceInput = document.getElementById('price');
    const markupInput = document.getElementById('markup_percentage');
    const quantityInput = document.getElementById('quantity');
    
    function updatePriceCalculation() {
        const basePrice = parseFloat(priceInput?.value) || 0;
        const markupPercentage = parseFloat(markupInput?.value) || 0;
        const quantity = parseInt(quantityInput?.value) || 0;
        
        const markupAmount = basePrice * (markupPercentage / 100);
        const finalPrice = basePrice + markupAmount;
        const totalValue = finalPrice * quantity;
        
        // Обновляем отображение
        const basePriceDisplay = document.getElementById('base-price-display');
        const markupDisplay = document.getElementById('markup-display');
        const finalPriceDisplay = document.getElementById('final-price-display');
        const totalValueDisplay = document.getElementById('total-value-display');
        
        if (basePriceDisplay) basePriceDisplay.textContent = basePrice.toLocaleString('ru-RU') + ' ₽';
        if (markupDisplay) markupDisplay.textContent = markupPercentage + '% (' + markupAmount.toLocaleString('ru-RU') + ' ₽)';
        if (finalPriceDisplay) finalPriceDisplay.textContent = finalPrice.toLocaleString('ru-RU') + ' ₽';
        if (totalValueDisplay) totalValueDisplay.textContent = totalValue.toLocaleString('ru-RU') + ' ₽';
    }
    
    if (priceInput) {
        priceInput.addEventListener('input', updatePriceCalculation);
        priceInput.addEventListener('blur', function() {
            const value = parseFloat(this.value) || 0;
            this.value = value.toFixed(2);
            updatePriceCalculation();
        });
    }

    if (markupInput) {
        markupInput.addEventListener('input', updatePriceCalculation);
    }
    
    if (quantityInput) {
        quantityInput.addEventListener('input', updatePriceCalculation);
    }
    
    // Инициализация расчёта
    updatePriceCalculation();
    
    // Проверяем предзаполненные значения при загрузке
    if (photoUrlInput && photoUrlInput.value) {
        photoUrlInput.dispatchEvent(new Event('blur'));
    }
});
</script>
@endsection