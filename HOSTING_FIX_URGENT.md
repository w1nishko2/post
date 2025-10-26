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

**Добавить вторую задачу:**
```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan queue:work database --stop-when-empty --max-time=300 --tries=3 --timeout=180 >> /dev/null 2>&1
```

## 3. Загрузить обновлённые файлы

Обновить на хостинге следующие файлы:
- `app/Console/Kernel.php`
- `app/Imports/ProductsImport.php`
- `app/Jobs/DownloadProductImagesJob.php`
- `app/Jobs/DownloadCategoryPhotoJob.php`

## 4. Проверить настройки PHP

В файле `.htaccess` добавить (если нет):
```apache
php_value max_execution_time 600
php_value memory_limit 512M
php_value post_max_size 64M
php_value upload_max_filesize 64M
```

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
```

## Что было исправлено:

✅ Убрана несуществующая команда `session:clear`
✅ Увеличен timeout для Jobs до 180 секунд
✅ Увеличен лимит времени для импорта до 600 секунд
✅ Увеличен лимит памяти до 512M
✅ Увеличена задержка между Jobs (5 сек вместо 2)
✅ Добавлена обработка ошибок и повторные попытки

## Почему импорт прерывался:

1. **Команда session:clear не существует** - вызывала ошибку в CRON
2. **Нехватка времени** - импорт с Яндекс.Диска долгий (5-10 сек на фото)
3. **Очередь не обрабатывалась** - Jobs накапливались, но не выполнялись
4. **Малый timeout** - Jobs прерывались раньше завершения

Теперь всё должно работать стабильно!
