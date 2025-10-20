<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class YandexImageProxyController extends Controller
{
    /**
     * Прокси для изображений из Яндекс.Диска
     */
    public function proxy(Request $request)
    {
        $url = $request->query('url');
        
        if (!$url) {
            return response('URL не указан', 400);
        }

        // Проверяем, что URL действительно от Яндекс.Диска
        if (!str_contains($url, 'downloader.disk.yandex.ru')) {
            return response('Недопустимый URL', 403);
        }

        try {
            // Кешируем изображения на 1 час
            $cacheKey = 'yandex_image_' . md5($url);
            
            $imageData = Cache::remember($cacheKey, 3600, function () use ($url) {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Referer' => 'https://disk.yandex.ru/',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    Log::error('Failed to fetch Yandex image', [
                        'url' => $url,
                        'status' => $response->status()
                    ]);
                    return null;
                }

                return [
                    'body' => $response->body(),
                    'content_type' => $response->header('Content-Type') ?? 'image/jpeg'
                ];
            });

            if (!$imageData) {
                return response('Изображение не найдено', 404);
            }

            return response($imageData['body'])
                ->header('Content-Type', $imageData['content_type'])
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            Log::error('Error proxying Yandex image', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return response('Ошибка загрузки изображения', 500);
        }
    }
}