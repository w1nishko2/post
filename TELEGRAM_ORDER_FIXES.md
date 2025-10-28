# 🔧 Исправления проблем с заказами через Telegram

## ✅ Исправленные проблемы

### 1. **Нет уведомления об успешной покупке в магазине**
- **Было**: После оформления заказа клиент не получал четкого уведомления об успехе
- **Исправлено**: Теперь показывается уведомление: `"✅ Заказ успешно оформлен! Номер: XXX. Ожидайте подтверждения от администратора."`

### 2. **Много лишних уведомлений от бота**
- **Было**: Клиент получал промежуточные уведомления:
  - "Проверяем корзину..."
  - "Оформляем заказ..."
  - "Заказ обрабатывается..."
  - "Заказ в очереди на обработку..."
- **Исправлено**: Теперь показывается только финальное уведомление об успехе или ошибке

### 3. **Заказы из Telegram не сохраняются в БД**
- **Было**: `QUEUE_CONNECTION=sync` - очередь работала синхронно, Job не выполнялся правильно
- **Исправлено**: Изменено на `QUEUE_CONNECTION=database` - теперь очередь работает через БД

### 4. **Кнопки "Подтвердить заказ" и "Отменить заказ" у админа не работают**
- **Было**: Методы `answerCallbackQuery` и `editMessage` не использовали сервис правильно
- **Исправлено**: Все вызовы теперь используют `TelegramBotService` с правильными параметрами
- **Добавлено**: После нажатия кнопки она удаляется из сообщения

---

## 📝 Внесенные изменения

### 1. `.env`
```diff
- QUEUE_CONNECTION=sync
+ QUEUE_CONNECTION=database
```

### 2. `resources/js/mini-app.js`

#### Убраны лишние уведомления в `proceedToCheckout()`:
```diff
  isSubmittingOrder = true;

- // Показываем загрузку
- showAlert('Проверяем корзину...', 'info');

  // Сначала получаем актуальные данные корзины с сервера
  fetch('/cart', {
```

```diff
      return;
  }

- // Показываем загрузку оформления
- showAlert('Оформляем заказ...', 'info');

  // Подготавливаем данные для отправки
```

#### Упрощена функция `checkOrderStatus()`:
```diff
  .then(data => {
      if (data.success && data.status === 'completed' && data.order) {
          clearInterval(checkInterval);
-         showAlert(`✅ Заказ оформлен! Номер: ${data.order.order_number}`, 'success');
+         showAlert(`✅ Заказ успешно оформлен! Номер: ${data.order.order_number}. Ожидайте подтверждения от администратора.`, 'success');
          
      } else if (data.status === 'failed') {
          clearInterval(checkInterval);
          showAlert(`❌ Ошибка: ${data.error || 'Не удалось оформить заказ'}`, 'error');
-         
-     } else if (data.status === 'processing') {
-         showAlert('⏳ Заказ обрабатывается...', 'info');
-         
-     } else if (data.status === 'pending') {
-         if (attempts % 5 === 0) {
-             showAlert('⏳ Заказ в очереди на обработку...', 'info');
-         }
      }
```

### 3. `app/Http/Controllers/TelegramWebhookController.php`

#### Исправлены все вызовы методов работы с Telegram:
```diff
  if (!$order) {
-     $this->answerCallbackQuery($bot, $callbackQueryId, 'Заказ не найден', true);
+     $this->telegramService->answerCallbackQuery($bot, $callbackQueryId, 'Заказ не найден', true);
      return response()->json(['ok' => true]);
  }
```

#### Добавлено удаление кнопок после действия:
```diff
+ // Обновляем сообщение администратору
+ $updatedMessage = "✅ <b>ОПЛАТА ПОДТВЕРЖДЕНА!</b>\n\n" ...;

  $this->telegramService->editMessageText($bot, $chatId, $messageId, $updatedMessage);
+ 
+ // Удаляем кнопки
+ $this->telegramService->editMessageReplyMarkup($bot, $chatId, $messageId);
```

