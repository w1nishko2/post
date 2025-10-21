<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <?php if(auth()->guard()->check()): ?>
    <!-- API Token для авторизованных пользователей -->
    <meta name="api-token" content="<?php echo e(auth()->user()->createToken('web-token')->plainTextToken); ?>">
    <?php endif; ?>

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <div id="app">
        <nav class="admin-navbar">
            <a class="admin-navbar-brand" href="<?php echo e(url('/')); ?>">
                <?php echo e(config('app.name', 'Laravel')); ?>

            </a>

            <ul class="admin-navbar-nav">
                <?php if(auth()->guard()->check()): ?>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                            <i class="fas fa-home"></i> Главная
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link <?php echo e(request()->routeIs('telegram-bots.*') ? 'active' : ''); ?>" href="<?php echo e(route('telegram-bots.index')); ?>">
                            <i class="fab fa-telegram"></i> Боты
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>" href="<?php echo e(route('orders.index')); ?>">
                            <i class="fas fa-shopping-cart"></i> Заказы
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link <?php echo e(request()->routeIs('statistics.*') ? 'active' : ''); ?>" href="<?php echo e(route('statistics.index')); ?>">
                            <i class="fas fa-chart-line"></i> Статистика
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="admin-navbar-user">
                <?php if(auth()->guard()->guest()): ?>
                    <?php if(Route::has('login')): ?>
                        <a class="admin-btn admin-btn-sm" href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
                    <?php endif; ?>
                    <?php if(Route::has('register')): ?>
                        <a class="admin-btn admin-btn-sm admin-ms-2" href="<?php echo e(route('register')); ?>"><?php echo e(__('Register')); ?></a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="admin-dropdown" id="userDropdown">
                        <button class="admin-dropdown-toggle" onclick="toggleDropdown('userDropdown')">
                            <?php echo e(Auth::user()->name); ?>

                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="admin-dropdown-menu">
                            <a class="admin-dropdown-item" href="<?php echo e(route('profile.show')); ?>">
                                <i class="fas fa-user admin-me-2"></i>
                                Мой профиль
                            </a>
                            <div class="admin-dropdown-divider"></div>
                            <a class="admin-dropdown-item" href="<?php echo e(route('logout')); ?>"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt admin-me-2"></i>
                                <?php echo e(__('Logout')); ?>

                            </a>
                        </div>
                    </div>

                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="admin-d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                <?php endif; ?>
            </div>
        </nav>

        <main class="admin-main">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <script>
        // Dropdown functionality
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.admin-dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        });
        
        // Alert auto-close functionality
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.admin-alert');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('.admin-alert-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        alert.style.display = 'none';
                    });
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH C:\OSPanel\domains\post\resources\views/layouts/app.blade.php ENDPATH**/ ?>