<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="short-name" content="<?php echo e($shortName); ?>">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-navbutton-color" content="#ffffff">
    <meta name="apple-mobile-web-app-status-bar-style" content="light-content">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-fullscreen" content="yes">
    <title><?php echo e($bot->bot_name); ?> - Mini App</title>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- App Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/mini-app.css']); ?>
    
    <!-- Telegram WebApp JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Mini App JS -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/mini-app.js']); ?>
</head>
<body class="mini-app-body">
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
        <?php if($products->count() > 0): ?>
        <section class="products-section" id="productsContainer">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-store"></i>
                    <span id="productsTitle">Товары магазина</span>
                </h2>
            </div>
            
            <div class="products-grid">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="product-card" onclick="showProductDetails(<?php echo e($product->id); ?>)">
                    <div class="product-image">
                        <?php if($product->main_photo_url): ?>
                            <img src="<?php echo e($product->main_photo_url); ?>" 
                                 alt="<?php echo e($product->name); ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
                        <?php endif; ?>
                        <div class="product-badge">
                            <span class="stock-count <?php echo e($product->quantity > 10 ? 'in-stock' : ($product->quantity > 0 ? 'low-stock' : 'out-of-stock')); ?>">
                                <?php echo e($product->quantity); ?> шт
                            </span>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo e(Str::limit($product->name, 35)); ?></h3>
                        <?php if($product->description): ?>
                            <p class="product-description"><?php echo e(Str::limit($product->description, 45)); ?></p>
                        <?php endif; ?>
                        
                        <div class="product-footer">
                            <div class="product-price"><?php echo e($product->formatted_price_with_markup); ?></div>
                            <button class="add-to-cart <?php echo e(!$product->isAvailable() ? 'disabled' : ''); ?>" 
                                    onclick="event.stopPropagation(); addToCart(<?php echo e($product->id); ?>)"
                                    <?php echo e(!$product->isAvailable() ? 'disabled' : ''); ?>>
                                <?php if($product->isAvailable()): ?>
                                    <i class="fas fa-plus"></i>
                                <?php else: ?>
                                    <i class="fas fa-times"></i>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <!-- Пагинация -->
            <?php if($products->hasPages()): ?>
            <nav class="pagination-nav">
                <div class="pagination-wrapper">
                    <?php if($products->onFirstPage()): ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($products->previousPageUrl()); ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php $__currentLoopData = $products->getUrlRange(1, $products->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $products->currentPage()): ?>
                            <span class="pagination-btn active"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" class="pagination-btn"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if($products->hasMorePages()): ?>
                        <a href="<?php echo e($products->nextPageUrl()); ?>" class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
        </section>
        <?php else: ?>
        <section class="empty-state">
            <div class="empty-content">
                <i class="fas fa-store-slash"></i>
                <h3>Магазин временно пуст</h3>
                <p>Товары скоро появятся!</p>
            </div>
        </section>
        <?php endif; ?>

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
        <?php
            $productsData = [];
            if (isset($products) && $products->count() > 0) {
                foreach ($products as $product) {
                    $productsData[$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'article' => $product->article,
                        'photo_url' => $product->photo_url,
                        'main_photo_url' => $product->main_photo_url,
                        'photos_gallery' => $product->photos_gallery,
                        'specifications' => $product->specifications,
                        'quantity' => $product->quantity,
                        'price' => $product->price,
                        'formatted_price' => $product->formatted_price_with_markup,
                        'availability_status' => $product->availability_status,
                        'isAvailable' => $product->isAvailable(),
                        'category_id' => $product->category_id
                    ];
                }
            }
        ?>
        <?php echo json_encode($productsData, 15, 512) ?>
    </script>

    <!-- Initialize Mini App -->
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
                    mousewheel: {
                        forceToAxis: true,
                        sensitivity: 1,
                        releaseOnEdges: true
                    },
                    // Добавляем обработчики событий для отладки
                    on: {
                        init: function() {
                            console.log('Swiper initialized successfully');
                        },
                        resize: function() {
                            console.log('Swiper resized');
                        }
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
                            // Безопасное обновление размеров после полной инициализации
                            setTimeout(() => {
                                try {
                                    if (this && this.updateSize && typeof this.updateSize === 'function') {
                                        this.updateSize();
                                    }
                                    if (this && this.updateSlides && typeof this.updateSlides === 'function') {
                                        this.updateSlides();
                                    }
                                    if (this && this.updateProgress && typeof this.updateProgress === 'function') {
                                        this.updateProgress();
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
        
        // Функция для переинициализации при изменении контента
        function reinitSwiper() {
            // Очищаем предыдущий таймаут если есть
            if (window.swiperReinitTimeout) {
                clearTimeout(window.swiperReinitTimeout);
            }
            
            window.swiperReinitTimeout = setTimeout(() => {
                console.log('Reinitializing Swiper...');
                const success = initCategoriesSwiper();
                if (success) {
                    console.log('Swiper reinitialization successful');
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
            
            // Ожидаем загрузки всех скриптов
            setTimeout(function() {
                if (typeof initApp === 'function') {
                    initApp();
                    // Переинициализируем Swiper после загрузки приложения
                    setTimeout(initCategoriesSwiper, 500);
                } else {
                    console.error('initApp function not found');
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
</body>
</html><?php /**PATH C:\OSPanel\domains\post\resources\views/mini-app/index.blade.php ENDPATH**/ ?>