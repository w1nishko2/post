// Mini App JavaScript functionality
console.log('Mini App загружается...');

// Переменные для отладки и разработки
const isDevelopmentMode = !window.Telegram?.WebApp;
let userData = null;
let isAppInitialized = false; // Флаг для предотвращения повторной инициализации

// Переменные для поиска и категорий
let allProducts = [];
let allCategories = [];
let isSearchActive = false;
let isInCategoryView = false; // Флаг для отслеживания просмотра категории
let isScrollLocked = false; // Флаг блокировки скролла для предотвращения сворачивания

// Переменные для контроля Swiper
let swiperReinitCount = 0;
let lastSwiperReinitTime = 0;
const SWIPER_REINIT_COOLDOWN = 500; // минимальная задержка между переинициализациями
const MAX_SWIPER_REINIT_ATTEMPTS = 3; // максимальное количество попыток переинициализации подряд

// Функция для экранирования HTML (защита от XSS)
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { 
        return map[m]; 
    });
}

// Функция для обработки ошибок загрузки изображений
function handleImageError(img, placeholderSelector) {
    img.style.display = 'none';
    const placeholder = img.nextElementSibling || document.querySelector(placeholderSelector);
    if (placeholder) {
        placeholder.style.display = 'flex';
        placeholder.title = 'Изображение недоступно';
    }
    console.warn('Ошибка загрузки изображения:', img.src);
}

// Функция для получения CSRF токена
function getCSRFToken() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    return csrfMeta ? csrfMeta.getAttribute('content') : null;
}

// Безопасная функция для fetch запросов с CSRF защитой
function secureFetch(url, options = {}) {
    const token = getCSRFToken();
    
    // Добавляем CSRF токен для POST/PUT/DELETE запросов
    if (options.method && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method.toUpperCase())) {
        options.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        };
        
        if (token) {
            options.headers['X-CSRF-TOKEN'] = token;
        }
    }
    
    return fetch(url, options);
}

// Основная функция инициализации
function initApp() {
    // Предотвращаем повторную инициализацию
    if (isAppInitialized) {
        console.log('Mini App уже инициализирован, пропускаем');
        return;
    }
    
    console.log('Инициализация Mini App...');
    isAppInitialized = true;
    
    // Максимальное время загрузки - 3 секунды
    const maxLoadTime = setTimeout(() => {
        console.log('Принудительно показываем приложение после тайм-аута');
        try {
            const loadingEl = document.getElementById('loading');
            const appEl = document.getElementById('app');
            
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            if (appEl) {
                appEl.style.display = 'block';
            }
        } catch (error) {
            console.error('Ошибка при принудительном показе приложения:', error);
        }
    }, 3000);
    
    // Инициализация Telegram WebApp
    if (window.Telegram && window.Telegram.WebApp) {
        const tg = window.Telegram.WebApp;
        
        try {
            tg.ready();
            tg.expand();
            
            console.log('Telegram WebApp инициализирован');
            console.log('Init data:', tg.initData);
            console.log('User data:', tg.initDataUnsafe?.user);
            
            // Получаем данные пользователя
            if (tg.initDataUnsafe && tg.initDataUnsafe.user) {
                userData = tg.initDataUnsafe.user;
                displayUserInfo(userData);
            } else if (tg.initData) {
                // Попытка парсинга initData
                userData = parseUserFromInitData(tg.initData);
                if (userData) {
                    displayUserInfo(userData);
                }
            }
            
            // Настройка темы и цветов
            document.documentElement.style.setProperty('--tg-color-scheme', tg.colorScheme);
            document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color || '#ffffff');
            document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color || '#000000');
            
            // Настраиваем поведение скролла для предотвращения сворачивания
            setupScrollBehavior();
            
            // Скрытие кнопки "Назад" (только для поддерживаемых версий)
            if (tg.version && parseFloat(tg.version) >= 6.1 && tg.BackButton) {
                try {
                    if (typeof tg.BackButton.hide === 'function') {
                        tg.BackButton.hide();
                        console.log('BackButton hidden for WebApp version', tg.version);
                    }
                } catch (e) {
                    console.log('BackButton control not supported in version', tg.version, ':', e.message);
                }
            } else {
                console.log('BackButton not available in WebApp version', tg.version || 'unknown');
            }
            
            console.log('Telegram WebApp полностью настроен');
            
        } catch (error) {
            console.error('Ошибка инициализации Telegram WebApp:', error);
            showErrorMessage('Ошибка загрузки Telegram WebApp');
        }
    } else {
        console.log('Режим разработки - Telegram WebApp недоступен');
        userData = {
            id: 123456789,
            first_name: 'Тестовый',
            last_name: 'Пользователь',
            username: 'testuser'
        };
        displayUserInfo(userData);
    }
    
    // Инициализация поиска и категорий асинхронно
    try {
        initSearch();
        loadCategories().catch(error => {
            console.error('Ошибка загрузки категорий:', error);
        });
    } catch (error) {
        console.error('Ошибка инициализации поиска/категорий:', error);
    }
    
    // Настройка обработчиков модальных окон
    try {
        // setupModalBackdropHandlers(); // Временно отключаем, перенесем в конец
    } catch (error) {
        console.error('Ошибка настройки модальных окон:', error);
    }
    
    // Скрыть загрузочный экран независимо от загрузки данных
    setTimeout(() => {
        try {
            clearTimeout(maxLoadTime); // Очищаем таймер принудительной загрузки
            
            const loadingEl = document.getElementById('loading');
            const appEl = document.getElementById('app');
            
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            if (appEl) {
                appEl.style.display = 'block';
            }
            
            console.log('Mini App загружен успешно');
            
            // Настройка модальных окон после полной загрузки
            setTimeout(() => {
                try {
                    setupModalBackdropHandlers();
                } catch (error) {
                    console.error('Ошибка настройки модальных окон:', error);
                }
            }, 100);
            
            // Обработчик изменения размера окна для кнопки "Назад"
            window.addEventListener('resize', handleBackButtonVisibility);
            
        } catch (error) {
            console.error('Ошибка при скрытии загрузочного экрана:', error);
        }
    }, 800);
}

// Показать сообщение об ошибке
function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `;
    errorDiv.innerHTML = `
        <strong>Ошибка:</strong> ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

// Отобразить информацию о пользователе
function displayUserInfo(user) {
    console.log('Данные пользователя:', user);
    
    // Можно добавить отображение имени пользователя в интерфейс
    const userGreeting = document.querySelector('.user-greeting');
    if (userGreeting && user) {
        const userName = user.first_name || user.username || 'Пользователь';
        userGreeting.textContent = `Привет, ${userName}!`;
    }
}

// Парсинг пользователя из initData
function parseUserFromInitData(initData) {
    try {
        const params = new URLSearchParams(initData);
        const userParam = params.get('user');
        if (userParam) {
            return JSON.parse(decodeURIComponent(userParam));
        }
    } catch (error) {
        console.error('Ошибка парсинга user из initData:', error);
    }
    return null;
}

// Показать уведомление с проверкой совместимости
function showAlert(message, type = 'info') {
    // Попытка использовать Telegram WebApp уведомления
    if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.showAlert) {
        try {
            window.Telegram.WebApp.showAlert(message);
            return;
        } catch (error) {
            console.warn('Не удалось показать Telegram уведомление:', error);
        }
    }
    
    // Fallback на toast уведомления
    showToast(message, type);
}

// Toast уведомления как fallback
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center  bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Инициализация Bootstrap toast
    if (window.bootstrap && window.bootstrap.Toast) {
        const bsToast = new window.bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    } else {
        // Простой показ без Bootstrap
        toast.style.display = 'block';
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Feature detection for CSS Grid
function detectCSSGrid() {
    if (!CSS.supports || !CSS.supports('display', 'grid')) {
        document.documentElement.classList.add('no-cssgrid');
    }
}

// Инициализация поиска
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    let searchTimeout;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(e.target.value);
        }, 300); // Задержка для предотвращения частых запросов
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch(e.target.value);
        }
    });

    // Загружаем все товары для поиска
    loadAllProducts();
}

// Загрузка всех товаров для поиска
async function loadAllProducts() {
    try {
        // Сначала попытаемся получить данные из встроенного JSON
        const productsDataElement = document.getElementById('products-data');
        if (productsDataElement) {
            try {
                const productsData = JSON.parse(productsDataElement.textContent);
                allProducts = Object.values(productsData);
                console.log('Загружено товаров из встроенных данных:', allProducts.length);
                return;
            } catch (e) {
                console.warn('Ошибка парсинга встроенных данных товаров:', e);
            }
        }

        // Если встроенные данные недоступны, загружаем через API
        const shortName = document.querySelector('meta[name="short-name"]')?.content || 
                         window.location.pathname.split('/')[1];
        const response = await fetch(`/${shortName}/api/products`);
        
        if (response.ok) {
            allProducts = await response.json();
            console.log('Загружено товаров через API:', allProducts.length);
        }
    } catch (error) {
        console.error('Ошибка при загрузке товаров:', error);
    }
}

