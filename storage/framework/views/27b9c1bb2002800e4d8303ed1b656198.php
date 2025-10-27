<?php $__env->startSection('title', $bot->bot_name . ' - Mini App'); ?>

<?php $__env->startPush('styles'); ?>
<meta name="short-name" content="<?php echo e($shortName); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                <?php if($bot->logo): ?>
                    <div class="app-logo">
                        <img src="<?php echo e(asset('storage/' . $bot->logo)); ?>" alt="<?php echo e($bot->bot_name); ?>" />
                    </div>
                <?php endif; ?>
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
                <article class="product-card" data-product-id="<?php echo e($product->id); ?>">
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
                                    data-product-id="<?php echo e($product->id); ?>"
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
            
            <!-- Индикатор загрузки для бесконечной прокрутки -->
            <?php if($products->hasMorePages()): ?>
            <div class="infinite-scroll-loader" id="infiniteScrollLoader" style="display: none;">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Загрузка товаров...</div>
                </div>
            </div>
            <div class="infinite-scroll-trigger" id="infiniteScrollTrigger" data-next-page="<?php echo e($products->currentPage() + 1); ?>" data-has-more="true"></div>
            <?php else: ?>
            <div class="infinite-scroll-end" style="text-align: center; padding: 20px; color: #888;">
                <i class="fas fa-check-circle"></i> Все товары загружены
            </div>
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

    <!-- Модальное окно для оформления заказа в веб-версии -->
    <div class="modal" id="webCheckoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal-back" onclick="closeWebCheckoutModal()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h3 class="modal-title">
                        <i class="fas fa-clipboard-check"></i>
                        Оформление заказа
                    </h3>
                </div>
                <div class="modal-body" id="webCheckoutModalBody">
                    <form id="webCheckoutForm" class="checkout-form">
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-user"></i>
                                Ваши данные
                            </h4>
                            
                            <div class="form-group">
                                <label for="customerName" class="form-label">
                                    Ваше имя <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="customerName" 
                                    name="customer_name"
                                    placeholder="Введите ваше имя"
                                    required
                                    minlength="2"
                                    maxlength="100"
                                >
                                <div class="invalid-feedback">
                                    Пожалуйста, введите ваше имя (минимум 2 символа)
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="customerPhone" class="form-label">
                                    Номер телефона <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="customerPhone" 
                                    name="customer_phone"
                                    placeholder="+7 (___) ___-__-__"
                                    required
                                    pattern="[\+]?[0-9]{10,15}"
                                >
                                <div class="invalid-feedback">
                                    Пожалуйста, введите корректный номер телефона
                                </div>
                                <small class="form-text text-muted">
                                    Введите номер в формате: +79991234567
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="customerComment" class="form-label">
                                    Комментарий к заказу
                                </label>
                                <textarea 
                                    class="form-control" 
                                    id="customerComment" 
                                    name="customer_comment"
                                    rows="3"
                                    placeholder="Дополнительные пожелания (необязательно)"
                                    maxlength="500"
                                ></textarea>
                                <small class="form-text text-muted">
                                    Максимум 500 символов
                                </small>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-shopping-bag"></i>
                                Состав заказа
                            </h4>
                            <div id="webCheckoutItems" class="checkout-items-list">
                                <!-- Товары будут загружены динамически -->
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="checkout-total">
                                <span class="checkout-total-label">Итого к оплате:</span>
                                <span class="checkout-total-amount" id="webCheckoutTotal">0 ₽</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeWebCheckoutModal()">
                        <i class="fas fa-times"></i>
                        Отмена
                    </button>
                    <button type="button" class="btn-primary" onclick="submitWebOrder()" id="submitWebOrderBtn">
                        <i class="fas fa-paper-plane"></i>
                        Отправить заказ
                    </button>
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
        ?>
        <?php echo json_encode($productsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>

    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
                // Инициализируем счетчики попыток
                if (!window.swiperInitAttempts) {
                    window.swiperInitAttempts = 0;
                }
                
                window.swiperInitAttempts++;
                
                // Максимум 10 попыток инициализации
                if (window.swiperInitAttempts > 10) {
                    return false;
                }
                
                // Проверяем готовность DOM
                if (!isDOMReady()) {
                    waitForDOM().then(() => {
                        setTimeout(initCategoriesSwiper, 100);
                    });
                    return false;
                }
                
                // Проверяем загрузку Swiper библиотеки
                if (typeof Swiper === 'undefined') {
                    if (window.swiperInitAttempts < 10) {
                        setTimeout(initCategoriesSwiper, 500);
                    }
                    return false;
                }
                
                // Уничтожаем предыдущий экземпляр если есть
                if (categoriesSwiper) {
                    try {
                        categoriesSwiper.destroy(true, true);
                    } catch (e) {
                        // Игнорируем ошибки при уничтожении
                    }
                    categoriesSwiper = null;
                }
                
                // Проверяем наличие контейнера
                const swiperContainer = document.querySelector('.categories-swiper');
                if (!swiperContainer) {
                    return false;
                }
                
                // Проверяем размеры контейнера
                const containerRect = swiperContainer.getBoundingClientRect();
                if (containerRect.width === 0 || containerRect.height === 0) {
                    // Прекращаем попытки после 5 раза
                    if (window.swiperInitAttempts < 5) {
                        setTimeout(initCategoriesSwiper, 300);
                    }
                    return false;
                }
                
                // Убеждаемся, что контейнер видим
                const containerStyle = getComputedStyle(swiperContainer);
                if (containerStyle.display === 'none' || containerStyle.visibility === 'hidden') {
                    if (window.swiperInitAttempts < 5) {
                        setTimeout(initCategoriesSwiper, 300);
                    }
                    return false;
                }
                
                // Проверяем наличие категорий
                const wrapper = swiperContainer.querySelector('.swiper-wrapper');
                const slides = wrapper ? wrapper.querySelectorAll('.swiper-slide') : [];
                
                if (slides.length === 0) {
                    return false;
                }
                
                // Проверяем что слайды правильно обернуты (для совместимости со старым кодом)
                const categoryCards = wrapper.querySelectorAll('.category-card');
                categoryCards.forEach(card => {
                    if (!card.parentElement.classList.contains('swiper-slide')) {
                        const slide = document.createElement('div');
                        slide.className = 'swiper-slide';
                        card.parentNode.insertBefore(slide, card);
                        slide.appendChild(card);
                    }
                });
                
                // Инициализируем Swiper с исправленными настройками
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
                                    // Игнорируем ошибки обновления
                                }
                            }, 100);
                        },
                        resize: function() {
                            try {
                                if (this && this.update && typeof this.update === 'function') {
                                    this.update();
                                }
                            } catch (resizeError) {
                                // Игнорируем ошибки при ресайзе
                            }
                        }
                    }
                });
                
                // Сохраняем глобально для доступа
                window.categoriesSwiper = categoriesSwiper;
                
                // Сбрасываем счетчик попыток после успешной инициализации
                window.swiperInitAttempts = 0;
                
                // Принудительное обновление после инициализации
                setTimeout(() => {
                    if (categoriesSwiper && categoriesSwiper.update) {
                        try {
                            categoriesSwiper.update();
                        } catch (e) {
                            // Игнорируем ошибки обновления
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
                categoriesSwiper = null; // Очищаем переменную при ошибке
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
                return;
            }
            
            // Очищаем предыдущий таймаут если есть
            if (window.swiperReinitTimeout) {
                clearTimeout(window.swiperReinitTimeout);
            }
            
            window.swiperReinitRequestCount++;
            window.lastSwiperReinitRequest = now;
            
            window.swiperReinitTimeout = setTimeout(() => {
                const success = initCategoriesSwiper();
                if (success) {
                    window.swiperReinitRequestCount = 0; // сброс счетчика после успешной инициализации
                }
            }, 300);
        }
        
        // Глобальная функция для использования из других скриптов
        window.reinitCategoriesSwiper = reinitSwiper;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Гарантируем скрытие экрана загрузки через 2 секунды максимум
            setTimeout(function() {
                const loadingEl = document.getElementById('loading');
                const appEl = document.getElementById('app');
                
                if (loadingEl && loadingEl.style.display !== 'none') {
                    loadingEl.style.display = 'none';
                }
                if (appEl && appEl.style.display === 'none') {
                    appEl.style.display = 'block';
                }
            }, 2000);
            
            // Увеличиваем задержку для инициализации Swiper
            setTimeout(initCategoriesSwiper, 1000);
            
            // Ожидаем загрузки всех скриптов - убираем дублирующий вызов initApp
            setTimeout(function() {
                if (typeof initApp === 'function' && !window.isAppInitializedByBlade) {
                    window.isAppInitializedByBlade = true;
                    initApp();
                    // Переинициализируем Swiper после загрузки приложения
                    setTimeout(initCategoriesSwiper, 500);
                } else {
                    // Даже если initApp не вызван, скрываем экран загрузки
                    const loadingEl = document.getElementById('loading');
                    const appEl = document.getElementById('app');
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (appEl) appEl.style.display = 'block';
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
                    } catch (error) {
                        // Если обновление не удалось, попробуем переинициализировать
                        reinitSwiper();
                    }
                } else {
                    initCategoriesSwiper();
                }
            }, 250);
        });
        
        // Наблюдатель за изменениями в DOM для автоматической переинициализации
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.target.id === 'categoriesTrack') {
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.mini-app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/mini-app/index.blade.php ENDPATH**/ ?>