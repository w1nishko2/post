<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @auth
    <!-- API Token для авторизованных пользователей -->
    <meta name="api-token" content="{{ auth()->user()->createToken('web-token')->plainTextToken }}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="admin-navbar">
            <div class="admin-navbar-container">
                <a class="admin-navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <button class="admin-navbar-toggle" onclick="toggleMobileNav()" id="navbarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="admin-navbar-collapse" id="navbarCollapse">
                    <ul class="admin-navbar-nav">
                        @auth
                            <li class="admin-nav-item">
                                <a class="admin-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                    <i class="fas fa-home"></i> 
                                    <span>Главная</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link {{ request()->routeIs('telegram-bots.*') ? 'active' : '' }}" href="{{ route('telegram-bots.index') }}">
                                    <i class="fab fa-telegram"></i> 
                                    <span>Боты</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                    <i class="fas fa-shopping-cart"></i> 
                                    <span>Заказы</span>
                                </a>
                            </li>
                            <li class="admin-nav-item">
                                <a class="admin-nav-link {{ request()->routeIs('statistics.*') ? 'active' : '' }}" href="{{ route('statistics.index') }}">
                                    <i class="fas fa-chart-line"></i> 
                                    <span>Статистика</span>
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <div class="admin-navbar-user">
                        @guest
                            @if (Route::has('login'))
                                <a class="admin-btn admin-btn-sm" href="{{ route('login') }}">{{ __('Login') }}</a>
                            @endif
                            @if (Route::has('register'))
                                <a class="admin-btn admin-btn-sm admin-ms-2" href="{{ route('register') }}">{{ __('Register') }}</a>
                            @endif
                        @else
                            <div class="admin-dropdown" id="userDropdown">
                                <button class="admin-dropdown-toggle" onclick="toggleDropdown('userDropdown')">
                                    <i class="fas fa-user admin-me-2 admin-d-block-xs"></i>
                                    <span class="admin-d-none-xs">{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down admin-d-none-xs"></i>
                                </button>
                                <div class="admin-dropdown-menu">
                                    <a class="admin-dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="fas fa-user admin-me-2"></i>
                                        Мой профиль
                                    </a>
                                    <div class="admin-dropdown-divider"></div>
                                    <a class="admin-dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt admin-me-2"></i>
                                        {{ __('Logout') }}
                                    </a>
                                </div>
                            </div>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="admin-d-none">
                                @csrf
                            </form>
                        @endguest
                    </div>
                </div>
            </div>
        </nav>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    
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
