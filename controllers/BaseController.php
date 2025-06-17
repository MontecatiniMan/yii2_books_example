<?php

declare(strict_types=1);

namespace app\controllers;

use Throwable;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Базовый контроллер с RBAC функционалом
 */
abstract class BaseController extends Controller
{
    /**
     * Проверяет разрешение пользователя
     * 
     * @param string $permission Название разрешения
     * @param array $params Дополнительные параметры
     * @return bool
     */
    protected function checkAccess(string $permission, array $params = []): bool
    {
        // Для гостей разрешены только базовые действия
        if (Yii::$app->user->isGuest) {
            $guestPermissions = [
                'viewBooks',
                'viewAuthors', 
                'subscribeToAuthor',
                'viewReports' // Отчеты доступны всем
            ];
            return in_array($permission, $guestPermissions);
        }
        
        return Yii::$app->user->can($permission, $params);
    }

    /**
     * Требует разрешение или выбрасывает исключение
     * 
     * @param string $permission Название разрешения
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException
     */
    protected function requirePermission(string $permission, array $params = []): void
    {
        if (!$this->checkAccess($permission, $params)) {
            throw new ForbiddenHttpException('У вас нет прав для выполнения этого действия.');
        }
    }

    /**
     * Назначает роль пользователю (только для админов)
     * 
     * @param string $role Название роли
     * @param int $userId ID пользователя
     * @return bool
     */
    protected function assignRole(string $role, int $userId): bool
    {
        if (!$this->checkAccess('manageUsers')) {
            return false;
        }

        $auth = Yii::$app->authManager;
        $roleObject = $auth->getRole($role);
        
        if (!$roleObject) {
            return false;
        }

        try {
            $auth->assign($roleObject, $userId);
            return true;
        } catch (Throwable $th) {
            Yii::error("Ошибка при назначении роли {$role} пользователю {$userId}: " . $th->getMessage());
            return false;
        }
    }

    /**
     * Получает роли текущего пользователя
     * 
     * @return array
     */
    protected function getCurrentUserRoles(): array
    {
        if (Yii::$app->user->isGuest) {
            return ['guest']; // Виртуальная роль для удобства
        }

        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser(Yii::$app->user->id);
        
        return array_keys($roles);
    }

    /**
     * Проверяет, является ли пользователь администратором
     * 
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->checkAccess('manageBooks') || $this->checkAccess('manageAuthors');
    }
} 