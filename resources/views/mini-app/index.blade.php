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
                    <input type="text" id="search-input" class="form-control" placeholder="Поиск по артикулу или названию товара..." onkeypress="if(event.key==='Enter') searchGoods()">
                    <button class="btn btn-primary" onclick="searchGoods()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-center mt-2 gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="loadRandomGoods()">
                        <i class="fas fa-random"></i> Случайные товары
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="loadInitialGoods()">
                        <i class="fas fa-star"></i> Популярные товары
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
                console.log('Начинаем инициализацию Mini App...');
                console.log('window.Telegram:', window.Telegram);
                console.log('window.Telegram.WebApp:', window.Telegram?.WebApp);
                
                // Проверяем доступность Telegram WebApp
                const isTelegramWebApp = window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.initData;
                const isDevelopmentMode = !isTelegramWebApp && (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1') || window.location.hostname.includes('ospanel'));
                
                console.log('isTelegramWebApp:', isTelegramWebApp);
                console.log('isDevelopmentMode:', isDevelopmentMode);
                
                if (isTelegramWebApp) {
                    console.log('Инициализируем Telegram WebApp...');
                    
                    // Настраиваем Telegram WebApp
                    tg.ready();
                    tg.expand();

                    // Применяем тему Telegram
                    if (tg.themeParams) {
                        document.body.style.backgroundColor = tg.themeParams.bg_color || '#ffffff';
                        document.body.style.color = tg.themeParams.text_color || '#000000';
                        console.log('Тема применена:', tg.themeParams);
                    }

                    // Показываем кнопку "Назад" если нужно
                    if (tg.BackButton) {
                        tg.BackButton.show();
                        tg.BackButton.onClick(() => {
                            console.log('Нажата кнопка назад');
                            tg.close();
                        });
                    }

                    // Получаем данные пользователя
                    console.log('initDataUnsafe:', tg.initDataUnsafe);
                    console.log('initData:', tg.initData);
                    
                    if (tg.initDataUnsafe && tg.initDataUnsafe.user) {
                        userData = tg.initDataUnsafe.user;
                        console.log('Данные пользователя из initDataUnsafe:', userData);
                        displayUserInfo(userData);
                    } else if (tg.initData) {
                        // Попробуем извлечь данные пользователя из initData
                        console.log('Попытка извлечь данные пользователя из initData...');
                        userData = parseUserFromInitData(tg.initData);
                        if (userData) {
                            console.log('Данные пользователя извлечены из initData:', userData);
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

                // Загружаем начальные товары
                loadInitialGoods();

            } catch (error) {
                console.error('Ошибка инициализации:', error);
                showErrorMessage(error.message || 'Неизвестная ошибка инициализации');
            }
        }

        // Функция для безопасного отображения ошибок
        function showErrorMessage(message) {
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Ошибка инициализации</h5>
                        <p>${message}</p>
                        <small>Проверьте настройки бота в Telegram</small>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary" onclick="location.reload()">
                                <i class="fas fa-redo"></i> Попробовать снова
                            </button>
                            <button class="btn btn-outline-secondary" onclick="showDebugInfo()">
                                <i class="fas fa-bug"></i> Отладка
                            </button>
                        </div>
                    </div>
                `;
            } else {
                console.error('Элемент loading не найден, ошибка:', message);
            }
        }

        // Функция отображения отладочной информации
        function showDebugInfo() {
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                const debugInfo = `
                    <div class="alert alert-info mt-3">
                        <h6>Отладочная информация:</h6>
                        <small>
                            <strong>URL:</strong> ${window.location.href}<br>
                            <strong>User Agent:</strong> ${navigator.userAgent}<br>
                            <strong>Telegram:</strong> ${window.Telegram ? 'Доступен' : 'Недоступен'}<br>
                            <strong>WebApp:</strong> ${window.Telegram?.WebApp ? 'Доступен' : 'Недоступен'}<br>
                            <strong>initData:</strong> ${window.Telegram?.WebApp?.initData || 'Отсутствует'}<br>
                            <strong>Platform:</strong> ${window.Telegram?.WebApp?.platform || 'Неизвестно'}
                        </small>
                    </div>
                `;
                loadingEl.innerHTML += debugInfo;
            }
        }

        // Отображение информации о пользователе (заглушка - элементы удалены из HTML)
        function displayUserInfo(user) {
            if (user) {
                console.log('Данные пользователя получены:', {
                    id: user.id,
                    first_name: user.first_name,
                    last_name: user.last_name,
                    username: user.username,
                    language_code: user.language_code
                });
            } else {
                console.warn('Данные пользователя не переданы');
            }
        }

        // Парсинг данных пользователя из initData
        function parseUserFromInitData(initData) {
            try {
                const urlParams = new URLSearchParams(initData);
                const userParam = urlParams.get('user');
                if (userParam) {
                    const user = JSON.parse(decodeURIComponent(userParam));
                    console.log('Пользователь успешно извлечен из initData:', user);
                    return user;
                }
            } catch (error) {
                console.error('Ошибка парсинга данных пользователя:', error);
            }
            return null;
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

        // Haptic Feedback для кнопок (инициализируется после загрузки DOM)
        function setupHapticFeedback() {
            const elements = document.querySelectorAll('.feature-card, .btn');
            console.log('Настраиваем Haptic Feedback для', elements.length, 'элементов');
            
            elements.forEach(el => {
                el.addEventListener('click', () => {
                    if (tg && tg.HapticFeedback) {
                        try {
                            tg.HapticFeedback.impactOccurred('light');
                        } catch (error) {
                            console.warn('Ошибка Haptic Feedback:', error);
                        }
                    }
                });
            });
        }

        // ===== FORUM-AUTO МАГАЗИН =====
        
        let cart = [];
        const apiBase = '/api/forum-auto/{{ $shortName }}';

        // Загрузить товары при старте (случайные или популярные)
        async function loadInitialGoods() {
            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Загружаем товары...</div>';

            try {
                // Сначала проверим статус API
                let response = await fetch(`${apiBase}/test-credentials`);
                let testResult = await response.json();
                
                console.log('API credentials test:', testResult);
                
                if (!testResult.success) {
                    resultsContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Добро пожаловать в демо-версию каталога!</strong><br>
                            ${testResult.error || 'API Forum-Auto не настроен - показываем демонстрационные товары'}
                            <div class="mt-2">
                                <small class="text-muted">Для доступа к реальному каталогу обратитесь к администратору</small>
                            </div>
                        </div>
                        <div class="mt-3">

                            <button class="btn btn-outline-secondary" onclick="searchSampleGoods()">
                                <i class="fas fa-search"></i> Попробовать поиск
                            </button>
                        </div>
                    `;
                    return;
                }

                // Сначала пробуем случайные товары
                response = await fetch(`${apiBase}/goods/random?limit=12`);
                let data = await response.json();

                console.log('Random goods response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                    return;
                }

                // Если случайные не загрузились, пробуем популярные
                response = await fetch(`${apiBase}/goods/popular`);
                data = await response.json();

                console.log('Popular goods response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    // Показываем сообщение, что нет данных
                    resultsContainer.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            <strong>Нет данных для отображения</strong><br>
                            Попробуйте выполнить поиск по артикулу или названию товара.
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Initial goods loading error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Ошибка загрузки товаров</strong><br>
                        Не удалось подключиться к каталогу товаров.
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadInitialGoods()">
                                <i class="fas fa-redo"></i> Повторить попытку
                            </button>
                        </div>
                    </div>
                `;
            }
        }

        // Загрузить случайные товары
        async function loadRandomGoods() {
            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Загружаем случайные товары...</div>';

            try {
                const response = await fetch(`${apiBase}/goods/random?limit=15`);
                const data = await response.json();

                console.log('Random goods response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    // Если API не вернул данные, показываем сообщение
                    resultsContainer.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            <strong>Нет случайных товаров</strong><br>
                            Попробуйте выполнить поиск.
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Random goods loading error:', error);
                await loadInitialGoods();
            }
        }

        // Поиск по примерам популярных артикулов
        async function searchSampleGoods() {
            const sampleArticles = ['OC47', 'W712', 'LF787', 'OX123D', 'HU7008z'];
            const randomArticle = sampleArticles[Math.floor(Math.random() * sampleArticles.length)];
            
            const searchInput = document.getElementById('search-input');
            searchInput.value = randomArticle;
            
            await searchGoods();
        }



        // Расширенный поиск товаров по всем критериям
        async function searchGoods() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.trim();
            
            if (!searchTerm || searchTerm.length < 2) {
                showAlert('Введите минимум 2 символа для поиска (артикул или название товара)');
                return;
            }

            const resultsContainer = document.getElementById('goods-results');
            resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Расширенный поиск товаров...</div>';

            try {
                // Используем новый расширенный поиск с фильтрацией по 70% совпадению
                const response = await fetch(`${apiBase}/goods/advanced-search?search=${encodeURIComponent(searchTerm)}&min_match=70&limit=20`);
                const data = await response.json();

                console.log('Advanced search response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                    
                    // Показываем информацию о найденных результатах
                    const totalFound = data.filter ? data.filter.total_found : data.data.length;
                    if (totalFound > 0) {
                        const infoElement = document.createElement('div');
                        infoElement.className = 'alert alert-info mt-2';
                        infoElement.innerHTML = `
                            <i class="fas fa-info-circle"></i>
                            Найдено ${totalFound} товаров с совпадением 70% и выше для запроса "${searchTerm}"
                        `;
                        resultsContainer.insertBefore(infoElement, resultsContainer.firstChild);
                    }
                } else {
                    // Если по расширенному поиску ничего не найдено, попробуем базовый поиск
                    await fallbackSearch(searchTerm, resultsContainer);
                }
            } catch (error) {
                console.error('Advanced search error:', error);
                // При ошибке пробуем базовый поиск
                await fallbackSearch(searchTerm, resultsContainer);
            }
        }

        // Резервный поиск, если расширенный не дал результатов
        async function fallbackSearch(searchTerm, resultsContainer) {
            try {
                resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Поиск по артикулу...</div>';
                
                const response = await fetch(`${apiBase}/goods/search?search=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();

                console.log('Fallback search response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayGoods(data.data);
                } else {
                    // Если и базовый поиск не дал результатов, покажем доступные бренды
                    await searchBrands(searchTerm, resultsContainer);
                }
            } catch (error) {
                console.error('Fallback search error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Ошибка поиска. Попробуйте позже или загрузите случайные товары.
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" onclick="loadRandomGoods()">
                            <i class="fas fa-random"></i> Показать случайные товары
                        </button>
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
                            Найдены бренды для артикула "${searchTerm}". Выберите бренд для поиска:
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
                    
                    html += `
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-secondary btn-sm me-2" onclick="loadRandomGoods()">
                                <i class="fas fa-random"></i> Случайные товары
                            </button>
                            <button class="btn btn-info btn-sm" onclick="loadInitialGoods()">
                                <i class="fas fa-star"></i> Популярные товары
                            </button>
                        </div>
                    `;
                    
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>По запросу "${searchTerm}" ничего не найдено</p>
                            <small>Попробуйте изменить поисковый запрос или посмотрите товары ниже</small>
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm me-2" onclick="loadRandomGoods()">
                                    <i class="fas fa-random"></i> Случайные товары
                                </button>
                                <button class="btn btn-info btn-sm" onclick="loadInitialGoods()">
                                    <i class="fas fa-star"></i> Популярные товары
                                </button>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Brands search error:', error);
                resultsContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>Произошла ошибка при поиске "${searchTerm}"</p>
                        <small>Попробуйте еще раз или посмотрите другие товары</small>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="loadRandomGoods()">
                                <i class="fas fa-random"></i> Случайные товары
                            </button>
                            <button class="btn btn-info btn-sm" onclick="loadInitialGoods()">
                                <i class="fas fa-star"></i> Популярные товары
                            </button>
                        </div>
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
            
            if (!goods || goods.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>Товары не найдены</p>
                        <small>Попробуйте изменить поисковый запрос</small>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" onclick="loadRandomGoods()">
                                <i class="fas fa-random"></i> Показать случайные товары
                            </button>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            goods.forEach(item => {
                // Определяем цвет badge в зависимости от процента совпадения
                let matchBadge = '';
                if (typeof item.match_percent !== 'undefined') {
                    let badgeClass = 'bg-secondary';
                    if (item.match_percent >= 95) badgeClass = 'bg-success';
                    else if (item.match_percent >= 85) badgeClass = 'bg-info';
                    else if (item.match_percent >= 75) badgeClass = 'bg-warning';
                    
                    matchBadge = `<span class="badge ${badgeClass} ms-2">Совпадение: ${item.match_percent}%</span>`;
                }

                // Информация о поле, в котором найдено совпадение
                let matchingFieldInfo = '';
                if (item.matching_field) {
                    const fieldNames = {
                        'art': 'артикул',
                        'name': 'название', 
                        'brand': 'бренд',
                        'gid': 'код'
                    };
                    const fieldName = fieldNames[item.matching_field] || item.matching_field;
                    matchingFieldInfo = `<small class="text-info ms-1">(найдено в: ${fieldName})</small>`;
                }

                html += `
                    <div class="goods-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${item.name || 'Название не указано'}</h6>
                                <div class="mb-2">
                                    <span class="brand-badge">${item.brand || 'Неизвестный бренд'}</span>
                                    <small class="text-muted ms-2">Арт: ${item.art || 'N/A'}</small>
                                    ${matchBadge}
                                    ${matchingFieldInfo}
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="price-highlight">${item.price ? parseFloat(item.price).toFixed(2) : '0.00'} ₽</span>
                                        ${(item.num && item.num > 0) ? 
                                            `<small class="text-success ms-2">В наличии: ${item.num} шт.</small>` : 
                                            '<small class="text-danger ms-2">Нет в наличии</small>'
                                        }
                                        ${item.kr && item.kr > 1 ? `<small class="text-muted ms-2">Кратность: ${item.kr}</small>` : ''}
                                    </div>
                                    ${(item.num && item.num > 0) ? `
                                        <button class="btn btn-sm btn-primary" onclick="addToCart('${item.gid}', '${(item.name || '').replace(/'/g, '\\\'') }', ${item.price || 0}, '${(item.brand || '').replace(/'/g, '\\\'')}', '${(item.art || '').replace(/'/g, '\\\'')}')">
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

        // Логирование для отладки
        console.log('Mini App script загружен');
        console.log('Telegram доступен:', !!window.Telegram);
        console.log('Telegram WebApp доступен:', !!window.Telegram?.WebApp);
    </script>
</body>
</html>