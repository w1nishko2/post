/**
 * Компонент галереи изображений для товаров
 * Поддержка: загрузка файлов, drag & drop, превью, сортировка, выбор главной фотографии
 */

class ProductImageGallery {
    constructor(options = {}) {
        console.log('Инициализация ProductImageGallery с опциями:', options);
        
        // Определяем базовый URL для API (относительно текущей страницы или абсолютный)
        let baseUrl = options.uploadUrl || '/products/images/upload';
        
        this.options = {
            maxImages: options.maxImages || 5,
            uploadUrl: baseUrl,
            deleteUrl: options.deleteUrl || '/products/images',
            setMainUrl: options.setMainUrl || '/products/images',
            updateOrderUrl: options.updateOrderUrl || '/products/images/update-order',
            productId: options.productId || null,
            csrfToken: options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content,
            ...options
        };

        console.log('Финальные опции галереи:', this.options);

        this.images = [];
        this.pendingImages = []; // Для хранения превью до загрузки
        this.mainImageIndex = 0;
        this.initElements();
        this.bindEvents();
        this.loadExistingImages();
    }

    initElements() {
        console.log('Инициализация элементов галереи...');
        
        // Поддержка как новых, так и старых ID для совместимости
        this.dropzone = document.getElementById('image-dropzone') || document.getElementById('photo-dropzone');
        this.fileInput = document.getElementById('image-file-input') || document.getElementById('file-input');
        this.selectFilesBtn = document.getElementById('select-images-btn') || document.getElementById('select-files-btn');
        this.gallery = document.getElementById('image-gallery') || document.getElementById('photo-gallery');
        this.galleryGrid = document.getElementById('image-gallery-grid') || document.getElementById('gallery-grid');
        this.galleryHeader = document.getElementById('gallery-header');
        this.imageCount = document.getElementById('image-count') || document.getElementById('photo-count');
        this.clearAllBtn = document.getElementById('clear-all-images') || document.getElementById('clear-gallery-btn');
        this.uploadProgress = document.getElementById('upload-progress');
        
        console.log('Найденные элементы:', {
            dropzone: !!this.dropzone,
            fileInput: !!this.fileInput,
            selectFilesBtn: !!this.selectFilesBtn,
            gallery: !!this.gallery,
            galleryGrid: !!this.galleryGrid,
            galleryHeader: !!this.galleryHeader,
            imageCount: !!this.imageCount,
            clearAllBtn: !!this.clearAllBtn,
            uploadProgress: !!this.uploadProgress
        });
    }

    bindEvents() {
        // Кнопка выбора файлов
        if (this.selectFilesBtn) {
            console.log('Кнопка выбора файлов найдена:', this.selectFilesBtn);
            this.selectFilesBtn.addEventListener('click', () => {
                console.log('Клик по кнопке выбора файлов');
                if (this.fileInput) {
                    console.log('Открываем диалог выбора файлов');
                    this.fileInput.click();
                } else {
                    console.error('Input для выбора файлов не найден!');
                }
            });
        } else {
            console.warn('Кнопка выбора файлов не найдена! Проверьте ID: select-images-btn или select-files-btn');
        }

        // Выбор файлов
        if (this.fileInput) {
            console.log('Input для файлов найден:', this.fileInput);
            this.fileInput.addEventListener('change', (e) => {
                console.log('Файлы выбраны:', e.target.files);
                this.handleFiles(e.target.files);
            });
        } else {
            console.warn('Input для файлов не найден! Проверьте ID: image-file-input или file-input');
        }

        // Drag & Drop
        if (this.dropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.dropzone.addEventListener(eventName, this.preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                this.dropzone.addEventListener(eventName, () => {
                    this.dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                this.dropzone.addEventListener(eventName, () => {
                    this.dropzone.classList.remove('dragover');
                }, false);
            });

            this.dropzone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                this.handleFiles(files);
            }, false);
        }

