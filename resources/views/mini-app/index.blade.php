<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="short-name" content="{{ $shortName }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-navbutton-color" content="#ffffff">
    <meta name="apple-mobile-web-app-status-bar-style" content="light-content">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-fullscreen" content="yes">
    <title>{{ $bot->bot_name }} - Mini App</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- App Styles -->
    @vite(['resources/css/app.css', 'resources/css/mini-app.css'])
    
    <!-- Telegram WebApp JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Mini App JS -->
    @vite(['resources/js/mini-app.js'])
</head>
<body class="mini-app-body">
    <!-- Экран загрузки -->
    <div id="loading">
        <div class="text-center loading-flex">
            <div class="loading-spinner mb-3"></div>
            <div>Загрузка Mini App...</div>
        </div>
    </div>

    <!-- Основное содержимое -->
    <div id="app" class="mini-app mini-app-container" style="display: none;">
        <!-- Область для свайпа с левого края -->
        <div class="swipe-edge-area"></div>
        
        <!-- Блок поиска -->
        <div class="search-container ">
            <div class="search-box">
                <div class="input-group search-box-h">
                    <input type="text" class="form-control search-input" id="searchInput" 
                           placeholder="Поиск товаров..." autocomplete="off">
                    <button class="btn btn-primary search-btn" type="button" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Слайдер категорий -->
        <div class="categories-slider-container" id="categoriesContainer" style="display: none;">
            <div class="categories-slider">
                <div class="categories-track" id="categoriesTrack">
                    <!-- Категории будут загружены через JavaScript -->
                </div>
            </div>
        </div>

        @if($products->count() > 0)
        <div class="products-grid" id="productsContainer">
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-store me-2"></i>Товары магазина</h5>
            </div>
            
            <div class="products-flex-container">
                @foreach($products as $product)
                <div class="product-flex-item">
                    <div class="product-card" onclick="showProductDetails({{ $product->id }})">
                        <div class="product-image-container">
                            @if($product->photo_url)
                                <img src="{{ $product->photo_url }}" 
                                     class="product-image" 
                                     alt="{{ $product->name }}"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-placeholder" style="display: none;">
                                    <i class="fas fa-image"></i>
                                    <span>Ошибка загрузки</span>
                                </div>
                            @else
                                <div class="product-image-placeholder">
                                    <i class="fas fa-image"></i>
                                    <span>Нет фото</span>
                                </div>
                            @endif
                            
                            <!-- Quantity badge on image -->
                            <span class="quantity-badge {{ $product->quantity > 10 ? 'quantity-success' : ($product->quantity > 0 ? 'quantity-warning' : 'quantity-danger') }}">
                                {{ $product->quantity }} шт.
                            </span>
                        </div>
                        
                        <div class="product-content">
                            <div class="product-info">
                                <h6 class="product-title">{{ Str::limit($product->name, 40) }}</h6>
                                @if($product->description)
                                <p class="product-description">{{ Str::limit($product->description, 50) }}</p>
                                @endif
                            </div>
                            
                            <div class="product-actions">
                                <div class="product-action-row">
                                    <div class="cart-button-wrapper">
                                        @if($product->isAvailable())
                                        <button class="cart-btn cart-btn-primary" 
                                                onclick="event.stopPropagation(); addToCart({{ $product->id }})"
                                                title="Добавить в корзину">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        @else
                                        <button class="cart-btn cart-btn-disabled" disabled
                                                title="Нет в наличии">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                    
                                    <div class="product-price-wrapper">
                                        <span class="product-price">{{ $product->formatted_price_with_markup }}</span>
                                    </div>
                                    
                                    <div class="product-quantity-wrapper">
                                        <span class="quantity-badge quantity-success">
                                            {{ $product->quantity }} шт.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Пагинация -->
            @if($products->hasPages())
            <div class="products-pagination">
                {{ $products->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="text-center py-4">
            <div class="text-muted">
                <h5><i class="fas fa-store-slash me-2"></i>Магазин временно пуст</h5>
                <p class="small">Товары скоро появятся!</p>
            </div>
        </div>
        @endif

        <!-- Корзина (плавающая кнопка) -->
        <div class="cart-float hidden" id="cart-float">
            <button class="cart-float-btn" onclick="showCartModal()" type="button" aria-label="Открыть корзину">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-counter hidden" id="cart-counter">0</span>
            </button>
        </div>
    </div>

    <!-- Бэкдроп для панельных окон -->
    <div class="slide-panel-backdrop" id="panelBackdrop" onclick="closePanel()"></div>

    <!-- Панель товара -->
    <div class="slide-panel" id="productPanel">
        <div class="slide-panel-header">
            <h5 class="slide-panel-title" id="productPanelTitle">Товар</h5>
            <button class="slide-panel-close" onclick="closePanel()" type="button">×</button>
        </div>
        
        <div class="slide-panel-body" id="productPanelBody">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="margin: 50px 0;">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        </div>
        
        <div class="slide-panel-footer" id="productPanelFooter" style="display: none;">
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="addToCartFromPanel">Добавить в корзину</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно товара -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-header-left">
                        <button type="button" class="modal-back-btn" onclick="closeProductModal()">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <h5 class="modal-title" id="productModalTitle">Загрузка...</h5>
                    </div>
                    <div class="modal-header-right">
                        <!-- Дополнительные кнопки если нужны -->
                    </div>
                </div>
                <div class="modal-body" id="productModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-none" id="productModalFooter">
                    <!-- Будет заполнено динамически -->
                </div>
            </div>
        </div>
    </div>

    <!-- Корзина (модальное окно) -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-header-left">
                        <button type="button" class="modal-back-btn" onclick="closeCartModal()">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <h5 class="modal-title" id="cartModalTitle">
                            <i class="fas fa-shopping-cart me-2"></i>Корзина
                        </h5>
                    </div>
                    <div class="modal-header-right">
                        <!-- Дополнительные кнопки если нужны -->
                    </div>
                </div>
                <div class="modal-body" id="cartModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка корзины...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-none" id="cartModalFooter">
                    <!-- Будет заполнено динамически -->
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
                    $productsData[$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'article' => $product->article,
                        'photo_url' => $product->photo_url,
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
        @endphp
        @json($productsData)
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Initialize Mini App -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ожидаем загрузки всех скриптов
            setTimeout(function() {
                if (typeof initApp === 'function') {
                    initApp();
                } else {
                    console.error('initApp function not found');
                }
            }, 100);
        });
    </script>
</body>
</html>