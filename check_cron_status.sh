#!/bin/bash

################################################################################
# Скрипт для проверки статуса cron задач и очередей
# Использование: bash check_cron_status.sh
################################################################################

# Цвета для вывода
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}         ПРОВЕРКА СТАТУСА CRON ЗАДАЧ И ОЧЕРЕДЕЙ${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Получаем путь к проекту (текущая директория)
PROJECT_PATH=$(pwd)
echo -e "${YELLOW}📁 Путь к проекту:${NC} $PROJECT_PATH"
echo ""

# Функция для выполнения SQL запросов
run_sql() {
    php artisan tinker --execute="$1"
}

# 1. Проверка очереди оформления заказов (checkout_queue)
echo -e "${GREEN}🛒 ОЧЕРЕДЬ ОФОРМЛЕНИЯ ЗАКАЗОВ (checkout_queue)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$stats = DB::table('checkout_queue')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
    
foreach (\$stats as \$stat) {
    echo \$stat->status . ': ' . \$stat->count . PHP_EOL;
}

\$total = DB::table('checkout_queue')->count();
echo 'Всего: ' . \$total . PHP_EOL;
"
echo ""

# 2. Проверка очереди импорта товаров (import_queue)
echo -e "${GREEN}📦 ОЧЕРЕДЬ ИМПОРТА ТОВАРОВ (import_queue)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$stats = DB::table('import_queue')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
    
foreach (\$stats as \$stat) {
    echo \$stat->status . ': ' . \$stat->count . PHP_EOL;
}

\$total = DB::table('import_queue')->count();
echo 'Всего: ' . \$total . PHP_EOL;
"
echo ""

# 3. Проверка фоновых задач (jobs)
echo -e "${GREEN}⚙️  ФОНОВЫЕ ЗАДАЧИ (jobs)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$count = DB::table('jobs')->count();
echo 'Всего задач в очереди: ' . \$count . PHP_EOL;

if (\$count > 0) {
    \$jobs = DB::table('jobs')->orderBy('created_at', 'desc')->limit(5)->get();
    echo 'Последние 5 задач:' . PHP_EOL;
    foreach (\$jobs as \$job) {
        echo '  - Queue: ' . \$job->queue . ', Попыток: ' . \$job->attempts . PHP_EOL;
    }
}
"
echo ""

# 4. Проверка проваленных задач (failed_jobs)
echo -e "${RED}❌ ПРОВАЛЕННЫЕ ЗАДАЧИ (failed_jobs)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$count = DB::table('failed_jobs')->count();
echo 'Всего проваленных задач: ' . \$count . PHP_EOL;

if (\$count > 0) {
    \$failed = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(3)->get();
    echo 'Последние 3 ошибки:' . PHP_EOL;
    foreach (\$failed as \$fail) {
        echo '  - Connection: ' . \$fail->connection . ', Queue: ' . \$fail->queue . PHP_EOL;
        echo '    Ошибка: ' . substr(\$fail->exception, 0, 100) . '...' . PHP_EOL;
    }
}
"
echo ""

# 5. Проверка последних логов Laravel
echo -e "${BLUE}📋 ПОСЛЕДНИЕ ЛОГИ LARAVEL${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Последние 10 строк из laravel.log:"
    tail -n 10 storage/logs/laravel.log
else
    echo -e "${YELLOW}⚠️  Файл laravel.log не найден${NC}"
fi
echo ""

# 6. Проверка логов очередей
echo -e "${BLUE}📋 ПОСЛЕДНИЕ ЛОГИ ОЧЕРЕДЕЙ${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ -f "storage/logs/queue.log" ]; then
    echo "Последние 10 строк из queue.log:"
    tail -n 10 storage/logs/queue.log
else
    echo -e "${YELLOW}⚠️  Файл queue.log не найден (это нормально, если не используется queue:work)${NC}"
fi
echo ""

# 7. Статистика заказов
echo -e "${GREEN}📊 СТАТИСТИКА ЗАКАЗОВ${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$stats = DB::table('orders')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
    
foreach (\$stats as \$stat) {
    echo \$stat->status . ': ' . \$stat->count . PHP_EOL;
}

\$total = DB::table('orders')->count();
echo 'Всего заказов: ' . \$total . PHP_EOL;

\$today = DB::table('orders')
    ->whereDate('created_at', today())
    ->count();
echo 'Создано сегодня: ' . \$today . PHP_EOL;
"
echo ""

# 8. Рекомендации
echo -e "${YELLOW}💡 РЕКОМЕНДАЦИИ${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan tinker --execute="
\$pending_checkout = DB::table('checkout_queue')->where('status', 'pending')->count();
\$pending_import = DB::table('import_queue')->where('status', 'pending')->count();
\$failed = DB::table('failed_jobs')->count();

if (\$pending_checkout > 100) {
    echo '⚠️  Много заказов в очереди checkout (' . \$pending_checkout . '). Рассмотрите увеличение --limit или добавление queue:work' . PHP_EOL;
}

if (\$pending_import > 500) {
    echo '⚠️  Много товаров в очереди import (' . \$pending_import . '). Рассмотрите увеличение --limit' . PHP_EOL;
}

if (\$failed > 10) {
    echo '❌ Слишком много проваленных задач (' . \$failed . '). Проверьте логи и исправьте ошибки!' . PHP_EOL;
    echo '   Команда для просмотра: php artisan queue:failed' . PHP_EOL;
    echo '   Команда для повтора: php artisan queue:retry all' . PHP_EOL;
}

if (\$pending_checkout == 0 && \$pending_import == 0 && \$failed == 0) {
    echo '✅ Всё отлично! Очереди пусты, ошибок нет.' . PHP_EOL;
}
"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}         ПРОВЕРКА ЗАВЕРШЕНА${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
