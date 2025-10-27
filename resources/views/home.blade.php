@extends('layouts.app')

@section('content')
@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                try {
                    const modal = document.querySelector('.admin-modal.show');
                    if (modal) {
                        modal.classList.add('show');
                    }
                } catch (error) {
                    console.error('Modal error:', error);
                }
            }, 100);
        });
    </script>
@endif

<div class="admin-container">
    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle admin-me-2"></i>
            {{ session('success') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            {{ session('error') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill active" href="{{ route('home') }}">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="{{ route('products.select-bot') }}">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <!-- Основной контент ботов -->
    @if(request()->is('home') || request()->routeIs('home'))
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">
                <i class="fas fa-robot admin-me-2"></i>
                Мои Telegram боты
            </h5>
            <button class="admin-btn admin-btn-primary" onclick="showModal('addBotModal')">
                <i class="fas fa-plus admin-me-2"></i>
                Добавить бота
            </button>
        </div>

        <div class="admin-card-body">
            @if(auth()->user()->telegramBots && auth()->user()->telegramBots->count() > 0)
                <div class="admin-bot-grid">
                    @foreach(auth()->user()->telegramBots as $bot)
                        <div class="admin-bot-card">
                            <div class="admin-bot-header">
                                <div class="admin-bot-avatar {{ $bot->is_active ? '' : 'inactive' }}">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="admin-bot-info">
                                    <h6 class="admin-mb-1">{{ $bot->bot_name }}</h6>
                                    <div class="admin-bot-username admin-text-muted">@{{ $bot->bot_username }}</div>
                                </div>
                            </div>

                            <div class="admin-bot-stats admin-mb-3">
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Статус</div>
                                    <div class="admin-bot-stat-value">
                                        @if($bot->is_active)
                                            <span class="admin-badge admin-badge-success">Активен</span>
                                        @else
                                            <span class="admin-badge admin-badge-warning">Неактивен</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Товаров</div>
                                    <div class="admin-bot-stat-value">{{ $bot->products()->count() }}</div>
                                </div>
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Заказов</div>
                                    <div class="admin-bot-stat-value">{{ $bot->orders()->count() ?? 0 }}</div>
                                </div>
                            </div>

                            <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                                <a href="{{ route('bot.products.index', $bot) }}" class="admin-btn admin-btn-sm admin-flex-1">
                                    <i class="fas fa-boxes admin-me-1"></i>
                                    Товары
                                </a>
                                <button class="admin-btn admin-btn-sm" onclick="showModal('editBotModal{{ $bot->id }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="admin-btn admin-btn-sm" onclick="showModal('setupMiniAppModal{{ $bot->id }}')">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <form method="POST" action="{{ route('telegram-bots.destroy', $bot) }}" class="admin-d-inline" onsubmit="return confirm('Вы уверены?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admin-empty-state">
                    <i class="fas fa-robot"></i>
                    <h3>У вас пока нет ботов</h3>
                    <p class="admin-mb-4">Создайте первого бота, чтобы начать продавать товары через Telegram</p>
                    <button class="admin-btn admin-btn-primary" onclick="showModal('addBotModal')">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создать первого бота
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Модальное окно добавления бота -->
<div class="admin-modal" id="addBotModal">
    <div class="admin-modal-dialog">
        <div class="admin-modal-content">
            <form method="POST" action="{{ route('telegram-bots.store') }}">
                @csrf
                <div class="admin-modal-header">
                    <h5 class="admin-modal-title">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить нового бота
                    </h5>
                    <button type="button" class="admin-modal-close" onclick="hideModal('addBotModal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="bot_token" class="admin-form-label required">Токен бота</label>
                    <input type="text" class="admin-form-control" id="bot_token" name="bot_token" required
                           placeholder="Введите токен от @BotFather">
                    <div class="admin-form-text">Получите токен у @BotFather в Telegram</div>
                </div>

                <div class="admin-form-group">
                    <label for="bot_username" class="admin-form-label required">Username бота</label>
                    <div class="admin-input-group">
                        <div class="admin-input-group-text">@</div>
                        <input type="text" class="admin-form-control" id="bot_username" name="bot_username" required
                               placeholder="username_bot">
                    </div>
                    <div class="admin-form-text">Username бота без символа @</div>
                </div>

                <div class="admin-form-group">
                    <label for="bot_name" class="admin-form-label required">Название бота</label>
                    <input type="text" class="admin-form-control" id="bot_name" name="bot_name" required
                           placeholder="Мой магазин">
                </div>

                <div class="admin-form-group">
                    <label for="admin_telegram_id" class="admin-form-label">ID администратора в Telegram</label>
                    <input type="text" class="admin-form-control" id="admin_telegram_id" name="admin_telegram_id"
                           placeholder="123456789">
                    <div class="admin-form-text">Ваш Telegram ID для получения уведомлений о заказах. Узнать можно у @userinfobot</div>
                </div>

                <div class="admin-form-group">
                    <label for="admin_telegram_username" class="admin-form-label">Username администратора</label>
                    <div class="admin-input-group">
                        <div class="admin-input-group-text">@</div>
                        <input type="text" class="admin-form-control" id="admin_telegram_username" name="admin_telegram_username"
                               placeholder="username">
                    </div>
                    <div class="admin-form-text">Ваш Telegram username для создания ссылок в заказах (необязательно)</div>
                </div>

                <div class="admin-form-group admin-mb-0">
                    <div class="admin-form-check">
                        <input type="checkbox" class="admin-form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label for="is_active" class="admin-form-check-label">Активировать бота сразу</label>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn" onclick="hideModal('addBotModal')">Отмена</button>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus admin-me-2"></i>
                    Создать бота
                </button>
            </div>
        </form>
    </div>
</div>
</div>

@foreach(auth()->user()->telegramBots ?? [] as $bot)
    <!-- Модальное окно редактирования бота -->
    <div class="admin-modal" id="editBotModal{{ $bot->id }}">
        <div class="admin-modal-dialog">
            <div class="admin-modal-content">
                <form method="POST" action="{{ route('telegram-bots.update', $bot) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="admin-modal-header">
                        <h5 class="admin-modal-title">
                            <i class="fas fa-edit admin-me-2"></i>
                            Редактировать бота
                        </h5>
                        <button type="button" class="admin-modal-close" onclick="hideModal('editBotModal{{ $bot->id }}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <div class="admin-modal-body">
                    <div class="admin-form-group">
                        <label for="bot_token_{{ $bot->id }}" class="admin-form-label required">Токен бота</label>
                        <input type="text" class="admin-form-control" id="bot_token_{{ $bot->id }}" name="bot_token" 
                               value="{{ $bot->bot_token }}" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="bot_username_{{ $bot->id }}" class="admin-form-label required">Username бота</label>
                        <div class="admin-input-group">
                            <div class="admin-input-group-text">@</div>
                            <input type="text" class="admin-form-control" id="bot_username_{{ $bot->id }}" name="bot_username" 
                                   value="{{ $bot->bot_username }}" required>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="bot_name_{{ $bot->id }}" class="admin-form-label required">Название бота</label>
                        <input type="text" class="admin-form-control" id="bot_name_{{ $bot->id }}" name="bot_name" 
                               value="{{ $bot->bot_name }}" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="admin_telegram_id_{{ $bot->id }}" class="admin-form-label">ID администратора в Telegram</label>
                        <input type="text" class="admin-form-control" id="admin_telegram_id_{{ $bot->id }}" name="admin_telegram_id"
                               value="{{ $bot->admin_telegram_id }}" placeholder="123456789">
                        <div class="admin-form-text">Ваш Telegram ID для получения уведомлений о заказах. Узнать можно у @userinfobot</div>
                    </div>

                    <div class="admin-form-group">
                        <label for="admin_telegram_username_{{ $bot->id }}" class="admin-form-label">Username администратора</label>
                        <div class="admin-input-group">
                            <div class="admin-input-group-text">@</div>
                            <input type="text" class="admin-form-control" id="admin_telegram_username_{{ $bot->id }}" name="admin_telegram_username"
                                   value="{{ ltrim($bot->admin_telegram_username ?? '', '@') }}" placeholder="username">
                        </div>
                        <div class="admin-form-text">Ваш Telegram username для создания ссылок в заказах (необязательно)</div>
                    </div>

                    <div class="admin-form-group">
                        <label for="logo_{{ $bot->id }}" class="admin-form-label">Логотип Mini App</label>
                        @if($bot->logo)
                            <div class="admin-mb-2">
                                <img src="{{ asset('storage/' . $bot->logo) }}" alt="Логотип" style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                                <div class="admin-form-check admin-mt-2">
                                    <input type="checkbox" class="admin-form-check-input" id="remove_logo_{{ $bot->id }}" name="remove_logo" value="1">
                                    <label for="remove_logo_{{ $bot->id }}" class="admin-form-check-label">Удалить логотип</label>
                                </div>
                            </div>
                        @endif
                        <input type="file" class="admin-form-control" id="logo_{{ $bot->id }}" name="logo" accept="image/png,image/webp,image/jpeg">
                        <div class="admin-form-text">Поддерживаемые форматы: PNG, WebP, JPEG. Максимальный размер: 10 МБ</div>
                    </div>

                    <div class="admin-form-group admin-mb-0">
                        <div class="admin-form-check">
                            <input type="checkbox" class="admin-form-check-input" id="is_active_{{ $bot->id }}" name="is_active" 
                                   value="1" {{ $bot->is_active ? 'checked' : '' }}>
                            <label for="is_active_{{ $bot->id }}" class="admin-form-check-label">Бот активен</label>
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn" onclick="hideModal('editBotModal{{ $bot->id }}')">Отмена</button>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- Модальное окно настройки Mini App -->
    <div class="admin-modal" id="setupMiniAppModal{{ $bot->id }}">
        <div class="admin-modal-dialog">
            <div class="admin-modal-content">
                <form method="POST" action="{{ route('telegram-bots.setup-mini-app', $bot) }}">
                    @csrf
                    <div class="admin-modal-header">
                        <h5 class="admin-modal-title">
                            <i class="fas fa-cog admin-me-2"></i>
                            Настройка Mini App
                        </h5>
                        <button type="button" class="admin-modal-close" onclick="hideModal('setupMiniAppModal{{ $bot->id }}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <div class="admin-modal-body">
                    <!-- Отображение ошибок валидации -->
                    @if ($errors->any())
                        <div class="admin-alert admin-alert-danger admin-mb-3">
                            <i class="fas fa-exclamation-triangle admin-me-2"></i>
                            <strong>Ошибки при сохранении:</strong>
                            <ul class="admin-mb-0" style="margin-top: 8px; padding-left: 20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="admin-form-group">
                        <label for="mini_app_short_name_setup_{{ $bot->id }}" class="admin-form-label required">Короткое имя Mini App</label>
                        <input type="text" 
                               class="admin-form-control" 
                               id="mini_app_short_name_setup_{{ $bot->id }}" 
                               name="mini_app_short_name" 
                               value="{{ $bot->mini_app_short_name }}"
                               placeholder="myshop"
                               pattern="[a-zA-Z0-9_]+"
                               required>
                        <div class="admin-form-text">
                            <i class="fas fa-info-circle"></i>
                            Используется в URL Mini App. Только латинские буквы, цифры и подчеркивания
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="mini_app_url_setup_{{ $bot->id }}" class="admin-form-label">
                            URL Mini App 
                            <span class="admin-text-success" id="url_status_{{ $bot->id }}" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        </label>
                        <input type="text" 
                               class="admin-form-control" 
                               id="mini_app_url_setup_{{ $bot->id }}" 
                               name="mini_app_url" 
                               value="{{ $bot->mini_app_url }}" 
                               readonly
                               style="background-color: #f5f5f5; cursor: not-allowed;"
                               placeholder="https://example.com/myshop">
                        <div class="admin-form-text">
                            <i class="fas fa-magic"></i> 
                            Автоматически генерируется на основе короткого имени
                        </div>
                    </div>

                    <div class="admin-form-group admin-mb-0">
                        <label for="menu_button_text_setup_{{ $bot->id }}" class="admin-form-label">
                            Текст кнопки меню
                            <span class="admin-text-muted" style="font-size: 0.85em;">(макс. 16 символов)</span>
                        </label>
                        <input type="text" 
                               class="admin-form-control" 
                               id="menu_button_text_setup_{{ $bot->id }}" 
                               name="menu_button_text" 
                               value="{{ $bot->menu_button['text'] ?? 'Открыть' }}"
                               placeholder="Открыть"
                               maxlength="16">
                        <div class="admin-form-text">
                            <i class="fas fa-text-width"></i> 
                            Текст кнопки в меню Telegram (по умолчанию: "Открыть")
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn" onclick="hideModal('setupMiniAppModal{{ $bot->id }}')">Отмена</button>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endforeach



<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '{{ config("app.url") }}';

    // Функция для показа модального окна
    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    // Функция для скрытия модального окна
    window.hideModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    // Закрытие модальных окон при клике на фон
    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal(modal.id);
            }
        });
    });

    // Закрытие модальных окон по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.admin-modal.show');
            if (openModal) {
                hideModal(openModal.id);
            }
        }
    });

    // Функция для обновления URL Mini App
    function updateMiniAppUrl(shortNameInput, urlInput, botId = null) {
        try {
            if (!shortNameInput || !urlInput) {
                console.error('updateMiniAppUrl: отсутствуют необходимые элементы');
                return;
            }
            
            const shortName = shortNameInput.value.trim();
            console.log('Обновление URL для короткого имени:', shortName);
            
            if (shortName) {
                // Убираем readonly временно для обновления значения
                urlInput.removeAttribute('readonly');
                const fullUrl = `${baseUrl}/${shortName}`;
                urlInput.value = fullUrl;
                // Возвращаем readonly
                urlInput.setAttribute('readonly', 'readonly');
                console.log('URL обновлен на:', fullUrl);
                
                // Показываем индикатор успеха
                if (botId) {
                    const statusIndicator = document.getElementById(`url_status_${botId}`);
                    if (statusIndicator) {
                        statusIndicator.style.display = 'inline';
                    }
                }
            } else {
                urlInput.removeAttribute('readonly');
                urlInput.value = '';
                urlInput.setAttribute('readonly', 'readonly');
                console.log('URL очищен');
                
                // Скрываем индикатор
                if (botId) {
                    const statusIndicator = document.getElementById(`url_status_${botId}`);
                    if (statusIndicator) {
                        statusIndicator.style.display = 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Ошибка в updateMiniAppUrl:', error);
        }
    }

    // Автоматическое заполнение username из токена
    const botTokenInput = document.getElementById('bot_token');
    if (botTokenInput) {
        botTokenInput.addEventListener('input', function() {
            const token = this.value.trim();
            const usernameInput = document.getElementById('bot_username');
            
            if (token && token.includes(':') && usernameInput) {
                fetch(`https://api.telegram.org/bot${token}/getMe`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok && data.result.username) {
                            usernameInput.value = data.result.username;
                            const nameInput = document.getElementById('bot_name');
                            if (nameInput && !nameInput.value) {
                                nameInput.value = data.result.first_name || data.result.username;
                            }
                        }
                    })
                    .catch(error => console.log('Не удалось получить информацию о боте'));
            }
        });
    }

    // Автоматическое формирование URL для новых ботов
    const miniAppShortNameInput = document.getElementById('mini_app_short_name');
    const miniAppUrlInput = document.getElementById('mini_app_url');
    if (miniAppShortNameInput && miniAppUrlInput) {
        miniAppShortNameInput.addEventListener('input', function() {
            updateMiniAppUrl(this, miniAppUrlInput);
        });
    }

    // Автоматическое формирование URL для всех ботов
    @foreach(auth()->user()->telegramBots ?? [] as $bot)
        (function() {
            const botId = {{ $bot->id }};
            const setupShortNameInput = document.getElementById('mini_app_short_name_setup_' + botId);
            const setupUrlInput = document.getElementById('mini_app_url_setup_' + botId);
            const setupForm = document.querySelector('#setupMiniAppModal' + botId + ' form');
            
            if (setupShortNameInput && setupUrlInput) {
                console.log('Инициализация для бота ' + botId);
                
                // Инициализируем URL при загрузке страницы, если есть короткое имя
                const currentShortName = setupShortNameInput.value.trim();
                if (currentShortName) {
                    updateMiniAppUrl(setupShortNameInput, setupUrlInput, botId);
                }
                
                // Добавляем обработчик на изменение короткого имени
                setupShortNameInput.addEventListener('input', function() {
                    console.log('Изменение короткого имени для бота ' + botId + ':', this.value);
                    updateMiniAppUrl(this, setupUrlInput, botId);
                });
                
                // Также обновляем при фокусе (на случай если модальное окно открывается)
                setupShortNameInput.addEventListener('focus', function() {
                    if (this.value.trim()) {
                        updateMiniAppUrl(this, setupUrlInput, botId);
                    }
                });
                
                // Обработчик отправки формы - убедимся что URL заполнен
                if (setupForm) {
                    setupForm.addEventListener('submit', function(e) {
                        const shortName = setupShortNameInput.value.trim();
                        if (!shortName) {
                            e.preventDefault();
                            alert('Пожалуйста, введите короткое имя Mini App');
                            setupShortNameInput.focus();
                            return false;
                        }
                        
                        // Принудительно обновляем URL перед отправкой
                        updateMiniAppUrl(setupShortNameInput, setupUrlInput, botId);
                        
                        const url = setupUrlInput.value.trim();
                        if (!url) {
                            e.preventDefault();
                            alert('Ошибка: URL Mini App не сформирован');
                            return false;
                        }
                        
                        console.log('Отправка формы с данными:', {
                            short_name: shortName,
                            url: url
                        });
                    });
                }
            } else {
                console.error('Не найдены элементы для бота ' + botId);
            }
        })();
    @endforeach
});
</script>
@endsection
