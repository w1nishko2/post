@extends('layouts.app')

@section('content')
<div class="admin-container">
    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            <i class="fas fa-check-circle admin-me-2"></i>
            {{ session('success') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            {{ session('error') }}
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('warning'))
        <div class="admin-alert admin-alert-warning">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            {{ session('warning') }}
            @if (session('import_errors'))
                <div class="admin-mt-2 admin-pt-2" style="border-top: 1px solid var(--color-border);">
                    <strong>Детали ошибок:</strong><br>
                    <small>{!! nl2br(e(session('import_errors'))) !!}</small>
                </div>
            @endif
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="{{ route('home') }}">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill active" href="{{ route('products.select-bot') }}">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <!-- Информация о боте -->
    @if(isset($telegramBot))
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <div class="admin-d-flex admin-align-items-center">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar {{ $telegramBot->is_active ? '' : 'inactive' }}">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="admin-mb-1">{{ $telegramBot->bot_name }}</h6>
                        <div class="admin-text-muted">@{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('products.select-bot') }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-exchange-alt admin-me-2"></i>
                        Сменить бота
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Блоки управления -->
    @if(isset($telegramBot))
    <div class="admin-row admin-mb-4">
        <!-- Блок управления категориями -->
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-tags admin-me-2"></i>
                        Управление категориями
                    </h6>
                </div>
                <div class="admin-card-body">
                    <p class="admin-text-muted admin-mb-3">
                        Создавайте и управляйте категориями товаров для лучшей организации каталога
                    </p>
                    <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                        <a href="{{ route('bot.categories.index', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-list admin-me-1"></i>
                            Все категории
                        </a>
                        <a href="{{ route('bot.categories.create', $telegramBot) }}" class="admin-btn admin-btn-sm admin-btn-primary">
                            <i class="fas fa-plus admin-me-1"></i>
                            Добавить
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Блок массового управления товарами -->
        <div class="admin-col admin-col-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-mb-0">
                        <i class="fas fa-boxes admin-me-2"></i>
                        Массовые операции
                    </h6>
                </div>
                <div class="admin-card-body">
                    <p class="admin-text-muted admin-mb-3">
                        Импорт товаров из Excel файлов и экспорт данных для массового редактирования
                    </p>
                    <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                        <button class="admin-btn admin-btn-sm admin-btn-success" onclick="showModal('importModal')">
                            <i class="fas fa-upload admin-me-1"></i>
                            Импорт
                        </button>
                        <a href="{{ route('bot.products.export-data', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-download admin-me-1"></i>
                            Экспорт
                        </a>
                        <a href="{{ route('bot.products.download-template', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-file-excel admin-me-1"></i>
                            Шаблон
                        </a>
                        <a href="{{ route('bot.products.table', $telegramBot) }}" class="admin-btn admin-btn-sm">
                            <i class="fas fa-table admin-me-1"></i>
                            Таблица
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body admin-text-center">
            <i class="fas fa-info-circle admin-text-info" style="font-size: 32px;"></i>
            <h5 class="admin-mt-3">Выберите бота для управления товарами</h5>
            <p class="admin-text-muted">Сначала выберите бота из списка выше</p>
        </div>
    </div>
    @endif

    <!-- Контент товаров -->
    <div class="admin-card">
        <div class="admin-card-header admin-d-flex admin-justify-content-between admin-align-items-center">
            <h5 class="admin-mb-0">Управление товарами</h5>
            @if(isset($telegramBot))
                <div class="admin-d-flex admin-gap-sm">
                    <a href="{{ route('bot.products.create', $telegramBot) }}" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить товар
                    </a>
                </div>
            @else
                <span class="admin-text-muted">Выберите бота для добавления товаров</span>
            @endif
        </div>

        <div class="admin-card-body">
            @if($products->count() > 0)
                <div class="admin-products-grid">
                    @foreach($products as $product)
                        <div class="admin-product-card">
                            <div class="admin-product-image">
                                @if($product->main_photo_url)
                                    <img src="{{ $product->main_photo_url }}" alt="{{ $product->name }}">
                                @else
                                    <div class="admin-d-flex admin-align-items-center admin-justify-content-center admin-h-100 admin-text-muted">
                                        <i class="fas fa-image" style="font-size: 32px;"></i>
                                    </div>
                                @endif
                                <div class="admin-product-badge">
                                    @if($product->quantity > 0)
                                        <span class="admin-badge admin-badge-success">{{ $product->quantity }} шт</span>
                                    @else
                                        <span class="admin-badge admin-badge-danger">Нет в наличии</span>
                                    @endif
                                </div>
                            </div>
                            <div class="admin-product-info">
                                <h6 class="admin-product-name">{{ $product->name }}</h6>
                                @if($product->description)
                                    <p class="admin-product-description">{{ $product->description }}</p>
                                @endif
                                <div class="admin-product-footer">
                                    <div class="admin-product-price">{{ number_format($product->price, 0, ',', ' ') }} ₽</div>
                                    <div class="admin-product-actions">
                                        <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" 
                                           class="admin-btn admin-btn-sm" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" 
                                           class="admin-btn admin-btn-sm" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('bot.products.destroy', [$telegramBot, $product]) }}" 
                                              class="admin-d-inline" onsubmit="return confirm('Удалить товар {{ $product->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline-danger" title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Пагинация -->
                @if($products->hasPages())
                <div class="admin-pagination admin-mt-4">
                    @if($products->onFirstPage())
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="admin-page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if($page == $products->currentPage())
                            <span class="admin-page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="admin-page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="admin-page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="admin-page-link disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
                @endif
            @else
                <div class="admin-empty-state">
                    <i class="fas fa-boxes"></i>
                    <h3>Товары не найдены</h3>
                    @if(isset($telegramBot))
                        <p class="admin-mb-4">Создайте первый товар для этого бота</p>
                        
                    @else
                        <p>Сначала выберите бота для просмотра товаров</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно импорта -->
