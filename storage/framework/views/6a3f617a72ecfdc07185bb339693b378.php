<?php $__env->startSection('content'); ?>
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

            <!-- Заголовок страницы -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body admin-text-center">
            <h1 class="admin-mb-3">
                <i class="fas fa-user-circle admin-me-2"></i>
                Мой профиль
            </h1>
            <p class="admin-text-muted admin-mb-0">Управление данными вашего аккаунта</p>
        </div>
    </div>

    <div class="admin-row">
        <!-- Основная информация -->
        <div class="admin-col admin-col-4">
            <div class="admin-card admin-mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-user admin-me-2"></i>
                        Основная информация
                    </h5>
                </div>
                <div class="admin-card-body">
                    <div class="admin-text-center admin-mb-4">
                        <div style="width: 80px; height: 80px; background-color: var(--color-gray); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    
                    <div class="admin-mb-3">
                        <div class="admin-form-label">Имя:</div>
                        <div class="admin-fw-bold"><?php echo e(Auth::user()->name); ?></div>
                    </div>
                    
                    <div class="admin-mb-3">
                        <div class="admin-form-label">Email:</div>
                        <div class="admin-fw-bold"><?php echo e(Auth::user()->email); ?></div>
                    </div>
                    
                    <div class="admin-mb-3">
                        <div class="admin-form-label">Дата регистрации:</div>
                        <div class="admin-fw-bold"><?php echo e(Auth::user()->created_at->format('d.m.Y')); ?></div>
                    </div>
                    
                    <div class="admin-mb-0">
                        <div class="admin-form-label">Последняя активность:</div>
                        <div class="admin-fw-bold"><?php echo e(Auth::user()->updated_at->format('d.m.Y H:i')); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Формы редактирования -->
        <div class="admin-col admin-col-8">
            <!-- Редактирование имени -->
            <div class="admin-card admin-mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-edit admin-me-2"></i>
                        Изменить имя
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="<?php echo e(route('profile.update.name')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="admin-form-group">
                            <label for="name" class="admin-form-label required">Имя</label>
                            <input type="text" class="admin-form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="name" name="name" value="<?php echo e(old('name', Auth::user()->name)); ?>" required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-d-flex admin-justify-content-end">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save admin-me-2"></i>
                                Сохранить имя
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Редактирование email -->
            <div class="admin-card admin-mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-envelope admin-me-2"></i>
                        Изменить email
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="<?php echo e(route('profile.update.email')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="admin-form-group">
                            <label for="email" class="admin-form-label required">Email адрес</label>
                            <input type="email" class="admin-form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="email" name="email" value="<?php echo e(old('email', Auth::user()->email)); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-d-flex admin-justify-content-end">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save admin-me-2"></i>
                                Сохранить email
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Изменение пароля -->
            <div class="admin-card admin-mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-key admin-me-2"></i>
                        Изменить пароль
                    </h5>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="<?php echo e(route('profile.update.password')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="admin-form-group">
                            <label for="current_password" class="admin-form-label required">Текущий пароль</label>
                            <div class="admin-input-group">
                                <input type="password" class="admin-form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="current_password" name="current_password" required>
                                <div class="admin-input-group-append">
                                    <button type="button" class="admin-btn" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-form-group">
                            <label for="new_password" class="admin-form-label required">Новый пароль</label>
                            <div class="admin-input-group">
                                <input type="password" class="admin-form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="new_password" name="password" required>
                                <div class="admin-input-group-append">
                                    <button type="button" class="admin-btn" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye" id="new_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-form-group">
                            <label for="password_confirmation" class="admin-form-label required">Подтверждение пароля</label>
                            <div class="admin-input-group">
                                <input type="password" class="admin-form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <div class="admin-input-group-append">
                                    <button type="button" class="admin-btn" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="admin-d-flex admin-justify-content-end">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-key admin-me-2"></i>
                                Изменить пароль
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопка возврата -->
    <div class="admin-text-center">
        <a href="<?php echo e(route('home')); ?>" class="admin-btn">
            <i class="fas fa-arrow-left admin-me-2"></i>
            Вернуться на главную
        </a>
    </div>
</div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Добавляем анимацию при отправке форм
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin admin-me-2"></i>Сохранение...';
                submitBtn.disabled = true;
                
                // Восстанавливаем кнопку через 5 секунд на случай ошибки
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
});
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/profile/show.blade.php ENDPATH**/ ?>