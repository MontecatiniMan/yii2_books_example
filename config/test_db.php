<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases

// Используем настройки из docker-compose.yml для тестовой БД
$db['dsn'] = $_ENV['TEST_DB_DSN'] ?? 'mysql:host=mysql_test;dbname=book_catalog_test';
$db['username'] = $_ENV['TEST_DB_USERNAME'] ?? 'test_user';
$db['password'] = $_ENV['TEST_DB_PASSWORD'] ?? 'test_password';

return $db;
