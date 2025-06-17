<?php

declare(strict_types=1);

namespace app\commands;

use app\services\interfaces\UserServiceInterface;
use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Команда для создания пользователей
 */
class CreateUserController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly UserServiceInterface $userService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Создает нового пользователя с автоматическим назначением роли 'user'
     * 
     * @param string $username Имя пользователя
     * @param string $password Пароль
     * @param string $email Email
     * @return int
     */
    public function actionIndex(string $username, string $password, string $email): int
    {
        try {
            $user = $this->userService->createUser($username, $password, $email);
            
            $this->stdout("Пользователь успешно создан:\n");
            $this->stdout("ID: {$user->id}\n");
            $this->stdout("Имя пользователя: {$user->username}\n");
            $this->stdout("Email: {$user->email}\n");
            $this->stdout("Роль 'user' автоматически назначена\n");
            
            return ExitCode::OK;
        } catch (Throwable $th) {
            $this->stderr("Ошибка создания пользователя: {$th->getMessage()}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
} 