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
            <div class="admin-navbar-container">
                <a class="admin-navbar-brand" href="<?php echo e(url('/')); ?>">
                    <?php echo e(config('app.name', 'Laravel')); ?>

                </a>

                <button class="admin-navbar-toggle" onclick="toggleMobileNav()" id="navbarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="admin-navbar-collapse" id="navbarCollapse">
                    <ul class="admin-navbar-nav">
                        <?php if(auth()->guard()->check()): ?>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                                    <i class="fas fa-home"></i> 
                                    <span>Главная</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link <?php echo e(request()->routeIs('telegram-bots.*') ? 'active' : ''); ?>" href="<?php echo e(route('telegram-bots.index')); ?>">
                                    <i class="fab fa-telegram"></i> 
                                    <span>Боты</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>" href="<?php echo e(route('orders.index')); ?>">
                                    <i class="fas fa-shopping-cart"></i> 
                                    <span>Заказы</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link <?php echo e(request()->routeIs('statistics.*') ? 'active' : ''); ?>" href="<?php echo e(route('statistics.index')); ?>">
                                    <i class="fas fa-chart-line"></i> 
                                    <span>Статистика</span>
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
                                    <i class="fas fa-user admin-me-2 admin-d-block-xs"></i>
                                    <span class="admin-d-none-xs"><?php echo e(Auth::user()->name); ?></span>
                                    <i class="fas fa-chevron-down admin-d-none-xs"></i>
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
                </div>
            </div>
        </nav>

        <main class="admin-main">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <script>
        // Mobile navigation toggle
        function toggleMobileNav() {
            const collapse = document.getElementById('navbarCollapse');
            const toggle = document.getElementById('navbarToggle');
            collapse.classList.toggle('show');
            toggle.classList.toggle('active');
        }
        
        // Dropdown functionality
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown and mobile nav when clicking outside
        document.addEventListener('click', function(e) {
            // Close dropdowns
            const dropdowns = document.querySelectorAll('.admin-dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Close mobile nav
            const navCollapse = document.getElementById('navbarCollapse');
            const navToggle = document.getElementById('navbarToggle');
            if (navCollapse && navToggle && !navToggle.contains(e.target) && !navCollapse.contains(e.target)) {
                navCollapse.classList.remove('show');
                navToggle.classList.remove('active');
            }
        });
        
        // Close mobile nav when clicking on nav links
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.admin-nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const navCollapse = document.getElementById('navbarCollapse');
                    const navToggle = document.getElementById('navbarToggle');
                    if (navCollapse && navToggle) {
                        navCollapse.classList.remove('show');
                        navToggle.classList.remove('active');
                    }
                });
            });
            
            // Alert auto-close functionality
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