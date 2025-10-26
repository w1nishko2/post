# Оптимизация импорта и устранение проблем

## Проблемы, выявленные из логов:

### 1. ❌ Ошибка `session:clear` command not found
**Причина:** Cron на хостинге пытается выполнить несуществующую команду `session:clear`.

**Решение:** Исправлено в `app/Console/Kernel.php` - заменено на `app:clear-sessions`.

### 2. ⏱️ Превышение времени выполнения (Timeout)
**Причина:** Импорт с загрузкой изображений из Яндекс.Диска занимает много времени.

**Решения:**
- ✅ Увеличен `timeout` в Job с 120 до 180 секунд
- ✅ Добавлено увеличение `max_execution_time` в PHP через `set_time_limit(600)` в конструкторе импорта
- ✅ Увеличен лимит памяти до 512M

### 3. 🚦 Перегрузка очереди
**Причина:** Jobs создаются быстрее, чем обрабатываются.

**Решения:**
- ✅ Увеличена задержка между Jobs с 2 до 5 секунд для товаров
- ✅ Увеличена задержка для категорий с 1 до 3 секунд

## Настройки хостинга

### PHP настройки (в `.htaccess` или `php.ini`):

```apache
# В файле .htaccess (если поддерживается)
php_value max_execution_time 600
php_value max_input_time 600
php_value memory_limit 512M
php_value post_max_size 64M
php_value upload_max_filesize 64M
```

### Настройка CRON на хостинге:

**Старая (неправильная) настройка:**
```bash
php artisan session:clear  # ❌ Эта команда не существует
```

**Правильная настройка:**
```bash
# Запуск планировщика Laravel каждую минуту
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan schedule:run >> /dev/null 2>&1

# Запуск обработчика очереди (если используется database queue)
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan queue:work --stop-when-empty --max-time=300 >> /dev/null 2>&1
```

### Проверка очереди:

```bash
# Посмотреть задачи в очереди
php artisan queue:monitor

# Запустить обработчик очереди вручную
php artisan queue:work --tries=3 --timeout=300

# Очистить failed jobs
php artisan queue:flush
```

## Рекомендации по импорту

### Для больших файлов (более 50 товаров):

1. **Разбивайте импорт на части** - импортируйте по 20-30 товаров за раз
2. **Проверяйте статус очереди** перед новым импортом
3. **Не запускайте несколько импортов одновременно**

### Проверка статуса загрузки изображений:

```php
// В консоли Laravel Tinker
php artisan tinker

// Проверить товары с незавершённой загрузкой
Product::whereIn('images_download_status', ['pending', 'processing'])->count();

// Товары с ошибками
Product::where('images_download_status', 'failed')->get(['id', 'name', 'images_download_error']);
```

### Мониторинг Jobs:

```bash
# Посмотреть failed jobs
php artisan queue:failed

# Перезапустить failed jobs
php artisan queue:retry all

# Очистить failed jobs
php artisan queue:flush
```

## Оптимизации в коде

### ✅ Внесённые изменения:

1. **ProductsImport.php:**
   - Увеличен `max_execution_time` до 600 секунд (10 минут)
   - Увеличен `memory_limit` до 512M
   - Увеличена задержка между Jobs с 2 до 5 секунд

2. **DownloadProductImagesJob.php:**
   - Увеличен `timeout` с 120 до 180 секунд
   - Добавлено `maxExceptions = 3`
   - Добавлено увеличение `max_execution_time` в методе `handle()`

3. **DownloadCategoryPhotoJob.php:**
   - Увеличен `timeout` с 120 до 180 секунд
   - Добавлено `maxExceptions = 3`
   - Добавлено увеличение `max_execution_time` в методе `handle()`

4. **Console/Kernel.php:**
   - Исправлена команда `session:clear` на `app:clear-sessions`
   - Добавлено ежедневное выполнение очистки сессий

## Мониторинг и дебаг

### Просмотр логов на хостинге:

```bash
# Последние ошибки
tail -n 100 storage/logs/laravel.log

# Логи в реальном времени
tail -f storage/logs/laravel.log

# Поиск конкретных ошибок
grep "ERROR" storage/logs/laravel.log

# Поиск по дате
grep "2025-10-26" storage/logs/laravel.log
```

### Проверка размера очереди:

```bash
# Для database queue
php artisan tinker
DB::table('jobs')->count();

# Для Redis queue (если используется)
redis-cli
LLEN queues:default
```

## Контрольный список перед импортом

- [ ] Проверить, что очередь пустая (`php artisan queue:monitor`)
- [ ] Убедиться, что queue:work запущен
- [ ] Проверить логи на наличие критических ошибок
- [ ] Убедиться, что хватает места на диске
- [ ] Разбить большой файл на части (если > 50 товаров)

## В случае зависания импорта

1. **Проверить процессы:**
   ```bash
   ps aux | grep artisan
   ```

2. **Перезапустить queue worker:**
   ```bash
   php artisan queue:restart
   ```

3. **Проверить failed jobs:**
   ```bash
   php artisan queue:failed
   ```

4. **Очистить и перезапустить:**
   ```bash
   php artisan queue:flush
   php artisan cache:clear
   php artisan config:clear
   ```

## Контакты для поддержки

Если проблемы сохраняются, предоставьте:
- Последние 200 строк из `storage/logs/laravel.log`
- Вывод команды `php artisan queue:failed`
- Информацию о настройках PHP (`php artisan tinker` -> `phpinfo();`)
