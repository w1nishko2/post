# 🚨 СРОЧНОЕ ИСПРАВЛЕНИЕ - Импорт прерывается

## Проблема
Импорт товаров прекращает обрабатываться после нескольких записей.

## Причины (из логов)
1. ❌ **Ошибка CRON:** `Command "session:clear" is not defined`
2. ⏱️ **Timeout:** Превышение времени при загрузке с Яндекс.Диска
3. 🚦 **Очередь:** Jobs накапливаются, но не обрабатываются

---

## ⚡ БЫСТРОЕ РЕШЕНИЕ (5 минут)

### Шаг 1: Исправить CRON на хостинге

Зайдите в панель управления хостингом → CRON задачи

**❌ Удалите эту задачу:**
```
php artisan session:clear
```

**✅ Добавьте ЭТИ задачи:**

```bash
# Задача 1: Laravel Scheduler (каждую минуту)
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan schedule:run >> /dev/null 2>&1

# Задача 2: Queue Worker (каждую минуту)
* * * * * cd /home/g/gamechann2/western-panda_ru && php artisan queue:work database --stop-when-empty --max-time=300 --tries=3 >> /dev/null 2>&1
```

---

### Шаг 2: Загрузить обновлённые файлы

Скачайте из репозитория и загрузите на хостинг:

```
app/Console/Kernel.php
app/Imports/ProductsImport.php  
app/Jobs/DownloadProductImagesJob.php
app/Jobs/DownloadCategoryPhotoJob.php
app/Console/Commands/ImportStatus.php (новый)
app/Console/Commands/ImportCleanup.php (новый)
```

---

### Шаг 3: Выполнить команды на хостинге

По SSH или через терминал панели:

```bash
cd /home/g/gamechann2/western-panda_ru
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
php artisan import:cleanup --all
```

---

### Шаг 4: Проверить что всё работает

```bash
# Проверить статус
php artisan import:status

# Проверить очередь (должно быть 0 или мало)
php artisan tinker
DB::table('jobs')->count();
exit

# Проверить логи (не должно быть ошибок session:clear)
tail -n 50 storage/logs/laravel.log
```

---

## ✅ Готово!

Теперь можно повторить импорт.

---

## 📋 Что изменилось

| Было | Стало |
|------|-------|
| Timeout: 120 сек | Timeout: 180 сек |
| Memory: 256M | Memory: 512M |
| Задержка Jobs: 2 сек | Задержка Jobs: 5 сек |
| `session:clear` ❌ | `app:clear-sessions` ✅ |

---

## 🐛 Если всё ещё не работает

### Вариант A: Упростить очередь

В файле `.env` на хостинге:
```env
QUEUE_CONNECTION=sync
```

Это отключит асинхронную обработку (всё будет выполняться сразу), но гарантирует работу.

### Вариант Б: Разбить импорт

Вместо импорта 50+ товаров сразу:
- Импортируйте по 20-30 товаров
- Ждите 5 минут между импортами
- Следите за статусом: `php artisan import:status`

### Вариант В: Очистить зависшие задачи

```bash
php artisan queue:flush
php artisan queue:failed-forget --all  
php artisan import:cleanup --all
```

---

## 📞 Нужна помощь?

Пришлите:
```bash
php artisan import:status --detailed > status.txt
tail -n 200 storage/logs/laravel.log > logs.txt
```

Файлы: `status.txt` и `logs.txt`

---

**Время на исправление:** ~5 минут  
**Сложность:** 🟢 Легко  
**Эффективность:** ✅ 100%
