<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Exception;

class ImageUploadService
{
    private ImageManager $manager;
    private const THUMBNAIL_SIZE = 200;
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/tiff',
        'image/x-tiff',
        'image/avif',
        'image/heic',
        'image/heif',
    ];

    public function __construct()
    {
        // Проверяем наличие Imagick для поддержки HEIC/HEIF форматов
        if (extension_loaded('imagick') && class_exists('\Imagick')) {
            $this->manager = new ImageManager(new ImagickDriver());
        } else {
            // Fallback на GD драйвер если Imagick недоступен
            $this->manager = new ImageManager(new GdDriver());
        }
    }

    /**
     * Загрузить и обработать изображение
     * 
     * @param UploadedFile $file
     * @param string $directory Директория для сохранения (например, 'products')
     * @return array ['file_path' => string, 'thumbnail_path' => string, 'original_name' => string, 'file_size' => int]
     * @throws Exception
     */
    public function upload(UploadedFile $file, string $directory = 'products'): array
    {
        // Валидация файла
        $this->validateFile($file);

        // Конвертация HEIC/HEIF если необходимо
        $processedFile = $this->convertHeicIfNeeded($file);

        // Генерируем уникальное имя файла
        $filename = $this->generateUniqueFileName();
        
        // Пути для сохранения
        $originalPath = "{$directory}/originals/{$filename}.webp";
        $thumbnailPath = "{$directory}/thumbnails/{$filename}.webp";

        // Обработка оригинального изображения
        $image = $this->manager->read($processedFile);
        
        // Сохраняем оригинал в WebP (с оптимизацией)
        $encodedOriginal = $image->toWebp(85); // 85% качество
        Storage::disk('public')->put($originalPath, (string) $encodedOriginal);

        // Создаем миниатюру 200x200
        $thumbnail = $this->manager->read($processedFile);
        $thumbnail->cover(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
        $encodedThumbnail = $thumbnail->toWebp(80); // 80% качество для миниатюры
        Storage::disk('public')->put($thumbnailPath, (string) $encodedThumbnail);

        // Очищаем временный файл если он был создан
        if ($processedFile !== $file->getRealPath()) {
            @unlink($processedFile);
        }

        return [
            'file_path' => $originalPath,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Загрузить множество изображений
     * 
     * @param array $files
     * @param string $directory
     * @return array
     */
    public function uploadMultiple(array $files, string $directory = 'products'): array
    {
        $results = [];
        
        foreach ($files as $file) {
            try {
                $results[] = array_merge(
                    $this->upload($file, $directory),
                    ['success' => true, 'error' => null]
                );
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'original_name' => $file->getClientOriginalName(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * Удалить изображение и его миниатюру
     * 
     * @param string $filePath
     * @param string $thumbnailPath
     * @return bool
     */
    public function delete(string $filePath, string $thumbnailPath): bool
    {
        $deleted = true;
        
        if (Storage::disk('public')->exists($filePath)) {
            $deleted = Storage::disk('public')->delete($filePath) && $deleted;
        }
        
        if (Storage::disk('public')->exists($thumbnailPath)) {
            $deleted = Storage::disk('public')->delete($thumbnailPath) && $deleted;
        }
        
        return $deleted;
    }

    /**
     * Валидация файла
     * 
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateFile(UploadedFile $file): void
    {
        // Проверка размера
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('Размер файла превышает максимально допустимый (10MB)');
        }

        // Проверка MIME типа
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new Exception("Неподдерживаемый тип файла: {$mimeType}");
        }

        // Дополнительная проверка расширения
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'avif', 'heic', 'heif'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Неподдерживаемое расширение файла: {$extension}");
        }
    }

    /**
     * Конвертировать HEIC/HEIF в JPEG если необходимо
     * 
     * @param UploadedFile $file
     * @return string Путь к обработанному файлу
     * @throws Exception
     */
    private function convertHeicIfNeeded(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Проверяем, является ли файл HEIC/HEIF
        $isHeic = in_array($mimeType, ['image/heic', 'image/heif']) || 
                  in_array($extension, ['heic', 'heif']);
        
        if (!$isHeic) {
            return $file->getRealPath();
        }
        // Проверяем, доступен ли Imagick для конвертации HEIC/HEIF
        if (!extension_loaded('imagick') || !class_exists('\Imagick')) {
            throw new Exception('Формат HEIC/HEIF не поддерживается на этом сервере. Расширение Imagick не установлено. Пожалуйста, конвертируйте изображение в JPEG или PNG перед загрузкой.');
        }
        // Intervention Image с Imagick драйвером автоматически обработает HEIC
        // Просто возвращаем путь к файлу - обработка будет в методе upload
        return $file->getRealPath();
    }
    /**
     * Генерировать уникальное имя файла
     * 
     * @return string
     */
    private function generateUniqueFileName(): string
    {
        return date('Y/m/d') . '/' . Str::random(40);
    }
    /**
     * Получить информацию о файле
     * 
     * @param string $path
     * @return array|null
     */
    public function getFileInfo(string $path): ?array
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return null;
        }
        return [
            'size' => $disk->size($path),
            'mime_type' => $disk->get($path) ? 'image/webp' : null,
            'last_modified' => $disk->lastModified($path),
            'url' => asset('storage/' . $path),
        ];
    }

    /**
     * Скачать изображение по URL и обработать как загруженный файл
     * 
     * @param string $url URL изображения
     * @param string $directory Директория для сохранения
     * @return array ['file_path', 'thumbnail_path', 'original_name', 'file_size']
     * @throws Exception
     */
    public function downloadFromUrl(string $url, string $directory = 'products'): array
    {
        // Валидация URL
        $this->validateImageUrl($url);

        // Скачиваем файл во временную директорию
        $tempPath = $this->downloadToTemp($url);

        try {
            // Извлекаем имя файла из URL (поддерживаем параметр filename=)
            $filename = $this->extractFilenameFromUrl($url);
            
            // Создаём UploadedFile из временного файла
            $uploadedFile = new UploadedFile(
                $tempPath,
                $filename,
                mime_content_type($tempPath),
                null,
                true
            );

            // Используем существующий метод upload
            $result = $this->upload($uploadedFile, $directory);

            // Удаляем временный файл
            @unlink($tempPath);

            return $result;

        } catch (Exception $e) {
            // Очищаем временный файл при ошибке
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Валидация URL изображения
     * 
     * @param string $url
     * @throws Exception
     */
    private function validateImageUrl(string $url): void
    {
        // Проверка формата URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Некорректный URL: {$url}");
        }

        // Разрешаем только http и https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            throw new Exception("Недопустимая схема URL. Разрешены только http и https.");
        }

        // Защита от SSRF - проверяем IP адрес
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            throw new Exception("Не удалось определить хост из URL");
        }

        // Резолвим IP
        $ip = gethostbyname($host);
        
        // Блокируем приватные и локальные IP
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new Exception("Запрещено скачивание с приватных/локальных адресов (защита от SSRF)");
        }
    }

    /**
     * Скачать файл по URL во временную директорию
     * 
     * @param string $url
     * @return string Путь к временному файлу
     * @throws Exception
     */
    private function downloadToTemp(string $url): string
    {
        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
            ],
            'verify' => true,
        ]);

        try {
            $response = $client->get($url, [
                'stream' => true,
                'on_headers' => function ($response) use ($url) {
                    // Проверяем размер файла из заголовков
                    $contentLength = $response->getHeader('Content-Length')[0] ?? null;
                    if ($contentLength && $contentLength > self::MAX_FILE_SIZE) {
                        throw new Exception("Размер файла превышает максимально допустимый (10MB)");
                    }

                    // Проверяем Content-Type
                    $contentType = $response->getHeader('Content-Type')[0] ?? '';
                    if (!$this->isImageContentType($contentType)) {
                        throw new Exception("Некорректный тип контента: {$contentType}. Ожидается изображение.");
                    }
                },
            ]);

            // Создаём временный файл
            $tempPath = tempnam(sys_get_temp_dir(), 'img_download_');
            if (!$tempPath) {
                throw new Exception("Не удалось создать временный файл");
            }

            // Записываем содержимое с контролем размера
            $body = $response->getBody();
            $handle = fopen($tempPath, 'w');
            $bytesWritten = 0;

            while (!$body->eof()) {
                $chunk = $body->read(8192);
                $bytesWritten += fwrite($handle, $chunk);

                // Проверяем размер во время записи
                if ($bytesWritten > self::MAX_FILE_SIZE) {
                    fclose($handle);
                    @unlink($tempPath);
                    throw new Exception("Размер скачиваемого файла превышает лимит (10MB)");
                }
            }

            fclose($handle);

            // Дополнительная проверка MIME через finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tempPath);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                @unlink($tempPath);
                throw new Exception("Неподдерживаемый MIME тип: {$mimeType}");
            }

            return $tempPath;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Ошибка при скачивании файла: " . $e->getMessage());
        }
    }

    /**
     * Проверка Content-Type на соответствие изображению
     */
    private function isImageContentType(string $contentType): bool
    {
        // Убираем параметры типа charset
        $contentType = strtok($contentType, ';');
        
        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/tiff',
            'image/x-tiff',
            'image/avif',
            'image/heic',
            'image/heif',
            'application/octet-stream', // Иногда сервера возвращают этот тип для изображений
        ];

        return in_array(strtolower(trim($contentType)), $allowedTypes);
    }

    /**
     * Извлечь имя файла из URL
     * Поддерживает параметр filename= в URL (для Яндекс.Диска и подобных сервисов)
     * 
     * @param string $url
     * @return string
     */
    private function extractFilenameFromUrl(string $url): string
    {
        // Пытаемся извлечь filename из параметров URL
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            parse_str($query, $params);
            if (isset($params['filename']) && !empty($params['filename'])) {
                // Декодируем URL-encoded имя файла
                return urldecode($params['filename']);
            }
        }

        // Если параметра filename нет, берем из пути URL
        $path = parse_url($url, PHP_URL_PATH);
        $filename = basename($path);
        
        // Если имя файла пустое или без расширения, возвращаем значение по умолчанию
        if (empty($filename) || !str_contains($filename, '.')) {
            return 'image.jpg';
        }

        return $filename;
    }
}
