{{-- Компонент галереи изображений для товаров --}}
@props(['productId' => null])

<div id="product-image-gallery-container" data-product-id="{{ $productId }}">
    {{-- Зона для загрузки (Drag & Drop) --}}
    <div id="image-dropzone">
        <i class="fas fa-cloud-upload-alt fa-3x"></i>
        <p><strong>Перетащите изображения сюда</strong></p>
        <p class="text-muted">или</p>
        <button type="button" class="admin-btn admin-btn-outline-primary admin-btn-sm" id="select-images-btn">
            <i class="fas fa-folder-open admin-me-1"></i>
            Выбрать файлы
        </button>
        <input type="file" id="image-file-input" accept="image/*,.heic,.heif" multiple hidden>
        <p class="text-muted admin-mt-2" style="font-size: 0.875rem;">
            <i class="fas fa-info-circle"></i>
            Максимум 5 фотографий. Поддерживаемые форматы: JPEG, PNG, GIF, WebP, HEIC, HEIF
        </p>
        <p class="text-muted" style="font-size: 0.75rem;">
            Изображения будут автоматически конвертированы в формат WebP 200x200px
        </p>
    </div>

    {{-- Прогресс загрузки --}}
    <div id="upload-progress">
        <div class="spinner"></div>
        <p class="admin-mt-2">Загрузка изображений...</p>
    </div>

    {{-- Галерея загруженных изображений --}}
    <div id="image-gallery">
        <div id="gallery-header">
            <span class="gallery-title">
                <i class="fas fa-images"></i>
                Загруженные изображения (<span id="image-count">0</span>/5)
            </span>
            <button type="button" class="admin-btn admin-btn-sm admin-btn-outline-danger" id="clear-all-images">
                <i class="fas fa-trash admin-me-1"></i>
                Удалить все
            </button>
        </div>
        <div id="image-gallery-grid">
            {{-- Изображения будут добавлены через JavaScript --}}
        </div>
        <div class="gallery-info" style="margin-top: 1rem;">
            <small class="text-muted">
                <i class="fas fa-star admin-me-1"></i>
                <strong>Главное изображение</strong> будет отображаться по умолчанию в каталоге и миниаппе
            </small>
            <br>
            <small class="text-muted">
                <i class="fas fa-mouse-pointer admin-me-1"></i>
                Нажмите на звездочку, чтобы установить изображение как главное
            </small>
        </div>
    </div>
</div>

{{-- Подключение стилей --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/product-image-gallery.css') }}">
@endpush

{{-- Подключение скриптов --}}
@push('scripts')
<script src="{{ asset('js/product-image-gallery.js') }}"></script>
@endpush
