@extends('layouts.auth')

@section('title', 'Подтверждение Email')
@section('subtitle', 'Проверьте вашу электронную почту')

@section('content')
    @if (session('resent'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            Новая ссылка для подтверждения была отправлена на ваш email адрес.
        </div>
    @endif

    <div style="text-align: center; margin-bottom: var(--space-xxl);">
        <div style="width: 60px; height: 60px; background-color: var(--color-success); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-lg);">
            <i class="fas fa-envelope" style="font-size: 24px; color: var(--color-white);"></i>
        </div>
        
        <p style="color: var(--color-text-light); font-size: var(--font-size-sm); line-height: 1.6; margin-bottom: var(--space-lg);">
            Прежде чем продолжить, пожалуйста, проверьте вашу электронную почту и перейдите по ссылке подтверждения, которую мы вам отправили.
        </p>
    </div>

    <div class="auth-form-group">
        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="auth-btn auth-btn-primary">
                <i class="fas fa-paper-plane"></i>
                Отправить ссылку повторно
            </button>
        </form>
    </div>

    <div class="auth-links">
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="auth-link" style="background: none; border: none; padding: 0; color: var(--color-text-light); text-decoration: none; font-size: var(--font-size-sm); cursor: pointer;">
                <i class="fas fa-sign-out-alt admin-me-1"></i>
                Выйти из аккаунта
            </button>
        </form>
    </div>
@endsection
