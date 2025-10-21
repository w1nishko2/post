

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('products.select-bot')); ?>">
            <i class="fas fa-boxes"></i> Мои магазины
        </a>
    </div>

    <?php if(isset($telegramBot)): ?>
    <!-- Информация о боте -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between">
                <div class="admin-d-flex admin-align-items-center">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar <?php echo e($telegramBot->is_active ? '' : 'inactive'); ?>">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="admin-mb-1"><?php echo e($telegramBot->bot_name); ?></h6>
                        <div class="admin-text-muted">{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div class="admin-d-flex admin-gap-sm">
                    <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn admin-btn-sm">
                        <i class="fas fa-th-large admin-me-2"></i>
                        Плитки
                    </a>
                    <span class="admin-btn admin-btn-sm admin-btn-primary">
                        <i class="fas fa-table admin-me-2"></i>
                        Таблица
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Действия и поиск -->
    <div class="admin-card admin-mb-4">
        <div class="admin-card-body">
            <div class="admin-row admin-align-items-end admin-gap-md">
                <div class="admin-col admin-col-4">
                    <div class="admin-form-group admin-mb-0">
                        <label for="search" class="admin-form-label">Поиск товаров</label>
                        <div class="admin-input-group">
                            <input type="text" class="admin-form-control" id="search" placeholder="Поиск по названию или артикулу">
                            <button class="admin-btn admin-btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="admin-col admin-col-3">
                    <div class="admin-form-group admin-mb-0">
                        <label for="category-filter" class="admin-form-label">Категория</label>
                        <select class="admin-form-control admin-select" id="category-filter">
                            <option value="">Все категории</option>
                            <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="admin-col admin-col-2">
                    <div class="admin-form-group admin-mb-0">
                        <label for="status-filter" class="admin-form-label">Статус</label>
                        <select class="admin-form-control admin-select" id="status-filter">
                            <option value="">Все</option>
                            <option value="1">Активные</option>
                            <option value="0">Неактивные</option>
                        </select>
                    </div>
                </div>
                <div class="admin-col admin-col-3">
                    <div class="admin-d-flex admin-gap-sm">
                        <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="admin-btn admin-btn-primary admin-btn-sm">
                            <i class="fas fa-plus admin-me-1"></i>
                            Добавить товар
                        </a>
                        <button class="admin-btn admin-btn-sm" onclick="openImportModal()">
                            <i class="fas fa-upload admin-me-1"></i>
                            Импорт
                        </button>
                        <button class="admin-btn admin-btn-sm">
                            <i class="fas fa-download admin-me-1"></i>
                            Экспорт
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица товаров -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <h5 class="admin-mb-0">
                    <i class="fas fa-table admin-me-2"></i>
                    Товары (<?php echo e($products->total() ?? 0); ?>)
                </h5>
                <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                    <span class="admin-text-muted">Показать по:</span>
                    <select class="admin-form-control admin-form-control-sm" id="per-page" style="width: auto;">
                        <option value="10" <?php echo e(request('per_page', 15) == 10 ? 'selected' : ''); ?>>10</option>
                        <option value="15" <?php echo e(request('per_page', 15) == 15 ? 'selected' : ''); ?>>15</option>
                        <option value="25" <?php echo e(request('per_page', 15) == 25 ? 'selected' : ''); ?>>25</option>
                        <option value="50" <?php echo e(request('per_page', 15) == 50 ? 'selected' : ''); ?>>50</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="admin-card-body admin-p-0">
            <?php if($products->count() > 0): ?>
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
                                <th style="width: 120px;">Категория</th>
                                <th style="width: 100px;">Цена</th>
                                <th style="width: 80px;">Кол-во</th>
                                <th style="width: 80px;">Статус</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-product-id="<?php echo e($product->id); ?>">
                                    <td>
                                        <input type="checkbox" class="admin-form-check-input product-checkbox" 
                                               value="<?php echo e($product->id); ?>">
                                    </td>
                                    <td>
                                        <?php if($product->photo_url): ?>
                                            <div class="admin-product-photo">
                                                <img src="<?php echo e($product->photo_url); ?>" alt="<?php echo e($product->name); ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="admin-product-photo admin-no-photo">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-product-info">
                                            <div class="admin-product-name">
                                                <span class="editable-field" 
                                                      data-field="name" 
                                                      data-type="text" 
                                                      data-value="<?php echo e($product->name); ?>"
                                                      title="Нажмите для редактирования">
                                                    <?php echo e($product->name); ?>

                                                </span>
                                                <input type="text" class="admin-form-control edit-input" 
                                                       value="<?php echo e($product->name); ?>" 
                                                       style="display: none; font-size: 14px; padding: 4px 8px;">
                                            </div>
                                            <?php if($product->description): ?>
                                                <div class="admin-product-desc">
                                                    <?php echo e(Str::limit($product->description, 50)); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($product->article): ?>
                                            <span class="admin-text-mono"><?php echo e($product->article); ?></span>
                                        <?php else: ?>
                                            <span class="admin-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="editable-field" 
                                              data-field="category_id" 
                                              data-type="select" 
                                              data-value="<?php echo e($product->category_id); ?>"
                                              title="Нажмите для изменения категории">
                                            <?php if($product->category): ?>
                                                <span class="admin-badge"><?php echo e($product->category->name); ?></span>
                                            <?php else: ?>
                                                <span class="admin-text-muted">Без категории</span>
                                            <?php endif; ?>
                                        </span>
                                        <select class="admin-form-control edit-input" style="display: none; font-size: 12px; padding: 4px;">
                                            <option value="">Без категории</option>
                                            <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($category->id); ?>" 
                                                        <?php echo e($product->category_id == $category->id ? 'selected' : ''); ?>>
                                                    <?php echo e($category->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="admin-product-price">
                                            <span class="editable-field" 
                                                  data-field="price" 
                                                  data-type="number" 
                                                  data-value="<?php echo e($product->price); ?>"
                                                  title="Нажмите для изменения цены">
                                                <?php echo e(number_format($product->price, 0, ',', ' ')); ?> ₽
                                            </span>
                                            <input type="number" class="admin-form-control edit-input" 
                                                   value="<?php echo e($product->price); ?>" 
                                                   min="0" step="1"
                                                   style="display: none; font-size: 12px; padding: 4px; width: 80px;">
                                            <?php if($product->markup_percentage > 0): ?>
                                                <div class="admin-text-muted admin-small">
                                                    +<?php echo e($product->markup_percentage); ?>%
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            <span class="editable-field" 
                                                  data-field="quantity" 
                                                  data-type="number" 
                                                  data-value="<?php echo e($product->quantity); ?>"
                                                  title="Нажмите для изменения количества">
                                                <span class="admin-badge <?php echo e($product->quantity > 0 ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                                    <?php echo e($product->quantity); ?>

                                                </span>
                                            </span>
                                            <input type="number" class="admin-form-control edit-input" 
                                                   value="<?php echo e($product->quantity); ?>" 
                                                   min="0" step="1"
                                                   style="display: none; font-size: 12px; padding: 4px; width: 60px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-text-center">
                                            <span class="editable-field status-toggle" 
                                                  data-field="is_active" 
                                                  data-type="boolean" 
                                                  data-value="<?php echo e($product->is_active ? 1 : 0); ?>"
                                                  title="Нажмите для изменения статуса"
                                                  style="cursor: pointer;">
                                                <?php if($product->is_active): ?>
                                                    <span class="admin-status-active">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="admin-status-inactive">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" 
                                               class="admin-btn admin-btn-xs" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                                               class="admin-btn admin-btn-xs admin-btn-primary" title="Полное редактирование">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="admin-btn admin-btn-xs admin-btn-danger" 
                                                    onclick="deleteProduct(<?php echo e($product->id); ?>, '<?php echo e($product->name); ?>')" 
                                                    title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <?php if($products->hasPages()): ?>
                    <div class="admin-card-footer">
                        <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                            <div class="admin-text-muted">
                                Показаны записи <?php echo e($products->firstItem()); ?>-<?php echo e($products->lastItem()); ?> 
                                из <?php echo e($products->total()); ?>

                            </div>
                            <div class="admin-pagination">
                                <?php if($products->onFirstPage()): ?>
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e($products->previousPageUrl()); ?>" class="admin-page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php $__currentLoopData = $products->getUrlRange(1, $products->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($page == $products->currentPage()): ?>
                                        <span class="admin-page-link active"><?php echo e($page); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($url); ?>" class="admin-page-link"><?php echo e($page); ?></a>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($products->hasMorePages()): ?>
                                    <a href="<?php echo e($products->nextPageUrl()); ?>" class="admin-page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="admin-page-link disabled">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="admin-empty-state">
                    <div class="admin-empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h6>Товары не найдены</h6>
                    <p class="admin-text-muted">У вас пока нет товаров в этом боте</p>
                    <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus admin-me-2"></i>
                        Добавить первый товар
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Массовые действия -->
    <div class="admin-card admin-mt-4" id="bulk-actions" style="display: none;">
        <div class="admin-card-body">
            <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                <div>
                    <span id="selected-count">0</span> товаров выбрано
                </div>
                <div class="admin-d-flex admin-gap-sm">
                    <button class="admin-btn admin-btn-sm" onclick="bulkActivate()">
                        <i class="fas fa-check admin-me-1"></i>
                        Активировать
                    </button>
                    <button class="admin-btn admin-btn-sm" onclick="bulkDeactivate()">
                        <i class="fas fa-times admin-me-1"></i>
                        Деактивировать
                    </button>
                    <button class="admin-btn admin-btn-sm admin-btn-danger" onclick="bulkDelete()">
                        <i class="fas fa-trash admin-me-1"></i>
                        Удалить
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
                <form method="POST" action="<?php echo e(route('bot.products.import', $telegramBot)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="admin-form-group">
                        <label for="import_file" class="admin-form-label">Выберите файл для импорта</label>
                        <input type="file" class="admin-form-control" id="import_file" name="import_file" 
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
                        <a href="<?php echo e(route('bot.products.download-template', $telegramBot)); ?>" class="admin-btn">
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
        fetch('<?php echo e(route("bot.products.update-field", $telegramBot)); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
        form.action = '<?php echo e(route("bot.products.destroy", [$telegramBot, ":id"])); ?>'.replace(':id', productId);
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/table.blade.php ENDPATH**/ ?>