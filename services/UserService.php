<?php

declare(strict_types=1);

namespace app\services;

use app\models\User;
use app\repositories\interfaces\UserRepositoryInterface;
use app\services\interfaces\UserServiceInterface;
use Yii;
use yii\base\Exception;
use yii\db\Transaction;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}
    
    /**
     * @throws Exception
     */
    public function createUser(string $username, string $password, string $email): User
    {
        return Yii::$app->db->transaction(function () use ($username, $password, $email) {
            // Создаем пользователя через репозиторий
            $user = $this->userRepository->create($username, $password, $email);
            
            // Назначаем роль 'user' - это бизнес-логика
            $this->assignUserRole($user->id);
            
            return $user;
        });
    }
    
    public function assignUserRole(int $userId): bool
    {
        $auth = Yii::$app->authManager;
        $userRole = $auth->getRole('user');
        
        if (!$userRole) {
            Yii::warning("Роль 'user' не найдена в системе RBAC", __METHOD__);
            return false;
        }
        
        // Проверяем, не назначена ли уже роль
        if ($auth->getAssignment('user', $userId)) {
            return true; // Роль уже назначена
        }
        
        try {
            $auth->assign($userRole, $userId);
            return true;
        } catch (\Exception $e) {
            Yii::error("Ошибка назначения роли пользователю {$userId}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
    
    public function getUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
    
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }
} 