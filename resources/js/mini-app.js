// Mini App JavaScript functionality
console.log('Mini App загружается...');

// Переменные для отладки и разработки
const isDevelopmentMode = !window.Telegram?.WebApp;
let userData = null;

// Переменные для поиска и категорий
let allProducts = [];
let allCategories = [];
let isSearchActive = false;

// Основная функция инициализации
function initApp() {
    console.log('Инициализация Mini App...');
    
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
            
            // Скрытие кнопки "Назад"
            tg.BackButton.hide();
            
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
    
    // Инициализация поиска и категорий
    initSearch();
    loadCategories();
    
    // Скрыть загрузочный экран
    setTimeout(() => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('app').style.display = 'block';
    }, 1000);
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
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
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
        const response = await fetch(`/${shortName}/api/categories`);
        
        if (response.ok) {
            allCategories = await response.json();
            console.log('Загружено категорий:', allCategories.length);
            if (allCategories.length > 0) {
                const categoriesContainer = document.getElementById('categoriesContainer');
                if (categoriesContainer) {
                    categoriesContainer.style.display = 'block';
                }
            }
            renderCategories(allCategories);
            
            if (allCategories.length > 0) {
                const categoriesContainer = document.getElementById('categoriesContainer');
                if (categoriesContainer) {
                    categoriesContainer.style.display = 'block';
                }
            }
        } else {
            console.log('Категории не найдены или ошибка загрузки');
        }
    } catch (error) {
        console.error('Ошибка при загрузке категорий:', error);
    }
}

// Отрисовка категорий
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

    track.innerHTML = categories.map(category => `
        <div class="category-card" onclick="filterByCategory(${category.id}, '${category.name.replace(/'/g, "\\'")}')">
            <div class="card h-200">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${category.name}</div>
                            ${category.description ? `<div class="category-description">${category.description}</div>` : ''}
                            <div class="category-products-count">${category.products_count || 0} товаров</div>
                        </div>
                        ${category.photo_url 
                            ? `<img src="${category.photo_url}" class="category-image " alt="${category.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                               </div>`
                            : `<div class="category-placeholder ">
                                   <i class="fas fa-folder"></i>
                               </div>`
                        }
                        
                    </div>
                </div>
            </div>
        </div>
    `).join('');
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
        return;
    }

    isSearchActive = true;

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
            
            return 0;
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
    
    const title = document.getElementById('productsTitle');
    if (title) {
        title.textContent = `Результаты поиска: "${query}"`;
    }

    if (products.length === 0) {
        container.innerHTML = `
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${query}"</h5>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <p>Попробуйте изменить запрос или просмотреть все товары</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;
        return;
    }

    const productsHTML = products.map(product => `
        <div class="product-flex-item">
            <div class="card product-card h-100 ${product.quantity <= 0 ? 'out-of-stock' : ''} ${!product.isAvailable ? 'inactive' : ''}" onclick="showProductDetails(${product.id})" style="cursor: pointer; position: relative;">
                ${product.similarity ? `<div class="badge bg-success position-absolute top-0 start-0 m-2" style="z-index: 10; font-size: 0.7em;">${Math.round(product.similarity)}%</div>` : ''}
                <div class="product-image-container">
                    ${product.photo_url 
                        ? `<img src="${product.photo_url}" class="product-image" alt="${product.name}" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="product-image-placeholder" style="display: none;">
                               <i class="fas fa-image"></i>
                               <span>Ошибка загрузки</span>
                           </div>`
                        : `<div class="product-image-placeholder">
                               <i class="fas fa-cube"></i>
                               <span>Без фото</span>
                           </div>`
                    }
                    <!-- Quantity badge on image -->
                    <span class="quantity-badge ${product.quantity > 10 ? 'quantity-success' : (product.quantity > 0 ? 'quantity-warning' : 'quantity-danger')}">
                        ${product.quantity} шт.
                    </span>
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${product.name}</h6>
                        ${product.description ? `<p class="product-description">${product.description}</p>` : ''}
                        ${product.similarity && product.matchField ? `<small class="text-muted">Совпадение в: ${product.matchField === 'name' ? 'названии' : product.matchField === 'article' ? 'артикуле' : 'описании'}</small>` : ''}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="cart-button-wrapper">
                                ${product.isAvailable ? `
                                <button class="cart-btn cart-btn-primary" 
                                        onclick="event.stopPropagation(); addToCart(${product.id})"
                                        title="Добавить в корзину">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                ` : `
                                <button class="cart-btn cart-btn-disabled" disabled
                                        title="Нет в наличии">
                                    <i class="fas fa-times"></i>
                                </button>
                                `}
                            </div>
                            <div class="product-price-wrapper">
                                <div class="product-price">${formatPrice(product.price)} ₽</div>
                            </div>
                            <div class="product-quantity-wrapper">
                                <span class="quantity-badge quantity-success">
                                    ${product.quantity} шт.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = `
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${query}" (найдено: ${products.length})</h5>
        </div>
        <div class="products-flex-container">
            ${productsHTML}
        </div>
    `;
}

// Фильтрация по категории
function filterByCategory(categoryId, categoryName) {
    console.log('Фильтрация по категории:', categoryId, categoryName);
    console.log('Все товары:', allProducts);
    
    isSearchActive = true;
    
    // Преобразуем categoryId к числу для корректного сравнения
    const numCategoryId = parseInt(categoryId);
    
    const categoryProducts = allProducts.filter(product => {
        const productCategoryId = parseInt(product.category_id);
        console.log(`Товар ${product.name}: category_id=${productCategoryId}, ищем=${numCategoryId}`);
        return productCategoryId === numCategoryId;
    });

    console.log('Найдено товаров в категории:', categoryProducts.length, categoryProducts);

    const container = document.getElementById('productsContainer');
    if (!container) {
        console.error('Контейнер productsContainer не найден');
        return;
    }

    if (categoryProducts.length === 0) {
        container.innerHTML = `
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${categoryName}</h5>
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
    // Отображаем товары категории
    renderCategoryResults(categoryProducts, categoryName);

    // Очищаем поле поиска
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
    }
}

