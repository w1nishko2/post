<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Навигационная панель -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>">
                            Мои боты
                        </a>
                        <a class="nav-link" href="<?php echo e(route('bot.products.index', $telegramBot)); ?>">
                            Товары
                        </a>
                        <a class="nav-link active" href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
                            Категории
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Хлебные крошки -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
                            Категории
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Создание категории
                    </li>
                </ol>
            </nav>

            <!-- Форма создания категории -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Создание новой категории</h5>
                    <small class="text-muted">Создайте категорию для группировки товаров в вашем магазине</small>
                </div>

                <div class="card-body">
                    <form action="<?php echo e(route('bot.categories.store', $telegramBot)); ?>" method="POST" id="categoryForm">
                        <?php echo csrf_field(); ?>

                        <!-- Название категории -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                Название категории <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo e(old('name')); ?>" 
                                   placeholder="Например: Электроника, Одежда, Книги..."
                                   maxlength="100"
                                   required>
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
                            <div class="form-text">
                                Краткое и понятное название для ваших покупателей
                            </div>
                        </div>

                        <!-- Описание категории -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Описание категории
                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Краткое описание категории (необязательно)"
                                      maxlength="500"><?php echo e(old('description')); ?></textarea>
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
                            <div class="form-text">
                                Дополнительная информация о товарах в этой категории
                            </div>
                        </div>

                        <!-- Фотография категории -->
                        <div class="mb-4">
                            <label for="photo_url" class="form-label">
                                Ссылка на фотографию
                            </label>
                            <input type="url" 
                                   class="form-control <?php $__errorArgs = ['photo_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="photo_url" 
                                   name="photo_url" 
                                   value="<?php echo e(old('photo_url')); ?>" 
                                   placeholder="https://example.com/category-image.jpg">
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
                            <div class="form-text">
                                Рекомендуемый размер: 300x300px. Поддерживаются форматы: JPG, PNG
                            </div>
                        </div>

                        <!-- Предварительный просмотр фото -->
                        <div class="mb-4" id="photoPreview" style="display: none;">
                            <label class="form-label">
                                Предварительный просмотр
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex align-items-center">
                                    <img id="previewImage" 
                                         src="" 
                                         alt="Предварительный просмотр" 
                                         class="rounded me-3"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1" id="previewName">Название категории</h6>
                                        <p class="text-muted small mb-0" id="previewDescription">Описание категории</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Статус активности -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on text-success"></i> Категория активна
                                </label>
                            </div>
                            <div class="form-text">
                                Неактивные категории не отображаются в мини-приложении
                            </div>
                        </div>

                        <!-- Кнопки управления -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                Создать категорию
                            </button>
                            <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Подсказки -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Полезные советы</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Названия категорий</h6>
                            <ul class="small text-muted mb-3">
                                <li>Используйте простые и понятные названия</li>
                                <li>Избегайте слишком длинных названий</li>
                                <li>Думайте о том, как клиент будет искать товар</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">Изображения категорий</h6>
                            </h6>
                            <ul class="small text-muted mb-0">
                                <li>Рекомендуемый размер: 300x300 пикселей</li>
                                <li>Используйте качественные изображения</li>
                                <li>Изображение должно отражать суть категории</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoUrlInput = document.getElementById('photo_url');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    const previewName = document.getElementById('previewName');
    const previewDescription = document.getElementById('previewDescription');

    function updatePreview() {
        const photoUrl = photoUrlInput.value.trim();
        const name = nameInput.value.trim() || 'Название категории';
        const description = descriptionInput.value.trim() || 'Описание категории';

        previewName.textContent = name;
        previewDescription.textContent = description;

        if (photoUrl && isValidUrl(photoUrl)) {
            previewImage.src = photoUrl;
            previewImage.onerror = function() {
                photoPreview.style.display = 'none';
            };
            previewImage.onload = function() {
                photoPreview.style.display = 'block';
            };
        } else {
            photoPreview.style.display = 'none';
        }
    }

    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Обновление превью при изменении полей
    photoUrlInput.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    // Валидация формы
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            nameInput.focus();
            alert('Пожалуйста, укажите название категории');
            return false;
        }

        const photoUrl = photoUrlInput.value.trim();
        if (photoUrl && !isValidUrl(photoUrl)) {
            e.preventDefault();
            photoUrlInput.focus();
            alert('Пожалуйста, укажите корректную ссылку на изображение');
            return false;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\categories\create.blade.php ENDPATH**/ ?>