

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php if($errors->any()): ?>
        <div class="admin-alert admin-alert-danger">
            <i class="fas fa-exclamation-triangle admin-me-2"></i>
            <strong>Пожалуйста, исправьте ошибки:</strong>
            <ul class="admin-mb-0 admin-mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button class="admin-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

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
                <div>
                    <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn admin-btn-sm">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        К списку товаров
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Форма создания товара -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-mb-0">
                <i class="fas fa-plus admin-me-2"></i>
                Добавить новый товар
            </h5>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="<?php echo e(route('bot.products.store', $telegramBot)); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <div class="admin-row">
                    <div class="admin-col admin-col-8">
                        <!-- Основная информация -->
                        <div class="admin-form-group">
                            <label for="name" class="admin-form-label required">Название товара</label>
                            <input type="text" class="admin-form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="name" name="name" value="<?php echo e(old('name')); ?>" required 
                                   placeholder="Например: iPhone 14 Pro Max 256GB">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">Описание товара</label>
                            <textarea class="admin-form-control admin-textarea <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="4"
                                      placeholder="Подробное описание товара, его характеристики и особенности"><?php echo e(old('description')); ?></textarea>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-row">
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-group">
                                    <label for="article" class="admin-form-label">Артикул</label>
                                    <input type="text" class="admin-form-control <?php $__errorArgs = ['article'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="article" name="article" value="<?php echo e(old('article')); ?>" 
                                           placeholder="ART-001">
                                    <?php $__errorArgs = ['article'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="admin-col admin-col-6">
                                <div class="admin-form-group">
                                    <label for="category_id" class="admin-form-label">Категория</label>
                                    <select class="admin-form-control admin-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="category_id" name="category_id">
                                        <option value="">Выберите категорию</option>
                                        <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category->id); ?>" 
                                                    <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                                <?php echo e($category->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-col admin-col-4">
                        <!-- Фотографии товара -->
                        <div class="admin-form-group">
                            <label for="images" class="admin-form-label">
                                <i class="fas fa-image admin-me-1"></i>
                                Фотографии товара
                                <small class="text-muted d-block" style="font-weight: normal;">
                                    Выберите до 5 фотографий (JPEG, PNG, GIF, WebP, HEIC и др.)
                                </small>
                            </label>
                            <input type="file" 
                                   class="admin-form-control <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="images" 
                                   name="images[]" 
                                   accept="image/*,.heic,.heif,.avif"
                                   multiple>
                            <small class="admin-form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Максимум 5 фото, до 10MB каждое. Файлы автоматически конвертируются в WebP.
                            </small>
                            <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            
                            <!-- Превью загруженных фотографий -->
                            <div id="images-preview" style="display: none; flex-wrap: wrap; gap: 10px; margin-top: 15px;"></div>
                            
                            <!-- Скрытое поле для индекса главной фотографии -->
                            <input type="hidden" name="main_photo_index" id="main_photo_index" value="0">
                        </div>
                    </div>
                </div>
                <!-- Цена и количество -->
                <div class="admin-row">
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="price" class="admin-form-label required">Цена (₽)</label>
                            <input type="number" class="admin-form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="price" name="price" value="<?php echo e(old('price')); ?>" required 
                                   min="0" step="0.01" placeholder="0.00">
                            <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="markup_percentage" class="admin-form-label">Наценка (%)</label>
                            <input type="number" class="admin-form-control <?php $__errorArgs = ['markup_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="markup_percentage" name="markup_percentage" value="<?php echo e(old('markup_percentage', 0)); ?>" 
                                   min="0" step="0.1" placeholder="0">
                            <?php $__errorArgs = ['markup_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="admin-col admin-col-4">
                        <div class="admin-form-group">
                            <label for="quantity" class="admin-form-label required">Количество</label>
                            <input type="number" class="admin-form-control <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="quantity" name="quantity" value="<?php echo e(old('quantity', 1)); ?>" required 
                                   min="0" placeholder="1">
                            <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="admin-form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Расчет цены -->
                <div class="admin-card admin-mb-4" style="background-color: var(--color-light-gray);">
                    <div class="admin-card-body">
                        <h6 class="admin-mb-3">Расчет стоимости:</h6>
                        <div class="admin-row">
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Базовая цена:</div>
                                    <div class="admin-fw-bold" id="base-price-display">0 ₽</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Наценка:</div>
                                    <div class="admin-fw-bold" id="markup-display">0% (0 ₽)</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Итоговая цена:</div>
                                    <div class="admin-fw-bold admin-text-success" id="final-price-display">0 ₽</div>
                                </div>
                            </div>
                            <div class="admin-col admin-col-3">
                                <div class="admin-text-center">
                                    <div class="admin-text-muted admin-mb-1">Общая стоимость:</div>
                                    <div class="admin-fw-bold admin-text-info" id="total-value-display">0 ₽</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Дополнительные настройки -->
                <div class="admin-form-group">
                    <div class="admin-form-check">
                        <input type="checkbox" class="admin-form-check-input" id="is_active" name="is_active" 
                               value="1" <?php echo e(old('is_active', 1) ? 'checked' : ''); ?>>
                        <label for="is_active" class="admin-form-check-label">Товар активен (доступен для продажи)</label>
                    </div>
                </div>



                <!-- Кнопки действий -->
                <div class="admin-d-flex admin-justify-content-between admin-align-items-center">
                    <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="admin-btn">
                        <i class="fas fa-arrow-left admin-me-2"></i>
                        Отмена
                    </a>
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save admin-me-2"></i>
                        Создать товар
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Превью изображений
    const imagesInput = document.getElementById('images');
    const imagesPreview = document.getElementById('images-preview');
    const mainPhotoIndexInput = document.getElementById('main_photo_index');
    let selectedFiles = [];
    let mainPhotoIndex = 0;
    
    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            selectedFiles = Array.from(e.target.files);
            showImagesPreview();
        });
    }
    
    function showImagesPreview() {
        if (selectedFiles.length === 0) {
            imagesPreview.style.display = 'none';
            return;
        }
        
        imagesPreview.style.display = 'flex';
        imagesPreview.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const card = document.createElement('div');
                card.style.cssText = 'position: relative; width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 2px solid ' + (index === mainPhotoIndex ? '#f6ad55' : '#e2e8f0') + '; cursor: pointer;';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                
                const mainBadge = document.createElement('div');
                mainBadge.style.cssText = 'position: absolute; top: 0; left: 0; background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); color: white; padding: 4px 8px; font-size: 11px; font-weight: 600; display: ' + (index === mainPhotoIndex ? 'block' : 'none') + ';';
                mainBadge.innerHTML = '<i class="fas fa-star"></i> Главная';
                
                const fileName = document.createElement('div');
                fileName.style.cssText = 'position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 4px; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                fileName.textContent = file.name;
                
                card.appendChild(img);
                card.appendChild(mainBadge);
                card.appendChild(fileName);
                
                card.addEventListener('click', function() {
                    mainPhotoIndex = index;
                    mainPhotoIndexInput.value = index;
                    showImagesPreview();
                });
                
                imagesPreview.appendChild(card);
            };
            
            reader.readAsDataURL(file);
        });
    }
    
    // Функция автоматического расчета цены с наценкой
    const priceInput = document.getElementById('price');
    const markupInput = document.getElementById('markup_percentage');
    const quantityInput = document.getElementById('quantity');
    
    function updatePriceCalculation() {
        const basePrice = parseFloat(priceInput?.value) || 0;
        const markupPercentage = parseFloat(markupInput?.value) || 0;
        const quantity = parseInt(quantityInput?.value) || 0;
        
        const markupAmount = basePrice * (markupPercentage / 100);
        const finalPrice = basePrice + markupAmount;
        const totalValue = finalPrice * quantity;
        
        // Обновляем отображение
        const basePriceDisplay = document.getElementById('base-price-display');
        const markupDisplay = document.getElementById('markup-display');
        const finalPriceDisplay = document.getElementById('final-price-display');
        const totalValueDisplay = document.getElementById('total-value-display');
        
        if (basePriceDisplay) basePriceDisplay.textContent = basePrice.toLocaleString('ru-RU') + ' ₽';
        if (markupDisplay) markupDisplay.textContent = markupPercentage + '% (' + markupAmount.toLocaleString('ru-RU') + ' ₽)';
        if (finalPriceDisplay) finalPriceDisplay.textContent = finalPrice.toLocaleString('ru-RU') + ' ₽';
        if (totalValueDisplay) totalValueDisplay.textContent = totalValue.toLocaleString('ru-RU') + ' ₽';
    }
    
    // Привязываем обработчики событий
    if (priceInput) {
        priceInput.addEventListener('input', updatePriceCalculation);
        priceInput.addEventListener('blur', function() {
            const value = parseFloat(this.value) || 0;
            this.value = value.toFixed(2);
            updatePriceCalculation();
        });
    }
    if (markupInput) markupInput.addEventListener('input', updatePriceCalculation);
    if (quantityInput) quantityInput.addEventListener('input', updatePriceCalculation);
    
    // Первоначальный расчет
    updatePriceCalculation();
});
</script>
<?php $__env->stopSection(); ?>
                    
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/products/create.blade.php ENDPATH**/ ?>