<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductImageController extends Controller
{
    private ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
    }

    /**
     * Загрузить изображения для товара
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|file|image|max:10240|mimes:jpeg,jpg,png,gif,webp,bmp,tiff,tif,avif,heic,heif',
        ], [
            'images.required' => 'Необходимо выбрать хотя бы одно изображение',
            'images.max' => 'Максимальное количество изображений - 5',
            'images.*.max' => 'Размер файла не должен превышать 10MB',
            'images.*.mimes' => 'Поддерживаемые форматы: JPEG, PNG, GIF, WebP, BMP, TIFF, AVIF, HEIC/HEIF',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            
            // Проверка прав доступа
            if ($product->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для загрузки изображений к этому товару'
                ], 403);
            }

            // Проверка количества существующих изображений
            $existingImagesCount = $product->images()->count();
            $newImagesCount = count($request->file('images'));
            
            if ($existingImagesCount + $newImagesCount > 5) {
                return response()->json([
                    'success' => false,
                    'message' => "Максимальное количество изображений для товара - 5. У вас уже загружено {$existingImagesCount} изображений."
                ], 422);
            }

            $uploadedImages = [];
            $errors = [];
            $lastOrder = $product->images()->max('order') ?? -1;

            foreach ($request->file('images') as $index => $file) {
                try {
                    // Загружаем и обрабатываем изображение
                    $uploadData = $this->imageService->upload($file, 'products');
                    
                    // Создаем запись в БД
                    $image = $product->images()->create([
                        'file_path' => $uploadData['file_path'],
                        'thumbnail_path' => $uploadData['thumbnail_path'],
                        'original_name' => $uploadData['original_name'],
                        'file_size' => $uploadData['file_size'],
                        'is_main' => $existingImagesCount === 0 && $index === 0, // Первое изображение - главное
                        'order' => ++$lastOrder,
                    ]);

                    $uploadedImages[] = [
                        'id' => $image->id,
                        'url' => $image->url,
                        'thumbnail_url' => $image->thumbnail_url,
                        'is_main' => $image->is_main,
                        'order' => $image->order,
                        'original_name' => $image->original_name,
                    ];
                    
                    $existingImagesCount++;
                    
                } catch (Exception $e) {
                    Log::error('Ошибка загрузки изображения: ' . $e->getMessage(), [
                        'file' => $file->getClientOriginalName(),
                        'product_id' => $product->id,
                    ]);
                    
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'message' => $e->getMessage()
                    ];
                }
            }

            if (empty($uploadedImages) && !empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось загрузить изображения',
                    'errors' => $errors
                ], 500);
            }

            // Обновляем photos_gallery в таблице products
            $this->updateProductGallery($product);

            return response()->json([
                'success' => true,
                'message' => count($uploadedImages) > 1 
                    ? 'Изображения успешно загружены' 
                    : 'Изображение успешно загружено',
                'images' => $uploadedImages,
                'errors' => $errors,
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при загрузке изображений: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при загрузке изображений'
            ], 500);
        }
    }

    /**
     * Удалить изображение
     * 
     * @param int $imageId
     * @return JsonResponse
     */
    public function delete(int $imageId): JsonResponse
    {
        try {
            $image = ProductImage::findOrFail($imageId);
            
            // Проверка прав доступа
            if ($image->product->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для удаления этого изображения'
                ], 403);
            }

            $wasMain = $image->is_main;
            $productId = $image->product_id;
            
            // Удаляем изображение
            $image->deleteWithFiles();

            // Если удаленное изображение было главным, устанавливаем следующее как главное
            if ($wasMain) {
                $nextImage = ProductImage::where('product_id', $productId)
                    ->ordered()
                    ->first();
                    
                if ($nextImage) {
                    $nextImage->setAsMain();
                }
            }

            // Обновляем photos_gallery в таблице products
            $product = Product::find($productId);
            if ($product) {
                $this->updateProductGallery($product);
            }

            return response()->json([
                'success' => true,
                'message' => 'Изображение успешно удалено'
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при удалении изображения: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при удалении изображения'
            ], 500);
        }
    }

    /**
     * Установить изображение главным
     * 
     * @param int $imageId
     * @return JsonResponse
     */
    public function setMain(int $imageId): JsonResponse
    {
        Log::info('ProductImageController::setMain вызван', [
            'imageId' => $imageId,
            'userId' => Auth::id()
        ]);
        
        try {
            $image = ProductImage::findOrFail($imageId);
            
            Log::info('Изображение найдено', [
                'imageId' => $image->id,
                'productId' => $image->product_id,
                'productUserId' => $image->product->user_id
            ]);
            
            // Проверка прав доступа
            if ($image->product->user_id !== Auth::id()) {
                Log::warning('Отказано в доступе к изображению', [
                    'imageId' => $imageId,
                    'productUserId' => $image->product->user_id,
                    'currentUserId' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для изменения этого изображения'
                ], 403);
            }

            $result = $image->setAsMain();
            
            Log::info('Главное изображение установлено', [
                'imageId' => $imageId,
                'result' => $result
            ]);

            // Обновляем photos_gallery в таблице products
            $this->updateProductGallery($image->product);

            return response()->json([
                'success' => true,
                'message' => 'Главное изображение установлено'
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при установке главного изображения: ' . $e->getMessage(), [
                'imageId' => $imageId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при установке главного изображения'
            ], 500);
        }
    }

    /**
     * Обновить порядок изображений
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*.id' => 'required|exists:product_images,id',
            'images.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            foreach ($request->images as $imageData) {
                $image = ProductImage::findOrFail($imageData['id']);
                
                // Проверка прав доступа
                if ($image->product->user_id !== Auth::id()) {
                    continue;
                }
                
                $image->update(['order' => $imageData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Порядок изображений обновлен'
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при обновлении порядка изображений: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении порядка'
            ], 500);
        }
    }

    /**
     * Получить все изображения товара
     * 
     * @param int $productId
     * @return JsonResponse
     */
    public function index(int $productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Проверка прав доступа
            if ($product->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для просмотра изображений этого товара'
                ], 403);
            }

            $images = $product->images()->ordered()->get()->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'thumbnail_url' => $image->thumbnail_url,
                    'is_main' => $image->is_main,
                    'order' => $image->order,
                    'original_name' => $image->original_name,
                    'file_size' => $image->file_size,
                ];
            });

            return response()->json([
                'success' => true,
                'images' => $images
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при получении изображений: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении изображений'
            ], 500);
        }
    }

    /**
     * Обновить поле photos_gallery в таблице products на основе product_images
     * 
     * @param Product $product
     * @return void
     */
    private function updateProductGallery(Product $product): void
    {
        try {
            // Получаем все изображения товара отсортированные
            $images = $product->images()->ordered()->get();
            
            // Формируем массив URL для photos_gallery
            $photosGallery = $images->map(function($image) {
                return $image->url; // Полный публичный URL
            })->values()->toArray();
            
            // Находим индекс главного изображения
            $mainPhotoIndex = 0;
            foreach ($images as $index => $image) {
                if ($image->is_main) {
                    $mainPhotoIndex = $index;
                    break;
                }
            }
            
            // Обновляем товар
            $product->update([
                'photos_gallery' => $photosGallery,
                'main_photo_index' => $mainPhotoIndex,
                'photo_url' => $photosGallery[$mainPhotoIndex] ?? ($photosGallery[0] ?? null)
            ]);
            
            Log::info('Photos gallery updated for product', [
                'product_id' => $product->id,
                'photos_count' => count($photosGallery),
                'main_photo_index' => $mainPhotoIndex
            ]);
            
        } catch (Exception $e) {
            Log::error('Ошибка обновления photos_gallery: ' . $e->getMessage(), [
                'product_id' => $product->id
            ]);
        }
    }
}