// Загрузка категорий
async function loadCategories() {
    try {
        const shortName = document.querySelector('meta[name="short-name"]')?.content || 
                         window.location.pathname.split('/')[1];
        
        // Добавляем тайм-аут для запроса
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 секунд тайм-аут
        
        const response = await fetch(`/${shortName}/api/categories`, {
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        clearTimeout(timeoutId);
        
        if (response.ok) {
            allCategories = await response.json();
            console.log('Загружено категорий:', allCategories.length);
            console.log('Данные категорий:', allCategories);
            
            if (allCategories.length > 0) {
                const categoriesContainer = document.getElementById('categoriesContainer');
                if (categoriesContainer) {
                    categoriesContainer.style.display = 'block';
                }
                renderCategories(allCategories);
            }
        } else {
            console.log('Категории не найдены или ошибка загрузки:', response.status);
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.log('Загрузка категорий прервана по тайм-ауту');
        } else {
            console.error('Ошибка при загрузке категорий:', error);
        }
    }
}

// Отрисовка категорий с ленивой загрузкой
function renderCategories(categories) {
    console.log('Отрисовка категорий:', categories);
    const track = document.getElementById('categoriesTrack');
    if (!track) {
        console.error('Элемент categoriesTrack не найден');
        return;
    }
    
    if (categories.length === 0) {
        const categoriesContainer = document.getElementById('categoriesContainer');
        if (categoriesContainer) {
            categoriesContainer.style.display = 'none';
        }
        return;
    }

    // Сохраняем данные категорий глобально для ленивой загрузки
    window.allCategoriesData = categories;
    
    // Рендерим только первые 8 категорий, остальные - пустые слайды-заглушки
    const initialLoadCount = 8;
    
    track.innerHTML = categories.map((category, index) => {
        const shouldRender = index < initialLoadCount;
        
        if (shouldRender) {
            // Рендерим полную карточку
            return `
            <div class="category-card" 
                 data-category-id="${category.id}" 
                 data-category-name="${escapeHtml(category.name)}"
                 data-index="${index}"
                 data-loaded="true">
                <div class="card h-200">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="category-info">
                                <div class="category-name">${escapeHtml(category.name)}</div>
                                ${category.description ? `<div class="category-description">${escapeHtml(category.description)}</div>` : ''}
                                <div class="category-products-count">${category.products_count || 0} товаров</div>
                            </div>
                            ${category.photo_url 
                                ? `<img src="${escapeHtml(category.photo_url)}" class="category-image" alt="${escapeHtml(category.name)}" onerror="handleImageError(this)" loading="eager">
                                   <div class="category-placeholder" style="display: none;">
                                       <i class="fas fa-folder"></i>
                                       <span class="placeholder-text">Изображение недоступно</span>
                                   </div>`
                                : `<div class="category-placeholder">
                                       <i class="fas fa-folder"></i>
                                   </div>`
                            }
                        </div>
                    </div>
                </div>
            </div>`;
        } else {
            // Рендерим заглушку для ленивой загрузки
            return `
            <div class="category-card category-skeleton" 
                 data-index="${index}"
                 data-loaded="false">
                <div class="card h-200">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="category-info">
                                <div class="skeleton-line skeleton-title"></div>
                                <div class="skeleton-line skeleton-count"></div>
                            </div>
                            <div class="skeleton-image">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }
    }).join('');
    
    // Добавляем обработчик кликов только для загруженных категорий
    const categoryCards = track.querySelectorAll('.category-card[data-loaded="true"]');
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            if (categoryId && categoryName) {
                filterByCategory(parseInt(categoryId), categoryName);
            }
        });
    });
    
    console.log('Категории отрисованы, запуск переинициализации Swiper');
    
    // Переинициализируем Swiper (ленивая загрузка запустится из Blade после инициализации)
    if (typeof window.reinitCategoriesSwiper === 'function') {
        setTimeout(() => {
            window.reinitCategoriesSwiper();
        }, 100);
    }
}

// Функция для настройки ленивой загрузки категорий
function setupCategoryLazyLoading() {
    const swiperInstance = window.categoriesSwiper;
    
    if (!swiperInstance) {
        console.warn('Swiper не инициализирован для ленивой загрузки');
        return;
    }
    
    if (!swiperInstance.slides || swiperInstance.slides.length === 0) {
        console.warn('Swiper slides не найдены, отложенная инициализация...');
        setTimeout(() => {
            setupCategoryLazyLoading();
        }, 500);
        return;
    }
    
    console.log('Настройка ленивой загрузки слайдов категорий, всего слайдов:', swiperInstance.slides.length);
    
    // Функция загрузки слайда категории
    const loadCategorySlide = (categoryCard) => {
        if (!categoryCard) return;
        if (categoryCard.getAttribute('data-loaded') === 'true') return;
        
        const index = parseInt(categoryCard.getAttribute('data-index'));
        const category = window.allCategoriesData?.[index];
        
        if (!category) return;
        
        console.log(`Загрузка категории ${index}: ${category.name}`);
        
        // Заменяем заглушку на полную карточку
        categoryCard.innerHTML = `
            <div class="card h-200">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${escapeHtml(category.name)}</div>
                            ${category.description ? `<div class="category-description">${escapeHtml(category.description)}</div>` : ''}
                            <div class="category-products-count">${category.products_count || 0} товаров</div>
                        </div>
                        ${category.photo_url 
                            ? `<img src="${escapeHtml(category.photo_url)}" class="category-image" alt="${escapeHtml(category.name)}" onerror="handleImageError(this)" loading="lazy">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                                   <span class="placeholder-text">Изображение недоступно</span>
                               </div>`
                            : `<div class="category-placeholder">
                                   <i class="fas fa-folder"></i>
                               </div>`
                        }
                    </div>
                </div>
            </div>
        `;
        
        // Устанавливаем атрибуты
        categoryCard.setAttribute('data-category-id', category.id);
        categoryCard.setAttribute('data-category-name', category.name);
        categoryCard.setAttribute('data-loaded', 'true');
        categoryCard.classList.remove('category-skeleton');
        categoryCard.classList.add('category-loaded');
        
        // Добавляем обработчик клика
        categoryCard.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            if (categoryId && categoryName) {
                filterByCategory(parseInt(categoryId), categoryName);
            }
        });
    };
    
    // Загрузка видимых категорий
    const loadVisibleCategories = () => {
        if (!swiperInstance.slides || swiperInstance.slides.length === 0) {
            console.warn('Slides не доступны для загрузки');
            return;
        }
        
        const activeIndex = swiperInstance.activeIndex || 0;
        const slidesPerView = swiperInstance.params.slidesPerView === 'auto' ? 4 : swiperInstance.params.slidesPerView;
        
        // Загружаем текущий слайд и соседние (±4 для запаса)
        const startIndex = Math.max(0, activeIndex - 2);
        const endIndex = Math.min(swiperInstance.slides.length - 1, activeIndex + slidesPerView + 4);
        
        console.log(`Загрузка видимых категорий: ${startIndex} - ${endIndex}`);
        
        for (let i = startIndex; i <= endIndex; i++) {
            const slide = swiperInstance.slides[i];
            const categoryCard = slide?.querySelector('.category-card');
            if (categoryCard) {
                loadCategorySlide(categoryCard);
            }
        }
    };
    
    // Подключаем обработчики событий Swiper
    swiperInstance.on('slideChange', () => {
        loadVisibleCategories();
    });
    
    swiperInstance.on('progress', () => {
        loadVisibleCategories();
    });
    
    swiperInstance.on('reachEnd', () => {
        // Загружаем все оставшиеся при достижении конца
        const allSkeletons = document.querySelectorAll('.category-skeleton');
        console.log('Достигнут конец, загружаем оставшиеся категории:', allSkeletons.length);
        allSkeletons.forEach(loadCategorySlide);
    });
    
    // Загружаем видимые сразу
    console.log('Загрузка начальных видимых категорий...');
    loadVisibleCategories();
}

// Поиск товаров
function performSearch(query = null) {
    const searchInput = document.getElementById('searchInput');
    const searchQuery = query !== null ? query : (searchInput ? searchInput.value.trim() : '');

    console.log('Выполняется поиск по запросу:', searchQuery);

    if (searchQuery === '' || searchQuery.length < 2) {
        // Если поиск пустой или слишком короткий, показываем все товары
        showAllProducts();
        isSearchActive = false;
        isInCategoryView = false;
        return;
    }

    isSearchActive = true;
    isInCategoryView = false; // Выходим из режима категории при поиске

    const SIMILARITY_THRESHOLD = 65; // Порог совпадения 65%

    // Фильтруем товары с нечётким поиском
    const productsWithSimilarity = allProducts.map(product => {
        const name = product.name || '';
        const description = product.description || '';
        const article = product.article || '';

        // Проверяем совпадение с названием (наивысший приоритет)
        const nameSimilarity = findBestMatch(searchQuery, name);
        
        // Проверяем совпадение с артикулом (высокий приоритет)
        const articleSimilarity = article ? findBestMatch(searchQuery, article) : 0;
        
        // Проверяем совпадение с описанием (средний приоритет)
        const descriptionSimilarity = description ? findBestMatch(searchQuery, description) : 0;

        // Берём максимальное совпадение из всех полей
        const maxSimilarity = Math.max(nameSimilarity, articleSimilarity, descriptionSimilarity);

        return {
            ...product,
            similarity: maxSimilarity,
            matchField: nameSimilarity === maxSimilarity ? 'name' : 
                       articleSimilarity === maxSimilarity ? 'article' : 'description'
        };
    });

    // Фильтруем товары по порогу совпадения и сортируем по релевантности
    const filteredProducts = productsWithSimilarity
        .filter(product => product.similarity >= SIMILARITY_THRESHOLD)
        .sort((a, b) => {
            // Сначала сортируем по проценту совпадения (убывание)
            if (b.similarity !== a.similarity) {
                return b.similarity - a.similarity;
            }
            // При равном проценте отдаём приоритет совпадению в названии
            if (a.matchField === 'name' && b.matchField !== 'name') return -1;
            if (b.matchField === 'name' && a.matchField !== 'name') return 1;
            // Затем приоритет артикулу
            if (a.matchField === 'article' && b.matchField === 'description') return -1;
            if (b.matchField === 'article' && a.matchField === 'description') return 1;
            
            // При прочем равенстве сортируем по наличию (quantity>0 первее), затем по дате создания (новые первее)
            const aInStock = (a.quantity || 0) > 0 ? 1 : 0;
            const bInStock = (b.quantity || 0) > 0 ? 1 : 0;
            if (bInStock !== aInStock) return bInStock - aInStock;

            const aTime = a.created_at ? new Date(a.created_at).getTime() : 0;
            const bTime = b.created_at ? new Date(b.created_at).getTime() : 0;
            return bTime - aTime;
        });

    console.log(`Найдено товаров: ${filteredProducts.length} из ${allProducts.length}`);
    filteredProducts.forEach(product => {
        console.log(`- ${product.name}: ${product.similarity.toFixed(1)}% (поле: ${product.matchField})`);
    });

    renderSearchResults(filteredProducts, searchQuery);
}

// Отрисовка результатов поиска
function renderSearchResults(products, query) {
    const container = document.getElementById('productsContainer');
    if (!container) return;

    if (products.length === 0) {
        container.innerHTML = `
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${escapeHtml(query)}"</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;
        return;
    }

    const productsHTML = products.map(product => {
        const imageUrl = product.main_photo_url || product.photo_url;
        const hasImage = !!imageUrl;
        const isAvailable = product.isAvailable && product.quantity > 0;
        
        return `
        <article class="product-card" data-product-id="${product.id}">
            <div class="product-image ${!hasImage ? 'no-image' : ''}">
                ${hasImage 
                    ? `<img src="${escapeHtml(processImageUrl(imageUrl))}" alt="${escapeHtml(product.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${product.has_multiple_photos ? '<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>' : ''}` 
                    : ''
                }
                ${getStatusBadge(product)}
                ${product.similarity ? `<div class="product-badge" style="background: var(--color-gray); top: auto; bottom: var(--space-xs); left: var(--space-xs);">${Math.round(product.similarity)}%</div>` : ''}
            </div>
            <div class="product-info">
                <h3 class="product-name">${escapeHtml(product.name)}</h3>
                ${product.description ? `<p class="product-description">${escapeHtml(product.description)}</p>` : ''}
                <div class="product-footer">
                    <span class="product-price">${escapeHtml(product.formatted_price || formatPrice(product.price))}</span>
                    <button class="add-to-cart ${!isAvailable ? 'disabled' : ''}" 
                            data-product-id="${product.id}"
                            ${!isAvailable ? 'disabled' : ''}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `;
    }).join('');

    container.innerHTML = `
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${escapeHtml(query)}" (найдено: ${products.length})</h5>
        </div>
        <div class="products-grid">
            ${productsHTML}
        </div>
    `;
}

// Фильтрация по категории
async function filterByCategory(categoryId, categoryName) {
    console.log('Фильтрация по категории:', categoryId, categoryName);
    
    isSearchActive = true;
    isInCategoryView = true; // Устанавливаем флаг просмотра категории
    
    // Показываем кнопку "Назад" только на десктопе
    const backButton = document.getElementById('backButton');
    if (backButton && window.innerWidth > 768) {
        backButton.style.display = 'flex';
        backButton.classList.add('show');
    }
    
    const container = document.getElementById('productsContainer');
    if (!container) {
        console.error('Контейнер productsContainer не найден');
        return;
    }

    // Показываем индикатор загрузки
    container.innerHTML = `
        <div class="loading-content" style="padding: 2rem; text-align: center;">
            <div class="loading-spinner"></div>
            <div class="loading-text">Загрузка товаров...</div>
        </div>
    `;

    try {
        // Получаем short_name из meta-тега
        const shortNameMeta = document.querySelector('meta[name="short-name"]');
        const shortName = shortNameMeta ? shortNameMeta.getAttribute('content') : '';
        
        if (!shortName) {
            throw new Error('Short name не найден');
        }

        // Загружаем товары категории с сервера
        const response = await secureFetch(`/${shortName}/api/search?category_id=${categoryId}`);
        
        if (!response.ok) {
            throw new Error(`Ошибка загрузки: ${response.status}`);
        }

        const categoryProducts = await response.json();
        console.log('Найдено товаров в категории:', categoryProducts.length, categoryProducts);

        if (categoryProducts.length === 0) {
            container.innerHTML = `
                <div class="products-header">
                    <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${escapeHtml(categoryName)}</h5>
                </div>
                <div class="no-results">
                    <i class="fas fa-folder-open"></i>
                    <h6>В этой категории пока нет товаров</h6>
                    <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                        Показать все товары
                    </button>
                </div>
            `;
            return;
        }

        // Отображаем товары категории
        renderCategoryResults(categoryProducts, categoryName);

        // Очищаем поле поиска
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Добавляем CSS класс для отображения подсказки о свайпе
        document.body.classList.add('category-view');
        
        // Показываем подсказку о свайпе через 1 секунду
        setTimeout(() => {
            showSwipeHint();
        }, 1000);
        
    } catch (error) {
        console.error('Ошибка загрузки товаров категории:', error);
        container.innerHTML = `
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${escapeHtml(categoryName)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <h6>Ошибка загрузки товаров</h6>
                <p class="text-muted">${escapeHtml(error.message)}</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Вернуться к каталогу
                </button>
            </div>
        `;
    }
}

// Отрисовка результатов для категории
function renderCategoryResults(products, categoryName) {
    const container = document.getElementById('productsContainer');
    if (!container) return;

    // Если нет товаров в категории
    if (products.length === 0) {
        container.innerHTML = `
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${escapeHtml(categoryName)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h6>В этой категории пока нет товаров</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;
        return;
    }

    const productsHTML = products.map(product => {
        const imageUrl = product.main_photo_url || product.photo_url;
        const hasImage = !!imageUrl;
        const isAvailable = product.isAvailable && product.quantity > 0;
        
        return `
        <article class="product-card" data-product-id="${product.id}">
            <div class="product-image ${!hasImage ? 'no-image' : ''}">
                ${hasImage 
                    ? `<img src="${escapeHtml(processImageUrl(imageUrl))}" alt="${escapeHtml(product.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${product.has_multiple_photos ? '<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>' : ''}` 
                    : ''
                }
                ${getStatusBadge(product)}
            </div>
            <div class="product-info">
                <h3 class="product-name">${escapeHtml(product.name)}</h3>
                ${product.description ? `<p class="product-description">${escapeHtml(product.description)}</p>` : ''}
                <div class="product-footer">
                    <span class="product-price">${escapeHtml(product.formatted_price || formatPrice(product.price))}</span>
                    <button class="add-to-cart ${!isAvailable ? 'disabled' : ''}" 
                            data-product-id="${product.id}"
                            ${!isAvailable ? 'disabled' : ''}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `;
    }).join('');

    container.innerHTML = `
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${escapeHtml(categoryName)}</h5>
        </div>
        <div class="products-grid">
            ${productsHTML}
        </div>
    `;
}

// Показать все товары
function showAllProducts() {
    isSearchActive = false;
    isInCategoryView = false; // Сбрасываем флаг просмотра категории
    
    // Скрываем кнопку "Назад"
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.style.display = 'none';
        backButton.classList.remove('show');
    }
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Убираем CSS класс для подсказки о свайпе
    document.body.classList.remove('category-view');
    
    // Перезагружаем страницу чтобы вернуть изначальное состояние
    window.location.reload();
}

// Форматирование цены
function formatPrice(price) {
    return Number(price).toLocaleString('ru-RU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

// Функция для обработки URL изображений
function processImageUrl(url) {
    if (!url) return url;
    
    // Если URL уже полный (начинается с http или /), возвращаем как есть
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
        return url;
    }
    
    // Иначе добавляем начальный слэш для относительного пути
    return '/' + url;
}

// Функция для вычисления расстояния Левенштейна
function levenshteinDistance(str1, str2) {
    const matrix = [];
    
    for (let i = 0; i <= str2.length; i++) {
        matrix[i] = [i];
    }
    
    for (let j = 0; j <= str1.length; j++) {
        matrix[0][j] = j;
    }
    
    for (let i = 1; i <= str2.length; i++) {
        for (let j = 1; j <= str1.length; j++) {
            if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1, // замена
                    matrix[i][j - 1] + 1,     // вставка
                    matrix[i - 1][j] + 1      // удаление
                );
            }
        }
    }
    
    return matrix[str2.length][str1.length];
}

// Функция для вычисления процента совпадения строк
function calculateSimilarity(str1, str2) {
    const longer = str1.length > str2.length ? str1 : str2;
    const shorter = str1.length > str2.length ? str2 : str1;
    
    if (longer.length === 0) {
        return 100; // Если обе строки пустые, совпадение 100%
    }
    
    const distance = levenshteinDistance(longer, shorter);
    const similarity = ((longer.length - distance) / longer.length) * 100;
    
    return Math.max(0, similarity);
}

// Функция для поиска совпадений в строке с учётом подстрок
function findBestMatch(searchQuery, targetText) {
    const query = searchQuery.toLowerCase();
    const text = targetText.toLowerCase();
    
    // Прямое включение (самый высокий приоритет)
    if (text.includes(query)) {
        return 100;
    }
    
    // Проверяем совпадение всей строки
    const fullSimilarity = calculateSimilarity(query, text);
    
    // Проверяем совпадения с подстроками текста
    const words = text.split(/\s+/);
    let bestWordSimilarity = 0;
    
    words.forEach(word => {
        if (word.length >= 2) { // Игнорируем слишком короткие слова
            const wordSimilarity = calculateSimilarity(query, word);
            bestWordSimilarity = Math.max(bestWordSimilarity, wordSimilarity);
        }
    });
    
    // Проверяем частичные совпадения (подстроки запроса)
    let bestPartialSimilarity = 0;
    if (query.length >= 3) {
        for (let i = 0; i <= text.length - query.length; i++) {
            const substring = text.substring(i, i + query.length);
            const partialSimilarity = calculateSimilarity(query, substring);
            bestPartialSimilarity = Math.max(bestPartialSimilarity, partialSimilarity);
        }
    }
    
    // Возвращаем лучший результат из всех проверок
    return Math.max(fullSimilarity, bestWordSimilarity, bestPartialSimilarity);
}

// Переменные для корзины
let cart = [];
let isSubmittingOrder = false; // Защита от повторных отправок

// Функции корзины и заказов
function addToCart(productId) {
    // Используем новую функцию с количеством по умолчанию = 1
    addToCartWithQuantity(productId, 1);
}

function updateCartCounter() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const counter = document.getElementById('cart-counter');
            const cartFloat = document.getElementById('cart-float');
            
            if (counter && cartFloat) {
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.classList.remove('hidden');
                    cartFloat.classList.remove('hidden');
                    
                    // Добавляем анимацию при обновлении счетчика
                    counter.style.animation = 'none';
                    setTimeout(() => {
                        counter.style.animation = 'cart-counter-pulse 2s infinite';
                    }, 50);
                } else {
                    counter.classList.add('hidden');
                    cartFloat.classList.add('hidden');
                }
            }
        })
        .catch(error => {
            console.error('Ошибка получения счетчика корзины:', error);
            // В случае ошибки скрываем кнопку корзины
            const cartFloat = document.getElementById('cart-float');
            if (cartFloat) {
                cartFloat.classList.add('hidden');
            }
        });
}

// Показать детали товара в модальном окне с актуальными данными
async function showProductDetails(productId) {
    try {
        // Получаем элементы модального окна
        const modal = document.getElementById('productModal');
        const title = document.getElementById('productModalTitle');
        const body = document.getElementById('productModalBody');
        const footer = document.getElementById('productModalFooter');
        
        if (!modal || !body || !footer) {
            console.error('Элементы модального окна не найдены');
            return;
        }

        // Показываем модальное окно без Bootstrap
        modal.classList.add('show');
        modal.style.display = 'block';
        
        // Добавляем блокировку скролла
        document.body.style.overflow = 'hidden';
        
        title.textContent = 'Загрузка...';
        body.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка товара...</div>
            </div>
        `;
        footer.style.display = 'none';

        // Получаем короткое имя из meta тега
        const shortName = document.querySelector('meta[name="short-name"]')?.getAttribute('content');
        if (!shortName) {
            throw new Error('Short name не найден');
        }

        // Загружаем актуальные данные товара
        const response = await fetch(`/${shortName}/api/products/${productId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const product = await response.json();
        
        // Обновляем кэшированные данные
        if (!window.cachedProductsData) {
            window.cachedProductsData = {};
        }
        window.cachedProductsData[productId] = product;

        // Отображаем данные товара
        displayProductInModal(product, body, title, footer);
        
    } catch (error) {
        console.error('Ошибка при загрузке данных товара:', error);
        showAlert('Ошибка при загрузке данных товара', 'error');
    }
}

// Отображение товара в модальном окне
function displayProductInModal(product, body, title, footer) {
    title.textContent = ''; // Убираем название из заголовка
    
    // Генерируем HTML для товара
    body.innerHTML = `
        <div class="row g-4">
            ${product.photos_gallery && product.photos_gallery.length > 0 ? `
                <div class="col-md-6">
                    <div class="product-gallery">
                        <img src="${processImageUrl(product.photos_gallery[0])}" alt="${product.name}" 
                             class="gallery-main-image" id="main-gallery-image"
                             onclick="openGalleryFullscreen(0)">
                        
                        ${product.photos_gallery.length > 1 ? `
                            <div class="gallery-counter">
                                <span id="gallery-current">1</span> / ${product.photos_gallery.length}
                            </div>
                            
                            <button class="gallery-navigation prev" onclick="previousGalleryImage()" id="gallery-prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="gallery-navigation next" onclick="nextGalleryImage()" id="gallery-next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            
                            <div class="gallery-thumbnails">
                                ${product.photos_gallery.map((photo, index) => `
                                    <img src="${processImageUrl(photo)}" alt="${product.name} ${index + 1}" 
                                         class="gallery-thumbnail ${index === 0 ? 'active' : ''}" 
                                         onclick="setGalleryImage(${index})"
                                         data-index="${index}">
                                `).join('')}
                            </div>
                        ` : ''}
                        
                        ${getProductStatusBadge(product)}
                    </div>
                </div>
            ` : (product.main_photo_url || product.photo_url ? `
                <div class="col-md-6">
                    <div class="position-relative">
                        <img src="${processImageUrl(product.main_photo_url || product.photo_url)}" alt="${product.name}" 
                             class="modal-product-image" 
                             onerror="handleImageError(this);" loading="lazy">
                        <div class="product-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Изображение недоступно</span>
                        </div>
                        ${getProductStatusBadge(product)}
                    </div>
                </div>
            ` : '')}
            
            <div class="${(product.photos_gallery && product.photos_gallery.length > 0) || product.main_photo_url || product.photo_url ? 'col-md-6' : 'col-12'}">
                <div class="product-info">
                    ${product.article ? `
                        <p class="text-muted mb-2">
                            <strong>Артикул:</strong> ${product.article}
                        </p>
                    ` : ''}
                    
                    <h4 class="modal-product-name mb-3">${escapeHtml(product.name)}</h4>
                    
                    <div class="modal-product-price">
                        ${product.formatted_price || formatPrice(product.price)}
                    </div>
                    
                    ${product.description ? `
                        <div class="modal-product-description">
                            ${product.description}
                        </div>
                    ` : ''}
                    
                    ${product.specifications ? `
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            ${typeof product.specifications === 'object' && product.specifications !== null ? 
                                Object.entries(product.specifications).map(([key, value]) => 
                                    `<p><strong>${escapeHtml(key)}:</strong> ${escapeHtml(value)}</p>`
                                ).join('') :
                                `<p>${escapeHtml(product.specifications)}</p>`
                            }
                        </div>
                    ` : ''}
                    
                    <div class="availability-info mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #374151; font-weight: 600;">Наличие:</span>
                            <span class="badge ${getQuantityBadgeClass(product.quantity)}">
                                ${getQuantityText(product.quantity)}
                            </span>
                        </div>
                    </div>
                    
                    ${product.isAvailable && product.quantity > 0 ? `
                        <div class="quantity-selector">
                            <label style="color: #374151; font-weight: 600;">Количество:</label>
                            <div class="d-flex align-items-center gap-3 justify-content-center">
                                <button class="quantity-btn" onclick="changeQuantityModal(${product.id}, -1)" id="modal-decrease-${product.id}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span id="modal-quantity-${product.id}" class="quantity-display">1</span>
                                <button class="quantity-btn" onclick="changeQuantityModal(${product.id}, 1)" id="modal-increase-${product.id}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div id="modal-total-price-${product.id}" class="text-center mt-2" style="font-weight: 600; color: var(--primary-color);"></div>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
        
        <!-- Fullscreen галерея -->
        <div class="gallery-fullscreen" id="gallery-fullscreen">
            <div class="gallery-fullscreen-content">
                <button class="gallery-fullscreen-close" onclick="closeGalleryFullscreen()">
                    <i class="fas fa-times"></i>
                </button>
                <img src="" alt="" class="gallery-fullscreen-image" id="fullscreen-image">
                
                ${product.photos_gallery && product.photos_gallery.length > 1 ? `
                    <button class="gallery-fullscreen-nav prev" onclick="previousFullscreenImage()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="gallery-fullscreen-nav next" onclick="nextFullscreenImage()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                ` : ''}
            </div>
        </div>
    `;
    
    // Сохраняем данные галереи в глобальной переменной для функций навигации
    window.currentGallery = {
        photos: product.photos_gallery || [],
        currentIndex: 0
    };
    
    // Настраиваем footer
    footer.style.display = 'block';
    footer.innerHTML = `
        <div class="full-width">
            ${product.isAvailable && product.quantity > 0 ? `
                <button type="button" class="btn-primary full-width" onclick="addToCartFromModal(${product.id})">
                    <i class="fas fa-shopping-cart"></i> Добавить в корзину
                </button>
            ` : `
                <button type="button" class="btn-primary full-width" disabled style="opacity: 0.5;">
                    <i class="fas fa-times"></i> Товар недоступен
                </button>
            `}
        </div>
    `;
    
    // Инициализируем количество если товар доступен
    if (product.isAvailable && product.quantity > 0) {
        setTimeout(() => {
            const priceToUse = product.price_with_markup || product.price;
            updateModalQuantity(product.id, 1, priceToUse, product.quantity);
        }, 100);
    }
    
    // Инициализируем свайп для галереи
    setTimeout(() => {
        initGallerySwipe();
    }, 150);
}

// Функции управления панелями
function showPanel() {
    const panel = document.getElementById('productPanel');
    const backdrop = document.getElementById('panelBackdrop');
    const footer = document.getElementById('productPanelFooter');
    
    if (backdrop) backdrop.classList.add('show');
    if (panel) panel.classList.add('show');
    if (footer) footer.style.display = 'block';
    
    // Блокируем прокрутку основного содержимого
    document.body.style.overflow = 'hidden';
}

function closePanel() {
    const panel = document.getElementById('productPanel');
    const backdrop = document.getElementById('panelBackdrop');
    const footer = document.getElementById('productPanelFooter');
    
    if (backdrop) backdrop.classList.remove('show');
    if (panel) panel.classList.remove('show');
    if (footer) footer.style.display = 'none';
    
    // Разблокируем прокрутку основного содержимого
    document.body.style.overflow = 'auto';
}

// Функция для получения badge статуса товара
function getStatusBadge(product) {
    let statusClass = 'secondary';
    if (product.availability_status === 'В наличии') statusClass = 'success';
    else if (product.availability_status === 'Заканчивается') statusClass = 'warning';  
    else if (product.availability_status === 'Нет в наличии') statusClass = 'danger';
    
    return `<span class="badge bg-${statusClass} shadow-sm">${escapeHtml(product.availability_status || '')}</span>`;
}

// Функции для работы с количеством товара
function changeQuantity(productId, delta) {
    const quantityInput = document.getElementById(`quantity-${productId}`);
    if (!quantityInput) return;
    
    const currentQuantity = parseInt(quantityInput.value) || 1;
    const newQuantity = Math.max(1, Math.min(parseInt(quantityInput.max), currentQuantity + delta));
    
    quantityInput.value = newQuantity;
    updateTotalPrice(productId, newQuantity);
    updateQuantityButtons(productId, newQuantity);
    
    // Haptic feedback
    triggerHapticFeedback('light');
}

function validateQuantity(productId) {
    const quantityInput = document.getElementById(`quantity-${productId}`);
    if (!quantityInput) return;
    
    const quantity = parseInt(quantityInput.value);
    const maxQuantity = parseInt(quantityInput.max);
    
    if (isNaN(quantity) || quantity < 1) {
        quantityInput.value = 1;
    } else if (quantity > maxQuantity) {
        quantityInput.value = maxQuantity;
        showAlert(`Максимальное количество: ${maxQuantity} шт.`, 'warning');
    }
    
    const finalQuantity = parseInt(quantityInput.value);
    updateTotalPrice(productId, finalQuantity);
    updateQuantityButtons(productId, finalQuantity);
}

function updateTotalPrice(productId, quantity) {
    const product = window.cachedProductsData ? window.cachedProductsData[productId] : null;
    if (product) {
        const totalPrice = product.price * quantity;
        const formattedTotal = new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB'
        }).format(totalPrice);
        
        const totalPriceElement = document.getElementById(`totalPrice-${productId}`);
        if (totalPriceElement) {
            totalPriceElement.textContent = formattedTotal;
        }
    }
}

function updateQuantityButtons(productId, quantity) {
    const decreaseBtn = document.getElementById(`decreaseBtn-${productId}`);
    const increaseBtn = document.getElementById(`increaseBtn-${productId}`);
    const quantityInput = document.getElementById(`quantity-${productId}`);
    
    if (decreaseBtn) {
        decreaseBtn.disabled = quantity <= 1;
    }
    
    if (increaseBtn && quantityInput) {
        const maxQuantity = parseInt(quantityInput.max);
        increaseBtn.disabled = quantity >= maxQuantity;
    }
}

// Функция добавления товара в корзину с количеством
function addToCartWithQuantity(productId, quantity) {
    // Показываем состояние загрузки
    setCartButtonLoading(true);
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('quantity', quantity);
    
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setCartButtonLoading(false);
        
        if (data.success) {
            showAlert(`Товар добавлен в корзину (${quantity} шт.)!`);
            updateCartCounter();
            animateCartButtonOnAdd();
            triggerHapticFeedback('success');
        } else {
            setCartButtonError(true);
            showAlert(data.message || 'Ошибка при добавлении товара', 'error');
            triggerHapticFeedback('error');
        }
    })
    .catch(error => {
        setCartButtonLoading(false);
        setCartButtonError(true);
        console.error('Ошибка при добавлении товара в корзину:', error);
        showAlert('Ошибка при добавлении товара в корзину', 'error');
        triggerHapticFeedback('error');
    });
}

function showCart() {
    showCartModal();
}

// Безопасный haptic feedback
function triggerHapticFeedback(type = 'light') {
    try {
        if (window.Telegram?.WebApp?.HapticFeedback?.impactOccurred && 
            typeof window.Telegram.WebApp.HapticFeedback.impactOccurred === 'function') {
            window.Telegram.WebApp.HapticFeedback.impactOccurred(type);
        }
        // Не делаем ничего если не поддерживается - это нормально
    } catch (error) {
        // Молча игнорируем ошибки haptic feedback
        console.debug('HapticFeedback не поддерживается:', error.message);
    }
}

// Настройка Haptic Feedback
function setupHapticFeedback() {
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            triggerHapticFeedback('light');
        });
    });
}

// Функция для анимированного показа/скрытия кнопки корзины
function toggleCartFloat(show = true, count = 0) {
    const cartFloat = document.getElementById('cart-float');
    const counter = document.getElementById('cart-counter');
    
    if (!cartFloat || !counter) return;
    
    if (show && count > 0) {
        // Показываем кнопку корзины
        counter.textContent = count;
        counter.classList.remove('hidden');
        cartFloat.classList.remove('hidden');
        
        // Добавляем анимацию пульсации для счетчика
        counter.style.animation = 'cart-counter-pulse 2s infinite';
    } else {
        // Скрываем кнопку корзины
        counter.classList.add('hidden');
        cartFloat.classList.add('hidden');
    }
}

// Функция для временной анимации при добавлении товара
function animateCartButtonOnAdd() {
    const cartBtn = document.querySelector('.cart-float-btn');
    const cartIcon = document.querySelector('.cart-float-btn .fa-shopping-cart');
    
    if (cartBtn && cartIcon) {
        // Анимация кнопки
        cartBtn.style.transform = 'translateY(-4px) scale(1.1)';
        cartBtn.style.boxShadow = '0 12px 32px rgba(16, 185, 129, 0.5)';
        
        // Анимация иконки
        cartIcon.style.transform = 'rotate(-15deg) scale(1.2)';
        
        // Возвращаем в исходное состояние через 300мс
        setTimeout(() => {
            cartBtn.style.transform = '';
            cartBtn.style.boxShadow = '';
            cartIcon.style.transform = '';
        }, 300);
        
        // Тактильная обратная связь
        triggerHapticFeedback('medium');
    }
}

// Функция для показа состояния загрузки кнопки корзины
function setCartButtonLoading(loading = true) {
    const cartBtn = document.querySelector('.cart-float-btn');
    if (cartBtn) {
        if (loading) {
            cartBtn.classList.add('loading');
            cartBtn.setAttribute('aria-busy', 'true');
        } else {
            cartBtn.classList.remove('loading');
            cartBtn.setAttribute('aria-busy', 'false');
        }
    }
}

// Функция для показа ошибки на кнопке корзины
function setCartButtonError(hasError = true) {
    const cartBtn = document.querySelector('.cart-float-btn');
    if (cartBtn) {
        if (hasError) {
            cartBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            setTimeout(() => {
                cartBtn.style.background = '';
            }, 2000);
        }
    }
}

// Функции для модального окна товара
function getProductStatusBadge(product) {
    // Проверяем как is_active, так и isAvailable для совместимости
    const isActive = product.is_active !== undefined ? product.is_active : product.isAvailable;
    
    if (!isActive) {
        return '<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>';
    }
    
    if (product.quantity <= 0) {
        return '<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>';
    }
    
    if (product.quantity <= 5) {
        return `<div class="product-status"><span class="status-badge status-limited">Осталось ${product.quantity}</span></div>`;
    }
    
    return '<div class="product-status"><span class="status-badge status-available">В наличии</span></div>';
}

function getQuantityBadgeClass(quantity) {
    return 'bg-success';
}

function getQuantityText(quantity) {
    if (quantity <= 0) return 'Нет в наличии';
    return `${quantity} шт.`;
}

function changeQuantityModal(productId, delta) {
    const quantityElement = document.getElementById(`modal-quantity-${productId}`);
    if (!quantityElement) return;
    
    const currentQuantity = parseInt(quantityElement.textContent) || 1;
    const newQuantity = Math.max(1, currentQuantity + delta);
    
    // Получаем максимальное количество из кэшированных данных товара
    const product = window.cachedProductsData?.[productId];
    if (product) {
        const maxQuantity = Math.min(product.quantity, 99);
        const finalQuantity = Math.min(newQuantity, maxQuantity);
        const priceToUse = product.price_with_markup || product.price;
        
        updateModalQuantity(productId, finalQuantity, priceToUse, product.quantity);
    }
}

function updateModalQuantity(productId, quantity, price, maxQuantity) {
    const quantityElement = document.getElementById(`modal-quantity-${productId}`);
    const decreaseBtn = document.getElementById(`modal-decrease-${productId}`);
    const increaseBtn = document.getElementById(`modal-increase-${productId}`);
    const totalElement = document.getElementById(`modal-total-price-${productId}`);
    
    if (quantityElement) {
        quantityElement.textContent = quantity;
    }
    
    if (decreaseBtn) {
        decreaseBtn.disabled = quantity <= 1;
    }
    
    if (increaseBtn) {
        increaseBtn.disabled = quantity >= maxQuantity;
    }
    
    if (totalElement && price) {
        const total = quantity * price;
        totalElement.textContent = `Итого: ${formatPrice(total)} ₽`;
    }
}

function addToCartFromModal(productId) {
    const quantityElement = document.getElementById(`modal-quantity-${productId}`);
    const quantity = quantityElement ? parseInt(quantityElement.textContent) || 1 : 1;
    
    addToCartWithQuantity(productId, quantity);
    
    // Закрываем модальное окно
    const modal = document.getElementById('productModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
}

// Переопределяем функцию showCart для модального окна
function showCartModal() {
    try {
        const modal = document.getElementById('cartModal');
        const body = document.getElementById('cartModalBody');
        const footer = document.getElementById('cartModalFooter');
        
        if (!modal || !body) {
            console.error('Элементы модального окна корзины не найдены');
            return;
        }

        // Показываем модальное окно без Bootstrap
        modal.classList.add('show');
        modal.style.display = 'block';
        
        // Добавляем блокировку скролла
        document.body.style.overflow = 'hidden';
        
        body.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка корзины...</div>
            </div>
        `;
        footer.style.display = 'none';

        // Загружаем данные корзины
        fetch('/cart', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items && data.items.length > 0) {
                displayCartItems(data.items, data.total_amount);
            } else {
                body.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина пуста</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `;
                footer.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Ошибка при загрузке корзины:', error);
            body.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Ошибка при загрузке корзины</h5>
                    <p class="text-muted">Попробуйте обновить страницу</p>
                </div>
            `;
            footer.style.display = 'none';
        });
        
    } catch (error) {
        console.error('Ошибка при загрузке корзины:', error);
        showAlert('Ошибка при загрузке корзины', 'error');
    }
}

// Функция отображения товаров в корзине
function displayCartItems(items, totalAmount) {
    const body = document.getElementById('cartModalBody');
    const footer = document.getElementById('cartModalFooter');
    
    if (!body || !footer) return;
    
    if (!items || items.length === 0) {
        body.innerHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Корзина пуста</h5>
                <p class="text-muted">Добавьте товары для оформления заказа</p>
            </div>
        `;
        footer.style.display = 'none';
        return;
    }
    
    let html = '<div class="cart-items">';
    
    items.forEach(item => {
        html += `
            <div class="cart-item mb-3 p-3 border rounded"style="flex-direction: column;" data-cart-id="${item.id}">
                <div class="d-flex align-items-start" style="width: 100%; padding-bottom: 10px;">
                    <div class="cart-item-image  flex-shrink-0">
                        ${item.main_photo_url || item.photo_url ? 
                            `<img src="${item.main_photo_url || item.photo_url}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="${item.name}">` :
                            `<div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>`
                        }
                    </div>
                    
                    <div class="cart-item-info flex-grow-1" >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold">${item.name}</h6>
                                ${item.article ? `<small class="text-muted d-block">Артикул: ${item.article}</small>` : ''}
                                <div class="text-primary fw-semibold">${item.formatted_price} за шт.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${item.id})" title="Удалить товар">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                       
                    </div>
                </div>
                 <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                            <div class="quantity-controls">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary disabled px-3">${item.quantity} шт</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})" ${item.quantity >= item.available_quantity ? 'disabled' : ''}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-item-total">
                                <strong class="text-success fs-5">${item.formatted_total}</strong>
                            </div>
                        </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    body.innerHTML = html;
    
    // Показываем футер с итоговой суммой и кнопками
    footer.innerHTML = `
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100">
            <div class="cart-total" style="width:100%;">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Итого: ${formatPrice(totalAmount)} ₽
                </h5>
                <small class="text-muted">Товаров в корзине: ${items.length}</small>
            </div>
            <div class="cart-actions d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="clearCart()">
                    <i class="fas fa-trash me-1"></i> Очистить
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="proceedToCheckout()">
                    <i class="fas fa-check me-1"></i> Оформить заказ
                </button>
            </div>
        </div>
    `;
    footer.style.display = 'block';
}

// Функция обновления количества товара в корзине
function updateCartQuantity(cartId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(cartId);
        return;
    }
    
    // Показываем индикатор загрузки для конкретного элемента
    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
    if (cartItem) {
        const quantityControls = cartItem.querySelector('.quantity-controls');
        if (quantityControls) {
            quantityControls.style.opacity = '0.5';
            quantityControls.style.pointerEvents = 'none';
        }
    }
    
    fetch(`/cart/update/${cartId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показываем toast уведомление
            showToast('Количество обновлено', 'success');
            updateCartCounter();
            // Обновляем только содержимое корзины без полной перезагрузки модального окна
            refreshCartContent();
            triggerHapticFeedback('light');
        } else {
            showToast(data.message || 'Ошибка при обновлении количества', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении количества:', error);
        showToast('Ошибка при обновлении количества', 'error');
    })
    .finally(() => {
        // Возвращаем интерактивность
        if (cartItem) {
            const quantityControls = cartItem.querySelector('.quantity-controls');
            if (quantityControls) {
                quantityControls.style.opacity = '1';
                quantityControls.style.pointerEvents = 'auto';
            }
        }
    });
}

// Функция обновления содержимого корзины без перезагрузки модального окна
function refreshCartContent() {
    const body = document.getElementById('cartModalBody');
    const footer = document.getElementById('cartModalFooter');
    
    if (!body || !footer) return;
    
    fetch('/cart', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.items && data.items.length > 0) {
            displayCartItems(data.items, data.total_amount);
        } else {
            body.innerHTML = `
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пуста</h5>
                    <p class="text-muted">Добавьте товары для оформления заказа</p>
                </div>
            `;
            footer.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении корзины:', error);
    });
}

// Функция удаления товара из корзины
function removeFromCart(cartId) {
    // Показываем красивое модальное подтверждение вместо стандартного confirm
    if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.showConfirm) {
        window.Telegram.WebApp.showConfirm('Удалить товар из корзины?', (confirmed) => {
            if (confirmed) {
                performRemoveFromCart(cartId);
            }
        });
    } else {
        if (!confirm('Удалить товар из корзины?')) {
            return;
        }
        performRemoveFromCart(cartId);
    }
}

function performRemoveFromCart(cartId) {
    // Показываем анимацию удаления
    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
    if (cartItem) {
        cartItem.style.opacity = '0.5';
        cartItem.style.transform = 'scale(0.95)';
        cartItem.style.pointerEvents = 'none';
    }
    
    fetch(`/cart/remove/${cartId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Товар удален из корзины', 'success');
            updateCartCounter();
            triggerHapticFeedback('medium');
            
            // Плавно удаляем элемент
            if (cartItem) {
                cartItem.style.transition = 'all 0.3s ease';
                cartItem.style.opacity = '0';
                cartItem.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    refreshCartContent();
                }, 300);
            } else {
                refreshCartContent();
            }
        } else {
            showToast(data.message || 'Ошибка при удалении товара', 'error');
            // Восстанавливаем элемент при ошибке
            if (cartItem) {
                cartItem.style.opacity = '1';
                cartItem.style.transform = 'scale(1)';
                cartItem.style.pointerEvents = 'auto';
            }
        }
    })
    .catch(error => {
        console.error('Ошибка при удалении товара:', error);
        showToast('Ошибка при удалении товара', 'error');
        // Восстанавливаем элемент при ошибке
        if (cartItem) {
            cartItem.style.opacity = '1';
            cartItem.style.transform = 'scale(1)';
            cartItem.style.pointerEvents = 'auto';
        }
    });
}

