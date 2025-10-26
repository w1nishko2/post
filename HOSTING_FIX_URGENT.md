# СРОЧНО: Исправления для хостинга

## 1. Исправить CRON задачу

**Текущая (неправильная):**
```
php artisan session:clear
```

**Заменить на:**
```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan schedule:run >> /dev/null 2>&1
```

## 2. Добавить обработчик очереди в CRON

**Добавить вторую задачу (БЕЗ ОГРАНИЧЕНИЙ ВРЕМЕНИ):**
```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan queue:work database --stop-when-empty --tries=3 --timeout=0 >> /dev/null 2>&1
```

⚠️ **ВАЖНО:** `--timeout=0` означает БЕЗ ОГРАНИЧЕНИЙ по времени!

## 3. Загрузить обновлённые файлы

Обновить на хостинге следующие файлы:
- `app/Console/Kernel.php`
- `app/Imports/ProductsImport.php`
- `app/Jobs/DownloadProductImagesJob.php`
- `app/Jobs/DownloadCategoryPhotoJob.php`
- `config/queue.php` ⭐ **НОВЫЙ**
- `public/.htaccess` ⭐ **ОБНОВЛЁН**
- `public/.user.ini` ⭐ **НОВЫЙ**

## 4. Проверить настройки PHP (КРИТИЧНО!)

### Вариант A: Через .htaccess (если поддерживается)

Файл `public/.htaccess` уже содержит настройки:
```apache
php_value max_execution_time 0
php_value max_input_time 0
php_value memory_limit -1
```

✅ **Просто загрузите обновлённый файл!**

### Вариант Б: Через .user.ini (для FastCGI/PHP-FPM)

Файл `public/.user.ini` уже создан:
```ini
max_execution_time = 0
max_input_time = 0
memory_limit = -1
```

✅ **Загрузите этот файл в папку public_html!**

### Вариант В: Через php.ini (если есть доступ)

Найдите в панели хостинга "PHP настройки" или "php.ini" и установите:
```ini
max_execution_time = 0
max_input_time = 0  
memory_limit = -1
```

⚠️ **На некоторых хостингах `-1` не работает, тогда используйте максимальное значение: `999999`**

## 5. После загрузки выполнить команды

```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

## 6. Проверить что импорт работает

```bash
# Проверить очередь
php artisan queue:monitor

# Если есть failed jobs - перезапустить
php artisan queue:retry all

# Проверить что timeout = 0
php -r "echo ini_get('max_execution_time');"
# Должно быть: 0 или очень большое число
```

## Что было исправлено:

✅ Убрана несуществующая команда `session:clear`
✅ **TIMEOUT полностью убран (0 = без ограничений)**
✅ **Memory limit убран (-1 = без ограничений)**
✅ **retry_after в очереди = 0 (без ограничений)**
✅ Добавлено `$failOnTimeout = false` в Jobs
✅ Добавлен сброс таймера на каждой итерации
✅ Добавлена очистка памяти каждые 10 строк
✅ Настроен `.htaccess` и `.user.ini` с правильными параметрами

## Почему импорт прерывался:

1. **Команда session:clear не существует** - вызывала ошибку в CRON
2. **Жёсткий timeout 120-180 секунд** - Jobs прерывались раньше завершения ❌ **ИСПРАВЛЕНО**
3. **Очередь не обрабатывалась** - Jobs накапливались, но не выполнялись
4. **Малая память** - не хватало для обработки изображений ❌ **ИСПРАВЛЕНО**
5. **PHP ограничения на хостинге** - убраны через .htaccess/.user.ini ❌ **ИСПРАВЛЕНО**

Теперь импорт будет работать **БЕЗ КАКИХ-ЛИБО ОГРАНИЧЕНИЙ ПО ВРЕМЕНИ!** 🚀
