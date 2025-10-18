

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Навигационная панель -->
            <div class="card mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill" style="padding: 0.5rem;">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-robot me-2"></i>Мои боты
                        </a>
                        <a class="nav-link active" href="<?php echo e(route('products.select-bot')); ?>"
                           style="border-radius: 12px; font-weight: 600; padding: 1rem 1.5rem; margin: 0.25rem; transition: all 0.3s ease;">
                            <i class="fas fa-boxes me-2"></i>Мои товары
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Информация о боте -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><?php echo e($telegramBot->bot_name); ?></h5>
                            <small class="text-muted">{{ $telegramBot->bot_username }}</small>
                        </div>
                        <div>
                            <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-th-large"></i> Карточки
                            </a>
                            <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Добавить товар
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таблица товаров -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>Товары - табличный редактор
                            </h5>
                            <small class="text-muted">Нажмите на ячейку для редактирования. Изменения сохраняются автоматически.</small>
                        </div>
                        <div class="text-muted">
                            Всего: <?php echo e($products->total()); ?> товаров
                        </div>
                    </div>
                </div>

                <!-- Фильтры и поиск -->
                <div class="card-body border-bottom">
                    <form method="GET" action="<?php echo e(route('bot.products.table', $telegramBot)); ?>" class="row g-3" accept-charset="UTF-8">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Поиск</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="Название, артикул, описание, категория, цена, ID..."
                                       autocomplete="off">
                            </div>
                            <small class="text-muted">Поиск по всем полям товара включая статус (активен/неактивен)</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Все категории</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" 
                                            <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="is_active" class="form-label">Статус</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="">Все статусы</option>
                                <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Активные</option>
                                <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Неактивные</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Сортировка</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="id" <?php echo e(request('sort_by', 'id') === 'id' ? 'selected' : ''); ?>>По ID</option>
                                <option value="name" <?php echo e(request('sort_by') === 'name' ? 'selected' : ''); ?>>По названию</option>
                                <option value="price" <?php echo e(request('sort_by') === 'price' ? 'selected' : ''); ?>>По цене</option>
                                <option value="quantity" <?php echo e(request('sort_by') === 'quantity' ? 'selected' : ''); ?>>По количеству</option>
                                <option value="created_at" <?php echo e(request('sort_by') === 'created_at' ? 'selected' : ''); ?>>По дате</option>
                            </select>
                        </div>
                        
                        <div class="col-md-1">
                            <label for="sort_direction" class="form-label">Порядок</label>
                            <select class="form-select" id="sort_direction" name="sort_direction">
                                <option value="desc" <?php echo e(request('sort_direction', 'desc') === 'desc' ? 'selected' : ''); ?>>↓</option>
                                <option value="asc" <?php echo e(request('sort_direction') === 'asc' ? 'selected' : ''); ?>>↑</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Применить фильтры
                            </button>
                            <a href="<?php echo e(route('bot.products.table', $telegramBot)); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Сбросить
                            </a>
                            <a href="<?php echo e(route('bot.products.export-data', $telegramBot)); ?>" class="btn btn-success ms-2">
                                <i class="fas fa-file-excel"></i> Экспорт базы
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <?php if($products->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="productsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">
                                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])); ?>" 
                                               class=" text-decoration-none">
                                                ID
                                                <?php if(request('sort_by', 'id') === 'id'): ?>
                                                    <i class="fas fa-sort-<?php echo e(request('sort_direction', 'desc') === 'asc' ? 'up' : 'down'); ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="20%">
                                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])); ?>" 
                                               class=" text-decoration-none">
                                                Название
                                                <?php if(request('sort_by') === 'name'): ?>
                                                    <i class="fas fa-sort-<?php echo e(request('sort_direction', 'desc') === 'asc' ? 'up' : 'down'); ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="10%">Артикул</th>
                                        <th width="15%">Категория</th>
                                        <th width="10%">
                                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])); ?>" 
                                               class=" text-decoration-none">
                                                Цена
                                                <?php if(request('sort_by') === 'price'): ?>
                                                    <i class="fas fa-sort-<?php echo e(request('sort_direction', 'desc') === 'asc' ? 'up' : 'down'); ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="8%">
                                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort_by' => 'quantity', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])); ?>" 
                                               class=" text-decoration-none">
                                                Количество
                                                <?php if(request('sort_by') === 'quantity'): ?>
                                                    <i class="fas fa-sort-<?php echo e(request('sort_direction', 'desc') === 'asc' ? 'up' : 'down'); ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="8%">Статус</th>
                                        <th width="20%">Описание</th>
                                        <th width="4%">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-product-id="<?php echo e($product->id); ?>">
                                            <td class="text-center"><?php echo e($product->id); ?></td>
                                            
                                            <!-- Название -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="name" 
                                                     data-type="text"
                                                     data-value="<?php echo e($product->name); ?>">
                                                    <?php echo e($product->name); ?>

                                                </div>
                                            </td>

                                            <!-- Артикул -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="article" 
                                                     data-type="text"
                                                     data-value="<?php echo e($product->article); ?>">
                                                    <?php echo e($product->article ?: '-'); ?>

                                                </div>
                                            </td>

                                            <!-- Категория -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="category_id" 
                                                     data-type="select"
                                                     data-value="<?php echo e($product->category_id); ?>"
                                                     data-options="<?php echo e(json_encode($categories->map(function($cat) { return ['value' => $cat->id, 'text' => $cat->name]; }))); ?>">
                                                    <?php echo e($product->category ? $product->category->name : 'Без категории'); ?>

                                                </div>
                                            </td>

                                            <!-- Цена -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="price" 
                                                     data-type="number"
                                                     data-value="<?php echo e($product->price); ?>">
                                                    <?php echo e(number_format($product->price, 2)); ?> ₽
                                                </div>
                                            </td>

                                            <!-- Количество -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="quantity" 
                                                     data-type="number"
                                                     data-value="<?php echo e($product->quantity); ?>">
                                                    <?php echo e($product->quantity); ?>

                                                </div>
                                            </td>

                                            <!-- Статус -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="is_active" 
                                                     data-type="boolean"
                                                     data-value="<?php echo e($product->is_active ? '1' : '0'); ?>">
                                                    <span class="badge <?php echo e($product->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                                        <?php echo e($product->is_active ? 'Активен' : 'Неактивен'); ?>

                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Описание -->
                                            <td>
                                                <div class="editable-field" 
                                                     data-field="description" 
                                                     data-type="textarea"
                                                     data-value="<?php echo e($product->description); ?>">
                                                    <?php echo e(Str::limit($product->description, 50)); ?>

                                                </div>
                                            </td>

                                            <!-- Действия -->
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" 
                                                       class="btn btn-outline-info btn-sm" title="Просмотр">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                                                       class="btn btn-outline-primary btn-sm" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="<?php echo e(route('bot.products.destroy', [$telegramBot, $product])); ?>" 
                                                          class="d-inline" onsubmit="return confirmDelete('<?php echo e($product->name); ?>')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Пагинация -->
                        <?php if($products->hasPages()): ?>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        Показано <?php echo e($products->firstItem()); ?>-<?php echo e($products->lastItem()); ?> из <?php echo e($products->total()); ?> записей
                                    </div>
                                    <div>
                                        <?php echo e($products->links()); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <?php if(request('search') || request('category_id') || request()->has('is_active')): ?>
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Товары не найдены</h5>
                                <p class="text-muted mb-4">По вашему запросу ничего не найдено. Попробуйте изменить параметры поиска.</p>
                                <a href="<?php echo e(route('bot.products.table', $telegramBot)); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-times"></i> Сбросить фильтры
                                </a>
                            <?php else: ?>
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Товары не найдены</h5>
                                <p class="text-muted mb-4">У этого бота пока нет товаров</p>
                                <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Добавить первый товар
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Содержимое будет заполнено JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.editable-field {
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
    min-height: 20px;
}

