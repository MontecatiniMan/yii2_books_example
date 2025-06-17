<?php

use yii\db\Migration;
use yii\console\Application;

/**
 * Инициализация RBAC системы
 * Создает роль user с полными CRUD правами
 * Гости имеют ограниченные права без роли в БД
 */
class m250617_051615_init_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Применяем стандартные RBAC миграции Yii2
        $this->executeRbacMigrations();
        
        $auth = Yii::$app->authManager;

        // Создаем разрешения
        $viewBooks = $auth->createPermission('viewBooks');
        $viewBooks->description = 'Просмотр книг';
        $auth->add($viewBooks);

        $viewAuthors = $auth->createPermission('viewAuthors');
        $viewAuthors->description = 'Просмотр авторов';
        $auth->add($viewAuthors);

        $subscribeToAuthor = $auth->createPermission('subscribeToAuthor');
        $subscribeToAuthor->description = 'Подписка на автора';
        $auth->add($subscribeToAuthor);

        $manageBooks = $auth->createPermission('manageBooks');
        $manageBooks->description = 'Управление книгами (создание, редактирование, удаление)';
        $auth->add($manageBooks);

        $manageAuthors = $auth->createPermission('manageAuthors');
        $manageAuthors->description = 'Управление авторами (создание, редактирование, удаление)';
        $auth->add($manageAuthors);

        $viewReports = $auth->createPermission('viewReports');
        $viewReports->description = 'Просмотр отчетов';
        $auth->add($viewReports);

        // Создаем роль только для авторизованных пользователей
        $user = $auth->createRole('user');
        $user->description = 'Авторизованный пользователь с полными правами CRUD';
        $auth->add($user);

        // Назначаем разрешения роли user (полный CRUD согласно ТЗ)
        $auth->addChild($user, $viewBooks);
        $auth->addChild($user, $viewAuthors);
        $auth->addChild($user, $subscribeToAuthor);
        $auth->addChild($user, $viewReports);
        $auth->addChild($user, $manageBooks);
        $auth->addChild($user, $manageAuthors);

        // Роль user назначается автоматически всем авторизованным пользователям
        // через UserService при создании пользователя
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();
        
        // Откатываем стандартные RBAC миграции
        $this->rollbackRbacMigrations();
    }

    /**
     * Применяет стандартные RBAC миграции Yii2
     */
    private function executeRbacMigrations(): void
    {
        $migrationPath = Yii::getAlias('@yii/rbac/migrations');
        $migrations = [
            'm140506_102106_rbac_init',
            'm170907_052038_rbac_add_index_on_auth_assignment_user_id',
            'm180523_151638_rbac_updates_indexes_without_prefix',
            'm200409_110543_rbac_update_mssql_trigger'
        ];

        foreach ($migrations as $migration) {
            $migrationFile = $migrationPath . DIRECTORY_SEPARATOR . $migration . '.php';
            if (file_exists($migrationFile)) {
                require_once $migrationFile;
                $migrationClass = new $migration();
                $migrationClass->up();
                
                // Записываем в историю миграций
                $this->insert('{{%migration}}', [
                    'version' => $migration,
                    'apply_time' => time()
                ]);
            }
        }
    }

    /**
     * Откатывает стандартные RBAC миграции Yii2
     */
    private function rollbackRbacMigrations(): void
    {
        $migrations = [
            'm200409_110543_rbac_update_mssql_trigger',
            'm180523_151638_rbac_updates_indexes_without_prefix',
            'm170907_052038_rbac_add_index_on_auth_assignment_user_id',
            'm140506_102106_rbac_init'
        ];

        foreach ($migrations as $migration) {
            $migrationPath = Yii::getAlias('@yii/rbac/migrations');
            $migrationFile = $migrationPath . DIRECTORY_SEPARATOR . $migration . '.php';
            if (file_exists($migrationFile)) {
                require_once $migrationFile;
                $migrationClass = new $migration();
                $migrationClass->down();
                
                // Удаляем из истории миграций
                $this->delete('{{%migration}}', ['version' => $migration]);
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250617_051615_init_rbac cannot be reverted.\n";

        return false;
    }
    */
}
