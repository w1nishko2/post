# 📝 Обновления системы - 26 октября 2025

## ✅ Что было сделано

### 1. Настройка автоматической обработки задач

**Проблема:** Queue Worker требовал ручного запуска для обработки фоновых задач (скачивание изображений).

**Решение:**
- ✅ Создан `.bat` файл для автоматического запуска на Windows
- ✅ Создан PowerShell скрипт автоматической установки
- ✅ Подготовлена конфигурация для Supervisor (Linux/VPS)
- ✅ Написана полная документация для хостинга

**Файлы:**
- `start_queue_worker.bat` - автозапуск Queue Worker на Windows
- `setup_queue_worker.ps1` - автоматическая установка через PowerShell
- `QUEUE_WORKER_SETUP.md` - полное руководство
- `QUICK_QUEUE_SETUP.md` - краткая инструкция
- `HOSTING_CRON_SETUP.md` - **НОВОЕ** настройка cron на хостинге

---

### 2. Поддержка Яндекс.Диска

**Проблема:** Ссылки на альбомы `/a/` не работали (404 NOT FOUND).

**Решение:**
- ✅ Добавлена проверка типа ссылки
- ✅ Понятное сообщение об ошибке для альбомов
- ✅ Поддержка папок `/d/` и файлов `/i/`
- ✅ Автоматическое определение одиночных файлов vs папок

**Файлы:**
- `app/Services/YandexDiskService.php` - обновлён с проверкой альбомов
- `YANDEX_DISK_LINKS_GUIDE.md` - руководство по типам ссылок

**Поддерживаемые ссылки:**
- ✅ **Папки:** `https://disk.yandex.ru/d/xxxxxxxxxxx`
- ✅ **Файлы:** `https://disk.yandex.ru/i/xxxxxxxxxxx`
- ❌ **Альбомы:** `https://disk.yandex.ru/a/xxxxxxxxxxx` - НЕ ПОДДЕРЖИВАЮТСЯ

---

### 3. Улучшен интерфейс импорта

**Проблема:** Пользователи не знали, что можно закрыть окно во время импорта.

**Решение:**
- ✅ Добавлено информационное сообщение в модальное окно
- ✅ Обновлена подсказка о поддерживаемых типах ссылок
- ✅ Добавлена ссылка на документацию

**Файлы:**
- `resources/views/products/index.blade.php` - обновлён интерфейс

**Новый текст в окне импорта:**
```
ℹ️ Можно закрыть это окно
Товары появятся в списке автоматически по завершении импорта.
Если включена загрузка изображений, они будут скачиваться в фоновом режиме после импорта.
```

---

## 🚀 Инструкции для пользователей

### Для локальной разработки (Windows/OSPanel)

**Автоматическая установка:**

1. Откройте PowerShell от администратора
2. Выполните:
   ```powershell
   cd c:\ospanel\domains\post
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
   .\setup_queue_worker.ps1
   ```

**Результат:** Queue Worker будет автоматически запускаться при старте Windows.

---

### Для хостинга (shared hosting)

**См. файл:** `HOSTING_CRON_SETUP.md`

**Краткая инструкция:**

1. Откройте панель управления хостингом
2. Найдите раздел **Cron Jobs** / **Планировщик заданий**
3. Добавьте задачу:
   - **Частота:** Каждую минуту (`* * * * *`)
   - **Команда:**
     ```bash
     cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
     ```
4. Добавьте вторую задачу для Queue Worker:
   ```bash
   cd /home/username/public_html && php artisan queue:work --max-time=50 --max-jobs=10 >> /dev/null 2>&1
   ```

**Альтернатива для слабых хостингов:**

Измените `.env`:
```env
QUEUE_CONNECTION=sync
```
При этом задачи будут выполняться сразу (без очереди).

---

### Для VPS / Dedicated Server

**См. файл:** `QUEUE_WORKER_SETUP.md`

**Краткая инструкция:**

1. Настройте cron:
   ```bash
   crontab -e
   ```
   Добавьте:
   ```bash
   * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
   ```

2. Установите Supervisor:
   ```bash
   sudo apt-get install supervisor
   sudo cp laravel-queue-worker.conf /etc/supervisor/conf.d/
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-queue-worker:*
   ```

---

## 📚 Документация

### Основные файлы:

