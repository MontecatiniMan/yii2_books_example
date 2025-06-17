<?php

declare(strict_types=1);

namespace app\services\interfaces;

use app\models\User;

interface UserServiceInterface
{
    /**
     * Создает нового пользователя с автоматическим назначением роли
     */
    public function createUser(string $username, string $password, string $email): User;
    
    /**
     * Назначает роль 'user' пользователю
     */
    public function assignUserRole(int $userId): bool;
    
    /**
     * Получает пользователя по ID
     */
    public function getUser(int $id): ?User;
    
    /**
     * Получает пользователя по имени пользователя
     */
    public function getUserByUsername(string $username): ?User;
} 