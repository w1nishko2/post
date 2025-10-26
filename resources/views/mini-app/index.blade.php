@extends('layouts.mini-app')

@section('title', $bot->bot_name . ' - Mini App')

@push('styles')
<meta name="short-name" content="{{ $shortName }}">
@endpush

@section('content')
    <!-- Экран загрузки -->
    <div class="loading-screen" id="loading">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Загрузка магазина...</div>
        </div>
    </div>

    <!-- Основное приложение -->
    <main class="app-main" id="app" style="display: none;">
        <!-- Шапка с поиском -->
        <header class="app-header">
            <div class="search-container">
                <button class="back-button" id="backButton" type="button" onclick="showAllProducts()" style="display: none;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="search-wrapper">
                    <input type="text" class="search-input" id="searchInput" 
                           placeholder="Поиск товаров..." autocomplete="off">
                    <button class="search-button" type="button" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Слайдер категорий -->
        <section class="categories-section" id="categoriesContainer" style="display: none;">
            <div class="swiper categories-swiper">
                <div class="swiper-wrapper" id="categoriesTrack">
                    <!-- Категории будут загружены через JavaScript -->
                </div>
            </div>
        </section>

        <!-- Товары -->
        @if($products->count() > 0)
        <section class="products-section" id="productsContainer">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-store"></i>
                    <span id="productsTitle">Товары магазина</span>
                </h2>
            </div>
            
            <div class="products-grid">
                @foreach($products as $product)
                <article class="product-card" data-product-id="{{ $product->id }}">
                    <div class="product-image">
                        @if($product->main_photo_url)
                            <img src="{{ $product->main_photo_url }}" 
                                 alt="{{ $product->name }}"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
                        @endif
                        <div class="product-badge">
                            <span class="stock-count {{ $product->quantity > 10 ? 'in-stock' : ($product->quantity > 0 ? 'low-stock' : 'out-of-stock') }}">
                                {{ $product->quantity }} шт
                            </span>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">{{ Str::limit($product->name, 35) }}</h3>
                        @if($product->description)
                            <p class="product-description">{{ Str::limit($product->description, 45) }}</p>
                        @endif
                        
                        <div class="product-footer">
                            <div class="product-price">{{ $product->formatted_price_with_markup }}</div>
                            <button class="add-to-cart {{ !$product->isAvailable() ? 'disabled' : '' }}" 
                                    data-product-id="{{ $product->id }}"
                                    {{ !$product->isAvailable() ? 'disabled' : '' }}>
                                @if($product->isAvailable())
                                    <i class="fas fa-plus"></i>
                                @else
                                    <i class="fas fa-times"></i>
                                @endif
                            </button>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            
            <!-- Индикатор загрузки для бесконечной прокрутки -->
            @if($products->hasMorePages())
            <div class="infinite-scroll-loader" id="infiniteScrollLoader" style="display: none;">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Загрузка товаров...</div>
                </div>
            </div>
            <div class="infinite-scroll-trigger" id="infiniteScrollTrigger" data-next-page="{{ $products->currentPage() + 1 }}" data-has-more="true"></div>
            @else
            <div class="infinite-scroll-end" style="text-align: center; padding: 20px; color: #888;">
                <i class="fas fa-check-circle"></i> Все товары загружены
            </div>
            @endif
        </section>
        @else
        <section class="empty-state">
            <div class="empty-content">
                <i class="fas fa-store-slash"></i>
                <h3>Магазин временно пуст</h3>
                <p>Товары скоро появятся!</p>
            </div>
        </section>
        @endif

        <!-- Плавающая корзина -->
        <div class="floating-cart " id="cart-float">
            <button class="cart-button" onclick="showCartModal()" type="button">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count " id="cart-counter">0</span>
            </button>
        </div>
    </main>

    <!-- Модальные окна и панели (остались без изменений для совместимости) -->
    <div class="modal-backdrop" id="panelBackdrop" onclick="closePanel()"></div>

    <div class="side-panel" id="productPanel">
        <div class="panel-header">
            <h3 class="panel-title" id="productPanelTitle">Товар</h3>
            <button class="panel-close" onclick="closePanel()" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="panel-body" id="productPanelBody">
            <div class="loading-content">
                <div class="loading-spinner"></div>
            </div>
        </div>
        
        <div class="panel-footer" id="productPanelFooter" style="display: none;">
            <button type="button" class="btn-primary full-width" id="addToCartFromPanel">
                Добавить в корзину
            </button>
        </div>
    </div>

    <div class="modal" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal-back" onclick="closeProductModal()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h3 class="modal-title" id="productModalTitle">Загрузка...</h3>
                </div>
                <div class="modal-body" id="productModalBody">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
                <div class="modal-footer " id="productModalFooter">
                    <!-- Динамический контент -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="cartModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal-back" onclick="closeCartModal()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h3 class="modal-title" id="cartModalTitle">
                        <i class="fas fa-shopping-cart"></i>
                        Корзина
                    </h3>
                </div>
                <div class="modal-body" id="cartModalBody">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
                <div class="modal-footer " id="cartModalFooter">
                    <!-- Динамический контент -->
                </div>
            </div>
        </div>
    </div>

    <!-- Скрытые данные товаров для JavaScript -->
    <script type="application/json" id="products-data">
        @php
            $productsData = [];
            if (isset($products) && $products->count() > 0) {
                foreach ($products as $product) {
                    // Преобразуем пути изображений в полные URL
                    $photosGallery = [];
                    if ($product->photos_gallery && is_array($product->photos_gallery)) {
                        foreach ($product->photos_gallery as $photoPath) {
                            // Удаляем ведущий слэш если есть и добавляем /storage/
                            $photosGallery[] = asset('storage/' . ltrim($photoPath, '/'));
                        }
                    }
                    
                    $productsData[$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'article' => $product->article,
                        'photo_url' => $product->photo_url,
                        'main_photo_url' => $product->main_photo_url,
                        'photos_gallery' => $photosGallery,
                        'specifications' => $product->specifications,
                        'quantity' => $product->quantity,
                        'price' => $product->price,
                        'formatted_price' => $product->formatted_price_with_markup,
                        'availability_status' => $product->availability_status,
                        'isAvailable' => $product->isAvailable(),
                        'category_id' => (int)$product->category_id
                    ];
                }
            }
        @endphp
        {!! json_encode($productsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@push('scripts')
<script>
        // Инициализация Swiper для категорий
        let categoriesSwiper = null;
        
        // Функция проверки готовности DOM
        function isDOMReady() {
            return document.readyState === 'complete' || document.readyState === 'interactive';
        }
        
        // Функция ожидания готовности DOM
        function waitForDOM() {
            return new Promise((resolve) => {
                if (isDOMReady()) {
                    resolve();
                } else {
                    document.addEventListener('DOMContentLoaded', resolve, { once: true });
                }
            });
        }
        
        function initCategoriesSwiper() {
            try {
                // Проверяем готовность DOM
                if (!isDOMReady()) {
                    console.warn('DOM not ready, waiting...');
                    waitForDOM().then(() => {
                        setTimeout(initCategoriesSwiper, 100);
                    });
                    return false;
                }
                
                // Проверяем загрузку Swiper библиотеки
                if (typeof Swiper === 'undefined') {
                    console.warn('Swiper library not loaded yet, retrying...');
                    setTimeout(initCategoriesSwiper, 500);
                    return false;
                }
                
                // Уничтожаем предыдущий экземпляр если есть
                if (categoriesSwiper) {
                    try {
                        categoriesSwiper.destroy(true, true);
                    } catch (e) {
                        console.warn('Error destroying previous Swiper instance:', e);
                    }
                    categoriesSwiper = null;
                }
                
                // Проверяем наличие контейнера
                const swiperContainer = document.querySelector('.categories-swiper');
                if (!swiperContainer) {
                    console.warn('Swiper container not found');
                    return false;
                }
                
                // Проверяем размеры контейнера
                const containerRect = swiperContainer.getBoundingClientRect();
                if (containerRect.width === 0 || containerRect.height === 0) {
                    console.warn('Swiper container has zero size, retrying...');
                    setTimeout(initCategoriesSwiper, 200);
                    return false;
                }
                
                // Убеждаемся, что контейнер видим
                const containerStyle = getComputedStyle(swiperContainer);
                if (containerStyle.display === 'none' || containerStyle.visibility === '') {
                    console.warn('Swiper container is not visible, retrying...');
                    setTimeout(initCategoriesSwiper, 200);
                    return false;
                }
            
            // Проверяем наличие категорий
            const wrapper = swiperContainer.querySelector('.swiper-wrapper');
            const categoryCards = wrapper ? wrapper.querySelectorAll('.category-card') : [];
            
            if (categoryCards.length === 0) {
                console.warn('No category cards found for Swiper');
                return false;
            }
            
            // Оборачиваем каждую category-card в swiper-slide
            categoryCards.forEach(card => {
                if (!card.parentElement.classList.contains('swiper-slide')) {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    card.parentNode.insertBefore(slide, card);
                    slide.appendChild(card);
                }
            });
            
            console.log('Swiper initialized with', categoryCards.length, 'slides');
            
            // Инициализируем Swiper с исправленными настройками
            try {
                // Дополнительная проверка перед созданием Swiper
                if (typeof Swiper === 'undefined') {
                    console.error('Swiper library not loaded');
                    return false;
                }
                
                categoriesSwiper = new Swiper('.categories-swiper', {
                    slidesPerView: 'auto',
                    spaceBetween: 12,
                    freeMode: {
                        enabled: true,
                        sticky: false,
                    },
                    grabCursor: true,
                    resistance: true,
                    resistanceRatio: 0.5,
                    speed: 300,
                    preventClicks: false,
                    preventClicksPropagation: false,
                    allowTouchMove: true,
                    touchRatio: 1,
                    centeredSlides: false,
                    centeredSlidesBounds: false,
                    slidesOffsetBefore: 0,
                    slidesOffsetAfter: 0,
                    pagination: false,
                    navigation: false,
                    simulateTouch: true, // Включаем симуляцию touch для мыши
                    touchStartPreventDefault: false,
                    mousewheel: {
                        forceToAxis: true,
                        sensitivity: 1,
                        releaseOnEdges: true,
                        eventsTarget: 'container',
                        thresholdDelta: 7
                    },
                    breakpoints: {
                        320: {
                            spaceBetween: 8,
                        },
                        480: {
                            spaceBetween: 12,
                        }
                    },
                    on: {
                        init: function() {
                            console.log('Swiper initialized successfully');
                            
                            // Добавляем passive обработчики wheel событий для улучшения производительности
                            const swiperEl = this.el;
                            if (swiperEl && !swiperEl.hasPassiveWheelListeners) {
                                swiperEl.addEventListener('wheel', function(e) {
                                    // Пустой passive обработчик для улучшения производительности
                                }, { passive: true });
                                swiperEl.hasPassiveWheelListeners = true;
                            }
                            
                            // Безопасное обновление размеров после полной инициализации
                            setTimeout(() => {
                                try {
                                    if (this && this.updateSize && typeof this.updateSize === 'function') {
                                        this.updateSize();
                                    }
                                    if (this && this.updateSlides && typeof this.updateSlides === 'function') {
                                        this.updateSlides();
                                    }
                                    if (this && this.updateSlidesClasses && typeof this.updateSlidesClasses === 'function') {
                                        this.updateSlidesClasses();
                                    }
                                } catch (updateError) {
                                    console.warn('Error during Swiper size update:', updateError);
                                }
                            }, 100);
                        },
                        resize: function() {
                            try {
                                if (this && this.update && typeof this.update === 'function') {
                                    this.update();
                                }
                            } catch (resizeError) {
                                console.warn('Error during Swiper resize:', resizeError);
                            }
                        }
                    }
                });
                
                console.log('Swiper successfully created with', categoryCards.length, 'category cards');
                
                // Сохраняем глобально для доступа
                window.categoriesSwiper = categoriesSwiper;
                
            } catch (swiperError) {
                console.error('Error creating Swiper instance:', swiperError);
                categoriesSwiper = null;
                return false;
            }
                
            // Принудительное обновление после инициализации
            setTimeout(() => {
                    if (categoriesSwiper && categoriesSwiper.update) {
                        try {
                            categoriesSwiper.update();
                        } catch (e) {
                            console.warn('Error updating Swiper:', e);
                        }
                    }
                    
                    // Запускаем ленивую загрузку категорий после инициализации Swiper
                    if (typeof window.setupCategoryLazyLoading === 'function') {
                        setTimeout(() => {
                            window.setupCategoryLazyLoading();
                        }, 200);
                    }
                }, 100);
                
                return true; // Успешная инициализация
                
            } catch (error) {
                console.error('Error initializing Swiper:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                categoriesSwiper = null; // Очищаем переменную при ошибке
                
                // Ограничиваем количество попыток переинициализации
                if (!window.swiperRetryCount) window.swiperRetryCount = 0;
                if (window.swiperRetryCount < 3) {
                    window.swiperRetryCount++;
                    setTimeout(() => {
                        console.log('Retrying Swiper initialization... Attempt:', window.swiperRetryCount);
                        initCategoriesSwiper();
                    }, 1000 * window.swiperRetryCount);
                } else {
                    console.error('Max Swiper initialization attempts reached. Giving up.');
                }
                
                return false; // Неудачная инициализация
            }
        }
        
        // Функция для переинициализации при изменении контента с улучшенной защитой
        function reinitSwiper() {
            // Инициализируем счетчики если их нет
            if (!window.swiperReinitRequestCount) {
                window.swiperReinitRequestCount = 0;
                window.lastSwiperReinitRequest = 0;
            }
            
            const now = Date.now();
            const timeSinceLastRequest = now - window.lastSwiperReinitRequest;
            
            // Сбрасываем счетчик если прошло достаточно времени
            if (timeSinceLastRequest > 1000) {
                window.swiperReinitRequestCount = 0;
            }
            
            // Защита от слишком частых вызовов
            if (window.swiperReinitRequestCount >= 5) {
                console.warn('Слишком много запросов на переинициализацию Swiper, игнорируем');
                return;
            }
            
            // Очищаем предыдущий таймаут если есть
            if (window.swiperReinitTimeout) {
                clearTimeout(window.swiperReinitTimeout);
            }
            
            window.swiperReinitRequestCount++;
            window.lastSwiperReinitRequest = now;
            
            window.swiperReinitTimeout = setTimeout(() => {
                console.log('Reinitializing Swiper... (request #' + window.swiperReinitRequestCount + ')');
                const success = initCategoriesSwiper();
                if (success) {
                    console.log('Swiper reinitialization successful');
                    window.swiperReinitRequestCount = 0; // сброс счетчика после успешной инициализации
                } else {
                    console.warn('Swiper reinitialization failed');
                }
            }, 300);
        }
        
        // Глобальная функция для использования из других скриптов
        window.reinitCategoriesSwiper = reinitSwiper;
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, preparing Swiper initialization');
            
            // Увеличиваем задержку для инициализации Swiper
            setTimeout(initCategoriesSwiper, 1000);
            
            // Ожидаем загрузки всех скриптов - убираем дублирующий вызов initApp
            setTimeout(function() {
                if (typeof initApp === 'function' && !window.isAppInitializedByBlade) {
                    window.isAppInitializedByBlade = true;
                    console.log('Calling initApp from Blade template');
                    initApp();
                    // Переинициализируем Swiper после загрузки приложения
                    setTimeout(initCategoriesSwiper, 500);
                } else {
                    console.log('initApp already called or function not found');
                }
            }, 800);
        });
        
        // Переинициализация при изменении размера окна
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (categoriesSwiper && categoriesSwiper.update) {
                    try {
                        categoriesSwiper.update();
                        console.log('Swiper updated on resize');
                    } catch (error) {
                        console.warn('Error updating Swiper on resize:', error);
                        // Если обновление не удалось, попробуем переинициализировать
                        reinitSwiper();
                    }
                } else {
                    console.log('Swiper not available, reinitializing...');
                    initCategoriesSwiper();
                }
            }, 250);
        });
        
        // Наблюдатель за изменениями в DOM для автоматической переинициализации
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.target.id === 'categoriesTrack') {
                        console.log('Categories content changed, reinitializing Swiper');
                        setTimeout(reinitSwiper, 100);
                    }
                });
            });
            
            // Начинаем наблюдение когда DOM готов
            document.addEventListener('DOMContentLoaded', function() {
                const categoriesTrack = document.getElementById('categoriesTrack');
                if (categoriesTrack) {
                    observer.observe(categoriesTrack, {
                        childList: true,
                        subtree: false
                    });
                }
            });
        }
    </script>
@endpush