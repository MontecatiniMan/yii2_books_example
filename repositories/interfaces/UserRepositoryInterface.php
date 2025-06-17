<?php

declare(strict_types=1);

namespace app\repositories\interfaces;

use app\models\User;

interface UserRepositoryInterface
{
    /**
     * Сохраняет пользователя
     */
    public function save(User $user): bool;
    
    /**
     * Находит пользователя по ID
     */
    public function findById(int $id): ?User;
    
    /**
     * Находит пользователя по имени пользователя
     */
    public function findByUsername(string $username): ?User;
    
    /**
     * Создает нового пользователя
     */
    public function create(string $username, string $password, string $email): User;
} 