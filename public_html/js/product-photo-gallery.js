/**
 * Галерея фотографий для товаров - обновленная версия с поддержкой загрузки файлов
 */

class ProductPhotoGallery {
    constructor(options = {}) {
        this.maxPhotos = options.maxPhotos || 5;
        this.allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff', 'image/avif', 'image/heic', 'image/heif'];
        this.maxFileSize = 10 * 1024 * 1024; // 10MB
        
        this.photosData = [];
        this.mainPhotoIndex = 0;
        
        this.initElements();
        this.bindEvents();
        this.updateGallery();
    }
    
    initElements() {
        // Основные элементы
        this.photoUrlInput = document.getElementById('photo_url_input');
        this.addPhotoBtn = document.getElementById('add-photo-btn');
        this.photoDropzone = document.getElementById('photo-dropzone');
        this.fileInput = document.getElementById('file-input');
        this.selectFilesBtn = document.getElementById('select-files-btn');
        this.galleryGrid = document.getElementById('gallery-grid');
        this.photoCount = document.getElementById('photo-count');
        this.clearGalleryBtn = document.getElementById('clear-gallery-btn');
        
        // Скрытые поля формы
        this.photosGalleryInput = document.getElementById('photos_gallery_input');
        this.mainPhotoIndexInput = document.getElementById('main_photo_index_input');
        this.photoUrlHidden = document.getElementById('photo_url_hidden');
    }
    