@if(isset($telegramBot))
<div class="admin-modal" id="importModal">
    <div class="admin-modal-dialog">
        <div class="admin-modal-content">
            <form method="POST" action="{{ route('bot.products.import', $telegramBot) }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="admin-modal-header">
                    <h5 class="admin-modal-title">
                        <i class="fas fa-upload admin-me-2"></i>
                        Импорт товаров
                    </h5>
                    <button type="button" class="admin-modal-close" onclick="hideModal('importModal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="admin-modal-body">
                    <!-- Прогресс импорта -->
                    <div id="importProgress" class="import-progress" style="display: none;">
                        <div class="admin-text-center admin-mb-3">
                            <h5 class="admin-mb-2">
                                <i class="fas fa-spinner fa-spin admin-me-2"></i>
                                Импорт товаров в процессе...
                            </h5>
                            <p class="admin-text-muted">Пожалуйста, дождитесь завершения процесса</p>
                        </div>
                        
                        <!-- Прогресс-бар -->
                        <div class="progress-bar-container admin-mb-3">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                            <div class="progress-text" id="progressText">0%</div>
                        </div>
                        
                        <!-- Статус импорта -->
                        <div class="import-status" id="importStatus">
                            <div class="status-item">
                                <i class="fas fa-clock admin-me-2"></i>
                                <span>Подготовка к импорту...</span>
                            </div>
                        </div>

                        <!-- Информация о фоновой обработке -->
                        <div class="admin-alert admin-alert-info admin-mt-3" style="font-size: 0.9rem;">
                            <div class="admin-d-flex admin-align-items-start">
                                <i class="fas fa-info-circle admin-me-2" style="margin-top: 2px;"></i>
                                <div>
                                    <strong>Можно закрыть это окно</strong><br>
                                    Товары появятся в списке автоматически по завершении импорта. 
                                    Если включена загрузка изображений, они будут скачиваться в фоновом режиме после импорта.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Форма импорта -->
                    <div id="importFormContent">
                        <div class="admin-form-group">
                            <label for="import_file" class="admin-form-label required">Файл Excel</label>
                            <input type="file" class="admin-form-control" id="import_file" name="file" 
                                   accept=".xlsx,.xls,.csv" required>
                            <div class="admin-form-text">Поддерживаемые форматы: Excel (.xlsx, .xls), CSV</div>
                        </div>

                        <div class="admin-form-group admin-mb-3">
                            <div class="admin-form-check">
                                <input type="checkbox" class="admin-form-check-input" id="update_existing" name="update_existing" value="1">
                                <label for="update_existing" class="admin-form-check-label">
                                    Обновлять существующие товары по артикулу
                                </label>
                            </div>
                        </div>

                        <div class="admin-form-group admin-mb-0">
                            <div class="admin-form-check">
                                <input type="checkbox" class="admin-form-check-input" id="download_images" name="download_images" value="1" checked>
                                <label for="download_images" class="admin-form-check-label">
                                    <i class="fas fa-download admin-me-1"></i>
                                    Скачивать изображения по ссылкам
                                </label>
                                <small class="admin-text-muted admin-d-block admin-mt-1" style="padding-left: 1.5rem;">
                                    <i class="fas fa-info-circle"></i>
                                    Поддержка: прямые ссылки (через ;), Яндекс.Диск папки <code>/d/</code> и файлы <code>/i/</code>.
                                    <br><strong>⚠️ Альбомы <code>/a/</code> не поддерживаются!</strong> Используйте папки или отдельные файлы.
                                    <br>Изображения загружаются в фоновом режиме после импорта.
                                    <a href="{{ asset('YANDEX_DISK_LINKS_GUIDE.md') }}" target="_blank" class="admin-text-primary">
                                        <i class="fas fa-question-circle"></i> Подробнее
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn" onclick="hideModal('importModal')" id="cancelButton">Отмена</button>
                    <button type="submit" class="admin-btn admin-btn-success" id="importButton">
                        <i class="fas fa-upload admin-me-2"></i>
                        Импортировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Стили для прогресса импорта -->
