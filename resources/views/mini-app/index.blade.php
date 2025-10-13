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

        <div id="user-info" class="user-info" style="display: none;">
            <h5>👤 Информация о пользователе</h5>
            <div id="user-details"></div>
        </div>

        <div class="bot-info">
            <h5>🤖 Информация о боте</h5>
            <p><strong>Имя бота:</strong> {{ $bot->bot_name }}</p>
            <p><strong>Username:</strong> @{{ $bot->bot_username }}</p>
            <p><strong>Mini App:</strong> {{ $shortName }}</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card" onclick="showAlert('Профиль')">
                <div class="feature-icon">👤</div>
                <h6>Профиль</h6>
                <small class="text-muted">Ваш профиль</small>
            </div>

            <div class="feature-card" onclick="showAlert('Настройки')">
                <div class="feature-icon">⚙️</div>
                <h6>Настройки</h6>
                <small class="text-muted">Параметры</small>
            </div>

            <div class="feature-card" onclick="showAlert('Помощь')">
                <div class="feature-icon">❓</div>
                <h6>Помощь</h6>
                <small class="text-muted">FAQ и поддержка</small>
            </div>

            <div class="feature-card" onclick="sendData()">
                <div class="feature-icon">💾</div>
                <h6>Сохранить</h6>
                <small class="text-muted">Сохранить данные</small>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="closeApp()">
                Закрыть приложение
            </button>
        </div>
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

        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', initApp);

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