// Функция очистки корзины
function clearCart() {
    // Используем Telegram WebApp подтверждение если доступно
    if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.showConfirm) {
        window.Telegram.WebApp.showConfirm('Очистить всю корзину?', (confirmed) => {
            if (confirmed) {
                performClearCart();
            }
        });
    } else {
        if (!confirm('Очистить всю корзину?')) {
            return;
        }
        performClearCart();
    }
}

function performClearCart() {
    // Показываем индикатор загрузки
    const body = document.getElementById('cartModalBody');
    const footer = document.getElementById('cartModalFooter');
    
    if (body) {
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Очистка корзины...</span>
                </div>
                <div class="mt-3">Очищаем корзину...</div>
            </div>
        `;
    }
    if (footer) {
        footer.style.display = 'none';
    }
    
    fetch('/cart/clear', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Корзина очищена', 'success');
            updateCartCounter();
            triggerHapticFeedback('medium');
            
            // Показываем сообщение о пустой корзине
            if (body) {
                body.innerHTML = `
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина очищена</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `;
            }
            
            // Автоматически закрываем модальное окно через 2 секунды
            setTimeout(() => {
                const modal = document.getElementById('cartModal');
                if (modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            }, 1500);
            
        } else {
            showToast(data.message || 'Ошибка при очистке корзины', 'error');
            refreshCartContent();
        }
    })
    .catch(error => {
        console.error('Ошибка при очистке корзины:', error);
        showToast('Ошибка при очистке корзины', 'error');
        refreshCartContent();
    });
}

// Функция перехода к оформлению заказа
function proceedToCheckout() {
    if (!userData) {
        showAlert('Ошибка: данные пользователя недоступны', 'error');
        return;
    }

    // Показываем загрузку
    showAlert('Проверяем корзину...', 'info');

    // Сначала получаем актуальные данные корзины с сервера
    fetch('/cart', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(cartData => {
        if (!cartData.success || !cartData.items || cartData.items.length === 0) {
            showAlert('Корзина пуста', 'warning');
            return;
        }

        // Показываем загрузку оформления
        showAlert('Оформляем заказ...', 'info');

        // Подготавливаем данные для отправки
        const orderData = {
            bot_short_name: document.querySelector('meta[name="short-name"]').getAttribute('content'),
            user_data: userData,
            notes: '' // Можно добавить поле для комментариев
        };

        // Отправляем запрос на оформление заказа
        return secureFetch('/cart/checkout', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    })
    .then(response => {
        if (!response) return; // Если корзина была пуста
        return response.json();
    })
    .then(data => {
        if (!data) return; // Если корзина была пуста
        
        if (data.success) {
            // Закрываем модальное окно корзины
            closeCartModal();
            
            // Показываем успешное сообщение
            showAlert(`Заказ успешно оформлен! Номер заказа: ${data.order.order_number}`, 'success');
            
            // Обновляем счетчик корзины
            updateCartCounter();
            
            // Обновляем содержимое корзины (очищаем)
            setTimeout(() => {
                const body = document.getElementById('cartModalBody');
                if (body) {
                    body.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Корзина пуста</h5>
                            <p class="text-muted">Добавьте товары для оформления заказа</p>
                        </div>
                    `;
                }
                
                const footer = document.getElementById('cartModalFooter');
                if (footer) {
                    footer.style.display = 'none';
                }
            }, 1000);
            
            triggerHapticFeedback('success');
        } else {
            showAlert(data.message || 'Ошибка при оформлении заказа', 'error');
            triggerHapticFeedback('error');
        }
    })
    .catch(error => {
        console.error('Ошибка при оформлении заказа:', error);
        showAlert('Произошла ошибка при оформлении заказа', 'error');
        triggerHapticFeedback('error');
    });
}

