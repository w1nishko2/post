console.log("Mini App загружается...");var B;(B=window.Telegram)!=null&&B.WebApp;let m=null,u=[],p=[];function F(){var t;if(console.log("Инициализация Mini App..."),window.Telegram&&window.Telegram.WebApp){const e=window.Telegram.WebApp;try{e.ready(),e.expand(),console.log("Telegram WebApp инициализирован"),console.log("Init data:",e.initData),console.log("User data:",(t=e.initDataUnsafe)==null?void 0:t.user),e.initDataUnsafe&&e.initDataUnsafe.user?(m=e.initDataUnsafe.user,v(m)):e.initData&&(m=D(e.initData),m&&v(m)),document.documentElement.style.setProperty("--tg-color-scheme",e.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",e.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",e.themeParams.text_color||"#000000"),e.BackButton.hide()}catch(n){console.error("Ошибка инициализации Telegram WebApp:",n),q("Ошибка загрузки Telegram WebApp")}}else console.log("Режим разработки - Telegram WebApp недоступен"),m={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},v(m);W(),U(),setTimeout(()=>{document.getElementById("loading").style.display="none",document.getElementById("app").style.display="block"},1e3)}function q(t){const e=document.createElement("div");e.className="alert alert-danger",e.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,e.innerHTML=`
        <strong>Ошибка:</strong> ${t}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(e),setTimeout(()=>{e.parentElement&&e.remove()},5e3)}function v(t){console.log("Данные пользователя:",t);const e=document.querySelector(".user-greeting");if(e&&t){const n=t.first_name||t.username||"Пользователь";e.textContent=`Привет, ${n}!`}}function D(t){try{const n=new URLSearchParams(t).get("user");if(n)return JSON.parse(decodeURIComponent(n))}catch(e){console.error("Ошибка парсинга user из initData:",e)}return null}function d(t,e="info"){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showAlert)try{window.Telegram.WebApp.showAlert(t);return}catch(n){console.warn("Не удалось показать Telegram уведомление:",n)}_(t,e)}function _(t,e="info"){const n=document.getElementById("toast-container")||Q(),i=document.createElement("div");i.className=`toast align-items-center text-white bg-${e==="error"?"danger":e==="success"?"success":"primary"} border-0`,i.setAttribute("role","alert"),i.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${t}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,n.appendChild(i),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(i).show(),i.addEventListener("hidden.bs.toast",()=>{i.remove()})):(i.style.display="block",setTimeout(()=>{i.remove()},5e3))}function Q(){const t=document.createElement("div");return t.id="toast-container",t.className="toast-container position-fixed top-0 end-0 p-3",t.style.zIndex="9999",document.body.appendChild(t),t}function H(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function W(){const t=document.getElementById("searchInput");if(!t)return;let e;t.addEventListener("input",function(n){clearTimeout(e),e=setTimeout(()=>{$(n.target.value)},300)}),t.addEventListener("keypress",function(n){n.key==="Enter"&&$(n.target.value)}),j()}async function j(){var t;try{const e=document.getElementById("products-data");if(e)try{const a=JSON.parse(e.textContent);u=Object.values(a),console.log("Загружено товаров из встроенных данных:",u.length);return}catch(a){console.warn("Ошибка парсинга встроенных данных товаров:",a)}const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],i=await fetch(`/${n}/api/products`);i.ok&&(u=await i.json(),console.log("Загружено товаров через API:",u.length))}catch(e){console.error("Ошибка при загрузке товаров:",e)}}async function U(){var t;try{const e=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],n=await fetch(`/${e}/api/categories`);if(n.ok){if(p=await n.json(),console.log("Загружено категорий:",p.length),p.length>0){const i=document.getElementById("categoriesContainer");i&&(i.style.display="block")}if(N(p),p.length>0){const i=document.getElementById("categoriesContainer");i&&(i.style.display="block")}}else console.log("Категории не найдены или ошибка загрузки")}catch(e){console.error("Ошибка при загрузке категорий:",e)}}function N(t){console.log("Отрисовка категорий:",t);const e=document.getElementById("categoriesTrack");if(!e){console.error("Элемент categoriesTrack не найден");return}if(t.length===0){const n=document.getElementById("categoriesContainer");n&&(n.style.display="none");return}e.innerHTML=t.map(n=>`
        <div class="category-card" onclick="filterByCategory(${n.id}, '${n.name.replace(/'/g,"\\'")}')">
            <div class="card h-200">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${n.name}</div>
                            ${n.description?`<div class="category-description">${n.description}</div>`:""}
                            <div class="category-products-count">${n.products_count||0} товаров</div>
                        </div>
                        ${n.photo_url?`<img src="${n.photo_url}" class="category-image " alt="${n.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                               </div>`:`<div class="category-placeholder ">
                                   <i class="fas fa-folder"></i>
                               </div>`}
                        
                    </div>
                </div>
            </div>
        </div>
    `).join("")}function $(t=null){const e=document.getElementById("searchInput"),n=t!==null?t:e?e.value.trim():"";if(console.log("Выполняется поиск по запросу:",n),n===""||n.length<2){I();return}const i=65,s=u.map(o=>{const c=o.name||"",l=o.description||"",r=o.article||"",f=b(n,c),T=r?b(n,r):0,L=l?b(n,l):0,g=Math.max(f,T,L);return{...o,similarity:g,matchField:f===g?"name":T===g?"article":"description"}}).filter(o=>o.similarity>=i).sort((o,c)=>c.similarity!==o.similarity?c.similarity-o.similarity:o.matchField==="name"&&c.matchField!=="name"?-1:c.matchField==="name"&&o.matchField!=="name"?1:o.matchField==="article"&&c.matchField==="description"?-1:c.matchField==="article"&&o.matchField==="description"?1:0);console.log(`Найдено товаров: ${s.length} из ${u.length}`),s.forEach(o=>{console.log(`- ${o.name}: ${o.similarity.toFixed(1)}% (поле: ${o.matchField})`)}),O(s,n)}function O(t,e){const n=document.getElementById("productsContainer");if(!n)return;const i=document.getElementById("productsTitle");if(i&&(i.textContent=`Результаты поиска: "${e}"`),t.length===0){n.innerHTML=`
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${e}"</h5>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <p>Попробуйте изменить запрос или просмотреть все товары</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const a=t.map(s=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${s.quantity<=0?"out-of-stock":""} ${s.isAvailable?"":"inactive"}" onclick="showProductDetails(${s.id})" style="cursor: pointer; position: relative;">
                ${s.similarity?`<div class="badge bg-success position-absolute top-0 start-0 m-2" style="z-index: 10; font-size: 0.7em;">${Math.round(s.similarity)}%</div>`:""}
                ${x(s)}
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
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${s.name}</h6>
                        ${s.description?`<p class="product-description">${s.description}</p>`:""}
                        ${s.similarity&&s.matchField?`<small class="text-muted">Совпадение в: ${s.matchField==="name"?"названии":s.matchField==="article"?"артикуле":"описании"}</small>`:""}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="product-price-wrapper">
                                <div class="product-price">${h(s.price)} ₽</div>
                            </div>
                            ${S(s)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${e}" (найдено: ${t.length})</h5>
        </div>
        <div class="products-flex-container">
            ${a}
        </div>
    `}function R(t,e){console.log("Фильтрация по категории:",t,e),console.log("Все товары:",u);const n=parseInt(t),i=u.filter(o=>{const c=parseInt(o.category_id);return console.log(`Товар ${o.name}: category_id=${c}, ищем=${n}`),c===n});console.log("Найдено товаров в категории:",i.length,i);const a=document.getElementById("productsContainer");if(!a){console.error("Контейнер productsContainer не найден");return}if(i.length===0){a.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${e}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h6>В этой категории пока нет товаров</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}z(i,e);const s=document.getElementById("searchInput");s&&(s.value="")}function z(t,e){const n=document.getElementById("productsContainer");if(!n)return;const i=t.map(a=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${a.quantity<=0?"out-of-stock":""} ${a.isAvailable?"":"inactive"}" onclick="showProductDetails(${a.id})" style="cursor: pointer; position: relative;">
                <div class="product-status">
                    ${x(a)}
                </div>
                <div class="product-image-container">
                    ${a.photo_url?`<img src="${a.photo_url}" class="product-image" alt="${a.name}" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="product-image-placeholder" style="display: none;">
                               <i class="fas fa-image"></i>
                               <span>Ошибка загрузки</span>
                           </div>`:`<div class="product-image-placeholder">
                               <i class="fas fa-cube"></i>
                               <span>Без фото</span>
                           </div>`}
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${a.name}</h6>
                        ${a.description?`<p class="product-description">${a.description}</p>`:""}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="product-price-wrapper">
                                <div class="product-price">${h(a.price)} ₽</div>
                            </div>
                            ${S(a)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder me-2"></i>Категория: ${e}</h5>
        </div>
        <div class="products-flex-container">
            ${i}
        </div>
    `}function I(){const t=document.getElementById("searchInput");t&&(t.value=""),window.location.reload()}function h(t){return Number(t).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function G(t,e){const n=[];for(let i=0;i<=e.length;i++)n[i]=[i];for(let i=0;i<=t.length;i++)n[0][i]=i;for(let i=1;i<=e.length;i++)for(let a=1;a<=t.length;a++)e.charAt(i-1)===t.charAt(a-1)?n[i][a]=n[i-1][a-1]:n[i][a]=Math.min(n[i-1][a-1]+1,n[i][a-1]+1,n[i-1][a]+1);return n[e.length][t.length]}function w(t,e){const n=t.length>e.length?t:e,i=t.length>e.length?e:t;if(n.length===0)return 100;const a=G(n,i),s=(n.length-a)/n.length*100;return Math.max(0,s)}function b(t,e){const n=t.toLowerCase(),i=e.toLowerCase();if(i.includes(n))return 100;const a=w(n,i),s=i.split(/\s+/);let o=0;s.forEach(l=>{if(l.length>=2){const r=w(n,l);o=Math.max(o,r)}});let c=0;if(n.length>=3)for(let l=0;l<=i.length-n.length;l++){const r=i.substring(l,l+n.length),f=w(n,r);c=Math.max(c,f)}return Math.max(a,o,c)}function J(t){E(t,1)}function M(){fetch("/cart/count").then(t=>t.json()).then(t=>{const e=document.querySelector(".cart-counter"),n=document.getElementById("cart-float");e&&n&&(t.count>0?(e.textContent=t.count,e.style.display="inline",n.style.display="block"):(e.style.display="none",n.style.display="none"))}).catch(t=>{console.error("Ошибка получения счетчика корзины:",t)})}async function Y(t){var e;try{const n=document.getElementById("productModal"),i=document.getElementById("productModalTitle"),a=document.getElementById("productModalBody"),s=document.getElementById("productModalFooter");if(!n||!a||!s){console.error("Элементы модального окна не найдены");return}new bootstrap.Modal(n).show(),i.textContent="Загрузка...",a.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        `,s.classList.add("d-none");const c=(e=document.querySelector('meta[name="short-name"]'))==null?void 0:e.getAttribute("content");if(!c)throw new Error("Short name не найден");const l=await fetch(`/${c}/api/products/${t}`);if(!l.ok)throw new Error(`HTTP error! status: ${l.status}`);const r=await l.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[t]=r,K(r,a,i,s)}catch(n){console.error("Ошибка при загрузке данных товара:",n),d("Ошибка при загрузке данных товара","error")}}function K(t,e,n,i){n.textContent=t.name,e.innerHTML=`
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
                        ${x(t)}
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
                        ${h(t.price)} ₽
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
                            <span class="badge ${tt(t.quantity)}">
                                ${et(t.quantity)}
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
    `,i.classList.remove("d-none"),i.innerHTML=`
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
    `,t.isAvailable&&t.quantity>0&&setTimeout(()=>{k(t.id,1,t.price,t.quantity)},100)}function C(){const t=document.getElementById("productPanel"),e=document.getElementById("panelBackdrop"),n=document.getElementById("productPanelFooter");e&&e.classList.remove("show"),t&&t.classList.remove("show"),n&&(n.style.display="none"),document.body.style.overflow="auto"}function S(t){let e="secondary";return t.availability_status==="В наличии"?e="success":t.availability_status==="Заканчивается"?e="warning":t.availability_status==="Нет в наличии"&&(e="danger"),`<span class="badge bg-${e} shadow-sm">${t.availability_status}</span>`}function V(t,e){const n=document.getElementById(`quantity-${t}`);if(!n)return;const i=parseInt(n.value)||1,a=Math.max(1,Math.min(parseInt(n.max),i+e));n.value=a,A(t,a),P(t,a),y("light")}function X(t){const e=document.getElementById(`quantity-${t}`);if(!e)return;const n=parseInt(e.value),i=parseInt(e.max);isNaN(n)||n<1?e.value=1:n>i&&(e.value=i,d(`Максимальное количество: ${i} шт.`,"warning"));const a=parseInt(e.value);A(t,a),P(t,a)}function A(t,e){const n=window.cachedProductsData?window.cachedProductsData[t]:null;if(n){const i=n.price*e,a=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(i),s=document.getElementById(`totalPrice-${t}`);s&&(s.textContent=a)}}function P(t,e){const n=document.getElementById(`decreaseBtn-${t}`),i=document.getElementById(`increaseBtn-${t}`),a=document.getElementById(`quantity-${t}`);if(n&&(n.disabled=e<=1),i&&a){const s=parseInt(a.max);i.disabled=e>=s}}function E(t,e){const n=new FormData;n.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),n.append("quantity",e),fetch(`/cart/add/${t}`,{method:"POST",body:n}).then(i=>i.json()).then(i=>{i.success?(d(`Товар добавлен в корзину (${e} шт.)!`),M(),y("success")):(d(i.message||"Ошибка при добавлении товара","error"),y("error"))}).catch(i=>{console.error("Ошибка при добавлении товара в корзину:",i),d("Ошибка при добавлении товара в корзину","error"),y("error")})}function Z(){fetch("/cart").then(t=>t.json()).then(t=>{t.items&&t.items.length>0?showCheckoutModal(t.items,t.total):d("Ваша корзина пуста","warning")}).catch(t=>{console.error("Ошибка при получении корзины:",t),d("Ошибка при загрузке корзины","error")})}function y(t="light"){var e,n,i;try{(i=(n=(e=window.Telegram)==null?void 0:e.WebApp)==null?void 0:n.HapticFeedback)!=null&&i.impactOccurred&&typeof window.Telegram.WebApp.HapticFeedback.impactOccurred=="function"&&window.Telegram.WebApp.HapticFeedback.impactOccurred(t)}catch(a){console.debug("HapticFeedback не поддерживается:",a.message)}}function x(t){return(t.is_active!==void 0?t.is_active:t.isAvailable)?t.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':t.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${t.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function tt(t){return t<=0?"bg-danger":t<=5?"bg-warning":"bg-success"}function et(t){return t<=0?"Нет в наличии":`${t} шт.`}function nt(t,e){var o;const n=document.getElementById(`modal-quantity-${t}`);if(!n)return;const i=parseInt(n.textContent)||1,a=Math.max(1,i+e),s=(o=window.cachedProductsData)==null?void 0:o[t];if(s){const c=Math.min(s.quantity,99),l=Math.min(a,c);k(t,l,s.price,s.quantity)}}function k(t,e,n,i){const a=document.getElementById(`modal-quantity-${t}`),s=document.getElementById(`modal-decrease-${t}`),o=document.getElementById(`modal-increase-${t}`),c=document.getElementById(`modal-total-price-${t}`);if(a&&(a.textContent=e),s&&(s.disabled=e<=1),o&&(o.disabled=e>=i),c&&n){const l=e*n;c.textContent=`Итого: ${h(l)} ₽`}}function it(t){const e=document.getElementById(`modal-quantity-${t}`),n=e&&parseInt(e.textContent)||1;E(t,n);const i=document.getElementById("productModal");if(i){const a=bootstrap.Modal.getInstance(i);a&&a.hide()}}function at(){try{const t=document.getElementById("cartModal"),e=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");if(!t||!e){console.error("Элементы модального окна корзины не найдены");return}new bootstrap.Modal(t).show(),e.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Обновление корзины...</span>
                </div>
            </div>
        `,n.classList.add("d-none"),setTimeout(()=>{e.innerHTML=`
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пока не реализована</h5>
                    <p class="text-muted">Функционал корзины будет добавлен позже</p>
                </div>
            `},1e3)}catch(t){console.error("Ошибка при загрузке корзины:",t),d("Ошибка при загрузке корзины","error")}}window.initApp=F;window.showAlert=d;window.filterByCategory=R;window.showAllProducts=I;window.performSearch=$;window.addToCart=J;window.addToCartWithQuantity=E;window.showProductDetails=Y;window.showCart=Z;window.showCartModal=at;window.closePanel=C;window.changeQuantityModal=nt;window.addToCartFromModal=it;window.changeQuantity=V;window.validateQuantity=X;document.addEventListener("DOMContentLoaded",function(){H(),setTimeout(()=>{M()},1e3),document.addEventListener("keydown",function(t){t.key==="Escape"&&C()})});
