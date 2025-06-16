<?php
/**
 * Скрипт инициализации тестовой базы данных
 */

define('YII_ENV', 'test');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../../config/console.php';
$config['components']['db'] = require __DIR__ . '/../../config/test_db.php';

// Создаем консольное приложение
$app = new yii\console\Application($config);

echo "Инициализация тестовой базы данных...\n";

try {
    // Проверяем подключение к базе данных
    $db = Yii::$app->db;
    $db->open();
    echo "✓ Подключение к тестовой БД установлено\n";
    
    // Очищаем существующие таблицы
    echo "Очищаем существующие таблицы...\n";
    $tables = ['author_subscriptions', 'book_author', 'books', 'authors', 'user', 'migration'];
    foreach ($tables as $table) {
        try {
            $db->createCommand("DROP TABLE IF EXISTS `{$table}`")->execute();
            echo "  ✓ Таблица {$table} удалена\n";
        } catch (Exception $e) {
            echo "  - Таблица {$table} не существует\n";
        }
    }
    
    // Применяем миграции
    echo "\nПрименяем миграции...\n";
    
    // Создаем таблицу миграций
    $db->createCommand('CREATE TABLE `migration` (
        `version` VARCHAR(180) NOT NULL PRIMARY KEY,
        `apply_time` INT(11)
    )')->execute();
    echo "  ✓ Создана таблица migration\n";
    
    // Запускаем миграции через Yii
    $migrationController = new \yii\console\controllers\MigrateController('migrate', $app);
    $migrationController->interactive = false;
    $migrationController->migrationPath = '@app/migrations';
    
    // Применяем все миграции
    $exitCode = $migrationController->runAction('up');
    
    if ($exitCode === 0) {
        echo "✓ Все миграции применены успешно\n";
    } else {
        throw new Exception("Ошибка применения миграций, код: {$exitCode}");
    }
    
    echo "\n✓ Тестовая база данных инициализирована успешно\n";
    echo "✓ Готово для запуска тестов!\n\n";
    echo "Запустите тесты командой: php vendor/bin/codecept run unit\n";
    
} catch (Exception $e) {
    echo "✗ Ошибка инициализации тестовой БД: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 