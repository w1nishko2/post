<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexDiskService
{
    private const API_BASE_URL = 'https://cloud-api.yandex.net/v1/disk';
    
    /**
     * Получить список файлов из публичной папки Яндекс.Диска
     */
    public function getPublicFolderFiles(string $publicKey): array
    {
        try {
            // Сначала получаем информацию о папке без ограничений по полям
            $response = Http::timeout(30)->get(self::API_BASE_URL . '/public/resources', [
                'public_key' => "https://disk.yandex.ru/d/{$publicKey}",
                'limit' => 100
            ]);

            if (!$response->successful()) {
                Log::error('Yandex Disk API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'public_key' => $publicKey
                ]);
                return [];
            }

            $data = $response->json();
            Log::info('Yandex Disk API response', ['data' => $data]);
            
            $items = $data['_embedded']['items'] ?? [];
            
            if (empty($items)) {
                Log::warning('No items found in Yandex Disk folder', [
                    'public_key' => $publicKey,
                    'response_structure' => array_keys($data)
                ]);
                return [];
            }
            
            // Фильтруем только изображения
            $images = array_filter($items, function ($item) {
                $mediaType = $item['media_type'] ?? '';
                $mimeType = $item['mime_type'] ?? '';
                $name = $item['name'] ?? '';
                
                // Проверяем по media_type, mime_type или расширению файла
                $isImage = str_starts_with($mediaType, 'image/') || 
                          str_starts_with($mimeType, 'image/') ||
                          preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $name);
                
                return $isImage && isset($item['file']);
            });

            Log::info('Filtered images', [
                'total_items' => count($items),
                'filtered_images' => count($images)
            ]);

            // Возвращаем массив с информацией о изображениях
            return array_map(function ($item) {
                $previewUrl = $item['preview'] ?? null;
                $fileUrl = $item['file'];
                
                return [
                    'name' => $item['name'],
                    'url' => $fileUrl, // Оригинальный URL для сохранения
                    'preview' => $previewUrl,
                    'media_type' => $item['media_type'] ?? $item['mime_type'] ?? null,
                    // URL для отображения через прокси
                    'display_url' => $previewUrl ?: $fileUrl, // Используем preview или основной файл
                    'proxy_url' => url('/api/yandex-image-proxy?url=' . urlencode($previewUrl ?: $fileUrl))
                ];
            }, array_values($images));

        } catch (\Exception $e) {
            Log::error('Error fetching Yandex Disk folder', [
                'public_key' => $publicKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Извлечь публичный ключ из URL Яндекс.Диска
     */
    public function extractPublicKey(string $url): ?string
    {
        // Поддерживаем различные форматы URL
        $patterns = [
            // Папки
            '/disk\.yandex\.ru\/d\/([a-zA-Z0-9_-]+)/',
            '/disk\.yandex\.com\/d\/([a-zA-Z0-9_-]+)/',
            '/yadi\.sk\/d\/([a-zA-Z0-9_-]+)/',
            // Отдельные файлы
            '/disk\.yandex\.ru\/i\/([a-zA-Z0-9_-]+)/',
            '/disk\.yandex\.com\/i\/([a-zA-Z0-9_-]+)/',
            '/yadi\.sk\/i\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Определить тип ссылки Яндекс.Диска (папка или файл)
     */
    public function getLinkType(string $url): ?string
    {
        if (preg_match('/disk\.yandex\.(ru|com)\/d\//', $url) || preg_match('/yadi\.sk\/d\//', $url)) {
            return 'folder';
        }
        
        if (preg_match('/disk\.yandex\.(ru|com)\/i\//', $url) || preg_match('/yadi\.sk\/i\//', $url)) {
            return 'file';
        }
        
        return null;
    }

    /**
     * Получить информацию о публичном файле
     */
    public function getPublicFileInfo(string $publicKey): ?array
    {
        try {
            $response = Http::timeout(30)->get(self::API_BASE_URL . '/public/resources', [
                'public_key' => "https://disk.yandex.ru/i/{$publicKey}"
            ]);

            if (!$response->successful()) {
                Log::error('Yandex Disk file API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'public_key' => $publicKey
                ]);
                return null;
            }

            $data = $response->json();
            
            // Проверяем, что это изображение
            $mediaType = $data['media_type'] ?? '';
            $mimeType = $data['mime_type'] ?? '';
            $name = $data['name'] ?? '';
            
            $isImage = str_starts_with($mediaType, 'image/') || 
                      str_starts_with($mimeType, 'image/') ||
                      preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $name);
            
            if (!$isImage || !isset($data['file'])) {
                return null;
            }

            return [
                'name' => $data['name'],
                'url' => $data['file'],
                'preview' => $data['preview'] ?? null,
                'media_type' => $mediaType ?: $mimeType,
                'display_url' => $data['preview'] ? url('/api/yandex-image-proxy?url=' . urlencode($data['preview'])) : url('/api/yandex-image-proxy?url=' . urlencode($data['file']))
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching Yandex Disk file', [
                'public_key' => $publicKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Проверить валидность URL папки Яндекс.Диска
     */
    public function validateFolderUrl(string $url): bool
    {
        $publicKey = $this->extractPublicKey($url);
        if (!$publicKey) {
            return false;
        }

        try {
            $response = Http::get(self::API_BASE_URL . '/public/resources', [
                'public_key' => "https://disk.yandex.ru/d/{$publicKey}",
                'fields' => 'type,name'
            ]);

            return $response->successful() && 
                   $response->json('type') === 'dir';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получить актуальные ссылки на изображения для отображения
     */
    public function getActualImageUrls(string $publicKey): array
    {
        $files = $this->getPublicFolderFiles($publicKey);
        
        return array_map(function ($file) {
            // Возвращаем превью для отображения, так как они более стабильны
            return [
                'name' => $file['name'],
                'url' => $file['url'],
                'display_url' => $file['preview'] ?? $file['url']
            ];
        }, $files);
    }

    /**
     * Получить прямые ссылки на изображения для отображения
     */
    public function getImageUrls(string $publicKey): array
    {
        $files = $this->getPublicFolderFiles($publicKey);
        
        return array_map(function ($file) {
            return $file['url'];
        }, $files);
    }

    /**
     * Получить информацию о папке
     */
    public function getFolderInfo(string $publicKey): ?array
    {
        try {
            $response = Http::timeout(30)->get(self::API_BASE_URL . '/public/resources', [
                'public_key' => "https://disk.yandex.ru/d/{$publicKey}"
            ]);

            if (!$response->successful()) {
                Log::error('Error getting folder info', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'public_key' => $publicKey
                ]);
                return null;
            }

            $data = $response->json();
            Log::info('Folder info response', ['data' => $data]);
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Error getting Yandex Disk folder info', [
                'public_key' => $publicKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}