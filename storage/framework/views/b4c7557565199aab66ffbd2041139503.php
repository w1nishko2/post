<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <!-- Навигационные табы -->
    <div class="admin-nav-pills admin-mb-4">
        <a class="admin-nav-pill" href="<?php echo e(route('home')); ?>">
            <i class="fas fa-robot"></i> Мои боты
        </a>
        <a class="admin-nav-pill" href="<?php echo e(route('bot.products.index', $telegramBot)); ?>">
            <i class="fas fa-boxes"></i> Товары
        </a>
        <a class="admin-nav-pill active" href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>">
            <i class="fas fa-folder"></i> Категории
        </a>
    </div>

    <!-- Хлебные крошки -->
    <div class="admin-breadcrumb admin-mb-4">
        <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="admin-breadcrumb-link">
            <i class="fas fa-folder admin-me-1"></i> Категории
        </a>
        <span class="admin-breadcrumb-separator">></span>
        <span class="admin-breadcrumb-current">Создание категории</span>
    </div>

    <!-- Контент в адаптивной сетке -->
    <div class="admin-row">
        <div class="admin-col admin-col-12">
            <!-- Форма создания категории -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-mb-0">
                        <i class="fas fa-plus admin-me-2"></i>
                        Создание новой категории
                    </h5>
                    <p class="admin-text-muted admin-mb-0 admin-mt-2">
                        Создайте категорию для группировки товаров в вашем магазине
                    </p>
                </div>

                <div class="admin-card-body">
                    <form action="<?php echo e(route('bot.categories.store', $telegramBot)); ?>" method="POST" id="categoryForm">
                        <?php echo csrf_field(); ?>

                        <!-- Основная информация в адаптивной сетке -->
                        <div class="admin-row">
                            <div class="admin-col admin-col-12 admin-col-md-6">
                                <!-- Название категории -->
                                <div class="admin-form-group">
                                    <label for="name" class="admin-form-label required">
                                        Название категории
                                    </label>
                                    <input type="text" 
                                           class="admin-form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-form-error <?php unset($message);
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
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="admin-form-text">
                                        Краткое и понятное название для ваших покупателей
                                    </div>
                                </div>
                            </div>
                            
                            <div class="admin-col admin-col-12 admin-col-md-6">
                                <!-- Фотография категории -->
                                <div class="admin-form-group">
                                    <label for="photo" class="admin-form-label">
                                        <i class="fas fa-image admin-me-1"></i>
                                        Фотография категории
                                    </label>
                                    
                                    <!-- Зона загрузки -->
                                    <div id="category-photo-dropzone" style="border: 2px dashed #cbd5e0; border-radius: 8px; padding: 1.5rem; text-align: center; background-color: #f7fafc; transition: all 0.3s ease; cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #4299e1; margin-bottom: 0.5rem;"></i>
                                        <p style="margin-bottom: 0.5rem;"><strong>Перетащите изображение сюда</strong></p>
                                        <p class="admin-text-muted" style="font-size: 0.875rem;">или</p>
                                        <button type="button" class="admin-btn admin-btn-outline-primary admin-btn-sm" id="select-category-photo-btn">
                                            <i class="fas fa-folder-open admin-me-1"></i>
                                            Выбрать файл
                                        </button>
                                        <input type="file" id="photo" name="photo" accept="image/*,.heic,.heif" hidden>
                                        <p class="admin-text-muted admin-mt-2" style="font-size: 0.75rem;">
                                            Максимальный размер: 10MB. Поддерживаемые форматы: JPEG, PNG, GIF, WebP, HEIC, HEIF
                                        </p>
                                    </div>
                                    
                                    <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error admin-mt-2"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    
                                    <!-- Превью загруженного изображения -->
                                    <div id="category-photo-preview" style="display: none; margin-top: 1rem;">
                                        <div style="position: relative; width: 200px; height: 200px; border-radius: 8px; overflow: hidden; border: 2px solid #e2e8f0;">
                                            <img id="category-preview-img" src="" style="width: 100%; height: 100%; object-fit: cover;" alt="Превью">
                                            <button type="button" onclick="removeCategoryPhoto()" style="position: absolute; top: 8px; right: 8px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Описание категории -->
                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">
                                Описание категории
                            </label>
                            <textarea class="admin-form-control admin-textarea <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> admin-form-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" 
                                      name="description" 
                                      placeholder="Краткое описание категории (необязательно)"
                                      maxlength="500"><?php echo e(old('description')); ?></textarea>
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
                            <div class="admin-form-text">
                                Дополнительная информация о товарах в этой категории
                            </div>
                        </div>



                        <!-- Статус активности -->
                        <div class="admin-form-group">
                            <div class="admin-form-check">
                                <input class="admin-form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                                <label class="admin-form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on admin-text-success admin-me-1"></i> 
                                    Категория активна
                                </label>
                            </div>
                            <div class="admin-form-text">
                                Неактивные категории не отображаются в мини-приложении
                            </div>
                        </div>

                        <!-- Кнопки управления -->
                        <div class="admin-d-flex admin-gap-md admin-flex-wrap">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-plus admin-me-2"></i>
                                Создать категорию
                            </button>
                            <a href="<?php echo e(route('bot.categories.index', $telegramBot)); ?>" class="admin-btn">
                                <i class="fas fa-arrow-left admin-me-2"></i>
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>

           
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<script>
// Функция удаления фото категории
window.removeCategoryPhoto = function() {
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('category-photo-preview');
    const dropzone = document.getElementById('category-photo-dropzone');
    
    photoInput.value = '';
    preview.style.display = 'none';
    dropzone.style.display = 'block';
};

document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const selectBtn = document.getElementById('select-category-photo-btn');
    const dropzone = document.getElementById('category-photo-dropzone');
    const preview = document.getElementById('category-photo-preview');
    const previewImg = document.getElementById('category-preview-img');
    const nameInput = document.getElementById('name');
    const form = document.getElementById('categoryForm');

    // Изменяем enctype формы для загрузки файлов
    form.setAttribute('enctype', 'multipart/form-data');

    // Обработка клика на кнопку выбора файла
    selectBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Предотвращаем всплытие события к dropzone
        photoInput.click();
    });

    // Обработка клика на зону dropzone
    dropzone.addEventListener('click', function(e) {
        // Проверяем, что клик был именно на dropzone, а не на кнопке внутри
        if (e.target === dropzone || e.target.closest('#category-photo-dropzone') === dropzone) {
            photoInput.click();
        }
    });

    // Обработка выбора файла
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFile(file);
        }
    });

    // Drag & Drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#4299e1';
        this.style.backgroundColor = '#ebf8ff';
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#cbd5e0';
        this.style.backgroundColor = '#f7fafc';
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#cbd5e0';
        this.style.backgroundColor = '#f7fafc';
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;
            handleFile(file);
        } else {
            alert('Пожалуйста, загрузите изображение');
        }
    });

    // Обработка файла
    function handleFile(file) {
        // Проверка размера (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('Файл слишком большой. Максимальный размер: 10MB');
            photoInput.value = '';
            return;
        }

        // Проверка типа файла
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif'];
        if (!validTypes.includes(file.type) && !file.name.match(/\.(heic|heif)$/i)) {
            alert('Неподдерживаемый формат файла. Используйте: JPEG, PNG, GIF, WebP, HEIC, HEIF');
            photoInput.value = '';
            return;
        }

        // Показываем превью
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            dropzone.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    // Валидация формы
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            nameInput.focus();
            alert('Пожалуйста, укажите название категории');
            return false;
        }
    });
});
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\post\resources\views/categories/create.blade.php ENDPATH**/ ?>