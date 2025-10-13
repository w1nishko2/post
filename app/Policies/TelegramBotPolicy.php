<?php

namespace App\Policies;

use App\Models\TelegramBot;
use App\Models\User;

class TelegramBotPolicy
{
    /**
     * Определить, может ли пользователь просматривать любые боты
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Определить, может ли пользователь просматривать бота
     */
    public function view(User $user, TelegramBot $telegramBot): bool
    {
        return $user->id === $telegramBot->user_id;
    }

    /**
     * Определить, может ли пользователь создавать ботов
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Определить, может ли пользователь обновлять бота
     */
    public function update(User $user, TelegramBot $telegramBot): bool
    {
        return $user->id === $telegramBot->user_id;
    }

    /**
     * Определить, может ли пользователь удалять бота
     */
    public function delete(User $user, TelegramBot $telegramBot): bool
    {
        return $user->id === $telegramBot->user_id;
    }

    /**
     * Определить, может ли пользователь восстанавливать бота
     */
    public function restore(User $user, TelegramBot $telegramBot): bool
    {
        return $user->id === $telegramBot->user_id;
    }

    /**
     * Определить, может ли пользователь принудительно удалять бота
     */
    public function forceDelete(User $user, TelegramBot $telegramBot): bool
    {
        return $user->id === $telegramBot->user_id;
    }
}