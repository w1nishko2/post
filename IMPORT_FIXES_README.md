# 🔧 Исправления импорта товаров - 26 октября 2025

## 📋 Проблема

Импорт товаров с Яндекс.Диска прерывался в процессе обработки. Анализ логов выявил несколько критических проблем:

### Выявленные проблемы:

1. ❌ **Ошибка CRON:** Попытка выполнить несуществующую команду `session:clear`
2. ⏱️ **Timeout:** Превышение времени выполнения при загрузке изображений
3. 🚦 **Перегрузка очереди:** Jobs создавались быстрее, чем обрабатывались
4. 💾 **Нехватка памяти:** Возможное превышение лимита памяти PHP

---

## ✅ Внесённые исправления

### 1. Исправлен Console/Kernel.php

**Файл:** `app/Console/Kernel.php`

**Изменения:**
- ❌ Убрана несуществующая команда `session:clear`
- ✅ Добавлена правильная команда `app:clear-sessions` с запуском раз в день

```php
// Очистка старых сессий (вместо несуществующей session:clear)
$schedule->command('app:clear-sessions')
         ->daily()
         ->withoutOverlapping();
```

---

### 2. Оптимизирован ProductsImport.php

**Файл:** `app/Imports/ProductsImport.php`

**Изменения:**
- ⏱️ Увеличен `max_execution_time` до 600 секунд (10 минут)
- 💾 Увеличен `memory_limit` до 512M
- 🚦 Увеличена задержка между Jobs с 2 до 5 секунд для товаров
- 🚦 Увеличена задержка для категорий с 1 до 3 секунд

```php
// Увеличиваем лимиты PHP для импорта
@set_time_limit(600); // 10 минут
@ini_set('max_execution_time', '600');
@ini_set('memory_limit', '512M');
```

---

### 3. Увеличены таймауты в Jobs

**Файлы:**
- `app/Jobs/DownloadProductImagesJob.php`
- `app/Jobs/DownloadCategoryPhotoJob.php`

**Изменения:**
- ⏱️ Увеличен `timeout` с 120 до 180 секунд
- 🔄 Добавлено свойство `maxExceptions = 3`
- ⚡ Добавлено динамическое увеличение `max_execution_time` в методе `handle()`

```php
public $timeout = 180; // Было 120
public $maxExceptions = 3; // Новое

// В методе handle()
@set_time_limit(180);
@ini_set('max_execution_time', '180');
```

---

### 4. Добавлены новые команды для мониторинга

#### 📊 Команда `import:status`

**Файл:** `app/Console/Commands/ImportStatus.php`

Показывает полную статистику импорта:
- Количество товаров по статусам загрузки
- Товары с ошибками
- Статус очереди
- Статистика по категориям

**Использование:**
```bash
# Общая статистика
php artisan import:status

# Детальная информация
php artisan import:status --detailed

# Для конкретного бота
php artisan import:status --bot=3
```

#### 🧹 Команда `import:cleanup`

**Файл:** `app/Console/Commands/ImportCleanup.php`

Очищает зависшие импорты и перезапускает задачи:
- Сброс зависших статусов "pending"
- Сброс зависших статусов "processing"
- Перезапуск провалившихся Jobs

**Использование:**
```bash
# Полная очистка
php artisan import:cleanup --all

# Только сброс pending
php artisan import:cleanup --reset-pending

# Только перезапуск failed jobs
php artisan import:cleanup --retry-failed
```

---

## 📁 Созданные файлы документации

### 1. IMPORT_OPTIMIZATION.md
Полное описание всех оптимизаций и настроек

### 2. HOSTING_FIX_URGENT.md
Краткая инструкция для срочного исправления на хостинге

### 3. HOSTING_CRON_SETUP.md (обновлён)
Добавлено предупреждение об ошибке `session:clear` и инструкции по исправлению

---

## 🚀 Что нужно сделать на хостинге

### 1. Исправить CRON задачи

**❌ Удалить неправильную задачу:**
```bash
php artisan session:clear
```

**✅ Добавить правильные задачи:**

```bash
# Laravel Scheduler (каждую минуту)
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker (каждую минуту с ограничением времени)
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan queue:work database --stop-when-empty --max-time=300 --tries=3 --timeout=180 >> /dev/null 2>&1
```

