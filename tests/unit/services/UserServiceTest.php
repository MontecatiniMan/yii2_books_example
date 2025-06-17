<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\models\User;
use app\repositories\interfaces\UserRepositoryInterface;
use app\services\UserService;
use Codeception\Test\Unit;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\rbac\ManagerInterface;
use yii\rbac\Role;

class UserServiceTest extends Unit
{
    private UserService $userService;
    private UserRepositoryInterface $userRepository;
    private ManagerInterface $authManager;
    private Role $userRole;

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function _before(): void
    {
        parent::_before();
        
        // Мокаем репозиторий
        $this->userRepository = $this->makeEmpty(UserRepositoryInterface::class, [
            'create' => function (string $username, string $password, string $email) {
                $user = new User();
                $user->id = 1;
                $user->username = $username;
                $user->email = $email;
                $user->status = User::STATUS_ACTIVE;
                return $user;
            },
            'findById' => function (int $id) {
                if ($id === 1) {
                    $user = new User();
                    $user->id = 1;
                    $user->username = 'testuser';
                    $user->email = 'test@example.com';
                    return $user;
                }
                return null;
            },
            'findByUsername' => function (string $username) {
                if ($username === 'testuser') {
                    $user = new User();
                    $user->id = 1;
                    $user->username = 'testuser';
                    $user->email = 'test@example.com';
                    return $user;
                }
                return null;
            }
        ]);

        // Мокаем роль
        $this->userRole = $this->makeEmpty(Role::class, [
            'name' => 'user'
        ]);

        // Мокаем менеджер авторизации
        $this->authManager = $this->makeEmpty(ManagerInterface::class, [
            'getRole' => function (string $name) {
                return $name === 'user' ? $this->userRole : null;
            },
            'getAssignment' => function (string $roleName, $userId) {
                return null; // Роль не назначена
            },
            'assign' => function ($role, $userId) {
                return true;
            }
        ]);

        // Подменяем authManager в Yii
        Yii::$app->set('authManager', $this->authManager);

        $this->userService = new UserService($this->userRepository);
    }

    public function testCreateUserSuccess(): void
    {
        $user = $this->userService->createUser('testuser', 'password123', 'test@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals(1, $user->id);
    }

    public function testAssignUserRoleSuccess(): void
    {
        $result = $this->userService->assignUserRole(1);

        $this->assertTrue($result);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function testAssignUserRoleWhenRoleNotExists(): void
    {
        // Переопределяем мок для возврата null
        $authManager = $this->makeEmpty(ManagerInterface::class, [
            'getRole' => function (string $name) {
                return null; // Роль не найдена
            }
        ]);
        
        Yii::$app->set('authManager', $authManager);
        $userService = new UserService($this->userRepository);

        $result = $userService->assignUserRole(1);

        $this->assertFalse($result);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function testAssignUserRoleWhenAlreadyAssigned(): void
    {
        // Переопределяем мок для возврата существующего назначения
        $authManager = $this->makeEmpty(ManagerInterface::class, [
            'getRole' => function (string $name) {
                return $name === 'user' ? $this->userRole : null;
            },
            'getAssignment' => function (string $roleName, $userId) {
                return $this->userRole; // Роль уже назначена
            }
        ]);
        
        Yii::$app->set('authManager', $authManager);
        $userService = new UserService($this->userRepository);

        $result = $userService->assignUserRole(1);

        $this->assertTrue($result); // Должен вернуть true, так как роль уже назначена
    }

    public function testGetUser(): void
    {
        $user = $this->userService->getUser(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('testuser', $user->username);
    }

    public function testGetUserNotFound(): void
    {
        $user = $this->userService->getUser(999);

        $this->assertNull($user);
    }

    public function testGetUserByUsername(): void
    {
        $user = $this->userService->getUserByUsername('testuser');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testuser', $user->username);
    }

    public function testGetUserByUsernameNotFound(): void
    {
        $user = $this->userService->getUserByUsername('nonexistent');

        $this->assertNull($user);
    }
} 