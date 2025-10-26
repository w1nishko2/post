# ⚙️ Настройка Cron на Хостинге

Эта инструкция предназначена для настройки автоматической обработки задач Laravel на **shared hosting** (виртуальном хостинге).

---

## 🎯 Что нужно настроить на хостинге

На хостинге нужно настроить **две задачи**:

1. **Laravel Scheduler** - выполняется каждую минуту для периодических задач
2. **Queue Worker** - постоянно работающий процесс для обработки фоновых задач

---

## 📋 Вариант 1: cPanel / ISPmanager / DirectAdmin

### Шаг 1: Найдите раздел Cron Jobs

**cPanel:**
- Панель управления → Advanced → Cron Jobs

**ISPmanager:**
- Инструменты → Планировщик заданий (cron)

**DirectAdmin:**
- Advanced Features → Cronjobs

---

### Шаг 2: Добавьте задачу для Laravel Scheduler

**Частота выполнения:** Каждую минуту

**Команда для cPanel:**
```bash
* * * * * cd /home/username/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Команда для ISPmanager:**
```bash
* * * * * cd /var/www/username/data/www/yourdomain.com && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Где заменить:**
- `username` - ваше имя пользователя на хостинге
- `yourdomain.com` - ваш домен
- `/usr/bin/php` - путь к PHP (можно узнать командой `which php` в SSH)

**Пример для cPanel:**
```
Minute: *
Hour: *
Day: *
Month: *
Weekday: *
Command: cd /home/myuser/domains/example.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

### Шаг 3: Добавьте задачу для Queue Worker

⚠️ **ВАЖНО:** Queue Worker должен работать **постоянно**, но на shared hosting это проблема, так как cron задачи имеют ограничение по времени выполнения.

**Решение 1 - Запуск каждую минуту (для shared hosting):**

```bash
* * * * * cd /home/username/domains/yourdomain.com/public_html && /usr/bin/php artisan queue:work --max-time=50 --max-jobs=10 --tries=3 >> /dev/null 2>&1
```

Параметры:
- `--max-time=50` - worker работает 50 секунд и завершается (следующий запустится через минуту)
- `--max-jobs=10` - обработать максимум 10 задач и завершиться
- `--tries=3` - 3 попытки при ошибке

**Решение 2 - Использовать sync драйвер (без очереди):**

Если хостинг не позволяет долгие процессы, измените `.env`:

```env
QUEUE_CONNECTION=sync
```

При этом задачи будут выполняться **синхронно** (без фоновой обработки), но это гарантированно работает на любом хостинге.

---

## 📋 Вариант 2: VPS / Dedicated Server (с SSH доступом)

### Шаг 1: Подключитесь по SSH

```bash
ssh username@your-server-ip
```

### Шаг 2: Откройте crontab

```bash
crontab -e
```

### Шаг 3: Добавьте задачи

Вставьте в конец файла:

```bash
# Laravel Scheduler - выполняется каждую минуту
* * * * * cd /var/www/yourdomain.com && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Опционально: очистка старых failed jobs раз в день в 3 часа ночи
0 3 * * * cd /var/www/yourdomain.com && /usr/bin/php artisan queue:prune-failed --hours=48 >> /dev/null 2>&1
```

### Шаг 4: Настройте Supervisor для Queue Worker

```bash
# Установите Supervisor
sudo apt-get update
sudo apt-get install supervisor

# Создайте конфигурацию
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

Вставьте:

```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/yourdomain.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/yourdomain.com/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Запустите:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue-worker:*
```

---

## 📋 Вариант 3: Beget, TimeWeb, Hosting Ukraine и другие популярные хостинги

### Beget

1. Панель управления → MySQL & Файлы → Планировщик (cron)
2. Добавить задание:
   - **Период:** Каждую минуту
   - **Команда:**
     ```bash
     cd /home/u123456/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run
     ```

3. Для Queue Worker добавьте вторую задачу:
   ```bash
   cd /home/u123456/domains/yourdomain.com/public_html && /usr/bin/php artisan queue:work --max-time=50 --max-jobs=10
   ```

### TimeWeb

1. Панель управления → Инструменты → Планировщик заданий (Cron)
2. Создать задание:
   - **Минуты:** `*`
   - **Часы:** `*`
   - **Дни месяца:** `*`
   - **Месяцы:** `*`
   - **Дни недели:** `*`
   - **Команда:**
     ```bash
     cd /home/username/public_html && php artisan schedule:run
     ```

### HostPro, Ukraine

1. cPanel → Дополнительно → Задания Cron
2. Добавить задание Cron:
   - **Общие настройки:** Раз в минуту
   - **Команда:**
     ```bash
     /usr/bin/php /home/username/public_html/artisan schedule:run
     ```

---

## 🔍 Как узнать путь к PHP на хостинге

### Способ 1: SSH (если есть доступ)

```bash
which php
# Выведет: /usr/bin/php или /usr/local/bin/php
```

