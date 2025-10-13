@extends('layouts.app')

@section('content')
@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Альтернативный способ открытия модального окна через data-атрибуты
            const modalTrigger = document.createElement('button');
            modalTrigger.setAttribute('data-bs-toggle', 'modal');
            modalTrigger.setAttribute('data-bs-target', '#addBotModal');
            modalTrigger.style.display = 'none';
            document.body.appendChild(modalTrigger);
            modalTrigger.click();
            document.body.removeChild(modalTrigger);
        });
    </script>
@endif
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Управление Telegram ботами</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBotModal">
                        <i class="fas fa-plus"></i> Добавить бота
                    </button>
                </div>

                <div class="card-body">
                    @if(auth()->user()->telegramBots->count() > 0)
                        <div class="row">
                            @foreach(auth()->user()->telegramBots as $bot)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ $bot->bot_name }}</h6>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#editBotModal{{ $bot->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('telegram-bots.toggle', $bot) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-{{ $bot->is_active ? 'warning' : 'success' }} btn-sm">
                                                        <i class="fas fa-{{ $bot->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('telegram-bots.destroy', $bot) }}" 
                                                      class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого бота?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Username:</strong> @{{ $bot->bot_username }}
                                            </div>
                                            <div class="mb-2">
                                                <strong>Токен:</strong> 
                                                <code>{{ $bot->masked_token }}</code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Статус:</strong>
                                                <span class="badge bg-{{ $bot->is_active ? 'success' : 'secondary' }}">
                                                    {{ $bot->is_active ? 'Активен' : 'Неактивен' }}
                                                </span>
                                            </div>
                                            @if($bot->hasMiniApp())
                                                <div class="mb-2">
                                                    <strong>Mini App:</strong>
                                                    <a href="{{ $bot->getMiniAppUrl() }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-external-link-alt"></i> {{ $bot->mini_app_short_name }}
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">{{ $bot->getDisplayMiniAppUrl() }}</small>
                                                </div>
                                            @else
                                                <div class="mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            data-bs-toggle="modal" data-bs-target="#setupMiniAppModal{{ $bot->id }}">
                                                        <i class="fas fa-cog"></i> Настроить Mini App
                                                    </button>
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <strong>Forum-Auto API:</strong>
                                                @if($bot->hasForumAutoApi())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Настроен
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">Логин: {{ $bot->masked_forum_auto_login }}</small>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times"></i> Не настроен
                                                    </span>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-outline-warning mt-1" 
                                                            data-bs-toggle="modal" data-bs-target="#setupForumAutoModal{{ $bot->id }}">
                                                        <i class="fas fa-cog"></i> Настроить Forum-Auto
                                                    </button>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                Обновлен: {{ $bot->updated_at->format('d.m.Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                            <h5>У вас пока нет Telegram ботов</h5>
                            <p class="text-muted">Добавьте своего первого бота, чтобы начать создавать Mini App</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBotModal">
                                <i class="fas fa-plus"></i> Добавить первого бота
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления бота -->
<div class="modal fade" id="addBotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('telegram-bots.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить Telegram бота</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bot_name" class="form-label">Название бота</label>
                        <input type="text" class="form-control @error('bot_name') is-invalid @enderror" 
                               id="bot_name" name="bot_name" value="{{ old('bot_name') }}" required>
                        @error('bot_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bot_username" class="form-label">Username бота</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control @error('bot_username') is-invalid @enderror" 
                                   id="bot_username" name="bot_username" value="{{ old('bot_username') }}" required>
                        </div>
                        @error('bot_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bot_token" class="form-label">Токен бота</label>
                        <input type="text" class="form-control @error('bot_token') is-invalid @enderror" 
                               id="bot_token" name="bot_token" value="{{ old('bot_token') }}" 
                               placeholder="1234567890:ABCdefGHijklMNOpqrsTUvwxyz" required>
                        <div class="form-text">Получите токен от @BotFather в Telegram</div>
                        @error('bot_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="api_id" class="form-label">API ID (необязательно)</label>
                                <input type="text" class="form-control @error('api_id') is-invalid @enderror" 
                                       id="api_id" name="api_id" value="{{ old('api_id') }}">
                                @error('api_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="api_hash" class="form-label">API Hash (необязательно)</label>
                                <input type="text" class="form-control @error('api_hash') is-invalid @enderror" 
                                       id="api_hash" name="api_hash" value="{{ old('api_hash') }}">
                                @error('api_hash')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6>Настройки Mini App (необязательно)</h6>

                    <div class="mb-3">
                        <label for="mini_app_short_name" class="form-label">Имя Mini App</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ config('app.url') }}/</span>
                            <input type="text" class="form-control @error('mini_app_short_name') is-invalid @enderror" 
                                   id="mini_app_short_name" name="mini_app_short_name" value="{{ old('mini_app_short_name') }}" 
                                   placeholder="myapp" maxlength="64">
                        </div>
                        <div class="form-text">Введите только имя приложения. URL будет: {{ config('app.url') }}/ваше_имя</div>
                        @error('mini_app_short_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" id="mini_app_url" name="mini_app_url" value="{{ old('mini_app_url') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить бота</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach(auth()->user()->telegramBots as $bot)
    <!-- Модальное окно редактирования бота -->
    <div class="modal fade" id="editBotModal{{ $bot->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('telegram-bots.update', $bot) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Редактировать бота: {{ $bot->bot_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="bot_name_{{ $bot->id }}" class="form-label">Название бота</label>
                            <input type="text" class="form-control" id="bot_name_{{ $bot->id }}" 
                                   name="bot_name" value="{{ $bot->bot_name }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="api_id_{{ $bot->id }}" class="form-label">API ID</label>
                                    <input type="text" class="form-control" id="api_id_{{ $bot->id }}" 
                                           name="api_id" value="{{ $bot->api_id }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="api_hash_{{ $bot->id }}" class="form-label">API Hash</label>
                                    <input type="text" class="form-control" id="api_hash_{{ $bot->id }}" 
                                           name="api_hash" value="{{ $bot->api_hash }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mini_app_short_name_{{ $bot->id }}" class="form-label">Имя Mini App</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.url') }}/</span>
                                <input type="text" class="form-control" id="mini_app_short_name_{{ $bot->id }}" 
                                       name="mini_app_short_name" value="{{ $bot->mini_app_short_name }}" maxlength="64">
                            </div>
                        </div>

                        <input type="hidden" id="mini_app_url_{{ $bot->id }}" name="mini_app_url" value="{{ $bot->mini_app_url }}">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active_{{ $bot->id }}" 
                                   name="is_active" value="1" {{ $bot->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active_{{ $bot->id }}">
                                Активный бот
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно настройки Mini App -->
    <div class="modal fade" id="setupMiniAppModal{{ $bot->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('telegram-bots.setup-mini-app', $bot) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Настроить Mini App для {{ $bot->bot_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <div class="mb-3">
                        <label for="mini_app_short_name_setup_{{ $bot->id }}" class="form-label">Имя Mini App</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ config('app.url') }}/</span>
                            <input type="text" class="form-control" id="mini_app_short_name_setup_{{ $bot->id }}" 
                                   name="mini_app_short_name" value="{{ $bot->mini_app_short_name }}" 
                                   placeholder="myapp" maxlength="64" required>
                        </div>
                        <div class="form-text">URL будет: {{ config('app.url') }}/<span class="mini-app-preview">{{ $bot->mini_app_short_name ?: 'ваше_имя' }}</span></div>
                    </div>

                    <input type="hidden" id="mini_app_url_setup_{{ $bot->id }}" name="mini_app_url" value="{{ $bot->mini_app_url }}">                        <div class="mb-3">
                            <label for="menu_button_text_{{ $bot->id }}" class="form-label">Текст кнопки меню</label>
                            <input type="text" class="form-control" id="menu_button_text_{{ $bot->id }}" 
                                   name="menu_button_text" value="Открыть приложение" 
                                   placeholder="Открыть приложение" maxlength="16">
                            <div class="form-text">Максимум 16 символов</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Настроить Mini App</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно настройки Forum-Auto API -->
    <div class="modal fade" id="setupForumAutoModal{{ $bot->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('telegram-bots.setup-forum-auto', $bot) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Настроить Forum-Auto API для {{ $bot->bot_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Внимание:</strong> Эти данные будут использоваться для доступа к API Forum-Auto в вашем Mini App. 
                            Убедитесь, что у вас есть активный аккаунт на forum-auto.ru.
                        </div>

                        <div class="mb-3">
                            <label for="forum_auto_login_{{ $bot->id }}" class="form-label">Логин Forum-Auto</label>
                            <input type="text" class="form-control @error('forum_auto_login') is-invalid @enderror" 
                                   id="forum_auto_login_{{ $bot->id }}" name="forum_auto_login" 
                                   value="{{ old('forum_auto_login', $bot->forum_auto_login) }}" 
                                   placeholder="615286_pynzaru_andrey" required>
                            @error('forum_auto_login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Логин для доступа к API Forum-Auto</div>
                        </div>

                        <div class="mb-3">
                            <label for="forum_auto_pass_{{ $bot->id }}" class="form-label">Пароль Forum-Auto</label>
                            <input type="password" class="form-control @error('forum_auto_pass') is-invalid @enderror" 
                                   id="forum_auto_pass_{{ $bot->id }}" name="forum_auto_pass" 
                                   placeholder="ji45fDI9nCbj" {{ $bot->forum_auto_pass ? '' : 'required' }}>
                            @error('forum_auto_pass')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                @if($bot->forum_auto_pass)
                                    Пароль сохранен. Оставьте пустым, если не хотите изменять.
                                @else
                                    Пароль для доступа к API Forum-Auto
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" 
                                       id="forum_auto_enabled_{{ $bot->id }}" name="forum_auto_enabled" 
                                       value="1" {{ old('forum_auto_enabled', $bot->forum_auto_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="forum_auto_enabled_{{ $bot->id }}">
                                    Включить Forum-Auto API для этого бота
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6>Тестирование подключения:</h6>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="testForumAutoConnection({{ $bot->id }})">
                                <i class="fas fa-vial"></i> Проверить подключение
                            </button>
                            <div id="connection-test-result-{{ $bot->id }}" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить настройки
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    const baseUrl = '{{ config("app.url") }}';

    // Функция для обновления URL Mini App
    function updateMiniAppUrl(shortNameInput, urlInput, previewElement = null) {
        const shortName = shortNameInput.value.trim();
        if (shortName) {
            const fullUrl = `${baseUrl}/${shortName}`;
            urlInput.value = fullUrl;
            
            if (previewElement) {
                previewElement.textContent = shortName;
            }
        } else {
            urlInput.value = '';
            if (previewElement) {
                previewElement.textContent = 'ваше_имя';
            }
        }
    }

    // Автоматическое заполнение username из токена
    document.getElementById('bot_token').addEventListener('input', function() {
        const token = this.value.trim();
        const usernameInput = document.getElementById('bot_username');
        
        if (token && token.includes(':')) {
            // Пытаемся получить информацию о боте через AJAX
            fetch(`https://api.telegram.org/bot${token}/getMe`)
                .then(response => response.json())
                .then(data => {
                    if (data.ok && data.result.username) {
                        usernameInput.value = data.result.username;
                        document.getElementById('bot_name').value = data.result.first_name || '';
                    }
                })
                .catch(error => {
                    // Игнорируем ошибки CORS в браузере
                });
        }
    });

    // Автоматическое формирование URL для новых ботов
    document.getElementById('mini_app_short_name').addEventListener('input', function() {
        updateMiniAppUrl(this, document.getElementById('mini_app_url'));
    });

    // Автоматическое формирование URL для редактирования ботов
    @foreach(auth()->user()->telegramBots ?? [] as $bot)
        document.getElementById('mini_app_short_name_{{ $bot->id }}').addEventListener('input', function() {
            updateMiniAppUrl(this, document.getElementById('mini_app_url_{{ $bot->id }}'));
        });

        // Для модального окна настройки Mini App
        document.getElementById('mini_app_short_name_setup_{{ $bot->id }}').addEventListener('input', function() {
            const previewElement = this.closest('.modal-body').querySelector('.mini-app-preview');
            updateMiniAppUrl(this, document.getElementById('mini_app_url_setup_{{ $bot->id }}'), previewElement);
        });
    @endforeach

    // Функция для тестирования подключения к Forum-Auto API
    function testForumAutoConnection(botId) {
        const resultDiv = document.getElementById(`connection-test-result-${botId}`);
        const loginInput = document.getElementById(`forum_auto_login_${botId}`);
        const passInput = document.getElementById(`forum_auto_pass_${botId}`);
        
        const login = loginInput.value.trim();
        const pass = passInput.value.trim();
        
        if (!login || !pass) {
            resultDiv.innerHTML = '<div class="alert alert-warning alert-sm">Введите логин и пароль для тестирования</div>';
            return;
        }
        
        resultDiv.innerHTML = '<div class="alert alert-info alert-sm"><i class="fas fa-spinner fa-spin"></i> Проверяем подключение...</div>';
        
        // Отправляем AJAX запрос для проверки подключения
        fetch('/test-forum-auto-connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                login: login,
                pass: pass
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let clientInfoText = '';
                if (data.client_info && data.client_info['ИД клиента']) {
                    clientInfoText = `<br><small>Клиент ID: ${data.client_info['ИД клиента']}</small>`;
                }
                
                resultDiv.innerHTML = `
                    <div class="alert alert-success alert-sm">
                        <i class="fas fa-check"></i> Подключение успешно!
                        ${clientInfoText}
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger alert-sm">
                        <i class="fas fa-times"></i> Ошибка подключения: ${data.error || 'Неизвестная ошибка'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Connection test error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger alert-sm"><i class="fas fa-times"></i> Произошла ошибка при проверке подключения</div>';
        });
    }


</script>
@endpush
@endsection