<style>
.progress-bar-container {
    position: relative;
    margin: 20px 0;
}

.progress-bar {
    width: 100%;
    height: 25px;
    background-color: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(45deg, #28a745, #20c997);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 12px;
    position: relative;
}

.progress-fill::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
    );
    background-size: 30px 30px;
    animation: move 2s linear infinite;
}

@keyframes move {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 30px 30px;
    }
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    color: #495057;
    font-size: 14px;
}

.import-status {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    max-height: 200px;
    overflow-y: auto;
}

.status-item {
    padding: 5px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    display: flex;
    align-items: center;
}

.status-item:last-child {
    border-bottom: none;
}

.status-item i {
    width: 20px;
    color: #6c757d;
}

.status-item.success i {
    color: #28a745;
}

.status-item.error i {
    color: #dc3545;
}

.status-item.warning i {
    color: #ffc107;
}

.import-progress {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
@endif

<script>
// Функции для работы с модальными окнами
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Сброс состояния импорта при закрытии
        resetImportState();
    }
}

// Сброс состояния импорта
function resetImportState() {
    document.getElementById('importFormContent').style.display = 'block';
    document.getElementById('importProgress').style.display = 'none';
    document.getElementById('cancelButton').textContent = 'Отмена';
    document.getElementById('importButton').disabled = false;
    document.getElementById('progressFill').style.width = '0%';
    document.getElementById('progressText').textContent = '0%';
    document.getElementById('importStatus').innerHTML = '<div class="status-item"><i class="fas fa-clock admin-me-2"></i><span>Подготовка к импорту...</span></div>';
    
    // Очищаем форму
    document.getElementById('importForm').reset();
}

// Обработка отправки формы импорта
document.addEventListener('DOMContentLoaded', function() {
    // Закрытие модальных окон при клике на фон
    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal(modal.id);
            }
        });
    });

    // Обработка формы импорта
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            startImport();
        });
    }
});

// Запуск процесса импорта
function startImport() {
    const formData = new FormData(document.getElementById('importForm'));
    const fileInput = document.getElementById('import_file');
    
    if (!fileInput.files.length) {
        alert('Пожалуйста, выберите файл для импорта');
        return;
    }

    // Переключаемся на экран прогресса
    document.getElementById('importFormContent').style.display = 'none';
    document.getElementById('importProgress').style.display = 'block';
    document.getElementById('cancelButton').textContent = 'Скрыть';
    document.getElementById('importButton').disabled = true;

    // Симуляция прогресса (так как мы не можем получить реальный прогресс от сервера)
    simulateProgress();

    // Отправляем запрос на сервер
    fetch('{{ route("bot.products.ajax-import", $telegramBot ?? 0) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.redirected) {
            // Если сервер перенаправляет (обычный случай), идем по ссылке
            window.location.href = response.url;
        } else {
            return response.json();
        }
    })
    .then(data => {
        if (data) {
            // Обработка JSON ответа (если сервер вернул JSON вместо редиректа)
            completeImport(data);
        }
    })
    .catch(error => {
        console.error('Ошибка импорта:', error);
        addStatusItem('error', 'Произошла ошибка при импорте: ' + error.message);
        document.getElementById('cancelButton').textContent = 'Закрыть';
    });
}

