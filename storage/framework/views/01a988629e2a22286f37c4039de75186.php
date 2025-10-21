<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
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
<?php endif; ?>

<div class="admin-container">
    <?php if(session('success')): ?>
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle admin-me-2"></i>
            <?php echo e(session('success')); ?>

            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            <?php echo e(session('error')); ?>

            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill active" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <!-- Основной контент ботов -->
    <?php if(request()->is('home') || request()->routeIs('home')): ?>
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
            <?php if(auth()->user()->telegramBots && auth()->user()->telegramBots->count() > 0): ?>
                <div class="admin-bot-grid">
                    <?php $__currentLoopData = auth()->user()->telegramBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="admin-bot-card">
                            <div class="admin-bot-header">
                                <div class="admin-bot-avatar <?php echo e($bot->is_active ? '' : 'inactive'); ?>">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="admin-bot-info">
                                    <h6 class="admin-mb-1"><?php echo e($bot->bot_name); ?></h6>
                                    <div class="admin-bot-username admin-text-muted">{{ $bot->bot_username }}</div>
                                </div>
                            </div>

                            <div class="admin-bot-stats admin-mb-3">
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Статус</div>
                                    <div class="admin-bot-stat-value">
                                        <?php if($bot->is_active): ?>
                                            <span class="admin-badge admin-badge-success">Активен</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-warning">Неактивен</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Товаров</div>
                                    <div class="admin-bot-stat-value"><?php echo e($bot->products()->count()); ?></div>
                                </div>
                                <div class="admin-bot-stat">
                                    <div class="admin-bot-stat-label">Заказов</div>
                                    <div class="admin-bot-stat-value"><?php echo e($bot->orders()->count() ?? 0); ?></div>
                                </div>
                            </div>

                            <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                                <a href="<?php echo e(route('bot.products.index', $bot)); ?>" class="admin-btn admin-btn-sm admin-flex-1">
                                    <i class="fas fa-boxes admin-me-1"></i>
                                    Товары
                                </a>
                                <button class="admin-btn admin-btn-sm" onclick="showModal('editBotModal<?php echo e($bot->id); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="admin-btn admin-btn-sm" onclick="showModal('setupMiniAppModal<?php echo e($bot->id); ?>')">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <form method="POST" action="<?php echo e(route('telegram-bots.destroy', $bot)); ?>" class="admin-d-inline" onsubmit="return confirm('Вы уверены?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="admin-empty-state">
                    <i class="fas fa-robot"></i>
                    <h3>У вас пока нет ботов</h3>
                    <p class="admin-mb-4">Создайте первого бота, чтобы начать продавать товары через Telegram</p>
                    <button class="admin-btn admin-btn-primary" onclick="showModal('addBotModal')">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создать первого бота
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно добавления бота -->
<div class="admin-modal" id="addBotModal">
    <div class="admin-modal-dialog">
        <div class="admin-modal-content">
            <form method="POST" action="<?php echo e(route('telegram-bots.store')); ?>">
                <?php echo csrf_field(); ?>
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

<?php $__currentLoopData = auth()->user()->telegramBots ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Модальное окно редактирования бота -->
    <div class="admin-modal" id="editBotModal<?php echo e($bot->id); ?>">
        <div class="admin-modal-dialog">
            <div class="admin-modal-content">
                <form method="POST" action="<?php echo e(route('telegram-bots.update', $bot)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="admin-modal-header">
                        <h5 class="admin-modal-title">
                            <i class="fas fa-edit admin-me-2"></i>
                            Редактировать бота
                        </h5>
                        <button type="button" class="admin-modal-close" onclick="hideModal('editBotModal<?php echo e($bot->id); ?>')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <div class="admin-modal-body">
                    <div class="admin-form-group">
                        <label for="bot_token_<?php echo e($bot->id); ?>" class="admin-form-label required">Токен бота</label>
                        <input type="text" class="admin-form-control" id="bot_token_<?php echo e($bot->id); ?>" name="bot_token" 
                               value="<?php echo e($bot->bot_token); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="bot_username_<?php echo e($bot->id); ?>" class="admin-form-label required">Username бота</label>
                        <div class="admin-input-group">
                            <div class="admin-input-group-text">@</div>
                            <input type="text" class="admin-form-control" id="bot_username_<?php echo e($bot->id); ?>" name="bot_username" 
                                   value="<?php echo e($bot->bot_username); ?>" required>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="bot_name_<?php echo e($bot->id); ?>" class="admin-form-label required">Название бота</label>
                        <input type="text" class="admin-form-control" id="bot_name_<?php echo e($bot->id); ?>" name="bot_name" 
                               value="<?php echo e($bot->bot_name); ?>" required>
                    </div>

                    <div class="admin-form-group admin-mb-0">
                        <div class="admin-form-check">
                            <input type="checkbox" class="admin-form-check-input" id="is_active_<?php echo e($bot->id); ?>" name="is_active" 
                                   value="1" <?php echo e($bot->is_active ? 'checked' : ''); ?>>
                            <label for="is_active_<?php echo e($bot->id); ?>" class="admin-form-check-label">Бот активен</label>
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn" onclick="hideModal('editBotModal<?php echo e($bot->id); ?>')">Отмена</button>
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
    <div class="admin-modal" id="setupMiniAppModal<?php echo e($bot->id); ?>">
        <div class="admin-modal-dialog">
            <div class="admin-modal-content">
                <form method="POST" action="<?php echo e(route('telegram-bots.setup-mini-app', $bot)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="admin-modal-header">
                        <h5 class="admin-modal-title">
                            <i class="fas fa-cog admin-me-2"></i>
                            Настройка Mini App
                        </h5>
                        <button type="button" class="admin-modal-close" onclick="hideModal('setupMiniAppModal<?php echo e($bot->id); ?>')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <div class="admin-modal-body">
                    <div class="admin-form-group">
                        <label for="mini_app_short_name_<?php echo e($bot->id); ?>" class="admin-form-label">Короткое имя Mini App</label>
                        <input type="text" class="admin-form-control" id="mini_app_short_name_setup_<?php echo e($bot->id); ?>" 
                               name="mini_app_short_name" value="<?php echo e($bot->mini_app_short_name); ?>"
                               placeholder="myshop">
                        <div class="admin-form-text">Используется в URL Mini App</div>
                    </div>

                    <div class="admin-form-group admin-mb-0">
                        <label for="mini_app_url_<?php echo e($bot->id); ?>" class="admin-form-label">URL Mini App</label>
                        <input type="url" class="admin-form-control" id="mini_app_url_setup_<?php echo e($bot->id); ?>" 
                               name="mini_app_url" value="<?php echo e($bot->mini_app_url); ?>" readonly
                               placeholder="https://example.com/myshop">
                        <div class="admin-form-text">Автоматически генерируется на основе короткого имени</div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn" onclick="hideModal('setupMiniAppModal<?php echo e($bot->id); ?>')">Отмена</button>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo e(config("app.url")); ?>';

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
    function updateMiniAppUrl(shortNameInput, urlInput) {
        try {
            if (!shortNameInput || !urlInput) return;
            
            const shortName = shortNameInput.value.trim();
            if (shortName) {
                const fullUrl = `${baseUrl}/${shortName}`;
                urlInput.value = fullUrl;
            } else {
                urlInput.value = '';
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
    <?php $__currentLoopData = auth()->user()->telegramBots ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        const setupShortNameInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_short_name_setup_<?php echo e($bot->id); ?>');
        const setupUrlInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_url_setup_<?php echo e($bot->id); ?>');
        if (setupShortNameInput<?php echo e($bot->id); ?> && setupUrlInput<?php echo e($bot->id); ?>) {
            setupShortNameInput<?php echo e($bot->id); ?>.addEventListener('input', function() {
                updateMiniAppUrl(this, setupUrlInput<?php echo e($bot->id); ?>);
            });
        }
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/home.blade.php ENDPATH**/ ?>