// Функция закрытия модального окна товара
function closeProductModal() {
    try {
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
        
        // Убираем блокировку скролла
        document.body.style.overflow = '';
        
        triggerHapticFeedback('light');
    } catch (error) {
        console.error('Ошибка при закрытии модального окна товара:', error);
        // Убираем блокировку даже при ошибке
        document.body.style.overflow = '';
    }
}

// Функция закрытия модального окна корзины
function closeCartModal() {
    try {
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
        
        // Убираем блокировку скролла
        document.body.style.overflow = '';
        
        triggerHapticFeedback('light');
    } catch (error) {
        console.error('Ошибка при закрытии модального окна корзины:', error);
        // Убираем блокировку даже при ошибке
        document.body.style.overflow = '';
    }
}

// Функция показа подсказки о возможности свайпа
function showSwipeHint() {
    // Показываем подсказку только в первый раз для пользователя
    const hasSeenSwipeHint = localStorage.getItem('hasSeenSwipeHint');
    if (hasSeenSwipeHint) {
        return;
    }
    
    // Создаем индикатор свайпа
    const swipeIndicator = document.createElement('div');
    swipeIndicator.className = 'swipe-indicator';
    swipeIndicator.innerHTML = `
        <span class="arrow">→</span>
        <span>Свайп для выхода</span>
    `;
    
    document.body.appendChild(swipeIndicator);
    
    // Убираем индикатор через 3 секунды
    setTimeout(() => {
        if (swipeIndicator.parentNode) {
            swipeIndicator.remove();
        }
        // Запоминаем, что пользователь видел подсказку
        localStorage.setItem('hasSeenSwipeHint', 'true');
    }, 3000);
}

