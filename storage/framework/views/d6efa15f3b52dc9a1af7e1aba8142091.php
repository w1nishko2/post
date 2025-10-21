<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
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
                            <i class="fas fa-boxes me-2"></i>Мои магазины
                        </a>
                    </nav>
                </div>
            </div>

            <?php if(isset($telegramBot)): ?>
                <!-- Информация о боте -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot "></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo e($telegramBot->bot_name); ?></h6>
                                <small class="text-muted">Добавление товара в магазин</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Добавить новый товар</h5>
                    <?php if(isset($telegramBot)): ?>
                        <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к товарам
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('products.select-bot')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Выбрать магазин
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if(isset($telegramBot)): ?>
                        <form method="POST" action="<?php echo e(route('bot.products.store', $telegramBot)); ?>">
                            <?php echo csrf_field(); ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Сначала выберите магазин для добавления товара</p>
                            <a href="<?php echo e(route('products.select-bot')); ?>" class="btn btn-primary">Выбрать магазин</a>
                        </div>
                    <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название товара <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="name" name="name" value="<?php echo e(old('name')); ?>" 
                                           placeholder="Например: Тормозные колодки передние" required>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="article" class="form-label">Артикул <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['article'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="article" name="article" value="<?php echo e(old('article')); ?>" 
                                           placeholder="BP001" required>
                                    <?php $__errorArgs = ['article'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="category_id" name="category_id">
                                <option value="">Выберите категорию (необязательно)</option>
                                <?php if(isset($categories)): ?>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" 
                                                <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                            <?php echo e($category->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">Если не выберете категорию, товар будет добавлен без категории</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Подробное описание товара..."><?php echo e(old('description')); ?></textarea>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="photo_url" class="form-label">Ссылка на фотографию</label>
                            <input type="url" class="form-control <?php $__errorArgs = ['photo_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="photo_url" name="photo_url" value="<?php echo e(old('photo_url')); ?>" 
                                   placeholder="https://example.com/photo.jpg">
                            <?php $__errorArgs = ['photo_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="specifications_text" class="form-label">Характеристики товара</label>
                            <textarea class="form-control <?php $__errorArgs = ['specifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="specifications_text" name="specifications_text" rows="6" 
                                      placeholder="Введите каждую характеристику с новой строки:&#10;Материал: Пластик&#10;Цвет: Черный&#10;Вес: 500 г&#10;Гарантия: 1 год"><?php echo e(old('specifications_text') ? implode("\n", old('specifications_text')) : ''); ?></textarea>
                            <div class="form-text">Каждую характеристику вводите с новой строки</div>
                            <?php $__errorArgs = ['specifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Количество в наличии <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="quantity" name="quantity" value="<?php echo e(old('quantity', 0)); ?>" 
                                           min="0" max="999999" required>
                                    <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Цена за штуку (₽) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="price" name="price" value="<?php echo e(old('price')); ?>" 
                                           step="0.01" min="0" max="999999.99" 
                                           placeholder="2500.00" required>
                                    <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="markup_percentage" class="form-label">Наценка (%)</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['markup_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="markup_percentage" name="markup_percentage" value="<?php echo e(old('markup_percentage', 0)); ?>" 
                                           step="0.01" min="0" max="1000" 
                                           placeholder="10.00">
                                    <?php $__errorArgs = ['markup_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="form-text">Наценка к базовой цене</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_active">
                                            Товар активен (доступен для продажи)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Блок расчёта цены с наценкой -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Расчёт цены с наценкой</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <small class="text-muted">Базовая цена:</small>
                                                <div id="base-price-display" class="fw-bold">0 ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Наценка:</small>
                                                <div id="markup-display" class="fw-bold text-info">0% (0 ₽)</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Итоговая цена:</small>
                                                <div id="final-price-display" class="fw-bold text-success fs-5">0 ₽</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Общая стоимость:</small>
                                                <div id="total-value-display" class="fw-bold text-primary">0 ₽</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(isset($telegramBot)): ?>
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo e(route('bot.products.index', $telegramBot)); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Сохранить товар
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предварительный просмотр изображения
    const photoUrlInput = document.getElementById('photo_url');
    
    if (photoUrlInput) {
        photoUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            let previewContainer = document.getElementById('photo-preview');
            
            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.id = 'photo-preview';
                previewContainer.className = 'mt-2';
                this.parentNode.appendChild(previewContainer);
            }
            
            if (url && url.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
                previewContainer.innerHTML = '<img src="' + url + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" onerror="this.style.display=\'none\'">';
            } else {
                previewContainer.innerHTML = '';
            }
        });
    }

    // Автоматическое форматирование цены
    const priceInput = document.getElementById('price');
    const markupInput = document.getElementById('markup_percentage');
    const quantityInput = document.getElementById('quantity');
    
    if (priceInput) {
        priceInput.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
            updatePriceCalculation();
        });
    }

    if (markupInput) {
        markupInput.addEventListener('input', updatePriceCalculation);
    }

    // Функция обновления расчёта цены с наценкой
    function updatePriceCalculation() {
        const basePrice = parseFloat(priceInput.value) || 0;
        const markupPercentage = parseFloat(markupInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        const markupAmount = basePrice * (markupPercentage / 100);
        const finalPrice = basePrice + markupAmount;
        const totalValue = finalPrice * quantity;
        
        // Обновляем отображение
        document.getElementById('base-price-display').textContent = basePrice.toLocaleString('ru-RU') + ' ₽';
        document.getElementById('markup-display').textContent = markupPercentage + '% (' + markupAmount.toLocaleString('ru-RU') + ' ₽)';
        document.getElementById('final-price-display').textContent = finalPrice.toLocaleString('ru-RU') + ' ₽';
        document.getElementById('total-value-display').textContent = totalValue.toLocaleString('ru-RU') + ' ₽';
    }
    
    if (quantityInput && priceInput) {
        quantityInput.addEventListener('input', updatePriceCalculation);
        priceInput.addEventListener('input', updatePriceCalculation);
        updatePriceCalculation(); // Инициализация
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\products\create.blade.php ENDPATH**/ ?>