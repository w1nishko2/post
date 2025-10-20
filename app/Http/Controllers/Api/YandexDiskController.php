<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YandexDiskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class YandexDiskController extends Controller
{
    private YandexDiskService $yandexDiskService;

    public function __construct(YandexDiskService $yandexDiskService)
    {
        $this->yandexDiskService = $yandexDiskService;
    }

    /**
     * Получить список изображений из папки Яндекс.Диска
     */
    public function getFolderImages(Request $request): JsonResponse
    {
        $request->validate([
            'folder_url' => 'required|string|url'
        ]);

        $folderUrl = $request->input('folder_url');
        $publicKey = $this->yandexDiskService->extractPublicKey($folderUrl);

        if (!$publicKey) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректная ссылка на папку Яндекс.Диска'
            ], 400);
        }

        $images = $this->yandexDiskService->getPublicFolderFiles($publicKey);

        if (empty($images)) {
            return response()->json([
                'success' => false,
                'message' => 'В папке не найдено изображений или папка недоступна'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'public_key' => $publicKey,
                'folder_url' => $folderUrl,
                'images' => $images,
                'count' => count($images)
            ]
        ]);
    }

    /**
     * Проверить валидность ссылки на папку
     */
    public function validateFolderUrl(Request $request): JsonResponse
    {
        $request->validate([
            'folder_url' => 'required|string'
        ]);

        $folderUrl = $request->input('folder_url');
        $isValid = $this->yandexDiskService->validateFolderUrl($folderUrl);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Ссылка недействительна или папка недоступна'
            ], 400);
        }

        $publicKey = $this->yandexDiskService->extractPublicKey($folderUrl);
        $folderInfo = $this->yandexDiskService->getFolderInfo($publicKey);

        return response()->json([
            'success' => true,
            'data' => [
                'public_key' => $publicKey,
                'folder_info' => $folderInfo
            ]
        ]);
    }

    /**
     * Получить информацию об отдельном файле из Яндекс.Диска
     */
    public function getFileInfo(Request $request): JsonResponse
    {
        $request->validate([
            'file_url' => 'required|string|url'
        ]);

        $fileUrl = $request->input('file_url');
        $linkType = $this->yandexDiskService->getLinkType($fileUrl);
        
        if ($linkType !== 'file') {
            return response()->json([
                'success' => false,
                'message' => 'Это не ссылка на отдельный файл'
            ], 400);
        }

        $publicKey = $this->yandexDiskService->extractPublicKey($fileUrl);

        if (!$publicKey) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректная ссылка на файл Яндекс.Диска'
            ], 400);
        }

        $fileInfo = $this->yandexDiskService->getPublicFileInfo($publicKey);

        if (!$fileInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Файл не найден или не является изображением'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $fileInfo
        ]);
    }
    public function getImageUrls(Request $request): JsonResponse
    {
        $request->validate([
            'public_key' => 'required|string'
        ]);

        $publicKey = $request->input('public_key');
        $imageUrls = $this->yandexDiskService->getImageUrls($publicKey);

        return response()->json([
            'success' => true,
            'data' => [
                'urls' => $imageUrls,
                'count' => count($imageUrls)
            ]
        ]);
    }
}