// Функция для настройки поведения скролла в Telegram WebApp
function setupScrollBehavior() {
    if (window.Telegram && window.Telegram.WebApp) {
        const tg = window.Telegram.WebApp;
        
        try {
            // Устанавливаем полноэкранный режим и блокируем сворачивание
            tg.expand();
            
            // Отключаем стандартное поведение pull-to-refresh/close (только для поддерживаемых версий)
            if (tg.version && parseFloat(tg.version) >= 6.1 && tg.disableClosingConfirmation) {
                try {
                    tg.disableClosingConfirmation();
                    console.log('Closing confirmation disabled for WebApp version', tg.version);
                } catch (e) {
                    console.log('Closing confirmation control not supported in version', tg.version);
                }
            } else {
                console.log('Closing confirmation not available in WebApp version', tg.version || 'unknown');
            }
            
            // Блокируем возможность закрытия через свайп вниз
            if (tg.isClosingConfirmationEnabled !== undefined) {
                tg.isClosingConfirmationEnabled = false;
            }
            
            // Устанавливаем фиксированную высоту viewport
            if (tg.setViewportHeight) {
                tg.setViewportHeight(window.innerHeight);
            }
            
            console.log('Настройки скролла для Telegram WebApp применены');
        } catch (error) {
            console.error('Ошибка при настройке поведения скролла:', error);
        }
    }
    
    // Дополнительные настройки для веб-версии
    document.body.style.touchAction = 'pan-x pan-y';
    document.documentElement.style.touchAction = 'pan-x pan-y';
}

