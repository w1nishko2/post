# 🕐 Настройка Crontab для Sweb Shared Hosting

## 📋 Что нужно добавить в cPanel Cron Jobs

**ВАЖНО:** Замените ` /home/g/gamechann2/western-panda_ru` на ваш реальный путь к проекту!

---

## ✅ ОБЯЗАТЕЛЬНАЯ КОМАНДА (добавьте в cPanel):

```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php8.1 artisan schedule:run >> /dev/null 2>&1
```

**ВАЖНО для Sweb:** Используйте `php8.1` (или другую версию: php8.2, php8.3, php8.4) вместо `/usr/local/bin/php`

**Эта одна команда запускает ВСЕ автоматические задачи:**

1. **Обработка очереди оформления заказов** (`checkout:process-queue`)
   - Частота: каждую минуту
   - Лимит: 100 заказов за раз
   - Что делает: создает заказы из корзины, бронирует товары, отправляет уведомления

2. **Обработка очереди импорта товаров** (`import:process-queue`)
   - Частота: каждую минуту
   - Лимит: 50 товаров за раз
   - Что делает: массовая загрузка товаров из Excel/CSV

3. **Отмена просроченных заказов** (`orders:cancel-expired`)
   - Частота: каждые 15 минут
   - Что делает: отменяет заказы старше 5 часов в статусе "pending", снимает бронь товаров, возвращает количество на склад

4. **Очистка старых сессий** (`app:clear-sessions`)
   - Частота: раз в день (в 00:00)
   - Что делает: удаляет неиспользуемые сессии для освобождения места в БД

---

## 🔧 ДОПОЛНИТЕЛЬНАЯ КОМАНДА (опционально, только для высоких нагрузок):

```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php8.1 artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> /home/g/gamechann2/western-panda_ru/storage/logs/queue.log 2>&1
```

**Для чего нужна:**
- Обработка фоновых задач из таблицы `jobs` в режиме реального времени
- Загрузка изображений категорий и товаров
- Отправка Telegram уведомлений
- Другие асинхронные операции

**Параметры:**
- `--sleep=3` - пауза 3 секунды между проверками
- `--tries=3` - максимум 3 попытки выполнения задачи
- `--max-time=3600` - автоматический рестарт каждый час

**⚠️ Рекомендация:** Добавляйте только если основная команда не справляется с нагрузкой

---

## 🛒 КОМАНДЫ ДЛЯ РУЧНОЙ ОБРАБОТКИ ОЧЕРЕДЕЙ

Если нужно обработать очереди вручную (через SSH):

```bash
# Обработка очереди оформления заказов (checkout_queue)
php8.1 artisan checkout:process-queue

# Обработка очереди импорта товаров (import_queue)
php8.1 artisan import:process-queue

# Обработка одной задачи из jobs
php8.1 artisan queue:work database --once

# Отмена просроченных заказов
php8.1 artisan orders:cancel-expired
```

---

## 📝 Инструкция по добавлению в cPanel Sweb

### Шаг 1: Войдите в cPanel
Откройте: **https://sweb.ru/cpanel** и авторизуйтесь

### Шаг 2: Откройте Cron Jobs
Найдите раздел **"Advanced"** → **"Cron Jobs"**

### Шаг 3: Добавьте команду
1. Выберите **"Common Settings"** → **"Once Per Minute (✶ ✶ ✶ ✶ ✶)"**
2. В поле **"Command"** вставьте:
   ```bash
   cd /home/g/gamechann2/western-panda_ru && php8.1 artisan schedule:run >> /dev/null 2>&1
   ```
3. **ВАЖНО:** Используйте `php8.1` (или php8.2, php8.3, php8.4) - НЕ используйте `/usr/local/bin/php`
4. Нажмите **"Add New Cron Job"**

### Шаг 4: Узнайте свой путь к проекту
Подключитесь по SSH и выполните:
```bash
pwd
```

**Примеры путей на Sweb:**
```
/home/username/domains/example.com/public_html
/home/username/public_html
/home/username/example.com
```

