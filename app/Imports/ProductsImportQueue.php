<?php

namespace App\Imports;

use App\Models\ImportQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * БЫСТРЫЙ ИМПОРТ В ТАБЛИЦУ ОЧЕРЕДИ
 * 
 * Стратегия:
 * 1. Читаем Excel файл чанками по 500 строк
 * 2. Сохраняем ВСЕ данные в таблицу import_queue (БЕЗ обработки)
 * 3. Мгновенный ответ пользователю (1-3 секунды)
 * 4. CRON обрабатывает очередь в фоне
 */
class ProductsImportQueue implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $userId;
    protected $botId;
    protected $updateExisting;
    protected $downloadImages;
    protected $importSessionId;
    
    protected static $totalImported = 0;

    public function __construct($userId, $botId, $updateExisting = false, $downloadImages = true)
    {
        $this->userId = $userId;
        $this->botId = $botId;
        $this->updateExisting = $updateExisting;
        $this->downloadImages = $downloadImages;
        $this->importSessionId = (string) Str::uuid();
        
        // УБИРАЕМ ВСЕ ОГРАНИЧЕНИЯ
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');
        @ini_set('display_errors', '0');
        
        Log::info("🚀 Начало БЫСТРОГО импорта в очередь", [
            'session' => $this->importSessionId,
            'user_id' => $userId,
            'bot_id' => $botId
        ]);
    }

    /**
     * Обработка коллекции строк
     * Просто складываем в БД без обработки
     */
    public function collection(Collection $rows)
    {
        $chunkNumber = ceil(self::$totalImported / $this->chunkSize());
        
        Log::info("📦 Импорт чанка #{$chunkNumber}", [
            'session' => $this->importSessionId,
            'rows' => $rows->count()
        ]);

        $dataToInsert = [];

        foreach ($rows as $index => $row) {
            try {
                // Пропускаем пустые строки
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Просто сохраняем данные в массив для bulk insert
                $dataToInsert[] = [
                    'session_id' => $this->importSessionId,
                    'user_id' => $this->userId,
                    'telegram_bot_id' => $this->botId,
                    'row_data' => json_encode($row->toArray()),
                    'update_existing' => $this->updateExisting,
                    'download_images' => $this->downloadImages,
                    'status' => 'pending',
                    'attempts' => 0,
                    'max_attempts' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                self::$totalImported++;

            } catch (\Throwable $e) {
                Log::error("❌ Ошибка добавления строки в очередь", [
                    'session' => $this->importSessionId,
                    'row' => $index,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Bulk insert - ОЧЕНЬ БЫСТРО!
        if (!empty($dataToInsert)) {
            ImportQueue::insert($dataToInsert);
            
            Log::info("✅ Вставлено в очередь: " . count($dataToInsert), [
                'session' => $this->importSessionId,
                'total' => self::$totalImported
            ]);
        }

        // Очищаем память после обработки чанка
        unset($rows, $dataToInsert);
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Размер чанка (читаем по 500 строк - можно больше т.к. только сохраняем в БД)
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Проверка пустой строки
     */
    private function isEmptyRow($row): bool
    {
        $values = array_filter($row->toArray(), function($value) {
            return !empty(trim($value));
        });
        
        return empty($values);
    }

    /**
     * Получить ID сессии импорта
     */
    public function getImportSessionId(): string
    {
        return $this->importSessionId;
    }

    /**
     * Получить статистику
     */
    public static function getTotalImported(): int
    {
        return self::$totalImported;
    }

    /**
     * Сбросить счётчик
     */
    public static function resetCounter(): void
    {
        self::$totalImported = 0;
    }
}