// Функция для предотвращения сворачивания при скролле
function preventPullToClose() {
    let startY = 0;
    let startX = 0;
    let isPreventingPull = false;
    const PULL_THRESHOLD = 10; // Минимальное расстояние для активации защиты

    function handleTouchStart(event) {
        if (event.touches.length !== 1) return;
        
        const touch = event.touches[0];
        startY = touch.clientY;
        startX = touch.clientX;
        isPreventingPull = false;
        
        // Получаем текущую позицию скролла
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
        
        // Если мы находимся в самом верху страницы, готовимся к блокировке
        if (scrollTop <= 5) {
            isPreventingPull = true;
        }
    }

    function handleTouchMove(event) {
        if (event.touches.length !== 1 || !isPreventingPull) return;
        
        const touch = event.touches[0];
        const deltaY = touch.clientY - startY;
        const deltaX = Math.abs(touch.clientX - startX);
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
        
        // Если это вертикальный жест вниз на самом верху страницы
        if (scrollTop <= 5 && deltaY > PULL_THRESHOLD && deltaX < 50) {
            // Блокируем событие для предотвращения pull-to-close
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            console.log('Заблокировано потенциальное сворачивание через pull-to-close');
            
            // Добавляем небольшую вибрацию для обратной связи
            if (window.Telegram?.WebApp?.HapticFeedback) {
                window.Telegram.WebApp.HapticFeedback.impactOccurred('rigid');
            }
            
            return false;
        }
    }

    function handleTouchEnd(event) {
        isPreventingPull = false;
    }

    // Используем capturing phase для более раннего перехвата
    document.addEventListener('touchstart', handleTouchStart, { passive: false, capture: true });
    document.addEventListener('touchmove', handleTouchMove, { passive: false, capture: true });
    document.addEventListener('touchend', handleTouchEnd, { passive: true, capture: true });
    
    // Добавляем дополнительную защиту через CSS
    document.body.style.overscrollBehavior = 'none';
    document.documentElement.style.overscrollBehavior = 'none';
    
    // Блокируем refresh/reload жесты
    window.addEventListener('beforeunload', function(event) {
        if (event.clientY < 50) {
            event.preventDefault();
            return false;
        }
    });
    
    console.log('Защита от сворачивания через скролл активирована');
}

// Функция для добавления поддержки свайпа
function addSwipeSupport() {
    let startX = 0;
    let startY = 0;
    let isSwipeStarted = false;

    function handleTouchStart(event) {
        if (event.touches.length !== 1) return;
        
        const touch = event.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
        isSwipeStarted = true;
    }

    function handleTouchMove(event) {
        if (!isSwipeStarted || event.touches.length !== 1) return;
        
        const touch = event.touches[0];
        const deltaX = touch.clientX - startX;
        const deltaY = touch.clientY - startY;
        
        // Проверяем, что это горизонтальный свайп справа налево
        if (Math.abs(deltaX) > Math.abs(deltaY) && deltaX > 50 && Math.abs(deltaY) < 100) {
            // Свайп вправо - закрываем модальное окно
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                if (activeModal.id === 'productModal') {
                    closeProductModal();
                } else if (activeModal.id === 'cartModal') {
                    closeCartModal();
                }
            }
            isSwipeStarted = false;
        }
    }

    function handleTouchEnd() {
        isSwipeStarted = false;
    }

    // Добавляем обработчики к модальным окнам
    const modals = [
        document.getElementById('productModal'),
        document.getElementById('cartModal')
    ];

    modals.forEach(modal => {
        if (modal) {
            modal.addEventListener('touchstart', handleTouchStart, { passive: true });
            modal.addEventListener('touchmove', handleTouchMove, { passive: true });
            modal.addEventListener('touchend', handleTouchEnd, { passive: true });
        }
    });
}

// Функция для добавления поддержки свайпа для выхода из категории
function addCategorySwipeSupport() {
    let startX = 0;
    let startY = 0;
    let isSwipeStarted = false;
    const SWIPE_THRESHOLD = 100; // Минимальное расстояние свайпа
    const EDGE_THRESHOLD = 50; // Максимальное расстояние от левого края для начала свайпа

    function handleTouchStart(event) {
        if (event.touches.length !== 1) return;
        
        const touch = event.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
        
        // Проверяем, что свайп начинается близко к левому краю экрана
        if (startX <= EDGE_THRESHOLD) {
            isSwipeStarted = true;
            console.log('Начат свайп с левого края:', startX);
        }
    }

    function handleTouchMove(event) {
        if (!isSwipeStarted || event.touches.length !== 1) return;
        
        const touch = event.touches[0];
        const deltaX = touch.clientX - startX;
        const deltaY = touch.clientY - startY;
        
        // Проверяем, что это горизонтальный свайп слева направо
        if (Math.abs(deltaX) > Math.abs(deltaY) && 
            deltaX > SWIPE_THRESHOLD && 
            Math.abs(deltaY) < 100) {
            
            console.log('Свайп вправо обнаружен, deltaX:', deltaX);
            
            // Если мы находимся в режиме просмотра категории - выходим
            if (isInCategoryView) {
                console.log('Выход из категории через свайп');
                
                // Показываем уведомление пользователю
                if (window.Telegram?.WebApp?.HapticFeedback) {
                    window.Telegram.WebApp.HapticFeedback.impactOccurred('light');
                }
                
                showAllProducts();
                isSwipeStarted = false;
                return;
            }
            
            // Если мы в режиме поиска - очищаем поиск
            if (isSearchActive) {
                console.log('Очистка поиска через свайп');
                
                if (window.Telegram?.WebApp?.HapticFeedback) {
                    window.Telegram.WebApp.HapticFeedback.impactOccurred('light');
                }
                
                showAllProducts();
                isSwipeStarted = false;
                return;
            }
            
            isSwipeStarted = false;
        }
    }

    function handleTouchEnd() {
        isSwipeStarted = false;
    }

    // Добавляем обработчики к основному контейнеру приложения
    const appContainer = document.getElementById('app');
    if (appContainer) {
        appContainer.addEventListener('touchstart', handleTouchStart, { passive: true });
        appContainer.addEventListener('touchmove', handleTouchMove, { passive: true });
        appContainer.addEventListener('touchend', handleTouchEnd, { passive: true });
        console.log('Обработчики свайпа для выхода из категории добавлены');
    }
}