| Файл | Назначение | Для кого |
|------|-----------|----------|
| `QUICK_QUEUE_SETUP.md` | Краткая инструкция установки Queue Worker | Windows пользователи |
| `QUEUE_WORKER_SETUP.md` | Полное руководство по Queue Worker | Все |
| `HOSTING_CRON_SETUP.md` | **НОВОЕ** Настройка cron на хостинге | Shared hosting |
| `YANDEX_DISK_LINKS_GUIDE.md` | Работа со ссылками Яндекс.Диска | Все |
| `FILES_OVERVIEW.md` | Обзор всех файлов настройки | Все |

---

## ⚙️ Технические детали

### Queue Worker параметры:

```bash
php artisan queue:work --tries=3 --timeout=120 --max-time=3600 --sleep=3
```

- `--tries=3` - 3 попытки при ошибке
- `--timeout=120` - таймаут 120 секунд на одну задачу
- `--max-time=3600` - worker перезапускается каждый час (предотвращает утечки памяти)
- `--sleep=3` - пауза 3 секунды между проверками очереди

### Для shared hosting (короткие сессии):

```bash
php artisan queue:work --max-time=50 --max-jobs=10 --tries=3
```

- `--max-time=50` - работает 50 секунд и завершается
- `--max-jobs=10` - обработать максимум 10 задач

---

## 🔍 Проверка работы

### Проверить статус Queue Worker:

**Windows:**
```powershell
Get-ScheduledTask -TaskName "Laravel Queue Worker" | Get-ScheduledTaskInfo
Get-Process php | Format-Table Id,ProcessName,StartTime,CPU -AutoSize
```

**Linux:**
```bash
sudo supervisorctl status
ps aux | grep queue:work
```

### Проверить логи:

```bash
# Просмотр логов в реальном времени
tail -f storage/logs/laravel.log

# Последние 50 строк
tail -n 50 storage/logs/laravel.log
```

### Проверить очередь:

```bash
php artisan tinker
>>> DB::table('jobs')->count();
>>> DB::table('failed_jobs')->count();
```

---

## ⚠️ Важные замечания

### После обновления кода ВСЕГДА перезапускайте worker:

**Windows:**
```powershell
Stop-ScheduledTask -TaskName "Laravel Queue Worker"
Start-ScheduledTask -TaskName "Laravel Queue Worker"
```

**Linux (Supervisor):**
```bash
sudo supervisorctl restart laravel-queue-worker:*
```

### Ссылки Яндекс.Диска:

❌ **НЕ ИСПОЛЬЗУЙТЕ альбомы** (`/a/`) - они не поддерживаются API!

✅ **Используйте:**
- Папки: `https://disk.yandex.ru/d/xxxxxxxxxxx`
- Файлы: `https://disk.yandex.ru/i/xxxxxxxxxxx`

**Как конвертировать альбом в папку:**
См. `YANDEX_DISK_LINKS_GUIDE.md`

---

## 🐛 Решение проблем

### Queue Worker не запускается

1. Проверьте Task Scheduler (Windows) или Supervisor (Linux)
2. Проверьте логи: `storage/logs/laravel.log`
3. Запустите вручную: `php artisan queue:work`

### Jobs не обрабатываются

1. Проверьте, что worker запущен: `Get-Process php` (Windows) или `ps aux | grep queue` (Linux)
2. Проверьте `.env`: `QUEUE_CONNECTION=database` (или `sync` для хостинга)
3. Проверьте failed jobs: `php artisan queue:failed`

### Яндекс.Диск выдает 404

1. Проверьте тип ссылки - должна быть `/d/` или `/i/`, НЕ `/a/`
2. Проверьте, что ссылка публичная (откройте в режиме инкогнито)
3. См. `YANDEX_DISK_LINKS_GUIDE.md`

---

## 📊 Статистика изменений

**Всего файлов создано/изменено:** 9

**Создано:**
- `start_queue_worker.bat`
- `setup_queue_worker.ps1`
- `laravel-queue-worker.conf`
- `QUEUE_WORKER_SETUP.md`
- `QUICK_QUEUE_SETUP.md`
- `HOSTING_CRON_SETUP.md` ⭐ **НОВОЕ**
- `YANDEX_DISK_LINKS_GUIDE.md`
- `FILES_OVERVIEW.md`
- `CHANGELOG.md` (этот файл)

**Изменено:**
- `app/Services/YandexDiskService.php` - добавлена проверка альбомов
- `resources/views/products/index.blade.php` - обновлён интерфейс импорта

---

**Дата обновления:** 26 октября 2025
**Автор:** GitHub Copilot
