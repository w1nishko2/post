var de;(de=window.Telegram)!=null&&de.WebApp;var ue,me;const qe=!!((me=(ue=window.Telegram)==null?void 0:ue.WebApp)!=null&&me.initData);let I=null,ae=!1,Q=[],R=[],q=!1,k=!1;function d(e){if(!e)return"";const t={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"};return String(e).replace(/[&<>"']/g,function(n){return t[n]})}function _e(){const e=document.querySelector('meta[name="csrf-token"]');return e?e.getAttribute("content"):null}function ee(e,t={}){const n=_e();return t.method&&["POST","PUT","DELETE","PATCH"].includes(t.method.toUpperCase())&&(t.headers={"Content-Type":"application/json",Accept:"application/json",...t.headers},n&&(t.headers["X-CSRF-TOKEN"]=n)),fetch(e,t)}function He(){var n;const e=(n=window.Telegram)==null?void 0:n.WebApp;if(!e)return null;const t=parseFloat(e.version||"0");return console.log(`[Telegram WebApp] Версия: ${e.version}`),t<6.1&&(console.warn("[Telegram WebApp] Устаревшая версия. Некоторые функции могут не работать."),console.warn("[Telegram WebApp] Рекомендуется обновить Telegram до последней версии."),setTimeout(()=>{b("Обновите Telegram для улучшенной работы приложения","warning")},3e3)),t}function fe(){if(ae)return;ae=!0;const e=setTimeout(()=>{try{const t=document.getElementById("loading"),n=document.getElementById("app");t&&(t.style.display="none"),n&&(n.style.display="block")}catch(t){console.error("Ошибка при принудительном показе приложения:",t)}},2e3);if(window.Telegram&&window.Telegram.WebApp){const t=window.Telegram.WebApp;try{const n=He();if(t.ready(),t.expand(),t.initDataUnsafe&&t.initDataUnsafe.user?(I=t.initDataUnsafe.user,O(I)):t.initData&&(I=Fe(t.initData),I&&O(I)),document.documentElement.style.setProperty("--tg-color-scheme",t.colorScheme),document.documentElement.style.setProperty("--tg-theme-bg-color",t.themeParams.bg_color||"#ffffff"),document.documentElement.style.setProperty("--tg-theme-text-color",t.themeParams.text_color||"#000000"),$e(),n>=6.2&&typeof t.enableClosingConfirmation=="function")try{t.enableClosingConfirmation()}catch(o){console.warn("enableClosingConfirmation не поддерживается:",o)}if(n>=6.1&&t.BackButton)try{typeof t.BackButton.hide=="function"&&t.BackButton.hide()}catch{}}catch(n){console.error("Ошибка инициализации Telegram WebApp:",n),Pe("Ошибка загрузки Telegram WebApp")}}else I={id:123456789,first_name:"Тестовый",last_name:"Пользователь",username:"testuser"},O(I);try{De(),je().catch(t=>{console.error("Ошибка загрузки категорий:",t)})}catch(t){console.error("Ошибка инициализации поиска/категорий:",t)}setTimeout(()=>{try{clearTimeout(e);const t=document.getElementById("loading"),n=document.getElementById("app");t&&(t.style.display="none"),n&&(n.style.display="block"),setTimeout(()=>{try{Le()}catch(o){console.error("Ошибка настройки модальных окон:",o)}},100),window.addEventListener("resize",Se)}catch(t){console.error("Ошибка при скрытии загрузочного экрана:",t)}},500)}function Pe(e){const t=document.createElement("div");t.className="alert alert-danger",t.style.cssText=`
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 9999;
        margin: 0;
    `,t.innerHTML=`
        <strong>Ошибка:</strong> ${e}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `,document.body.appendChild(t),setTimeout(()=>{t.parentElement&&t.remove()},5e3)}function O(e){const t=document.querySelector(".user-greeting");if(t&&e){const n=e.first_name||e.username||"Пользователь";t.textContent=`Привет, ${n}!`}}function Fe(e){try{const n=new URLSearchParams(e).get("user");if(n)return JSON.parse(decodeURIComponent(n))}catch(t){console.error("Ошибка парсинга user из initData:",t)}return null}function u(e,t="info"){var n;try{const o=(n=window.Telegram)==null?void 0:n.WebApp,s=parseFloat((o==null?void 0:o.version)||"0");if(o&&s>=6.1&&typeof o.showAlert=="function"){o.showAlert(e);return}}catch(o){console.warn("Не удалось показать Telegram уведомление:",o)}b(e,t)}function b(e,t="info"){const n=document.getElementById("toast-container")||Ge(),o=document.createElement("div"),s=t==="error"?"danger":t==="success"?"success":t==="warning"?"warning":"primary";o.className=`toast align-items-center bg-${s} border-0`,o.setAttribute("role","alert"),o.innerHTML=`
        <div class="d-flex">
            <div class="toast-body">${e}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `,n.appendChild(o),window.bootstrap&&window.bootstrap.Toast?(new window.bootstrap.Toast(o).show(),o.addEventListener("hidden.bs.toast",()=>{o.remove()})):(o.style.display="block",setTimeout(()=>{o.remove()},5e3))}function Ge(){const e=document.createElement("div");return e.id="toast-container",e.className="toast-container position-fixed top-0 end-0 p-3",e.style.zIndex="9999",document.body.appendChild(e),e}function Xe(){(!CSS.supports||!CSS.supports("display","grid"))&&document.documentElement.classList.add("no-cssgrid")}function De(){const e=document.getElementById("searchInput");if(!e)return;let t;e.addEventListener("input",function(n){clearTimeout(t),t=setTimeout(()=>{U(n.target.value)},300)}),e.addEventListener("keypress",function(n){n.key==="Enter"&&U(n.target.value)}),We()}async function We(){var e;try{const t=document.getElementById("products-data");if(t)try{const s=JSON.parse(t.textContent);Q=Object.values(s);return}catch(s){console.warn("Ошибка парсинга встроенных данных товаров:",s)}const n=((e=document.querySelector('meta[name="short-name"]'))==null?void 0:e.content)||window.location.pathname.split("/")[1],o=await fetch(`/${n}/api/products`);o.ok&&(Q=await o.json())}catch(t){console.error("Ошибка при загрузке товаров:",t)}}async function je(){var e;try{const t=((e=document.querySelector('meta[name="short-name"]'))==null?void 0:e.content)||window.location.pathname.split("/")[1],n=new AbortController,o=setTimeout(()=>n.abort(),5e3),s=`/${t}/api/categories`,a=await fetch(s,{signal:n.signal,headers:{Accept:"application/json","Content-Type":"application/json"}});if(clearTimeout(o),a.ok&&(R=await a.json(),R.length>0)){const r=document.getElementById("categoriesContainer");r&&(r.style.display="block"),Re(R)}}catch(t){t.name!=="AbortError"&&console.error("Ошибка при загрузке категорий:",t)}}function Re(e){const t=document.getElementById("categoriesTrack");if(!t){console.error("Элемент categoriesTrack не найден");return}if(e.length===0){const s=document.getElementById("categoriesContainer");s&&(s.style.display="none");return}window.allCategoriesData=e;const n=8;t.innerHTML=e.map((s,a)=>a<n?`
            <div class="swiper-slide">
                <div class="category-card" 
                     data-category-id="${s.id}" 
                     data-category-name="${d(s.name)}"
                     data-index="${a}"
                     data-loaded="true">
                    <div class="card h-200">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="category-info">
                                    <div class="category-name">${d(s.name)}</div>
                                    ${s.description?`<div class="category-description">${d(s.description)}</div>`:""}
                                    <div class="category-products-count">${s.products_count||0} товаров</div>
                                </div>
                                ${s.photo_url?`<img src="${d(s.photo_url)}" class="category-image" alt="${d(s.name)}" onerror="handleImageError(this)" loading="eager">
                                       <div class="category-placeholder" style="display: none;">
                                           <i class="fas fa-folder"></i>
                                           <span class="placeholder-text">Изображение недоступно</span>
                                       </div>`:`<div class="category-placeholder">
                                           <i class="fas fa-folder"></i>
                                       </div>`}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`:`
            <div class="swiper-slide">
                <div class="category-card category-skeleton" 
                     data-index="${a}"
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
                </div>
            </div>`).join(""),t.querySelectorAll('.category-card[data-loaded="true"]').forEach(s=>{s.addEventListener("click",function(){const a=this.getAttribute("data-category-id"),r=this.getAttribute("data-category-name");a&&r&&W(parseInt(a),r)})}),typeof window.reinitCategoriesSwiper=="function"&&setTimeout(()=>{window.reinitCategoriesSwiper()},100)}function pe(){const e=window.categoriesSwiper;if(!e){console.warn("Swiper не инициализирован для ленивой загрузки");return}if(!e.slides||e.slides.length===0){console.warn("Swiper slides не найдены, отложенная инициализация..."),setTimeout(()=>{pe()},500);return}const t=o=>{var r;if(!o||o.getAttribute("data-loaded")==="true")return;const s=parseInt(o.getAttribute("data-index")),a=(r=window.allCategoriesData)==null?void 0:r[s];a&&(o.innerHTML=`
            <div class="card h-200">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="category-info">
                            <div class="category-name">${d(a.name)}</div>
                            ${a.description?`<div class="category-description">${d(a.description)}</div>`:""}
                            <div class="category-products-count">${a.products_count||0} товаров</div>
                        </div>
                        ${a.photo_url?`<img src="${d(a.photo_url)}" class="category-image" alt="${d(a.name)}" onerror="handleImageError(this)" loading="lazy">
                               <div class="category-placeholder" style="display: none;">
                                   <i class="fas fa-folder"></i>
                                   <span class="placeholder-text">Изображение недоступно</span>
                               </div>`:`<div class="category-placeholder">
                                   <i class="fas fa-folder"></i>
                               </div>`}
                    </div>
                </div>
            </div>
        `,o.setAttribute("data-category-id",a.id),o.setAttribute("data-category-name",a.name),o.setAttribute("data-loaded","true"),o.classList.remove("category-skeleton"),o.classList.add("category-loaded"),o.addEventListener("click",function(){const i=this.getAttribute("data-category-id"),c=this.getAttribute("data-category-name");i&&c&&W(parseInt(i),c)}))},n=()=>{if(!e.slides||e.slides.length===0){console.warn("Slides не доступны для загрузки");return}const o=e.activeIndex||0,s=e.params.slidesPerView==="auto"?4:e.params.slidesPerView,a=Math.max(0,o-2),r=Math.min(e.slides.length-1,o+s+4);for(let i=a;i<=r;i++){const c=e.slides[i],l=c==null?void 0:c.querySelector(".category-card");l&&t(l)}};e.on("slideChange",()=>{n()}),e.on("progress",()=>{n()}),e.on("reachEnd",()=>{document.querySelectorAll(".category-skeleton").forEach(t)}),n()}function U(e=null){const t=document.getElementById("searchInput"),n=e!==null?e:t?t.value.trim():"";if(n===""||n.length<2){X(),q=!1,k=!1;return}q=!0,k=!1;const o=65,a=Q.map(r=>{const i=r.name||"",c=r.description||"",l=r.article||"",m=Y(n,i),p=l?Y(n,l):0,h=c?Y(n,c):0,g=Math.max(m,p,h);return{...r,similarity:g,matchField:m===g?"name":p===g?"article":"description"}}).filter(r=>r.similarity>=o).sort((r,i)=>{if(i.similarity!==r.similarity)return i.similarity-r.similarity;if(r.matchField==="name"&&i.matchField!=="name")return-1;if(i.matchField==="name"&&r.matchField!=="name")return 1;if(r.matchField==="article"&&i.matchField==="description")return-1;if(i.matchField==="article"&&r.matchField==="description")return 1;const c=(r.quantity||0)>0?1:0,l=(i.quantity||0)>0?1:0;if(l!==c)return l-c;const m=r.created_at?new Date(r.created_at).getTime():0;return(i.created_at?new Date(i.created_at).getTime():0)-m});a.forEach(r=>{}),Oe(a,n)}function Oe(e,t){const n=document.getElementById("productsContainer");if(!n)return;if(e.length===0){n.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${d(t)}"</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h6>Ничего не найдено</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const o=e.map(s=>{const a=s.main_photo_url||s.photo_url,r=!!a,i=s.isAvailable&&s.quantity>0;return`
        <article class="product-card" data-product-id="${s.id}">
            <div class="product-image ${r?"":"no-image"}">
                ${r?`<img src="${d($(a))}" alt="${d(s.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${s.has_multiple_photos?'<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>':""}`:""}
                ${he(s)}
                ${s.similarity?`<div class="product-badge" style="background: var(--color-gray); top: auto; bottom: var(--space-xs); left: var(--space-xs);">${Math.round(s.similarity)}%</div>`:""}
            </div>
            <div class="product-info">
                <h3 class="product-name">${d(s.name)}</h3>
                ${s.description?`<p class="product-description">${d(s.description)}</p>`:""}
                <div class="product-footer">
                    <span class="product-price">${d(s.formatted_price||S(s.price))}</span>
                    <button class="add-to-cart ${i?"":"disabled"}" 
                            data-product-id="${s.id}"
                            ${i?"":"disabled"}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `}).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-search me-2"></i>Результаты поиска: "${d(t)}" (найдено: ${e.length})</h5>
        </div>
        <div class="products-grid">
            ${o}
        </div>
    `}async function W(e,t){q=!0,k=!0;const n=document.getElementById("backButton");n&&window.innerWidth>768&&(n.style.display="flex",n.classList.add("show"));const o=document.getElementById("productsContainer");if(!o){console.error("Контейнер productsContainer не найден");return}o.innerHTML=`
        <div class="loading-content" style="padding: 2rem; text-align: center;">
            <div class="loading-spinner"></div>
            <div class="loading-text">Загрузка товаров...</div>
        </div>
    `;try{const s=document.querySelector('meta[name="short-name"]'),a=s?s.getAttribute("content"):"";if(!a)throw new Error("Short name не найден");const r=await ee(`/${a}/api/search?category_id=${e}`);if(!r.ok)throw new Error(`Ошибка загрузки: ${r.status}`);const i=await r.json();if(i.length===0){o.innerHTML=`
                <div class="products-header">
                    <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${d(t)}</h5>
                </div>
                <div class="no-results">
                    <i class="fas fa-folder-open"></i>
                    <h6>В этой категории пока нет товаров</h6>
                    <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                        Показать все товары
                    </button>
                </div>
            `;return}Ne(i,t);const c=document.getElementById("searchInput");c&&(c.value=""),document.body.classList.add("category-view"),setTimeout(()=>{xe()},1e3)}catch(s){console.error("Ошибка загрузки товаров категории:",s),o.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${d(t)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <h6>Ошибка загрузки товаров</h6>
                <p class="text-muted">${d(s.message)}</p>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Вернуться к каталогу
                </button>
            </div>
        `}}function Ne(e,t){const n=document.getElementById("productsContainer");if(!n)return;if(e.length===0){n.innerHTML=`
            <div class="products-header">
                <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${d(t)}</h5>
            </div>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h6>В этой категории пока нет товаров</h6>
                <button class="btn btn-primary btn-sm" onclick="showAllProducts()">
                    Показать все товары
                </button>
            </div>
        `;return}const o=e.map(s=>{const a=s.main_photo_url||s.photo_url,r=!!a,i=s.isAvailable&&s.quantity>0;return`
        <article class="product-card" data-product-id="${s.id}">
            <div class="product-image ${r?"":"no-image"}">
                ${r?`<img src="${d($(a))}" alt="${d(s.name)}" 
                         onerror="handleImageError(this); this.parentElement.classList.add('no-image');" loading="lazy">
                      ${s.has_multiple_photos?'<div class="position-absolute top-0 start-0 p-1"><span class="badge bg-dark bg-opacity-75"><i class="fas fa-images"></i></span></div>':""}`:""}
                ${he(s)}
            </div>
            <div class="product-info">
                <h3 class="product-name">${d(s.name)}</h3>
                ${s.description?`<p class="product-description">${d(s.description)}</p>`:""}
                <div class="product-footer">
                    <span class="product-price">${d(s.formatted_price||S(s.price))}</span>
                    <button class="add-to-cart ${i?"":"disabled"}" 
                            data-product-id="${s.id}"
                            ${i?"":"disabled"}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </article>
    `}).join("");n.innerHTML=`
        <div class="products-header">
            <h5 id="productsTitle"><i class="fas fa-folder-open me-2"></i>Категория: ${d(t)}</h5>
        </div>
        <div class="products-grid">
            ${o}
        </div>
    `}function X(){q=!1,k=!1;const e=document.getElementById("backButton");e&&(e.style.display="none",e.classList.remove("show"));const t=document.getElementById("searchInput");t&&(t.value=""),document.body.classList.remove("category-view"),window.location.reload()}function S(e){return Number(e).toLocaleString("ru-RU",{minimumFractionDigits:0,maximumFractionDigits:2})}function $(e){return!e||e.startsWith("http://")||e.startsWith("https://")||e.startsWith("/")?e:"/"+e}function Ye(e,t){const n=[];for(let o=0;o<=t.length;o++)n[o]=[o];for(let o=0;o<=e.length;o++)n[0][o]=o;for(let o=1;o<=t.length;o++)for(let s=1;s<=e.length;s++)t.charAt(o-1)===e.charAt(s-1)?n[o][s]=n[o-1][s-1]:n[o][s]=Math.min(n[o-1][s-1]+1,n[o][s-1]+1,n[o-1][s]+1);return n[t.length][e.length]}function N(e,t){const n=e.length>t.length?e:t,o=e.length>t.length?t:e;if(n.length===0)return 100;const s=Ye(n,o),a=(n.length-s)/n.length*100;return Math.max(0,a)}function Y(e,t){const n=e.toLowerCase(),o=t.toLowerCase();if(o.includes(n))return 100;const s=N(n,o),a=o.split(/\s+/);let r=0;a.forEach(c=>{if(c.length>=2){const l=N(n,c);r=Math.max(r,l)}});let i=0;if(n.length>=3)for(let c=0;c<=o.length-n.length;c++){const l=o.substring(c,c+n.length),m=N(n,l);i=Math.max(i,m)}return Math.max(s,r,i)}let x=!1;function te(e){oe(e,1)}function B(){fetch("/cart/count").then(e=>e.json()).then(e=>{const t=document.getElementById("cart-counter"),n=document.getElementById("cart-float");t&&n&&(e.count>0?(t.textContent=e.count,t.classList.remove("hidden"),n.classList.remove("hidden"),t.style.animation="none",setTimeout(()=>{t.style.animation="cart-counter-pulse 2s infinite"},50)):(t.classList.add("hidden"),n.classList.add("hidden")))}).catch(e=>{console.error("Ошибка получения счетчика корзины:",e);const t=document.getElementById("cart-float");t&&t.classList.add("hidden")})}async function ne(e){var t;try{const n=document.getElementById("productModal"),o=document.getElementById("productModalTitle"),s=document.getElementById("productModalBody"),a=document.getElementById("productModalFooter");if(!n||!s||!a){console.error("Элементы модального окна не найдены");return}n.classList.add("show"),n.style.display="block",document.body.style.overflow="hidden",o.textContent="Загрузка...",s.innerHTML=`
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка товара...</div>
            </div>
        `,a.style.display="none";const r=(t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.getAttribute("content");if(!r)throw new Error("Short name не найден");const i=await fetch(`/${r}/api/products/${e}`);if(!i.ok)throw new Error(`HTTP error! status: ${i.status}`);const c=await i.json();window.cachedProductsData||(window.cachedProductsData={}),window.cachedProductsData[e]=c,Qe(c,s,o,a)}catch(n){console.error("Ошибка при загрузке данных товара:",n),u("Ошибка при загрузке данных товара","error")}}function Qe(e,t,n,o){n.textContent="",t.innerHTML=`
        <div class="row g-4">
            ${e.photos_gallery&&e.photos_gallery.length>0?`
                <div class="col-md-6">
                    <div class="product-gallery">
                        <img src="${$(e.photos_gallery[0])}" alt="${e.name}" 
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
                                ${e.photos_gallery.map((s,a)=>`
                                    <img src="${$(s)}" alt="${e.name} ${a+1}" 
                                         class="gallery-thumbnail ${a===0?"active":""}" 
                                         onclick="setGalleryImage(${a})"
                                         data-index="${a}">
                                `).join("")}
                            </div>
                        `:""}
                        
                        ${ie(e)}
                    </div>
                </div>
            `:e.main_photo_url||e.photo_url?`
                <div class="col-md-6">
                    <div class="position-relative">
                        <img src="${$(e.main_photo_url||e.photo_url)}" alt="${e.name}" 
                             class="modal-product-image" 
                             onerror="handleImageError(this);" loading="lazy">
                        <div class="product-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Изображение недоступно</span>
                        </div>
                        ${ie(e)}
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
                    
                    <h4 class="modal-product-name mb-3">${d(e.name)}</h4>
                    
                    <div class="modal-product-price">
                        ${e.formatted_price||S(e.price)}
                    </div>
                    
                    ${e.description?`
                        <div class="modal-product-description">
                            ${e.description}
                        </div>
                    `:""}
                    
                    ${e.specifications?`
                        <div class="modal-product-specifications">
                            <h6>Характеристики</h6>
                            ${typeof e.specifications=="object"&&e.specifications!==null?Object.entries(e.specifications).map(([s,a])=>`<p><strong>${d(s)}:</strong> ${d(a)}</p>`).join(""):`<p>${d(e.specifications)}</p>`}
                        </div>
                    `:""}
                    
                    <div class="availability-info mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #374151; font-weight: 600;">Наличие:</span>
                            <span class="badge ${Ke(e.quantity)}">
                                ${Ze(e.quantity)}
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
    `,e.isAvailable&&e.quantity>0&&setTimeout(()=>{const s=e.price_with_markup||e.price;be(e.id,1,s,e.quantity)},100),setTimeout(()=>{ke()},150)}function ye(){const e=document.getElementById("productPanel"),t=document.getElementById("panelBackdrop"),n=document.getElementById("productPanelFooter");t&&t.classList.remove("show"),e&&e.classList.remove("show"),n&&(n.style.display="none"),document.body.style.overflow="auto"}function he(e){let t="secondary";return e.availability_status==="В наличии"?t="success":e.availability_status==="Заканчивается"?t="warning":e.availability_status==="Нет в наличии"&&(t="danger"),`<span class="badge bg-${t} shadow-sm">${d(e.availability_status||"")}</span>`}function Ue(e,t){const n=document.getElementById(`quantity-${e}`);if(!n)return;const o=parseInt(n.value)||1,s=Math.max(1,Math.min(parseInt(n.max),o+t));n.value=s,ge(e,s),ve(e,s),y("light")}function ze(e){const t=document.getElementById(`quantity-${e}`);if(!t)return;const n=parseInt(t.value),o=parseInt(t.max);isNaN(n)||n<1?t.value=1:n>o&&(t.value=o,u(`Максимальное количество: ${o} шт.`,"warning"));const s=parseInt(t.value);ge(e,s),ve(e,s)}function ge(e,t){const n=window.cachedProductsData?window.cachedProductsData[e]:null;if(n){const o=n.price*t,s=new Intl.NumberFormat("ru-RU",{style:"currency",currency:"RUB"}).format(o),a=document.getElementById(`totalPrice-${e}`);a&&(a.textContent=s)}}function ve(e,t){const n=document.getElementById(`decreaseBtn-${e}`),o=document.getElementById(`increaseBtn-${e}`),s=document.getElementById(`quantity-${e}`);if(n&&(n.disabled=t<=1),o&&s){const a=parseInt(s.max);o.disabled=t>=a}}function oe(e,t){P(!0);const n=new FormData;n.append("_token",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),n.append("quantity",t),fetch(`/cart/add/${e}`,{method:"POST",body:n}).then(o=>o.json()).then(o=>{P(!1),o.success?(u(`Товар добавлен в корзину (${t} шт.)!`),B(),we(),y("success")):(z(!0),u(o.message||"Ошибка при добавлении товара","error"),y("error"))}).catch(o=>{P(!1),z(!0),console.error("Ошибка при добавлении товара в корзину:",o),u("Ошибка при добавлении товара в корзину","error"),y("error")})}function Ve(){Ee()}function y(e="light"){var t;try{const n=(t=window.Telegram)==null?void 0:t.WebApp,o=parseFloat((n==null?void 0:n.version)||"0");n&&o>=6.1&&n.HapticFeedback&&typeof n.HapticFeedback.impactOccurred=="function"&&n.HapticFeedback.impactOccurred(e)}catch{}}function Je(e=!0,t=0){const n=document.getElementById("cart-float"),o=document.getElementById("cart-counter");!n||!o||(e&&t>0?(o.textContent=t,o.classList.remove("hidden"),n.classList.remove("hidden"),o.style.animation="cart-counter-pulse 2s infinite"):(o.classList.add("hidden"),n.classList.add("hidden")))}function we(){const e=document.querySelector(".cart-float-btn"),t=document.querySelector(".cart-float-btn .fa-shopping-cart");e&&t&&(e.style.transform="translateY(-4px) scale(1.1)",e.style.boxShadow="0 12px 32px rgba(16, 185, 129, 0.5)",t.style.transform="rotate(-15deg) scale(1.2)",setTimeout(()=>{e.style.transform="",e.style.boxShadow="",t.style.transform=""},300),y("medium"))}function P(e=!0){const t=document.querySelector(".cart-float-btn");t&&(e?(t.classList.add("loading"),t.setAttribute("aria-busy","true")):(t.classList.remove("loading"),t.setAttribute("aria-busy","false")))}function z(e=!0){const t=document.querySelector(".cart-float-btn");t&&e&&(t.style.background="linear-gradient(135deg, #ef4444 0%, #dc2626 100%)",setTimeout(()=>{t.style.background=""},2e3))}function ie(e){return(e.is_active!==void 0?e.is_active:e.isAvailable)?e.quantity<=0?'<div class="product-status"><span class="status-badge status-out-of-stock">Нет в наличии</span></div>':e.quantity<=5?`<div class="product-status"><span class="status-badge status-limited">Осталось ${e.quantity}</span></div>`:'<div class="product-status"><span class="status-badge status-available">В наличии</span></div>':'<div class="product-status"><span class="status-badge status-inactive">Недоступен</span></div>'}function Ke(e){return"bg-success"}function Ze(e){return e<=0?"Нет в наличии":`${e} шт.`}function et(e,t){var r;const n=document.getElementById(`modal-quantity-${e}`);if(!n)return;const o=parseInt(n.textContent)||1,s=Math.max(1,o+t),a=(r=window.cachedProductsData)==null?void 0:r[e];if(a){const i=Math.min(a.quantity,99),c=Math.min(s,i),l=a.price_with_markup||a.price;be(e,c,l,a.quantity)}}function be(e,t,n,o){const s=document.getElementById(`modal-quantity-${e}`),a=document.getElementById(`modal-decrease-${e}`),r=document.getElementById(`modal-increase-${e}`),i=document.getElementById(`modal-total-price-${e}`);if(s&&(s.textContent=t),a&&(a.disabled=t<=1),r&&(r.disabled=t>=o),i&&n){const c=t*n;i.textContent=`Итого: ${S(c)} ₽`}}function tt(e){const t=document.getElementById(`modal-quantity-${e}`),n=t&&parseInt(t.textContent)||1;oe(e,n);const o=document.getElementById("productModal");if(o){const s=bootstrap.Modal.getInstance(o);s&&s.hide()}}function Ee(){try{const e=document.getElementById("cartModal"),t=document.getElementById("cartModalBody"),n=document.getElementById("cartModalFooter");if(!e||!t){console.error("Элементы модального окна корзины не найдены");return}e.classList.add("show"),e.style.display="block",document.body.style.overflow="hidden",t.innerHTML=`
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Загрузка корзины...</div>
            </div>
        `,n.style.display="none",fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(o=>o.json()).then(o=>{o.success&&o.items&&o.items.length>0?Te(o.items,o.total_amount):(t.innerHTML=`
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
            `,n.style.display="none"})}catch(e){console.error("Ошибка при загрузке корзины:",e),u("Ошибка при загрузке корзины","error")}}function Te(e,t){const n=document.getElementById("cartModalBody"),o=document.getElementById("cartModalFooter");if(!n||!o)return;if(!e||e.length===0){n.innerHTML=`
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Корзина пуста</h5>
                <p class="text-muted">Добавьте товары для оформления заказа</p>
            </div>
        `,o.style.display="none";return}let s='<div class="cart-items">';e.forEach(a=>{s+=`
            <div class="cart-item mb-3 p-3 border rounded"style="flex-direction: column;" data-cart-id="${a.id}">
                <div class="d-flex align-items-start" style="width: 100%; padding-bottom: 10px;">
                    <div class="cart-item-image  flex-shrink-0">
                        ${a.main_photo_url||a.photo_url?`<img src="${a.main_photo_url||a.photo_url}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="${a.name}">`:`<div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>`}
                    </div>
                    
                    <div class="cart-item-info flex-grow-1" >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold">${a.name}</h6>
                                ${a.article?`<small class="text-muted d-block">Артикул: ${a.article}</small>`:""}
                                <div class="text-primary fw-semibold">${a.formatted_price} за шт.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${a.id})" title="Удалить товар">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                       
                    </div>
                </div>
                 <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                            <div class="quantity-controls">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${a.id}, ${a.quantity-1})" ${a.quantity<=1?"disabled":""}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary disabled px-3">${a.quantity} шт</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateCartQuantity(${a.id}, ${a.quantity+1})" ${a.quantity>=a.available_quantity?"disabled":""}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-item-total">
                                <strong class="text-success fs-5">${a.formatted_total}</strong>
                            </div>
                        </div>
            </div>
        `}),s+="</div>",n.innerHTML=s,o.innerHTML=`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100">
            <div class="cart-total" style="width:100%;">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-calculator me-2"></i>
                    Итого: ${S(t)} ₽
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
    `,o.style.display="block"}function nt(e,t){if(t<=0){Ie(e);return}const n=document.querySelector(`[data-cart-id="${e}"]`);if(n){const o=n.querySelector(".quantity-controls");o&&(o.style.opacity="0.5",o.style.pointerEvents="none")}fetch(`/cart/update/${e}`,{method:"PATCH",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"},body:JSON.stringify({quantity:t})}).then(o=>o.json()).then(o=>{o.success?(b("Количество обновлено","success"),B(),L(),y("light")):b(o.message||"Ошибка при обновлении количества","error")}).catch(o=>{console.error("Ошибка при обновлении количества:",o),b("Ошибка при обновлении количества","error")}).finally(()=>{if(n){const o=n.querySelector(".quantity-controls");o&&(o.style.opacity="1",o.style.pointerEvents="auto")}})}function L(){const e=document.getElementById("cartModalBody"),t=document.getElementById("cartModalFooter");!e||!t||fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success&&n.items&&n.items.length>0?Te(n.items,n.total_amount):(e.innerHTML=`
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5>Корзина пуста</h5>
                    <p class="text-muted">Добавьте товары для оформления заказа</p>
                </div>
            `,t.style.display="none")}).catch(n=>{console.error("Ошибка при обновлении корзины:",n)})}function Ie(e){var o;const t=(o=window.Telegram)==null?void 0:o.WebApp,n=parseFloat((t==null?void 0:t.version)||"0");if(t&&n>=6.2&&typeof t.showConfirm=="function")try{t.showConfirm("Удалить товар из корзины?",s=>{s&&re(e)});return}catch(s){console.warn("showConfirm не работает:",s)}confirm("Удалить товар из корзины?")&&re(e)}function re(e){const t=document.querySelector(`[data-cart-id="${e}"]`);t&&(t.style.opacity="0.5",t.style.transform="scale(0.95)",t.style.pointerEvents="none"),fetch(`/cart/remove/${e}`,{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success?(b("Товар удален из корзины","success"),B(),y("medium"),t?(t.style.transition="all 0.3s ease",t.style.opacity="0",t.style.transform="translateX(100%)",setTimeout(()=>{L()},300)):L()):(b(n.message||"Ошибка при удалении товара","error"),t&&(t.style.opacity="1",t.style.transform="scale(1)",t.style.pointerEvents="auto"))}).catch(n=>{console.error("Ошибка при удалении товара:",n),b("Ошибка при удалении товара","error"),t&&(t.style.opacity="1",t.style.transform="scale(1)",t.style.pointerEvents="auto")})}function ot(){var n;const e=(n=window.Telegram)==null?void 0:n.WebApp,t=parseFloat((e==null?void 0:e.version)||"0");if(e&&t>=6.2&&typeof e.showConfirm=="function")try{e.showConfirm("Очистить всю корзину?",o=>{o&&ce()});return}catch(o){console.warn("showConfirm не работает:",o)}confirm("Очистить всю корзину?")&&ce()}function ce(){const e=document.getElementById("cartModalBody"),t=document.getElementById("cartModalFooter");e&&(e.innerHTML=`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Очистка корзины...</span>
                </div>
                <div class="mt-3">Очищаем корзину...</div>
            </div>
        `),t&&(t.style.display="none"),fetch("/cart/clear",{method:"DELETE",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"}}).then(n=>n.json()).then(n=>{n.success?(b("Корзина очищена","success"),B(),y("medium"),e&&(e.innerHTML=`
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Корзина очищена</h5>
                        <p class="text-muted">Добавьте товары для оформления заказа</p>
                    </div>
                `),setTimeout(()=>{const o=document.getElementById("cartModal");if(o){const s=bootstrap.Modal.getInstance(o);s&&s.hide()}},1500)):(b(n.message||"Ошибка при очистке корзины","error"),L())}).catch(n=>{console.error("Ошибка при очистке корзины:",n),b("Ошибка при очистке корзины","error"),L()})}function st(){if(!qe){Ae();return}if(!I){u("Ошибка: данные пользователя недоступны","error");return}if(x){u("Заказ уже обрабатывается...","warning");return}x=!0,u("Проверяем корзину...","info"),fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{if(!e.success||!e.items||e.items.length===0){u("Корзина пуста","warning"),x=!1;return}u("Оформляем заказ...","info");const t={bot_short_name:document.querySelector('meta[name="short-name"]').getAttribute("content"),user_data:I,notes:""};return ee("/cart/checkout",{method:"POST",body:JSON.stringify(t)})}).then(e=>{if(e)return e.json()}).then(e=>{e&&(e.success?(_(),e.mode==="queue"&&e.checkout_session_id?(u("Заказ принят! Обрабатывается...","info"),B(),at(e.checkout_session_id)):(u(`Заказ успешно оформлен! Номер заказа: ${e.order.order_number}`,"success"),B(),y("success")),setTimeout(()=>{const t=document.getElementById("cartModalBody");t&&(t.innerHTML=`
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Корзина пуста</h5>
                            <p class="text-muted">Добавьте товары для оформления заказа</p>
                        </div>
                    `);const n=document.getElementById("cartModalFooter");n&&(n.style.display="none")},1e3),x=!1):(u(e.message||"Ошибка при оформлении заказа","error"),y("error"),x=!1))}).catch(e=>{console.error("Ошибка при оформлении заказа:",e),u("Произошла ошибка при оформлении заказа","error"),y("error"),x=!1})}function at(e){let t=0;const n=60,o=setInterval(()=>{t++,fetch("/cart/checkout-status",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"X-Requested-With":"XMLHttpRequest"},body:JSON.stringify({checkout_session_id:e})}).then(s=>s.json()).then(s=>{s.success&&s.status==="completed"&&s.order?(clearInterval(o),u(`✅ Заказ оформлен! Номер: ${s.order.order_number}`,"success"),y("success")):s.status==="failed"?(clearInterval(o),u(`❌ Ошибка: ${s.error||"Не удалось оформить заказ"}`,"error"),y("error")):s.status==="processing"?u("⏳ Заказ обрабатывается...","info"):s.status==="pending"&&t%5===0&&u("⏳ Заказ в очереди на обработку...","info"),t>=n&&(clearInterval(o),u("⚠️ Заказ принят, но обработка занимает больше времени. Проверьте позже.","warning"))}).catch(s=>{console.error("Ошибка проверки статуса заказа:",s),t>=n&&(clearInterval(o),u("⚠️ Не удалось проверить статус заказа. Проверьте историю заказов позже.","warning"))})},1e3)}function se(){try{const e=document.getElementById("productModal");e&&(e.classList.remove("show"),e.style.display="none"),document.body.style.overflow="",y("light")}catch(e){console.error("Ошибка при закрытии модального окна товара:",e),document.body.style.overflow=""}}function _(){try{const e=document.getElementById("cartModal");e&&(e.classList.remove("show"),e.style.display="none"),document.body.style.overflow="",y("light")}catch(e){console.error("Ошибка при закрытии модального окна корзины:",e),document.body.style.overflow=""}}function xe(){if(localStorage.getItem("hasSeenSwipeHint"))return;const t=document.createElement("div");t.className="swipe-indicator",t.innerHTML=`
        <span class="arrow">→</span>
        <span>Свайп для выхода</span>
    `,document.body.appendChild(t),setTimeout(()=>{t.parentNode&&t.remove(),localStorage.setItem("hasSeenSwipeHint","true")},3e3)}function $e(){if(window.Telegram&&window.Telegram.WebApp){const e=window.Telegram.WebApp;try{if(e.expand(),e.version&&parseFloat(e.version)>=6.1&&e.disableClosingConfirmation)try{e.disableClosingConfirmation()}catch{}e.isClosingConfirmationEnabled!==void 0&&(e.isClosingConfirmationEnabled=!1),e.setViewportHeight&&e.setViewportHeight(window.innerHeight)}catch(t){console.error("Ошибка при настройке поведения скролла:",t)}}document.body.style.touchAction="pan-x pan-y",document.documentElement.style.touchAction="pan-x pan-y"}function Me(){let e=0,t=0,n=!1;const o=10;function s(i){if(i.touches.length!==1)return;const c=i.touches[0];e=c.clientY,t=c.clientX,n=!1,(document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&(n=!0)}function a(i){var h,g;if(i.touches.length!==1||!n)return;const c=i.touches[0],l=c.clientY-e,m=Math.abs(c.clientX-t);if((document.documentElement.scrollTop||document.body.scrollTop||window.pageYOffset)<=5&&l>o&&m<50)return i.preventDefault(),i.stopPropagation(),i.stopImmediatePropagation(),(g=(h=window.Telegram)==null?void 0:h.WebApp)!=null&&g.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("rigid"),!1}function r(i){n=!1}document.addEventListener("touchstart",s,{passive:!1,capture:!0}),document.addEventListener("touchmove",a,{passive:!1,capture:!0}),document.addEventListener("touchend",r,{passive:!0,capture:!0}),document.body.style.overscrollBehavior="none",document.documentElement.style.overscrollBehavior="none",window.addEventListener("beforeunload",function(i){if(i.clientY<50)return i.preventDefault(),!1})}function Ce(){let e=0,t=0,n=!1;function o(i){if(i.touches.length!==1)return;const c=i.touches[0];e=c.clientX,t=c.clientY,n=!0}function s(i){if(!n||i.touches.length!==1)return;const c=i.touches[0],l=c.clientX-e,m=c.clientY-t;if(Math.abs(l)>Math.abs(m)&&l>50&&Math.abs(m)<100){const p=document.querySelector(".modal.show");p&&(p.id==="productModal"?se():p.id==="cartModal"&&_()),n=!1}}function a(){n=!1}[document.getElementById("productModal"),document.getElementById("cartModal")].forEach(i=>{i&&(i.addEventListener("touchstart",o,{passive:!0}),i.addEventListener("touchmove",s,{passive:!0}),i.addEventListener("touchend",a,{passive:!0}))})}function Be(){let e=0,t=0,n=!1;const o=100,s=50;function a(l){if(l.touches.length!==1)return;const m=l.touches[0];e=m.clientX,t=m.clientY,e<=s&&(n=!0)}function r(l){var g,M,E,f;if(!n||l.touches.length!==1)return;const m=l.touches[0],p=m.clientX-e,h=m.clientY-t;if(Math.abs(p)>Math.abs(h)&&p>o&&Math.abs(h)<100){if(k){(M=(g=window.Telegram)==null?void 0:g.WebApp)!=null&&M.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),X(),n=!1;return}if(q){(f=(E=window.Telegram)==null?void 0:E.WebApp)!=null&&f.HapticFeedback&&window.Telegram.WebApp.HapticFeedback.impactOccurred("light"),X(),n=!1;return}n=!1}}function i(){n=!1}const c=document.getElementById("app");c&&(c.addEventListener("touchstart",a,{passive:!0}),c.addEventListener("touchmove",r,{passive:!0}),c.addEventListener("touchend",i,{passive:!0}))}function Se(){const e=document.getElementById("backButton");e&&(k&&window.innerWidth>768?(e.style.display="flex",e.classList.add("show")):(e.style.display="none",e.classList.remove("show")))}window.initApp=fe;window.showAlert=u;window.filterByCategory=W;window.showAllProducts=X;window.performSearch=U;window.addToCart=te;window.addToCartWithQuantity=oe;window.showProductDetails=ne;window.showCart=Ve;window.showCartModal=Ee;window.closePanel=ye;window.changeQuantityModal=et;window.addToCartFromModal=tt;window.changeQuantity=Ue;window.validateQuantity=ze;window.updateCartQuantity=nt;window.removeFromCart=Ie;window.clearCart=ot;window.proceedToCheckout=st;window.refreshCartContent=L;window.closeProductModal=se;window.closeCartModal=_;window.showSwipeHint=xe;window.setupScrollBehavior=$e;window.preventPullToClose=Me;window.addSwipeSupport=Ce;window.addCategorySwipeSupport=Be;window.toggleCartFloat=Je;window.animateCartButtonOnAdd=we;window.setCartButtonLoading=P;window.setCartButtonError=z;window.handleBackButtonVisibility=Se;window.setGalleryImage=H;window.previousGalleryImage=V;window.nextGalleryImage=J;window.openGalleryFullscreen=rt;window.closeGalleryFullscreen=ct;window.previousFullscreenImage=K;window.nextFullscreenImage=Z;window.initGallerySwipe=ke;window.setupCategoryLazyLoading=pe;function ke(){const e=document.querySelector(".product-gallery"),t=document.getElementById("gallery-fullscreen");e&&le(e,!1),t&&le(t,!0)}function le(e,t=!1){let n=0,o=0,s=!1,a=0;const r=50,i=300;let c=!1;const l=t?e.querySelector(".gallery-fullscreen-image"):e.querySelector(".gallery-main-image");if(!l)return;l.style.cursor="grab",l.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",l.addEventListener("touchstart",m,{passive:!1}),l.addEventListener("touchmove",p,{passive:!1}),l.addEventListener("touchend",h,{passive:!1}),l.addEventListener("mousedown",g,{passive:!1}),document.addEventListener("mousemove",M,{passive:!1}),document.addEventListener("mouseup",E,{passive:!1});function m(f){const w=f.touches[0];n=w.clientX,o=w.clientY,w.clientX,s=!0,a=Date.now(),l.style.transition="none"}function p(f){if(!s)return;const w=f.touches[0];w.clientX;const T=Math.abs(w.clientY-o),v=w.clientX-n;if(Math.abs(v)>T&&Math.abs(v)>10){f.preventDefault();const A=v*.3;l.style.transform=`translateX(${A}px)`}}function h(f){if(!s)return;const w=f.changedTouches[0].clientX,T=f.changedTouches[0].clientY,v=w-n,A=Math.abs(T-o),j=Date.now()-a;l.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",l.style.transform="translateX(0)",s=!1,Math.abs(v)>A&&(Math.abs(v)>r||j<i&&Math.abs(v)>30)&&setTimeout(()=>{v>0?(t?K():V(),y("light")):(t?Z():J(),y("light"))},100)}function g(f){f.target.closest("button")||f.target.closest(".gallery-thumbnail")||(c=!0,n=f.clientX,o=f.clientY,f.clientX,s=!1,a=Date.now(),l.style.cursor="grabbing",l.style.transition="none",f.preventDefault())}function M(f){if(!c)return;f.clientX;const w=Math.abs(f.clientY-o),T=f.clientX-n;if((Math.abs(T)>5||w>5)&&(s=!0),Math.abs(T)>w&&Math.abs(T)>10){f.preventDefault();const v=T*.3;l.style.transform=`translateX(${v}px)`}}function E(f){if(!c)return;const w=f.clientX,T=f.clientY,v=w-n,A=Math.abs(T-o),j=Date.now()-a;c=!1,l.style.cursor="grab",l.style.transition="transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)",l.style.transform="translateX(0)",s&&Math.abs(v)>A&&(Math.abs(v)>r||j<i&&Math.abs(v)>30)&&(setTimeout(()=>{v>0?(t?K():V(),y("light")):(t?Z():J(),y("light"))},100),f.preventDefault()),s=!1}}function H(e){if(!window.currentGallery||!window.currentGallery.photos||e<0||e>=window.currentGallery.photos.length)return;window.currentGallery.currentIndex=e;const t=document.getElementById("main-gallery-image"),n=document.getElementById("gallery-current"),o=document.querySelectorAll(".gallery-thumbnail");t&&(t.style.opacity="0",t.style.transform="scale(0.95)",setTimeout(()=>{t.src=$(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1)"},50)},150)),n&&(n.textContent=e+1),o.forEach((s,a)=>{a===e?s.classList.add("active"):s.classList.remove("active")}),it()}function V(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.max(0,window.currentGallery.currentIndex-1);H(e)}function J(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.min(window.currentGallery.photos.length-1,window.currentGallery.currentIndex+1);H(e)}function it(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=document.getElementById("gallery-prev"),t=document.getElementById("gallery-next");e&&(e.disabled=window.currentGallery.currentIndex===0),t&&(t.disabled=window.currentGallery.currentIndex===window.currentGallery.photos.length-1)}function rt(e=null){if(!window.currentGallery||!window.currentGallery.photos)return;e!==null&&(window.currentGallery.currentIndex=e);const t=document.getElementById("gallery-fullscreen"),n=document.getElementById("fullscreen-image");t&&n&&(n.src=$(window.currentGallery.photos[window.currentGallery.currentIndex]),t.classList.add("show"),document.body.style.overflow="hidden")}function ct(){const e=document.getElementById("gallery-fullscreen");e&&(e.classList.remove("show"),document.body.style.overflow="auto")}function K(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.max(0,window.currentGallery.currentIndex-1);if(e===window.currentGallery.currentIndex)return;window.currentGallery.currentIndex=e;const t=document.getElementById("fullscreen-image");t&&(t.style.opacity="0",t.style.transform="scale(0.95) translateX(0)",setTimeout(()=>{t.src=$(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1) translateX(0)"},50)},150)),H(e)}function Z(){if(!window.currentGallery||!window.currentGallery.photos)return;const e=Math.min(window.currentGallery.photos.length-1,window.currentGallery.currentIndex+1);if(e===window.currentGallery.currentIndex)return;window.currentGallery.currentIndex=e;const t=document.getElementById("fullscreen-image");t&&(t.style.opacity="0",t.style.transform="scale(0.95) translateX(0)",setTimeout(()=>{t.src=$(window.currentGallery.photos[e]),setTimeout(()=>{t.style.opacity="1",t.style.transform="scale(1) translateX(0)"},50)},150)),H(e)}let F=!1,G=1,C=!0;function lt(){const e=document.getElementById("infiniteScrollTrigger");if(!e)return;G=parseInt(e.getAttribute("data-next-page"))||2,C=e.getAttribute("data-has-more")==="true",new IntersectionObserver(n=>{n.forEach(o=>{o.isIntersecting&&C&&!F&&dt()})},{root:null,rootMargin:"200px",threshold:.1}).observe(e)}async function dt(){var t;if(F||!C)return;F=!0;const e=document.getElementById("infiniteScrollLoader");e&&(e.style.display="block");try{const n=((t=document.querySelector('meta[name="short-name"]'))==null?void 0:t.content)||window.location.pathname.split("/")[1],o=new URL(`/${n}`,window.location.origin);o.searchParams.set("page",G);const s=await fetch(o.toString(),{headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"}});if(!s.ok)throw new Error(`HTTP error! status: ${s.status}`);const a=await s.text(),i=new DOMParser().parseFromString(a,"text/html"),c=i.querySelectorAll(".product-card");if(c.length===0){C=!1;const p=document.getElementById("infiniteScrollTrigger");p&&p.remove(),e&&(e.innerHTML=`
                    <div style="text-align: center; padding: 20px; color: #888;">
                        <i class="fas fa-check-circle"></i> Все товары загружены
                    </div>
                `,setTimeout(()=>{e.style.display="none"},2e3));return}const l=document.querySelector(".products-grid");l&&c.forEach(p=>{const h=p.cloneNode(!0),g=h.querySelector(".add-to-cart");g&&!g.disabled&&g.addEventListener("click",function(M){M.stopPropagation();const E=parseInt(this.getAttribute("data-product-id"));E&&te(E)}),h.addEventListener("click",function(M){if(!M.target.closest(".add-to-cart")){const E=parseInt(this.getAttribute("data-product-id"));E&&ne(E)}}),h.style.opacity="0",h.style.transform="translateY(20px)",l.appendChild(h),setTimeout(()=>{h.style.transition="opacity 0.3s ease, transform 0.3s ease",h.style.opacity="1",h.style.transform="translateY(0)"},50)});const m=i.getElementById("infiniteScrollTrigger");m?(G=parseInt(m.getAttribute("data-next-page"))||G+1,C=m.getAttribute("data-has-more")==="true"):C=!1}catch(n){console.error("Ошибка загрузки товаров:",n),C=!1,e&&(e.innerHTML=`
                <div style="text-align: center; padding: 20px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i> Ошибка загрузки товаров
                </div>
            `,setTimeout(()=>{e.style.display="none"},3e3))}finally{F=!1,e&&C&&(e.style.display="none")}}function Le(){const e=document.getElementById("productModal"),t=document.getElementById("cartModal");e&&e.addEventListener("click",function(n){n.target===e&&se()}),t&&t.addEventListener("click",function(n){n.target===t&&_()}),document.addEventListener("click",function(n){const o=n.target.closest(".product-card");if(o&&!n.target.closest(".add-to-cart")){const r=parseInt(o.getAttribute("data-product-id"));r&&ne(r)}const s=n.target.closest(".add-to-cart");if(s&&!s.disabled){n.stopPropagation();const r=parseInt(s.getAttribute("data-product-id"));r&&te(r)}const a=n.target.closest(".category-card");if(a){const r=parseInt(a.getAttribute("data-category-id")),i=a.getAttribute("data-category-name");r&&i&&W(r,i)}}),Ce(),Be()}window.setupModalBackdropHandlers=Le;document.addEventListener("DOMContentLoaded",function(){Xe();const e=document.getElementById("loading"),t=document.getElementById("app");setTimeout(()=>{e&&(e.style.display="none"),t&&(t.style.display="block")},2e3);try{window.isAppInitializedByBlade||fe()}catch(n){console.error("Критическая ошибка инициализации:",n),e&&(e.style.display="none"),t&&(t.style.display="block")}setTimeout(()=>{try{B()}catch(n){console.error("Ошибка обновления счетчика корзины:",n)}},1e3),setTimeout(()=>{try{lt()}catch(n){console.error("Ошибка инициализации infinite scroll:",n)}},1500),document.addEventListener("keydown",function(n){n.key==="Escape"&&ye()}),Me()});async function Ae(){const e=document.getElementById("webCheckoutModal"),t=document.getElementById("webCheckoutModalBody"),n=document.getElementById("webCheckoutItems"),o=document.getElementById("webCheckoutTotal");if(!e||!t||!n||!o){u("Ошибка: элементы формы не найдены","error");return}e.classList.add("show"),document.body.style.overflow="hidden";try{const a=await(await fetch("/cart",{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest"}})).json();if(!a.success||!a.items||a.items.length===0){u("Корзина пуста","warning"),D();return}n.innerHTML=a.items.map(i=>{var c,l,m,p;return`
            <div class="checkout-item">
                <img 
                    src="${((c=i.product)==null?void 0:c.main_photo_url)||((l=i.product)==null?void 0:l.photo_url)||"/images/placeholder.png"}" 
                    alt="${d(((m=i.product)==null?void 0:m.name)||"Товар")}"
                    class="checkout-item-image"
                    onerror="this.src='/images/placeholder.png'"
                >
                <div class="checkout-item-details">
                    <div class="checkout-item-name">${d(((p=i.product)==null?void 0:p.name)||"Товар")}</div>
                    <div class="checkout-item-quantity">${i.quantity} шт.</div>
                </div>
                <div class="checkout-item-price">${S(i.total_price)} ₽</div>
            </div>
        `}).join(""),o.textContent=`${S(a.total_amount)} ₽`;const r=document.getElementById("webCheckoutForm");r&&(r.reset(),r.querySelectorAll(".form-control").forEach(i=>{i.classList.remove("is-invalid")}))}catch(s){console.error("Ошибка загрузки данных корзины:",s),u("Ошибка загрузки данных корзины","error"),D()}}function D(){const e=document.getElementById("webCheckoutModal");e&&(e.classList.remove("show"),document.body.style.overflow="")}function ut(){if(!document.getElementById("webCheckoutForm"))return!1;let t=!0;const n=document.getElementById("customerName");n&&(n.value.trim().length<2?(n.classList.add("is-invalid"),t=!1):n.classList.remove("is-invalid"));const o=document.getElementById("customerPhone");if(o){const s=o.value.trim();/^[\+]?[0-9]{10,15}$/.test(s)?o.classList.remove("is-invalid"):(o.classList.add("is-invalid"),t=!1)}return t}async function mt(){if(!ut()){u("Пожалуйста, заполните все обязательные поля корректно","error");return}if(x){u("Заказ уже обрабатывается...","warning");return}x=!0;const e=document.getElementById("customerName"),t=document.getElementById("customerPhone"),n=document.getElementById("customerComment"),o={name:(e==null?void 0:e.value.trim())||"",phone:(t==null?void 0:t.value.trim())||"",comment:(n==null?void 0:n.value.trim())||""},s=document.getElementById("submitWebOrderBtn");s&&(s.disabled=!0,s.innerHTML='<i class="fas fa-spinner fa-spin"></i> Отправка...');try{const a=document.querySelector('meta[name="short-name"]'),r=a?a.getAttribute("content"):"",c=await(await ee("/cart/web-checkout",{method:"POST",body:JSON.stringify({bot_short_name:r,customer_name:o.name,customer_phone:o.phone,customer_comment:o.comment})})).json();c.success?(D(),u("Заказ успешно отправлен! Мы свяжемся с вами в ближайшее время.","success"),B(),_()):u(c.message||"Ошибка при отправке заказа","error")}catch(a){console.error("Ошибка отправки веб-заказа:",a),u("Произошла ошибка при отправке заказа. Попробуйте позже.","error")}finally{x=!1,s&&(s.disabled=!1,s.innerHTML='<i class="fas fa-paper-plane"></i> Отправить заказ')}}window.showWebCheckoutModal=Ae;window.closeWebCheckoutModal=D;window.submitWebOrder=mt;
