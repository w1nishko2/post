@extends('layouts.auth')

@section('title', 'Новый пароль')
@section('subtitle', 'Создайте новый пароль для вашего аккаунта')

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            Пожалуйста, исправьте ошибки в форме
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

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
                value="{{ $email ?? old('email') }}" 
                required 
                autocomplete="email" 
                autofocus
                placeholder="Ваш email адрес"
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
                Новый пароль
            </label>
            <div class="auth-input-group">
                <input 
                    id="password" 
                    type="password" 
                    class="auth-form-control @error('password') is-invalid @enderror" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    placeholder="Создайте новый пароль"
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
            <label for="password-confirm" class="auth-form-label">
                <i class="fas fa-lock admin-me-2"></i>
                Подтверждение пароля
            </label>
            <div class="auth-input-group">
                <input 
                    id="password-confirm" 
                    type="password" 
                    class="auth-form-control" 
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password"
                    placeholder="Повторите новый пароль"
                >
                <button 
                    type="button" 
                    class="auth-password-toggle" 
                    onclick="togglePassword('password-confirm')"
                    title="Показать/скрыть пароль"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="auth-form-group">
            <button type="submit" class="auth-btn auth-btn-primary">
                <i class="fas fa-key"></i>
                Сбросить пароль
            </button>
        </div>
    </form>

    <div class="auth-links">
        <span>Вспомнили пароль? </span>
        <a href="{{ route('login') }}" class="auth-link">
            <i class="fas fa-sign-in-alt admin-me-1"></i>
            Войти в систему
        </a>
    </div>
@endsection