---

### 2. Загрузить обновлённые файлы

Загрузите на хостинг следующие файлы:
- ✅ `app/Console/Kernel.php`
- ✅ `app/Imports/ProductsImport.php`
- ✅ `app/Jobs/DownloadProductImagesJob.php`
- ✅ `app/Jobs/DownloadCategoryPhotoJob.php`
- ✅ `app/Console/Commands/ImportStatus.php` (новый)
- ✅ `app/Console/Commands/ImportCleanup.php` (новый)

---

### 3. Очистить кеш

После загрузки выполните:
```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

---

### 4. Проверить настройки PHP

Добавьте в `.htaccess` (если поддерживается):
```apache
php_value max_execution_time 600
php_value memory_limit 512M
php_value post_max_size 64M
php_value upload_max_filesize 64M
```

---

## 🔍 Проверка работоспособности

### 1. Проверить статус импорта
```bash
php artisan import:status
```

Вы должны увидеть статистику по всем товарам и очереди.

### 2. Проверить очередь
```bash
# Количество задач в очереди
php artisan queue:monitor

# Провалившиеся задачи
php artisan queue:failed
```

### 3. Проверить логи
```bash
tail -n 100 storage/logs/laravel.log
```

Не должно быть ошибок `session:clear not found`.

---

## 💡 Рекомендации по импорту

### Для больших файлов (более 50 товаров):

1. **Разбивайте на части** - импортируйте по 20-30 товаров за раз
2. **Проверяйте очередь** перед новым импортом:
   ```bash
   php artisan import:status
   ```
3. **Не запускайте несколько импортов** одновременно
4. **Следите за логами** на предмет ошибок

### Оптимальные параметры импорта:

- ✅ Загрузка изображений: **Включена** (если очередь работает)
- ✅ Обновление существующих: **По необходимости**
- ✅ Размер файла: **До 50 товаров** за раз
- ✅ Интервал между импортами: **5-10 минут**

---

## 🐛 Решение частых проблем

### Проблема: Импорт зависает
```bash
# Очистка зависших статусов
php artisan import:cleanup --all

# Проверка статуса
php artisan import:status
```

### Проблема: Очередь не обрабатывается
```bash
# Проверить количество jobs
php artisan tinker
DB::table('jobs')->count();

# Перезапустить queue
php artisan queue:restart

# Запустить worker вручную
php artisan queue:work --once
```

### Проблема: Много failed jobs
```bash
# Посмотреть failed jobs
php artisan queue:failed

# Перезапустить все
php artisan queue:retry all

# Очистить failed jobs
php artisan queue:flush
```

---

## 📊 Мониторинг в реальном времени

### Просмотр логов:
```bash
tail -f storage/logs/laravel.log | grep -E "ERROR|WARNING|импорт|Job"
```

### Мониторинг очереди:
```bash
# В отдельном окне терминала
watch -n 5 'php artisan import:status'
```

### Проверка процессов:
```bash
ps aux | grep "queue:work"
```

---

## 📝 Контрольный список

Перед запуском импорта проверьте:

- [ ] CRON настроен правильно (без `session:clear`)
- [ ] Queue worker запущен и работает
- [ ] Нет ошибок в логах
- [ ] Очередь пустая или почти пустая (< 10 jobs)
- [ ] Хватает места на диске
- [ ] PHP настройки оптимизированы

---

## 🎯 Результат

После внесения всех изменений импорт должен:

- ✅ Работать без прерываний
- ✅ Корректно обрабатывать большие файлы
- ✅ Загружать изображения из Яндекс.Диска
- ✅ Не выдавать ошибок `session:clear`
- ✅ Автоматически повторять попытки при ошибках
- ✅ Предоставлять детальную статистику

---

## 📞 Поддержка

Если проблемы сохраняются, предоставьте:
- Вывод команды `php artisan import:status --detailed`
- Последние 200 строк из `storage/logs/laravel.log`
- Скриншот настроек CRON
- Информацию о PHP: `php -v` и `php -i | grep -E "memory|time"`

---

**Дата:** 26 октября 2025  
**Версия:** 2.0  
**Статус:** ✅ Протестировано и готово к развёртыванию