        // Кнопка очистки всех
        if (this.clearAllBtn) {
            this.clearAllBtn.addEventListener('click', () => {
                if (confirm('Вы уверены, что хотите удалить все изображения?')) {
                    this.clearAll();
                }
            });
        }
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    async handleFiles(files) {
        const filesArray = Array.from(files);

        // Проверка количества
        const totalImages = this.images.length + this.pendingImages.length + filesArray.length;
        if (totalImages > this.options.maxImages) {
            this.showMessage('error', `Максимальное количество изображений: ${this.options.maxImages}`);
            return;
        }

        // Валидация файлов
        const validFiles = filesArray.filter(file => {
            if (!file.type.startsWith('image/')) {
                this.showMessage('error', `Файл "${file.name}" не является изображением`);
                return false;
            }
            if (file.size > 10 * 1024 * 1024) {
                this.showMessage('error', `Файл "${file.name}" слишком большой (макс. 10MB)`);
                return false;
            }
            return true;
        });

        if (validFiles.length === 0) return;

        // Создаем превью для файлов
        for (const file of validFiles) {
            await this.createPreview(file);
        }

        // Если товар уже создан, загружаем файлы на сервер
        if (this.options.productId && this.options.productId !== 'null' && this.options.productId !== '') {
            await this.uploadFiles(validFiles);
        } else {
            // Иначе только показываем превью
            this.render();
        }
    }

    async createPreview(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const preview = {
                    id: 'pending_' + Date.now() + '_' + Math.random(),
                    file: file,
                    thumbnail_url: e.target.result,
                    url: e.target.result,
                    is_main: this.images.length === 0 && this.pendingImages.length === 0,
                    is_pending: true,
                    original_name: file.name,
                    file_size: file.size
                };
                
                this.pendingImages.push(preview);
                
                // Также добавляем в основной массив images для отображения
                this.images.push(preview);
                this.render();
                this.updateHiddenFields(); // Обновляем скрытые поля с локальными URL
                
                resolve();
            };
            
