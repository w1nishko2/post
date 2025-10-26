@extends('layouts.app')

@section('content')
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="{{ route('home') }}">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill active" href="{{ route('products.select-bot') }}">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    @if(isset($telegramBot))
    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <!-- Адаптивная компоновка для мобильных -->
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between admin-flex-wrap admin-gap-sm">
                <div class="admin-d-flex admin-align-items-center admin-flex-1">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar {{ $telegramBot->is_active ? '' : 'inactive' }}">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div class="admin-flex-1">
                        <h6 class="admin-mb-1">{{ $telegramBot->bot_name }}</h6>
                        <div class="admin-text-muted">@{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                    <a href="{{ route('bot.products.index', $telegramBot) }}" class="admin-btn admin-btn-sm">
                        <i class="fas fa-th-large admin-me-1"></i>
                        <span class="admin-d-none-xs">Плитки</span>
                    </a>
                    <span class="admin-btn admin-btn-sm admin-btn-primary">
                        <i class="fas fa-table admin-me-1"></i>
                        <span class="admin-d-none-xs">Таблица</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-filters-row">
                <div class="admin-filter-group">
                    <label for="search" class="admin-form-label">
                        <span class="admin-d-none-xs">Поиск товаров</span>
                        <span class="admin-d-block-xs">Поиск</span>
                    </label>
                    <div class="admin-input-group">
                        <input type="text" class="admin-form-control" id="search" placeholder="Название или артикул">
                        <button class="admin-btn admin-btn-sm">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <!-- Категория -->
                <div class="admin-filter-group">
                    <label for="category-filter" class="admin-form-label">Категория</label>
                    <select class="admin-form-control admin-select" id="category-filter">
                        <option value="">Все</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Статус -->
                <div class="admin-filter-group">
                    <label for="status-filter" class="admin-form-label">Статус</label>
                    <select class="admin-form-control admin-select" id="status-filter">
                        <option value="">Все</option>
                        <option value="1">Активные</option>
                        <option value="0">Неактивные</option>
                    </select>
                </div>
                
                <!-- Действия -->
                <div class="admin-filter-actions">
                    <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                        <a href="{{ route('bot.products.create', $telegramBot) }}" 
                           class="admin-btn admin-btn-primary admin-btn-sm">
                            <i class="fas fa-plus admin-me-1"></i>
                            <span class="admin-d-none-xs">Добавить</span>
                        </a>
                        <button class="admin-btn admin-btn-sm" onclick="openImportModal()">
                            <i class="fas fa-upload admin-me-1"></i>
                            <span class="admin-d-none-xs">Импорт</span>
                        </button>
                        <button class="admin-btn admin-btn-sm">
                            <i class="fas fa-download admin-me-1"></i>
                            <span class="admin-d-none-xs">Экспорт</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица товаров -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center admin-flex-wrap admin-gap-sm">
                <h5 class="admin-mb-0 admin-flex-1">
                    <i class="fas fa-table admin-me-2"></i>
                    <span class="admin-d-none-xs">Товары </span>
                    <span>({{ $products->total() ?? 0 }})</span>
                </h5>
                <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                    <span class="admin-text-muted admin-d-none-xs">Показать:</span>
                    <select class="admin-form-control admin-select" id="per-page" 
                            style="width: auto; min-width: 60px; padding: 4px 8px;">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="admin-card-body admin-p-0">
            @if($products->count() > 0)
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="admin-form-check-input" id="select-all">
                                </th>
                                <th style="width: 60px;">Фото</th>
                                <th>Товар</th>
                                <th style="width: 100px;">Артикул</th>
                                <th style="width: 170px;">Категория</th>
                                <th style="width: 150px;">Цена</th>
                                <th style="width: 120px;">Кол-во</th>
                                <th style="width: 120px;">Статус</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr data-product-id="{{ $product->id }}">
                                    <td>
                                        <input type="checkbox" class="admin-form-check-input product-checkbox" 
                                               value="{{ $product->id }}">
                                    </td>
                                    <td>
                                        @if($product->main_photo_url)
                                            <div class="admin-product-photo">
                                                <img src="{{ $product->main_photo_url }}" alt="{{ $product->name }}">
                                            </div>
                                        @else
                                            <div class="admin-product-photo admin-no-photo">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-product-info">
                                            <div class="admin-product-name">
                                                <span class="editable-field" 
                                                      data-field="name" 
                                                      data-type="text" 
                                                      data-value="{{ $product->name }}"
                                                      title="Нажмите для редактирования">
                                                    {{ $product->name }}
                                                </span>
                                                <input type="text" class="admin-form-control edit-input" 
                                                       value="{{ $product->name }}" 
                                                       style="display: none; font-size: 14px; padding: 4px 8px;">
                                            </div>
                                            @if($product->description)
                                                <div class="admin-product-desc">
                                                    {{ Str::limit($product->description, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($product->article)
                                            <span class="admin-text-mono">{{ $product->article }}</span>
                                        @else
                                            <span class="admin-text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="editable-field" 
                                              data-field="category_id" 
                                              data-type="select" 
                                              data-value="{{ $product->category_id }}"
                                              title="Нажмите для изменения категории">
                                            @if($product->category)
                                                <span class="admin-badge">{{ $product->category->name }}</span>
                                            @else
                                                <span class="admin-text-muted">Без категории</span>
                                            @endif
                                        </span>
                                        <select class="admin-form-control edit-input" style="display: none; font-size: 12px; padding: 4px;">
                                            <option value="">Без категории</option>
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="admin-product-price">
                                            <span class="editable-field" 
                                                  data-field="price" 
                                                  data-type="number" 
                                                  data-value="{{ $product->price }}"
                                                  title="Нажмите для изменения цены">
                                                {{ number_format($product->price, 0, ',', ' ') }} ₽
                                            </span>
                                            <input type="number" class="admin-form-control edit-input" 
                                                   value="{{ $product->price }}" 
                                                   min="0" step="1"
                                                   style="display: none; font-size: 12px; padding: 4px; width: 80px;">
                                            @if($product->markup_percentage > 0)
                                                <div class="admin-text-muted admin-small">
                                                    +{{ $product->markup_percentage }}%
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            <span class="editable-field" 
                                                  data-field="quantity" 
                                                  data-type="number" 
                                                  data-value="{{ $product->quantity }}"
                                                  title="Нажмите для изменения количества">
                                                <span class="admin-badge {{ $product->quantity > 0 ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                    {{ $product->quantity }}
                                                </span>
                                            </span>
                                            <input type="number" class="admin-form-control edit-input" 
                                                   value="{{ $product->quantity }}" 
                                                   min="0" step="1"
                                                   style="display: none; font-size: 12px; padding: 4px; width: 60px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            <span class="editable-field status-toggle" 
                                                  data-field="is_active" 
                                                  data-type="boolean" 
                                                  data-value="{{ $product->is_active ? 1 : 0 }}"
                                                  title="Нажмите для изменения статуса"
                                                  style="cursor: pointer;">
                                                @if($product->is_active)
                                                    <span class="admin-status-active">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                @else
                                                    <span class="admin-status-inactive">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="{{ route('bot.products.show', [$telegramBot, $product]) }}" 
                                               class="admin-btn admin-btn-xs" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('bot.products.edit', [$telegramBot, $product]) }}" 
                                               class="admin-btn admin-btn-xs admin-btn-primary" title="Полное редактирование">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="admin-btn admin-btn-xs admin-btn-danger" 
                                                    onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')" 
                                                    title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                @if($products->hasPages())
                    <div class="admin-card-footer">
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <div class="admin-text-muted">
                                Показаны записи {{ $products->firstItem() }}-{{ $products->lastItem() }} 
                                из {{ $products->total() }}
                            </div>
                            <div class="admin-pagination">
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
                        </div>
                    </div>
                @endif
            @else
                <div class="admin-empty-state">
                    <div class="admin-empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h6>Товары не найдены</h6>
                    <p class="admin-text-muted">У вас пока нет товаров в этом боте</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Массовые действия -->
    <div class="admin-card admin-mt-4" id="bulk-actions" style="display: none;">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center admin-flex-wrap admin-gap-sm">
                <div class="admin-flex-1">
                    <span id="selected-count">0</span> товаров выбрано
                </div>
                <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                    <button class="admin-btn admin-btn-sm" onclick="bulkActivate()">
                        <i class="fas fa-check admin-me-1"></i>
                        <span class="admin-d-none-xs">Активировать</span>
                    </button>
                    <button class="admin-btn admin-btn-sm" onclick="bulkDeactivate()">
                        <i class="fas fa-times admin-me-1"></i>
                        <span class="admin-d-none-xs">Деактивировать</span>
                    </button>
                    <button class="admin-btn admin-btn-sm admin-btn-danger" onclick="bulkDelete()">
                        <i class="fas fa-trash admin-me-1"></i>
                        <span class="admin-d-none-xs">Удалить</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно импорта -->
<div class="admin-modal" id="importModal">
    <div class="admin-modal-dialog">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h5 class="admin-modal-title">
                    <i class="fas fa-upload admin-me-2"></i>
                    Импорт товаров
                </h5>
                <button class="admin-modal-close" onclick="closeImportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="admin-modal-body">
                <form method="POST" action="{{ route('bot.products.import', $telegramBot) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="admin-form-group">
                        <label for="import_file" class="admin-form-label">Выберите файл для импорта</label>
                        <input type="file" class="admin-form-control" id="import_file" name="file" 
                               accept=".csv,.xlsx,.xls" required>
                        <div class="admin-form-help">Поддерживаемые форматы: CSV, Excel (.xlsx, .xls)</div>
                    </div>
                    
                    <div class="admin-form-group">
                        <div class="admin-form-check">
                            <input type="checkbox" class="admin-form-check-input" id="update_existing" 
                                   name="update_existing" value="1">
                            <label for="update_existing" class="admin-form-check-label">
                                Обновлять существующие товары
                            </label>
                        </div>
                    </div>
                    
                    <div class="admin-d-flex admin-justify-content-between">
                        <a href="{{ route('bot.products.download-template', $telegramBot) }}" class="admin-btn">
                            <i class="fas fa-download admin-me-2"></i>
                            Скачать шаблон
                        </a>
                        <div class="admin-d-flex admin-gap-sm">
                            <button type="button" class="admin-btn" onclick="closeImportModal()">
                                Отмена
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-upload admin-me-2"></i>
                                Импортировать
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Выбор всех товаров
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionsCard = document.getElementById('bulk-actions');
    const selectedCountSpan = document.getElementById('selected-count');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (selectedCountSpan) {
            selectedCountSpan.textContent = count;
        }
        
        if (bulkActionsCard) {
            bulkActionsCard.style.display = count > 0 ? 'block' : 'none';
        }
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = count === productCheckboxes.length;
            selectAllCheckbox.indeterminate = count > 0 && count < productCheckboxes.length;
        }
    }

    // Поиск товаров
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Реализация поиска
                console.log('Поиск:', this.value);
            }, 300);
        });
    }

    // Фильтры
    const categoryFilter = document.getElementById('category-filter');
    const statusFilter = document.getElementById('status-filter');
    const perPageSelect = document.getElementById('per-page');

    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            // Реализация фильтрации по категории
            console.log('Фильтр категории:', this.value);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            // Реализация фильтрации по статусу
            console.log('Фильтр статуса:', this.value);
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            // Изменение количества записей на странице
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            window.location.href = url.toString();
        });
    }

    // ===== INLINE РЕДАКТИРОВАНИЕ =====
    
    // Обработка кликов по редактируемым полям
    document.querySelectorAll('.editable-field').forEach(field => {
        field.addEventListener('click', function(e) {
            e.preventDefault();
            startEditing(this);
        });
    });

    function startEditing(element) {
        const type = element.dataset.type;
        const currentValue = element.dataset.value;
        
        // Скрываем текст и показываем поле ввода
        element.style.display = 'none';
        const input = element.parentElement.querySelector('.edit-input');
        
        if (input) {
            input.style.display = 'inline-block';
            
            if (type === 'select') {
                input.value = currentValue;
            } else if (type === 'number') {
                input.value = currentValue;
            } else {
                input.value = currentValue;
            }
            
            input.focus();
            
            // Обработчики для сохранения/отмены
            const saveEdit = () => {
                const newValue = input.value;
                const productId = element.closest('tr').dataset.productId;
                const field = element.dataset.field;
                
                if (newValue !== currentValue) {
                    updateProductField(productId, field, newValue, element, input);
                } else {
                    cancelEdit(element, input);
                }
            };
            
            const cancelEdit = (element, input) => {
                input.style.display = 'none';
                element.style.display = 'inline';
            };
            
            // Сохранение при Enter или потере фокуса
            input.addEventListener('blur', saveEdit, { once: true });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveEdit();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelEdit(element, input);
                }
            }, { once: true });
        }
    }

    // Специальная обработка для статусов (toggle)
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.closest('tr').dataset.productId;
            const currentValue = this.dataset.value;
            const newValue = currentValue === '1' ? '0' : '1';
            
            updateProductField(productId, 'is_active', newValue, this);
        });
    });

    function updateProductField(productId, field, value, element, input = null) {
        // Показываем индикатор загрузки
        const originalHtml = element.innerHTML;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // AJAX запрос для обновления
        fetch('{{ route("bot.products.update-field", $telegramBot) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id: productId,
                field: field,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем отображение
                updateFieldDisplay(element, field, value, data.formatted_value);
                element.dataset.value = value;
                
                // Скрываем поле ввода если есть
                if (input) {
                    input.style.display = 'none';
                    element.style.display = 'inline';
                }
                
                // Показываем уведомление об успехе
                showNotification('Поле успешно обновлено', 'success');
            } else {
                // Восстанавливаем оригинальное значение
                element.innerHTML = originalHtml;
                if (input) {
                    input.style.display = 'none';
                    element.style.display = 'inline';
                }
                showNotification(data.message || 'Ошибка при обновлении', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            element.innerHTML = originalHtml;
            if (input) {
                input.style.display = 'none';
                element.style.display = 'inline';
            }
            showNotification('Произошла ошибка при обновлении', 'error');
        });
    }

    function updateFieldDisplay(element, field, value, formattedValue) {
        switch (field) {
            case 'name':
                element.textContent = value;
                break;
                
            case 'price':
                element.innerHTML = formattedValue || (new Intl.NumberFormat('ru-RU').format(value) + ' ₽');
                break;
                
            case 'quantity':
                const badgeClass = parseInt(value) > 0 ? 'admin-badge-success' : 'admin-badge-danger';
                element.innerHTML = `<span class="admin-badge ${badgeClass}">${value}</span>`;
                break;
                
            case 'is_active':
                const isActive = value === '1' || value === 1;
                if (isActive) {
                    element.innerHTML = '<span class="admin-status-active"><i class="fas fa-check-circle"></i></span>';
                } else {
                    element.innerHTML = '<span class="admin-status-inactive"><i class="fas fa-times-circle"></i></span>';
                }
                break;
                
            case 'category_id':
                if (formattedValue) {
                    element.innerHTML = `<span class="admin-badge">${formattedValue}</span>`;
                } else {
                    element.innerHTML = '<span class="admin-text-muted">Без категории</span>';
                }
                break;
        }
    }

    function showNotification(message, type = 'info') {
        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `admin-alert admin-alert-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} admin-me-2"></i>
            ${message}
            <button class="admin-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматически удаляем через 3 секунды
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }
});

// Функции для работы с товарами
function deleteProduct(productId, productName) {
    if (confirm(`Вы уверены, что хотите удалить товар "${productName}"?\n\nЭто действие нельзя отменить!`)) {
        // Создаем скрытую форму для удаления
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("bot.products.destroy", [$telegramBot, ":id"]) }}'.replace(':id', productId);
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Массовые действия
function bulkActivate() {
    const selectedIds = getSelectedProductIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Активировать ${selectedIds.length} товаров?`)) {
        // Реализация массовой активации
        console.log('Активировать товары:', selectedIds);
    }
}

function bulkDeactivate() {
    const selectedIds = getSelectedProductIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Деактивировать ${selectedIds.length} товаров?`)) {
        // Реализация массовой деактивации
        console.log('Деактивировать товары:', selectedIds);
    }
}

function bulkDelete() {
    const selectedIds = getSelectedProductIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Удалить ${selectedIds.length} товаров?\n\nЭто действие нельзя отменить!`)) {
        // Реализация массового удаления
        console.log('Удалить товары:', selectedIds);
    }
}

function getSelectedProductIds() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

// Модальное окно импорта
function openImportModal() {
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}
</script>
@endsection