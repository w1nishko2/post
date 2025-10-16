console.log("Mini App загружается...");var x;(x=window.Telegram)!=null&&x.WebApp;let m=null,u=[],p=[];function S(){var t;if(console.log("Инициализация Mini App..."),window.Telegram&&window.Telegram.WebApp){const n=window.Telegram.WebApp;try{n.ready(),n.expand(),console.log("Telegram WebApp инициализирован"),console.log("Init data:",n.initData),console.log("User data:",(t=n.initDataUnsafe)==null?void 0:t.user),n.initDataUnsafe&&n.initDataUnsafe.user?(m=n.initDataUnsafe.user,v(m)):n.initData&&(m=L(n.initData),m&&v(m)),document.documentElement.style.setProperty("--tg-color-scheme",n.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",n.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",n.themeParams.text_color||"#000000"),n.BackButton.hide()}catch(e){console.error("Ошибка инициализации Telegram WebApp:",e),k("Ошибка загрузки Telegram WebApp")}}else console.log("Режим разработки - Telegram WebApp недоступен"),m={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},v(m);H(),W(),setTimeout(()=>{document.getElementById("loading").style.display="none",document.getElementById("app").style.display="block"},1e3)}function k(t){const n=document.createElement("div");n.className="alert alert-danger",n.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,n.innerHTML=`
        <strong>Ошибка:</strong> ${t}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(n),setTimeout(()=>{n.parentElement&&n.remove()},5e3)}function v(t){console.log("Данные пользователя:",t);const n=document.querySelector(".user-greeting");if(n&&t){const e=t.first_name||t.username||"Пользователь";n.textContent=`Привет, ${e}!`}}function L(t){try{const e=new URLSearchParams(t).get("user");if(e)return JSON.parse(decodeURIComponent(e))}catch(n){console.error("Ошибка парсинга user из initData:",n)}return null}function d(t,n="info"){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showAlert)try{window.Telegram.WebApp.showAlert(t);return}catch(e){console.warn("Не удалось показать Telegram уведомление:",e)}F(t,n)}function F(t,n="info"){const e=document.getElementById("toast-container")||D(),a=document.createElement("div");a.className=`toast align-items-center text-white bg-${n==="error"?"danger":n==="success"?"success":"primary"} border-0`,a.setAttribute("role","alert"),a.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${t}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,e.appendChild(a),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(a).show(),a.addEventListener("hidden.bs.toast",()=>{a.remove()})):(a.style.display="block",setTimeout(()=>{a.remove()},5e3))}function D(){const t=document.createElement("div");return t.id="toast-container",t.className="toast-container position-fixed top-0 end-0 p-3",t.style.zIndex="9999",document.body.appendChild(t),t}function Q(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function H(){const t=document.getElementById("searchInput");if(!t)return;let n;t.addEventListener("input",function(e){clearTimeout(n),n=setTimeout(()=>{$(e.target.value)},300)}),t.addEventListener("keypress",function(e){e.key==="Enter"&&$(e.target.value)}),_()}async function _(){var t;try{const n=document.getElementById("products-data");if(n)try{const i=JSON.parse(n.textContent);u=Object.values(i),console.log("Загружено товаров из встроенных данных:",u.length);return}catch(i){console.warn("Ошибка парсинга встроенных данных товаров:",i)}const e=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],a=await fetch(`/${e}/api/products`);a.ok&&(u=await a.json(),console.log("Загружено товаров через API:",u.length))}catch(n){console.error("Ошибка при загрузке товаров:",n)}}async function W(){var t;try{const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],e=await fetch(`/${n}/api/categories`);if(e.ok){if(p=await e.json(),console.log("Загружено категорий:",p.length),p.length>0){const a=document.getElementById("categoriesContainer");a&&(a.style.display="block")}if(j(p),p.length>0){const a=document.getElementById("categoriesContainer");a&&(a.style.display="block")}}else console.log("Категории не найдены или ошибка загрузки")}catch(n){console.error("Ошибка при загрузке категорий:",n)}}function j(t){console.log("Отрисовка категорий:",t);const n=document.getElementById("categoriesTrack");if(!n){console.error("Элемент categoriesTrack не найден");return}if(t.length===0){const e=document.getElementById("categoriesContainer");e&&(e.style.display="none");return}n.innerHTML=t.map(e=>`
        <div class="category-card" onclick="filterByCategory(${e.id}, '${e.name.replace(/'/g,"\\'")}')">
            <div class="card h-200">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${e.name}</div>
                            ${e.description?`<div class="category-description">${e.description}</div>`:""}
                            <div class="category-products-count">${e.products_count||0} товаров</div>
                        </div>
                        ${e.photo_url?`<img src="${e.photo_url}" class="category-image " alt="${e.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                               </div>`:`<div class="category-placeholder ">
                                   <i class="fas fa-folder"></i>
                               </div>`}
                        
                    </div>
                </div>
            </div>
        </div>
    `).join("")}function $(t=null){const n=document.getElementById("searchInput"),e=t!==null?t:n?n.value.trim():"";if(console.log("Выполняется поиск по запросу:",e),e===""||e.length<2){I();return}const a=65,s=u.map(o=>{const c=o.name||"",l=o.description||"",r=o.article||"",y=w(e,c),T=r?w(e,r):0,P=l?w(e,l):0,h=Math.max(y,T,P);return{...o,similarity:h,matchField:y===h?"name":T===h?"article":"description"}}).filter(o=>o.similarity>=a).sort((o,c)=>c.similarity!==o.similarity?c.similarity-o.similarity:o.matchField==="name"&&c.matchField!=="name"?-1:c.matchField==="name"&&o.matchField!=="name"?1:o.matchField==="article"&&c.matchField==="description"?-1:c.matchField==="article"&&o.matchField==="description"?1:0);console.log(`Найдено товаров: ${s.length} из ${u.length}`),s.forEach(o=>{console.log(`- ${o.name}: ${o.similarity.toFixed(1)}% (поле: ${o.matchField})`)}),U(s,e)}function U(t,n){const e=document.getElementById("productsContainer");if(!e)return;const a=document.getElementById("productsTitle");if(a&&(a.textContent=`Результаты поиска: "${n}"`),t.length===0){e.innerHTML=`
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${n}"</h5>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <p>Попробуйте изменить запрос или просмотреть все товары</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const i=t.map(s=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${s.quantity<=0?"out-of-stock":""} ${s.isAvailable?"":"inactive"}" onclick="showProductDetails(${s.id})" style="cursor: pointer; position: relative;">
                ${s.similarity?`<div class="badge bg-success position-absolute top-0 start-0 m-2" style="z-index: 10; font-size: 0.7em;">${Math.round(s.similarity)}%</div>`:""}
                <div class="product-image-container">
                    ${s.photo_url?`<img src="${s.photo_url}" class="product-image" alt="${s.name}" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="product-image-placeholder" style="display: none;">
                               <i class="fas fa-image"></i>
                               <span>Ошибка загрузки</span>
                           </div>`:`<div class="product-image-placeholder">
                               <i class="fas fa-cube"></i>
                               <span>Без фото</span>
                           </div>`}
                    <!-- Quantity badge on image -->
                    <span class="quantity-badge ${s.quantity>10?"quantity-success":s.quantity>0?"quantity-warning":"quantity-danger"}">
                        ${s.quantity} шт.
                    </span>
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${s.name}</h6>
                        ${s.description?`<p class="product-description">${s.description}</p>`:""}
                        ${s.similarity&&s.matchField?`<small class="text-muted">Совпадение в: ${s.matchField==="name"?"названии":s.matchField==="article"?"артикуле":"описании"}</small>`:""}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="cart-button-wrapper">
                                ${s.isAvailable?`
                                <button class="cart-btn cart-btn-primary" 
                                        onclick="event.stopPropagation(); addToCart(${s.id})"
                                        title="Добавить в корзину">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                `:`
                                <button class="cart-btn cart-btn-disabled" disabled
                                        title="Нет в наличии">
                                    <i class="fas fa-times"></i>
                                </button>
                                `}
                            </div>
                            <div class="product-price-wrapper">
                                <div class="product-price">${g(s.price)} ₽</div>
                            </div>
                            <div class="product-quantity-wrapper">
                                <span class="quantity-badge quantity-success">
                                    ${s.quantity} шт.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join("");e.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${n}" (найдено: ${t.length})</h5>
        </div>
        <div class="products-flex-container">
            ${i}
        </div>
    `}function N(t,n){console.log("Фильтрация по категории:",t,n),console.log("Все товары:",u);const e=parseInt(t),a=u.filter(o=>{const c=parseInt(o.category_id);return console.log(`Товар ${o.name}: category_id=${c}, ищем=${e}`),c===e});console.log("Найдено товаров в категории:",a.length,a);const i=document.getElementById("productsContainer");if(!i){console.error("Контейнер productsContainer не найден");return}if(a.length===0){i.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${n}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h6>В этой категории пока нет товаров</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}O(a,n);const s=document.getElementById("searchInput");s&&(s.value="")}function O(t,n){const e=document.getElementById("productsContainer");if(!e)return;const a=t.map(i=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${i.quantity<=0?"out-of-stock":""} ${i.isAvailable?"":"inactive"}" onclick="showProductDetails(${i.id})" style="cursor: pointer; position: relative;">
                <div class="product-image-container">
                    ${i.photo_url?`<img src="${i.photo_url}" class="product-image" alt="${i.name}" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="product-image-placeholder" style="display: none;">
                               <i class="fas fa-image"></i>
                               <span>Ошибка загрузки</span>
                           </div>`:`<div class="product-image-placeholder">
                               <i class="fas fa-cube"></i>
                               <span>Без фото</span>
                           </div>`}
                    <!-- Quantity badge on image -->
                    <span class="quantity-badge ${i.quantity>10?"quantity-success":i.quantity>0?"quantity-warning":"quantity-danger"}">
                        ${i.quantity} шт.
                    </span>
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${i.name}</h6>
                        ${i.description?`<p class="product-description">${i.description}</p>`:""}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="cart-button-wrapper">
                                ${i.isAvailable?`
                                <button class="cart-btn cart-btn-primary" 
                                        onclick="event.stopPropagation(); addToCart(${i.id})"
                                        title="Добавить в корзину">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                `:`
                                <button class="cart-btn cart-btn-disabled" disabled
                                        title="Нет в наличии">
                                    <i class="fas fa-times"></i>
                                </button>
                                `}
                            </div>
                            <div class="product-price-wrapper">
                                <div class="product-price">${g(i.price)} ₽</div>
                            </div>
                            <div class="product-quantity-wrapper">
                                <span class="quantity-badge quantity-success">
                                    ${i.quantity} шт.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join("");e.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder me-2"></i>Категория: ${n}</h5>
        </div>
        <div class="products-flex-container">
            ${a}
        </div>
    `}function I(){const t=document.getElementById("searchInput");t&&(t.value=""),window.location.reload()}function g(t){return Number(t).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function R(t,n){const e=[];for(let a=0;a<=n.length;a++)e[a]=[a];for(let a=0;a<=t.length;a++)e[0][a]=a;for(let a=1;a<=n.length;a++)for(let i=1;i<=t.length;i++)n.charAt(a-1)===t.charAt(i-1)?e[a][i]=e[a-1][i-1]:e[a][i]=Math.min(e[a-1][i-1]+1,e[a][i-1]+1,e[a-1][i]+1);return e[n.length][t.length]}function b(t,n){const e=t.length>n.length?t:n,a=t.length>n.length?n:t;if(e.length===0)return 100;const i=R(e,a),s=(e.length-i)/e.length*100;return Math.max(0,s)}function w(t,n){const e=t.toLowerCase(),a=n.toLowerCase();if(a.includes(e))return 100;const i=b(e,a),s=a.split(/\s+/);let o=0;s.forEach(l=>{if(l.length>=2){const r=b(e,l);o=Math.max(o,r)}});let c=0;if(e.length>=3)for(let l=0;l<=a.length-e.length;l++){const r=a.substring(l,l+e.length),y=b(e,r);c=Math.max(c,y)}return Math.max(i,o,c)}function z(t){E(t,1)}function M(){fetch("/cart/count").then(t=>t.json()).then(t=>{const n=document.querySelector(".cart-counter"),e=document.getElementById("cart-float");n&&e&&(t.count>0?(n.textContent=t.count,n.style.display="inline",e.style.display="block"):(n.style.display="none",e.style.display="none"))}).catch(t=>{console.error("Ошибка получения счетчика корзины:",t)})}async function G(t){var n;try{const e=document.getElementById("productModal"),a=document.getElementById("productModalTitle"),i=document.getElementById("productModalBody"),s=document.getElementById("productModalFooter");if(!e||!i||!s){console.error("Элементы модального окна не найдены");return}new bootstrap.Modal(e).show(),a.textContent="Загрузка...",i.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        `,s.classList.add("d-none");const c=(n=document.querySelector('meta[name="short-name"]'))==null?void 0:n.getAttribute("content");if(!c)throw new Error("Short name не найден");const l=await fetch(`/${c}/api/products/${t}`);if(!l.ok)throw new Error(`HTTP error! status: ${l.status}`);const r=await l.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[t]=r,J(r,i,a,s)}catch(e){console.error("Ошибка при загрузке данных товара:",e),d("Ошибка при загрузке данных товара","error")}}function J(t,n,e,a){e.textContent=t.name,n.innerHTML=`
        <div class="row g-4">
            ${t.photo_url?`
                <div class="col-md-6">
                    <div class="position-relative">
                        <img src="${t.photo_url}" alt="${t.name}" 
                             class="modal-product-image" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="product-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Изображение недоступно</span>
                        </div>
                        ${X(t)}
                    </div>
                </div>
            `:""}
            
            <div class="${t.photo_url?"col-md-6":"col-12"}">
                <div class="product-info">
                    ${t.article?`
                        <p class="text-muted mb-2">
                            <strong>Артикул:</strong> ${t.article}
                        </p>
                    `:""}
                    
                    <div class="modal-product-price">
                        ${g(t.price)} ₽
                    </div>
                    
                    ${t.description?`
                        <div class="modal-product-description">
                            ${t.description}
                        </div>
                    `:""}
                    
                    ${t.specifications?`
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            <p>${t.specifications}</p>
                        </div>
                    `:""}
                    
                    <div class="availability-info mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #374151; font-weight: 600;">Наличие:</span>
                            <span class="badge ${Z(t.quantity)}">
                                ${tt(t.quantity)}
                            </span>
                        </div>
                    </div>
                    
                    ${t.isAvailable&&t.quantity>0?`
                        <div class="quantity-selector">
                            <label style="color: #374151; font-weight: 600;">Количество:</label>
                            <div class="d-flex align-items-center gap-3 justify-content-center">
                                <button class="quantity-btn" onclick="changeQuantityModal(${t.id}, -1)" id="modal-decrease-${t.id}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span id="modal-quantity-${t.id}" class="quantity-display">1</span>
                                <button class="quantity-btn" onclick="changeQuantityModal(${t.id}, 1)" id="modal-increase-${t.id}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div id="modal-total-price-${t.id}" class="text-center mt-2" style="font-weight: 600; color: var(--primary-color);"></div>
                        </div>
                    `:""}
                </div>
            </div>
        </div>
    `,a.classList.remove("d-none"),a.innerHTML=`
        <div class="d-grid gap-2">
            ${t.isAvailable&&t.quantity>0?`
                <button type="button" class="btn btn-primary btn-lg" onclick="addToCartFromModal(${t.id})">
                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                </button>
            `:`
                <button type="button" class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-times me-2"></i>Товар недоступен
                </button>
            `}
        </div>
    `,t.isAvailable&&t.quantity>0&&setTimeout(()=>{A(t.id,1,t.price,t.quantity)},100)}function B(){const t=document.getElementById("productPanel"),n=document.getElementById("panelBackdrop"),e=document.getElementById("productPanelFooter");n&&n.classList.remove("show"),t&&t.classList.remove("show"),e&&(e.style.display="none"),document.body.style.overflow="auto"}function Y(t,n){const e=document.getElementById(`quantity-${t}`);if(!e)return;const a=parseInt(e.value)||1,i=Math.max(1,Math.min(parseInt(e.max),a+n));e.value=i,C(t,i),q(t,i),f("light")}function K(t){const n=document.getElementById(`quantity-${t}`);if(!n)return;const e=parseInt(n.value),a=parseInt(n.max);isNaN(e)||e<1?n.value=1:e>a&&(n.value=a,d(`Максимальное количество: ${a} шт.`,"warning"));const i=parseInt(n.value);C(t,i),q(t,i)}function C(t,n){const e=window.cachedProductsData?window.cachedProductsData[t]:null;if(e){const a=e.price*n,i=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(a),s=document.getElementById(`totalPrice-${t}`);s&&(s.textContent=i)}}function q(t,n){const e=document.getElementById(`decreaseBtn-${t}`),a=document.getElementById(`increaseBtn-${t}`),i=document.getElementById(`quantity-${t}`);if(e&&(e.disabled=n<=1),a&&i){const s=parseInt(i.max);a.disabled=n>=s}}function E(t,n){const e=new FormData;e.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),e.append("quantity",n),fetch(`/cart/add/${t}`,{method:"POST",body:e}).then(a=>a.json()).then(a=>{a.success?(d(`Товар добавлен в корзину (${n} шт.)!`),M(),f("success")):(d(a.message||"Ошибка при добавлении товара","error"),f("error"))}).catch(a=>{console.error("Ошибка при добавлении товара в корзину:",a),d("Ошибка при добавлении товара в корзину","error"),f("error")})}function V(){fetch("/cart").then(t=>t.json()).then(t=>{t.items&&t.items.length>0?showCheckoutModal(t.items,t.total):d("Ваша корзина пуста","warning")}).catch(t=>{console.error("Ошибка при получении корзины:",t),d("Ошибка при загрузке корзины","error")})}function f(t="light"){var n,e,a;try{(a=(e=(n=window.Telegram)==null?void 0:n.WebApp)==null?void 0:e.HapticFeedback)!=null&&a.impactOccurred&&typeof window.Telegram.WebApp.HapticFeedback.impactOccurred=="function"&&window.Telegram.WebApp.HapticFeedback.impactOccurred(t)}catch(i){console.debug("HapticFeedback не поддерживается:",i.message)}}function X(t){return(t.is_active!==void 0?t.is_active:t.isAvailable)?t.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':t.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${t.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function Z(t){return"bg-success"}function tt(t){return t<=0?"Нет в наличии":`${t} шт.`}function et(t,n){var o;const e=document.getElementById(`modal-quantity-${t}`);if(!e)return;const a=parseInt(e.textContent)||1,i=Math.max(1,a+n),s=(o=window.cachedProductsData)==null?void 0:o[t];if(s){const c=Math.min(s.quantity,99),l=Math.min(i,c);A(t,l,s.price,s.quantity)}}function A(t,n,e,a){const i=document.getElementById(`modal-quantity-${t}`),s=document.getElementById(`modal-decrease-${t}`),o=document.getElementById(`modal-increase-${t}`),c=document.getElementById(`modal-total-price-${t}`);if(i&&(i.textContent=n),s&&(s.disabled=n<=1),o&&(o.disabled=n>=a),c&&e){const l=n*e;c.textContent=`Итого: ${g(l)} ₽`}}function nt(t){const n=document.getElementById(`modal-quantity-${t}`),e=n&&parseInt(n.textContent)||1;E(t,e);const a=document.getElementById("productModal");if(a){const i=bootstrap.Modal.getInstance(a);i&&i.hide()}}function at(){try{const t=document.getElementById("cartModal"),n=document.getElementById("cartModalBody"),e=document.getElementById("cartModalFooter");if(!t||!n){console.error("Элементы модального окна корзины не найдены");return}new bootstrap.Modal(t).show(),n.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Обновление корзины...</span>
                </div>
            </div>
        `,e.classList.add("d-none"),setTimeout(()=>{n.innerHTML=`
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пока не реализована</h5>
                    <p class="text-muted">Функционал корзины будет добавлен позже</p>
                </div>
            `},1e3)}catch(t){console.error("Ошибка при загрузке корзины:",t),d("Ошибка при загрузке корзины","error")}}window.initApp=S;window.showAlert=d;window.filterByCategory=N;window.showAllProducts=I;window.performSearch=$;window.addToCart=z;window.addToCartWithQuantity=E;window.showProductDetails=G;window.showCart=V;window.showCartModal=at;window.closePanel=B;window.changeQuantityModal=et;window.addToCartFromModal=nt;window.changeQuantity=Y;window.validateQuantity=K;document.addEventListener("DOMContentLoaded",function(){Q(),setTimeout(()=>{M()},1e3),document.addEventListener("keydown",function(t){t.key==="Escape"&&B()})});
