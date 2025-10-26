@extends('layouts.auth')

@section('title', 'Подтверждение пароля')
@section('subtitle', 'Пожалуйста, подтвердите ваш пароль для продолжения')

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            Пожалуйста, исправьте ошибки в форме
        </div>
    @endif

    <div style="text-align: center; margin-bottom: var(--space-lg); color: var(--color-text-light); font-size: var(--font-size-sm);">
        Это защищенная область. Для безопасности подтвердите ваш пароль.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="auth-form-group">
            <label for="password" class="auth-form-label">
                <i class="fas fa-lock admin-me-2"></i>
                Текущий пароль
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
                    autofocus
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
            <button type="submit" class="auth-btn auth-btn-primary">
                <i class="fas fa-check"></i>
                Подтвердить пароль
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
        <a href="{{ route('home') }}" class="auth-link">
            <i class="fas fa-arrow-left admin-me-1"></i>
            Вернуться на главную
        </a>
    </div>
@endsection
