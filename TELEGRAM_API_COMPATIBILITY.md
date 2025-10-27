# Совместимость с Telegram WebApp API

## Проблема

Mini App показывал ошибки при работе со старыми версиями Telegram WebApp API (версия 6.0 и ниже):
- `WebAppMethodUnsupported` для `showPopup`, `showConfirm`, `showAlert`
- `HapticFeedback is not supported`
- `enableClosingConfirmation is not supported`

## Решение

### 1. Проверка версии API
Добавлена функция `checkTelegramVersion()` которая:
- Определяет текущую версию Telegram WebApp API
- Логирует предупреждения для старых версий
- Уведомляет пользователя о необходимости обновления

### 2. Условное использование функций

#### showAlert (версия 6.1+)
```javascript
// Проверяем версию перед использованием
if (tg && version >= 6.1 && typeof tg.showAlert === 'function') {
    tg.showAlert(message);
} else {
    showToast(message, type); // fallback
}
```

#### HapticFeedback (версия 6.1+)
```javascript
// Проверяем версию и наличие API
if (tg && version >= 6.1 && tg.HapticFeedback && 
    typeof tg.HapticFeedback.impactOccurred === 'function') {
    tg.HapticFeedback.impactOccurred(type);
}
```

#### showConfirm (версия 6.2+)
```javascript
// Проверяем версию перед использованием
if (tg && version >= 6.2 && typeof tg.showConfirm === 'function') {
    tg.showConfirm(message, callback);
} else {
    // Fallback на нативный confirm
    if (confirm(message)) {
        callback(true);
    }
}
```

#### enableClosingConfirmation (версия 6.2+)
```javascript
if (version >= 6.2 && typeof tg.enableClosingConfirmation === 'function') {
    tg.enableClosingConfirmation();
}
```

### 3. Таблица совместимости методов

| Метод | Минимальная версия | Fallback |
|-------|-------------------|----------|
| `ready()` | 6.0 | - |
| `expand()` | 6.0 | - |
| `showAlert()` | 6.1 | Toast уведомление |
| `HapticFeedback` | 6.1 | Нет (молча игнорируется) |
| `showConfirm()` | 6.2 | Нативный `confirm()` |
| `showPopup()` | 6.2 | Toast уведомление |
| `enableClosingConfirmation()` | 6.2 | Нет (молча игнорируется) |
| `BackButton` | 6.1 | Нет |

## Тестирование

### Версия 6.0
- ✅ Основной функционал работает
- ✅ Используется нативный `confirm()` для подтверждений
- ✅ Toast уведомления вместо Telegram Alert
- ✅ Haptic feedback отключен (без ошибок)

### Версия 6.1+
- ✅ Доступны `showAlert` и `HapticFeedback`
- ✅ Используется нативный `confirm()` для подтверждений

### Версия 6.2+
- ✅ Все функции полностью доступны
- ✅ Красивые модальные окна подтверждения
- ✅ Защита от случайного закрытия

## Рекомендации пользователям

Для старых версий Telegram отображается toast-уведомление:
> "Обновите Telegram для улучшенной работы приложения"

Показывается через 3 секунды после загрузки приложения.
