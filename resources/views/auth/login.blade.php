@extends('layouts.auth')

@section('title', 'Вход в систему')
@section('subtitle', 'Добро пожаловать обратно! Войдите в свой аккаунт')

@section('content')
    @if (session('status'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            Пожалуйста, исправьте ошибки в форме
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-form-group">
            <label for="email" class="auth-form-label">
                <i class="fas fa-envelope admin-me-2"></i>
                Email адрес
            </label>
            <input 
                id="email" 
                type="email" 
                class="auth-form-control @error('email') is-invalid @enderror" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autocomplete="email" 
                autofocus
                placeholder="Введите ваш email"
            >
            @error('email')
                <div class="auth-invalid-feedback">
                    <i class="fas fa-exclamation-circle admin-me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password" class="auth-form-label">
                <i class="fas fa-lock admin-me-2"></i>
                Пароль
            </label>
            <div class="auth-input-group">
                <input 
                    id="password" 
                    type="password" 
                    class="auth-form-control @error('password') is-invalid @enderror" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="Введите ваш пароль"
                >
                <button 
                    type="button" 
                    class="auth-password-toggle" 
                    onclick="togglePassword('password')"
                    title="Показать/скрыть пароль"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="auth-invalid-feedback">
                    <i class="fas fa-exclamation-circle admin-me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="auth-form-group">
            <div class="auth-form-check">
                <input 
                    class="auth-form-check-input" 
                    type="checkbox" 
                    name="remember" 
                    id="remember" 
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label class="auth-form-check-label" for="remember">
                    Запомнить меня
                </label>
            </div>
        </div>

        <div class="auth-form-group">
            <button type="submit" class="auth-btn auth-btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Войти в систему
            </button>
        </div>

        @if (Route::has('password.request'))
            <div class="auth-divider">
                <span>или</span>
            </div>
            
            <div class="auth-form-group admin-mb-0">
                <a class="auth-btn auth-btn-link" href="{{ route('password.request') }}">
                    <i class="fas fa-question-circle"></i>
                    Забыли пароль?
                </a>
            </div>
        @endif
    </form>

    <div class="auth-links">
        <span>Нет аккаунта? </span>
        <a href="{{ route('register') }}" class="auth-link">
            <i class="fas fa-user-plus admin-me-1"></i>
            Зарегистрироваться
        </a>
    </div>
@endsection