// Симуляция прогресса импорта
function simulateProgress() {
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 95) {
            progress = 95; // Останавливаем на 95% до получения ответа от сервера
        }
        
        updateProgress(progress);
        
        // Добавляем случайные статусы
        if (progress > 20 && progress < 30) {
            addStatusItem('success', 'Файл успешно загружен');
        }
        if (progress > 40 && progress < 50) {
            addStatusItem('success', 'Начата обработка товаров');
        }
        if (progress > 60 && progress < 70) {
            addStatusItem('success', 'Обработка изображений Яндекс.Диск');
        }
        if (progress > 80 && progress < 90) {
            addStatusItem('success', 'Сохранение в базу данных');
        }
        
        if (progress >= 95) {
            clearInterval(interval);
            addStatusItem('warning', 'Завершение импорта...');
        }
    }, 200);
    
    // Сохраняем интервал для возможности остановки
    window.importProgressInterval = interval;
}

// Обновление прогресс-бара
function updateProgress(percent) {
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    progressFill.style.width = percent + '%';
    progressText.textContent = Math.round(percent) + '%';
}

// Добавление элемента статуса
function addStatusItem(type, message) {
    const statusContainer = document.getElementById('importStatus');
    const statusItem = document.createElement('div');
    statusItem.className = `status-item ${type}`;
    
    let icon;
    switch(type) {
        case 'success':
            icon = 'fas fa-check-circle';
            break;
        case 'error':
            icon = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            break;
        default:
            icon = 'fas fa-info-circle';
    }
    
    statusItem.innerHTML = `<i class="${icon} admin-me-2"></i><span>${message}</span>`;
    statusContainer.appendChild(statusItem);
    
    // Прокручиваем вниз
    statusContainer.scrollTop = statusContainer.scrollHeight;
}

// Завершение импорта
function completeImport(data) {
    // Останавливаем симуляцию прогресса
    if (window.importProgressInterval) {
        clearInterval(window.importProgressInterval);
    }
    
    // Устанавливаем 100% прогресс
    updateProgress(100);
    
    // Добавляем финальный статус
    if (data.success) {
        addStatusItem('success', data.message || 'Импорт успешно завершен!');
    } else {
        addStatusItem('error', data.message || 'Произошла ошибка при импорте');
    }
    
    // Меняем кнопку
    document.getElementById('cancelButton').textContent = 'Закрыть';
    
    // Автоматически перенаправляем через 3 секунды
    setTimeout(() => {
        window.location.reload();
    }, 3000);
}

// Обработка ошибок загрузки изображений
function handleImageError(img) {
    // Создаем SVG placeholder
    const svgPlaceholder = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
        <svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f8f9fa"/>
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" 
                  font-family="Arial, sans-serif" font-size="14" fill="#6c757d">
                Изображение недоступно
            </text>
            <rect x="10" y="10" width="280" height="180" fill="none" stroke="#dee2e6" stroke-width="2" stroke-dasharray="5,5"/>
        </svg>
    `)}`;
    
    img.src = svgPlaceholder;
    img.classList.add('image-error');
    
    // Добавляем возможность повторной загрузки по клику
    img.style.cursor = 'pointer';
    img.title = 'Нажмите для повторной загрузки';
    
    img.onclick = function() {
        const originalSrc = this.getAttribute('data-original-src');
        if (originalSrc) {
            this.src = originalSrc;
            this.onclick = null;
            this.style.cursor = 'auto';
            this.title = '';
        }
    };
}

// Применяем обработчик ошибок ко всем изображениям товаров
document.addEventListener('DOMContentLoaded', function() {
    const productImages = document.querySelectorAll('.admin-product-image img');
    productImages.forEach(img => {
        // Сохраняем оригинальный src
        img.setAttribute('data-original-src', img.src);
        
        // Добавляем обработчик ошибки
        img.addEventListener('error', function() {
            handleImageError(this);
        });
        
        // Добавляем класс для стилизации
        img.addEventListener('load', function() {
            this.classList.add('image-loaded');
        });
    });
});
</script>

<!-- Дополнительные стили для изображений -->
<style>
.admin-product-image img {
    transition: opacity 0.3s ease;
    opacity: 1;
}

.admin-product-image img.image-loaded {
    opacity: 1;
}

.admin-product-image img.image-error {
    opacity: 0.7;
    border: 2px dashed #dee2e6;
}

.admin-product-image img.image-error:hover {
    opacity: 1;
    border-color: #007bff;
}
</style>
@endsection