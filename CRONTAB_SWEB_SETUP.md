# 🕐 Настройка Crontab для Sweb Shared Hosting

## 📋 Что нужно добавить в cPanel Cron Jobs

**ВАЖНО:** Замените `/home/your_username/domains/yourdomain.com/public_html` на ваш реальный путь к проекту!

---

## ✅ ОБЯЗАТЕЛЬНАЯ КОМАНДА (добавьте в cPanel):

```bash
* * * * * cd /home/your_username/domains/yourdomain.com/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Что автоматически выполняется:**
- ✅ Обработка очереди оформления заказов (checkout) - каждую минуту
- ✅ Обработка очереди импорта товаров - каждую минуту  
- ✅ Отмена просроченных заказов - каждые 15 минут
- ✅ Снятие просроченной брони товаров - каждые 15 минут
- ✅ Очистка старых сессий - раз в день

---

## 🔧 ДОПОЛНИТЕЛЬНАЯ КОМАНДА (опционально, для высоких нагрузок):

```bash
* * * * * cd /home/your_username/domains/yourdomain.com/public_html && /usr/local/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> /home/your_username/domains/yourdomain.com/public_html/storage/logs/queue.log 2>&1
```

**Для чего:** Обработка фоновых задач (загрузка изображений, отправка уведомлений и т.д.)

---

## 📝 Инструкция по добавлению в cPanel Sweb

### Шаг 1: Войдите в cPanel
Откройте: **https://sweb.ru/cpanel** и авторизуйтесь

### Шаг 2: Откройте Cron Jobs
Найдите раздел **"Advanced"** → **"Cron Jobs"**

### Шаг 3: Добавьте команду
1. Выберите **"Common Settings"** → **"Once Per Minute (* * * * *)"**
2. В поле **"Command"** вставьте команду (см. выше)
3. **Замените пути на свои!**
4. Нажмите **"Add New Cron Job"**

### Шаг 4: Узнайте свой путь к проекту
Подключитесь по SSH и выполните:
```bash
pwd
```

Обычный путь на Sweb:
```
/home/username/domains/yourdomain.com/public_html
```

---

## ✅ Готово!

После добавления cron задача будет автоматически:
- Обрабатывать заказы
- Импортировать товары  
- Отменять просроченные заказы
- Снимать бронь с просроченных заказов
- Очищать старые сессии

---
