console.log("Mini App загружается...");var re;(re=window.Telegram)!=null&&re.WebApp;let I=null,ne=!1,B=[],q=[],_=!1,C=!1;function u(e){if(!e)return"";const t={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"};return String(e).replace(/[&<>"']/g,function(n){return t[n]})}function Be(){const e=document.querySelector('meta[name="csrf-token"]');return e?e.getAttribute("content"):null}function le(e,t={}){const n=Be();return t.method&&["POST","PUT","DELETE","PATCH"].includes(t.method.toUpperCase())&&(t.headers={"Content-Type":"application/json",Accept:"application/json",...t.headers},n&&(t.headers["X-CSRF-TOKEN"]=n)),fetch(e,t)}function ce(){var t;if(ne){console.log("Mini App уже инициализирован, пропускаем");return}console.log("Инициализация Mini App..."),ne=!0;const e=setTimeout(()=>{console.log("Принудительно показываем приложение после тайм-аута");try{const n=document.getElementById("loading"),o=document.getElementById("app");n&&(n.style.display="none"),o&&(o.style.display="block")}catch(n){console.error("Ошибка при принудительном показе приложения:",n)}},3e3);if(window.Telegram&&window.Telegram.WebApp){const n=window.Telegram.WebApp;try{if(n.ready(),n.expand(),console.log("Telegram WebApp инициализирован"),console.log("Init data:",n.initData),console.log("User data:",(t=n.initDataUnsafe)==null?void 0:t.user),n.initDataUnsafe&&n.initDataUnsafe.user?(I=n.initDataUnsafe.user,R(I)):n.initData&&(I=Ce(n.initData),I&&R(I)),document.documentElement.style.setProperty("--tg-color-scheme",n.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",n.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",n.themeParams.text_color||"#000000"),Te(),n.version&&parseFloat(n.version)>=6.1&&n.BackButton)try{typeof n.BackButton.hide=="function"&&(n.BackButton.hide(),console.log("BackButton hidden for WebApp version",n.version))}catch(o){console.log("BackButton control not supported in version",n.version,":",o.message)}else console.log("BackButton not available in WebApp version",n.version||"unknown");console.log("Telegram WebApp полностью настроен")}catch(o){console.error("Ошибка инициализации Telegram WebApp:",o),Se("Ошибка загрузки Telegram WebApp")}}else console.log("Режим разработки - Telegram WebApp недоступен"),I={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},R(I);try{ke(),_e().catch(n=>{console.error("Ошибка загрузки категорий:",n)})}catch(n){console.error("Ошибка инициализации поиска/категорий:",n)}setTimeout(()=>{try{clearTimeout(e);const n=document.getElementById("loading"),o=document.getElementById("app");n&&(n.style.display="none"),o&&(o.style.display="block"),console.log("Mini App загружен успешно"),setTimeout(()=>{try{setupModalBackdropHandlers()}catch(a){console.error("Ошибка настройки модальных окон:",a)}},100),window.addEventListener("resize",$e)}catch(n){console.error("Ошибка при скрытии загрузочного экрана:",n)}},800)}function Se(e){const t=document.createElement("div");t.className="alert alert-danger",t.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,t.innerHTML=`
        <strong>Ошибка:</strong> ${e}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(t),setTimeout(()=>{t.parentElement&&t.remove()},5e3)}function R(e){console.log("Данные пользователя:",e);const t=document.querySelector(".user-greeting");if(t&&e){const n=e.first_name||e.username||"Пользователь";t.textContent=`Привет, ${n}!`}}function Ce(e){try{const n=new URLSearchParams(e).get("user");if(n)return JSON.parse(decodeURIComponent(n))}catch(t){console.error("Ошибка парсинга user из initData:",t)}return null}function v(e,t="info"){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showAlert)try{window.Telegram.WebApp.showAlert(e);return}catch(n){console.warn("Не удалось показать Telegram уведомление:",n)}E(e,t)}function E(e,t="info"){const n=document.getElementById("toast-container")||Le(),o=document.createElement("div");o.className=`toast align-items-center  bg-${t==="error"?"danger":t==="success"?"success":"primary"} border-0`,o.setAttribute("role","alert"),o.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${e}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,n.appendChild(o),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(o).show(),o.addEventListener("hidden.bs.toast",()=>{o.remove()})):(o.style.display="block",setTimeout(()=>{o.remove()},5e3))}function Le(){const e=document.createElement("div");return e.id="toast-container",e.className="toast-container position-fixed top-0 end-0 p-3",e.style.zIndex="9999",document.body.appendChild(e),e}function Ae(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function ke(){const e=document.getElementById("searchInput");if(!e)return;let t;e.addEventListener("input",function(n){clearTimeout(t),t=setTimeout(()=>{Q(n.target.value)},300)}),e.addEventListener("keypress",function(n){n.key==="Enter"&&Q(n.target.value)}),qe()}async function qe(){var e;try{const t=document.getElementById("products-data");if(t)try{const a=JSON.parse(t.textContent);B=Object.values(a),console.log("Загружено товаров из встроенных данных:",B.length);return}catch(a){console.warn("Ошибка парсинга встроенных данных товаров:",a)}const n=((e=document.querySelector('meta[name="short-name"]'))==null?void 0:e.content)||window.location.pathname.split("/")[1],o=await fetch(`/${n}/api/products`);o.ok&&(B=await o.json(),console.log("Загружено товаров через API:",B.length))}catch(t){console.error("Ошибка при загрузке товаров:",t)}}async function _e(){var e;try{const t=((e=document.querySelector('meta[name="short-name"]'))==null?void 0:e.content)||window.location.pathname.split("/")[1],n=new AbortController,o=setTimeout(()=>n.abort(),5e3),a=await fetch(`/${t}/api/categories`,{signal:n.signal,headers:{Accept:"application/json","Content-Type":"application/json"}});if(clearTimeout(o),a.ok){if(q=await a.json(),console.log("Загружено категорий:",q.length),console.log("Данные категорий:",q),q.length>0){const s=document.getElementById("categoriesContainer");s&&(s.style.display="block"),He(q)}}else console.log("Категории не найдены или ошибка загрузки:",a.status)}catch(t){t.name==="AbortError"?console.log("Загрузка категорий прервана по тайм-ауту"):console.error("Ошибка при загрузке категорий:",t)}}function He(e){console.log("Отрисовка категорий:",e);const t=document.getElementById("categoriesTrack");if(!t){console.error("Элемент categoriesTrack не найден");return}if(e.length===0){const a=document.getElementById("categoriesContainer");a&&(a.style.display="none");return}window.allCategoriesData=e;const n=8;t.innerHTML=e.map((a,s)=>s<n?`
            <div class="category-card" 
                 data-category-id="${a.id}" 
                 data-category-name="${u(a.name)}"
                 data-index="${s}"
                 data-loaded="true">
                <div class="card h-200">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="category-info">
                                <div class="category-name">${u(a.name)}</div>
                                ${a.description?`<div class="category-description">${u(a.description)}</div>`:""}
                                <div class="category-products-count">${a.products_count||0} товаров</div>
                            </div>
                            ${a.photo_url?`<img src="${u(a.photo_url)}" class="category-image" alt="${u(a.name)}" onerror="handleImageError(this)" loading="eager">
                                   <div class="category-placeholder" style="display: none;">
                                       <i class="fas fa-folder"></i>
                                       <span class="placeholder-text">Изображение недоступно</span>
                                   </div>`:`<div class="category-placeholder">
                                       <i class="fas fa-folder"></i>
                                   </div>`}
                        </div>
                    </div>
                </div>
            </div>`:`
            <div class="category-card category-skeleton" 
                 data-index="${s}"
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
            </div>`).join(""),t.querySelectorAll('.category-card[data-loaded="true"]').forEach(a=>{a.addEventListener("click",function(){const s=this.getAttribute("data-category-id"),r=this.getAttribute("data-category-name");s&&r&&D(parseInt(s),r)})}),console.log("Категории отрисованы, запуск переинициализации Swiper"),typeof window.reinitCategoriesSwiper=="function"&&setTimeout(()=>{window.reinitCategoriesSwiper()},100)}function de(){const e=window.categoriesSwiper;if(!e){console.warn("Swiper не инициализирован для ленивой загрузки");return}if(!e.slides||e.slides.length===0){console.warn("Swiper slides не найдены, отложенная инициализация..."),setTimeout(()=>{de()},500);return}console.log("Настройка ленивой загрузки слайдов категорий, всего слайдов:",e.slides.length);const t=o=>{var r;if(!o||o.getAttribute("data-loaded")==="true")return;const a=parseInt(o.getAttribute("data-index")),s=(r=window.allCategoriesData)==null?void 0:r[a];s&&(console.log(`Загрузка категории ${a}: ${s.name}`),o.innerHTML=`
            <div class="card h-200">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${u(s.name)}</div>
                            ${s.description?`<div class="category-description">${u(s.description)}</div>`:""}
                            <div class="category-products-count">${s.products_count||0} товаров</div>
                        </div>
                        ${s.photo_url?`<img src="${u(s.photo_url)}" class="category-image" alt="${u(s.name)}" onerror="handleImageError(this)" loading="lazy">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                                   <span class="placeholder-text">Изображение недоступно</span>
                               </div>`:`<div class="category-placeholder">
                                   <i class="fas fa-folder"></i>
                               </div>`}
                    </div>
                </div>
            </div>
        `,o.setAttribute("data-category-id",s.id),o.setAttribute("data-category-name",s.name),o.setAttribute("data-loaded","true"),o.classList.remove("category-skeleton"),o.classList.add("category-loaded"),o.addEventListener("click",function(){const i=this.getAttribute("data-category-id"),l=this.getAttribute("data-category-name");i&&l&&D(parseInt(i),l)}))},n=()=>{if(!e.slides||e.slides.length===0){console.warn("Slides не доступны для загрузки");return}const o=e.activeIndex||0,a=e.params.slidesPerView==="auto"?4:e.params.slidesPerView,s=Math.max(0,o-2),r=Math.min(e.slides.length-1,o+a+4);console.log(`Загрузка видимых категорий: ${s} - ${r}`);for(let i=s;i<=r;i++){const l=e.slides[i],c=l==null?void 0:l.querySelector(".category-card");c&&t(c)}};e.on("slideChange",()=>{n()}),e.on("progress",()=>{n()}),e.on("reachEnd",()=>{const o=document.querySelectorAll(".category-skeleton");console.log("Достигнут конец, загружаем оставшиеся категории:",o.length),o.forEach(t)}),console.log("Загрузка начальных видимых категорий..."),n()}function Q(e=null){const t=document.getElementById("searchInput"),n=e!==null?e:t?t.value.trim():"";if(console.log("Выполняется поиск по запросу:",n),n===""||n.length<2){X(),_=!1,C=!1;return}_=!0,C=!1;const o=65,s=B.map(r=>{const i=r.name||"",l=r.description||"",c=r.article||"",d=Y(n,i),f=c?Y(n,c):0,p=l?Y(n,l):0,g=Math.max(d,f,p);return{...r,similarity:g,matchField:d===g?"name":f===g?"article":"description"}}).filter(r=>r.similarity>=o).sort((r,i)=>{if(i.similarity!==r.similarity)return i.similarity-r.similarity;if(r.matchField==="name"&&i.matchField!=="name")return-1;if(i.matchField==="name"&&r.matchField!=="name")return 1;if(r.matchField==="article"&&i.matchField==="description")return-1;if(i.matchField==="article"&&r.matchField==="description")return 1;const l=(r.quantity||0)>0?1:0,c=(i.quantity||0)>0?1:0;if(c!==l)return c-l;const d=r.created_at?new Date(r.created_at).getTime():0;return(i.created_at?new Date(i.created_at).getTime():0)-d});console.log(`Найдено товаров: ${s.length} из ${B.length}`),s.forEach(r=>{console.log(`- ${r.name}: ${r.similarity.toFixed(1)}% (поле: ${r.matchField})`)}),Pe(s,n)}function Pe(e,t){const n=document.getElementById("productsContainer");if(!n)return;if(e.length===0){n.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${u(t)}"</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const o=e.map(a=>{const s=a.main_photo_url||a.photo_url,r=!!s,i=a.isAvailable&&a.quantity>0;return`
        <article class="product-card" data-product-id="${a.id}">
            <div class="product-image ${r?"":"no-image"}">
                ${r?`<img src="${u(x(s))}" alt="${u(a.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${a.has_multiple_photos?'<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>':""}`:""}
                ${me(a)}
                ${a.similarity?`<div class="product-badge" style="background: var(--color-gray); top: auto; bottom: var(--space-xs); left: var(--space-xs);">${Math.round(a.similarity)}%</div>`:""}
            </div>
            <div class="product-info">
                <h3 class="product-name">${u(a.name)}</h3>
                ${a.description?`<p class="product-description">${u(a.description)}</p>`:""}
                <div class="product-footer">
                    <span class="product-price">${u(a.formatted_price||H(a.price))}</span>
                    <button class="add-to-cart ${i?"":"disabled"}" 
                            data-product-id="${a.id}"
                            ${i?"":"disabled"}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `}).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${u(t)}" (найдено: ${e.length})</h5>
        </div>
        <div class="products-grid">
            ${o}
        </div>
    `}async function D(e,t){console.log("Фильтрация по категории:",e,t),_=!0,C=!0;const n=document.getElementById("backButton");n&&window.innerWidth>768&&(n.style.display="flex",n.classList.add("show"));const o=document.getElementById("productsContainer");if(!o){console.error("Контейнер productsContainer не найден");return}o.innerHTML=`
        <div class="loading-content" style="padding: 2rem; text-align: center;">
            <div class="loading-spinner"></div>
            <div class="loading-text">Загрузка товаров...</div>
        </div>
    `;try{const a=document.querySelector('meta[name="short-name"]'),s=a?a.getAttribute("content"):"";if(!s)throw new Error("Short name не найден");const r=await le(`/${s}/api/search?category_id=${e}`);if(!r.ok)throw new Error(`Ошибка загрузки: ${r.status}`);const i=await r.json();if(console.log("Найдено товаров в категории:",i.length,i),i.length===0){o.innerHTML=`
                <div class="products-header">
                    <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${u(t)}</h5>
                </div>
                <div class="no-results">
                    <i class="fas fa-folder-open"></i>
                    <h6>В этой категории пока нет товаров</h6>
                    <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                        Показать все товары
                    </button>
                </div>
            `;return}Fe(i,t);const l=document.getElementById("searchInput");l&&(l.value=""),document.body.classList.add("category-view"),setTimeout(()=>{be()},1e3)}catch(a){console.error("Ошибка загрузки товаров категории:",a),o.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${u(t)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <h6>Ошибка загрузки товаров</h6>
                <p class="text-muted">${u(a.message)}</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Вернуться к каталогу
                </button>
            </div>
        `}}function Fe(e,t){const n=document.getElementById("productsContainer");if(!n)return;if(e.length===0){n.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${u(t)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h6>В этой категории пока нет товаров</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const o=e.map(a=>{const s=a.main_photo_url||a.photo_url,r=!!s,i=a.isAvailable&&a.quantity>0;return`
        <article class="product-card" data-product-id="${a.id}">
            <div class="product-image ${r?"":"no-image"}">
                ${r?`<img src="${u(x(s))}" alt="${u(a.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${a.has_multiple_photos?'<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>':""}`:""}
                ${me(a)}
            </div>
            <div class="product-info">
                <h3 class="product-name">${u(a.name)}</h3>
                ${a.description?`<p class="product-description">${u(a.description)}</p>`:""}
                <div class="product-footer">
                    <span class="product-price">${u(a.formatted_price||H(a.price))}</span>
                    <button class="add-to-cart ${i?"":"disabled"}" 
                            data-product-id="${a.id}"
                            ${i?"":"disabled"}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `}).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${u(t)}</h5>
        </div>
        <div class="products-grid">
            ${o}
        </div>
    `}function X(){_=!1,C=!1;const e=document.getElementById("backButton");e&&(e.style.display="none",e.classList.remove("show"));const t=document.getElementById("searchInput");t&&(t.value=""),document.body.classList.remove("category-view"),window.location.reload()}function H(e){return Number(e).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function x(e){return!e||e.startsWith("http://")||e.startsWith("https://")||e.startsWith("/")?e:"/"+e}function Ge(e,t){const n=[];for(let o=0;o<=t.length;o++)n[o]=[o];for(let o=0;o<=e.length;o++)n[0][o]=o;for(let o=1;o<=t.length;o++)for(let a=1;a<=e.length;a++)t.charAt(o-1)===e.charAt(a-1)?n[o][a]=n[o-1][a-1]:n[o][a]=Math.min(n[o-1][a-1]+1,n[o][a-1]+1,n[o-1][a]+1);return n[t.length][e.length]}function O(e,t){const n=e.length>t.length?e:t,o=e.length>t.length?t:e;if(n.length===0)return 100;const a=Ge(n,o),s=(n.length-a)/n.length*100;return Math.max(0,s)}function Y(e,t){const n=e.toLowerCase(),o=t.toLowerCase();if(o.includes(n))return 100;const a=O(n,o),s=o.split(/\s+/);let r=0;s.forEach(l=>{if(l.length>=2){const c=O(n,l);r=Math.max(r,c)}});let i=0;if(n.length>=3)for(let l=0;l<=o.length-n.length;l++){const c=o.substring(l,l+n.length),d=O(n,c);i=Math.max(i,d)}return Math.max(a,r,i)}function K(e){ee(e,1)}function A(){fetch("/cart/count").then(e=>e.json()).then(e=>{const t=document.getElementById("cart-counter"),n=document.getElementById("cart-float");t&&n&&(e.count>0?(t.textContent=e.count,t.classList.remove("hidden"),n.classList.remove("hidden"),t.style.animation="none",setTimeout(()=>{t.style.animation="cart-counter-pulse 2s infinite"},50)):(t.classList.add("hidden"),n.classList.add("hidden")))}).catch(e=>{console.error("Ошибка получения счетчика корзины:",e);const t=document.getElementById("cart-float");t&&t.classList.add("hidden")})}async function Z(e){var t;try{const n=document.getElementById("productModal"),o=document.getElementById("productModalTitle"),a=document.getElementById("productModalBody"),s=document.getElementById("productModalFooter");if(!n||!a||!s){console.error("Элементы модального окна не найдены");return}n.classList.add("show"),n.style.display="block",document.body.style.overflow="hidden",o.textContent="Загрузка...",a.innerHTML=`
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка товара...</div>
            </div>
        `,s.style.display="none";const r=(t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.getAttribute("content");if(!r)throw new Error("Short name не найден");const i=await fetch(`/${r}/api/products/${e}`);if(!i.ok)throw new Error(`HTTP error! status: ${i.status}`);const l=await i.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[e]=l,Xe(l,a,o,s)}catch(n){console.error("Ошибка при загрузке данных товара:",n),v("Ошибка при загрузке данных товара","error")}}function Xe(e,t,n,o){n.textContent="",t.innerHTML=`
        <div class="row g-4">
            ${e.photos_gallery&&e.photos_gallery.length>0?`
                <div class="col-md-6">
                    <div class="product-gallery">
                        <img src="${x(e.photos_gallery[0])}" alt="${e.name}" 
                             class="gallery-main-image" id="main-gallery-image"
                             onclick="openGalleryFullscreen(0)">
                        
                        ${e.photos_gallery.length>1?`
                            <div class="gallery-counter">
                                <span id="gallery-current">1</span> / ${e.photos_gallery.length}
                            </div>
                            
                            <button class="gallery-navigation prev" onclick="previousGalleryImage()" id="gallery-prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="gallery-navigation next" onclick="nextGalleryImage()" id="gallery-next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            
                            <div class="gallery-thumbnails">
                                ${e.photos_gallery.map((a,s)=>`
                                    <img src="${x(a)}" alt="${e.name} ${s+1}" 
                                         class="gallery-thumbnail ${s===0?"active":""}" 
                                         onclick="setGalleryImage(${s})"
                                         data-index="${s}">
                                `).join("")}
                            </div>
                        `:""}
                        
                        ${oe(e)}
                    </div>
                </div>
            `:e.main_photo_url||e.photo_url?`
                <div class="col-md-6">
                    <div class="position-relative">
                        <img src="${x(e.main_photo_url||e.photo_url)}" alt="${e.name}" 
                             class="modal-product-image" 
                             onerror="handleImageError(this);" loading="lazy">
                        <div class="product-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Изображение недоступно</span>
                        </div>
                        ${oe(e)}
                    </div>
                </div>
            `:""}
            
            <div class="${e.photos_gallery&&e.photos_gallery.length>0||e.main_photo_url||e.photo_url?"col-md-6":"col-12"}">
                <div class="product-info">
                    ${e.article?`
                        <p class="text-muted mb-2">
                            <strong>Артикул:</strong> ${e.article}
                        </p>
                    `:""}
                    
                    <h4 class="modal-product-name mb-3">${u(e.name)}</h4>
                    
                    <div class="modal-product-price">
                        ${e.formatted_price||H(e.price)}
                    </div>
                    
                    ${e.description?`
                        <div class="modal-product-description">
                            ${e.description}
                        </div>
                    `:""}
                    
                    ${e.specifications?`
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            ${typeof e.specifications=="object"&&e.specifications!==null?Object.entries(e.specifications).map(([a,s])=>`<p><strong>${u(a)}:</strong> ${u(s)}</p>`).join(""):`<p>${u(e.specifications)}</p>`}
                        </div>
                    `:""}
                    
                    <div class="availability-info mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #374151; font-weight: 600;">Наличие:</span>
                            <span class="badge ${Oe(e.quantity)}">
                                ${Ye(e.quantity)}
                            </span>
                        </div>
                    </div>
                    
                    ${e.isAvailable&&e.quantity>0?`
                        <div class="quantity-selector">
                            <label style="color: #374151; font-weight: 600;">Количество:</label>
                            <div class="d-flex align-items-center gap-3 justify-content-center">
                                <button class="quantity-btn" onclick="changeQuantityModal(${e.id}, -1)" id="modal-decrease-${e.id}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span id="modal-quantity-${e.id}" class="quantity-display">1</span>
                                <button class="quantity-btn" onclick="changeQuantityModal(${e.id}, 1)" id="modal-increase-${e.id}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div id="modal-total-price-${e.id}" class="text-center mt-2" style="font-weight: 600; color: var(--primary-color);"></div>
                        </div>
                    `:""}
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
                
                ${e.photos_gallery&&e.photos_gallery.length>1?`
                    <button class="gallery-fullscreen-nav prev" onclick="previousFullscreenImage()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="gallery-fullscreen-nav next" onclick="nextFullscreenImage()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `:""}
            </div>
        </div>
    `,window.currentGallery={photos:e.photos_gallery||[],currentIndex:0},o.style.display="block",o.innerHTML=`
        <div class="full-width">
            ${e.isAvailable&&e.quantity>0?`
                <button type="button" class="btn-primary full-width" onclick="addToCartFromModal(${e.id})">
                    <i class="fas fa-shopping-cart"></i> Добавить в корзину
                </button>
            `:`
                <button type="button" class="btn-primary full-width" disabled style="opacity: 0.5;">
                    <i class="fas fa-times"></i> Товар недоступен
                </button>
            `}
        </div>
    `,e.isAvailable&&e.quantity>0&&setTimeout(()=>{const a=e.price_with_markup||e.price;ge(e.id,1,a,e.quantity)},100),setTimeout(()=>{Me()},150)}function ue(){const e=document.getElementById("productPanel"),t=document.getElementById("panelBackdrop"),n=document.getElementById("productPanelFooter");t&&t.classList.remove("show"),e&&e.classList.remove("show"),n&&(n.style.display="none"),document.body.style.overflow="auto"}function me(e){let t="secondary";return e.availability_status==="В наличии"?t="success":e.availability_status==="Заканчивается"?t="warning":e.availability_status==="Нет в наличии"&&(t="danger"),`<span class="badge bg-${t} shadow-sm">${u(e.availability_status||"")}</span>`}function De(e,t){const n=document.getElementById(`quantity-${e}`);if(!n)return;const o=parseInt(n.value)||1,a=Math.max(1,Math.min(parseInt(n.max),o+t));n.value=a,fe(e,a),pe(e,a),y("light")}function We(e){const t=document.getElementById(`quantity-${e}`);if(!t)return;const n=parseInt(t.value),o=parseInt(t.max);isNaN(n)||n<1?t.value=1:n>o&&(t.value=o,v(`Максимальное количество: ${o} шт.`,"warning"));const a=parseInt(t.value);fe(e,a),pe(e,a)}function fe(e,t){const n=window.cachedProductsData?window.cachedProductsData[e]:null;if(n){const o=n.price*t,a=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(o),s=document.getElementById(`totalPrice-${e}`);s&&(s.textContent=a)}}function pe(e,t){const n=document.getElementById(`decreaseBtn-${e}`),o=document.getElementById(`increaseBtn-${e}`),a=document.getElementById(`quantity-${e}`);if(n&&(n.disabled=t<=1),o&&a){const s=parseInt(a.max);o.disabled=t>=s}}function ee(e,t){F(!0);const n=new FormData;n.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),n.append("quantity",t),fetch(`/cart/add/${e}`,{method:"POST",body:n}).then(o=>o.json()).then(o=>{F(!1),o.success?(v(`Товар добавлен в корзину (${t} шт.)!`),A(),ye(),y("success")):(N(!0),v(o.message||"Ошибка при добавлении товара","error"),y("error"))}).catch(o=>{F(!1),N(!0),console.error("Ошибка при добавлении товара в корзину:",o),v("Ошибка при добавлении товара в корзину","error"),y("error")})}function je(){he()}function y(e="light"){var t,n,o;try{(o=(n=(t=window.Telegram)==null?void 0:t.WebApp)==null?void 0:n.HapticFeedback)!=null&&o.impactOccurred&&typeof window.Telegram.WebApp.HapticFeedback.impactOccurred=="function"&&window.Telegram.WebApp.HapticFeedback.impactOccurred(e)}catch(a){console.debug("HapticFeedback не поддерживается:",a.message)}}function Re(e=!0,t=0){const n=document.getElementById("cart-float"),o=document.getElementById("cart-counter");!n||!o||(e&&t>0?(o.textContent=t,o.classList.remove("hidden"),n.classList.remove("hidden"),o.style.animation="cart-counter-pulse 2s infinite"):(o.classList.add("hidden"),n.classList.add("hidden")))}function ye(){const e=document.querySelector(".cart-float-btn"),t=document.querySelector(".cart-float-btn .fa-shopping-cart");e&&t&&(e.style.transform="translateY(-4px) scale(1.1)",e.style.boxShadow="0 12px 32px rgba(16, 185, 129, 0.5)",t.style.transform="rotate(-15deg) scale(1.2)",setTimeout(()=>{e.style.transform="",e.style.boxShadow="",t.style.transform=""},300),y("medium"))}function F(e=!0){const t=document.querySelector(".cart-float-btn");t&&(e?(t.classList.add("loading"),t.setAttribute("aria-busy","true")):(t.classList.remove("loading"),t.setAttribute("aria-busy","false")))}function N(e=!0){const t=document.querySelector(".cart-float-btn");t&&e&&(t.style.background="linear-gradient(135deg, #ef4444 0%, #dc2626 100%)",setTimeout(()=>{t.style.background=""},2e3))}function oe(e){return(e.is_active!==void 0?e.is_active:e.isAvailable)?e.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':e.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${e.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function Oe(e){return"bg-success"}function Ye(e){return e<=0?"Нет в наличии":`${e} шт.`}function Qe(e,t){var r;const n=document.getElementById(`modal-quantity-${e}`);if(!n)return;const o=parseInt(n.textContent)||1,a=Math.max(1,o+t),s=(r=window.cachedProductsData)==null?void 0:r[e];if(s){const i=Math.min(s.quantity,99),l=Math.min(a,i),c=s.price_with_markup||s.price;ge(e,l,c,s.quantity)}}function ge(e,t,n,o){const a=document.getElementById(`modal-quantity-${e}`),s=document.getElementById(`modal-decrease-${e}`),r=document.getElementById(`modal-increase-${e}`),i=document.getElementById(`modal-total-price-${e}`);if(a&&(a.textContent=t),s&&(s.disabled=t<=1),r&&(r.disabled=t>=o),i&&n){const l=t*n;i.textContent=`Итого: ${H(l)} ₽`}}function Ne(e){const t=document.getElementById(`modal-quantity-${e}`),n=t&&parseInt(t.textContent)||1;ee(e,n);const o=document.getElementById("productModal");if(o){const a=bootstrap.Modal.getInstance(o);a&&a.hide()}}function he(){try{const e=document.getElementById("cartModal"),t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");if(!e||!t){console.error("Элементы модального окна корзины не найдены");return}e.classList.add("show"),e.style.display="block",document.body.style.overflow="hidden",t.innerHTML=`
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка корзины...</div>
            </div>
        `,n.style.display="none",fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(o=>o.json()).then(o=>{o.success&&o.items&&o.items.length>0?we(o.items,o.total_amount):(t.innerHTML=`
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина пуста</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `,n.style.display="none")}).catch(o=>{console.error("Ошибка при загрузке корзины:",o),t.innerHTML=`
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Ошибка при загрузке корзины</h5>
                    <p class="text-muted">Попробуйте обновить страницу</p>
                </div>
            `,n.style.display="none"})}catch(e){console.error("Ошибка при загрузке корзины:",e),v("Ошибка при загрузке корзины","error")}}function we(e,t){const n=document.getElementById("cartModalBody"),o=document.getElementById("cartModalFooter");if(!n||!o)return;if(!e||e.length===0){n.innerHTML=`
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Корзина пуста</h5>
                <p class="text-muted">Добавьте товары для оформления заказа</p>
            </div>
        `,o.style.display="none";return}let a='<div class="cart-items">';e.forEach(s=>{a+=`
            <div class="cart-item mb-3 p-3 border rounded"style="flex-direction: column;" data-cart-id="${s.id}">
                <div class="d-flex align-items-start" style="width: 100%; padding-bottom: 10px;">
                    <div class="cart-item-image  flex-shrink-0">
                        ${s.main_photo_url||s.photo_url?`<img src="${s.main_photo_url||s.photo_url}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="${s.name}">`:`<div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
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
        `}),a+="</div>",n.innerHTML=a,o.innerHTML=`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100">
            <div class="cart-total" style="width:100%;">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Итого: ${H(t)} ₽
                </h5>
                <small class="text-muted">Товаров в корзине: ${e.length}</small>
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
    `,o.style.display="block"}function Ue(e,t){if(t<=0){ve(e);return}const n=document.querySelector(`[data-cart-id="${e}"]`);if(n){const o=n.querySelector(".quantity-controls");o&&(o.style.opacity="0.5",o.style.pointerEvents="none")}fetch(`/cart/update/${e}`,{method:"PATCH",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"},body:JSON.stringify({quantity:t})}).then(o=>o.json()).then(o=>{o.success?(E("Количество обновлено","success"),A(),L(),y("light")):E(o.message||"Ошибка при обновлении количества","error")}).catch(o=>{console.error("Ошибка при обновлении количества:",o),E("Ошибка при обновлении количества","error")}).finally(()=>{if(n){const o=n.querySelector(".quantity-controls");o&&(o.style.opacity="1",o.style.pointerEvents="auto")}})}function L(){const e=document.getElementById("cartModalBody"),t=document.getElementById("cartModalFooter");!e||!t||fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success&&n.items&&n.items.length>0?we(n.items,n.total_amount):(e.innerHTML=`
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пуста</h5>
                    <p class="text-muted">Добавьте товары для оформления заказа</p>
                </div>
            `,t.style.display="none")}).catch(n=>{console.error("Ошибка при обновлении корзины:",n)})}function ve(e){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Удалить товар из корзины?",t=>{t&&ae(e)});else{if(!confirm("Удалить товар из корзины?"))return;ae(e)}}function ae(e){const t=document.querySelector(`[data-cart-id="${e}"]`);t&&(t.style.opacity="0.5",t.style.transform="scale(0.95)",t.style.pointerEvents="none"),fetch(`/cart/remove/${e}`,{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success?(E("Товар удален из корзины","success"),A(),y("medium"),t?(t.style.transition="all 0.3s ease",t.style.opacity="0",t.style.transform="translateX(100%)",setTimeout(()=>{L()},300)):L()):(E(n.message||"Ошибка при удалении товара","error"),t&&(t.style.opacity="1",t.style.transform="scale(1)",t.style.pointerEvents="auto"))}).catch(n=>{console.error("Ошибка при удалении товара:",n),E("Ошибка при удалении товара","error"),t&&(t.style.opacity="1",t.style.transform="scale(1)",t.style.pointerEvents="auto")})}function ze(){if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.showConfirm)window.Telegram.WebApp.showConfirm("Очистить всю корзину?",e=>{e&&se()});else{if(!confirm("Очистить всю корзину?"))return;se()}}function se(){const e=document.getElementById("cartModalBody"),t=document.getElementById("cartModalFooter");e&&(e.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Очистка корзины...</span>
                </div>
                <div class="mt-3">Очищаем корзину...</div>
            </div>
        `),t&&(t.style.display="none"),fetch("/cart/clear",{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success?(E("Корзина очищена","success"),A(),y("medium"),e&&(e.innerHTML=`
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина очищена</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `),setTimeout(()=>{const o=document.getElementById("cartModal");if(o){const a=bootstrap.Modal.getInstance(o);a&&a.hide()}},1500)):(E(n.message||"Ошибка при очистке корзины","error"),L())}).catch(n=>{console.error("Ошибка при очистке корзины:",n),E("Ошибка при очистке корзины","error"),L()})}function Ve(){if(!I){v("Ошибка: данные пользователя недоступны","error");return}v("Проверяем корзину...","info"),fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{if(!e.success||!e.items||e.items.length===0){v("Корзина пуста","warning");return}v("Оформляем заказ...","info");const t={bot_short_name:document.querySelector('meta[name="short-name"]').getAttribute("content"),user_data:I,notes:""};return le("/cart/checkout",{method:"POST",body:JSON.stringify(t)})}).then(e=>{if(e)return e.json()}).then(e=>{e&&(e.success?(W(),v(`Заказ успешно оформлен! Номер заказа: ${e.order.order_number}`,"success"),A(),setTimeout(()=>{const t=document.getElementById("cartModalBody");t&&(t.innerHTML=`
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Корзина пуста</h5>
                            <p class="text-muted">Добавьте товары для оформления заказа</p>
                        </div>
                    `);const n=document.getElementById("cartModalFooter");n&&(n.style.display="none")},1e3),y("success")):(v(e.message||"Ошибка при оформлении заказа","error"),y("error")))}).catch(e=>{console.error("Ошибка при оформлении заказа:",e),v("Произошла ошибка при оформлении заказа","error"),y("error")})}function te(){try{const e=document.getElementById("productModal");e&&(e.classList.remove("show"),e.style.display="none"),document.body.style.overflow="",y("light")}catch(e){console.error("Ошибка при закрытии модального окна товара:",e),document.body.style.overflow=""}}function W(){try{const e=document.getElementById("cartModal");e&&(e.classList.remove("show"),e.style.display="none"),document.body.style.overflow="",y("light")}catch(e){console.error("Ошибка при закрытии модального окна корзины:",e),document.body.style.overflow=""}}function be(){if(localStorage.getItem("hasSeenSwipeHint"))return;const t=document.createElement("div");t.className="swipe-indicator",t.innerHTML=`
        <span class="arrow">→</span>
        <span>Свайп для выхода</span>
    `,document.body.appendChild(t),setTimeout(()=>{t.parentNode&&t.remove(),localStorage.setItem("hasSeenSwipeHint","true")},3e3)}function Te(){if(window.Telegram&&window.Telegram.WebApp){const e=window.Telegram.WebApp;try{if(e.expand(),e.version&&parseFloat(e.version)>=6.1&&e.disableClosingConfirmation)try{e.disableClosingConfirmation(),console.log("Closing confirmation disabled for WebApp version",e.version)}catch{console.log("Closing confirmation control not supported in version",e.version)}else console.log("Closing confirmation not available in WebApp version",e.version||"unknown");e.isClosingConfirmationEnabled!==void 0&&(e.isClosingConfirmationEnabled=!1),e.setViewportHeight&&e.setViewportHeight(window.innerHeight),console.log("Настройки скролла для Telegram WebApp применены")}catch(t){console.error("Ошибка при настройке поведения скролла:",t)}}document.body.style.touchAction="pan-x pan-y",document.documentElement.style.touchAction="pan-x pan-y"}function Ee(){let e=0,t=0,n=!1;const o=10;function a(i){if(i.touches.length!==1)return;const l=i.touches[0];e=l.clientY,t=l.clientX,n=!1,(document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&(n=!0)}function s(i){var p,g;if(i.touches.length!==1||!n)return;const l=i.touches[0],c=l.clientY-e,d=Math.abs(l.clientX-t);if((document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&c>o&&d<50)return i.preventDefault(),i.stopPropagation(),i.stopImmediatePropagation(),console.log("Заблокировано потенциальное сворачивание через pull-to-close"),(g=(p=window.Telegram)==null?void 0:p.WebApp)!=null&&g.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("rigid"),!1}function r(i){n=!1}document.addEventListener("touchstart",a,{passive:!1,capture:!0}),document.addEventListener("touchmove",s,{passive:!1,capture:!0}),document.addEventListener("touchend",r,{passive:!0,capture:!0}),document.body.style.overscrollBehavior="none",document.documentElement.style.overscrollBehavior="none",window.addEventListener("beforeunload",function(i){if(i.clientY<50)return i.preventDefault(),!1}),console.log("Защита от сворачивания через скролл активирована")}function Ie(){let e=0,t=0,n=!1;function o(i){if(i.touches.length!==1)return;const l=i.touches[0];e=l.clientX,t=l.clientY,n=!0}function a(i){if(!n||i.touches.length!==1)return;const l=i.touches[0],c=l.clientX-e,d=l.clientY-t;if(Math.abs(c)>Math.abs(d)&&c>50&&Math.abs(d)<100){const f=document.querySelector(".modal.show");f&&(f.id==="productModal"?te():f.id==="cartModal"&&W()),n=!1}}function s(){n=!1}[document.getElementById("productModal"),document.getElementById("cartModal")].forEach(i=>{i&&(i.addEventListener("touchstart",o,{passive:!0}),i.addEventListener("touchmove",a,{passive:!0}),i.addEventListener("touchend",s,{passive:!0}))})}function xe(){let e=0,t=0,n=!1;const o=100,a=50;function s(c){if(c.touches.length!==1)return;const d=c.touches[0];e=d.clientX,t=d.clientY,e<=a&&(n=!0,console.log("Начат свайп с левого края:",e))}function r(c){var g,$,b,m;if(!n||c.touches.length!==1)return;const d=c.touches[0],f=d.clientX-e,p=d.clientY-t;if(Math.abs(f)>Math.abs(p)&&f>o&&Math.abs(p)<100){if(console.log("Свайп вправо обнаружен, deltaX:",f),C){console.log("Выход из категории через свайп"),($=(g=window.Telegram)==null?void 0:g.WebApp)!=null&&$.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),X(),n=!1;return}if(_){console.log("Очистка поиска через свайп"),(m=(b=window.Telegram)==null?void 0:b.WebApp)!=null&&m.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),X(),n=!1;return}n=!1}}function i(){n=!1}const l=document.getElementById("app");l&&(l.addEventListener("touchstart",s,{passive:!0}),l.addEventListener("touchmove",r,{passive:!0}),l.addEventListener("touchend",i,{passive:!0}),console.log("Обработчики свайпа для выхода из категории добавлены"))}function $e(){const e=document.getElementById("backButton");e&&(C&&window.innerWidth>768?(e.style.display="flex",e.classList.add("show")):(e.style.display="none",e.classList.remove("show")))}window.initApp=ce;window.showAlert=v;window.filterByCategory=D;window.showAllProducts=X;window.performSearch=Q;window.addToCart=K;window.addToCartWithQuantity=ee;window.showProductDetails=Z;window.showCart=je;window.showCartModal=he;window.closePanel=ue;window.changeQuantityModal=Qe;window.addToCartFromModal=Ne;window.changeQuantity=De;window.validateQuantity=We;window.updateCartQuantity=Ue;window.removeFromCart=ve;window.clearCart=ze;window.proceedToCheckout=Ve;window.refreshCartContent=L;window.closeProductModal=te;window.closeCartModal=W;window.showSwipeHint=be;window.setupScrollBehavior=Te;window.preventPullToClose=Ee;window.addSwipeSupport=Ie;window.addCategorySwipeSupport=xe;window.toggleCartFloat=Re;window.animateCartButtonOnAdd=ye;window.setCartButtonLoading=F;window.setCartButtonError=N;window.handleBackButtonVisibility=$e;window.setGalleryImage=P;window.previousGalleryImage=U;window.nextGalleryImage=z;window.openGalleryFullscreen=Ke;window.closeGalleryFullscreen=Ze;window.previousFullscreenImage=V;window.nextFullscreenImage=J;window.initGallerySwipe=Me;window.setupCategoryLazyLoading=de;function Me(){const e=document.querySelector(".product-gallery"),t=document.getElementById("gallery-fullscreen");e&&ie(e,!1),t&&ie(t,!0)}function ie(e,t=!1){let n=0,o=0,a=!1,s=0;const r=50,i=300;let l=!1;const c=t?e.querySelector(".gallery-fullscreen-image"):e.querySelector(".gallery-main-image");if(!c)return;c.style.cursor="grab",c.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",c.addEventListener("touchstart",d,{passive:!1}),c.addEventListener("touchmove",f,{passive:!1}),c.addEventListener("touchend",p,{passive:!1}),c.addEventListener("mousedown",g,{passive:!1}),document.addEventListener("mousemove",$,{passive:!1}),document.addEventListener("mouseup",b,{passive:!1});function d(m){const w=m.touches[0];n=w.clientX,o=w.clientY,w.clientX,a=!0,s=Date.now(),c.style.transition="none"}function f(m){if(!a)return;const w=m.touches[0];w.clientX;const T=Math.abs(w.clientY-o),h=w.clientX-n;if(Math.abs(h)>T&&Math.abs(h)>10){m.preventDefault();const k=h*.3;c.style.transform=`translateX(${k}px)`}}function p(m){if(!a)return;const w=m.changedTouches[0].clientX,T=m.changedTouches[0].clientY,h=w-n,k=Math.abs(T-o),j=Date.now()-s;c.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",c.style.transform="translateX(0)",a=!1,Math.abs(h)>k&&(Math.abs(h)>r||j<i&&Math.abs(h)>30)&&setTimeout(()=>{h>0?(t?V():U(),y("light")):(t?J():z(),y("light"))},100)}function g(m){m.target.closest("button")||m.target.closest(".gallery-thumbnail")||(l=!0,n=m.clientX,o=m.clientY,m.clientX,a=!1,s=Date.now(),c.style.cursor="grabbing",c.style.transition="none",m.preventDefault())}function $(m){if(!l)return;m.clientX;const w=Math.abs(m.clientY-o),T=m.clientX-n;if((Math.abs(T)>5||w>5)&&(a=!0),Math.abs(T)>w&&Math.abs(T)>10){m.preventDefault();const h=T*.3;c.style.transform=`translateX(${h}px)`}}function b(m){if(!l)return;const w=m.clientX,T=m.clientY,h=w-n,k=Math.abs(T-o),j=Date.now()-s;l=!1,c.style.cursor="grab",c.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",c.style.transform="translateX(0)",a&&Math.abs(h)>k&&(Math.abs(h)>r||j<i&&Math.abs(h)>30)&&(setTimeout(()=>{h>0?(t?V():U(),y("light")):(t?J():z(),y("light"))},100),m.preventDefault()),a=!1}}function P(e){if(!window.currentGallery||!window.currentGallery.photos||e<0||e>=window.currentGallery.photos.length)return;window.currentGallery.currentIndex=e;const t=document.getElementById("main-gallery-image"),n=document.getElementById("gallery-current"),o=document.querySelectorAll(".gallery-thumbnail");t&&(t.style.opacity="0",t.style.transform="scale(0.95)",setTimeout(()=>{t.src=x(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1)"},50)},150)),n&&(n.textContent=e+1),o.forEach((a,s)=>{s===e?a.classList.add("active"):a.classList.remove("active")}),Je()}function U(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.max(0,window.currentGallery.currentIndex-1);P(e)}function z(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.min(window.currentGallery.photos.length-1,window.currentGallery.currentIndex+1);P(e)}function Je(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=document.getElementById("gallery-prev"),t=document.getElementById("gallery-next");e&&(e.disabled=window.currentGallery.currentIndex===0),t&&(t.disabled=window.currentGallery.currentIndex===window.currentGallery.photos.length-1)}function Ke(e=null){if(!window.currentGallery||!window.currentGallery.photos)return;e!==null&&(window.currentGallery.currentIndex=e);const t=document.getElementById("gallery-fullscreen"),n=document.getElementById("fullscreen-image");t&&n&&(n.src=x(window.currentGallery.photos[window.currentGallery.currentIndex]),t.classList.add("show"),document.body.style.overflow="hidden")}function Ze(){const e=document.getElementById("gallery-fullscreen");e&&(e.classList.remove("show"),document.body.style.overflow="auto")}function V(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.max(0,window.currentGallery.currentIndex-1);if(e===window.currentGallery.currentIndex)return;window.currentGallery.currentIndex=e;const t=document.getElementById("fullscreen-image");t&&(t.style.opacity="0",t.style.transform="scale(0.95) translateX(0)",setTimeout(()=>{t.src=x(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1) translateX(0)"},50)},150)),P(e)}function J(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.min(window.currentGallery.photos.length-1,window.currentGallery.currentIndex+1);if(e===window.currentGallery.currentIndex)return;window.currentGallery.currentIndex=e;const t=document.getElementById("fullscreen-image");t&&(t.style.opacity="0",t.style.transform="scale(0.95) translateX(0)",setTimeout(()=>{t.src=x(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1) translateX(0)"},50)},150)),P(e)}let G=!1,S=1,M=!0;function et(){const e=document.getElementById("infiniteScrollTrigger");if(!e){console.log("Infinite scroll trigger не найден");return}console.log("Инициализация бесконечной прокрутки товаров"),S=parseInt(e.getAttribute("data-next-page"))||2,M=e.getAttribute("data-has-more")==="true",new IntersectionObserver(n=>{n.forEach(o=>{o.isIntersecting&&M&&!G&&tt()})},{root:null,rootMargin:"200px",threshold:.1}).observe(e),console.log("Infinite scroll инициализирован, следующая страница:",S)}async function tt(){var t;if(G||!M)return;G=!0;const e=document.getElementById("infiniteScrollLoader");e&&(e.style.display="block");try{const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],o=new URL(`/${n}`,window.location.origin);o.searchParams.set("page",S),console.log("Загрузка страницы товаров:",S);const a=await fetch(o.toString(),{headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"}});if(!a.ok)throw new Error(`HTTP error! status: ${a.status}`);const s=await a.text(),i=new DOMParser().parseFromString(s,"text/html"),l=i.querySelectorAll(".product-card");if(l.length===0){M=!1;const f=document.getElementById("infiniteScrollTrigger");f&&f.remove(),e&&(e.innerHTML=`
                    <div style="text-align: center; padding: 20px; color: #888;">
                        <i class="fas fa-check-circle"></i> Все товары загружены
                    </div>
                `,setTimeout(()=>{e.style.display="none"},2e3)),console.log("Все товары загружены");return}const c=document.querySelector(".products-grid");c&&(l.forEach(f=>{const p=f.cloneNode(!0),g=p.querySelector(".add-to-cart");g&&!g.disabled&&g.addEventListener("click",function($){$.stopPropagation();const b=parseInt(this.getAttribute("data-product-id"));b&&K(b)}),p.addEventListener("click",function($){if(!$.target.closest(".add-to-cart")){const b=parseInt(this.getAttribute("data-product-id"));b&&Z(b)}}),p.style.opacity="0",p.style.transform="translateY(20px)",c.appendChild(p),setTimeout(()=>{p.style.transition="opacity 0.3s ease, transform 0.3s ease",p.style.opacity="1",p.style.transform="translateY(0)"},50)}),console.log(`Добавлено ${l.length} товаров`));const d=i.getElementById("infiniteScrollTrigger");d?(S=parseInt(d.getAttribute("data-next-page"))||S+1,M=d.getAttribute("data-has-more")==="true"):M=!1}catch(n){console.error("Ошибка загрузки товаров:",n),M=!1,e&&(e.innerHTML=`
                <div style="text-align: center; padding: 20px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i> Ошибка загрузки товаров
                </div>
            `,setTimeout(()=>{e.style.display="none"},3e3))}finally{G=!1,e&&M&&(e.style.display="none")}}document.addEventListener("DOMContentLoaded",function(){var o;console.log("DOM загружен, начинаем инициализацию Mini App"),Ae();const e=document.getElementById("loading"),t=document.getElementById("app");console.log("Элементы найдены:",{loading:!!e,app:!!t,telegramWebApp:!!((o=window.Telegram)!=null&&o.WebApp)});try{window.isAppInitializedByBlade?console.log("initApp already called from Blade template"):(console.log("Calling initApp from main DOMContentLoaded"),ce())}catch(a){console.error("Критическая ошибка инициализации:",a),e&&(e.style.display="none"),t&&(t.style.display="block")}setTimeout(()=>{try{A()}catch(a){console.error("Ошибка обновления счетчика корзины:",a)}},1e3),setTimeout(()=>{try{et()}catch(a){console.error("Ошибка инициализации infinite scroll:",a)}},1500),document.addEventListener("keydown",function(a){a.key==="Escape"&&ue()});function n(){const a=document.getElementById("productModal"),s=document.getElementById("cartModal");a&&a.addEventListener("click",function(r){r.target===a&&te()}),s&&s.addEventListener("click",function(r){r.target===s&&W()}),document.addEventListener("click",function(r){const i=r.target.closest(".product-card");if(i&&!r.target.closest(".add-to-cart")){const d=parseInt(i.getAttribute("data-product-id"));d&&Z(d)}const l=r.target.closest(".add-to-cart");if(l&&!l.disabled){r.stopPropagation();const d=parseInt(l.getAttribute("data-product-id"));d&&K(d)}const c=r.target.closest(".category-card");if(c){const d=parseInt(c.getAttribute("data-category-id")),f=c.getAttribute("data-category-name");d&&f&&D(d,f)}}),Ie(),xe(),console.log("Обработчики модальных окон настроены")}window.setupModalBackdropHandlers=n,Ee()});