### Шаг 5: Проверьте путь к PHP
```bash
which php8.1
# На Sweb доступны: php8.4, php8.3, php8.2, php8.1, php8.0, php7.4

php8.1 -v
# Должно показать версию PHP 8.1.x
```
# Должно показать: PHP 8.x.x
```

---

## 📋 ГОТОВАЯ КОМАНДА ДЛЯ КОПИРОВАНИЯ

**Скопируйте эту команду и замените пути:**

```bash
* * * * * cd /home/YOUR_USERNAME/YOUR_PROJECT_PATH && php8.1 artisan schedule:run >> /dev/null 2>&1
```

**Пример заполнения:**
- Ваш логин Sweb: `gamechann2`
- Путь к проекту: `western-panda_ru`

**Итоговая команда:**
```bash
* * * * * cd /home/g/gamechann2/western-panda_ru && php8.1 artisan schedule:run >> /dev/null 2>&1
```

**⚠️ ВАЖНО:** На Sweb используйте `php8.1` БЕЗ пути `/usr/local/bin/`!

---

## ✅ Готово!

После добавления cron задачи будут автоматически работать:

| Задача | Частота | Описание |
|--------|---------|----------|
| `checkout:process-queue` | Каждую минуту | Оформление заказов (до 100 шт/мин) |
| `import:process-queue` | Каждую минуту | Импорт товаров (до 50 шт/мин) |
| `orders:cancel-expired` | Каждые 15 минут | Отмена просроченных заказов + снятие брони |
| `app:clear-sessions` | Раз в день (00:00) | Очистка старых сессий |

---

## 🔍 Проверка работы

### После добавления cron задачи подождите 2-3 минуты и проверьте:

```bash
# Проверка логов Laravel
tail -f storage/logs/laravel.log

# Проверка очереди checkout
php8.1 artisan tinker --execute="echo App\Models\CheckoutQueue::where('status', 'pending')->count();"

# Проверка очереди импорта
php8.1 artisan tinker --execute="echo App\Models\ImportQueue::where('status', 'pending')->count();"

# Проверка фоновых задач
php8.1 artisan tinker --execute="echo DB::table('jobs')->count();"
```

### Что должно происходить:

✅ Очередь `checkout_queue` с `status='pending'` обрабатывается каждую минуту
✅ Очередь `import_queue` с `status='pending'` обрабатывается каждую минуту
✅ Заказы старше 5 часов автоматически отменяются
✅ Бронь товаров автоматически снимается при отмене заказа

---

## 🚨 Возможные проблемы

### 1. Cron не запускается

**Проверьте:**
```bash
# Права доступа
chmod -R 755 storage bootstrap/cache

# Путь к PHP на Sweb
which php8.1
php8.1 -v
```

**Для Sweb используйте:**
- `php8.1` или `php8.2`, `php8.3`, `php8.4`
- НЕ используйте `/usr/local/bin/php` - такого пути НЕТ на Sweb!

### 2. Ошибка "artisan not found"

**Проверьте путь к проекту:**
```bash
pwd
ls -la artisan
```

**Убедитесь, что:**
- Путь в cron команде совпадает с `pwd`
- Файл `artisan` существует и исполняемый

### 3. Задачи не выполняются

**Проверьте конфигурацию:**
```bash
# .env файл существует
ls -la .env

# База данных доступна
php8.1 artisan tinker --execute="DB::connection()->getPdo();"

# Проверка очереди вручную
php8.1 artisan import:process-queue
```

---

## 🎯 ЧАСТЫЕ ОШИБКИ НА SWEB

### ❌ НЕПРАВИЛЬНО:
```bash
/usr/local/bin/php artisan schedule:run
/usr/local/bin/php8.1 artisan queue:work
```
**Ошибка:** `/bin/sh: /usr/local/bin/php: No such file or directory`

### ✅ ПРАВИЛЬНО:
```bash
php8.1 artisan schedule:run
php8.1 artisan queue:work database
```

---
