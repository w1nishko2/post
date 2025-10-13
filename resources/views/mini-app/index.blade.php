<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bot->bot_name }} - Mini App</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Telegram WebApp JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <style>
        body {
            background: var(--tg-theme-bg-color, #ffffff);
            color: var(--tg-theme-text-color, #000000);
            font-family: var(--tg-theme-font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .container {
            max-width: 100%;
            padding: 16px;
        }

        .welcome-card {
            background: var(--tg-theme-secondary-bg-color, #f8f9fa);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            text-align: center;
            border: 1px solid var(--tg-theme-hint-color, #e9ecef);
        }

        .bot-info {
            background: var(--tg-theme-secondary-bg-color, #f8f9fa);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .btn-primary {
            background-color: var(--tg-theme-button-color, #007bff);
            border-color: var(--tg-theme-button-color, #007bff);
            color: var(--tg-theme-button-text-color, #ffffff);
        }

        .user-info {
            background: var(--tg-theme-secondary-bg-color, #f8f9fa);
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }

        .feature-card {
            background: var(--tg-theme-secondary-bg-color, #f8f9fa);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--tg-theme-hint-color, #e9ecef);
        }

        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        #loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .shop-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .goods-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }

        .goods-item h6 {
            color: #007bff;
            margin-bottom: 5px;
        }

        .brand-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
        }

        .brand-badge:hover {
            background: #1976d2;
            color: white;
        }

        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 90%;
            max-width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.3);
            transition: right 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .cart-sidebar.open {
            right: 0;
        }

        .cart-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .cart-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .cart-footer {
            padding: 20px;
            border-top: 1px solid #eee;
        }

        .btn-close-cart {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            margin-left: auto;
        }

        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }

        .cart-overlay.show {
            display: block;
        }

        .cart-item {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .price-highlight {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div id="loading" class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
        <p class="mt-2">Инициализация Mini App...</p>
    </div>

    <div id="app" class="container" style="display: none;">
        <div class="welcome-card">
            <h1 class="h3 mb-3">🚀 {{ $bot->bot_name }}</h1>
            <p class="text-muted mb-0">Добро пожаловать в наше Mini App!</p>
        </div>

     

        @if($bot->hasForumAutoApi())
        <!-- Магазин Forum-Auto -->
        <div class="shop-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>🛒 Магазин автозапчастей</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="toggleCart()">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="badge bg-danger">0</span>
                </button>
            </div>

            <!-- Поиск товаров -->
            <div class="search-section mb-3">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control" placeholder="Поиск по артикулу...">
                    <button class="btn btn-primary" onclick="searchGoods()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Бренды -->
            <div class="brands-section mb-3" style="display: none;">
                <h6>Популярные бренды:</h6>
                <div id="brands-list" class="d-flex flex-wrap gap-2"></div>
            </div>

            <!-- Результаты поиска -->
            <div id="goods-results" class="goods-section">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Введите артикул для поиска товаров</p>
                </div>
            </div>

            <!-- Корзина -->
            <div id="cart-sidebar" class="cart-sidebar">
                <div class="cart-header">
                    <h6>Корзина</h6>
                    <button class="btn-close-cart" onclick="toggleCart()">×</button>
                </div>
                <div id="cart-items" class="cart-body"></div>
                <div class="cart-footer">
                    <button class="btn btn-success w-100" onclick="submitOrder()">
                        Оформить заказ
                    </button>
                </div>
            </div>

            <!-- Оверлей корзины -->
            <div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Магазин недоступен</strong><br>
            Владелец бота не настроил интеграцию с Forum-Auto API.
        </div>
        @endif

       

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let tg = window.Telegram.WebApp;
        let userData = null;

        // Инициализация Mini App
        function initApp() {
            try {
                // Настраиваем Telegram WebApp
                tg.ready();
                tg.expand();

                // Применяем тему Telegram
                document.body.style.backgroundColor = tg.themeParams.bg_color || '#ffffff';
                document.body.style.color = tg.themeParams.text_color || '#000000';

                // Показываем кнопку "Назад" если нужно
                if (tg.BackButton) {
                    tg.BackButton.show();
                    tg.BackButton.onClick(() => {
                        tg.close();
                    });
                }

                // Получаем данные пользователя
                if (tg.initDataUnsafe && tg.initDataUnsafe.user) {
                    userData = tg.initDataUnsafe.user;
                    displayUserInfo(userData);
                }

                // Скрываем загрузку и показываем приложение
                document.getElementById('loading').style.display = 'none';
                document.getElementById('app').style.display = 'block';

                console.log('Mini App инициализирован');
                console.log('Telegram WebApp данные:', tg.initDataUnsafe);

            } catch (error) {
                console.error('Ошибка инициализации:', error);
                document.getElementById('loading').innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Ошибка инициализации</h5>
                        <p>Не удалось подключиться к Telegram WebApp</p>
                        <small>Убедитесь, что приложение запущено из Telegram</small>
                    </div>
                `;
            }
        }

        // Отображение информации о пользователе
        function displayUserInfo(user) {
            const userInfoEl = document.getElementById('user-info');
            const userDetailsEl = document.getElementById('user-details');
            
            if (user) {
                userDetailsEl.innerHTML = `
                    <p><strong>Имя:</strong> ${user.first_name || 'Не указано'}</p>
                    ${user.last_name ? `<p><strong>Фамилия:</strong> ${user.last_name}</p>` : ''}
                    ${user.username ? `<p><strong>Username:</strong> @${user.username}</p>` : ''}
                    <p><strong>ID:</strong> ${user.id}</p>
                    <p><strong>Язык:</strong> ${user.language_code || 'Не определен'}</p>
                `;
                userInfoEl.style.display = 'block';
            }
        }

        // Показать уведомление
        function showAlert(message) {
            if (tg.showAlert) {
                tg.showAlert(`Вы выбрали: ${message}`);
            } else {
                alert(`Вы выбрали: ${message}`);
            }
        }

        // Отправить данные боту
        function sendData() {
            const data = {
                action: 'save_data',
                user_data: userData,
                timestamp: Date.now(),
                bot_short_name: '{{ $shortName }}'
            };

            if (tg.sendData) {
                tg.sendData(JSON.stringify(data));
                showAlert('Данные отправлены боту!');
            } else {
                console.log('Данные для отправки:', data);
                showAlert('Данные подготовлены к отправке');
            }
        }

        // Закрыть приложение
        function closeApp() {
            if (tg.close) {
                tg.close();
            } else {
                showAlert('Приложение будет закрыто');
            }
        }

        // Haptic Feedback для кнопок
        document.querySelectorAll('.feature-card, .btn').forEach(el => {
            el.addEventListener('click', () => {
                if (tg.HapticFeedback) {
                    tg.HapticFeedback.impactOccurred('light');
                }
            });
        });

        // ===== FORUM-AUTO МАГАЗИН =====
        
        let cart = [];
        const apiBase = '/api/forum-auto/{{ $shortName }}';

        // Загрузить популярные товары при старте
        async function loadPopularGoods() {
            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Загружаем товары...</div>';

            try {
                const response = await fetch(`${apiBase}/goods/popular`);
                const data = await response.json();

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    resultsContainer.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box fa-2x mb-2"></i>
                            <p>Товары временно недоступны</p>
                            <small>Попробуйте воспользоваться поиском</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Popular goods error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Не удалось загрузить товары. Воспользуйтесь поиском.
                    </div>
                `;
            }
        }

        // Поиск товаров
        async function searchGoods() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.trim();
            
            if (!searchTerm || searchTerm.length < 2) {
                showAlert('Введите минимум 2 символа для поиска (артикул товара)');
                return;
            }

            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Поиск по артикулу...</div>';

            try {
                const response = await fetch(`${apiBase}/goods/search?search=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();

                console.log('Search response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    // Если товары не найдены, покажем доступные бренды для этого артикула
                    await searchBrands(searchTerm, resultsContainer);
                }
            } catch (error) {
                console.error('Search error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Ошибка поиска. Попробуйте позже.
                    </div>
                `;
            }
        }

        // Поиск брендов по артикулу
        async function searchBrands(searchTerm, resultsContainer) {
            try {
                const response = await fetch(`${apiBase}/brands?art=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();

                console.log('Brands response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    let html = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Найдены бренды для артикула "${searchTerm}":
                        </div>
                        <div class="row">
                    `;
                    
                    data.data.forEach(brand => {
                        html += `
                            <div class="col-6 col-md-4 mb-2">
                                <button class="btn btn-outline-primary btn-sm w-100" onclick="searchByBrand('${searchTerm}', '${brand.brand}')">
                                    ${brand.brand}
                                </button>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>По артикулу "${searchTerm}" ничего не найдено</p>
                            <small>Убедитесь, что артикул введен правильно</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Brands search error:', error);
                resultsContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>По артикулу "${searchTerm}" ничего не найдено</p>
                        <small>Убедитесь, что артикул введен правильно</small>
                    </div>
                `;
            }
        }

        // Поиск товаров по артикулу и бренду
        async function searchByBrand(article, brand) {
            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Поиск товаров бренда...</div>';

            try {
                const response = await fetch(`${apiBase}/goods?art=${encodeURIComponent(article)}&br=${encodeURIComponent(brand)}`);
                const data = await response.json();

                console.log('Brand goods response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    resultsContainer.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>Товары бренда "${brand}" для артикула "${article}" не найдены</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Brand search error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Ошибка поиска по бренду. Попробуйте позже.
                    </div>
                `;
            }
        }

        // Отображение товаров
        function displayGoods(goods) {
            const resultsContainer = document.getElementById('goods-results');
            
            let html = '';
            goods.forEach(item => {
                html += `
                    <div class="goods-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6>${item.name}</h6>
                                <div class="mb-2">
                                    <span class="brand-badge">${item.brand}</span>
                                    <small class="text-muted ms-2">Арт: ${item.art}</small>
                                    ${typeof item.match_percent !== 'undefined' ? `<span class="badge bg-info ms-2">Совпадение: ${item.match_percent}%</span>` : ''}
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="price-highlight">${parseFloat(item.price).toFixed(2)} ₽</span>
                                        ${item.num > 0 ? `<small class="text-success ms-2">В наличии: ${item.num}</small>` : '<small class="text-danger ms-2">Нет в наличии</small>'}
                                    </div>
                                    ${item.num > 0 ? `
                                        <button class="btn btn-sm btn-primary" onclick="addToCart('${item.gid}', '${item.name}', ${item.price}, '${item.brand}', '${item.art}')">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML = html;
        }

        // Добавить товар в корзину
        function addToCart(goodsCode, name, price, brand, art) {
            // Проверяем, есть ли товар уже в корзине
            const existingItem = cart.find(item => item.goodsCode === goodsCode);
            
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    goodsCode,
                    name,
                    price: parseFloat(price),
                    brand,
                    art,
                    quantity: 1
                });
            }
            
            updateCartUI();
            showAlert(`${name} добавлен в корзину`);
            
            // Haptic feedback
            if (tg.HapticFeedback) {
                tg.HapticFeedback.impactOccurred('medium');
            }
        }

        // Обновить UI корзины
        function updateCartUI() {
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            
            // Обновляем счетчик
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'inline' : 'none';
            
            // Обновляем содержимое корзины
            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="text-center text-muted py-4">Корзина пуста</div>';
                return;
            }
            
            let html = '';
            let total = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                html += `
                    <div class="cart-item">
                        <h6 class="mb-1">${item.name}</h6>
                        <div class="mb-2">
                            <small class="text-muted">${item.brand} • ${item.art}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="changeQuantity(${index}, -1)">−</button>
                                <button class="btn btn-outline-secondary">${item.quantity}</button>
                                <button class="btn btn-outline-secondary" onclick="changeQuantity(${index}, 1)">+</button>
                            </div>
                            <div>
                                <span class="price-highlight">${itemTotal.toFixed(2)} ₽</span>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                <div class="mt-3 pt-3 border-top">
                    <h5>Итого: <span class="price-highlight">${total.toFixed(2)} ₽</span></h5>
                </div>
            `;
            
            cartItems.innerHTML = html;
        }

        // Изменить количество товара в корзине
        function changeQuantity(index, delta) {
            if (cart[index]) {
                cart[index].quantity += delta;
                if (cart[index].quantity <= 0) {
                    cart.splice(index, 1);
                }
                updateCartUI();
            }
        }

        // Удалить товар из корзины
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartUI();
        }

        // Переключить отображение корзины
        function toggleCart() {
            const sidebar = document.getElementById('cart-sidebar');
            const overlay = document.getElementById('cart-overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        // Оформить заказ
        async function submitOrder() {
            if (cart.length === 0) {
                showAlert('Корзина пуста');
                return;
            }

            try {
                for (const item of cart) {
                    const response = await fetch(`${apiBase}/cart/add`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            goods_code: item.goodsCode,
                            quantity: item.quantity,
                            comment: `Заказ из Mini App`
                        })
                    });

                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.error || 'Ошибка добавления товара');
                    }
                }

                cart = [];
                updateCartUI();
                toggleCart();
                showAlert('Заказ успешно оформлен!');

            } catch (error) {
                console.error('Order error:', error);
                showAlert('Ошибка при оформлении заказа: ' + error.message);
            }
        }

        // Поиск по Enter
        document.getElementById('search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchGoods();
            }
        });

        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', () => {
            initApp();
            // Загружаем популярные товары в магазине
            if (document.getElementById('goods-results')) {
                loadPopularGoods();
            }
        });

        // Для тестирования вне Telegram
        if (!window.Telegram) {
            console.warn('Telegram WebApp не обнаружен. Режим отладки.');
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('app').style.display = 'block';
            }, 1000);
        }
    </script>
</body>
</html>