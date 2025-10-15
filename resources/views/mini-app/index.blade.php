<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bot->bot_name }} - Mini App</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- App Styles -->
    @vite(['resources/css/app.css'])
    
    <!-- Telegram WebApp JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body class="mini-app-body">
    <!-- Экран загрузки -->
    <div id="loading">
        <div class="text-center">
            <div class="loading-spinner mb-3"></div>
            <div>Загрузка Mini App...</div>
        </div>
    </div>

    <!-- Основное содержимое -->
    <div id="app" class="mini-app mini-app-container" style="display: none;">
        <!-- Блок поиска -->
        <div class="search-container mb-3">
            <div class="search-box">
                <div class="input-group">
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
        <div class="products-grid mt-1" id="productsContainer">
            <h5 id="productsTitle">🛍️ Товары магазина</h5>
            <div class="row">
                @foreach($products as $product)
                <div class="col-6 col-md-4  ">
                    <div class="card product-card h-100" onclick="showProductDetails({{ $product->id }})" style="cursor: pointer;">
                        @if($product->photo_url)
                            <img src="{{ $product->photo_url }}" 
                                 class="card-img-top" 
                                 alt="{{ $product->name }}"
                                 style="height: 150px; object-fit: cover;"
                                 onerror="console.log('Ошибка загрузки изображения:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="card-img-top d-none justify-content-center align-items-center bg-light" 
                                 style="height: 150px; color: #6c757d;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-2x mb-2"></i><br>
                                    <small>Ошибка загрузки</small>
                                </div>
                            </div>
                        @else
                            <div class="card-img-top d-flex justify-content-center align-items-center bg-light" 
                                 style="height: 150px; color: #6c757d;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-2x mb-2"></i><br>
                                    <small>Нет фото</small>
                                </div>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ Str::limit($product->name, 40) }}</h6>
                            @if($product->description)
                            <p class="card-text small">{{ Str::limit($product->description, 50) }}</p>
                            @endif
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- Кнопка корзины слева -->
                                    <div class="me-2">
                                        @if($product->isAvailable())
                                        <button class="btn btn-primary btn-sm rounded-circle p-1" 
                                                style="width: 32px; height: 32px; font-size: 12px;"
                                                onclick="event.stopPropagation(); addToCart({{ $product->id }})"
                                                title="Добавить в корзину">
                                            🛒
                                        </button>
                                        @else
                                        <button class="btn btn-outline-secondary btn-sm rounded-circle p-1" 
                                                style="width: 32px; height: 32px; font-size: 10px;" disabled
                                                title="Нет в наличии">
                                            ❌
                                        </button>
                                        @endif
                                    </div>
                                    
                                    <!-- Цена по центру -->
                                    <div class="flex-grow-1 text-center">
                                        <span class="fw-bold text-success">{{ $product->formatted_price }}</span>
                                    </div>
                                    
                                    <!-- Количество справа -->
                                    <div class="ms-2">
                                        <span class="badge bg-{{ $product->quantity > 5 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger') }}"
                                              style="font-size: 10px;">
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
            <div class="d-flex justify-content-center mt-3">
                {{ $products->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="text-center py-4">
            <div class="text-muted">
                <h5>🏪 Магазин временно пуст</h5>
                <p class="small">Товары скоро появятся!</p>
            </div>
        </div>
        @endif

        <!-- Корзина (плавающая кнопка) -->
        <div class="cart-float" id="cart-float" style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        ">
            <button class="btn btn-success rounded-circle p-3 shadow" onclick="showCart()">
                🛒
                <span class="cart-counter badge bg-danger rounded-pill position-absolute" 
                      style="top: -5px; right: -5px; min-width: 20px; display: none;">0</span>
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

    <!-- Скрытые данные товаров для JavaScript -->
    <script type="application/json" id="products-data">
        {!! json_encode($products->keyBy('id')->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'article' => $product->article,
                'photo_url' => $product->photo_url,
                'specifications' => $product->specifications,
                'quantity' => $product->quantity,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price,
                'availability_status' => $product->availability_status,
                'isAvailable' => $product->isAvailable()
            ];
        })) !!}
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        console.log('Mini App загружается...');
        
        // Переменные для отладки и разработки
        const isDevelopmentMode = !window.Telegram?.WebApp;
        let userData = null;

        // Основная функция инициализации
        function initApp() {
            try {
                console.log('Инициализируем Mini App...');

                if (window.Telegram?.WebApp) {
                    // Telegram WebApp доступен
                    const tg = window.Telegram.WebApp;
                    
                    // Разворачиваем приложение
                    tg.expand();
                    
                    // Применяем тему Telegram
                    document.body.style.backgroundColor = tg.backgroundColor || '#ffffff';
                    document.body.style.color = tg.textColor || '#000000';
                    
                    // Получаем данные пользователя
                    if (tg.initDataUnsafe?.user) {
                        userData = tg.initDataUnsafe.user;
                        displayUserInfo(userData);
                    } else if (tg.initData) {
                        // Парсим initData если доступно
                        userData = parseUserFromInitData(tg.initData);
                        if (userData) {
                            displayUserInfo(userData);
                        }
                    }

                    console.log('Mini App успешно инициализирован в Telegram');
                    
                } else if (isDevelopmentMode) {
                    // Режим разработки - создаем фиктивные данные пользователя
                    userData = {
                        id: 12345,
                        first_name: 'Разработчик',
                        last_name: 'Тестовый',
                        username: 'developer'
                    };
                    displayUserInfo(userData);
                    
                    // Применяем базовую тему
                    document.body.style.backgroundColor = '#ffffff';
                    document.body.style.color = '#000000';
                    
                    console.log('Mini App запущен в режиме разработки');
                    
                    // Показываем предупреждение о режиме разработки
                    const devWarning = document.createElement('div');
                    devWarning.className = 'alert alert-warning text-center';
                    devWarning.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Режим разработки</strong><br>
                        Приложение работает вне Telegram WebApp
                    `;
                    document.querySelector('.container').insertBefore(devWarning, document.querySelector('.container').firstChild);
                    
                } else {
                    // Показываем ошибку для продакшн-среды
                    throw new Error('Telegram WebApp недоступен');
                }

                // Скрываем загрузку и показываем приложение
                const loadingEl = document.getElementById('loading');
                const appEl = document.getElementById('app');
                
                if (loadingEl) loadingEl.style.display = 'none';
                if (appEl) appEl.style.display = 'block';

                // Mini App готов к работе
                console.log('Mini App готов к работе');

            } catch (error) {
                console.error('Ошибка инициализации:', error);
                showErrorMessage(error.message || 'Неизвестная ошибка инициализации');
            }
        }

        // Показать сообщение об ошибке
        function showErrorMessage(message) {
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.innerHTML = `
                    <div class="text-center">
                        <div class="alert alert-danger">
                            <strong>Ошибка загрузки</strong><br>
                            ${message}
                        </div>
                        <button class="btn btn-primary" onclick="location.reload()">
                            Перезагрузить
                        </button>
                    </div>
                `;
            }
        }

        // Отобразить информацию о пользователе
        function displayUserInfo(user) {
            if (!user) return;
            
            const userInfo = document.createElement('div');
            userInfo.className = 'bot-info';
            userInfo.innerHTML = `
                <h5>👤 Информация о пользователе</h5>
                <p><strong>Имя:</strong> ${user.first_name || 'Не указано'} ${user.last_name || ''}</p>
                ${user.username ? `<p><strong>Username:</strong> @${user.username}</p>` : ''}
                <p><small class="text-muted">ID: ${user.id}</small></p>
            `;
            
            const container = document.querySelector('#app .container');
            if (container) {
                container.appendChild(userInfo);
            }
        }

        // Парсинг пользователя из initData
        function parseUserFromInitData(initData) {
            try {
                const params = new URLSearchParams(initData);
                const userStr = params.get('user');
                if (userStr) {
                    return JSON.parse(userStr);
                }
            } catch (error) {
                console.error('Ошибка парсинга пользователя:', error);
            }
            return null;
        }

        // Показать уведомление с проверкой совместимости
        function showAlert(message) {
            try {
                if (window.Telegram?.WebApp?.showAlert && 
                    typeof window.Telegram.WebApp.showAlert === 'function') {
                    window.Telegram.WebApp.showAlert(message);
                } else {
                    // Fallback для старых версий или браузера
                    showToast(message);
                }
            } catch (error) {
                console.log('Используем fallback для уведомления:', error);
                showToast(message);
            }
        }

        // Toast уведомления как fallback
        function showToast(message, type = 'info') {
            // Удаляем предыдущий toast если есть
            const existingToast = document.querySelector('.custom-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `custom-toast alert alert-${type === 'error' ? 'danger' : 'success'} position-fixed`;
            toast.style.cssText = `
                top: 20px; 
                left: 50%; 
                transform: translateX(-50%); 
                z-index: 9999; 
                max-width: 90%; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                border-radius: 8px;
            `;
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
                    <div>${message}</div>
                </div>
            `;

            document.body.appendChild(toast);

            // Автоматически убираем через 3 секунды
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }

        // Безопасный haptic feedback
        function triggerHapticFeedback(type = 'light') {
            try {
                if (window.Telegram?.WebApp?.HapticFeedback?.impactOccurred && 
                    typeof window.Telegram.WebApp.HapticFeedback.impactOccurred === 'function') {
                    window.Telegram.WebApp.HapticFeedback.impactOccurred(type);
                }
                // Не делаем ничего если не поддерживается - это нормально
            } catch (error) {
                // Молча игнорируем ошибки haptic feedback
                console.debug('HapticFeedback не поддерживается:', error.message);
            }
        }

        // Настройка Haptic Feedback
        function setupHapticFeedback() {
            const buttons = document.querySelectorAll('button, .btn');
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    triggerHapticFeedback('light');
                });
            });
        }

        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM загружен, инициализируем приложение...');
            
            // Ожидаем полной загрузки Telegram WebApp
            setTimeout(() => {
                initApp();
                
                // Настраиваем Haptic Feedback после инициализации
                setTimeout(setupHapticFeedback, 500);
            }, 100);
        });

        // Дополнительная инициализация если DOM уже загружен
        if (document.readyState === 'loading') {
            console.log('DOM загружается...');
        } else {
            console.log('DOM уже загружен, инициализируем немедленно...');
            setTimeout(() => {
                initApp();
                setTimeout(setupHapticFeedback, 500);
            }, 100);
        }

        // Глобальная обработка ошибок
        window.addEventListener('error', (event) => {
            console.error('Глобальная ошибка:', event.error, event.filename, event.lineno);
        });

        // Корзина
        let cart = [];
        
        function addToCart(productId) {
            // Используем новую функцию с количеством по умолчанию = 1
            addToCartWithQuantity(productId, 1);
        }
        
        function updateCartCounter() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    const counter = document.querySelector('.cart-counter');
                    const cartFloat = document.getElementById('cart-float');
                    
                    if (data.count > 0) {
                        counter.textContent = data.count;
                        counter.style.display = 'inline';
                        cartFloat.style.display = 'block';
                    } else {
                        counter.style.display = 'none';
                        cartFloat.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Ошибка получения счетчика корзины:', error);
                });
        }
        
        // Показать детали товара в панели
        function showProductDetails(productId) {
            try {
                // Кэшируем данные товаров
                if (!window.cachedProductsData) {
                    window.cachedProductsData = JSON.parse(document.getElementById('products-data').textContent);
                }
                
                const product = window.cachedProductsData[productId];
                if (!product) {
                    showAlert('Товар не найден', 'error');
                    return;
                }
                
                // Получаем элементы панели
                const panel = document.getElementById('productPanel');
                const title = document.getElementById('productPanelTitle');
                const body = document.getElementById('productPanelBody');
                const footer = document.getElementById('productPanelFooter');
                const backdrop = document.getElementById('panelBackdrop');
                
                // Устанавливаем заголовок
                title.textContent = product.name.length > 30 ? product.name.substring(0, 30) + '...' : product.name;
                
                // Формируем контент панели
                const panelContent = `
                    <!-- Изображение товара -->
                    <div class="position-relative mb-3">
                        ${product.photo_url ? `
                            <img src="${product.photo_url}" 
                                 class="w-100 rounded" 
                                 style="height: 250px; object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="d-none justify-content-center align-items-center bg-light rounded" 
                                 style="height: 250px; color: #6c757d;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x mb-2"></i><br>
                                    <span>Изображение недоступно</span>
                                </div>
                            </div>
                        ` : `
                            <div class="d-flex justify-content-center align-items-center bg-light rounded" 
                                 style="height: 250px; color: #6c757d;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x mb-2"></i><br>
                                    <span>Нет фото</span>
                                </div>
                            </div>
                        `}
                        
                        <!-- Статус товара -->
                        <div class="position-absolute top-0 end-0 m-2">
                            ${getStatusBadge(product)}
                        </div>
                    </div>
                    
                    <!-- Информация о товаре -->
                    <div class="mb-3">
                        <!-- Артикул -->
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted me-1">Артикул:</small>
                            <code>${product.article}</code>
                        </div>
                        
                        <!-- Цена -->
                        <div class="mb-3">
                            <span class="h3 text-success fw-bold">${product.formatted_price}</span>
                        </div>
                        
                        <!-- Описание -->
                        ${product.description ? `
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2">📝 Описание</h6>
                                <p class="text-muted">${product.description.length > 200 ? product.description.substring(0, 200) + '...' : product.description}</p>
                            </div>
                        ` : ''}
                        
                        <!-- Характеристики -->
                        ${product.specifications && product.specifications.length > 0 ? `
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2">📋 Характеристики</h6>
                                <div class="bg-light rounded p-3">
                                    ${product.specifications.map(spec => 
                                        `<div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span>${spec}</span>
                                         </div>`
                                    ).join('')}
                                </div>
                            </div>
                        ` : ''}
                        
                        <!-- Информация о количестве -->
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-3">
                            <span class="fw-bold">В наличии:</span>
                            <span class="badge bg-${product.quantity > 5 ? 'success' : (product.quantity > 0 ? 'warning' : 'danger')} fs-6">
                                ${product.quantity} шт.
                            </span>
                        </div>
                        
                        <!-- Выбор количества -->
                        ${product.isAvailable ? `
                            <div class="mb-3">
                                <h6 class="fw-bold mb-3">🔢 Количество</h6>
                                <div class="d-flex align-items-center justify-content-center p-3 bg-light rounded">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            id="decreaseBtn-${product.id}"
                                            onclick="changeQuantity(${product.id}, -1)"
                                            style="width: 40px; height: 40px; border-radius: 50%;">
                                        −
                                    </button>
                                    
                                    <div class="mx-4 text-center">
                                        <input type="number" 
                                               class="form-control text-center fw-bold" 
                                               id="quantity-${product.id}"
                                               value="1" 
                                               min="1" 
                                               max="${product.quantity}"
                                               onchange="validateQuantity(${product.id})"
                                               style="width: 80px; font-size: 18px;">
                                        <small class="text-muted">шт.</small>
                                    </div>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            id="increaseBtn-${product.id}"
                                            onclick="changeQuantity(${product.id}, 1)"
                                            style="width: 40px; height: 40px; border-radius: 50%;">
                                        +
                                    </button>
                                </div>
                                
                                <!-- Общая стоимость -->
                                <div class="mt-3 p-3 bg-success bg-opacity-10 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Итого:</span>
                                        <span class="h5 text-success fw-bold mb-0" id="totalPrice-${product.id}">
                                            ${product.formatted_price}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                // Обновляем контент
                body.innerHTML = panelContent;
                
                // Настраиваем кнопку в футере
                const addButton = document.getElementById('addToCartFromPanel');
                
                // Отладка: выводим информацию о товаре
                console.log('Отладка товара:', {
                    id: product.id,
                    name: product.name,
                    quantity: product.quantity,
                    isAvailable: product.isAvailable,
                    availability_status: product.availability_status
                });
                
                if (product.isAvailable) {
                    addButton.disabled = false;
                    addButton.className = 'btn btn-primary btn-lg';
                    addButton.innerHTML = `🛒 Добавить в корзину`;
                    addButton.onclick = () => {
                        const quantity = parseInt(document.getElementById(`quantity-${product.id}`).value) || 1;
                        addToCartWithQuantity(product.id, quantity);
                        closePanel();
                    };
                } else {
                    addButton.disabled = true;
                    addButton.className = 'btn btn-secondary btn-lg';
                    addButton.innerHTML = '❌ Нет в наличии';
                }
                
                // Показываем панель
                showPanel();
                
                // Инициализируем кнопки количества если товар доступен
                if (product.isAvailable) {
                    setTimeout(() => {
                        updateQuantityButtons(product.id, 1);
                        updateTotalPrice(product.id, 1);
                    }, 100);
                }
                
                // Haptic feedback
                triggerHapticFeedback('light');
                
            } catch (error) {
                console.error('Ошибка при показе деталей товара:', error);
                showAlert('Ошибка при загрузке деталей товара');
            }
        }

        // Функции управления панелями
        function showPanel() {
            const panel = document.getElementById('productPanel');
            const backdrop = document.getElementById('panelBackdrop');
            const footer = document.getElementById('productPanelFooter');
            
            backdrop.classList.add('show');
            panel.classList.add('show');
            footer.style.display = 'block';
            
            // Блокируем прокрутку основного содержимого
            document.body.style.overflow = 'hidden';
        }

        function closePanel() {
            const panel = document.getElementById('productPanel');
            const backdrop = document.getElementById('panelBackdrop');
            const footer = document.getElementById('productPanelFooter');
            
            backdrop.classList.remove('show');
            panel.classList.remove('show');
            footer.style.display = 'none';
            
            // Разблокируем прокрутку основного содержимого
            document.body.style.overflow = 'auto';
        }

        // Закрытие панели по ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePanel();
            }
        });

        // Функция для получения badge статуса товара
        function getStatusBadge(product) {
            let statusClass = 'secondary';
            if (product.availability_status === 'В наличии') statusClass = 'success';
            else if (product.availability_status === 'Заканчивается') statusClass = 'warning';  
            else if (product.availability_status === 'Нет в наличии') statusClass = 'danger';
            
            return `<span class="badge bg-${statusClass} shadow-sm">${product.availability_status}</span>`;
        }

        // Функции для работы с количеством товара
        function changeQuantity(productId, delta) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
            const currentQuantity = parseInt(quantityInput.value) || 1;
            const newQuantity = Math.max(1, Math.min(parseInt(quantityInput.max), currentQuantity + delta));
            
            quantityInput.value = newQuantity;
            updateTotalPrice(productId, newQuantity);
            updateQuantityButtons(productId, newQuantity);
            
            // Haptic feedback
            triggerHapticFeedback('light');
        }

        function validateQuantity(productId) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
            const quantity = parseInt(quantityInput.value);
            const maxQuantity = parseInt(quantityInput.max);
            
            if (isNaN(quantity) || quantity < 1) {
                quantityInput.value = 1;
            } else if (quantity > maxQuantity) {
                quantityInput.value = maxQuantity;
                showAlert(`Максимальное количество: ${maxQuantity} шт.`, 'warning');
            }
            
            const finalQuantity = parseInt(quantityInput.value);
            updateTotalPrice(productId, finalQuantity);
            updateQuantityButtons(productId, finalQuantity);
        }

        function updateTotalPrice(productId, quantity) {
            const product = window.cachedProductsData[productId];
            if (product) {
                const totalPrice = product.price * quantity;
                const formattedTotal = new Intl.NumberFormat('ru-RU', {
                    style: 'currency',
                    currency: 'RUB'
                }).format(totalPrice);
                
                const totalPriceElement = document.getElementById(`totalPrice-${productId}`);
                if (totalPriceElement) {
                    totalPriceElement.textContent = formattedTotal;
                }
            }
        }

        function updateQuantityButtons(productId, quantity) {
            const decreaseBtn = document.getElementById(`decreaseBtn-${productId}`);
            const increaseBtn = document.getElementById(`increaseBtn-${productId}`);
            const quantityInput = document.getElementById(`quantity-${productId}`);
            
            if (decreaseBtn) {
                decreaseBtn.disabled = quantity <= 1;
            }
            
            if (increaseBtn && quantityInput) {
                const maxQuantity = parseInt(quantityInput.max);
                increaseBtn.disabled = quantity >= maxQuantity;
            }
        }

        // Функция добавления товара в корзину с количеством
        function addToCartWithQuantity(productId, quantity) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('quantity', quantity);
            
            fetch(`/cart/add/${productId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`Товар добавлен в корзину (${quantity} шт.)! 🛒`);
                    updateCartCounter();
                    triggerHapticFeedback('success');
                } else {
                    showAlert(data.message || 'Ошибка при добавлении товара', 'error');
                    triggerHapticFeedback('error');
                }
            })
            .catch(error => {
                console.error('Ошибка при добавлении товара в корзину:', error);
                showAlert('Ошибка при добавлении товара в корзину', 'error');
                triggerHapticFeedback('error');
            });
        }
        
        function showCart() {
            // Получаем данные корзины
            fetch('/cart')
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        showCheckoutModal(data.items, data.total);
                    } else {
                        showAlert('Ваша корзина пуста', 'warning');
                    }
                })
                .catch(error => {
                    console.error('Ошибка при получении корзины:', error);
                    showAlert('Ошибка при загрузке корзины', 'error');
                });
        }

        // Показать модальное окно оформления заказа
        function showCheckoutModal(cartItems, total) {
            const modalHtml = `
                <div class="modal fade" id="checkoutModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">🛒 Оформление заказа</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Товары в корзине -->
                                <div class="mb-4">
                                    <h6>Ваши товары:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            ${cartItems.map(item => `
                                                <tr id="cart-item-${item.id}">
                                                    <td style="width: 60px;">
                                                        ${item.photo_url ? 
                                                            `<img src="${item.photo_url}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">` :
                                                            '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-image text-muted"></i></div>'
                                                        }
                                                    </td>
                                                    <td>
                                                        <div><strong>${item.name}</strong></div>
                                                        <div class="text-muted small mb-2">${item.formatted_price} за шт.</div>
                                                        
                                                        <!-- Контролы количества -->
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" 
                                                                    class="btn btn-outline-secondary" 
                                                                    onclick="changeCartQuantity(${item.id}, -1)"
                                                                    style="width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 1;">
                                                                −
                                                            </button>
                                                            
                                                            <input type="number" 
                                                                   class="form-control mx-1 text-center" 
                                                                   id="cart-quantity-${item.id}"
                                                                   value="${item.quantity}" 
                                                                   min="1" 
                                                                   max="${item.available_quantity || 999}"
                                                                   onchange="updateCartQuantity(${item.id})"
                                                                   style="width: 45px; height: 24px; font-size: 12px; padding: 2px;">
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-outline-secondary" 
                                                                    onclick="changeCartQuantity(${item.id}, 1)"
                                                                    style="width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 1;">
                                                                +
                                                            </button>
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger ms-1" 
                                                                    onclick="removeFromCart(${item.id})"
                                                                    title="Удалить товар"
                                                                    style="width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 10px; line-height: 1;">
                                                                🗑️
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="text-end" style="width: 100px;">
                                                        <div class="fw-bold" id="cart-total-${item.id}">${item.formatted_total}</div>
                                                        <small class="text-muted">${item.quantity} шт.</small>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                            <tr class="table-active">
                                                <td colspan="2"><strong>Итого:</strong></td>
                                                <td class="text-end"><strong id="checkout-total">${total}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Дополнительная информация -->
                                <div>
                                    <form id="checkoutForm">
                                        <div class="mb-3">
                                            <label class="form-label">Комментарий к заказу (необязательно)</label>
                                            <textarea class="form-control" name="notes" rows="3" placeholder="Укажите дополнительные пожелания, адрес доставки или другие комментарии..."></textarea>
                                        </div>
                                    </form>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>Для связи с вами будет использован ваш Telegram аккаунт</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn " data-bs-dismiss="modal">Отмена</button>
                                <button type="button" class="btn " onclick="submitOrder()">
                                    <i class="fas fa-check me-2"></i>Оформить заказ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Удаляем предыдущее модальное окно если есть
            const existingModal = document.getElementById('checkoutModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Добавляем новое модальное окно
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Показываем модальное окно
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
            
            // Очистка после закрытия
            document.getElementById('checkoutModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }


        // Функции для управления количеством в корзине
        function changeCartQuantity(cartId, delta) {
            const quantityInput = document.getElementById(`cart-quantity-${cartId}`);
            const currentQuantity = parseInt(quantityInput.value) || 1;
            const maxQuantity = parseInt(quantityInput.max);
            const newQuantity = Math.max(1, Math.min(maxQuantity, currentQuantity + delta));
            
            quantityInput.value = newQuantity;
            updateCartQuantity(cartId);
        }

        function updateCartQuantity(cartId) {
            const quantityInput = document.getElementById(`cart-quantity-${cartId}`);
            const quantity = parseInt(quantityInput.value);
            const maxQuantity = parseInt(quantityInput.max);
            
            if (isNaN(quantity) || quantity < 1) {
                quantityInput.value = 1;
                return;
            } else if (quantity > maxQuantity) {
                quantityInput.value = maxQuantity;
                showAlert(`Максимальное количество: ${maxQuantity} шт.`, 'warning');
            }

            const finalQuantity = parseInt(quantityInput.value);
            
            // Отправляем запрос на обновление количества
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('quantity', finalQuantity);
            formData.append('_method', 'PATCH');

            fetch(`/cart/update/${cartId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем отображение
                    updateCartItemDisplay(cartId, finalQuantity, data.item_total, data.formatted_item_total);
                    updateCheckoutTotal();
                    triggerHapticFeedback('light');
                } else {
                    showAlert(data.message || 'Ошибка при обновлении количества', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка при обновлении количества:', error);
                showAlert('Ошибка при обновлении количества', 'error');
            });
        }

        function updateCartItemDisplay(cartId, quantity, itemTotal, formattedItemTotal) {
            const totalElement = document.getElementById(`cart-total-${cartId}`);
            const quantityDisplay = totalElement.nextElementSibling;
            
            if (totalElement) {
                totalElement.textContent = formattedItemTotal;
            }
            
            if (quantityDisplay) {
                quantityDisplay.textContent = `${quantity} шт.`;
            }
        }

        function updateCheckoutTotal() {
            // Пересчитываем общую сумму
            fetch('/cart')
                .then(response => response.json())
                .then(data => {
                    if (data.total) {
                        const totalElement = document.getElementById('checkout-total');
                        if (totalElement) {
                            totalElement.textContent = data.formatted_total || data.total;
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка при обновлении общей суммы:', error);
                });
        }

        function removeFromCart(cartId) {
            if (!confirm('Удалить товар из корзины?')) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('_method', 'DELETE');

            fetch(`/cart/remove/${cartId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    const row = document.getElementById(`cart-item-${cartId}`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Обновляем счетчик корзины и общую сумму
                    updateCartCounter();
                    updateCheckoutTotal();
                    
                    showAlert('Товар удален из корзины');
                    triggerHapticFeedback('success');
                    
                    // Если корзина пуста, закрываем модальное окно
                    const remainingItems = document.querySelectorAll('[id^="cart-item-"]');
                    if (remainingItems.length === 0) {
                        bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
                        showAlert('Корзина пуста', 'info');
                    }
                } else {
                    showAlert(data.message || 'Ошибка при удалении товара', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении товара:', error);
                showAlert('Ошибка при удалении товара', 'error');
            });
        }

        function submitOrder() {
            if (!userData) {
                showAlert('Ошибка получения данных пользователя', 'error');
                return;
            }

            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);
            
            const orderData = {
                bot_short_name: '{{ $shortName }}',
                user_data: userData,
                notes: formData.get('notes')
            };

            // Показываем индикатор загрузки
            const submitBtn = document.querySelector('#checkoutModal .btn-success');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Оформляем...';
            submitBtn.disabled = true;

            fetch('/cart/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Закрываем модальное окно
                    bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
                    
                    // Показываем успешное сообщение
                    showAlert(`✅ ${data.message}\\n\\n📋 Номер заказа: ${data.order.order_number}`, 'success');
                    
                    // Обновляем счетчик корзины
                    updateCartCounter();
                    
                    // Уведомляем Telegram Web App о успешном заказе
                    if (window.Telegram?.WebApp?.HapticFeedback) {
                        window.Telegram.WebApp.HapticFeedback.notificationOccurred('success');
                    }
                } else {
                    showAlert(data.message || 'Ошибка при оформлении заказа', 'error');
                    
                    // Восстанавливаем кнопку
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showAlert('Произошла ошибка при оформлении заказа', 'error');
                
                // Восстанавливаем кнопку
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // Функция поделиться товаром
        function shareProduct(productId) {
            const product = window.cachedProductsData[productId];
            if (!product) return;
            
            const shareText = `🛍️ ${product.name}\n💰 ${product.formatted_price}\n\n${product.description || 'Отличный товар!'}`;
            
            if (window.Telegram?.WebApp?.openTelegramLink) {
                // Используем Telegram WebApp API для шаринга
                const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(shareText)}`;
                window.Telegram.WebApp.openTelegramLink(shareUrl);
            } else if (navigator.share) {
                // Используем Web Share API
                navigator.share({
                    title: product.name,
                    text: shareText,
                    url: window.location.href
                }).catch(err => console.log('Ошибка при шаринге:', err));
            } else {
                // Fallback: копируем в буфер обмена
                navigator.clipboard.writeText(`${shareText}\n\n${window.location.href}`)
                    .then(() => showToast('Скопировано в буфер обмена! 📋'))
                    .catch(() => showToast('Не удалось скопировать', 'error'));
            }
        }

        // Логирование для отладки
        console.log('Mini App script загружен');
        console.log('Telegram доступен:', !!window.Telegram);
        console.log('Telegram WebApp доступен:', !!window.Telegram?.WebApp);
        
        // Инициализируем счетчик корзины при загрузке
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(updateCartCounter, 1000);
            loadCategories();
            initSearch();
        });
    </script>

    <!-- Стили для поиска и категорий -->
    <style>
        /* Стили для поиска */
        .search-container {
            padding: 0 15px;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            border-radius: 9px;
            padding-left: 20px;
            padding-right: 50px;
            border: 2px solid #e9ecef;
            font-size: 14px;
        }

        .search-input:focus {
            border-color: var(--tg-theme-button-color, #007bff);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .search-btn {
            border-radius: 9px;
            background: var(--tg-theme-button-color, #007bff);
            border: none;
            padding: 0.5rem 1rem;
        }

        .search-btn:hover {
            background: var(--tg-theme-button-color, #0056b3);
        }

        /* Стили для слайдера категорий */
        .categories-slider-container {
            padding: 0px;
            overflow: hidden;
        }

        .categories-slider {
            position: relative;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .categories-slider::-webkit-scrollbar {
            display: none;
        }

        .categories-track {
            display: flex;
            gap: 10px;
            padding: 5px 0;
            scroll-snap-type: x mandatory;
        }

        .category-card {
            min-width: 250px;
            max-width: 280px;
            flex-shrink: 0;
            scroll-snap-align: start;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .category-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .category-placeholder {
            width: 80px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .category-info {
            flex: 1;
        }

        .category-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
            color: var(--tg-theme-text-color, #333);
        }

        .category-description {
            font-size: 13px;
            color: var(--tg-theme-hint-color, #666);
            line-height: 1.3;
        }

        .category-products-count {
            font-size: 12px;
            color: var(--tg-theme-button-color, #007bff);
            font-weight: 500;
        }

        /* Адаптивность */
        @media (max-width: 576px) {
            .category-card {
                min-width: 220px;
                max-width: 250px;
            }
            
            .category-image,
            .category-placeholder {
                width: 100px;
                height: 100px;
            }
        }

        /* Индикатор загрузки для категорий */
        .categories-loading {
            text-align: center;
            padding: 20px;
            color: var(--tg-theme-hint-color, #666);
        }

        /* Скрытие товаров при поиске */
        .search-results {
            margin-top: 15px;
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--tg-theme-hint-color, #666);
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        /* FontAwesome иконки для мини-приложения */
        .fas, .far, .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro" !important;
            font-weight: 900 !important;
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
        }

        .far {
            font-weight: 400 !important;
        }

        .fab {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400 !important;
        }

        /* Убедимся, что иконки отображаются корректно */
        i.fas, i.far, i.fab {
            min-width: 1em;
            text-align: center;
        }
    </style>

    <script>
        // Переменные для поиска и категорий
        let allProducts = [];
        let allCategories = [];
        let isSearchActive = false;

        // Инициализация поиска
        function initSearch() {
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch(e.target.value);
                }, 300); // Задержка для предотвращения частых запросов
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch(e.target.value);
                }
            });

            // Загружаем все товары для поиска
            loadAllProducts();
        }

        // Загрузка всех товаров для поиска
        async function loadAllProducts() {
            try {
                const shortName = '{{ $shortName }}';
                const response = await fetch(`/${shortName}/api/products`);
                
                if (response.ok) {
                    allProducts = await response.json();
                    console.log('Загружено товаров для поиска:', allProducts.length);
                }
            } catch (error) {
                console.error('Ошибка при загрузке товаров:', error);
            }
        }

        // Загрузка категорий
        async function loadCategories() {
            try {
                const shortName = '{{ $shortName }}';
                const response = await fetch(`/${shortName}/api/categories`);
                
                if (response.ok) {
                    allCategories = await response.json();
                    renderCategories(allCategories);
                    
                    if (allCategories.length > 0) {
                        document.getElementById('categoriesContainer').style.display = 'block';
                    }
                } else {
                    console.log('Категории не найдены или ошибка загрузки');
                }
            } catch (error) {
                console.error('Ошибка при загрузке категорий:', error);
            }
        }

        // Отрисовка категорий
        function renderCategories(categories) {
            const track = document.getElementById('categoriesTrack');
            
            if (categories.length === 0) {
                document.getElementById('categoriesContainer').style.display = 'none';
                return;
            }

            track.innerHTML = categories.map(category => `
                <div class="category-card" onclick="filterByCategory(${category.id}, '${category.name}')">
                    <div class="card h-200">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="category-info">
                                    <div class="category-name">${category.name}</div>
                                    ${category.description ? `<div class="category-description">${category.description}</div>` : ''}
                                    <div class="category-products-count">${category.products_count || 0} товаров</div>
                                </div>
                                ${category.photo_url 
                                    ? `<img src="${category.photo_url}" class="category-image " alt="${category.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                       <div class="category-placeholder" style="display: none;">
                                           <i class="fas fa-folder"></i>
                                       </div>`
                                    : `<div class="category-placeholder ">
                                           <i class="fas fa-folder"></i>
                                       </div>`
                                }
                                
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Поиск товаров
        function performSearch(query = null) {
            const searchInput = document.getElementById('searchInput');
            const searchQuery = query !== null ? query : searchInput.value.trim();

            if (searchQuery === '') {
                // Если поиск пустой, показываем все товары
                showAllProducts();
                isSearchActive = false;
                return;
            }

            isSearchActive = true;

            // Фильтруем товары по названию, описанию и артикулу
            const filteredProducts = allProducts.filter(product => {
                const name = product.name.toLowerCase();
                const description = (product.description || '').toLowerCase();
                const article = (product.article || '').toLowerCase();
                const search = searchQuery.toLowerCase();

                return name.includes(search) || 
                       description.includes(search) || 
                       article.includes(search);
            });

            renderSearchResults(filteredProducts, searchQuery);
        }

        // Отрисовка результатов поиска
        function renderSearchResults(products, query) {
            const container = document.getElementById('productsContainer');
            const title = document.getElementById('productsTitle');
            
            title.textContent = `🔍 Результаты поиска: "${query}"`;

            if (products.length === 0) {
                container.innerHTML = `
                    <h5 id="productsTitle">🔍 Результаты поиска: "${query}"</h5>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h6>Ничего не найдено</h6>
                        <p>Попробуйте изменить запрос или просмотреть все товары</p>
                        <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                            Показать все товары
                        </button>
                    </div>
                `;
                return;
            }

            const productsHTML = products.map(product => `
                <div class="col-6 col-md-4">
                    <div class="card product-card h-100" onclick="showProductDetails(${product.id})" style="cursor: pointer;">
                        ${product.photo_url 
                            ? `<img src="${product.photo_url}" class="card-img-top" alt="${product.name}" 
                                 style="height: 150px; object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="card-img-top d-none justify-content-center align-items-center bg-light" 
                                    style="height: 150px; color: #6c757d;">
                                   <div class="text-center">
                                       <i class="fas fa-image fa-2x mb-2"></i><br>
                                       <small>Ошибка загрузки</small>
                                   </div>
                               </div>`
                            : `<div class="card-img-top d-flex justify-content-center align-items-center bg-light" 
                                    style="height: 150px; color: #6c757d;">
                                   <div class="text-center">
                                       <i class="fas fa-cube fa-2x mb-2"></i><br>
                                       <small>Без фото</small>
                                   </div>
                               </div>`
                        }
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2" style="font-size: 14px; line-height: 1.3;">${product.name}</h6>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="text-success fw-bold">${formatPrice(product.price)} ₽</div>
                                    ${product.quantity > 0 
                                        ? `<small class="text-muted">В наличии: ${product.quantity}</small>`
                                        : `<small class="text-danger">Нет в наличии</small>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `
                <h5 id="productsTitle">🔍 Результаты поиска: "${query}"</h5>
                <div class="row">
                    ${productsHTML}
                </div>
            `;
        }

        // Фильтрация по категории
        function filterByCategory(categoryId, categoryName) {
            isSearchActive = true;
            
            const categoryProducts = allProducts.filter(product => 
                product.category_id === categoryId
            );

            const container = document.getElementById('productsContainer');
            const title = document.getElementById('productsTitle');
            
            title.textContent = `📁 Категория: ${categoryName}`;

            if (categoryProducts.length === 0) {
                container.innerHTML = `
                    <h5 id="productsTitle">📁 Категория: ${categoryName}</h5>
                    <div class="no-results">
                        <i class="fas fa-folder-open"></i>
                        <h6>В этой категории пока нет товаров</h6>
                        <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                            Показать все товары
                        </button>
                    </div>
                `;
                return;
            }

            renderSearchResults(categoryProducts, `Категория: ${categoryName}`);

            // Очищаем поле поиска
            document.getElementById('searchInput').value = '';
        }

        // Показать все товары
        function showAllProducts() {
            isSearchActive = false;
            document.getElementById('searchInput').value = '';
            
            // Перезагружаем страницу чтобы вернуть изначальное состояние
            window.location.reload();
        }

        // Форматирование цены
        function formatPrice(price) {
            return Number(price).toLocaleString('ru-RU', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }
    </script>
</body>
</html>