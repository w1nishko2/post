<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Авторизация') - {{ config('app.name', 'Weebs Market') }}</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin CSS -->
    @vite(['resources/css/app.css'])
    
    <!-- Auth specific styles -->
    <style>
        body {
            background-color: var(--color-light-gray);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-lg);
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
        }

        .auth-card {
            background-color: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: var(--card-border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .auth-header {
            padding: var(--space-xxl) var(--space-lg) var(--space-lg);
            text-align: center;
            border-bottom: 1px solid var(--color-border);
        }

        .auth-logo {
            width: 60px;
            height: 60px;
            background-color: var(--color-accent);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-lg);
        }

        .auth-logo i {
            font-size: 24px;
            color: var(--color-white);
        }

        .auth-title {
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: var(--space-sm);
        }

        .auth-subtitle {
            color: var(--color-text-light);
            font-size: var(--font-size-sm);
        }

        .auth-body {
            padding: var(--space-lg);
        }

        /* Переопределяем стили форм для auth страниц */
        .auth-form-group {
            margin-bottom: var(--space-lg);
        }

        .auth-form-label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: 500;
            color: var(--color-text);
            font-size: var(--font-size-sm);
        }

        .auth-form-control {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-text);
            background-color: var(--color-white);
            min-height: var(--form-height);
            transition: all 0.2s;
        }

        .auth-form-control:focus {
            outline: none;
            border-color: var(--color-accent);
            background-color: var(--color-light-gray);
        }

        .auth-form-control::placeholder {
            color: var(--color-text-muted);
            opacity: 0.7;
        }

        .auth-form-control.is-invalid {
            border-color: var(--color-danger);
        }

        .auth-invalid-feedback {
            display: block;
            font-size: var(--font-size-xs);
            color: var(--color-danger);
            margin-top: var(--space-xs);
        }

        .auth-form-check {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-sm);
        }

        .auth-form-check-input {
            width: 16px;
            height: 16px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            background-color: var(--color-white);
        }

        .auth-form-check-input:checked {
            background-color: var(--color-accent);
            border-color: var(--color-accent);
        }

        .auth-form-check-label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
            font-size: var(--font-size-sm);
            color: var(--color-text);
        }

        .auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-sm) var(--space-lg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            gap: var(--space-sm);
            background-color: var(--color-white);
            color: var(--color-text);
            min-height: var(--form-height);
            width: 100%;
        }

        .auth-btn:hover {
            background-color: var(--color-light-gray);
            border-color: var(--color-accent);
            color: var(--color-text);
        }

        .auth-btn-primary {
            background-color: var(--color-accent);
            color: var(--color-white);
            border-color: var(--color-accent);
        }

        .auth-btn-primary:hover {
            background-color: var(--color-accent-dark);
            border-color: var(--color-accent-dark);
            color: var(--color-white);
        }

        .auth-btn-link {
            background: transparent;
            border: none;
            color: var(--color-accent);
            padding: var(--space-sm) 0;
            font-weight: 500;
            text-align: center;
            width: 100%;
        }

        .auth-btn-link:hover {
            color: var(--color-accent-dark);
            text-decoration: underline;
            background: transparent;
        }

        .auth-input-group {
            position: relative;
        }

        .auth-password-toggle {
            position: absolute;
            right: var(--space-sm);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            padding: var(--space-xs);
            border-radius: var(--radius-sm);
        }

        .auth-password-toggle:hover {
            color: var(--color-text);
            background-color: var(--color-light-gray);
        }

        .auth-divider {
            margin: var(--space-lg) 0;
            text-align: center;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--color-border);
        }

        .auth-divider span {
            background: var(--color-white);
            padding: 0 var(--space-md);
            color: var(--color-text-muted);
            font-size: var(--font-size-xs);
        }

        .auth-links {
            margin-top: var(--space-lg);
            text-align: center;
            padding-top: var(--space-lg);
            border-top: 1px solid var(--color-border);
        }

        .auth-link {
            color: var(--color-text-light);
            text-decoration: none;
            font-size: var(--font-size-sm);
        }

        .auth-link:hover {
            color: var(--color-accent);
            text-decoration: underline;
        }

        .auth-alert {
            padding: var(--space-sm) var(--space-md);
            margin-bottom: var(--space-lg);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .auth-alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .auth-alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-color: rgba(239, 68, 68, 0.3);
        }

        @media (max-width: 640px) {
            body {
                padding: var(--space-md);
            }
            
            .auth-container {
                max-width: 100%;
            }
            
            .auth-header {
                padding: var(--space-lg);
            }
            
            .auth-body {
                padding: var(--space-lg);
            }
        }

        /* Анимация появления */
        .auth-card {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1 class="auth-title">@yield('title')</h1>
                <p class="auth-subtitle">@yield('subtitle')</p>
            </div>
            
            <div class="auth-body">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            if (!toggle || !toggle.classList.contains('auth-password-toggle')) return;
            
            const icon = toggle.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.auth-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>