            reader.readAsDataURL(file);
        });
    }

    async uploadFiles(files) {
        // Если товар еще не создан, обрабатываем как локальные превью
        if (!this.options.productId || this.options.productId === 'null' || this.options.productId === '') {
            console.log('Товар еще не создан, сохраняем изображения локально');
            
            // Обрабатываем файлы как локальные превью - уже сделано в createPreview
            // Просто показываем информационное сообщение
            this.showMessage('info', 'Изображения добавлены. Они будут сохранены при создании товара.');
            
            return;
        }

        const formData = new FormData();
        formData.append('product_id', this.options.productId);
        
        files.forEach((file, index) => {
            formData.append(`images[${index}]`, file);
        });

        try {
            this.showProgress();
            
            console.log('Загрузка файлов на URL:', this.options.uploadUrl);
            console.log('Product ID:', this.options.productId);
            console.log('CSRF Token:', this.options.csrfToken);
            
            const response = await fetch(this.options.uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            console.log('Ответ сервера:', response.status, response.statusText);

            // Проверяем тип контента ответа
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Ошибка сервера (текст):', errorText.substring(0, 500));
                
                // Пытаемся распарсить как JSON
                try {
                    const errorJson = JSON.parse(errorText);
                    this.showMessage('error', errorJson.message || 'Ошибка загрузки изображений');
                } catch (e) {
                    // Если не JSON, значит это HTML страница ошибки
                    this.showMessage('error', `Ошибка сервера: ${response.status} ${response.statusText}`);
                }
                return;
            }

            const result = await response.json();
            console.log('Результат загрузки:', result);

            if (result.success) {
                // Очищаем pending изображения после успешной загрузки
                this.pendingImages = [];
                
                result.images.forEach(image => {
                    this.addImage(image);
                });
                
                if (result.errors && result.errors.length > 0) {
                    console.warn('Некоторые файлы не были загружены:', result.errors);
                }
                
                this.showMessage('success', result.message);
            } else {
                this.showMessage('error', result.message || 'Ошибка загрузки изображений');
            }

        } catch (error) {
            console.error('Ошибка загрузки:', error);
            this.showMessage('error', 'Произошла ошибка при загрузке изображений: ' + error.message);
        } finally {
            this.hideProgress();
        }
    }

    addImage(imageData) {
        this.images.push(imageData);
        
        if (imageData.is_main) {
            this.mainImageIndex = this.images.length - 1;
        }
        
        this.render();
        this.updateHiddenFields(); // Обновляем скрытые поля формы
    }

    async deleteImage(imageId, index) {
        if (!confirm('Вы уверены, что хотите удалить это изображение?')) {
            return;
        }

        try {
            const response = await fetch(`${this.options.deleteUrl}/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.images.splice(index, 1);
                
                // Корректируем индекс главного изображения
                if (this.mainImageIndex >= index) {
                    this.mainImageIndex = Math.max(0, this.mainImageIndex - 1);
                }
                if (this.mainImageIndex >= this.images.length) {
                    this.mainImageIndex = Math.max(0, this.images.length - 1);
                }
                
                this.render();
                this.updateHiddenFields(); // Обновляем скрытые поля формы
                this.showMessage('success', result.message);
            } else {
                this.showMessage('error', result.message || 'Ошибка удаления изображения');
            }

        } catch (error) {
            console.error('Ошибка удаления:', error);
            this.showMessage('error', 'Произошла ошибка при удалении изображения');
        }
    }

    async setMainImage(imageId, index) {
        console.log('setMainImage вызван:', { imageId, index, url: `${this.options.setMainUrl}/${imageId}/set-main` });
        
        // Проверка наличия productId
        if (!this.options.productId) {
            console.warn('ProductId не установлен, изображения будут сохранены после создания товара');
            this.showMessage('warning', 'Сначала сохраните товар, затем установите главное изображение');
            return;
        }
        
        try {
            const url = `${this.options.setMainUrl}/${imageId}/set-main`;
            console.log('Отправка POST запроса на:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('Статус ответа:', response.status, response.statusText);
            const responseText = await response.text();
            console.log('Тело ответа (текст):', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
                this.showMessage('error', 'Сервер вернул некорректный ответ');
                return;
            }

            console.log('Результат (JSON):', result);

            if (result.success) {
                // Обновляем локальное состояние
                this.images.forEach((img, i) => {
                    img.is_main = (i === index);
                });
                this.mainImageIndex = index;
                
                this.render();
                this.updateHiddenFields(); // Обновляем скрытые поля формы
                this.showMessage('success', result.message);
            } else {
                this.showMessage('error', result.message || 'Ошибка установки главного изображения');
            }

        } catch (error) {
            console.error('Ошибка установки главного изображения:', error);
            this.showMessage('error', 'Произошла ошибка при установке главного изображения');
        }
    }

    async clearAll() {
        const imagesToDelete = [...this.images];
        
        for (const image of imagesToDelete) {
            try {
                await fetch(`${this.options.deleteUrl}/${image.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.options.csrfToken
                    }
                });
            } catch (error) {
                console.error('Ошибка удаления изображения:', error);
            }
        }

        this.images = [];
        this.pendingImages = [];
        this.mainImageIndex = 0;
        this.render();
        this.updateHiddenFields(); // Обновляем скрытые поля формы
        this.showMessage('success', 'Все изображения удалены');
    }

    loadExistingImages() {
        if (!this.options.productId || this.options.productId === 'null' || this.options.productId === '') {
            console.log('Товар еще не создан, пропускаем загрузку существующих изображений');
            return;
        }

        const url = `/products/${this.options.productId}/images`;
        console.log('Загрузка существующих изображений с URL:', url);

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': this.options.csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Ответ при загрузке существующих изображений:', response.status);
            
            if (!response.ok) {
                console.warn('Не удалось загрузить существующие изображения:', response.status, response.statusText);
                return null;
            }
            
            return response.json();
        })
        .then(result => {
            if (result && result.success && result.images) {
                this.images = result.images;
                this.mainImageIndex = this.images.findIndex(img => img.is_main);
                if (this.mainImageIndex === -1) this.mainImageIndex = 0;
                console.log('Загружено изображений:', this.images.length);
                this.render();
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки существующих изображений:', error);
        });
    }

    render() {
        if (!this.galleryGrid) return;

        const totalImages = this.images.length + this.pendingImages.length;

        // Обновляем счетчик
        if (this.imageCount) {
            this.imageCount.textContent = totalImages;
        }

        // Показываем/скрываем заголовок галереи (если есть отдельный элемент)
        if (this.galleryHeader) {
            this.galleryHeader.style.display = totalImages > 0 ? 'flex' : 'none';
        }
        
        // Для старого варианта с .gallery-header внутри .photo-gallery
        const oldGalleryHeader = this.gallery?.querySelector('.gallery-header');
        if (oldGalleryHeader) {
            oldGalleryHeader.style.display = totalImages > 0 ? 'flex' : 'none';
        }

        // Показываем/скрываем галерею
        if (this.gallery) {
            this.gallery.style.display = totalImages > 0 ? 'block' : 'none';
        }

        // Показываем/скрываем dropzone
        if (this.dropzone) {
            this.dropzone.style.display = totalImages >= this.options.maxImages ? 'none' : 'flex';
        }

        // Очищаем и заполняем галерею
        this.galleryGrid.innerHTML = '';

        // Сначала добавляем загруженные изображения
        this.images.forEach((image, index) => {
            const imageCard = this.createImageCard(image, index, false);
            this.galleryGrid.appendChild(imageCard);
        });

        // Затем добавляем ожидающие загрузки
        this.pendingImages.forEach((image, index) => {
            const imageCard = this.createImageCard(image, index, true);
            this.galleryGrid.appendChild(imageCard);
        });
        
        // Показываем/скрываем информацию о галерее
        const galleryInfo = this.gallery?.querySelector('.gallery-info');
        if (galleryInfo) {
            galleryInfo.style.display = totalImages > 0 ? 'block' : 'none';
        }
    }

    createImageCard(image, index, isPending = false) {
        const card = document.createElement('div');
        card.className = 'image-card';
        if (image.is_main) {
            card.classList.add('main-image');
        }
        if (isPending) {
            card.classList.add('pending-upload');
            card.style.opacity = '0.7';
        }

        const displayIndex = this.images.length + index + 1;
        const statusBadge = isPending ? '<div class="pending-badge" style="position: absolute; top: 0; right: 0; background: #ffc107; color: #000; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-bottom-left-radius: 8px;"><i class="fas fa-clock"></i> Ожидание</div>' : '';
        
        card.innerHTML = `
            <div class="image-wrapper">
                <img src="${image.thumbnail_url || image.url}" alt="Изображение ${displayIndex}" loading="lazy">
                ${image.is_main ? '<div class="main-badge"><i class="fas fa-star"></i> Главная</div>' : ''}
                ${statusBadge}
                <div class="image-overlay">
                    ${!isPending ? `<button type="button" class="image-action-btn set-main-btn" data-index="${index}" data-id="${image.id}" title="Сделать главной">
                        <i class="fas fa-star"></i>
                    </button>` : ''}
                    <button type="button" class="image-action-btn delete-btn" data-index="${index}" data-id="${image.id}" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="image-info">
                <small class="text-muted">${isPending ? 'Ожидает сохранения товара' : 'Изображение ' + displayIndex}</small>
            </div>
        `;

        // Привязываем события
        const setMainBtn = card.querySelector('.set-main-btn');
        const deleteBtn = card.querySelector('.delete-btn');

        if (setMainBtn && !isPending) {
            setMainBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Клик по кнопке "Сделать главной":', { imageId: image.id, index });
                this.setMainImage(image.id, index);
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                if (isPending) {
                    this.removePendingImage(index);
                } else {
                    this.deleteImage(image.id, index);
                }
            });
        }

        return card;
    }

    removePendingImage(index) {
        this.pendingImages.splice(index, 1);
        this.render();
    }

    showProgress() {
        if (this.uploadProgress) {
            this.uploadProgress.style.display = 'block';
        }
    }

    hideProgress() {
        if (this.uploadProgress) {
            this.uploadProgress.style.display = 'none';
        }
    }

    showMessage(type, message) {
        // Можно использовать toast-уведомления или alert
        console.log(`[${type}] ${message}`);
        
        // Создаем временное уведомление
        const alert = document.createElement('div');
        let alertClass = 'danger';
        if (type === 'success') alertClass = 'success';
        if (type === 'warning') alertClass = 'warning';
        
        alert.className = `alert alert-${alertClass} alert-dismissible fade show`;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => alert.remove());
        }
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    /**
     * Обновить скрытые поля формы с данными галереи
     */
    updateHiddenFields() {
        console.log('Обновление скрытых полей формы...');
        
        // Получаем скрытые поля
        const photosGalleryInput = document.getElementById('photos_gallery_input');
        const mainPhotoIndexInput = document.getElementById('main_photo_index_input');
        const photoUrlHidden = document.getElementById('photo_url_hidden');
        
        if (!photosGalleryInput) {
            console.warn('Поле photos_gallery_input не найдено');
            return;
        }
        
        // Собираем URL изображений
        const imageUrls = this.images.map(img => img.url || img.file_path);
        
        // Записываем в скрытое поле photos_gallery как JSON
        photosGalleryInput.value = JSON.stringify(imageUrls);
        
        // Записываем индекс главной фотографии
        if (mainPhotoIndexInput) {
            mainPhotoIndexInput.value = this.mainImageIndex;
        }
        
        // Записываем URL главной фотографии в photo_url
        if (photoUrlHidden && imageUrls.length > 0) {
            photoUrlHidden.value = imageUrls[this.mainImageIndex] || imageUrls[0] || '';
        } else if (photoUrlHidden) {
            photoUrlHidden.value = '';
        }
        
        console.log('Скрытые поля обновлены:', {
            photos_gallery: photosGalleryInput.value,
            main_photo_index: mainPhotoIndexInput?.value,
            photo_url: photoUrlHidden?.value,
            images_count: imageUrls.length
        });
    }
}

