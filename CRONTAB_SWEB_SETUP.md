# 🕐 Настройка Crontab для Sweb Shared Hosting

## 📋 Общая информация

Для автоматизации работы Laravel приложения на Sweb shared хостинге необходимо настроить следующие cron задачи:

1. **Laravel Scheduler** - основной планировщик Laravel (запускает все задачи)
2. **Queue Worker** - обработчик очередей для фоновых задач

---

## 🔧 Команды Crontab для Sweb

### 1️⃣ Laravel Scheduler (ОБЯЗАТЕЛЬНО)
Эта команда запускает планировщик Laravel каждую минуту. Он автоматически выполняет все задачи, указанные в `app/Console/Kernel.php`:

```bash
* * * * * cd /home/your_username/domains/post && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Что выполняет:**
- ✅ Обработка очереди оформления заказов (checkout) - каждую минуту
- ✅ Обработка очереди импорта товаров - каждую минуту
- ✅ Отмена просроченных заказов - каждые 15 минут
- ✅ Очистка старых сессий - раз в день

---

### 2️⃣ Queue Worker (РЕКОМЕНДУЕТСЯ для тяжелых задач)
Для обработки очередей в режиме реального времени:

```bash
* * * * * cd /home/your_username/domains/post && /usr/local/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> /home/your_username/domains/post/storage/logs/queue.log 2>&1
```

**Параметры:**
- `database` - использует драйвер database для очередей
- `--sleep=3` - ждать 3 секунды между проверками
- `--tries=3` - максимум 3 попытки выполнения
- `--max-time=3600` - перезапуск каждый час (для предотвращения утечек памяти)

---

## 🎯 Рекомендуемая конфигурация для Sweb

### Вариант 1: Только Laravel Scheduler (МИНИМАЛЬНАЯ)
Подходит для малых и средних нагрузок:

```bash
* * * * * cd /home/your_username/domains/post && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

### Вариант 2: Scheduler + Queue Worker (ОПТИМАЛЬНАЯ)
Подходит для средних и высоких нагрузок:

```bash
# Laravel Scheduler (обработка checkout и import очередей)
* * * * * cd /home/your_username/domains/post && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

# Queue Worker для дополнительных фоновых задач
* * * * * cd /home/your_username/domains/post && /usr/local/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> /home/your_username/domains/post/storage/logs/queue.log 2>&1
```

---

## 📝 Инструкция по настройке на Sweb

### Шаг 1: Войдите в cPanel
1. Перейдите на https://sweb.ru/cpanel
2. Авторизуйтесь

### Шаг 2: Найдите "Cron Jobs"
1. В разделе "Advanced" найдите "Cron Jobs"
2. Кликните на него

### Шаг 3: Добавьте задачу
1. В поле "Minute" введите: `*`
2. В поле "Hour" введите: `*`
3. В поле "Day" введите: `*`
4. В поле "Month" введите: `*`
5. В поле "Weekday" введите: `*`
6. В поле "Command" введите команду (см. ниже)

### Шаг 4: Определите правильный путь
**ВАЖНО!** Замените `/home/your_username/domains/post` на ваш реальный путь:

```bash
# Для определения пути выполните в SSH:
pwd
```

Обычно на Sweb путь выглядит так:
- `/home/username/domains/yourdomain.com/public_html`
- `/home/username/public_html`

### Шаг 5: Определите путь к PHP
На Sweb обычно используется:
- `/usr/local/bin/php` (PHP 8.x)
- `/usr/bin/php` (альтернатива)

Проверьте версию PHP:
```bash
/usr/local/bin/php -v
```

---

## 🔍 Финальные команды для копирования

### Для Laravel Scheduler (замените пути):
```bash
* * * * * cd /home/your_username/domains/yourdomain.com/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

### Для Queue Worker (замените пути):
```bash
* * * * * cd /home/your_username/domains/yourdomain.com/public_html && /usr/local/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> /home/your_username/domains/yourdomain.com/public_html/storage/logs/queue.log 2>&1
```

---

## ✅ Проверка работы

### 1. Проверьте логи Laravel
```bash
tail -f storage/logs/laravel.log
```

### 2. Проверьте логи очереди
```bash
tail -f storage/logs/queue.log
```

### 3. Проверьте таблицы в БД
```sql
-- Проверка очереди оформления заказов
SELECT * FROM checkout_queue WHERE status = 'pending';

-- Проверка очереди импорта
SELECT * FROM import_queue WHERE status = 'pending';

-- Проверка фоновых задач
SELECT * FROM jobs;
```

---

## 🚨 Частые проблемы

### Проблема 1: Cron не выполняется
**Решение:**
- Проверьте права доступа: `chmod -R 755 storage bootstrap/cache`
- Проверьте путь к PHP: `/usr/local/bin/php -v`
- Проверьте логи cron в cPanel

### Проблема 2: Ошибки доступа к БД
**Решение:**
- Проверьте `.env` файл
- Убедитесь, что БД доступна
- Проверьте credentials в `.env`

### Проблема 3: Таймаут выполнения
**Решение:**
- Увеличьте лимиты в командах:
  ```bash
  --limit=50    # уменьшите до 20-30
  --max-time=1800  # уменьшите до 30 минут
  ```

### Проблема 4: Queue Worker падает
**Решение:**
- Используйте supervisor (если доступен на Sweb)
- Или используйте только `schedule:run` без queue:work

---

## 📊 Мониторинг

### Создайте скрипт для проверки статуса
```bash
# Файл: check_queues.sh
#!/bin/bash

echo "=== Checkout Queue Status ==="
mysql -u username -p'password' -D database -e "SELECT status, COUNT(*) as count FROM checkout_queue GROUP BY status;"

echo ""
echo "=== Import Queue Status ==="
mysql -u username -p'password' -D database -e "SELECT status, COUNT(*) as count FROM import_queue GROUP BY status;"

echo ""
echo "=== Jobs Queue Status ==="
mysql -u username -p'password' -D database -e "SELECT COUNT(*) as count FROM jobs;"
```

Запускайте: `bash check_queues.sh`

---

## 🎉 Готово!

После настройки cron задач ваше приложение будет автоматически:
- ✅ Обрабатывать заказы из корзины (до 100 заказов в минуту)
- ✅ Импортировать товары (до 50 товаров в минуту)
- ✅ Отменять просроченные заказы (каждые 15 минут)
- ✅ Очищать старые сессии (раз в день)

---

## 📞 Поддержка

Если возникли проблемы:
1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте cron логи в cPanel
3. Свяжитесь с поддержкой Sweb для уточнения путей

---

**Последнее обновление:** 27 октября 2025 г.