### Способ 2: Создайте файл phpinfo.php

```php
<?php
echo 'PHP Path: ' . PHP_BINARY;
phpinfo();
```

Откройте в браузере: `https://yourdomain.com/phpinfo.php`

**Не забудьте удалить файл после проверки!**

### Способ 3: Через cron тест

Создайте тестовое задание:

```bash
* * * * * which php > /home/username/php_path.txt
```

Через минуту проверьте файл `/home/username/php_path.txt`

---

## 🛠️ Проверка работы Cron

### Способ 1: Логи Laravel

```bash
tail -f storage/logs/laravel.log
```

Вы должны увидеть записи каждую минуту от Scheduler.

### Способ 2: Создайте тестовую команду

```bash
php artisan make:command TestCron
```

Отредактируйте `app/Console/Commands/TestCron.php`:

```php
protected $signature = 'test:cron';

public function handle()
{
    \Log::info('Cron работает! ' . now());
    $this->info('Test completed');
}
```

Добавьте в `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('test:cron')->everyMinute();
}
```

Проверьте логи через минуту:

```bash
grep "Cron работает" storage/logs/laravel.log
```

### Способ 3: Проверка через базу данных

Добавьте задачу в очередь вручную:

```bash
php artisan tinker
>>> dispatch(new \App\Jobs\TestJob());
```

Проверьте таблицу `jobs`:

```bash
php artisan tinker
>>> DB::table('jobs')->count();
```

Если счётчик уменьшается - Queue Worker работает!

---

## ⚠️ Частые проблемы на хостинге

### 1. "Queue Worker killed after 60 seconds"

**Причина:** Хостинг ограничивает время выполнения cron задач.

**Решение:**
```bash
# Используйте короткое время работы
* * * * * cd /path && php artisan queue:work --max-time=50 --max-jobs=5
```

### 2. "Permission denied"

**Причина:** Неправильные права доступа.

**Решение:**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. "Class not found"

**Причина:** Не обновлен autoload после deploy.

**Решение:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### 4. Queue Worker не обрабатывает задачи

**Причина:** Shared hosting не поддерживает долгие процессы.

**Решение:** Используйте `QUEUE_CONNECTION=sync` в `.env`

```env
QUEUE_CONNECTION=sync
```

При этом задачи будут выполняться сразу (без очереди), но это работает на любом хостинге.

---

## 📊 Рекомендуемые настройки для разных типов хостинга

### Shared Hosting (виртуальный хостинг)

**.env:**
```env
QUEUE_CONNECTION=database
# Или для немедленной обработки:
# QUEUE_CONNECTION=sync
```

**Cron (Laravel Scheduler):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Cron (Queue Worker - короткие сессии):**
```bash
* * * * * cd /path/to/project && php artisan queue:work --max-time=50 --max-jobs=10 --tries=3 >> /dev/null 2>&1
```

---

### VPS / Cloud Server

**.env:**
```env
QUEUE_CONNECTION=redis
```

**Cron (Laravel Scheduler):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Supervisor (Queue Worker - постоянный процесс):**
```ini
[program:laravel-queue-worker]
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
numprocs=2
autostart=true
autorestart=true
```

---

## 🔗 Полезные команды для проверки

```bash
# Проверить список cron задач
crontab -l

# Просмотр логов cron (Ubuntu/Debian)
grep CRON /var/log/syslog

# Просмотр логов Laravel
tail -f storage/logs/laravel.log

# Проверить failed jobs
php artisan queue:failed

# Повторить failed jobs
php artisan queue:retry all

# Очистить очередь
php artisan queue:clear

# Проверить количество задач в очереди
php artisan tinker
>>> DB::table('jobs')->count();
```

---

## 📝 Чеклист настройки на хостинге

- [ ] Добавлен cron для `schedule:run` (каждую минуту)
- [ ] Добавлен cron для `queue:work` (каждую минуту с `--max-time=50`) или настроен `QUEUE_CONNECTION=sync`
- [ ] Проверен путь к PHP (`which php`)
- [ ] Установлены права `chmod 755` на `storage` и `bootstrap/cache`
- [ ] Выполнен `composer install --no-dev --optimize-autoloader`
- [ ] Настроен `.env` файл (QUEUE_CONNECTION, DB credentials)
- [ ] Протестирована обработка задач
- [ ] Проверены логи `storage/logs/laravel.log`

---

## 🚀 Готовые команды для копирования

### Для cPanel хостинга:

**Laravel Scheduler:**
```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Queue Worker (короткие сессии):**
```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan queue:work --max-time=50 --max-jobs=10 --tries=3 >> /dev/null 2>&1
```

### Для VPS с Supervisor:

**Cron:**
```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

**Supervisor конфиг** (`/etc/supervisor/conf.d/laravel-queue.conf`):
```ini
[program:laravel-queue-worker]
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/var/www/html/storage/logs/queue-worker.log
```

---

**Дата создания:** 26 октября 2025
