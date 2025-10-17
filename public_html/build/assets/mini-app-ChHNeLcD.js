console.log("Mini App загружается...");var j;(j=window.Telegram)!=null&&j.WebApp;let y=null,b=[],C=[],E=!1,$=!1;function g(t){if(!t)return"";const n={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"};return String(t).replace(/[&<>"']/g,function(e){return n[e]})}function nt(){const t=document.querySelector('meta[name="csrf-token"]');return t?t.getAttribute("content"):null}function ot(t,n={}){const e=nt();return n.method&&["POST","PUT","DELETE","PATCH"].includes(n.method.toUpperCase())&&(n.headers={"Content-Type":"application/json",Accept:"application/json",...n.headers},e&&(n.headers["X-CSRF-TOKEN"]=e)),fetch(t,n)}function R(){var n;console.log("Инициализация Mini App...");const t=setTimeout(()=>{console.log("Принудительно показываем приложение после тайм-аута");try{const e=document.getElementById("loading"),o=document.getElementById("app");e&&(e.style.display="none"),o&&(o.style.display="block")}catch(e){console.error("Ошибка при принудительном показе приложения:",e)}},3e3);if(window.Telegram&&window.Telegram.WebApp){const e=window.Telegram.WebApp;try{e.ready(),e.expand(),console.log("Telegram WebApp инициализирован"),console.log("Init data:",e.initData),console.log("User data:",(n=e.initDataUnsafe)==null?void 0:n.user),e.initDataUnsafe&&e.initDataUnsafe.user?(y=e.initDataUnsafe.user,B(y)):e.initData&&(y=st(e.initData),y&&B(y)),document.documentElement.style.setProperty("--tg-color-scheme",e.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",e.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",e.themeParams.text_color||"#000000"),V(),e.BackButton.hide(),console.log("Telegram WebApp полностью настроен")}catch(o){console.error("Ошибка инициализации Telegram WebApp:",o),at("Ошибка загрузки Telegram WebApp")}}else console.log("Режим разработки - Telegram WebApp недоступен"),y={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},B(y);try{rt(),dt().catch(e=>{console.error("Ошибка загрузки категорий:",e)})}catch(e){console.error("Ошибка инициализации поиска/категорий:",e)}setTimeout(()=>{try{clearTimeout(t);const e=document.getElementById("loading"),o=document.getElementById("app");e&&(e.style.display="none"),o&&(o.style.display="block"),console.log("Mini App загружен успешно")}catch(e){console.error("Ошибка при скрытии загрузочного экрана:",e)}},800)}function at(t){const n=document.createElement("div");n.className="alert alert-danger",n.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,n.innerHTML=`
        <strong>Ошибка:</strong> ${t}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(n),setTimeout(()=>{n.parentElement&&n.remove()},5e3)}function B(t){console.log("Данные пользователя:",t);const n=document.querySelector(".user-greeting");if(n&&t){const e=t.first_name||t.username||"Пользователь";n.textContent=`Привет, ${e}!`}}function st(t){try{const e=new URLSearchParams(t).get("user");if(e)return JSON.parse(decodeURIComponent(e))}catch(n){console.error("Ошибка парсинга user из initData:",n)}return null}function u(t,n="info"){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showAlert)try{window.Telegram.WebApp.showAlert(t);return}catch(e){console.warn("Не удалось показать Telegram уведомление:",e)}f(t,n)}function f(t,n="info"){const e=document.getElementById("toast-container")||it(),o=document.createElement("div");o.className=`toast align-items-center text-white bg-${n==="error"?"danger":n==="success"?"success":"primary"} border-0`,o.setAttribute("role","alert"),o.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${t}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,e.appendChild(o),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(o).show(),o.addEventListener("hidden.bs.toast",()=>{o.remove()})):(o.style.display="block",setTimeout(()=>{o.remove()},5e3))}function it(){const t=document.createElement("div");return t.id="toast-container",t.className="toast-container position-fixed top-0 end-0 p-3",t.style.zIndex="9999",document.body.appendChild(t),t}function ct(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function rt(){const t=document.getElementById("searchInput");if(!t)return;let n;t.addEventListener("input",function(e){clearTimeout(n),n=setTimeout(()=>{q(e.target.value)},300)}),t.addEventListener("keypress",function(e){e.key==="Enter"&&q(e.target.value)}),lt()}async function lt(){var t;try{const n=document.getElementById("products-data");if(n)try{const a=JSON.parse(n.textContent);b=Object.values(a),console.log("Загружено товаров из встроенных данных:",b.length);return}catch(a){console.warn("Ошибка парсинга встроенных данных товаров:",a)}const e=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],o=await fetch(`/${e}/api/products`);o.ok&&(b=await o.json(),console.log("Загружено товаров через API:",b.length))}catch(n){console.error("Ошибка при загрузке товаров:",n)}}async function dt(){var t;try{const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],e=new AbortController,o=setTimeout(()=>e.abort(),5e3),a=await fetch(`/${n}/api/categories`,{signal:e.signal,headers:{Accept:"application/json","Content-Type":"application/json"}});if(clearTimeout(o),a.ok){if(C=await a.json(),console.log("Загружено категорий:",C.length),C.length>0){const s=document.getElementById("categoriesContainer");s&&(s.style.display="block"),ut(C)}}else console.log("Категории не найдены или ошибка загрузки:",a.status)}catch(n){n.name==="AbortError"?console.log("Загрузка категорий прервана по тайм-ауту"):console.error("Ошибка при загрузке категорий:",n)}}function ut(t){console.log("Отрисовка категорий:",t);const n=document.getElementById("categoriesTrack");if(!n){console.error("Элемент categoriesTrack не найден");return}if(t.length===0){const e=document.getElementById("categoriesContainer");e&&(e.style.display="none");return}n.innerHTML=t.map(e=>`
        <div class="category-card" onclick="filterByCategory(${e.id}, '${g(e.name).replace(/'/g,"\\'")}')">
            <div class="card h-200">
                <div class="card-body ">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${g(e.name)}</div>
                            ${e.description?`<div class="category-description">${g(e.description)}</div>`:""}
                            <div class="category-products-count">${e.products_count||0} товаров</div>
                        </div>
                        ${e.photo_url?`<img src="${g(e.photo_url)}" class="category-image " alt="${g(e.name)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                               </div>`:`<div class="category-placeholder ">
                                   <i class="fas fa-folder"></i>
                               </div>`}
                        
                    </div>
                </div>
            </div>
        </div>
    `).join("")}function q(t=null){const n=document.getElementById("searchInput"),e=t!==null?t:n?n.value.trim():"";if(console.log("Выполняется поиск по запросу:",e),e===""||e.length<2){S(),E=!1,$=!1;return}E=!0,$=!1;const o=65,s=b.map(c=>{const i=c.name||"",r=c.description||"",l=c.article||"",d=I(e,i),p=l?I(e,l):0,v=r?I(e,r):0,h=Math.max(d,p,v);return{...c,similarity:h,matchField:d===h?"name":p===h?"article":"description"}}).filter(c=>c.similarity>=o).sort((c,i)=>{if(i.similarity!==c.similarity)return i.similarity-c.similarity;if(c.matchField==="name"&&i.matchField!=="name")return-1;if(i.matchField==="name"&&c.matchField!=="name")return 1;if(c.matchField==="article"&&i.matchField==="description")return-1;if(i.matchField==="article"&&c.matchField==="description")return 1;const r=(c.quantity||0)>0?1:0,l=(i.quantity||0)>0?1:0;if(l!==r)return l-r;const d=c.created_at?new Date(c.created_at).getTime():0;return(i.created_at?new Date(i.created_at).getTime():0)-d});console.log(`Найдено товаров: ${s.length} из ${b.length}`),s.forEach(c=>{console.log(`- ${c.name}: ${c.similarity.toFixed(1)}% (поле: ${c.matchField})`)}),mt(s,e)}function mt(t,n){const e=document.getElementById("productsContainer");if(!e)return;const o=document.getElementById("productsTitle");if(o&&(o.textContent=`Результаты поиска: "${n}"`),t.length===0){e.innerHTML=`
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${n}"</h5>
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
                                <div class="product-price">${x(s.price)} ₽</div>
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
            ${a}
        </div>
    `}function pt(t,n){console.log("Фильтрация по категории:",t,n),console.log("Все товары:",b),E=!0,$=!0;const e=parseInt(t),o=b.filter(c=>{const i=parseInt(c.category_id);return console.log(`Товар ${c.name}: category_id=${i}, ищем=${e}`),i===e});console.log("Найдено товаров в категории:",o.length,o);const a=document.getElementById("productsContainer");if(!a){console.error("Контейнер productsContainer не найден");return}if(o.length===0){a.innerHTML=`
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
        `;return}ft(o,n);const s=document.getElementById("searchInput");s&&(s.value=""),document.body.classList.add("category-view"),setTimeout(()=>{K()},1e3)}function ft(t,n){const e=document.getElementById("productsContainer");if(!e)return;const o=t.map(a=>`
        <div class="product-flex-item">
            <div class="card product-card h-100 ${a.quantity<=0?"out-of-stock":""} ${a.isAvailable?"":"inactive"}" onclick="showProductDetails(${a.id})" style="cursor: pointer; position: relative;">
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
                    <!-- Quantity badge on image -->
                    <span class="quantity-badge ${a.quantity>10?"quantity-success":a.quantity>0?"quantity-warning":"quantity-danger"}">
                        ${a.quantity} шт.
                    </span>
                </div>
                <div class="product-content">
                    <div class="product-info">
                        <h6 class="product-title">${a.name}</h6>
                        ${a.description?`<p class="product-description">${a.description}</p>`:""}
                    </div>
                    <div class="product-actions">
                        <div class="product-action-row">
                            <div class="cart-button-wrapper">
                                ${a.isAvailable?`
                                <button class="cart-btn cart-btn-primary" 
                                        onclick="event.stopPropagation(); addToCart(${a.id})"
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
                                <div class="product-price">${x(a.price)} ₽</div>
                            </div>
                            <div class="product-quantity-wrapper">
                                <span class="quantity-badge quantity-success">
                                    ${a.quantity} шт.
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
            ${o}
        </div>
    `}function S(){E=!1,$=!1;const t=document.getElementById("searchInput");t&&(t.value=""),document.body.classList.remove("category-view"),window.location.reload()}function x(t){return Number(t).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function ht(t,n){const e=[];for(let o=0;o<=n.length;o++)e[o]=[o];for(let o=0;o<=t.length;o++)e[0][o]=o;for(let o=1;o<=n.length;o++)for(let a=1;a<=t.length;a++)n.charAt(o-1)===t.charAt(a-1)?e[o][a]=e[o-1][a-1]:e[o][a]=Math.min(e[o-1][a-1]+1,e[o][a-1]+1,e[o-1][a]+1);return e[n.length][t.length]}function L(t,n){const e=t.length>n.length?t:n,o=t.length>n.length?n:t;if(e.length===0)return 100;const a=ht(e,o),s=(e.length-a)/e.length*100;return Math.max(0,s)}function I(t,n){const e=t.toLowerCase(),o=n.toLowerCase();if(o.includes(e))return 100;const a=L(e,o),s=o.split(/\s+/);let c=0;s.forEach(r=>{if(r.length>=2){const l=L(e,r);c=Math.max(c,l)}});let i=0;if(e.length>=3)for(let r=0;r<=o.length-e.length;r++){const l=o.substring(r,r+e.length),d=L(e,l);i=Math.max(i,d)}return Math.max(a,c,i)}function yt(t){k(t,1)}function T(){fetch("/cart/count").then(t=>t.json()).then(t=>{const n=document.getElementById("cart-counter"),e=document.getElementById("cart-float");n&&e&&(t.count>0?(n.textContent=t.count,n.classList.remove("hidden"),e.classList.remove("hidden"),n.style.animation="none",setTimeout(()=>{n.style.animation="cart-counter-pulse 2s infinite"},50)):(n.classList.add("hidden"),e.classList.add("hidden")))}).catch(t=>{console.error("Ошибка получения счетчика корзины:",t);const n=document.getElementById("cart-float");n&&n.classList.add("hidden")})}async function gt(t){var n;try{const e=document.getElementById("productModal"),o=document.getElementById("productModalTitle"),a=document.getElementById("productModalBody"),s=document.getElementById("productModalFooter");if(!e||!a||!s){console.error("Элементы модального окна не найдены");return}new bootstrap.Modal(e,{backdrop:!0,keyboard:!1}).show(),document.body.classList.add("modal-open"),o.textContent="Загрузка...",a.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>
        `,s.classList.add("d-none");const i=(n=document.querySelector('meta[name="short-name"]'))==null?void 0:n.getAttribute("content");if(!i)throw new Error("Short name не найден");const r=await fetch(`/${i}/api/products/${t}`);if(!r.ok)throw new Error(`HTTP error! status: ${r.status}`);const l=await r.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[t]=l,bt(l,a,o,s)}catch(e){console.error("Ошибка при загрузке данных товара:",e),u("Ошибка при загрузке данных товара","error")}}function bt(t,n,e,o){e.textContent="",n.innerHTML=`
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
                        ${$t(t)}
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
                    
                    <h4 class="modal-product-name mb-3">${g(t.name)}</h4>
                    
                    <div class="modal-product-price">
                        ${x(t.price)} ₽
                    </div>
                    
                    ${t.description?`
                        <div class="modal-product-description">
                            ${t.description}
                        </div>
                    `:""}
                    
                    ${t.specifications?`
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            ${typeof t.specifications=="object"&&t.specifications!==null?Object.entries(t.specifications).map(([a,s])=>`<p><strong>${g(a)}:</strong> ${g(s)}</p>`).join(""):`<p>${g(t.specifications)}</p>`}
                        </div>
                    `:""}
                    
                    <div class="availability-info mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #374151; font-weight: 600;">Наличие:</span>
                            <span class="badge ${xt(t.quantity)}">
                                ${Ct(t.quantity)}
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
    `,o.classList.remove("d-none"),o.innerHTML=`
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
    `,t.isAvailable&&t.quantity>0&&setTimeout(()=>{Y(t.id,1,t.price,t.quantity)},100)}function X(){const t=document.getElementById("productPanel"),n=document.getElementById("panelBackdrop"),e=document.getElementById("productPanelFooter");n&&n.classList.remove("show"),t&&t.classList.remove("show"),e&&(e.style.display="none"),document.body.style.overflow="auto"}function vt(t,n){const e=document.getElementById(`quantity-${t}`);if(!e)return;const o=parseInt(e.value)||1,a=Math.max(1,Math.min(parseInt(e.max),o+n));e.value=a,O(t,a),Q(t,a),m("light")}function wt(t){const n=document.getElementById(`quantity-${t}`);if(!n)return;const e=parseInt(n.value),o=parseInt(n.max);isNaN(e)||e<1?n.value=1:e>o&&(n.value=o,u(`Максимальное количество: ${o} шт.`,"warning"));const a=parseInt(n.value);O(t,a),Q(t,a)}function O(t,n){const e=window.cachedProductsData?window.cachedProductsData[t]:null;if(e){const o=e.price*n,a=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(o),s=document.getElementById(`totalPrice-${t}`);s&&(s.textContent=a)}}function Q(t,n){const e=document.getElementById(`decreaseBtn-${t}`),o=document.getElementById(`increaseBtn-${t}`),a=document.getElementById(`quantity-${t}`);if(e&&(e.disabled=n<=1),o&&a){const s=parseInt(a.max);o.disabled=n>=s}}function k(t,n){M(!0);const e=new FormData;e.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),e.append("quantity",n),fetch(`/cart/add/${t}`,{method:"POST",body:e}).then(o=>o.json()).then(o=>{M(!1),o.success?(u(`Товар добавлен в корзину (${n} шт.)!`),T(),N(),m("success")):(A(!0),u(o.message||"Ошибка при добавлении товара","error"),m("error"))}).catch(o=>{M(!1),A(!0),console.error("Ошибка при добавлении товара в корзину:",o),u("Ошибка при добавлении товара в корзину","error"),m("error")})}function Tt(){U()}function m(t="light"){var n,e,o;try{(o=(e=(n=window.Telegram)==null?void 0:n.WebApp)==null?void 0:e.HapticFeedback)!=null&&o.impactOccurred&&typeof window.Telegram.WebApp.HapticFeedback.impactOccurred=="function"&&window.Telegram.WebApp.HapticFeedback.impactOccurred(t)}catch(a){console.debug("HapticFeedback не поддерживается:",a.message)}}function Et(t=!0,n=0){const e=document.getElementById("cart-float"),o=document.getElementById("cart-counter");!e||!o||(t&&n>0?(o.textContent=n,o.classList.remove("hidden"),e.classList.remove("hidden"),o.style.animation="cart-counter-pulse 2s infinite"):(o.classList.add("hidden"),e.classList.add("hidden")))}function N(){const t=document.querySelector(".cart-float-btn"),n=document.querySelector(".cart-float-btn .fa-shopping-cart");t&&n&&(t.style.transform="translateY(-4px) scale(1.1)",t.style.boxShadow="0 12px 32px rgba(16, 185, 129, 0.5)",n.style.transform="rotate(-15deg) scale(1.2)",setTimeout(()=>{t.style.transform="",t.style.boxShadow="",n.style.transform=""},300),m("medium"))}function M(t=!0){const n=document.querySelector(".cart-float-btn");n&&(t?(n.classList.add("loading"),n.setAttribute("aria-busy","true")):(n.classList.remove("loading"),n.setAttribute("aria-busy","false")))}function A(t=!0){const n=document.querySelector(".cart-float-btn");n&&t&&(n.style.background="linear-gradient(135deg, #ef4444 0%, #dc2626 100%)",setTimeout(()=>{n.style.background=""},2e3))}function $t(t){return(t.is_active!==void 0?t.is_active:t.isAvailable)?t.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':t.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${t.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function xt(t){return"bg-success"}function Ct(t){return t<=0?"Нет в наличии":`${t} шт.`}function Mt(t,n){var c;const e=document.getElementById(`modal-quantity-${t}`);if(!e)return;const o=parseInt(e.textContent)||1,a=Math.max(1,o+n),s=(c=window.cachedProductsData)==null?void 0:c[t];if(s){const i=Math.min(s.quantity,99),r=Math.min(a,i);Y(t,r,s.price,s.quantity)}}function Y(t,n,e,o){const a=document.getElementById(`modal-quantity-${t}`),s=document.getElementById(`modal-decrease-${t}`),c=document.getElementById(`modal-increase-${t}`),i=document.getElementById(`modal-total-price-${t}`);if(a&&(a.textContent=n),s&&(s.disabled=n<=1),c&&(c.disabled=n>=o),i&&e){const r=n*e;i.textContent=`Итого: ${x(r)} ₽`}}function St(t){const n=document.getElementById(`modal-quantity-${t}`),e=n&&parseInt(n.textContent)||1;k(t,e);const o=document.getElementById("productModal");if(o){const a=bootstrap.Modal.getInstance(o);a&&a.hide()}}function U(){try{const t=document.getElementById("cartModal"),n=document.getElementById("cartModalBody"),e=document.getElementById("cartModalFooter");if(!t||!n){console.error("Элементы модального окна корзины не найдены");return}new bootstrap.Modal(t,{backdrop:!0,keyboard:!1}).show(),document.body.classList.add("modal-open"),n.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка корзины...</span>
                </div>
            </div>
        `,e.classList.add("d-none"),fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(a=>a.json()).then(a=>{a.success&&a.items&&a.items.length>0?G(a.items,a.total_amount):(n.innerHTML=`
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина пуста</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `,e.classList.add("d-none"))}).catch(a=>{console.error("Ошибка при загрузке корзины:",a),n.innerHTML=`
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Ошибка при загрузке корзины</h5>
                    <p class="text-muted">Попробуйте обновить страницу</p>
                </div>
            `,e.classList.add("d-none")})}catch(t){console.error("Ошибка при загрузке корзины:",t),u("Ошибка при загрузке корзины","error")}}function G(t,n){const e=document.getElementById("cartModalBody"),o=document.getElementById("cartModalFooter");if(!e||!o)return;if(!t||t.length===0){e.innerHTML=`
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Корзина пуста</h5>
                <p class="text-muted">Добавьте товары для оформления заказа</p>
            </div>
        `,o.classList.add("d-none");return}let a='<div class="cart-items">';t.forEach(s=>{a+=`
            <div class="cart-item mb-3 p-3 border rounded"style="flex-direction: column;" data-cart-id="${s.id}">
                <div class="d-flex align-items-start" style="width: 100%; padding-bottom: 10px;">
                    <div class="cart-item-image  flex-shrink-0">
                        ${s.photo_url?`<img src="${s.photo_url}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="${s.name}">`:`<div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>`}
                    </div>
                    
                    <div class="cart-item-info flex-grow-1" >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold">${s.name}</h6>
                                ${s.article?`<small class="text-muted d-block">Артикул: ${s.article}</small>`:""}
                                <div class="text-primary fw-semibold">${s.formatted_price} за шт.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${s.id})" title="Удалить товар">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                       
                    </div>
                </div>
                 <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                            <div class="quantity-controls">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${s.id}, ${s.quantity-1})" ${s.quantity<=1?"disabled":""}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary disabled px-3">${s.quantity} шт</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${s.id}, ${s.quantity+1})" ${s.quantity>=s.available_quantity?"disabled":""}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-item-total">
                                <strong class="text-success fs-5">${s.formatted_total}</strong>
                            </div>
                        </div>
            </div>
        `}),a+="</div>",e.innerHTML=a,o.innerHTML=`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100">
            <div class="cart-total" style="width:100%;">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Итого: ${x(n)} ₽
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
    `,o.classList.remove("d-none")}function Bt(t,n){if(n<=0){z(t);return}const e=document.querySelector(`[data-cart-id="${t}"]`);if(e){const o=e.querySelector(".quantity-controls");o&&(o.style.opacity="0.5",o.style.pointerEvents="none")}fetch(`/cart/update/${t}`,{method:"PATCH",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"},body:JSON.stringify({quantity:n})}).then(o=>o.json()).then(o=>{o.success?(f("Количество обновлено","success"),T(),w(),m("light")):f(o.message||"Ошибка при обновлении количества","error")}).catch(o=>{console.error("Ошибка при обновлении количества:",o),f("Ошибка при обновлении количества","error")}).finally(()=>{if(e){const o=e.querySelector(".quantity-controls");o&&(o.style.opacity="1",o.style.pointerEvents="auto")}})}function w(){const t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");!t||!n||fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success&&e.items&&e.items.length>0?G(e.items,e.total_amount):(t.innerHTML=`
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пуста</h5>
                    <p class="text-muted">Добавьте товары для оформления заказа</p>
                </div>
            `,n.classList.add("d-none"))}).catch(e=>{console.error("Ошибка при обновлении корзины:",e)})}function z(t){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Удалить товар из корзины?",n=>{n&&D(t)});else{if(!confirm("Удалить товар из корзины?"))return;D(t)}}function D(t){const n=document.querySelector(`[data-cart-id="${t}"]`);n&&(n.style.opacity="0.5",n.style.transform="scale(0.95)",n.style.pointerEvents="none"),fetch(`/cart/remove/${t}`,{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success?(f("Товар удален из корзины","success"),T(),m("medium"),n?(n.style.transition="all 0.3s ease",n.style.opacity="0",n.style.transform="translateX(100%)",setTimeout(()=>{w()},300)):w()):(f(e.message||"Ошибка при удалении товара","error"),n&&(n.style.opacity="1",n.style.transform="scale(1)",n.style.pointerEvents="auto"))}).catch(e=>{console.error("Ошибка при удалении товара:",e),f("Ошибка при удалении товара","error"),n&&(n.style.opacity="1",n.style.transform="scale(1)",n.style.pointerEvents="auto")})}function Lt(){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Очистить всю корзину?",t=>{t&&_()});else{if(!confirm("Очистить всю корзину?"))return;_()}}function _(){const t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");t&&(t.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Очистка корзины...</span>
                </div>
                <div class="mt-3">Очищаем корзину...</div>
            </div>
        `),n&&n.classList.add("d-none"),fetch("/cart/clear",{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{e.success?(f("Корзина очищена","success"),T(),m("medium"),t&&(t.innerHTML=`
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина очищена</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `),setTimeout(()=>{const o=document.getElementById("cartModal");if(o){const a=bootstrap.Modal.getInstance(o);a&&a.hide()}},1500)):(f(e.message||"Ошибка при очистке корзины","error"),w())}).catch(e=>{console.error("Ошибка при очистке корзины:",e),f("Ошибка при очистке корзины","error"),w()})}function It(){if(!y){u("Ошибка: данные пользователя недоступны","error");return}u("Проверяем корзину...","info"),fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(t=>t.json()).then(t=>{if(!t.success||!t.items||t.items.length===0){u("Корзина пуста","warning");return}u("Оформляем заказ...","info");const n={bot_short_name:document.querySelector('meta[name="short-name"]').getAttribute("content"),user_data:y,notes:""};return ot("/cart/checkout",{method:"POST",body:JSON.stringify(n)})}).then(t=>{if(t)return t.json()}).then(t=>{t&&(t.success?(H(),u(`Заказ успешно оформлен! Номер заказа: ${t.order.order_number}`,"success"),T(),setTimeout(()=>{const n=document.getElementById("cartModalBody");n&&(n.innerHTML=`
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Корзина пуста</h5>
                            <p class="text-muted">Добавьте товары для оформления заказа</p>
                        </div>
                    `);const e=document.getElementById("cartModalFooter");e&&e.classList.add("d-none")},1e3),m("success")):(u(t.message||"Ошибка при оформлении заказа","error"),m("error")))}).catch(t=>{console.error("Ошибка при оформлении заказа:",t),u("Произошла ошибка при оформлении заказа","error"),m("error")})}function J(){try{const t=document.getElementById("productModal");if(t){const n=bootstrap.Modal.getInstance(t);n&&n.hide()}document.body.classList.remove("modal-open"),m("light")}catch(t){console.error("Ошибка при закрытии модального окна товара:",t),document.body.classList.remove("modal-open")}}function H(){try{const t=document.getElementById("cartModal");if(t){const n=bootstrap.Modal.getInstance(t);n&&n.hide()}document.body.classList.remove("modal-open"),m("light")}catch(t){console.error("Ошибка при закрытии модального окна корзины:",t),document.body.classList.remove("modal-open")}}function K(){if(localStorage.getItem("hasSeenSwipeHint"))return;const n=document.createElement("div");n.className="swipe-indicator",n.innerHTML=`
        <span class="arrow">→</span>
        <span>Свайп для выхода</span>
    `,document.body.appendChild(n),setTimeout(()=>{n.parentNode&&n.remove(),localStorage.setItem("hasSeenSwipeHint","true")},3e3)}function V(){if(window.Telegram&&window.Telegram.WebApp){const t=window.Telegram.WebApp;try{t.expand(),t.disableClosingConfirmation(),t.isClosingConfirmationEnabled!==void 0&&(t.isClosingConfirmationEnabled=!1),t.setViewportHeight&&t.setViewportHeight(window.innerHeight),console.log("Настройки скролла для Telegram WebApp применены")}catch(n){console.error("Ошибка при настройке поведения скролла:",n)}}document.body.style.touchAction="pan-x pan-y",document.documentElement.style.touchAction="pan-x pan-y"}function Z(){let t=0,n=0,e=!1;const o=10;function a(i){if(i.touches.length!==1)return;const r=i.touches[0];t=r.clientY,n=r.clientX,e=!1,(document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&(e=!0)}function s(i){var v,h;if(i.touches.length!==1||!e)return;const r=i.touches[0],l=r.clientY-t,d=Math.abs(r.clientX-n);if((document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&l>o&&d<50)return i.preventDefault(),i.stopPropagation(),i.stopImmediatePropagation(),console.log("Заблокировано потенциальное сворачивание через pull-to-close"),(h=(v=window.Telegram)==null?void 0:v.WebApp)!=null&&h.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("rigid"),!1}function c(i){e=!1}document.addEventListener("touchstart",a,{passive:!1,capture:!0}),document.addEventListener("touchmove",s,{passive:!1,capture:!0}),document.addEventListener("touchend",c,{passive:!0,capture:!0}),document.body.style.overscrollBehavior="none",document.documentElement.style.overscrollBehavior="none",window.addEventListener("beforeunload",function(i){if(i.clientY<50)return i.preventDefault(),!1}),console.log("Защита от сворачивания через скролл активирована")}function tt(){let t=0,n=0,e=!1;function o(i){if(i.touches.length!==1)return;const r=i.touches[0];t=r.clientX,n=r.clientY,e=!0}function a(i){if(!e||i.touches.length!==1)return;const r=i.touches[0],l=r.clientX-t,d=r.clientY-n;if(Math.abs(l)>Math.abs(d)&&l>50&&Math.abs(d)<100){const p=document.querySelector(".modal.show");p&&(p.id==="productModal"?J():p.id==="cartModal"&&H()),e=!1}}function s(){e=!1}[document.getElementById("productModal"),document.getElementById("cartModal")].forEach(i=>{i&&(i.addEventListener("touchstart",o,{passive:!0}),i.addEventListener("touchmove",a,{passive:!0}),i.addEventListener("touchend",s,{passive:!0}))})}function et(){let t=0,n=0,e=!1;const o=100,a=50;function s(l){if(l.touches.length!==1)return;const d=l.touches[0];t=d.clientX,n=d.clientY,t<=a&&(e=!0,console.log("Начат свайп с левого края:",t))}function c(l){var h,P,F,W;if(!e||l.touches.length!==1)return;const d=l.touches[0],p=d.clientX-t,v=d.clientY-n;if(Math.abs(p)>Math.abs(v)&&p>o&&Math.abs(v)<100){if(console.log("Свайп вправо обнаружен, deltaX:",p),$){console.log("Выход из категории через свайп"),(P=(h=window.Telegram)==null?void 0:h.WebApp)!=null&&P.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),S(),e=!1;return}if(E){console.log("Очистка поиска через свайп"),(W=(F=window.Telegram)==null?void 0:F.WebApp)!=null&&W.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),S(),e=!1;return}e=!1}}function i(){e=!1}const r=document.getElementById("app");r&&(r.addEventListener("touchstart",s,{passive:!0}),r.addEventListener("touchmove",c,{passive:!0}),r.addEventListener("touchend",i,{passive:!0}),console.log("Обработчики свайпа для выхода из категории добавлены"))}window.initApp=R;window.showAlert=u;window.filterByCategory=pt;window.showAllProducts=S;window.performSearch=q;window.addToCart=yt;window.addToCartWithQuantity=k;window.showProductDetails=gt;window.showCart=Tt;window.showCartModal=U;window.closePanel=X;window.changeQuantityModal=Mt;window.addToCartFromModal=St;window.changeQuantity=vt;window.validateQuantity=wt;window.updateCartQuantity=Bt;window.removeFromCart=z;window.clearCart=Lt;window.proceedToCheckout=It;window.refreshCartContent=w;window.closeProductModal=J;window.closeCartModal=H;window.showSwipeHint=K;window.setupScrollBehavior=V;window.preventPullToClose=Z;window.addSwipeSupport=tt;window.addCategorySwipeSupport=et;window.toggleCartFloat=Et;window.animateCartButtonOnAdd=N;window.setCartButtonLoading=M;window.setCartButtonError=A;document.addEventListener("DOMContentLoaded",function(){var a;console.log("DOM загружен, начинаем инициализацию Mini App"),ct();const t=document.getElementById("loading"),n=document.getElementById("app");console.log("Элементы найдены:",{loading:!!t,app:!!n,telegramWebApp:!!((a=window.Telegram)!=null&&a.WebApp)});try{R()}catch(s){console.error("Критическая ошибка инициализации:",s),t&&(t.style.display="none"),n&&(n.style.display="block")}setTimeout(()=>{try{T()}catch(s){console.error("Ошибка обновления счетчика корзины:",s)}},1e3),document.addEventListener("keydown",function(s){s.key==="Escape"&&X()});const e=document.getElementById("productModal"),o=document.getElementById("cartModal");e&&e.addEventListener("hidden.bs.modal",function(){document.body.classList.remove("modal-open")}),o&&o.addEventListener("hidden.bs.modal",function(){document.body.classList.remove("modal-open")}),tt(),et(),Z()});