.editable-field:hover {
    background-color: #f8f9fa;
}

.editable-field.editing {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
}

.saving {
    opacity: 0.7;
    pointer-events: none;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentEditingElement = null;
    let originalValue = null;
    const botId = <?php echo e($telegramBot->id); ?>;
    
    // Функция для отправки формы с очисткой пустых параметров
    function submitFormWithCleanParams(form) {
        // Временно удаляем пустые параметры
        const inputs = form.querySelectorAll('input, select');
        const emptyInputs = [];
        
        inputs.forEach(input => {
            if (input.value === '' && (input.name === 'category_id' || input.name === 'is_active')) {
                emptyInputs.push({
                    input: input,
                    name: input.name
                });
                input.removeAttribute('name');
            }
        });
        
        // Отправляем форму
        form.submit();
        
        // Восстанавливаем имена после отправки
        setTimeout(() => {
            emptyInputs.forEach(item => {
                item.input.setAttribute('name', item.name);
            });
        }, 100);
    }

    // Автоматический поиск с задержкой
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    if (searchInput) {
        // Убеждаемся, что форма отправляется с правильной кодировкой
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Проверяем, что значение не пустое после trim
                const searchValue = this.value.trim();
                console.log('Search value:', searchValue);
                console.log('Search value encoded:', encodeURIComponent(searchValue));
                submitFormWithCleanParams(this.closest('form'));
            }, 500); // Задержка 500мс для избежания частых запросов
        });
        
        // Также обрабатываем Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                submitFormWithCleanParams(this.closest('form'));
            }
        });
    }
    
    // Автоотправка формы при изменении селектов
    document.querySelectorAll('#category_id, #is_active, #sort_by, #sort_direction').forEach(select => {
        select.addEventListener('change', function() {
            submitFormWithCleanParams(this.closest('form'));
        });
    });

    // Обработчик для кнопки "Применить фильтры"
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            submitFormWithCleanParams(this.closest('form'));
        });
    }

    // Обработчик клика по редактируемым полям
    document.querySelectorAll('.editable-field').forEach(field => {
        field.addEventListener('click', function() {
            if (currentEditingElement) return; // Если уже редактируем другое поле
            
            startEditing(this);
        });
    });

    function startEditing(element) {
        currentEditingElement = element;
        originalValue = element.dataset.value;
        
        const field = element.dataset.field;
        const type = element.dataset.type;
        const value = element.dataset.value;
        
        element.classList.add('editing');
        
        let input;
        
        switch (type) {
            case 'text':
            case 'number':
                input = document.createElement('input');
                input.type = type;
                input.value = value;
                input.className = 'form-control form-control-sm';
                if (type === 'number') {
                    input.step = field === 'price' ? '0.01' : '1';
                    input.min = '0';
                }
                break;
                
            case 'textarea':
                input = document.createElement('textarea');
                input.value = value;
                input.className = 'form-control form-control-sm';
                input.rows = 2;
                break;
                
            case 'select':
                input = document.createElement('select');
                input.className = 'form-select form-select-sm';
                
                // Добавляем опцию "Без категории"
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Без категории';
                input.appendChild(emptyOption);
                
                // Добавляем категории
                const options = JSON.parse(element.dataset.options);
                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value == value) {
                        opt.selected = true;
                    }
                    input.appendChild(opt);
                });
                break;
                
            case 'boolean':
                input = document.createElement('select');
                input.className = 'form-select form-select-sm';
                
                const activeOption = document.createElement('option');
                activeOption.value = '1';
                activeOption.textContent = 'Активен';
                if (value === '1') activeOption.selected = true;
                
                const inactiveOption = document.createElement('option');
                inactiveOption.value = '0';
                inactiveOption.textContent = 'Неактивен';
                if (value === '0') inactiveOption.selected = true;
                
                input.appendChild(activeOption);
                input.appendChild(inactiveOption);
                break;
        }
        
        element.innerHTML = '';
        element.appendChild(input);
        input.focus();
        
        // Обработчики для сохранения/отмены
        input.addEventListener('blur', function() {
            saveField(element, input.value);
        });
        
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                saveField(element, input.value);
            } else if (e.key === 'Escape') {
                cancelEditing(element);
            }
        });
    }

    function saveField(element, newValue) {
        if (!currentEditingElement) return;
        
        const field = element.dataset.field;
        const productId = element.closest('tr').dataset.productId;
        
        // Проверяем, изменилось ли значение
        if (newValue === originalValue) {
            cancelEditing(element);
            return;
        }
        
        element.classList.add('saving');
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append(field, newValue);
        
        fetch(`/bots/${botId}/products/${productId}/quick-update`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем значение и отображение
                element.dataset.value = newValue;
                updateFieldDisplay(element, newValue, data.product);
                showNotification('success', data.message);
            } else {
                throw new Error(data.message || 'Ошибка при сохранении');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Ошибка при сохранении: ' + error.message);
            cancelEditing(element);
        })
        .finally(() => {
            element.classList.remove('saving', 'editing');
            currentEditingElement = null;
            originalValue = null;
        });
    }

    function cancelEditing(element) {
        updateFieldDisplay(element, originalValue);
        element.classList.remove('editing');
        currentEditingElement = null;
        originalValue = null;
    }

    function updateFieldDisplay(element, value, product = null) {
        const field = element.dataset.field;
        const type = element.dataset.type;
        
        let displayValue = value;
        
        switch (field) {
            case 'price':
                displayValue = parseFloat(value).toFixed(2) + ' ₽';
                break;
            case 'category_id':
                if (product && product.category) {
                    displayValue = product.category.name;
                } else {
                    displayValue = 'Без категории';
                }
                break;
            case 'is_active':
                const isActive = value === '1' || value === true;
                displayValue = `<span class="badge ${isActive ? 'bg-success' : 'bg-secondary'}">
                    ${isActive ? 'Активен' : 'Неактивен'}
                </span>`;
                break;
            case 'description':
                displayValue = value.length > 50 ? value.substring(0, 50) + '...' : value;
                break;
            case 'article':
                displayValue = value || '-';
                break;
        }
        
        element.innerHTML = displayValue;
    }

    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Автоматически скрыть через 3 секунды
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 3000);
    }

    // Функция подтверждения удаления
    window.confirmDelete = function(productName) {
        return confirm(`Вы уверены, что хотите удалить товар "${productName}"?\n\nЭто действие необратимо!`);
    };
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/table.blade.php ENDPATH**/ ?>