---

## 🚀 Что нужно сделать для применения изменений

### 1. **Перезапустить Laravel**
После изменения `.env` файла необходимо:

```bash
php artisan config:clear
php artisan cache:clear
```

### 2. **Убедиться, что cron задачи работают**
Проверьте, что в crontab добавлена команда:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Эта команда автоматически запускает:
- `checkout:process-queue` - обработка очереди оформления заказов
- `queue:work` - обработка фоновых задач (отправка уведомлений)

### 3. **Проверить webhook Telegram бота**
Убедитесь, что webhook настроен правильно:

```bash
php artisan tinker
```

```php
$bot = \App\Models\TelegramBot::first();
$service = app(\App\Services\TelegramBotService::class);
$service->setWebhook($bot, $service->getWebhookUrl($bot));
```

### 4. **Собрать frontend ресурсы**
После изменения `mini-app.js`:

```bash
npm run build
# или для разработки
npm run dev
```

---

## 🧪 Как протестировать

### 1. Оформление заказа через Telegram Mini App:
1. Откройте Mini App в Telegram
2. Добавьте товары в корзину
3. Нажмите "Оформить заказ"
4. **Ожидаемый результат**: Одно уведомление об успехе с номером заказа

### 2. Подтверждение заказа администратором:
1. Оформите заказ через Mini App
2. Администратор получит сообщение с кнопками
3. Нажмите "✅ Подтвердить оплату"
4. **Ожидаемый результат**: 
   - Сообщение обновится на "ОПЛАТА ПОДТВЕРЖДЕНА"
   - Кнопки исчезнут
   - Клиент получит уведомление об успешной оплате

### 3. Отмена заказа администратором:
1. Оформите заказ через Mini App
2. Нажмите "❌ Отменить заказ"
3. **Ожидаемый результат**:
   - Сообщение обновится на "ЗАКАЗ ОТМЕНЕН"
   - Кнопки исчезнут
   - Клиент получит уведомление об отмене
   - Товары вернутся на склад

---

## 📊 Проверка работы очереди

Используйте команду для проверки статуса:

```bash
bash check_cron_status.sh
```

Или вручную:

```bash
php artisan tinker
```

```php
// Проверка очереди оформления
\DB::table('checkout_queue')->select('status', \DB::raw('count(*) as count'))->groupBy('status')->get();

// Проверка фоновых задач
\DB::table('jobs')->count();

// Проверка проваленных задач
\DB::table('failed_jobs')->count();
```

---

## ⚠️ Важные примечания

1. **Queue Worker**: При использовании `QUEUE_CONNECTION=database` необходимо запустить worker:
   ```bash
   php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
   ```
   
   Или добавить в crontab (уже есть в инструкции):
   ```bash
   * * * * * cd /path/to/project && php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 >> storage/logs/queue.log 2>&1
   ```

2. **Логи**: Все действия логируются в `storage/logs/laravel.log`

3. **Время жизни заказа**: Заказы автоматически отменяются через 5 часов, если не подтверждены

---

## 🔍 Troubleshooting

### Заказы не создаются:
```bash
# Проверьте очередь checkout_queue
php artisan tinker
\DB::table('checkout_queue')->where('status', 'pending')->count();

# Обработайте очередь вручную
php artisan checkout:process-queue
```

### Уведомления не отправляются:
```bash
# Проверьте jobs
php artisan tinker
\DB::table('jobs')->count();

# Обработайте jobs вручную
php artisan queue:work database --once
```

### Кнопки не работают:
```bash
# Проверьте логи
tail -f storage/logs/laravel.log

# Проверьте webhook
php artisan tinker
$bot = \App\Models\TelegramBot::first();
$service = app(\App\Services\TelegramBotService::class);
$service->getWebhookInfo($bot);
```

---

**Дата исправлений**: 27 октября 2025 г.
**Версия**: 1.0.0