// Автоматическая инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализируем галерею изображений...');
    
    // Проверяем наличие нового контейнера галереи
    const galleryContainer = document.getElementById('product-image-gallery-container');
    
    if (galleryContainer) {
        console.log('Найден новый контейнер галереи');
        const productId = galleryContainer.dataset.productId;
        
        window.productImageGallery = new ProductImageGallery({
            productId: productId,
            maxImages: 5
        });
    } else {
        console.log('Новый контейнер галереи не найден, ищем старый вариант...');
        
        // Проверяем наличие старого варианта галереи
        const oldDropzone = document.getElementById('photo-dropzone');
        const oldGallery = document.getElementById('photo-gallery');
        
        console.log('Старые элементы:', { dropzone: !!oldDropzone, gallery: !!oldGallery });
        
        if (oldDropzone || oldGallery) {
            console.log('Найден старый вариант галереи, инициализируем...');
            
            // Получаем productId из data-атрибута формы или другого источника
            const form = document.querySelector('form');
            let productId = null;
            
            if (form) {
                const productIdInput = form.querySelector('input[name="product_id"]');
                if (productIdInput) {
                    productId = productIdInput.value;
                    console.log('ProductId найден в скрытом поле:', productId);
                } else {
                    // Пытаемся извлечь из URL (для страницы редактирования)
                    const urlMatch = window.location.pathname.match(/\/products\/(\d+)/);
                    if (urlMatch) {
                        productId = urlMatch[1];
                        console.log('ProductId извлечен из URL:', productId);
                    } else {
                        console.log('ProductId не найден (товар еще не создан)');
                    }
                }
            }
            
            window.productImageGallery = new ProductImageGallery({
                productId: productId,
                maxImages: 5
            });
        } else {
            console.warn('Галерея изображений не найдена на странице!');
        }
    }
});
