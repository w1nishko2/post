@extends('layouts.app')

@section('content')
@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ждем полной загрузки Bootstrap и всех модальных окон
            setTimeout(function() {
                try {
                    const addBotModal = document.getElementById('addBotModal');
                    if (addBotModal) {
                        // Используем Bootstrap API для открытия модального окна
                        const modal = new bootstrap.Modal(addBotModal);
                        modal.show();
                    } else {
                        console.warn('Модальное окно addBotModal не найдено');
                    }
                } catch (error) {
                    console.error('Ошибка при открытии модального окна:', error);
                }
            }, 100);
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

            <!-- Навигационная панель -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link {{ request()->is('home') || request()->routeIs('home') ? 'active' : '' }}" 
                           href="{{ route('home') }}">
                            Мои боты
                        </a>
                        <a class="nav-link {{ request()->is('products*') || request()->routeIs('products.*') ? 'active' : '' }}" 
                           href="{{ route('products.select-bot') }}">
                            Мои товары
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Контент для ботов (показывается только на главной странице) -->
            @if(request()->is('home') || request()->routeIs('home'))
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
                                                        <i class="fas fa-external-link"></i> {{ $bot->mini_app_short_name }}
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
            @endif
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

                    <div class="mb-3">
                        <label for="admin_telegram_id" class="form-label">ID администратора (необязательно)</label>
                        <input type="text" class="form-control @error('admin_telegram_id') is-invalid @enderror" 
                               id="admin_telegram_id" name="admin_telegram_id" value="{{ old('admin_telegram_id') }}" 
                               placeholder="123456789">
                        <div class="form-text">Telegram ID администратора для получения уведомлений о покупках</div>
                        @error('admin_telegram_id')
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

                        <div class="mb-3">
                            <label for="admin_telegram_id_{{ $bot->id }}" class="form-label">ID администратора</label>
                            <input type="text" class="form-control" id="admin_telegram_id_{{ $bot->id }}" 
                                   name="admin_telegram_id" value="{{ $bot->admin_telegram_id }}" 
                                   placeholder="123456789">
                            <div class="form-text">Telegram ID администратора для получения уведомлений о покупках</div>
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


@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '{{ config("app.url") }}';

    // Функция для обновления URL Mini App
    function updateMiniAppUrl(shortNameInput, urlInput, previewElement = null) {
        try {
            if (!shortNameInput || !urlInput) {
                console.warn('updateMiniAppUrl: недостающие элементы DOM');
                return;
            }
            
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
                // Пытаемся получить информацию о боте через AJAX
                fetch(`https://api.telegram.org/bot${token}/getMe`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok && data.result.username) {
                            usernameInput.value = data.result.username;
                            const botNameInput = document.getElementById('bot_name');
                            if (botNameInput) {
                                botNameInput.value = data.result.first_name || '';
                            }
                        }
                    })
                    .catch(error => {
                        // Игнорируем ошибки CORS в браузере
                        console.log('CORS error ignored:', error);
                    });
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

    // Автоматическое формирование URL для редактирования ботов
    @foreach(auth()->user()->telegramBots ?? [] as $bot)
        const editShortNameInput{{ $bot->id }} = document.getElementById('mini_app_short_name_{{ $bot->id }}');
        const editUrlInput{{ $bot->id }} = document.getElementById('mini_app_url_{{ $bot->id }}');
        if (editShortNameInput{{ $bot->id }} && editUrlInput{{ $bot->id }}) {
            editShortNameInput{{ $bot->id }}.addEventListener('input', function() {
                updateMiniAppUrl(this, editUrlInput{{ $bot->id }});
            });
        }

        // Для модального окна настройки Mini App
        const setupShortNameInput{{ $bot->id }} = document.getElementById('mini_app_short_name_setup_{{ $bot->id }}');
        const setupUrlInput{{ $bot->id }} = document.getElementById('mini_app_url_setup_{{ $bot->id }}');
        if (setupShortNameInput{{ $bot->id }} && setupUrlInput{{ $bot->id }}) {
            setupShortNameInput{{ $bot->id }}.addEventListener('input', function() {
                const previewElement = this.closest('.modal-body').querySelector('.mini-app-preview');
                updateMiniAppUrl(this, setupUrlInput{{ $bot->id }}, previewElement);
            });
        }
    @endforeach


    
}); // Закрытие DOMContentLoaded

</script>
@endpush
@endsection
