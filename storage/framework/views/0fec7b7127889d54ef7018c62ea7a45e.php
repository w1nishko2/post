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
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('bot.categories.show', [$telegramBot, $category])); ?>">
                            <?php echo e($category->name); ?>

                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Редактирование
                    </li>
                </ol>
            </nav>

            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Форма редактирования категории -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактирование категории "<?php echo e($category->name); ?>"</h5>
                    <small class="text-muted">Внесите изменения в информацию о категории</small>
                </div>

                <div class="card-body">
                    <form action="<?php echo e(route('bot.categories.update', [$telegramBot, $category])); ?>" method="POST" id="categoryForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

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
                                   value="<?php echo e(old('name', $category->name)); ?>" 
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
                                      maxlength="500"><?php echo e(old('description', $category->description)); ?></textarea>
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
                                   value="<?php echo e(old('photo_url', $category->photo_url)); ?>" 
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

                        <!-- Текущее изображение -->
                        <?php if($category->photo_url): ?>
                        <div class="mb-4">
                            <label class="form-label">
                                Текущее изображение
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <img src="<?php echo e($category->photo_url); ?>" 
                                     alt="<?php echo e($category->name); ?>" 
                                     class="rounded"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="this.parentElement.innerHTML='<div class=\'text-muted\'>Изображение недоступно</div>'">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Предварительный просмотр фото -->
                        <div class="mb-4" id="photoPreview" style="display: none;">
                            <label class="form-label">
                                Предварительный просмотр изменений
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex align-items-center">
                                    <img id="previewImage" 
                                         src="" 
                                         alt="Предварительный просмотр" 
                                         class="rounded me-3"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1" id="previewName"><?php echo e($category->name); ?></h6>
                                        <p class="text-muted small mb-0" id="previewDescription"><?php echo e($category->description); ?></p>
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
                                       <?php echo e(old('is_active', $category->is_active) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on text-success"></i> Категория активна
                                </label>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Неактивные категории не отображаются в мини-приложении
                            </div>
                        </div>

                        <!-- Информация о товарах в категории -->
                        <?php if($category->products->count() > 0): ?>
                        <div class="alert alert-info">
                            <h6>Информация о товарах</h6>
                            <p class="mb-2">
                                В этой категории находится <strong><?php echo e($category->products->count()); ?></strong> товаров.
                                Активных товаров: <strong><?php echo e($category->activeProducts->count()); ?></strong>.
                            </p>
                            <?php if($category->activeProducts->count() > 0): ?>
                            <small class="text-muted">
                                При деактивации категории товары останутся, но категория не будет отображаться в мини-приложении.
                            </small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Кнопки управления -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                Сохранить изменения
                            </button>
                            <a href="<?php echo e(route('bot.categories.show', [$telegramBot, $category])); ?>" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> К списку категорий
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Опасная зона -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">Опасная зона</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Удалить категорию</h6>
                            <p class="text-muted small mb-0">
                                Удаление категории необратимо. 
                                <?php if($category->products->count() > 0): ?>
                                    <strong>Внимание:</strong> В категории есть товары, удаление невозможно.
                                <?php else: ?>
                                    Категория будет удалена безвозвратно.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <?php if($category->products->count() > 0): ?>
                                <button class="btn btn-outline-danger" disabled>
                                    Нельзя удалить
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-danger" onclick="deleteCategory()">
                                    Удалить категорию
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрытая форма для удаления -->
<form id="delete-form" method="POST" action="<?php echo e(route('bot.categories.destroy', [$telegramBot, $category])); ?>" class="d-none">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
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

    const originalPhotoUrl = '<?php echo e($category->photo_url); ?>';
    const originalName = '<?php echo e($category->name); ?>';
    const originalDescription = '<?php echo e($category->description); ?>';

    function updatePreview() {
        const photoUrl = photoUrlInput.value.trim();
        const name = nameInput.value.trim() || 'Название категории';
        const description = descriptionInput.value.trim() || 'Описание категории';

        // Проверяем, есть ли изменения
        const hasChanges = photoUrl !== originalPhotoUrl || 
                          name !== originalName || 
                          description !== originalDescription;

        previewName.textContent = name;
        previewDescription.textContent = description;

        if (hasChanges && photoUrl && isValidUrl(photoUrl)) {
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

    // Автоматическое скрытие алертов
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

function deleteCategory() {
    if (confirm(`Вы уверены, что хотите удалить категорию "<?php echo e($category->name); ?>"?\n\nЭто действие необратимо!`)) {
        document.getElementById('delete-form').submit();
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views\categories\edit.blade.php ENDPATH**/ ?>