// Функция для управления видимостью кнопки "Назад" при изменении размера окна
function handleBackButtonVisibility() {
    const backButton = document.getElementById('backButton');
    
    if (!backButton) return;
    
    // Если мы в режиме просмотра категории
    if (isInCategoryView) {
        // Показываем кнопку только на десктопе (> 768px)
        if (window.innerWidth > 768) {
            backButton.style.display = 'flex';
            backButton.classList.add('show');
        } else {
            backButton.style.display = 'none';
            backButton.classList.remove('show');
        }
    } else {
        // Если не в режиме категории, скрываем кнопку
        backButton.style.display = 'none';
        backButton.classList.remove('show');
    }
}

// Глобальные функции для использования в HTML
window.initApp = initApp;
window.showAlert = showAlert;
window.filterByCategory = filterByCategory;
window.showAllProducts = showAllProducts;
window.performSearch = performSearch;
window.addToCart = addToCart;
window.addToCartWithQuantity = addToCartWithQuantity;
window.showProductDetails = showProductDetails;
window.showCart = showCart;
window.showCartModal = showCartModal;
window.closePanel = closePanel;
window.changeQuantityModal = changeQuantityModal;
window.addToCartFromModal = addToCartFromModal;
window.changeQuantity = changeQuantity;
window.validateQuantity = validateQuantity;
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.clearCart = clearCart;
window.proceedToCheckout = proceedToCheckout;
window.refreshCartContent = refreshCartContent;
window.closeProductModal = closeProductModal;
window.closeCartModal = closeCartModal;
window.showSwipeHint = showSwipeHint;
window.setupScrollBehavior = setupScrollBehavior;
window.preventPullToClose = preventPullToClose;
window.addSwipeSupport = addSwipeSupport;
window.addCategorySwipeSupport = addCategorySwipeSupport;
window.toggleCartFloat = toggleCartFloat;
window.animateCartButtonOnAdd = animateCartButtonOnAdd;
window.setCartButtonLoading = setCartButtonLoading;
window.setCartButtonError = setCartButtonError;
window.handleBackButtonVisibility = handleBackButtonVisibility;

// Функции галереи
window.setGalleryImage = setGalleryImage;
window.previousGalleryImage = previousGalleryImage;
window.nextGalleryImage = nextGalleryImage;
window.openGalleryFullscreen = openGalleryFullscreen;
window.closeGalleryFullscreen = closeGalleryFullscreen;
window.previousFullscreenImage = previousFullscreenImage;
window.nextFullscreenImage = nextFullscreenImage;
window.initGallerySwipe = initGallerySwipe;
window.setupCategoryLazyLoading = setupCategoryLazyLoading;

// ===== ФУНКЦИИ СВАЙПА ДЛЯ ГАЛЕРЕИ =====

// Добавление поддержки свайпа для галереи изображений
function initGallerySwipe() {
    const galleryMain = document.querySelector('.product-gallery');
    const galleryFullscreen = document.getElementById('gallery-fullscreen');
    
    if (galleryMain) {
        addSwipeToGallery(galleryMain, false);
    }
    
    if (galleryFullscreen) {
        addSwipeToGallery(galleryFullscreen, true);
    }
}

// Универсальная функция добавления свайпа к галерее
function addSwipeToGallery(element, isFullscreen = false) {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchCurrentX = 0;
    let isDragging = false;
    let startTime = 0;
    const minSwipeDistance = 50; // минимальная дистанция для переключения
    const maxSwipeTime = 300; // максимальное время для быстрого свайпа
    
    // Поддержка мыши для ПК
    let isMouseDown = false;
    
    // Находим целевой элемент для свайпа
    const swipeTarget = isFullscreen 
        ? element.querySelector('.gallery-fullscreen-image')
        : element.querySelector('.gallery-main-image');
    
    if (!swipeTarget) return;
    
    // Добавляем курсор grab для визуальной индикации
    swipeTarget.style.cursor = 'grab';
    swipeTarget.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    
    // Touch события
    swipeTarget.addEventListener('touchstart', handleStart, { passive: false });
    swipeTarget.addEventListener('touchmove', handleMove, { passive: false });
    swipeTarget.addEventListener('touchend', handleEnd, { passive: false });
    
    // Mouse события для ПК
    swipeTarget.addEventListener('mousedown', handleMouseDown, { passive: false });
    document.addEventListener('mousemove', handleMouseMove, { passive: false });
    document.addEventListener('mouseup', handleMouseUp, { passive: false });
    
    function handleStart(e) {
        const touch = e.touches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
        touchCurrentX = touch.clientX;
        isDragging = true;
        startTime = Date.now();
        swipeTarget.style.transition = 'none'; // Убираем анимацию во время перетаскивания
    }
    
    function handleMove(e) {
        if (!isDragging) return;
        
        const touch = e.touches[0];
        touchCurrentX = touch.clientX;
        const deltaY = Math.abs(touch.clientY - touchStartY);
        const deltaX = touch.clientX - touchStartX;
        
        // Если свайп горизонтальный, предотвращаем вертикальную прокрутку
        if (Math.abs(deltaX) > deltaY && Math.abs(deltaX) > 10) {
            e.preventDefault();
            // Применяем трансформацию для визуального следования за пальцем
            const translateX = deltaX * 0.3; // Коэффициент 0.3 для более плавного движения
            swipeTarget.style.transform = `translateX(${translateX}px)`;
        }
    }
    
    function handleEnd(e) {
        if (!isDragging) return;
        
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const deltaX = touchEndX - touchStartX;
        const deltaY = Math.abs(touchEndY - touchStartY);
        const deltaTime = Date.now() - startTime;
        
        // Возвращаем анимацию
        swipeTarget.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        swipeTarget.style.transform = 'translateX(0)';
        
        isDragging = false;
        
        // Проверяем, что свайп горизонтальный
        if (Math.abs(deltaX) > deltaY) {
            // Быстрый свайп или достаточная дистанция
            if (Math.abs(deltaX) > minSwipeDistance || (deltaTime < maxSwipeTime && Math.abs(deltaX) > 30)) {
                // Добавляем небольшую задержку перед переключением для плавности
                setTimeout(() => {
                    if (deltaX > 0) {
                        // Свайп вправо - предыдущее изображение
                        if (isFullscreen) {
                            previousFullscreenImage();
                        } else {
                            previousGalleryImage();
                        }
                        triggerHapticFeedback('light');
                    } else {
                        // Свайп влево - следующее изображение
                        if (isFullscreen) {
                            nextFullscreenImage();
                        } else {
                            nextGalleryImage();
                        }
                        triggerHapticFeedback('light');
                    }
                }, 100);
            }
        }
    }
    
    // Mouse handlers для ПК
    function handleMouseDown(e) {
        // Игнорируем клики по кнопкам
        if (e.target.closest('button') || e.target.closest('.gallery-thumbnail')) {
            return;
        }
        
        isMouseDown = true;
        touchStartX = e.clientX;
        touchStartY = e.clientY;
        touchCurrentX = e.clientX;
        isDragging = false;
        startTime = Date.now();
        swipeTarget.style.cursor = 'grabbing';
        swipeTarget.style.transition = 'none';
        e.preventDefault();
    }
    
    function handleMouseMove(e) {
        if (!isMouseDown) return;
        
        touchCurrentX = e.clientX;
        const deltaY = Math.abs(e.clientY - touchStartY);
        const deltaX = e.clientX - touchStartX;
        
        // Начинаем dragging если прошли минимальное расстояние
        if (Math.abs(deltaX) > 5 || deltaY > 5) {
            isDragging = true;
        }
        
        // Если тянем горизонтально
        if (Math.abs(deltaX) > deltaY && Math.abs(deltaX) > 10) {
            e.preventDefault();
            // Применяем трансформацию для визуального следования за мышью
            const translateX = deltaX * 0.3; // Коэффициент 0.3 для более плавного движения
            swipeTarget.style.transform = `translateX(${translateX}px)`;
        }
    }
    
    function handleMouseUp(e) {
        if (!isMouseDown) return;
        
        const touchEndX = e.clientX;
        const touchEndY = e.clientY;
        const deltaX = touchEndX - touchStartX;
        const deltaY = Math.abs(touchEndY - touchStartY);
        const deltaTime = Date.now() - startTime;
        
        isMouseDown = false;
        swipeTarget.style.cursor = 'grab';
        
        // Возвращаем анимацию
        swipeTarget.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        swipeTarget.style.transform = 'translateX(0)';
        
        // Если было dragging и свайп горизонтальный
        if (isDragging && Math.abs(deltaX) > deltaY) {
            // Быстрый свайп или достаточная дистанция
            if (Math.abs(deltaX) > minSwipeDistance || (deltaTime < maxSwipeTime && Math.abs(deltaX) > 30)) {
                // Добавляем небольшую задержку перед переключением для плавности
                setTimeout(() => {
                    if (deltaX > 0) {
                        // Свайп вправо - предыдущее изображение
                        if (isFullscreen) {
                            previousFullscreenImage();
                        } else {
                            previousGalleryImage();
                        }
                        triggerHapticFeedback('light');
                    } else {
                        // Свайп влево - следующее изображение
                        if (isFullscreen) {
                            nextFullscreenImage();
                        } else {
                            nextGalleryImage();
                        }
                        triggerHapticFeedback('light');
                    }
                }, 100);
                e.preventDefault();
            }
        }
        
        isDragging = false;
    }
}

// ===== ФУНКЦИИ ГАЛЕРЕИ =====

// Установка изображения в галерее
function setGalleryImage(index) {
    if (!window.currentGallery || !window.currentGallery.photos || index < 0 || index >= window.currentGallery.photos.length) {
        return;
    }
    
    window.currentGallery.currentIndex = index;
    
    const mainImage = document.getElementById('main-gallery-image');
    const counter = document.getElementById('gallery-current');
    const thumbnails = document.querySelectorAll('.gallery-thumbnail');
    
    if (mainImage) {
        // Добавляем плавную анимацию появления
        mainImage.style.opacity = '0';
        mainImage.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            mainImage.src = processImageUrl(window.currentGallery.photos[index]);
            
            // Анимация появления нового изображения
            setTimeout(() => {
                mainImage.style.opacity = '1';
                mainImage.style.transform = 'scale(1)';
            }, 50);
        }, 150);
    }
    
    if (counter) {
        counter.textContent = index + 1;
    }
    
    // Обновляем активный thumbnail
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
    
    // Обновляем навигационные кнопки
    updateGalleryNavigation();
}

// Предыдущее изображение
function previousGalleryImage() {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    const newIndex = Math.max(0, window.currentGallery.currentIndex - 1);
    setGalleryImage(newIndex);
}

// Следующее изображение
function nextGalleryImage() {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    const newIndex = Math.min(window.currentGallery.photos.length - 1, window.currentGallery.currentIndex + 1);
    setGalleryImage(newIndex);
}

// Обновление состояния навигационных кнопок
function updateGalleryNavigation() {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    const prevBtn = document.getElementById('gallery-prev');
    const nextBtn = document.getElementById('gallery-next');
    
    if (prevBtn) {
        prevBtn.disabled = window.currentGallery.currentIndex === 0;
    }
    
    if (nextBtn) {
        nextBtn.disabled = window.currentGallery.currentIndex === window.currentGallery.photos.length - 1;
    }
}

