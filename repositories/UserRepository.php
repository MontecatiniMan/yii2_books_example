<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\User;
use app\repositories\interfaces\UserRepositoryInterface;
use Yii;
use yii\base\Exception;

class UserRepository implements UserRepositoryInterface
{
    public function save(User $user): bool
    {
        return $user->save();
    }
    
    public function findById(int $id): ?User
    {
        return User::findOne(['id' => $id, 'status' => User::STATUS_ACTIVE]);
    }
    
    public function findByUsername(string $username): ?User
    {
        return User::findOne(['username' => $username, 'status' => User::STATUS_ACTIVE]);
    }
    
    /**
     * @throws Exception
     */
    public function create(string $username, string $password, string $email): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        
        if (!$user->save()) {
            throw new Exception('Не удалось создать пользователя: ' . implode(', ', $user->getFirstErrors()));
        }
        
        return $user;
    }
} 