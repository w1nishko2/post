@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Заголовок страницы -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body text-center py-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        Мой профиль
                    </h1>
                    <p class="text-muted mt-2 mb-0">Управление данными вашего аккаунта</p>
                </div>
            </div>

            <div class="row">
                <!-- Основная информация -->
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                Основная информация
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x text-muted"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Имя:</label>
                                <div class="fw-bold">{{ $user->name }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Email:</label>
                                <div class="fw-bold">{{ $user->email }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Дата регистрации:</label>
                                <div class="fw-bold">{{ $user->created_at->format('d.m.Y') }}</div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label text-muted">Последнее обновление:</label>
                                <div class="fw-bold">{{ $user->updated_at->format('d.m.Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Формы редактирования -->
                <div class="col-md-8">
                    <!-- Редактирование имени -->
                    <div class="card shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>
                                Изменить имя
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update.name') }}">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Сохранить имя
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Редактирование email -->
                    <div class="card shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-envelope me-2"></i>
                                Изменить email
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update.email') }}">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email адрес</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Сохранить email
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Изменение пароля -->
                    <div class="card shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-lock me-2"></i>
                                Изменить пароль
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update.password') }}">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Текущий пароль</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" 
                                               name="current_password" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Новый пароль</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password_icon"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">Минимум 8 символов</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Подтверждение нового пароля</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Изменить пароль
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопка возврата -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Вернуться на главную
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Автоматическое скрытие алертов
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });

    // Добавляем анимацию при отправке форм
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Сохранение...';
                submitBtn.disabled = true;
            }
        });
    });
});
</script>
@endpush