<?php

declare(strict_types=1);

namespace tests\unit\repositories;

use app\models\User;
use app\repositories\UserRepository;
use Codeception\Test\Unit;
use tests\fixtures\UserFixture;
use Throwable;
use yii\base\Exception;

class UserRepositoryTest extends Unit
{
    private UserRepository $userRepository;

    public function _fixtures(): array
    {
        return [
            'users' => UserFixture::class,
        ];
    }

    protected function _before(): void
    {
        parent::_before();
        $this->userRepository = new UserRepository();
    }

    public function testFindById(): void
    {
        $user = $this->userRepository->findById(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('admin', $user->username);
    }

    public function testFindByIdNotFound(): void
    {
        $user = $this->userRepository->findById(999);

        $this->assertNull($user);
    }

    public function testFindByUsername(): void
    {
        $user = $this->userRepository->findByUsername('admin');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->username);
        $this->assertEquals('admin@example.com', $user->email);
    }

    public function testFindByUsernameNotFound(): void
    {
        $user = $this->userRepository->findByUsername('nonexistent');

        $this->assertNull($user);
    }

    /**
     * @throws Exception
     */
    public function testCreateUser(): void
    {
        $user = $this->userRepository->create('newuser', 'password123', 'new@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('newuser', $user->username);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->id);
        $this->assertNotEmpty($user->password_hash);
        $this->assertNotEmpty($user->auth_key);
    }

    /**
     * @throws Exception
     */
    public function testCreateUserWithDuplicateUsername(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Не удалось создать пользователя');

        $this->userRepository->create('admin', 'password123', 'another@example.com');
    }

    /**
     * @throws Exception
     */
    public function testCreateUserWithDuplicateEmail(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Не удалось создать пользователя');

        $this->userRepository->create('newuser2', 'password123', 'admin@example.com');
    }

    public function testSaveExistingUser(): void
    {
        $user = $this->userRepository->findById(1);
        $user->email = 'updated@example.com';

        $result = $this->userRepository->save($user);

        $this->assertTrue($result);
        
        // Проверяем, что изменения сохранились
        $updatedUser = $this->userRepository->findById(1);
        $this->assertEquals('updated@example.com', $updatedUser->email);
    }
} 