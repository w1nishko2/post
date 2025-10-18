

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
       
            <div class="card mb-3 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-light py-2">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0 fw-semibold"><?php echo e($telegramBot->bot_name); ?> - Товары</h6>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-primary"><?php echo e($products->total()); ?> товаров</span>
                        </div>
                    </div>
                </div>
                
                <!-- Панель управления и фильтрации -->
                <div class="card-body p-0">
                    <!-- Строка быстрых действий -->
                    <div class="d-flex align-items-center justify-content-between p-3 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center ">
                            <div class="me-4">
                                <h6 class="mb-0 fw-bold">Управление товарами</h6>
                                <small class="opacity-75"><?php echo e($products->total()); ?> товаров найдено</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light btn-sm" onclick="toggleFilters()" id="filterToggle">
                                    <i class="fas fa-filter"></i> Фильтры
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm" onclick="toggleBulkActions()" id="bulkToggle">
                                    <i class="fas fa-tools"></i> Массовые действия
                                </button>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='<?php echo e(route('bot.products.create', $telegramBot)); ?>'">
                                <i class="fas fa-plus"></i> Добавить товар
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="window.location.href='<?php echo e(route('bot.products.export-data', $telegramBot)); ?>'">
                                <i class="fas fa-download"></i> Экспорт
                            </button>
                        </div>
                    </div>

                    <!-- Компактные фильтры -->
                    <div id="filtersPanel" class="filters-panel" style="display: none;">
                        <form method="GET" action="<?php echo e(route('bot.products.table', $telegramBot)); ?>" id="filtersForm" class="filters-form">
                            <!-- Поиск -->
                            <div class="search-box">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="🔍 Поиск..."
                                       class="search-input">
                                <button type="button" class="clear-btn" style="display: none;">×</button>
                            </div>

                            <!-- Категории -->
                            <div class="categories-section">
                                <input type="hidden" name="category_id" id="category_id" value="<?php echo e(request('category_id')); ?>">
                                <div class="chips">
                                    <span class="chip <?php echo e(!request('category_id') ? 'active' : ''); ?>" onclick="selectCategory('', this)">Все</span>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="chip <?php echo e(request('category_id') == $category->id ? 'active' : ''); ?>" 
                                              onclick="selectCategory('<?php echo e($category->id); ?>', this)"><?php echo e($category->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <!-- Статус и сортировка -->
                            <div class="controls-section">
                                <select name="status" id="status" class="mini-select">
                                    <option value="">Все</option>
                                    <option value="1" <?php echo e(request('status') === '1' ? 'selected' : ''); ?>>✅ Активные</option>
                                    <option value="0" <?php echo e(request('status') === '0' ? 'selected' : ''); ?>>❌ Неактивные</option>
                                </select>
                                
                                <select name="sort_by" id="sort_by" class="mini-select">
                                    <option value="id" <?php echo e(request('sort_by', 'id') == 'id' ? 'selected' : ''); ?>>ID</option>
                                    <option value="name" <?php echo e(request('sort_by') == 'name' ? 'selected' : ''); ?>>Название</option>
                                    <option value="price" <?php echo e(request('sort_by') == 'price' ? 'selected' : ''); ?>>Цена</option>
                                    <option value="quantity" <?php echo e(request('sort_by') == 'quantity' ? 'selected' : ''); ?>>Кол-во</option>
                                </select>
                                
                                <button type="button" onclick="toggleSortDirection()" class="sort-btn">
                                    <i class="fas fa-arrow-<?php echo e(request('sort_direction', 'desc') == 'desc' ? 'down' : 'up'); ?>"></i>
                                </button>
                                <input type="hidden" name="sort_direction" value="<?php echo e(request('sort_direction', 'desc')); ?>" id="sort_direction">
                                
                                <button type="submit" class="apply-btn">Применить</button>
                                <button type="button" onclick="resetFilters()" class="reset-btn">Сброс</button>
                            </div>
                        </form>
                    </div>

                    <!-- Компактные массовые действия -->
                    <div id="bulk-panel" class="bulk-panel" style="display: none;">
                        <div class="bulk-content">
                            <div class="bulk-title">⚡ Массовые операции (<span id="selected-count">0</span> выбрано)</div>
                            <div class="bulk-controls">
                                <div class="markup-group">
                                    <input type="number" id="markup-value" step="0.01" min="0" max="1000" placeholder="10%" class="markup-input">
                                    <button onclick="applyMarkup()" class="markup-btn">Наценка</button>
                                </div>
                                
                                <label class="checkbox-label">
                                    <input type="checkbox" id="markup-percentage" checked>
                                    <span>%</span>
                                </label>
                                
                                <button onclick="bulkChangeStatus('active')" class="action-btn activate">ON</button>
                                <button onclick="bulkChangeStatus('inactive')" class="action-btn deactivate">OFF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таблица товаров -->
            <div class="card shadow-sm" style="border-radius: 12px;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 80px;">Фото</th>
                                <th>Название</th>
                                <th style="width: 120px;">Артикул</th>
                                <th style="width: 150px;">Категория</th>
                                <th style="width: 100px;">Цена</th>
                                <th style="width: 80px;">Наценка</th>
                                <th style="width: 100px;">Итого</th>
                                <th style="width: 80px;">Количество</th>
                                <th style="width: 100px;">Статус</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_products[]" value="<?php echo e($product->id); ?>" class="form-check-input">
                                    </td>
                                    <td class="fw-bold text-primary"><?php echo e($product->id); ?></td>
                                    <td>
                                        <?php if($product->photo_url): ?>
                                            <img src="<?php echo e($product->photo_url); ?>" alt="<?php echo e($product->name); ?>" 
                                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="name" data-id="<?php echo e($product->id); ?>">
                                            <?php echo e($product->name); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="article" data-id="<?php echo e($product->id); ?>">
                                            <?php echo e($product->article ?? '-'); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($product->category->name ?? 'Без категории'); ?></span>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="price" data-id="<?php echo e($product->id); ?>">
                                            <?php echo e(number_format($product->price, 2)); ?> ₽
                                        </div>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="markup_percentage" data-id="<?php echo e($product->id); ?>">
                                            <?php echo e($product->markup_percentage ?? 0); ?>%
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success" id="total-price-<?php echo e($product->id); ?>">
                                            <?php echo e(number_format($product->price * (1 + ($product->markup_percentage ?? 0) / 100), 2)); ?> ₽
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="quantity" data-id="<?php echo e($product->id); ?>">
                                            <?php echo e($product->quantity); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <div class="editable-field" data-field="is_active" data-id="<?php echo e($product->id); ?>">
                                            <?php if($product->is_active): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Неактивен</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" 
                                               class="btn btn-outline-info btn-sm" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                                               class="btn btn-outline-primary btn-sm" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p>Товары не найдены</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($products->hasPages()): ?>
                    <div class="card-footer bg-light">
                        <?php echo e($products->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование товара</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Название</label>
                        <input type="text" class="form-control" id="editName">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
<script>
// Компактный скрипт для нового интерфейса
document.addEventListener('DOMContentLoaded', function() {
    // Поиск с автоматической очисткой
    const searchInput = document.getElementById('search');
    const clearBtn = document.querySelector('.clear-btn');
    
    if (searchInput && clearBtn) {
        searchInput.addEventListener('input', function() {
            clearBtn.style.display = this.value ? 'flex' : 'none';
        });
        
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            this.style.display = 'none';
        });
        
        // Показать кнопку очистки если есть значение
        if (searchInput.value) {
            clearBtn.style.display = 'flex';
        }
    }
    
    // Выбор категорий через чипы
    window.selectCategory = function(id, element) {
        const input = document.getElementById('category_id');
        const chips = document.querySelectorAll('.chip');
        
        if (input.value === id.toString()) {
            input.value = '';
            element.classList.remove('active');
        } else {
            input.value = id;
            chips.forEach(chip => chip.classList.remove('active'));
            element.classList.add('active');
        }
    };
    
    // Переключение направления сортировки
    window.toggleSortDirection = function() {
        const dirSelect = document.getElementById('sort_direction');
        const btn = document.querySelector('.sort-btn');
        
        if (dirSelect.value === 'asc') {
            dirSelect.value = 'desc';
            btn.innerHTML = '<i class="fas fa-arrow-down"></i>';
        } else {
            dirSelect.value = 'asc';
            btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        }
    };
    
    // Сброс фильтров
    window.resetFilters = function() {
        document.getElementById('search').value = '';
        document.getElementById('category_id').value = '';
        document.getElementById('status').value = '';
        document.getElementById('sort_by').value = 'id';
        document.getElementById('sort_direction').value = 'desc';
        
        document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));
        if (clearBtn) clearBtn.style.display = 'none';
        
        document.getElementById('filtersForm').submit();
    };
    
    // Управление массовыми операциями
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('input[name="selected_products[]"]');
    const bulkPanel = document.getElementById('bulk-panel');
    const selectedCountSpan = document.getElementById('selected-count');
    
    function updateBulkPanel() {
        const selectedCount = document.querySelectorAll('input[name="selected_products[]"]:checked').length;
        if (selectedCountSpan) selectedCountSpan.textContent = selectedCount;
        
        if (bulkPanel) {
            if (selectedCount > 0) {
                bulkPanel.style.display = 'block';
                bulkPanel.classList.add('animate-fade-in');
            } else {
                bulkPanel.style.display = 'none';
            }
        }
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkPanel();
        });
    }
    
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="selected_products[]"]:checked').length;
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedCount === productCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < productCheckboxes.length;
            }
            updateBulkPanel();
        });
    });
    
    // Применение наценки
    window.applyMarkup = function() {
        const markup = document.getElementById('markup-value').value;
        const isPercentage = document.getElementById('markup-percentage').checked;
        const selected = Array.from(document.querySelectorAll('input[name="selected_products[]"]:checked')).map(cb => cb.value);
        
        if (!markup || selected.length === 0) {
            alert('Введите наценку и выберите товары');
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('markup', markup);
        formData.append('is_percentage', isPercentage ? '1' : '0');
        selected.forEach(id => formData.append('product_ids[]', id));
        
        fetch(`<?php echo e(route('bot.products.bulk-markup', $telegramBot)); ?>`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при выполнении запроса: ' + error.message);
        });
    };
    
    // Массовое изменение статуса
    window.bulkChangeStatus = function(status) {
        const selected = Array.from(document.querySelectorAll('input[name="selected_products[]"]:checked')).map(cb => cb.value);
        
        if (selected.length === 0) {
            alert('Выберите товары');
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('status', status);
        selected.forEach(id => formData.append('product_ids[]', id));
        
        fetch(`<?php echo e(route('bot.products.bulk-status', $telegramBot)); ?>`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при выполнении запроса: ' + error.message);
        });
    };

    // Функция для пересчета итоговой цены
    function updateTotalPrice(productId) {
        const priceElement = document.querySelector(`[data-field="price"][data-id="${productId}"]`);
        const markupElement = document.querySelector(`[data-field="markup_percentage"][data-id="${productId}"]`);
        const totalElement = document.getElementById(`total-price-${productId}`);
        
        if (priceElement && markupElement && totalElement) {
            const price = parseFloat(priceElement.textContent.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
            const markup = parseFloat(markupElement.textContent.replace('%', '')) || 0;
            const total = price * (1 + markup / 100);
            totalElement.textContent = total.toFixed(2) + ' ₽';
        }
    }

    // Редактирование полей
    document.querySelectorAll('.editable-field').forEach(cell => {
        cell.addEventListener('click', function() {
            if (this.classList.contains('editing')) return;
            
            const fieldName = this.dataset.field;
            const productId = this.dataset.id;
            const originalContent = this.innerHTML;
            
            this.classList.add('editing');
            
            let input;
            if (fieldName === 'is_active') {
                input = document.createElement('select');
                input.innerHTML = '<option value="1">Активен</option><option value="0">Неактивен</option>';
                input.value = this.textContent.includes('Активен') ? '1' : '0';
            } else {
                input = document.createElement('input');
                input.type = fieldName === 'price' || fieldName === 'markup_percentage' ? 'number' : 'text';
                if (fieldName === 'price') input.step = '0.01';
                input.value = this.textContent.replace(/[^\d.-]/g, '');
            }
            
            input.style.width = '100%';
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            const saveValue = () => {
                const newValue = input.value;
                this.classList.add('saving');
                
                fetch(`<?php echo e(route('bot.products.update-field', $telegramBot)); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id: this.dataset.id,
                        field: this.dataset.field,
                        value: newValue
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server returned non-JSON response');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (fieldName === 'is_active') {
                            this.innerHTML = newValue == '1' ? 
                                '<span class="badge bg-success">Активен</span>' : 
                                '<span class="badge bg-secondary">Неактивен</span>';
                        } else if (fieldName === 'markup_percentage') {
                            this.innerHTML = newValue + '%';
                            updateTotalPrice(productId);
                        } else if (fieldName === 'price') {
                            this.innerHTML = parseFloat(newValue).toFixed(2) + ' ₽';
                            updateTotalPrice(productId);
                        } else {
                            this.innerHTML = newValue;
                        }
                    } else {
                        this.innerHTML = originalContent;
                        alert('Ошибка сохранения: ' + (data.message || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = originalContent;
                    alert('Произошла ошибка при сохранении: ' + error.message);
                })
                .finally(() => {
                    this.classList.remove('editing', 'saving');
                });
            };
            
            const cancelEdit = () => {
                this.innerHTML = originalContent;
                this.classList.remove('editing');
            };
            
            input.addEventListener('blur', saveValue);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveValue();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelEdit();
                }
            });
        });
    });
    
    // Инициализация
    updateBulkPanel();
    
    // Функции переключения панелей
    window.toggleFilters = function() {
        const panel = document.getElementById('filtersPanel');
        const btn = document.getElementById('filterToggle');
        
        if (!panel || !btn) {
            console.error('Elements filtersPanel or filterToggle not found');
            return;
        }
        
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
            panel.classList.add('animate-fade-in');
            btn.innerHTML = '<i class="fas fa-filter"></i> Скрыть фильтры';
        } else {
            panel.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-filter"></i> Фильтры';
        }
    };

    window.toggleBulkActions = function() {
        const panel = document.getElementById('bulk-panel');
        const btn = document.getElementById('bulkToggle');
        
        if (!panel || !btn) {
            console.error('Elements bulk-panel or bulkToggle not found');
            return;
        }
        
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
            panel.classList.add('animate-fade-in');
            btn.innerHTML = '<i class="fas fa-tools"></i> Скрыть действия';
        } else {
            panel.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-tools"></i> Массовые действия';
        }
    };

    // Инициализация при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        // Показываем фильтры, если есть активные фильтры
        const searchEl = document.getElementById('search');
        const selectedCategoryEl = document.getElementById('selectedCategory');
        const isActiveEl = document.querySelector('[name="is_active"]');

        const hasActiveFilters = (searchEl && searchEl.value) ||
                                (selectedCategoryEl && selectedCategoryEl.value) ||
                                (isActiveEl && isActiveEl.value);

        if (hasActiveFilters) {
            toggleFilters();
        }

        // Автодополнение поиска с задержкой
        if (searchEl) {
            let searchTimeout;
            searchEl.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        updateFilters();
                    }
                }, 500);
            });
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/table.blade.php ENDPATH**/ ?>