// Отрисовка результатов для категории
function renderCategoryResults(products, categoryName) {
    const container = document.getElementById('productsContainer');
    if (!container) return;

    const productsHTML = products.map(product => `
        <div class="product-flex-item">
            <div class="card product-card h-100 ${product.quantity <= 0 ? 'out-of-stock' : ''} ${!product.isAvailable ? 'inactive' : ''}" onclick="showProductDetails(${product.id})" style="cursor: pointer; position: relative;">
                <div class="product-image-container">
                    ${product.photo_url 
                        ? `<img src="${product.photo_url}" class="product-image" alt="${product.name}" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="product-image-placeholder" style="display: none;">
                               <i class="fas fa-image"></i>
                               <span>Ошибка загрузки</span>
                           </div>`
                        : `<div class="product-image-placeholder">
                               <i class="fas fa-cube"></i>
                               <span>Без фото</span>
                           </div>`
                    }
                    <!-- Quantity badge on image -->
                    <span class="quantity-badge ${product.quantity > 10 ? 'quantity-success' : (product.quantity > 0 ? 'quantity-warning' : 'quantity-danger')}">
                        ${product.quantity} шт.
                    </span>
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${product.name}</h6>
                        ${product.description ? `<p class="product-description">${product.description}</p>` : ''}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="cart-button-wrapper">
                                ${product.isAvailable ? `
                                <button class="cart-btn cart-btn-primary" 
                                        onclick="event.stopPropagation(); addToCart(${product.id})"
                                        title="Добавить в корзину">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                ` : `
                                <button class="cart-btn cart-btn-disabled" disabled
                                        title="Нет в наличии">
                                    <i class="fas fa-times"></i>
                                </button>
                                `}
                            </div>
                            <div class="product-price-wrapper">
                                <div class="product-price">${formatPrice(product.price)} ₽</div>
                            </div>
                            <div class="product-quantity-wrapper">
                                <span class="quantity-badge quantity-success">
                                    ${product.quantity} шт.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = `
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder me-2"></i>Категория: ${categoryName}</h5>
        </div>
        <div class="products-flex-container">
            ${productsHTML}
        </div>
    `;
}

// Показать все товары
function showAllProducts() {
    isSearchActive = false;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    
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
            const counter = document.querySelector('.cart-counter');
            const cartFloat = document.getElementById('cart-float');
            
            if (counter && cartFloat) {
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.style.display = 'inline';
                    cartFloat.style.display = 'block';
                } else {
                    counter.style.display = 'none';
                    cartFloat.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Ошибка получения счетчика корзины:', error);
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

        // Показываем модальное окно с loader
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        title.textContent = 'Загрузка...';
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        `;
        footer.classList.add('d-none');

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
    title.textContent = product.name;
    
    // Генерируем HTML для товара
    body.innerHTML = `
        <div class="row g-4">
            ${product.photo_url ? `
                <div class="col-md-6">
                    <div class="position-relative">
                        <img src="${product.photo_url}" alt="${product.name}" 
                             class="modal-product-image" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="product-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Изображение недоступно</span>
                        </div>
                        ${getProductStatusBadge(product)}
                    </div>
                </div>
            ` : ''}
            
            <div class="${product.photo_url ? 'col-md-6' : 'col-12'}">
                <div class="product-info">
                    ${product.article ? `
                        <p class="text-muted mb-2">
                            <strong>Артикул:</strong> ${product.article}
                        </p>
                    ` : ''}
                    
                    <div class="modal-product-price">
                        ${formatPrice(product.price)} ₽
                    </div>
                    
                    ${product.description ? `
                        <div class="modal-product-description">
                            ${product.description}
                        </div>
                    ` : ''}
                    
                    ${product.specifications ? `
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            <p>${product.specifications}</p>
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
    `;
    
    // Настраиваем footer
    footer.classList.remove('d-none');
    footer.innerHTML = `
        <div class="d-grid gap-2">
            ${product.isAvailable && product.quantity > 0 ? `
                <button type="button" class="btn btn-primary btn-lg" onclick="addToCartFromModal(${product.id})">
                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                </button>
            ` : `
                <button type="button" class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-times me-2"></i>Товар недоступен
                </button>
            `}
        </div>
    `;
    
    // Инициализируем количество если товар доступен
    if (product.isAvailable && product.quantity > 0) {
        setTimeout(() => {
            updateModalQuantity(product.id, 1, product.price, product.quantity);
        }, 100);
    }
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
    
    return `<span class="badge bg-${statusClass} shadow-sm">${product.availability_status}</span>`;
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
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('quantity', quantity);
    
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Товар добавлен в корзину (${quantity} шт.)!`);
            updateCartCounter();
            triggerHapticFeedback('success');
        } else {
            showAlert(data.message || 'Ошибка при добавлении товара', 'error');
            triggerHapticFeedback('error');
        }
    })
    .catch(error => {
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
        
        updateModalQuantity(productId, finalQuantity, product.price, product.quantity);
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

        // Показываем модальное окно с loader
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка корзины...</span>
                </div>
            </div>
        `;
        footer.classList.add('d-none');

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
                footer.classList.add('d-none');
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
            footer.classList.add('d-none');
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
    
    let html = '<div class="cart-items">';
    
    items.forEach(item => {
        html += `
            <div class="cart-item mb-3 p-3 border rounded" data-cart-id="${item.id}">
                <div class="row align-items-center">
                    <div class="col-3">
                        ${item.photo_url ? 
                            `<img src="${item.photo_url}" class="img-fluid rounded" style="max-height: 60px; object-fit: cover;" alt="${item.name}">` :
                            `<div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 60px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>`
                        }
                    </div>
                    <div class="col-6">
                        <h6 class="mb-1">${item.name}</h6>
                        ${item.article ? `<small class="text-muted">Артикул: ${item.article}</small><br>` : ''}
                        <small class="text-muted">${item.formatted_price} за шт.</small>
                    </div>
                    <div class="col-3 text-end">
                        <div class="quantity-controls mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="btn btn-outline-secondary disabled">${item.quantity}</span>
                                <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})" ${item.quantity >= item.available_quantity ? 'disabled' : ''}>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="fw-bold">${item.formatted_total}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeFromCart(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    body.innerHTML = html;
    
    // Показываем футер с итоговой суммой и кнопкой заказа
    footer.innerHTML = `
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <strong>Итого: ${formatPrice(totalAmount)} ₽</strong>
            </div>
            <div>
                <button type="button" class="btn btn-secondary me-2" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Очистить
                </button>
                <button type="button" class="btn btn-primary" onclick="proceedToCheckout()">
                    <i class="fas fa-check"></i> Оформить заказ
                </button>
            </div>
        </div>
    `;
    footer.classList.remove('d-none');
}

