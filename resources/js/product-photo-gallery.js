/**
 * Галерея фотографий для товаров - упрощенная версия (только URL)
 */

class ProductPhotoGallery {
    constructor(options = {}) {
        this.maxPhotos = options.maxPhotos || 5;
        
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
        
        // Очистка галереи
        if (this.clearGalleryBtn) {
            this.clearGalleryBtn.addEventListener('click', () => {
                if (confirm('Удалить все фотографии?')) {
                    this.clearGallery();
                }
            });
        }
    }
    
    async handleUrlAdd() {
        const url = this.photoUrlInput.value.trim();
        
        if (!url) {
            this.showError('Введите URL изображения');
            return;
        }
        
        if (this.photosData.length >= this.maxPhotos) {
            this.showError(`Максимум ${this.maxPhotos} фотографий`);
            return;
        }
        
        if (!this.isValidImageUrl(url)) {
            this.showError('Неверный формат URL изображения');
            return;
        }
        
        // Проверяем, что URL еще не добавлен
        if (this.photosData.some(photo => photo.url === url)) {
            this.showError('Это изображение уже добавлено');
            return;
        }
        
        // Добавляем фото
        this.addPhotoToGallery({
            url: url,
            thumbnail_url: url
        });
        
        this.photoUrlInput.value = '';
        this.showSuccess('Фото добавлено');
    }
    
    isValidImageUrl(url) {
        try {
            const urlObj = new URL(url);
            // Проверяем расширение
            const path = urlObj.pathname.toLowerCase();
            return /\.(jpg|jpeg|png|gif|webp|bmp|svg)(\?.*)?$/i.test(path) || 
                   urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
        } catch {
            return false;
        }
    }
    
    addPhotoToGallery(photoData) {
        this.photosData.push(photoData);
        
        // Если это первое фото, делаем его главным
        if (this.photosData.length === 1) {
            this.mainPhotoIndex = 0;
        }
        
        this.updateGallery();
        this.updateFormFields();
    }
    
    removePhoto(index) {
        this.photosData.splice(index, 1);
        
        // Корректируем индекс главной фотографии
        if (this.mainPhotoIndex >= this.photosData.length) {
            this.mainPhotoIndex = Math.max(0, this.photosData.length - 1);
        }
        
        this.updateGallery();
        this.updateFormFields();
    }
    
    setMainPhoto(index) {
        this.mainPhotoIndex = index;
        this.updateGallery();
        this.updateFormFields();
    }
    
    clearGallery() {
        this.photosData = [];
        this.mainPhotoIndex = 0;
        this.updateGallery();
        this.updateFormFields();
    }
    
    updateGallery() {
        if (!this.galleryGrid) return;
        
        const galleryHeader = document.querySelector('.gallery-header');
        const galleryInfo = document.querySelector('.gallery-info');
        
        if (this.photosData.length === 0) {
            this.galleryGrid.innerHTML = '';
            if (galleryHeader) galleryHeader.style.display = 'none';
            if (galleryInfo) galleryInfo.style.display = 'none';
        } else {
            if (galleryHeader) galleryHeader.style.display = 'flex';
            if (galleryInfo) galleryInfo.style.display = 'block';
            
            this.galleryGrid.innerHTML = this.photosData.map((photo, index) => 
                this.createPhotoElement(photo, index)
            ).join('');
        }
        
        if (this.photoCount) {
            this.photoCount.textContent = this.photosData.length;
        }
    }
    
    createPhotoElement(photo, index) {
        const isMain = index === this.mainPhotoIndex;
        
        return `
            <div class="photo-item ${isMain ? 'main-photo' : ''}" data-index="${index}">
                <img src="${photo.thumbnail_url || photo.url}" alt="Photo ${index + 1}" 
                     onclick="window.photoGallery.setMainPhoto(${index})"
                     onerror="this.src='/images/placeholder.png'">
                ${isMain ? '<div class="main-photo-badge"><i class="fas fa-star"></i> Главное</div>' : ''}
                <button type="button" class="remove-photo-btn" 
                        onclick="window.photoGallery.removePhoto(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <div class="photo-caption">${isMain ? 'Главное фото' : `Фото ${index + 1}`}</div>
            </div>
        `;
    }
    
    updateFormFields() {
        // Обновляем скрытые поля формы
        if (this.photosGalleryInput) {
            this.photosGalleryInput.value = JSON.stringify(this.photosData);
        }
        
        if (this.mainPhotoIndexInput) {
            this.mainPhotoIndexInput.value = this.mainPhotoIndex;
        }
        
        // Устанавливаем главное фото в photo_url
        if (this.photoUrlHidden) {
            this.photoUrlHidden.value = this.photosData.length > 0 
                ? this.photosData[this.mainPhotoIndex]?.url || '' 
                : '';
        }
    }
    
    // Инициализация из существующих данных (для редактирования)
    loadExistingPhotos(photosData, mainIndex = 0) {
        if (!photosData || photosData.length === 0) return;
        
        this.photosData = Array.isArray(photosData) ? photosData : [];
        this.mainPhotoIndex = mainIndex;
        
        if (this.mainPhotoIndex >= this.photosData.length) {
            this.mainPhotoIndex = 0;
        }
        
        this.updateGallery();
        this.updateFormFields();
    }
    
    // Утилиты
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
    
    showError(message) {
        console.error('PhotoGallery Error:', message);
        alert(message);
    }
    
    showSuccess(message) {
        console.log('PhotoGallery Success:', message);
    }
}

// Автоинициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('gallery-grid')) {
        // Создаем экземпляр галереи
        window.photoGallery = new ProductPhotoGallery();
        
        // Загружаем существующие данные для страницы редактирования
        const existingPhotosScript = document.getElementById('existing_photos_data');
        const existingMainPhotoIndex = document.getElementById('existing_main_photo_index');
        
        if (existingPhotosScript) {
            try {
                const photosData = JSON.parse(existingPhotosScript.textContent);
                const mainIndex = existingMainPhotoIndex 
                    ? parseInt(existingMainPhotoIndex.textContent) 
                    : 0;
                
                window.photoGallery.loadExistingPhotos(photosData, mainIndex);
            } catch (e) {
                console.error('Ошибка загрузки существующих фотографий:', e);
            }
        }
    }
});
