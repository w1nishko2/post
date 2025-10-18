@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $telegramBot->bot_name }}</h6>
                                <small class="text-muted">Редактирование товара в магазине</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Редактировать товар: {{ $product->name }}</h5>
                    <div>
                        @if(isset($telegramBot))
                            <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> Просмотр
                            </a>
                            <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Назад к товарам
                            </a>
                        @else
                            <a href="{{ route('products.select-bot') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Выбрать магазин
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if(isset($telegramBot))
                        <form method="POST" action="{{ route('bot.products.update', [$telegramBot, $product]) }}">
                            @csrf
                            @method('PUT')
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">Товар не привязан к магазину</p>
                            <a href="{{ route('products.select-bot') }}" class="btn btn-primary">Выбрать магазин</a>
                        </div>
                    @endif
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название товара <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $product->name) }}" 
                                           placeholder="Например: Тормозные колодки передние" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="article" class="form-label">Артикул <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('article') is-invalid @enderror" 
                                           id="article" name="article" value="{{ old('article', $product->article) }}" 
                                           placeholder="BP001" required>
                                    @error('article')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id">
                                <option value="">Выберите категорию (необязательно)</option>
                                @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Если не выберете категорию, товар будет без категории</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Подробное описание товара...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="photo_url" class="form-label">Ссылка на фотографию</label>
                            <input type="url" class="form-control @error('photo_url') is-invalid @enderror" 
                                   id="photo_url" name="photo_url" value="{{ old('photo_url', $product->photo_url) }}" 
                                   placeholder="https://example.com/photo.jpg">
                            @error('photo_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($product->photo_url)
                                <div class="mt-2" id="current-photo">
                                    <img src="{{ $product->photo_url }}" class="img-thumbnail" 
                                         style="max-width: 200px; max-height: 200px;" 
                                         onerror="this.style.display='none'">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="specifications_text" class="form-label">Характеристики товара</label>
                            <textarea class="form-control @error('specifications') is-invalid @enderror" 
                                      id="specifications_text" name="specifications_text" rows="6" 
                                      placeholder="Введите каждую характеристику с новой строки:&#10;Материал: Пластик&#10;Цвет: Черный&#10;Вес: 500 г&#10;Гарантия: 1 год">{{ old('specifications_text', is_array($product->specifications) ? implode("\n", $product->specifications) : '') }}</textarea>
                            <div class="form-text">Каждую характеристику вводите с новой строки</div>
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Количество в наличии <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity', $product->quantity) }}" 
                                           min="0" max="999999" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Цена за штуку (₽) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $product->price) }}" 
                                           step="0.01" min="0" max="999999.99" 
                                           placeholder="2500.00" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="markup_percentage" class="form-label">Наценка (%)</label>
                                    <input type="number" class="form-control @error('markup_percentage') is-invalid @enderror" 
                                           id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', $product->markup_percentage) }}" 
                                           step="0.01" min="0" max="1000" 
                                           placeholder="10.00">
                                    @error('markup_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Наценка к базовой цене</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Товар активен (доступен для продажи)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Блок расчёта цены с наценкой -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Расчёт цены с наценкой</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <small class="text-muted">Базовая цена:</small>
                                                <div id="base-price-display" class="fw-bold">{{ number_format($product->price, 0, ',', ' ') }} ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Наценка:</small>
                                                <div id="markup-display" class="fw-bold text-info">{{ $product->markup_percentage }}% ({{ number_format($product->markup_amount, 0, ',', ' ') }} ₽)</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Итоговая цена:</small>
                                                <div id="final-price-display" class="fw-bold text-success fs-5">{{ number_format($product->price_with_markup, 0, ',', ' ') }} ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Общая стоимость:</small>
                                                <div id="total-value-display" class="fw-bold text-primary">{{ number_format($product->price_with_markup * $product->quantity, 0, ',', ' ') }} ₽</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($telegramBot))
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Отмена
                                    </a>
                                    <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i> Просмотр
                                    </a>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-danger me-2" onclick="deleteProduct()">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Сохранить изменения
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>

                    @if(isset($telegramBot))
                        <!-- Скрытая форма для удаления -->
                        <form id="delete-form" method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предварительный просмотр изображения
    const photoUrlInput = document.getElementById('photo_url');
    
    if (photoUrlInput) {
        photoUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            let previewContainer = document.getElementById('photo-preview');
            
            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.id = 'photo-preview';
                previewContainer.className = 'mt-2';
                this.parentNode.appendChild(previewContainer);
            }
            
            if (url && url.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
                previewContainer.innerHTML = '<img src="' + url + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" onerror="this.style.display=\'none\'">';
            } else {
                previewContainer.innerHTML = '';
            }
        });
    }

    // Автоматическое форматирование цены
    const priceInput = document.getElementById('price');
    const markupInput = document.getElementById('markup_percentage');
    const quantityInput = document.getElementById('quantity');
    
    if (priceInput) {
        priceInput.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
            updatePriceCalculation();
        });
    }

    if (markupInput) {
        markupInput.addEventListener('input', updatePriceCalculation);
    }

    // Функция обновления расчёта цены с наценкой
    function updatePriceCalculation() {
        const basePrice = parseFloat(priceInput.value) || 0;
        const markupPercentage = parseFloat(markupInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        const markupAmount = basePrice * (markupPercentage / 100);
        const finalPrice = basePrice + markupAmount;
        const totalValue = finalPrice * quantity;
        
        // Обновляем отображение
        document.getElementById('base-price-display').textContent = basePrice.toLocaleString('ru-RU') + ' ₽';
        document.getElementById('markup-display').textContent = markupPercentage + '% (' + markupAmount.toLocaleString('ru-RU') + ' ₽)';
        document.getElementById('final-price-display').textContent = finalPrice.toLocaleString('ru-RU') + ' ₽';
        document.getElementById('total-value-display').textContent = totalValue.toLocaleString('ru-RU') + ' ₽';
    }
    
    if (quantityInput && priceInput) {
        quantityInput.addEventListener('input', updatePriceCalculation);
        priceInput.addEventListener('input', updatePriceCalculation);
        updatePriceCalculation(); // Инициализация
    }

    // Функция удаления товара
    window.deleteProduct = function() {
        if (confirm('Вы уверены, что хотите удалить товар "{{ $product->name }}"?\n\nЭто действие нельзя отменить!')) {
            document.getElementById('delete-form').submit();
        }
    };
});
</script>
@endpush