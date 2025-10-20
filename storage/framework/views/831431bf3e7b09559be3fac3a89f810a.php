<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
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
<?php endif; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Навигационная панель -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill" style="padding: 0.5rem;">
                        <a class="nav-link <?php echo e(request()->is('home') || request()->routeIs('home') ? 'active' : ''); ?>" 
                           href="<?php echo e(route('home')); ?>" 
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-robot me-2"></i>Мои боты
                        </a>
                        <a class="nav-link <?php echo e(request()->is('products*') || request()->routeIs('products.*') ? 'active' : ''); ?>" 
                           href="<?php echo e(route('products.select-bot')); ?>"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-boxes me-2"></i>Мои магазины
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Контент для ботов (показывается только на главной странице) -->
            <?php if(request()->is('home') || request()->routeIs('home')): ?>
            <div class="card shadow-lg">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-bottom: 2px solid #e2e8f0;">
                    <h5 class="mb-0" style="color: #1e293b; font-weight: 700;">
                        <i class="fas fa-robot text-primary me-2"></i>Управление Telegram ботами
                    </h5>
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBotModal" style="border-radius: 10px; padding: 0.6rem 1.5rem; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i> Добавить бота
                    </button>
                </div>

                <div class="card-body">
                    <?php if(auth()->user()->telegramBots->count() > 0): ?>
                        <div class="row">
                            <?php $__currentLoopData = auth()->user()->telegramBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?php echo e($bot->bot_name); ?></h6>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#editBotModal<?php echo e($bot->id); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="<?php echo e(route('telegram-bots.toggle', $bot)); ?>" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <button type="submit" class="btn btn-outline-<?php echo e($bot->is_active ? 'warning' : 'success'); ?> btn-sm">
                                                        <i class="fas fa-<?php echo e($bot->is_active ? 'pause' : 'play'); ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?php echo e(route('telegram-bots.destroy', $bot)); ?>" 
                                                      class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого бота?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Username:</strong> {{ $bot->bot_username }}
                                            </div>
                                            <div class="mb-2">
                                                <strong>Токен:</strong> 
                                                <code><?php echo e($bot->masked_token); ?></code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Статус:</strong>
                                                <span class="badge bg-<?php echo e($bot->is_active ? 'success' : 'secondary'); ?>">
                                                    <?php echo e($bot->is_active ? 'Активен' : 'Неактивен'); ?>

                                                </span>
                                            </div>
                                            <?php if($bot->hasMiniApp()): ?>
                                                <div class="mb-2">
                                                    <strong>Mini App:</strong>
                                                    <a href="<?php echo e($bot->getMiniAppUrl()); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-external-link"></i> <?php echo e($bot->mini_app_short_name); ?>

                                                    </a>
                                                    <br>
                                                    <small class="text-muted"><?php echo e($bot->getDisplayMiniAppUrl()); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            data-bs-toggle="modal" data-bs-target="#setupMiniAppModal<?php echo e($bot->id); ?>">
                                                        <i class="fas fa-cog"></i> Настроить Mini App
                                                    </button>
                                                </div>
                                            <?php endif; ?>

                                            <small class="text-muted">
                                                Обновлен: <?php echo e($bot->updated_at->format('d.m.Y H:i')); ?>

                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                            <h5>У вас пока нет Telegram ботов</h5>
                            <p class="text-muted">Добавьте своего первого бота, чтобы начать создавать Mini App</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBotModal">
                                <i class="fas fa-plus"></i> Добавить первого бота
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно добавления бота -->
<div class="modal fade" id="addBotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('telegram-bots.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Добавить Telegram бота</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bot_name" class="form-label">Название бота</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['bot_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="bot_name" name="bot_name" value="<?php echo e(old('bot_name')); ?>" required>
                        <?php $__errorArgs = ['bot_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="bot_username" class="form-label">Username бота</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control <?php $__errorArgs = ['bot_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="bot_username" name="bot_username" value="<?php echo e(old('bot_username')); ?>" required>
                        </div>
                        <?php $__errorArgs = ['bot_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="bot_token" class="form-label">Токен бота</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['bot_token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="bot_token" name="bot_token" value="<?php echo e(old('bot_token')); ?>" 
                               placeholder="1234567890:ABCdefGHijklMNOpqrsTUvwxyz" required>
                        <div class="form-text">Получите токен от @BotFather в Telegram</div>
                        <?php $__errorArgs = ['bot_token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_telegram_id" class="form-label">ID администратора (необязательно)</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['admin_telegram_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="admin_telegram_id" name="admin_telegram_id" value="<?php echo e(old('admin_telegram_id')); ?>" 
                                       placeholder="123456789">
                                <div class="form-text">Telegram ID администратора для уведомлений</div>
                                <?php $__errorArgs = ['admin_telegram_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_telegram_username" class="form-label">Username администратора (необязательно)</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['admin_telegram_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="admin_telegram_username" name="admin_telegram_username" value="<?php echo e(old('admin_telegram_username')); ?>" 
                                       placeholder="admin_username">
                                <div class="form-text">Username для создания ссылки связи с клиентами</div>
                                <?php $__errorArgs = ['admin_telegram_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="api_id" class="form-label">API ID (необязательно)</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['api_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="api_id" name="api_id" value="<?php echo e(old('api_id')); ?>">
                                <?php $__errorArgs = ['api_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="api_hash" class="form-label">API Hash (необязательно)</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['api_hash'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="api_hash" name="api_hash" value="<?php echo e(old('api_hash')); ?>">
                                <?php $__errorArgs = ['api_hash'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6>Настройки Mini App (необязательно)</h6>

                    <div class="mb-3">
                        <label for="mini_app_short_name" class="form-label">Имя Mini App</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo e(config('app.url')); ?>/</span>
                            <input type="text" class="form-control <?php $__errorArgs = ['mini_app_short_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="mini_app_short_name" name="mini_app_short_name" value="<?php echo e(old('mini_app_short_name')); ?>" 
                                   placeholder="myapp" maxlength="64">
                        </div>
                        <div class="form-text">Введите только имя приложения. URL будет: <?php echo e(config('app.url')); ?>/ваше_имя</div>
                        <?php $__errorArgs = ['mini_app_short_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <input type="hidden" id="mini_app_url" name="mini_app_url" value="<?php echo e(old('mini_app_url')); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить бота</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__currentLoopData = auth()->user()->telegramBots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Модальное окно редактирования бота -->
    <div class="modal fade" id="editBotModal<?php echo e($bot->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('telegram-bots.update', $bot)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Редактировать бота: <?php echo e($bot->bot_name); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="bot_name_<?php echo e($bot->id); ?>" class="form-label">Название бота</label>
                            <input type="text" class="form-control" id="bot_name_<?php echo e($bot->id); ?>" 
                                   name="bot_name" value="<?php echo e($bot->bot_name); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="admin_telegram_id_<?php echo e($bot->id); ?>" class="form-label">ID администратора</label>
                                    <input type="text" class="form-control" id="admin_telegram_id_<?php echo e($bot->id); ?>" 
                                           name="admin_telegram_id" value="<?php echo e($bot->admin_telegram_id); ?>" 
                                           placeholder="123456789">
                                    <div class="form-text">Telegram ID для уведомлений</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="admin_telegram_username_<?php echo e($bot->id); ?>" class="form-label">Username администратора</label>
                                    <input type="text" class="form-control" id="admin_telegram_username_<?php echo e($bot->id); ?>" 
                                           name="admin_telegram_username" value="<?php echo e($bot->admin_telegram_username); ?>" 
                                           placeholder="admin_username">
                                    <div class="form-text">Username для связи с клиентами</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="api_id_<?php echo e($bot->id); ?>" class="form-label">API ID</label>
                                    <input type="text" class="form-control" id="api_id_<?php echo e($bot->id); ?>" 
                                           name="api_id" value="<?php echo e($bot->api_id); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="api_hash_<?php echo e($bot->id); ?>" class="form-label">API Hash</label>
                                    <input type="text" class="form-control" id="api_hash_<?php echo e($bot->id); ?>" 
                                           name="api_hash" value="<?php echo e($bot->api_hash); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mini_app_short_name_<?php echo e($bot->id); ?>" class="form-label">Имя Mini App</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo e(config('app.url')); ?>/</span>
                                <input type="text" class="form-control" id="mini_app_short_name_<?php echo e($bot->id); ?>" 
                                       name="mini_app_short_name" value="<?php echo e($bot->mini_app_short_name); ?>" maxlength="64">
                            </div>
                        </div>

                        <input type="hidden" id="mini_app_url_<?php echo e($bot->id); ?>" name="mini_app_url" value="<?php echo e($bot->mini_app_url); ?>">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active_<?php echo e($bot->id); ?>" 
                                   name="is_active" value="1" <?php echo e($bot->is_active ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active_<?php echo e($bot->id); ?>">
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
    <div class="modal fade" id="setupMiniAppModal<?php echo e($bot->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('telegram-bots.setup-mini-app', $bot)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Настроить Mini App для <?php echo e($bot->bot_name); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <div class="mb-3">
                        <label for="mini_app_short_name_setup_<?php echo e($bot->id); ?>" class="form-label">Имя Mini App</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo e(config('app.url')); ?>/</span>
                            <input type="text" class="form-control" id="mini_app_short_name_setup_<?php echo e($bot->id); ?>" 
                                   name="mini_app_short_name" value="<?php echo e($bot->mini_app_short_name); ?>" 
                                   placeholder="myapp" maxlength="64" required>
                        </div>
                        <div class="form-text">URL будет: <?php echo e(config('app.url')); ?>/<span class="mini-app-preview"><?php echo e($bot->mini_app_short_name ?: 'ваше_имя'); ?></span></div>
                    </div>

                    <input type="hidden" id="mini_app_url_setup_<?php echo e($bot->id); ?>" name="mini_app_url" value="<?php echo e($bot->mini_app_url); ?>">                        <div class="mb-3">
                            <label for="menu_button_text_<?php echo e($bot->id); ?>" class="form-label">Текст кнопки меню</label>
                            <input type="text" class="form-control" id="menu_button_text_<?php echo e($bot->id); ?>" 
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


<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo e(config("app.url")); ?>';

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
    <?php $__currentLoopData = auth()->user()->telegramBots ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        const editShortNameInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_short_name_<?php echo e($bot->id); ?>');
        const editUrlInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_url_<?php echo e($bot->id); ?>');
        if (editShortNameInput<?php echo e($bot->id); ?> && editUrlInput<?php echo e($bot->id); ?>) {
            editShortNameInput<?php echo e($bot->id); ?>.addEventListener('input', function() {
                updateMiniAppUrl(this, editUrlInput<?php echo e($bot->id); ?>);
            });
        }

        // Для модального окна настройки Mini App
        const setupShortNameInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_short_name_setup_<?php echo e($bot->id); ?>');
        const setupUrlInput<?php echo e($bot->id); ?> = document.getElementById('mini_app_url_setup_<?php echo e($bot->id); ?>');
        if (setupShortNameInput<?php echo e($bot->id); ?> && setupUrlInput<?php echo e($bot->id); ?>) {
            setupShortNameInput<?php echo e($bot->id); ?>.addEventListener('input', function() {
                const previewElement = this.closest('.modal-body').querySelector('.mini-app-preview');
                updateMiniAppUrl(this, setupUrlInput<?php echo e($bot->id); ?>, previewElement);
            });
        }
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


    
}); // Закрытие DOMContentLoaded

</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\home.blade.php ENDPATH**/ ?>