// Функция обновления количества товара в корзине
function updateCartQuantity(cartId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(cartId);
        return;
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
            showAlert('Количество обновлено');
            updateCartCounter();
            // Перезагружаем корзину
            showCartModal();
        } else {
            showAlert(data.message || 'Ошибка при обновлении количества', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении количества:', error);
        showAlert('Ошибка при обновлении количества', 'error');
    });
}

// Функция удаления товара из корзины
function removeFromCart(cartId) {
    if (!confirm('Удалить товар из корзины?')) {
        return;
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
            showAlert('Товар удален из корзины');
            updateCartCounter();
            // Перезагружаем корзину
            showCartModal();
        } else {
            showAlert(data.message || 'Ошибка при удалении товара', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при удалении товара:', error);
        showAlert('Ошибка при удалении товара', 'error');
    });
}

// Функция очистки корзины
function clearCart() {
    if (!confirm('Очистить всю корзину?')) {
        return;
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
            showAlert('Корзина очищена');
            updateCartCounter();
            // Закрываем модальное окно
            const modal = document.getElementById('cartModal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        } else {
            showAlert(data.message || 'Ошибка при очистке корзины', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при очистке корзины:', error);
        showAlert('Ошибка при очистке корзины', 'error');
    });
}

// Функция перехода к оформлению заказа
function proceedToCheckout() {
    showAlert('Функция оформления заказа будет реализована в следующих версиях', 'info');
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

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    detectCSSGrid();
    
    // Инициализируем счетчик корзины при загрузке
    setTimeout(() => {
        updateCartCounter();
    }, 1000);
    
    // Закрытие панели по ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePanel();
        }
    });
});