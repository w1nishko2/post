<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnership
{
    /**
     * Проверить, что пользователь владеет указанным ресурсом.
     */
    public function handle(Request $request, Closure $next, string $resourceParam): Response
    {
        if (!Auth::check()) {
            abort(401, 'Необходима авторизация');
        }

        $resource = $request->route($resourceParam);
        
        if (!$resource) {
            abort(404, 'Ресурс не найден');
        }

        // Проверяем, что ресурс принадлежит текущему пользователю
        if (!$this->userOwnsResource($resource, Auth::id())) {
            abort(403, 'У вас нет доступа к этому ресурсу');
        }

        return $next($request);
    }

    /**
     * Проверить принадлежность ресурса пользователю.
     */
    private function userOwnsResource($resource, int $userId): bool
    {
        // Проверяем, что ресурс действительно является объектом
        if (!is_object($resource)) {
            return false;
        }

        // Если у ресурса есть поле user_id
        if (property_exists($resource, 'user_id') && isset($resource->user_id)) {
            return (int) $resource->user_id === $userId;
        }

        // Если у ресурса есть связь с пользователем через user()
        if (method_exists($resource, 'user')) {
            try {
                return $resource->user()->where('id', $userId)->exists();
            } catch (\Exception $e) {
                // Логируем ошибку и возвращаем false
                Log::error('Error checking user ownership: ' . $e->getMessage());
                return false;
            }
        }

        // Для особых случаев можно добавить специфичные проверки
        try {
            $resourceClass = get_class($resource);
            
            switch ($resourceClass) {
                case 'App\Models\TelegramBot':
                case 'App\Models\Product':
                case 'App\Models\Category':
                    return property_exists($resource, 'user_id') && (int) $resource->user_id === $userId;
                    
                default:
                    // По умолчанию запрещаем доступ к неизвестным ресурсам
                    return false;
            }
        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем false для безопасности
            Log::error('Error in userOwnsResource: ' . $e->getMessage());
            return false;
        }
    }
}