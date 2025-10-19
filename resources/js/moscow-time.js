// Утилиты для работы с московским временем на фронтенде
class MoscowTime {
    static MOSCOW_TIMEZONE = 'Europe/Moscow';
    
    /**
     * Получить текущее время в московском часовом поясе
     */
    static now() {
        return new Date().toLocaleString('ru-RU', {
            timeZone: this.MOSCOW_TIMEZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
    
    /**
     * Получить объект Date в московском времени
     */
    static getMoscowDate() {
        const now = new Date();
        return new Date(now.toLocaleString('en-US', {
            timeZone: this.MOSCOW_TIMEZONE
        }));
    }
    
    /**
     * Форматировать дату в московском времени
     */
    static formatDate(date, options = {}) {
        const defaultOptions = {
            timeZone: this.MOSCOW_TIMEZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        
        return new Date(date).toLocaleString('ru-RU', {
            ...defaultOptions,
            ...options
        });
    }
    
    /**
     * Получить только дату в московском времени (без времени)
     */
    static formatDateOnly(date) {
        return this.formatDate(date, {
            hour: undefined,
            minute: undefined,
            second: undefined
        });
    }
    
    /**
     * Получить только время в московском времени (без даты)
     */
    static formatTimeOnly(date) {
        return this.formatDate(date, {
            year: undefined,
            month: undefined,
            day: undefined
        });
    }
    
    /**
     * Получить временную метку для московского времени
     */
    static getTimestamp() {
        return this.getMoscowDate().getTime();
    }
    
    /**
     * Преобразовать UTC дату в московское время
     */
    static fromUTC(utcDate) {
        return this.formatDate(utcDate);
    }
    
    /**
     * Получить относительное время (например, "2 часа назад")
     */
    static getRelativeTime(date) {
        const now = this.getMoscowDate();
        const targetDate = new Date(date);
        const diffInSeconds = Math.floor((now - targetDate) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Только что';
        }
        
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes} мин. назад`;
        }
        
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours} ч. назад`;
        }
        
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 7) {
            return `${diffInDays} дн. назад`;
        }
        
        // Для более старых дат возвращаем полную дату
        return this.formatDateOnly(date);
    }
    
    /**
     * Проверить, является ли дата сегодняшней по московскому времени
     */
    static isToday(date) {
        const today = this.formatDateOnly(this.getMoscowDate());
        const targetDate = this.formatDateOnly(date);
        return today === targetDate;
    }
    
    /**
     * Получить начало дня в московском времени
     */
    static getStartOfDay(date = null) {
        const targetDate = date ? new Date(date) : this.getMoscowDate();
        const moscowDate = new Date(targetDate.toLocaleString('en-US', {
            timeZone: this.MOSCOW_TIMEZONE
        }));
        moscowDate.setHours(0, 0, 0, 0);
        return moscowDate;
    }
    
    /**
     * Получить конец дня в московском времени
     */
    static getEndOfDay(date = null) {
        const targetDate = date ? new Date(date) : this.getMoscowDate();
        const moscowDate = new Date(targetDate.toLocaleString('en-US', {
            timeZone: this.MOSCOW_TIMEZONE
        }));
        moscowDate.setHours(23, 59, 59, 999);
        return moscowDate;
    }
}

// Интеграция с существующим кодом
if (typeof window !== 'undefined') {
    window.MoscowTime = MoscowTime;
    
    // Переопределяем глобальные функции для работы с московским временем
    window.formatCurrentTime = function() {
        return MoscowTime.now();
    };
    
    window.formatDateForDisplay = function(date) {
        return MoscowTime.formatDate(date);
    };
    
    window.getRelativeTime = function(date) {
        return MoscowTime.getRelativeTime(date);
    };
}

// Обновляем отображение времени в существующих элементах
function updateTimeDisplays() {
    // Обновляем все элементы с классом .time-display
    document.querySelectorAll('.time-display').forEach(element => {
        if (element.dataset.utcTime) {
            element.textContent = MoscowTime.fromUTC(element.dataset.utcTime);
        }
    });
    
    // Обновляем все элементы с классом .relative-time
    document.querySelectorAll('.relative-time').forEach(element => {
        if (element.dataset.utcTime) {
            element.textContent = MoscowTime.getRelativeTime(element.dataset.utcTime);
        }
    });
    
    // Обновляем заголовок страницы с текущим временем, если нужно
    const timeHeader = document.querySelector('.current-time');
    if (timeHeader) {
        timeHeader.textContent = MoscowTime.now();
    }
}

// Запускаем обновление времени каждую минуту
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        updateTimeDisplays();
        
        // Обновляем каждую минуту
        setInterval(updateTimeDisplays, 60000);
    });
}