    bindEvents() {
        // Добавление по URL
        if (this.addPhotoBtn) {
            this.addPhotoBtn.addEventListener('click', () => this.handleUrlAdd());
        }
        
        // Нажатие Enter в поле URL
        if (this.photoUrlInput) {
            this.photoUrlInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.handleUrlAdd();
                }
            });
        }
        
        // Выбор файлов
        if (this.selectFilesBtn) {
            this.selectFilesBtn.addEventListener('click', () => {
                this.fileInput?.click();
            });
        }
        
        // Обработка выбранных файлов
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => {
                this.handleFileSelect(e.target.files);
            });
        }
        
        // Drag & Drop
        if (this.photoDropzone) {
            this.setupDragAndDrop();
        }
        
        // Очистка галереи
        if (this.clearGalleryBtn) {
            this.clearGalleryBtn.addEventListener('click', () => {
                if (confirm('Удалить все фотографии из галереи?')) {
                    this.clearGallery();
                }
            });
        }
    }
    
    setupDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.photoDropzone.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            this.photoDropzone.addEventListener(eventName, () => {
                this.photoDropzone.classList.add('dragover');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            this.photoDropzone.addEventListener(eventName, () => {
                this.photoDropzone.classList.remove('dragover');
            }, false);
        });
        
        this.photoDropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFileSelect(files);
        }, false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    async handleUrlAdd() {
        const url = this.photoUrlInput?.value?.trim();
        if (!url) {
            this.showError('Введите URL изображения');
            return;
        }
        
        if (!this.isValidImageUrl(url)) {
            this.showError('Некорректный URL изображения');
            return;
        }
        
        if (this.photosData.length >= this.maxPhotos) {
            this.showError(`Максимум ${this.maxPhotos} фотографий`);
            return;
        }
        
        try {
            this.showLoading(true);
            const result = await this.uploadFromUrl(url);
            
            if (result.success) {
                this.addPhotoToGallery(result);
                this.photoUrlInput.value = '';
                this.showSuccess('Изображение добавлено');
            } else {
                this.showError(result.error || 'Ошибка загрузки изображения');
            }
        } catch (error) {
            this.showError('Ошибка загрузки: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }
    
    async handleFileSelect(files) {
        const fileArray = Array.from(files);
        
        if (this.photosData.length + fileArray.length > this.maxPhotos) {
            this.showError(`Максимум ${this.maxPhotos} фотографий. Сейчас: ${this.photosData.length}`);
            return;
        }
        
        const validFiles = fileArray.filter(file => this.validateFile(file));
        if (validFiles.length === 0) {
            this.showError('Нет подходящих файлов для загрузки');
            return;
        }
        
        try {
            this.showLoading(true);
            const results = await this.uploadMultipleFiles(validFiles);
            
            let successCount = 0;
            results.forEach(result => {
                if (result.success) {
                    this.addPhotoToGallery(result);
                    successCount++;
                } else {
                    console.error('Ошибка загрузки файла:', result.error);
                }
            });
            
            if (successCount > 0) {
                this.showSuccess(`Загружено ${successCount} изображений`);
            }
            
            // Очищаем input
            if (this.fileInput) {
                this.fileInput.value = '';
            }
            
        } catch (error) {
            this.showError('Ошибка загрузки: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }
    
    validateFile(file) {
        if (!this.allowedTypes.includes(file.type) && !this.isImageByExtension(file.name)) {
            this.showError(`Неподдерживаемый тип файла: ${file.name}`);
            return false;
        }
        
        if (file.size > this.maxFileSize) {
            this.showError(`Файл слишком большой: ${file.name} (макс. 10MB)`);
            return false;
        }
        
        return true;
    }
    
    isImageByExtension(filename) {
        const ext = filename.toLowerCase().split('.').pop();
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'avif', 'heic', 'heif'];
        return imageExtensions.includes(ext);
    }
    
    isValidImageUrl(url) {
        try {
            new URL(url);
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.tiff', '.tif', '.avif'];
            return imageExtensions.some(ext => url.toLowerCase().includes(ext)) || 
                   url.includes('image') || 
                   url.includes('photo');
        } catch {
            return false;
        }
    }
    
    async uploadFromUrl(url) {
        const formData = new FormData();
        formData.append('url', url);
        formData.append('directory', 'products');
        
        const response = await fetch('/image-upload/from-url', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: formData
        });
        
        return await response.json();
    }
    
    async uploadMultipleFiles(files) {
        const results = [];
        
        // Загружаем по одному файлу для лучшего контроля
        for (const file of files) {
            try {
                const result = await this.uploadSingleFile(file);
                results.push(result);
            } catch (error) {
                results.push({ success: false, error: error.message });
            }
        }
        
        return results;
    }
    
    async uploadSingleFile(file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('directory', 'products');
        
        const response = await fetch('/image-upload/single', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: formData
        });
        
        return await response.json();
    }
    
    addPhotoToGallery(photoData) {
        this.photosData.push({
            url: photoData.url,
            thumbnail_url: photoData.thumbnail_url || photoData.url,
            path: photoData.path,
            filename: photoData.filename,
            size: photoData.size,
            original_url: photoData.original_url
        });
        
        this.updateGallery();
        this.updateFormFields();
    }
    
    removePhoto(index) {
        if (index >= 0 && index < this.photosData.length) {
            this.photosData.splice(index, 1);
            
            // Корректируем индекс главной фотографии
            if (this.mainPhotoIndex >= index) {
                this.mainPhotoIndex = Math.max(0, this.mainPhotoIndex - 1);
            }
            if (this.mainPhotoIndex >= this.photosData.length) {
                this.mainPhotoIndex = Math.max(0, this.photosData.length - 1);
            }
            
            this.updateGallery();
            this.updateFormFields();
        }
    }
    
    setMainPhoto(index) {
        if (index >= 0 && index < this.photosData.length) {
            this.mainPhotoIndex = index;
            this.updateGallery();
            this.updateFormFields();
        }
    }
    
    clearGallery() {
        this.photosData = [];
        this.mainPhotoIndex = 0;
        this.updateGallery();
        this.updateFormFields();
    }
    
    updateGallery() {
        if (!this.galleryGrid) return;
        
        this.galleryGrid.innerHTML = '';
        
        this.photosData.forEach((photo, index) => {
            const photoItem = this.createPhotoElement(photo, index);
            this.galleryGrid.appendChild(photoItem);
        });
        
        // Обновляем счетчик
        if (this.photoCount) {
            this.photoCount.textContent = this.photosData.length;
        }
        
        // Показываем/скрываем элементы галереи
        const hasPhotos = this.photosData.length > 0;
        const galleryElements = document.querySelectorAll('.photo-gallery, .gallery-header, .gallery-info');
        galleryElements.forEach(el => {
            if (el) el.style.display = hasPhotos ? 'block' : 'none';
        });
    }
    
    createPhotoElement(photo, index) {
        const photoItem = document.createElement('div');
        photoItem.className = `photo-item ${index === this.mainPhotoIndex ? 'main-photo' : ''}`;
        
        photoItem.innerHTML = `
            <img src="${photo.thumbnail_url || photo.url}" 
                 alt="Фото товара ${index + 1}" 
                 loading="lazy"
                 onerror="this.src='/images/placeholder.jpg'">
            
            ${index === this.mainPhotoIndex ? '<div class="main-photo-badge"><i class="fas fa-star"></i></div>' : ''}
            
            <button type="button" class="remove-photo-btn" title="Удалить фото">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="photo-caption">
                ${index === this.mainPhotoIndex ? 'Главное фото' : `Фото ${index + 1}`}
            </div>
        `;
        
        // Обработчики событий
        const img = photoItem.querySelector('img');
        img.addEventListener('click', () => this.setMainPhoto(index));
        
        const removeBtn = photoItem.querySelector('.remove-photo-btn');
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removePhoto(index);
        });
        
        return photoItem;
    }
    
    updateFormFields() {
        // Обновляем скрытые поля формы
        if (this.photosGalleryInput) {
            this.photosGalleryInput.value = JSON.stringify(this.photosData.map(photo => photo.url));
        }
        
        if (this.mainPhotoIndexInput) {
            this.mainPhotoIndexInput.value = this.mainPhotoIndex;
        }
        
        // Обновляем старое поле photo_url для совместимости
        if (this.photoUrlHidden) {
            const mainPhoto = this.photosData[this.mainPhotoIndex];
            this.photoUrlHidden.value = mainPhoto ? mainPhoto.url : '';
        }
    }
    
    // Инициализация из существующих данных (для редактирования)
    loadExistingPhotos(photosData, mainIndex = 0) {
        if (Array.isArray(photosData)) {
            this.photosData = photosData.map(url => ({
                url: url,
                thumbnail_url: url,
                path: null,
                filename: null,
                size: null
            }));
            this.mainPhotoIndex = Math.max(0, Math.min(mainIndex, this.photosData.length - 1));
            this.updateGallery();
            this.updateFormFields();
        }
    }
    
    // Утилиты
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    
    showLoading(show) {
        // Показать/скрыть индикатор загрузки
        if (this.addPhotoBtn) {
            this.addPhotoBtn.disabled = show;
            this.addPhotoBtn.textContent = show ? 'Загрузка...' : 'Добавить';
        }
        
        if (this.selectFilesBtn) {
            this.selectFilesBtn.disabled = show;
        }
    }
    
    showError(message) {
        console.error('PhotoGallery Error:', message);
        // Можно добавить toast уведомления
        alert(message);
    }
    
    showSuccess(message) {
        console.log('PhotoGallery Success:', message);
        // Можно добавить toast уведомления
    }
}

// Автоинициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('photo-dropzone')) {
        window.productPhotoGallery = new ProductPhotoGallery({
            maxPhotos: 5
        });
        
        // Загружаем существующие фото для редактирования
        const existingPhotos = document.getElementById('existing_photos_data');
        const existingMainIndex = document.getElementById('existing_main_photo_index');
        
        if (existingPhotos && existingPhotos.textContent.trim()) {
            try {
                const photosData = JSON.parse(existingPhotos.textContent);
                const mainIndex = existingMainIndex ? parseInt(existingMainIndex.textContent) : 0;
                window.productPhotoGallery.loadExistingPhotos(photosData, mainIndex);
            } catch (error) {
                console.error('Ошибка загрузки существующих фотографий:', error);
            }
        }
    }
});