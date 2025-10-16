console.log("Mini App загружается...");var I;(I=window.Telegram)!=null&&I.WebApp;let p=null,u=[],b=[];function A(){var n;console.log("Инициализация Mini App...");const t=setTimeout(()=>{console.log("Принудительно показываем приложение после тайм-аута");try{const e=document.getElementById("loading"),a=document.getElementById("app");e&&(e.style.display="none"),a&&(a.style.display="block")}catch(e){console.error("Ошибка при принудительном показе приложения:",e)}},3e3);if(window.Telegram&&window.Telegram.WebApp){const e=window.Telegram.WebApp;try{e.ready(),e.expand(),console.log("Telegram WebApp инициализирован"),console.log("Init data:",e.initData),console.log("User data:",(n=e.initDataUnsafe)==null?void 0:n.user),e.initDataUnsafe&&e.initDataUnsafe.user?(p=e.initDataUnsafe.user,$(p)):e.initData&&(p=_(e.initData),p&&$(p)),document.documentElement.style.setProperty("--tg-color-scheme",e.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",e.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",e.themeParams.text_color||"#000000"),e.BackButton.hide(),console.log("Telegram WebApp полностью настроен")}catch(a){console.error("Ошибка инициализации Telegram WebApp:",a),Q("Ошибка загрузки Telegram WebApp")}}else console.log("Режим разработки - Telegram WebApp недоступен"),p={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},$(p);try{O(),U().catch(e=>{console.error("Ошибка загрузки категорий:",e)})}catch(e){console.error("Ошибка инициализации поиска/категорий:",e)}setTimeout(()=>{try{clearTimeout(t);const e=document.getElementById("loading"),a=document.getElementById("app");e&&(e.style.display="none"),a&&(a.style.display="block"),console.log("Mini App загружен успешно")}catch(e){console.error("Ошибка при скрытии загрузочного экрана:",e)}},800)}function Q(t){const n=document.createElement("div");n.className="alert alert-danger",n.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,n.innerHTML=`
        <strong>Ошибка:</strong> ${t}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(n),setTimeout(()=>{n.parentElement&&n.remove()},5e3)}function $(t){console.log("Данные пользователя:",t);const n=document.querySelector(".user-greeting");if(n&&t){const e=t.first_name||t.username||"Пользователь";n.textContent=`Привет, ${e}!`}}function _(t){try{const e=new URLSearchParams(t).get("user");if(e)return JSON.parse(decodeURIComponent(e))}catch(n){console.error("Ошибка парсинга user из initData:",n)}return null}function m(t,n="info"){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showAlert)try{window.Telegram.WebApp.showAlert(t);return}catch(e){console.warn("Не удалось показать Telegram уведомление:",e)}r(t,n)}function r(t,n="info"){const e=document.getElementById("toast-container")||R(),a=document.createElement("div");a.className=`toast align-items-center text-white bg-${n==="error"?"danger":n==="success"?"success":"primary"} border-0`,a.setAttribute("role","alert"),a.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${t}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,e.appendChild(a),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(a).show(),a.addEventListener("hidden.bs.toast",()=>{a.remove()})):(a.style.display="block",setTimeout(()=>{a.remove()},5e3))}function R(){const t=document.createElement("div");return t.id="toast-container",t.className="toast-container position-fixed top-0 end-0 p-3",t.style.zIndex="9999",document.body.appendChild(t),t}function N(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function O(){const t=document.getElementById("searchInput");if(!t)return;let n;t.addEventListener("input",function(e){clearTimeout(n),n=setTimeout(()=>{E(e.target.value)},300)}),t.addEventListener("keypress",function(e){e.key==="Enter"&&E(e.target.value)}),X()}async function X(){var t;try{const n=document.getElementById("products-data");if(n)try{const s=JSON.parse(n.textContent);u=Object.values(s),console.log("Загружено товаров из встроенных данных:",u.length);return}catch(s){console.warn("Ошибка парсинга встроенных данных товаров:",s)}const e=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],a=await fetch(`/${e}/api/products`);a.ok&&(u=await a.json(),console.log("Загружено товаров через API:",u.length))}catch(n){console.error("Ошибка при загрузке товаров:",n)}}async function U(){var t;try{const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],e=new AbortController,a=setTimeout(()=>e.abort(),5e3),s=await fetch(`/${n}/api/categories`,{signal:e.signal,headers:{Accept:"application/json","Content-Type":"application/json"}});if(clearTimeout(a),s.ok){if(b=await s.json(),console.log("Загружено категорий:",b.length),b.length>0){const i=document.getElementById("categoriesContainer");i&&(i.style.display="block"),z(b)}}else console.log("Категории не найдены или ошибка загрузки:",s.status)}catch(n){n.name==="AbortError"?console.log("Загрузка категорий прервана по тайм-ауту"):console.error("Ошибка при загрузке категорий:",n)}}function z(t){console.log("Отрисовка категорий:",t);const n=document.getElementById("categoriesTrack");if(!n){console.error("Элемент categoriesTrack не найден");return}if(t.length===0){const e=document.getElementById("categoriesContainer");e&&(e.style.display="none");return}n.innerHTML=t.map(e=>`
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
    `).join("")}function E(t=null){const n=document.getElementById("searchInput"),e=t!==null?t:n?n.value.trim():"";if(console.log("Выполняется поиск по запросу:",e),e===""||e.length<2){k();return}const a=65,i=u.map(o=>{const c=o.name||"",l=o.description||"",d=o.article||"",v=T(e,c),q=d?T(e,d):0,j=l?T(e,l):0,w=Math.max(v,q,j);return{...o,similarity:w,matchField:v===w?"name":q===w?"article":"description"}}).filter(o=>o.similarity>=a).sort((o,c)=>c.similarity!==o.similarity?c.similarity-o.similarity:o.matchField==="name"&&c.matchField!=="name"?-1:c.matchField==="name"&&o.matchField!=="name"?1:o.matchField==="article"&&c.matchField==="description"?-1:c.matchField==="article"&&o.matchField==="description"?1:0);console.log(`Найдено товаров: ${i.length} из ${u.length}`),i.forEach(o=>{console.log(`- ${o.name}: ${o.similarity.toFixed(1)}% (поле: ${o.matchField})`)}),G(i,e)}function G(t,n){const e=document.getElementById("productsContainer");if(!e)return;const a=document.getElementById("productsTitle");if(a&&(a.textContent=`Результаты поиска: "${n}"`),t.length===0){e.innerHTML=`
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${n}"</h5>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <p>Попробуйте изменить запрос или просмотреть все товары</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const s=t.map(i=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${i.quantity<=0?"out-of-stock":""} ${i.isAvailable?"":"inactive"}" onclick="showProductDetails(${i.id})" style="cursor: pointer; position: relative;">
                ${i.similarity?`<div class="badge bg-success position-absolute top-0 start-0 m-2" style="z-index: 10; font-size: 0.7em;">${Math.round(i.similarity)}%</div>`:""}
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
                        ${i.similarity&&i.matchField?`<small class="text-muted">Совпадение в: ${i.matchField==="name"?"названии":i.matchField==="article"?"артикуле":"описании"}</small>`:""}
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
                                <div class="product-price">${h(i.price)} ₽</div>
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
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${n}" (найдено: ${t.length})</h5>
        </div>
        <div class="products-flex-container">
            ${s}
        </div>
    `}function J(t,n){console.log("Фильтрация по категории:",t,n),console.log("Все товары:",u);const e=parseInt(t),a=u.filter(o=>{const c=parseInt(o.category_id);return console.log(`Товар ${o.name}: category_id=${c}, ищем=${e}`),c===e});console.log("Найдено товаров в категории:",a.length,a);const s=document.getElementById("productsContainer");if(!s){console.error("Контейнер productsContainer не найден");return}if(a.length===0){s.innerHTML=`
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
        `;return}K(a,n);const i=document.getElementById("searchInput");i&&(i.value="")}function K(t,n){const e=document.getElementById("productsContainer");if(!e)return;const a=t.map(s=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${s.quantity<=0?"out-of-stock":""} ${s.isAvailable?"":"inactive"}" onclick="showProductDetails(${s.id})" style="cursor: pointer; position: relative;">
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
                                <div class="product-price">${h(s.price)} ₽</div>
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
            <h5 id="productsTitle"><i class="fas fa-folder me-2"></i>Категория: ${n}</h5>
        </div>
        <div class="products-flex-container">
            ${a}
        </div>
    `}function k(){const t=document.getElementById("searchInput");t&&(t.value=""),window.location.reload()}function h(t){return Number(t).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function Y(t,n){const e=[];for(let a=0;a<=n.length;a++)e[a]=[a];for(let a=0;a<=t.length;a++)e[0][a]=a;for(let a=1;a<=n.length;a++)for(let s=1;s<=t.length;s++)n.charAt(a-1)===t.charAt(s-1)?e[a][s]=e[a-1][s-1]:e[a][s]=Math.min(e[a-1][s-1]+1,e[a][s-1]+1,e[a-1][s]+1);return e[n.length][t.length]}function x(t,n){const e=t.length>n.length?t:n,a=t.length>n.length?n:t;if(e.length===0)return 100;const s=Y(e,a),i=(e.length-s)/e.length*100;return Math.max(0,i)}function T(t,n){const e=t.toLowerCase(),a=n.toLowerCase();if(a.includes(e))return 100;const s=x(e,a),i=a.split(/\s+/);let o=0;i.forEach(l=>{if(l.length>=2){const d=x(e,l);o=Math.max(o,d)}});let c=0;if(e.length>=3)for(let l=0;l<=a.length-e.length;l++){const d=a.substring(l,l+e.length),v=x(e,d);c=Math.max(c,v)}return Math.max(s,o,c)}function V(t){C(t,1)}function g(){fetch("/cart/count").then(t=>t.json()).then(t=>{const n=document.querySelector(".cart-counter"),e=document.getElementById("cart-float");n&&e&&(t.count>0?(n.textContent=t.count,n.style.display="inline",e.style.display="block"):(n.style.display="none",e.style.display="none"))}).catch(t=>{console.error("Ошибка получения счетчика корзины:",t)})}async function Z(t){var n;try{const e=document.getElementById("productModal"),a=document.getElementById("productModalTitle"),s=document.getElementById("productModalBody"),i=document.getElementById("productModalFooter");if(!e||!s||!i){console.error("Элементы модального окна не найдены");return}new bootstrap.Modal(e).show(),a.textContent="Загрузка...",s.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        `,i.classList.add("d-none");const c=(n=document.querySelector('meta[name="short-name"]'))==null?void 0:n.getAttribute("content");if(!c)throw new Error("Short name не найден");const l=await fetch(`/${c}/api/products/${t}`);if(!l.ok)throw new Error(`HTTP error! status: ${l.status}`);const d=await l.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[t]=d,tt(d,s,a,i)}catch(e){console.error("Ошибка при загрузке данных товара:",e),m("Ошибка при загрузке данных товара","error")}}function tt(t,n,e,a){e.textContent=t.name,n.innerHTML=`
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
                        ${st(t)}
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
                            <span class="badge ${it(t.quantity)}">
                                ${ot(t.quantity)}
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
    `,t.isAvailable&&t.quantity>0&&setTimeout(()=>{F(t.id,1,t.price,t.quantity)},100)}function L(){const t=document.getElementById("productPanel"),n=document.getElementById("panelBackdrop"),e=document.getElementById("productPanelFooter");n&&n.classList.remove("show"),t&&t.classList.remove("show"),e&&(e.style.display="none"),document.body.style.overflow="auto"}function et(t,n){const e=document.getElementById(`quantity-${t}`);if(!e)return;const a=parseInt(e.value)||1,s=Math.max(1,Math.min(parseInt(e.max),a+n));e.value=s,S(t,s),P(t,s),y("light")}function nt(t){const n=document.getElementById(`quantity-${t}`);if(!n)return;const e=parseInt(n.value),a=parseInt(n.max);isNaN(e)||e<1?n.value=1:e>a&&(n.value=a,m(`Максимальное количество: ${a} шт.`,"warning"));const s=parseInt(n.value);S(t,s),P(t,s)}function S(t,n){const e=window.cachedProductsData?window.cachedProductsData[t]:null;if(e){const a=e.price*n,s=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(a),i=document.getElementById(`totalPrice-${t}`);i&&(i.textContent=s)}}function P(t,n){const e=document.getElementById(`decreaseBtn-${t}`),a=document.getElementById(`increaseBtn-${t}`),s=document.getElementById(`quantity-${t}`);if(e&&(e.disabled=n<=1),a&&s){const i=parseInt(s.max);a.disabled=n>=i}}function C(t,n){const e=new FormData;e.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),e.append("quantity",n),fetch(`/cart/add/${t}`,{method:"POST",body:e}).then(a=>a.json()).then(a=>{a.success?(m(`Товар добавлен в корзину (${n} шт.)!`),g(),y("success")):(m(a.message||"Ошибка при добавлении товара","error"),y("error"))}).catch(a=>{console.error("Ошибка при добавлении товара в корзину:",a),m("Ошибка при добавлении товара в корзину","error"),y("error")})}function at(){H()}function y(t="light"){var n,e,a;try{(a=(e=(n=window.Telegram)==null?void 0:n.WebApp)==null?void 0:e.HapticFeedback)!=null&&a.impactOccurred&&typeof window.Telegram.WebApp.HapticFeedback.impactOccurred=="function"&&window.Telegram.WebApp.HapticFeedback.impactOccurred(t)}catch(s){console.debug("HapticFeedback не поддерживается:",s.message)}}function st(t){return(t.is_active!==void 0?t.is_active:t.isAvailable)?t.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':t.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${t.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function it(t){return"bg-success"}function ot(t){return t<=0?"Нет в наличии":`${t} шт.`}function ct(t,n){var o;const e=document.getElementById(`modal-quantity-${t}`);if(!e)return;const a=parseInt(e.textContent)||1,s=Math.max(1,a+n),i=(o=window.cachedProductsData)==null?void 0:o[t];if(i){const c=Math.min(i.quantity,99),l=Math.min(s,c);F(t,l,i.price,i.quantity)}}function F(t,n,e,a){const s=document.getElementById(`modal-quantity-${t}`),i=document.getElementById(`modal-decrease-${t}`),o=document.getElementById(`modal-increase-${t}`),c=document.getElementById(`modal-total-price-${t}`);if(s&&(s.textContent=n),i&&(i.disabled=n<=1),o&&(o.disabled=n>=a),c&&e){const l=n*e;c.textContent=`Итого: ${h(l)} ₽`}}function lt(t){const n=document.getElementById(`modal-quantity-${t}`),e=n&&parseInt(n.textContent)||1;C(t,e);const a=document.getElementById("productModal");if(a){const s=bootstrap.Modal.getInstance(a);s&&s.hide()}}function H(){try{const t=document.getElementById("cartModal"),n=document.getElementById("cartModalBody"),e=document.getElementById("cartModalFooter");if(!t||!n){console.error("Элементы модального окна корзины не найдены");return}new bootstrap.Modal(t).show(),n.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка корзины...</span>
                </div>
            </div>
        `,e.classList.add("d-none"),fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(s=>s.json()).then(s=>{s.success&&s.items&&s.items.length>0?D(s.items,s.total_amount):(n.innerHTML=`
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина пуста</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `,e.classList.add("d-none"))}).catch(s=>{console.error("Ошибка при загрузке корзины:",s),n.innerHTML=`
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Ошибка при загрузке корзины</h5>
                    <p class="text-muted">Попробуйте обновить страницу</p>
                </div>
            `,e.classList.add("d-none")})}catch(t){console.error("Ошибка при загрузке корзины:",t),m("Ошибка при загрузке корзины","error")}}function D(t,n){const e=document.getElementById("cartModalBody"),a=document.getElementById("cartModalFooter");if(!e||!a)return;if(!t||t.length===0){e.innerHTML=`
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Корзина пуста</h5>
                <p class="text-muted">Добавьте товары для оформления заказа</p>
            </div>
        `,a.classList.add("d-none");return}let s='<div class="cart-items">';t.forEach(i=>{s+=`
            <div class="cart-item mb-3 p-3 border rounded"style="flex-direction: column;" data-cart-id="${i.id}">
                <div class="d-flex align-items-start">
                    <div class="cart-item-image me-3 flex-shrink-0">
                        ${i.photo_url?`<img src="${i.photo_url}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="${i.name}">`:`<div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>`}
                    </div>
                    
                    <div class="cart-item-info flex-grow-1" >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold">${i.name}</h6>
                                ${i.article?`<small class="text-muted d-block">Артикул: ${i.article}</small>`:""}
                                <div class="text-primary fw-semibold">${i.formatted_price} за шт.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${i.id})" title="Удалить товар">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                       
                    </div>
                </div>
                 <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                            <div class="quantity-controls">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${i.id}, ${i.quantity-1})" ${i.quantity<=1?"disabled":""}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary disabled px-3">${i.quantity} шт</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${i.id}, ${i.quantity+1})" ${i.quantity>=i.available_quantity?"disabled":""}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-item-total">
                                <strong class="text-success fs-5">${i.formatted_total}</strong>
                            </div>
                        </div>
            </div>
        `}),s+="</div>",e.innerHTML=s,a.innerHTML=`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100">
            <div class="cart-total" style="width:100%;">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Итого: ${h(n)} ₽
                </h5>
                <small class="text-muted">Товаров в корзине: ${t.length}</small>
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
    `,a.classList.remove("d-none")}function rt(t,n){if(n<=0){W(t);return}const e=document.querySelector(`[data-cart-id="${t}"]`);if(e){const a=e.querySelector(".quantity-controls");a&&(a.style.opacity="0.5",a.style.pointerEvents="none")}fetch(`/cart/update/${t}`,{method:"PATCH",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"},body:JSON.stringify({quantity:n})}).then(a=>a.json()).then(a=>{a.success?(r("Количество обновлено","success"),g(),f(),y("light")):r(a.message||"Ошибка при обновлении количества","error")}).catch(a=>{console.error("Ошибка при обновлении количества:",a),r("Ошибка при обновлении количества","error")}).finally(()=>{if(e){const a=e.querySelector(".quantity-controls");a&&(a.style.opacity="1",a.style.pointerEvents="auto")}})}function f(){const t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");!t||!n||fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success&&e.items&&e.items.length>0?D(e.items,e.total_amount):(t.innerHTML=`
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пуста</h5>
                    <p class="text-muted">Добавьте товары для оформления заказа</p>
                </div>
            `,n.classList.add("d-none"))}).catch(e=>{console.error("Ошибка при обновлении корзины:",e)})}function W(t){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Удалить товар из корзины?",n=>{n&&M(t)});else{if(!confirm("Удалить товар из корзины?"))return;M(t)}}function M(t){const n=document.querySelector(`[data-cart-id="${t}"]`);n&&(n.style.opacity="0.5",n.style.transform="scale(0.95)",n.style.pointerEvents="none"),fetch(`/cart/remove/${t}`,{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success?(r("Товар удален из корзины","success"),g(),y("medium"),n?(n.style.transition="all 0.3s ease",n.style.opacity="0",n.style.transform="translateX(100%)",setTimeout(()=>{f()},300)):f()):(r(e.message||"Ошибка при удалении товара","error"),n&&(n.style.opacity="1",n.style.transform="scale(1)",n.style.pointerEvents="auto"))}).catch(e=>{console.error("Ошибка при удалении товара:",e),r("Ошибка при удалении товара","error"),n&&(n.style.opacity="1",n.style.transform="scale(1)",n.style.pointerEvents="auto")})}function dt(){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Очистить всю корзину?",t=>{t&&B()});else{if(!confirm("Очистить всю корзину?"))return;B()}}function B(){const t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");t&&(t.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Очистка корзины...</span>
                </div>
                <div class="mt-3">Очищаем корзину...</div>
            </div>
        `),n&&n.classList.add("d-none"),fetch("/cart/clear",{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success?(r("Корзина очищена","success"),g(),y("medium"),t&&(t.innerHTML=`
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина очищена</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `),setTimeout(()=>{const a=document.getElementById("cartModal");if(a){const s=bootstrap.Modal.getInstance(a);s&&s.hide()}},1500)):(r(e.message||"Ошибка при очистке корзины","error"),f())}).catch(e=>{console.error("Ошибка при очистке корзины:",e),r("Ошибка при очистке корзины","error"),f()})}function ut(){m("Функция оформления заказа будет реализована в следующих версиях","info")}window.initApp=A;window.showAlert=m;window.filterByCategory=J;window.showAllProducts=k;window.performSearch=E;window.addToCart=V;window.addToCartWithQuantity=C;window.showProductDetails=Z;window.showCart=at;window.showCartModal=H;window.closePanel=L;window.changeQuantityModal=ct;window.addToCartFromModal=lt;window.changeQuantity=et;window.validateQuantity=nt;window.updateCartQuantity=rt;window.removeFromCart=W;window.clearCart=dt;window.proceedToCheckout=ut;window.refreshCartContent=f;document.addEventListener("DOMContentLoaded",function(){var e;console.log("DOM загружен, начинаем инициализацию Mini App"),N();const t=document.getElementById("loading"),n=document.getElementById("app");console.log("Элементы найдены:",{loading:!!t,app:!!n,telegramWebApp:!!((e=window.Telegram)!=null&&e.WebApp)});try{A()}catch(a){console.error("Критическая ошибка инициализации:",a),t&&(t.style.display="none"),n&&(n.style.display="block")}setTimeout(()=>{try{g()}catch(a){console.error("Ошибка обновления счетчика корзины:",a)}},1e3),document.addEventListener("keydown",function(a){a.key==="Escape"&&L()})});
