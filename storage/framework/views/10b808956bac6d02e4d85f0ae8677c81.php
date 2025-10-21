<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['currentScheme' => 'gray']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['currentScheme' => 'gray']); ?>
<?php foreach (array_filter((['currentScheme' => 'gray']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $colorSchemes = config('color-schemes.available', []);
?>

<div class="color-scheme-selector">
    <form method="POST" action="<?php echo e(route('profile.update.color-scheme')); ?>" id="colorSchemeForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        
        <input type="hidden" name="color_scheme" id="selectedColorScheme" value="<?php echo e($currentScheme); ?>">
        
        <div class="color-schemes-grid">
            <?php $__currentLoopData = $colorSchemes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schemeKey => $scheme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="color-scheme-option <?php echo e($schemeKey === $currentScheme ? 'active' : ''); ?>" 
                     data-scheme="<?php echo e($schemeKey); ?>"
                     data-colors="<?php echo e(json_encode($scheme['colors'])); ?>">
                    <div class="color-preview" style="background-color: <?php echo e($scheme['preview_color']); ?>"></div>
                    <div class="color-scheme-info">
                        <div class="color-scheme-name"><?php echo e($scheme['name']); ?></div>
                        <div class="color-scheme-description"><?php echo e($scheme['description']); ?></div>
                    </div>
                    <?php if($schemeKey === $currentScheme): ?>
                        <div class="active-indicator">
                            <i class="fas fa-check"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="color-scheme-actions">
            <button type="button" class="admin-btn admin-btn-sm" id="previewBtn" disabled>
                <i class="fas fa-eye admin-me-2"></i>
                Предварительный просмотр
            </button>
            <button type="button" class="admin-btn admin-btn-sm" id="resetBtn" disabled>
                <i class="fas fa-undo admin-me-2"></i>
                Сбросить
            </button>
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" id="applyBtn" disabled>
                <i class="fas fa-save admin-me-2"></i>
                Применить схему
            </button>
        </div>
    </form>
</div>

<style>
.color-scheme-selector {
    width: 100%;
}

.color-schemes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-md);
    margin-bottom: var(--space-lg);
}

.color-scheme-option {
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    background-color: var(--color-white);
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.color-scheme-option:hover {
    border-color: var(--color-accent);
    transform: translateY(-2px);
}

.color-scheme-option.active {
    border-color: var(--color-accent);
    background-color: var(--color-accent-light);
}

.color-preview {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid var(--color-white);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.color-scheme-info {
    flex: 1;
}

.color-scheme-name {
    font-weight: 600;
    color: var(--color-text);
    margin-bottom: var(--space-xs);
}

.color-scheme-description {
    font-size: var(--font-size-sm);
    color: var(--color-text-light);
    line-height: 1.4;
}

.active-indicator {
    position: absolute;
    top: var(--space-xs);
    right: var(--space-xs);
    width: 24px;
    height: 24px;
    background-color: var(--color-accent);
    color: var(--color-white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xs);
}

.color-scheme-actions {
    display: flex;
    gap: var(--space-sm);
    justify-content: flex-end;
    flex-wrap: wrap;
}

.color-scheme-actions .admin-btn {
    min-width: 120px;
}

@media (max-width: 767px) {
    .color-schemes-grid {
        grid-template-columns: 1fr;
        gap: var(--space-sm);
    }
    
    .color-scheme-option {
        padding: var(--space-sm);
        gap: var(--space-sm);
    }
    
    .color-preview {
        width: 40px;
        height: 40px;
    }
    
    .color-scheme-actions {
        justify-content: stretch;
    }
    
    .color-scheme-actions .admin-btn {
        flex: 1;
        min-width: auto;
    }
}

/* Предварительный просмотр */
.preview-mode {
    position: relative;
}

.preview-mode::before {
    content: 'ПРЕДВАРИТЕЛЬНЫЙ ПРОСМОТР';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background-color: #f59e0b;
    color: white;
    text-align: center;
    padding: var(--space-xs);
    font-weight: 600;
    font-size: var(--font-size-sm);
    z-index: 10000;
}

.preview-mode body {
    margin-top: 30px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('colorSchemeForm');
    const selectedInput = document.getElementById('selectedColorScheme');
    const previewBtn = document.getElementById('previewBtn');
    const resetBtn = document.getElementById('resetBtn');
    const applyBtn = document.getElementById('applyBtn');
    const options = document.querySelectorAll('.color-scheme-option');
    
    const originalScheme = selectedInput.value;
    let currentPreviewScheme = null;
    let originalColors = {};
    
    // Сохраняем оригинальные цвета
    const rootStyles = getComputedStyle(document.documentElement);
    originalColors = {
        '--color-primary': rootStyles.getPropertyValue('--color-primary'),
        '--color-accent': rootStyles.getPropertyValue('--color-accent'),
        '--color-accent-dark': rootStyles.getPropertyValue('--color-accent-dark'),
        '--color-accent-light': rootStyles.getPropertyValue('--color-accent-light'),
        '--color-accent-border': rootStyles.getPropertyValue('--color-accent-border'),
    };
    
    // Обработка выбора схемы
    options.forEach(option => {
        option.addEventListener('click', function() {
            const schemeKey = this.dataset.scheme;
            
            // Убираем активное состояние у всех
            options.forEach(opt => opt.classList.remove('active'));
            options.forEach(opt => opt.querySelector('.active-indicator')?.remove());
            
            // Добавляем активное состояние к выбранной
            this.classList.add('active');
            
            // Добавляем индикатор
            if (!this.querySelector('.active-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'active-indicator';
                indicator.innerHTML = '<i class="fas fa-check"></i>';
                this.appendChild(indicator);
            }
            
            selectedInput.value = schemeKey;
            
            // Включаем кнопки если выбрана другая схема
            const isDifferent = schemeKey !== originalScheme;
            previewBtn.disabled = !isDifferent;
            resetBtn.disabled = !isDifferent;
            applyBtn.disabled = !isDifferent;
        });
    });
    
    // Предварительный просмотр
    previewBtn.addEventListener('click', function() {
        const selectedScheme = selectedInput.value;
        const option = document.querySelector(`[data-scheme="${selectedScheme}"]`);
        
        if (option) {
            const colors = JSON.parse(option.dataset.colors);
            applyColorsToPage(colors);
            currentPreviewScheme = selectedScheme;
            document.body.classList.add('preview-mode');
            
            this.innerHTML = '<i class="fas fa-eye-slash admin-me-2"></i>Выйти из просмотра';
            this.onclick = exitPreview;
        }
    });
    
    // Сброс к оригинальной схеме
    resetBtn.addEventListener('click', function() {
        exitPreview();
        
        // Возвращаем к оригинальной схеме
        options.forEach(opt => opt.classList.remove('active'));
        options.forEach(opt => opt.querySelector('.active-indicator')?.remove());
        
        const originalOption = document.querySelector(`[data-scheme="${originalScheme}"]`);
        if (originalOption) {
            originalOption.classList.add('active');
            const indicator = document.createElement('div');
            indicator.className = 'active-indicator';
            indicator.innerHTML = '<i class="fas fa-check"></i>';
            originalOption.appendChild(indicator);
        }
        
        selectedInput.value = originalScheme;
        previewBtn.disabled = true;
        resetBtn.disabled = true;
        applyBtn.disabled = true;
    });
    
    function applyColorsToPage(colors) {
        Object.entries(colors).forEach(([property, value]) => {
            document.documentElement.style.setProperty(property, value);
        });
    }
    
    function exitPreview() {
        if (currentPreviewScheme) {
            // Возвращаем оригинальные цвета
            Object.entries(originalColors).forEach(([property, value]) => {
                document.documentElement.style.setProperty(property, value);
            });
            
            document.body.classList.remove('preview-mode');
            currentPreviewScheme = null;
            
            previewBtn.innerHTML = '<i class="fas fa-eye admin-me-2"></i>Предварительный просмотр';
            previewBtn.onclick = null;
        }
    }
    
    // Применение схемы
    form.addEventListener('submit', function(e) {
        exitPreview();
        
        const submitBtn = applyBtn;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin admin-me-2"></i>Применение...';
        submitBtn.disabled = true;
        
        // Восстанавливаем кнопку через 5 секунд на случай ошибки
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
        }, 5000);
    });
    
    // Обработка закрытия страницы во время предварительного просмотра
    window.addEventListener('beforeunload', function(e) {
        if (currentPreviewScheme) {
            exitPreview();
        }
    });
});
</script><?php /**PATH C:\OSPanel\domains\post\resources\views/components/color-scheme-selector.blade.php ENDPATH**/ ?>