// Открытие полноэкранной галереи
function openGalleryFullscreen(startIndex = null) {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    if (startIndex !== null) {
        window.currentGallery.currentIndex = startIndex;
    }
    
    const fullscreenGallery = document.getElementById('gallery-fullscreen');
    const fullscreenImage = document.getElementById('fullscreen-image');
    
    if (fullscreenGallery && fullscreenImage) {
        fullscreenImage.src = processImageUrl(window.currentGallery.photos[window.currentGallery.currentIndex]);
        fullscreenGallery.classList.add('show');
        
        // Блокируем прокрутку
        document.body.style.overflow = 'hidden';
    }
}

// Закрытие полноэкранной галереи
function closeGalleryFullscreen() {
    const fullscreenGallery = document.getElementById('gallery-fullscreen');
    
    if (fullscreenGallery) {
        fullscreenGallery.classList.remove('show');
        
        // Разблокируем прокрутку
        document.body.style.overflow = 'auto';
    }
}

// Предыдущее изображение в полноэкранном режиме
function previousFullscreenImage() {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    const newIndex = Math.max(0, window.currentGallery.currentIndex - 1);
    if (newIndex === window.currentGallery.currentIndex) return; // Уже на первом изображении
    
    window.currentGallery.currentIndex = newIndex;
    
    const fullscreenImage = document.getElementById('fullscreen-image');
    if (fullscreenImage) {
        // Добавляем плавную анимацию
        fullscreenImage.style.opacity = '0';
        fullscreenImage.style.transform = 'scale(0.95) translateX(0)';
        
        setTimeout(() => {
            fullscreenImage.src = processImageUrl(window.currentGallery.photos[newIndex]);
            
            setTimeout(() => {
                fullscreenImage.style.opacity = '1';
                fullscreenImage.style.transform = 'scale(1) translateX(0)';
            }, 50);
        }, 150);
    }
    
    // Обновляем также основную галерею
    setGalleryImage(newIndex);
}

// Следующее изображение в полноэкранном режиме
function nextFullscreenImage() {
    if (!window.currentGallery || !window.currentGallery.photos) return;
    
    const newIndex = Math.min(window.currentGallery.photos.length - 1, window.currentGallery.currentIndex + 1);
    if (newIndex === window.currentGallery.currentIndex) return; // Уже на последнем изображении
    
    window.currentGallery.currentIndex = newIndex;
    
    const fullscreenImage = document.getElementById('fullscreen-image');
    if (fullscreenImage) {
        // Добавляем плавную анимацию
        fullscreenImage.style.opacity = '0';
        fullscreenImage.style.transform = 'scale(0.95) translateX(0)';
        
        setTimeout(() => {
            fullscreenImage.src = processImageUrl(window.currentGallery.photos[newIndex]);
            
            setTimeout(() => {
                fullscreenImage.style.opacity = '1';
                fullscreenImage.style.transform = 'scale(1) translateX(0)';
            }, 50);
        }, 150);
    }
    
    // Обновляем также основную галерею
    setGalleryImage(newIndex);
}

// ===== БЕСКОНЕЧНАЯ ПРОКРУТКА ДЛЯ ТОВАРОВ =====

let isLoadingMoreProducts = false;
let currentProductsPage = 1;
let hasMoreProducts = true;

// Функция для инициализации бесконечной прокрутки
function initInfiniteScroll() {
    const trigger = document.getElementById('infiniteScrollTrigger');
    if (!trigger) {
        console.log('Infinite scroll trigger не найден');
        return;
    }
    
    console.log('Инициализация бесконечной прокрутки товаров');
    
    // Получаем начальные параметры
    currentProductsPage = parseInt(trigger.getAttribute('data-next-page')) || 2;
    hasMoreProducts = trigger.getAttribute('data-has-more') === 'true';
    
    // Создаем Intersection Observer для автоматической загрузки
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMoreProducts && !isLoadingMoreProducts) {
                loadMoreProducts();
            }
        });
    }, {
        root: null,
        rootMargin: '200px', // Начинаем загрузку за 200px до достижения триггера
        threshold: 0.1
    });
    
    observer.observe(trigger);
    
    console.log('Infinite scroll инициализирован, следующая страница:', currentProductsPage);
}

// Функция загрузки дополнительных товаров
async function loadMoreProducts() {
    if (isLoadingMoreProducts || !hasMoreProducts) {
        return;
    }
    
    isLoadingMoreProducts = true;
    
    // Показываем индикатор загрузки
    const loader = document.getElementById('infiniteScrollLoader');
    if (loader) {
        loader.style.display = 'block';
    }
    
    try {
        const shortName = document.querySelector('meta[name="short-name"]')?.content || 
                         window.location.pathname.split('/')[1];
        
        const url = new URL(`/${shortName}`, window.location.origin);
        url.searchParams.set('page', currentProductsPage);
        
        console.log('Загрузка страницы товаров:', currentProductsPage);
        
        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        
        // Парсим HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Извлекаем товары
        const newProducts = doc.querySelectorAll('.product-card');
        
        if (newProducts.length === 0) {
            // Больше нет товаров
            hasMoreProducts = false;
            const trigger = document.getElementById('infiniteScrollTrigger');
            if (trigger) {
                trigger.remove();
            }
            
            // Показываем сообщение о завершении
            if (loader) {
                loader.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #888;">
                        <i class="fas fa-check-circle"></i> Все товары загружены
                    </div>
                `;
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 2000);
            }
            
            console.log('Все товары загружены');
            return;
        }
        
        // Добавляем новые товары в grid
        const productsGrid = document.querySelector('.products-grid');
        if (productsGrid) {
            newProducts.forEach(product => {
                const clonedProduct = product.cloneNode(true);
                
                // Добавляем обработчики событий
                const addToCartBtn = clonedProduct.querySelector('.add-to-cart');
                if (addToCartBtn && !addToCartBtn.disabled) {
                    addToCartBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const productId = parseInt(this.getAttribute('data-product-id'));
                        if (productId) {
                            addToCart(productId);
                        }
                    });
                }
                
                // Клик по карточке для открытия деталей
                clonedProduct.addEventListener('click', function(e) {
                    if (!e.target.closest('.add-to-cart')) {
                        const productId = parseInt(this.getAttribute('data-product-id'));
                        if (productId) {
                            showProductDetails(productId);
                        }
                    }
                });
                
                // Добавляем анимацию появления
                clonedProduct.style.opacity = '0';
                clonedProduct.style.transform = 'translateY(20px)';
                productsGrid.appendChild(clonedProduct);
                
                // Плавное появление
                setTimeout(() => {
                    clonedProduct.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    clonedProduct.style.opacity = '1';
                    clonedProduct.style.transform = 'translateY(0)';
                }, 50);
            });
            
            console.log(`Добавлено ${newProducts.length} товаров`);
        }
        
        // Проверяем есть ли еще страницы
        const nextTrigger = doc.getElementById('infiniteScrollTrigger');
        if (nextTrigger) {
            currentProductsPage = parseInt(nextTrigger.getAttribute('data-next-page')) || (currentProductsPage + 1);
            hasMoreProducts = nextTrigger.getAttribute('data-has-more') === 'true';
        } else {
            hasMoreProducts = false;
        }
        
    } catch (error) {
        console.error('Ошибка загрузки товаров:', error);
        hasMoreProducts = false;
        
        if (loader) {
            loader.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i> Ошибка загрузки товаров
                </div>
            `;
            setTimeout(() => {
                loader.style.display = 'none';
            }, 3000);
        }
    } finally {
        isLoadingMoreProducts = false;
        
        // Скрываем loader если все успешно
        if (loader && hasMoreProducts) {
            loader.style.display = 'none';
        }
    }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, начинаем инициализацию Mini App');
    
    detectCSSGrid();
    
    // Проверяем наличие всех необходимых элементов
    const loadingEl = document.getElementById('loading');
    const appEl = document.getElementById('app');
    
    console.log('Элементы найдены:', {
        loading: !!loadingEl,
        app: !!appEl,
        telegramWebApp: !!(window.Telegram?.WebApp)
    });
    
    // Инициализируем приложение (проверяем, что не инициализирован из Blade)
    try {
        if (!window.isAppInitializedByBlade) {
            console.log('Calling initApp from main DOMContentLoaded');
            initApp();
        } else {
            console.log('initApp already called from Blade template');
        }
    } catch (error) {
        console.error('Критическая ошибка инициализации:', error);
        // Принудительно показываем приложение даже при ошибке
        if (loadingEl) loadingEl.style.display = 'none';
        if (appEl) appEl.style.display = 'block';
    }
    
    // Инициализируем счетчик корзины при загрузке
    setTimeout(() => {
        try {
            updateCartCounter();
        } catch (error) {
            console.error('Ошибка обновления счетчика корзины:', error);
        }
    }, 1000);
    
    // Инициализируем бесконечную прокрутку товаров
    setTimeout(() => {
        try {
            initInfiniteScroll();
        } catch (error) {
            console.error('Ошибка инициализации infinite scroll:', error);
        }
    }, 1500);
    
    // Закрытие панели по ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePanel();
        }
    });
    
    // Обработчики событий для модальных окон
// Функция для настройки обработчиков кликов по backdrop модальных окон
function setupModalBackdropHandlers() {
    const productModal = document.getElementById('productModal');
    const cartModal = document.getElementById('cartModal');
    
    // Обработка кликов по backdrop для productModal
    if (productModal) {
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) {
                closeProductModal();
            }
        });
    }
    
    // Обработка кликов по backdrop для cartModal
    if (cartModal) {
        cartModal.addEventListener('click', function(e) {
            if (e.target === cartModal) {
                closeCartModal();
            }
        });
    }
    
    // Добавляем делегирование событий для карточек товаров
    document.addEventListener('click', function(e) {
        // Клик по карточке товара
        const productCard = e.target.closest('.product-card');
        if (productCard && !e.target.closest('.add-to-cart')) {
            const productId = parseInt(productCard.getAttribute('data-product-id'));
            if (productId) {
                showProductDetails(productId);
            }
        }
        
        // Клик по кнопке добавления в корзину
        const addToCartBtn = e.target.closest('.add-to-cart');
        if (addToCartBtn && !addToCartBtn.disabled) {
            e.stopPropagation();
            const productId = parseInt(addToCartBtn.getAttribute('data-product-id'));
            if (productId) {
                addToCart(productId);
            }
        }
        
        // Клик по карточке категории
        const categoryCard = e.target.closest('.category-card');
        if (categoryCard) {
            const categoryId = parseInt(categoryCard.getAttribute('data-category-id'));
            const categoryName = categoryCard.getAttribute('data-category-name');
            if (categoryId && categoryName) {
                filterByCategory(categoryId, categoryName);
            }
        }
    });
    
    // Добавляем поддержку свайпа для закрытия модальных окон
    addSwipeSupport();
    
    // Добавляем поддержку свайпа для выхода из категории
    addCategorySwipeSupport();
    
    console.log('Обработчики модальных окон настроены');
}

// Экспортируем функцию в window после её определения
window.setupModalBackdropHandlers = setupModalBackdropHandlers;
    
    // Настраиваем защиту от сворачивания через скролл
    preventPullToClose();
});
