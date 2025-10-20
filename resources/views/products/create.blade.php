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
                            <i class="fas fa-boxes me-2"></i>Мои магазины
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
                                <small class="text-muted">Добавление товара в магазин</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Добавить новый товар</h5>
                    @if(isset($telegramBot))
                        <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к товарам
                        </a>
                    @else
                        <a href="{{ route('products.select-bot') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Выбрать магазин
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if(isset($telegramBot))
                        <form method="POST" action="{{ route('bot.products.store', $telegramBot) }}">
                            @csrf
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">Сначала выберите магазин для добавления товара</p>
                            <a href="{{ route('products.select-bot') }}" class="btn btn-primary">Выбрать магазин</a>
                        </div>
                    @endif
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название товара <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
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
                                           id="article" name="article" value="{{ old('article') }}" 
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
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Если не выберете категорию, товар будет добавлен без категории</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Подробное описание товара...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="photo_url" class="form-label">Ссылка на фотографию</label>
                            <input type="url" class="form-control @error('photo_url') is-invalid @enderror" 
                                   id="photo_url" name="photo_url" value="{{ old('photo_url') }}" 
                                   placeholder="https://example.com/photo.jpg">
                            @error('photo_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="yandex_disk_folder_url" class="form-label">Ссылка на папку с фотографиями (Яндекс.Диск)</label>
                            <input type="url" class="form-control @error('yandex_disk_folder_url') is-invalid @enderror" 
                                   id="yandex_disk_folder_url" name="yandex_disk_folder_url" value="{{ old('yandex_disk_folder_url') }}" 
                                   placeholder="https://disk.yandex.ru/d/hV4dQv-tEeXN_A">
                            <div class="form-text">
                                <i class="fas fa-info-circle text-primary"></i>
                                <strong>Как работает галерея фотографий из Яндекс.Диска:</strong><br>
                                1. Укажите ссылку на публичную папку в Яндекс.Диске<br>
                                2. Нажмите кнопку "Загрузить фотографии из папки"<br>
                                3. Все изображения из папки будут автоматически добавлены в галерею<br>
                                4. Выберите главную фотографию, нажав на неё<br>
                                5. При указании ссылки на папку поле "Ссылка на фотографию" игнорируется
                            </div>
                            @error('yandex_disk_folder_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Кнопка для загрузки фотографий из папки -->
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="load-yandex-photos" disabled>
                                    <i class="fas fa-download"></i> Загрузить фотографии из папки
                                </button>
                                <span class="text-muted ms-2" id="yandex-status"></span>
                            </div>
                        </div>

                        <!-- Блок предпросмотра фотографий -->
                        <div class="mb-3" id="photos-preview" style="display: none;">
                            <label class="form-label">Предпросмотр фотографий</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row" id="photos-grid">
                                        <!-- Фотографии будут добавлены через JavaScript -->
                                    </div>
                                    <div class="form-text mt-3">
                                        <i class="fas fa-info-circle"></i> 
                                        Перетаскивайте фотографии для изменения порядка. 
                                        Нажмите на фотографию, чтобы сделать её главной (обложкой).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Скрытые поля для хранения данных галереи -->
                        <input type="hidden" id="photos_gallery" name="photos_gallery" value="{{ old('photos_gallery') }}">
                        <input type="hidden" id="main_photo_index" name="main_photo_index" value="{{ old('main_photo_index', 0) }}">

                        <div class="mb-3">
                            <label for="specifications_text" class="form-label">Характеристики товара</label>
                            <textarea class="form-control @error('specifications') is-invalid @enderror" 
                                      id="specifications_text" name="specifications_text" rows="6" 
                                      placeholder="Введите каждую характеристику с новой строки:&#10;Материал: Пластик&#10;Цвет: Черный&#10;Вес: 500 г&#10;Гарантия: 1 год">{{ old('specifications_text') ? implode("\n", old('specifications_text')) : '' }}</textarea>
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
                                           id="quantity" name="quantity" value="{{ old('quantity', 0) }}" 
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
                                           id="price" name="price" value="{{ old('price') }}" 
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
                                           id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', 0) }}" 
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
                                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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
                                                <div id="base-price-display" class="fw-bold">0 ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Наценка:</small>
                                                <div id="markup-display" class="fw-bold text-info">0% (0 ₽)</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Итоговая цена:</small>
                                                <div id="final-price-display" class="fw-bold text-success fs-5">0 ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Общая стоимость:</small>
                                                <div id="total-value-display" class="fw-bold text-primary">0 ₽</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($telegramBot))
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('bot.products.index', $telegramBot) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Сохранить товар
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Глобальные переменные для работы с галереей
    let photosGallery = [];
    let mainPhotoIndex = 0;
    
    // Предварительный просмотр изображения
    const photoUrlInput = document.getElementById('photo_url');
    const yandexFolderInput = document.getElementById('yandex_disk_folder_url');
    const loadPhotosBtn = document.getElementById('load-yandex-photos');
    const yandexStatus = document.getElementById('yandex-status');
    const photosPreview = document.getElementById('photos-preview');
    const photosGrid = document.getElementById('photos-grid');
    
    if (photoUrlInput) {
        photoUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            let previewcontainer = document.getElementById('photo-preview');
            
            if (!previewcontainer) {
                previewcontainer = document.createElement('div');
                previewcontainer.id = 'photo-preview';
                previewcontainer.className = 'mt-2';
                this.parentNode.appendChild(previewcontainer);
            }
            
            if (url) {
                // Проверяем, является ли это ссылкой на Яндекс.Диск файл
                if (url.includes('disk.yandex.ru/i/') || url.includes('yadi.sk/i/')) {
                    handleYandexFileUrl(url, previewcontainer);
                } else if (url.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
                    previewcontainer.innerHTML = '<img src="' + url + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" onerror="this.style.display=\'none\'">';
                } else {
                    previewcontainer.innerHTML = '';
                }
            } else {
                previewcontainer.innerHTML = '';
            }
        });
    }

    // Обработка поля Яндекс.Диск
    if (yandexFolderInput) {
        yandexFolderInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url) {
                validateYandexUrl(url);
            } else {
                loadPhotosBtn.disabled = true;
                yandexStatus.textContent = '';
                hidePhotosPreview();
            }
        });
    }

    // Загрузка фотографий из Яндекс.Диска
    if (loadPhotosBtn) {
        loadPhotosBtn.addEventListener('click', function() {
            const folderUrl = yandexFolderInput.value.trim();
            if (folderUrl) {
                loadYandexPhotos(folderUrl);
            }
        });
    }

    // Функция обработки ссылки на отдельный файл Яндекс.Диска
    async function handleYandexFileUrl(url, previewContainer) {
        try {
            previewContainer.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Проверка файла Яндекс.Диска...';
            
            const response = await fetch('/api/yandex-disk/get-file', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ file_url: url })
            });

            const result = await response.json();

            if (result.success) {
                // Заменяем URL на прокси-URL для корректного отображения
                photoUrlInput.value = result.data.display_url;
                previewContainer.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${result.data.display_url}" class="img-thumbnail me-2" style="max-width: 200px; max-height: 200px;">
                        <div>
                            <small class="text-success">
                                <i class="fas fa-check"></i> Файл из Яндекс.Диска: ${result.data.name}
                            </small>
                        </div>
                    </div>
                `;
            } else {
                previewContainer.innerHTML = `<small class="text-danger"><i class="fas fa-times"></i> ${result.message}</small>`;
            }
        } catch (error) {
            console.error('Ошибка обработки файла:', error);
            previewContainer.innerHTML = '<small class="text-danger"><i class="fas fa-times"></i> Ошибка обработки файла</small>';
        }
    }

    // Функция валидации URL Яндекс.Диска
    async function validateYandexUrl(url) {
        try {
            yandexStatus.innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i> Проверка ссылки...';
            loadPhotosBtn.disabled = true;

            const response = await fetch('/api/yandex-disk/validate-folder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ folder_url: url })
            });

            const result = await response.json();

            if (result.success) {
                yandexStatus.innerHTML = '<i class="fas fa-check text-success"></i> Ссылка действительна';
                loadPhotosBtn.disabled = false;
            } else {
                yandexStatus.innerHTML = `<i class="fas fa-times text-danger"></i> ${result.message || 'Ошибка проверки ссылки'}`;
                loadPhotosBtn.disabled = true;
            }
        } catch (error) {
            console.error('Ошибка валидации:', error);
            yandexStatus.innerHTML = '<i class="fas fa-times text-danger"></i> Ошибка проверки ссылки. Проверьте подключение к интернету.';
            loadPhotosBtn.disabled = true;
        }
    }

    // Функция загрузки фотографий из Яндекс.Диска
    async function loadYandexPhotos(folderUrl) {
        try {
            yandexStatus.innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i> Загрузка фотографий...';
            loadPhotosBtn.disabled = true;
            loadPhotosBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';

            const response = await fetch('/api/yandex-disk/get-images', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ folder_url: folderUrl })
            });

            const result = await response.json();

            if (result.success && result.data.images.length > 0) {
                // Сохраняем оригинальные URL (display_url) для сохранения в базу
                photosGallery = result.data.images.map(img => img.display_url);
                mainPhotoIndex = 0;
                
                yandexStatus.innerHTML = `<i class="fas fa-check text-success"></i> Загружено ${result.data.images.length} фотографий`;
                loadPhotosBtn.disabled = false;
                loadPhotosBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Обновить фотографии из папки';
                
                // Сохраняем данные о изображениях для отображения
                window.yandexImages = result.data.images;
                
                console.log('Photos loaded:', photosGallery); // Для отладки
                console.log('Yandex images:', window.yandexImages); // Для отладки
                
                renderPhotosPreview();
                showPhotosPreview();
                updateHiddenFields();
                
                // Очищаем поле single photo URL, так как теперь используется галерея
                photoUrlInput.value = '';
            } else {
                const message = result.message || 'Фотографии не найдены или папка пуста';
                yandexStatus.innerHTML = `<i class="fas fa-exclamation-triangle text-warning"></i> ${message}`;
                loadPhotosBtn.disabled = false;
                loadPhotosBtn.innerHTML = '<i class="fas fa-download"></i> Загрузить фотографии из папки';
                hidePhotosPreview();
            }
        } catch (error) {
            console.error('Ошибка загрузки фотографий:', error);
            yandexStatus.innerHTML = '<i class="fas fa-times text-danger"></i> Ошибка загрузки. Проверьте подключение к интернету.';
            loadPhotosBtn.disabled = false;
            loadPhotosBtn.innerHTML = '<i class="fas fa-download"></i> Загрузить фотографии из папки';
        }
    }

    // Отображение предпросмотра фотографий
    function renderPhotosPreview() {
        photosGrid.innerHTML = '';
        
        photosGallery.forEach((photoUrl, index) => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-4 col-6 mb-3';
            
            // Используем прокси для изображений Яндекс.Диска
            let displayUrl = photoUrl;
            if (photoUrl && photoUrl.includes('downloader.disk.yandex.ru') && !photoUrl.includes('/api/yandex-image-proxy')) {
                displayUrl = `/api/yandex-image-proxy?url=${encodeURIComponent(photoUrl)}`;
            } else if (window.yandexImages && window.yandexImages[index]) {
                // Fallback к preview URL если доступен
                displayUrl = window.yandexImages[index].display_url || window.yandexImages[index].preview || photoUrl;
                if (displayUrl && displayUrl.includes('downloader.disk.yandex.ru') && !displayUrl.includes('/api/yandex-image-proxy')) {
                    displayUrl = `/api/yandex-image-proxy?url=${encodeURIComponent(displayUrl)}`;
                }
            }
            
            col.innerHTML = `
                <div class="position-relative photo-item" data-index="${index}">
                    <img src="${displayUrl}" class="img-thumbnail w-100" style="height: 150px; object-fit: cover; cursor: pointer;" 
                         onclick="setMainPhoto(${index})" onerror="this.parentElement.style.display='none'">
                    <div class="position-absolute top-0 end-0 p-1">
                        <span class="badge bg-secondary">${index + 1}</span>
                        ${index === mainPhotoIndex ? '<span class="badge bg-primary ms-1">Главная</span>' : ''}
                    </div>
                    <div class="position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-50 text-white text-center" style="font-size: 0.75rem;">
                        Нажмите для выбора главной
                    </div>
                </div>
            `;
            photosGrid.appendChild(col);
        });
    }

    // Установка главной фотографии
    window.setMainPhoto = function(index) {
        mainPhotoIndex = index;
        renderPhotosPreview();
        updateHiddenFields();
    };

    // Показать предпросмотр
    function showPhotosPreview() {
        photosPreview.style.display = 'block';
    }

    // Скрыть предпросмотр
    function hidePhotosPreview() {
        photosPreview.style.display = 'none';
        photosGallery = [];
        mainPhotoIndex = 0;
        updateHiddenFields();
    }

    // Обновление скрытых полей
    function updateHiddenFields() {
        const galleryJson = JSON.stringify(photosGallery);
        document.getElementById('photos_gallery').value = galleryJson;
        document.getElementById('main_photo_index').value = mainPhotoIndex;
        
        console.log('Updating hidden fields:'); // Для отладки
        console.log('- photosGallery array:', photosGallery);
        console.log('- galleryJson string:', galleryJson);
        console.log('- mainPhotoIndex:', mainPhotoIndex);
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
});
</script>
@endpush