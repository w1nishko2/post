@extends('layouts.auth')

@section('title', 'Сброс пароля')
@section('subtitle', 'Введите email для восстановления пароля')

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

    <form method="POST" action="{{ route('password.email') }}">
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
            <button type="submit" class="auth-btn auth-btn-primary">
                <i class="fas fa-paper-plane"></i>
                Отправить ссылку для сброса
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
