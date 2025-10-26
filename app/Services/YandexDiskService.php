<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Сервис для работы с публичными ресурсами Яндекс.Диска
 */
class YandexDiskService
{
    private Client $client;
    private const API_URL = 'https://cloud-api.yandex.net/v1/disk/public/resources';
    private const TIMEOUT = 15; // секунд
    private const MAX_FILES = 100; // максимум файлов для получения

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => self::TIMEOUT,
            'connect_timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Laravel-Product-Import/1.0',
            ],
        ]);
    }

    /**
     * Проверяет, является ли URL ссылкой на Яндекс.Диск
     */
    public function isYandexDiskUrl(string $url): bool
    {
        return strpos($url, 'disk.yandex.ru') !== false || 
               strpos($url, 'yadi.sk') !== false;
    }

    /**
     * Получить список изображений из публичной папки/альбома или один файл
     * 
     * @param string $publicUrl Публичная ссылка на папку или файл
     * @param int $limit Максимальное количество файлов (до 5 для товара)
     * @return array Массив прямых ссылок на скачивание изображений
     * @throws Exception
     */
    public function getImageUrlsFromPublicFolder(string $publicUrl, int $limit = 5): array
    {
        try {
            Log::info('Запрос к Яндекс.Диску', ['url' => $publicUrl]);

            // Проверяем, не является ли ссылка альбомом (/a/)
            if (strpos($publicUrl, '/a/') !== false) {
                Log::warning('Обнаружена ссылка на альбом (/a/)', ['url' => $publicUrl]);
                throw new Exception('Ссылки на альбомы Яндекс.Диска (/a/) не поддерживаются API. Пожалуйста, используйте ссылки на папки (/d/) или отдельные файлы (/i/). Для создания папки: откройте альбом → Три точки → Переместить в папку → Создать папку → Поделиться папкой.');
            }

            // Получаем информацию о публичном ресурсе
            $response = $this->client->get(self::API_URL, [
                'query' => [
                    'public_key' => $publicUrl,
                    'limit' => self::MAX_FILES,
                    'fields' => 'type,name,mime_type,file,media_type,_embedded.items.name,_embedded.items.type,_embedded.items.mime_type,_embedded.items.file,_embedded.items.media_type,_embedded.items.sizes',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Проверяем тип ресурса
            if (isset($data['type']) && $data['type'] === 'file') {
                // Это одиночный файл, а не папка
                Log::info('Яндекс.Диск: обнаружен одиночный файл', [
                    'url' => $publicUrl,
                    'name' => $data['name'] ?? 'unknown',
                    'type' => $data['mime_type'] ?? 'unknown'
                ]);

                // Проверяем, что это изображение
                if (!$this->isImageFile($data)) {
                    throw new Exception('Ссылка указывает на файл, который не является изображением.');
                }

                // Получаем прямую ссылку на скачивание файла
                if (isset($data['file'])) {
                    Log::info('Найдено одно изображение в Яндекс.Диске', ['url' => $publicUrl]);
                    return [$data['file']];
                } else {
                    throw new Exception('Не удалось получить ссылку на скачивание файла.');
                }
            }

            // Это папка/альбом - получаем список файлов
            if (!isset($data['_embedded']['items'])) {
                throw new Exception('Не удалось получить список файлов из Яндекс.Диска. Возможно, ссылка не является публичной или папка пуста.');
            }

            $imageUrls = [];

            // Фильтруем только изображения
            foreach ($data['_embedded']['items'] as $item) {
                // Пропускаем папки
                if ($item['type'] === 'dir') {
                    continue;
                }

                // Проверяем, что это изображение
                if ($this->isImageFile($item)) {
                    // Пробуем получить прямую ссылку из поля 'file' (для альбомов)
                    if (isset($item['file'])) {
                        $imageUrls[] = $item['file'];
                        Log::info('Прямая ссылка из поля file', ['file' => $item['name']]);
                    } 
                    // Если нет поля 'file', пробуем через download API
                    else {
                        $downloadUrl = $this->getDirectDownloadUrl($publicUrl, $item['name']);
                        
                        if ($downloadUrl) {
                            $imageUrls[] = $downloadUrl;
                            Log::info('Ссылка через download API', ['file' => $item['name']]);
                        }
                    }

                    // Ограничиваем количество
                    if (count($imageUrls) >= $limit) {
                        break;
                    }
                }
            }

            Log::info('Найдено изображений в Яндекс.Диске', [
                'url' => $publicUrl,
                'count' => count($imageUrls),
            ]);

            if (empty($imageUrls)) {
                throw new Exception('В указанной папке Яндекс.Диска не найдено изображений.');
            }

            return $imageUrls;

        } catch (Exception $e) {
            Log::error('Ошибка при работе с Яндекс.Диском', [
                'url' => $publicUrl,
                'error' => $e->getMessage(),
            ]);
            
            throw new Exception('Не удалось получить изображения из Яндекс.Диска: ' . $e->getMessage());
        }
    }

    /**
     * Получить прямую ссылку на скачивание файла
     */
    private function getDirectDownloadUrl(string $publicUrl, string $fileName): ?string
    {
        try {
            // Получаем ссылку на скачивание через API
            $response = $this->client->get(self::API_URL . '/download', [
                'query' => [
                    'public_key' => $publicUrl,
                    'path' => '/' . $fileName,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['href'] ?? null;

        } catch (Exception $e) {
            Log::warning('Не удалось получить прямую ссылку на файл', [
                'fileName' => $fileName,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Проверяет, является ли файл изображением
     */
    private function isImageFile(array $item): bool
    {
        // Проверяем по MIME типу
        if (isset($item['mime_type'])) {
            if (strpos($item['mime_type'], 'image/') === 0) {
                return true;
            }
        }

        // Проверяем по media_type
        if (isset($item['media_type']) && $item['media_type'] === 'image') {
            return true;
        }

        // Проверяем по расширению файла
        if (isset($item['name'])) {
            $extension = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'heic', 'heif', 'avif'];
            
            return in_array($extension, $imageExtensions);
        }

        return false;
    }

    /**
     * Нормализовать публичную ссылку Яндекс.Диска
     * (обрабатывает короткие ссылки yadi.sk и полные disk.yandex.ru)
     */
    public function normalizePublicUrl(string $url): string
    {
        // Убираем параметры запроса, которые могут мешать
        $url = strtok($url, '#'); // Убираем якорь
        
        return trim($url);
    }

    /**
     * Получить информацию о публичном ресурсе
     */
    public function getResourceInfo(string $publicUrl): ?array
    {
        try {
            $response = $this->client->get(self::API_URL, [
                'query' => [
                    'public_key' => $publicUrl,
                    'fields' => 'name,type,size,created,modified,public_url',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (Exception $e) {
            Log::error('Ошибка получения информации о ресурсе', [
                'url' => $publicUrl,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }
}
