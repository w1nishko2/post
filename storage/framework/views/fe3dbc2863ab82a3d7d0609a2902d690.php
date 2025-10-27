

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
            <!-- Адаптивная компоновка для мобильных -->
            <div class="admin-d-flex admin-align-items-center admin-justify-content-between admin-flex-wrap admin-gap-sm">
                <div class="admin-d-flex admin-align-items-center admin-flex-1">
                    <div class="admin-me-3">
                        <div class="admin-bot-avatar <?php echo e($telegramBot->is_active ? '' : 'inactive'); ?>">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div class="admin-flex-1">
                        <h6 class="admin-mb-1"><?php echo e($telegramBot->bot_name); ?></h6>
                        <div class="admin-text-muted">{{ $telegramBot->bot_username }}</div>
                    </div>
                </div>
                <div class="admin-d-flex admin-gap-sm admin-flex-wrap">
                    <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn admin-btn-sm">
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
    <?php endif; ?>

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
                        <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <button class="admin-btn admin-btn-sm" onclick="toggleHelpPanel()" title="Помощь по редактированию">
                            <i class="fas fa-question-circle admin-me-1"></i>
                            <span class="admin-d-none-xs">Помощь</span>
                        </button>
                        <a href="<?php echo e(route('bot.products.create', $telegramBot)); ?>" 
                           class="admin-btn admin-btn-primary admin-btn-sm">
                            <i class="fas fa-plus admin-me-1"></i>
                            <span class="admin-d-none-xs">Добавить</span>
                        </a>
                        <a href="<?php echo e(route('bot.products.export-data', $telegramBot)); ?>" 
                           class="admin-btn admin-btn-success admin-btn-sm">
                            <i class="fas fa-download admin-me-1"></i>
                            <span class="admin-d-none-xs">Экспорт</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Панель помощи (скрыта по умолчанию) -->
    <div class="admin-card admin-mb-4" id="help-panel" style="display: none;">
        <div class="admin-card-header" style="background-color: #f0f9ff;">
            <h6 class="admin-mb-0" style="color: #0369a1;">
                <i class="fas fa-lightbulb admin-me-2"></i>
                Быстрое редактирование в таблице
            </h6>
        </div>
        <div class="admin-card-body">
            <div class="admin-row">
                <div class="admin-col-md-6">
                    <h6><i class="fas fa-mouse-pointer admin-me-2"></i>Редактируемые поля:</h6>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li><strong>Название товара</strong> - кликните для изменения</li>
                        <li><strong>Описание</strong> - многострочное поле (Ctrl+Enter для сохранения)</li>
                        <li><strong>Артикул</strong> - уникальный код товара</li>
                        <li><strong>Категория</strong> - выбор из списка</li>
                        <li><strong>Цена</strong> - в рублях</li>
                        <li><strong>Наценка %</strong> - процент наценки</li>
                        <li><strong>Количество</strong> - остаток на складе</li>
                        <li><strong>Статус</strong> - активен/неактивен (одним кликом)</li>
                    </ul>
                </div>
                <div class="admin-col-md-6">
                    <h6><i class="fas fa-keyboard admin-me-2"></i>Горячие клавиши:</h6>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li><kbd>Enter</kbd> - сохранить изменения (обычные поля)</li>
                        <li><kbd>Ctrl+Enter</kbd> - сохранить (многострочные поля)</li>
                        <li><kbd>Escape</kbd> - отменить редактирование</li>
                        <li><strong>Blur</strong> (клик вне поля) - автосохранение</li>
                    </ul>
                    <div class="admin-alert admin-alert-info admin-mt-3" style="margin: 0;">
                        <i class="fas fa-info-circle admin-me-2"></i>
                        Изменения сохраняются мгновенно через AJAX без перезагрузки страницы!
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
                    <span>(<?php echo e($products->total() ?? 0); ?>)</span>
                </h5>
                <div class="admin-d-flex admin-align-items-center admin-gap-sm">
                    <div class="admin-text-muted admin-d-none-xs" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle admin-me-1"></i>
                        Кликните по полям для редактирования
                    </div>
                    <span class="admin-text-muted admin-d-none-xs">Показать:</span>
                    <select class="admin-form-control admin-select" id="per-page" 
                            style="width: auto; min-width: 60px; padding: 4px 8px;">
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
                                <th style="width: 30px; padding: 8px 4px;">
                                    <input type="checkbox" class="admin-form-check-input" id="select-all">
                                </th>
                                <th style="width: 50px; padding: 8px 4px;">Фото</th>
                                <th style="min-width: 150px; padding: 8px;">Товар</th>
                                <th style="min-width: 120px; padding: 8px;">Описание</th>
                                <th style="width: 90px; padding: 8px;">Артикул</th>
                                <th style="width: 120px; padding: 8px;">Категория</th>
                                <th style="width: 90px; padding: 8px;">Цена</th>
                                <th style="width: 70px; padding: 8px; text-align: center;">%</th>
                                <th style="width: 60px; padding: 8px; text-align: center;">Кол.</th>
                                <th style="width: 60px; padding: 8px; text-align: center;">Статус</th>
                                <th style="width: 100px; padding: 8px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-product-id="<?php echo e($product->id); ?>" style="font-size: 13px;">
                                    <td style="padding: 6px 4px;">
                                        <input type="checkbox" class="admin-form-check-input product-checkbox" 
                                               value="<?php echo e($product->id); ?>">
                                    </td>
                                    <td style="padding: 4px;">
                                        <?php if($product->main_photo_url): ?>
                                            <div class="admin-product-photo" style="width: 40px; height: 40px;">
                                                <img src="<?php echo e($product->main_photo_url); ?>" alt="<?php echo e($product->name); ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="admin-product-photo admin-no-photo" style="width: 40px; height: 40px; font-size: 16px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 6px 8px;">
                                        <div class="admin-product-info">
                                            <div class="admin-product-name" style="font-size: 13px; line-height: 1.3;">
                                                <span class="editable-field" 
                                                      data-field="name" 
                                                      data-type="text" 
                                                      data-value="<?php echo e($product->name); ?>"
                                                      title="Нажмите для редактирования">
                                                    <?php echo e(Str::limit($product->name, 40)); ?>

                                                </span>
                                                <input type="text" class="admin-form-control edit-input" 
                                                       value="<?php echo e($product->name); ?>" 
                                                       style="display: none; font-size: 12px; padding: 4px 6px; width: 100%;">
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 6px 8px;">
                                        <div class="admin-product-desc" style="font-size: 12px; line-height: 1.3;">
                                            <span class="editable-field" 
                                                  data-field="description" 
                                                  data-type="textarea" 
                                                  data-value="<?php echo e($product->description ?? ''); ?>"
                                                  title="Нажмите для редактирования описания"
                                                  style="cursor: pointer; display: block;">
                                                <?php if($product->description): ?>
                                                    <?php echo e(Str::limit($product->description, 40)); ?>

                                                <?php else: ?>
                                                    <span class="admin-text-muted">—</span>
                                                <?php endif; ?>
                                            </span>
                                            <textarea class="admin-form-control edit-input" 
                                                      rows="2"
                                                      style="display: none; font-size: 11px; padding: 4px 6px; width: 100%; resize: vertical;"><?php echo e($product->description); ?></textarea>
                                        </div>
                                    </td>
                                    <td style="padding: 6px 8px;">
                                        <span class="editable-field" 
                                              data-field="article" 
                                              data-type="text" 
                                              data-value="<?php echo e($product->article ?? ''); ?>"
                                              title="Нажмите для редактирования артикула"
                                              style="cursor: pointer; font-size: 12px;">
                                            <?php if($product->article): ?>
                                                <span class="admin-text-mono"><?php echo e(Str::limit($product->article, 15)); ?></span>
                                            <?php else: ?>
                                                <span class="admin-text-muted">—</span>
                                            <?php endif; ?>
                                        </span>
                                        <input type="text" class="admin-form-control edit-input" 
                                               value="<?php echo e($product->article); ?>" 
                                               placeholder="Артикул"
                                               style="display: none; font-size: 11px; padding: 3px 5px; width: 100%;">
                                    </td>
                                    <td style="padding: 6px 8px;">
                                        <span class="editable-field" 
                                              data-field="category_id" 
                                              data-type="select" 
                                              data-value="<?php echo e($product->category_id); ?>"
                                              title="Нажмите для изменения категории"
                                              style="font-size: 12px;">
                                            <?php if($product->category): ?>
                                                <span class="admin-badge" style="font-size: 11px; padding: 2px 6px;"><?php echo e(Str::limit($product->category->name, 20)); ?></span>
                                            <?php else: ?>
                                                <span class="admin-text-muted" style="font-size: 11px;">Без категории</span>
                                            <?php endif; ?>
                                        </span>
                                        <select class="admin-form-control edit-input" style="display: none; font-size: 11px; padding: 3px 5px;">
                                            <option value="">Без категории</option>
                                            <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($category->id); ?>" 
                                                        <?php echo e($product->category_id == $category->id ? 'selected' : ''); ?>>
                                                    <?php echo e($category->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td style="padding: 6px 8px;">
                                        <div class="admin-product-price" style="font-size: 13px; font-weight: 500;">
                                            <span class="editable-field" 
                                                  data-field="price" 
                                                  data-type="number" 
                                                  data-value="<?php echo e($product->price); ?>"
                                                  title="Нажмите для изменения цены"
                                                  style="cursor: pointer;">
                                                <?php echo e(number_format($product->price, 0, ',', ' ')); ?>₽
                                            </span>
                                            <input type="number" class="admin-form-control edit-input" 
                                                   value="<?php echo e($product->price); ?>" 
                                                   min="0" step="0.01"
                                                   style="display: none; font-size: 11px; padding: 3px 5px; width: 100%;">
                                        </div>
                                    </td>
                                    <td style="padding: 6px 4px; text-align: center;">
                                        <span class="editable-field" 
                                              data-field="markup_percentage" 
                                              data-type="number" 
                                              data-value="<?php echo e($product->markup_percentage ?? 0); ?>"
                                              title="Нажмите для изменения наценки"
                                              style="cursor: pointer;">
                                            <?php if($product->markup_percentage > 0): ?>
                                                <span class="admin-badge admin-badge-info" style="font-size: 10px; padding: 2px 5px;">+<?php echo e($product->markup_percentage); ?>%</span>
                                            <?php else: ?>
                                                <span class="admin-text-muted" style="font-size: 11px;">—</span>
                                            <?php endif; ?>
                                        </span>
                                        <input type="number" class="admin-form-control edit-input" 
                                               value="<?php echo e($product->markup_percentage ?? 0); ?>" 
                                               min="0" step="0.01" max="1000"
                                               placeholder="0"
                                               style="display: none; font-size: 11px; padding: 3px 5px; width: 60px;">
                                    </td>
                                    <td style="padding: 6px 4px; text-align: center;">
                                        <span class="editable-field" 
                                              data-field="quantity" 
                                              data-type="number" 
                                              data-value="<?php echo e($product->quantity); ?>"
                                              title="Нажмите для изменения количества">
                                            <span class="admin-badge <?php echo e($product->quantity > 0 ? 'admin-badge-success' : 'admin-badge-danger'); ?>" style="font-size: 11px; padding: 2px 6px;">
                                                <?php echo e($product->quantity); ?>

                                            </span>
                                        </span>
                                        <input type="number" class="admin-form-control edit-input" 
                                               value="<?php echo e($product->quantity); ?>" 
                                               min="0" step="1"
                                               style="display: none; font-size: 11px; padding: 3px 5px; width: 50px;">
                                    </td>
                                    <td style="padding: 6px 4px; text-align: center;">
                                        <span class="editable-field status-toggle" 
                                              data-field="is_active" 
                                              data-type="boolean" 
                                              data-value="<?php echo e($product->is_active ? 1 : 0); ?>"
                                              title="Нажмите для изменения статуса"
                                              style="cursor: pointer; font-size: 16px;">
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
                                    </td>
                                    <td style="padding: 4px;">
                                        <div class="admin-table-actions" style="gap: 2px;">
                                            <a href="<?php echo e(route('bot.products.show', [$telegramBot, $product])); ?>" 
                                               class="admin-btn admin-btn-xs" title="Просмотр" style="padding: 4px 6px; font-size: 11px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('bot.products.edit', [$telegramBot, $product])); ?>" 
                                               class="admin-btn admin-btn-xs admin-btn-primary" title="Полное редактирование" style="padding: 4px 6px; font-size: 11px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="admin-btn admin-btn-xs admin-btn-danger" 
                                                    onclick="deleteProduct(<?php echo e($product->id); ?>, '<?php echo e($product->name); ?>')" 
                                                    title="Удалить" style="padding: 4px 6px; font-size: 11px;">
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
                </div>
            <?php endif; ?>
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
            input.style.display = type === 'textarea' ? 'block' : 'inline-block';
            
            if (type === 'select') {
                input.value = currentValue;
            } else if (type === 'number') {
                input.value = currentValue;
            } else if (type === 'textarea') {
                input.value = currentValue;
            } else {
                input.value = currentValue;
            }
            
            input.focus();
            
            // Для textarea - выделяем весь текст в конце
            if (type === 'textarea') {
                input.selectionStart = input.selectionEnd = input.value.length;
            }
            
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
                element.style.display = type === 'textarea' ? 'block' : 'inline';
            };
            
            // Сохранение при потере фокуса
            input.addEventListener('blur', saveEdit, { once: true });
            
            // Обработка клавиш
            input.addEventListener('keydown', function(e) {
                if (type === 'textarea') {
                    // Для textarea: Ctrl+Enter сохраняет, Escape отменяет
                    if (e.key === 'Enter' && e.ctrlKey) {
                        e.preventDefault();
                        saveEdit();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelEdit(element, input);
                    }
                } else {
                    // Для обычных полей: Enter сохраняет, Escape отменяет
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveEdit();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelEdit(element, input);
                    }
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
        const fieldType = element.dataset.type;
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
                    element.style.display = fieldType === 'textarea' ? 'block' : 'inline';
                }
                
                // Показываем уведомление об успехе
                showNotification('Поле успешно обновлено', 'success');
            } else {
                // Восстанавливаем оригинальное значение
                element.innerHTML = originalHtml;
                if (input) {
                    input.style.display = 'none';
                    element.style.display = fieldType === 'textarea' ? 'block' : 'inline';
                }
                showNotification(data.message || 'Ошибка при обновлении', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            element.innerHTML = originalHtml;
            if (input) {
                input.style.display = 'none';
                element.style.display = fieldType === 'textarea' ? 'block' : 'inline';
            }
            showNotification('Произошла ошибка при обновлении', 'error');
        });
    }

    function updateFieldDisplay(element, field, value, formattedValue) {
        switch (field) {
            case 'name':
                element.textContent = value;
                break;
                
            case 'description':
                if (value && value.trim()) {
                    // Обрезаем длинный текст
                    const displayText = value.length > 60 ? value.substring(0, 60) + '...' : value;
                    element.textContent = displayText;
                } else {
                    element.innerHTML = '<span class="admin-text-muted">Нет описания</span>';
                }
                break;
                
            case 'article':
                if (value && value.trim()) {
                    element.innerHTML = `<span class="admin-text-mono">${value}</span>`;
                } else {
                    element.innerHTML = '<span class="admin-text-muted">—</span>';
                }
                break;
                
            case 'price':
                element.innerHTML = formattedValue || (new Intl.NumberFormat('ru-RU').format(value) + ' ₽');
                break;
                
            case 'markup_percentage':
                if (value > 0) {
                    element.innerHTML = `<span class="admin-badge admin-badge-info">+${value}%</span>`;
                } else {
                    element.innerHTML = '<span class="admin-text-muted">—</span>';
                }
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

    // Показываем подсказку при первом посещении
    if (!localStorage.getItem('table_edit_hint_shown')) {
        setTimeout(() => {
            showNotification('💡 Совет: Кликайте по полям в таблице для быстрого редактирования. Нажмите Enter для сохранения, Escape для отмены.', 'info');
            localStorage.setItem('table_edit_hint_shown', 'true');
        }, 1000);
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

// Панель помощи
function toggleHelpPanel() {
    const helpPanel = document.getElementById('help-panel');
    if (helpPanel.style.display === 'none') {
        helpPanel.style.display = 'block';
        // Плавная прокрутка к панели
        helpPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        helpPanel.style.display = 'none';
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/table.blade.php ENDPATH**/ ?>