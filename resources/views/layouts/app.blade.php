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
            <a class="admin-navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>

            <ul class="admin-navbar-nav">
                @auth
                    <li class="admin-nav-item">
                        <a class="admin-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home"></i> Главная
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link {{ request()->routeIs('telegram-bots.*') ? 'active' : '' }}" href="{{ route('telegram-bots.index') }}">
                            <i class="fab fa-telegram"></i> Боты
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <i class="fas fa-shopping-cart"></i> Заказы
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a class="admin-nav-link {{ request()->routeIs('statistics.*') ? 'active' : '' }}" href="{{ route('statistics.index') }}">
                            <i class="fas fa-chart-line"></i> Статистика
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
                            {{ Auth::user()->name }}
                            <i class="fas fa-chevron-down"></i>
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
        </nav>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    
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
