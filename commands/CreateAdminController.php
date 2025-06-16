<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Команда для создания администратора системы
 * 
 * Использование:
 * php yii create-admin [username] [password] [email]
 * 
 * Примеры:
 * php yii create-admin admin admin123 admin@example.com
 * php yii create-admin superuser mypassword user@domain.com
 * 
 * @author Система управления каталогом книг
 * @since 1.0
 */
class CreateAdminController extends Controller
{
    /**
     * Создает нового администратора в системе
     *
     * @return int Код завершения команды (0 - успешно, 1 - ошибка)
     *
     * Команда создает нового пользователя с административными правами.
     * Все параметры обязательны для заполнения.
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex(string $username, string $password, string $email): int
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        if ($user->save()) {
            $this->stdout("Администратор успешно создан!\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout("Ошибка при создании администратора:\n", Console::FG_RED);
        foreach ($user->errors as $errors) {
            foreach ($errors as $error) {
                $this->stdout("- $error\n", Console::FG_RED);
            }
        }
        return ExitCode::UNSPECIFIED_ERROR;
    }
} 