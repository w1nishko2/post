@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Навигационная панель -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link" href="{{ route('home') }}">
                            Мои боты
                        </a>
                        <a class="nav-link active" href="{{ route('products.select-bot') }}">
                            Мои товары
                        </a>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Выберите магазин (бота) для управления товарами</h5>
                </div>

                <div class="card-body">
                    @if($bots->count() > 0)
                        <div class="row">
                            @foreach($bots as $bot)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 bot-card">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    @if($bot->is_active)
                                                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-robot text-white"></i>
                                                        </div>
                                                    @else
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-robot text-white"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $bot->bot_name }}</h6>
                                                    <small class="text-muted">@{{ $bot->bot_username }}</small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Товаров:</span>
                                                    <span class="badge bg-primary">{{ $bot->products_count ?? $bot->products()->count() }}</span>
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                <a href="{{ route('bot.products.index', $bot) }}" class="btn btn-primary w-100">
                                                    <i class="fas fa-boxes"></i> Управлять товарами
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                            <h5>У вас пока нет ботов</h5>
                            <p class="text-muted">Создайте первого бота, чтобы начать управлять товарами</p>
                            <a href="{{ route('telegram-bots.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Создать бота
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.bot-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.bot-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
@endpush