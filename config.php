<?php
/**
 * Конфигурационный файл NewCSSLearn
 * Содержит параметры подключения к базе данных и константы приложения
 */

// Предотвращение прямого доступа к файлу
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Установка кодировки для корректной работы с кириллицей
mb_internal_encoding('UTF-8');

// Настройки вывода ошибок (для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Установка временной зоны
date_default_timezone_set('Europe/Moscow');

// Параметры подключения к базе данных
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'p-351366_php-docker');
define('DB_USER', $_ENV['DB_USER'] ?? 'p-351366_php-docker');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'Anna-140275');
define('DB_CHARSET', 'utf8mb4');

// Параметры приложения
define('APP_NAME', 'NewCSSLearn');
define('APP_URL', 'http://localhost:8080');
define('UPLOADS_PATH', __DIR__ . '/uploads/lessons/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Параметры администратора (зашиты в коде согласно ТЗ)
define('ADMIN_LOGIN', 'bodryakov.web');
define('ADMIN_PASSWORD', 'Anna-140275');

// Константы путей
define('ROOT_PATH', __DIR__);
define('TEMPLATES_PATH', __DIR__ . '/templates/');
define('ADMIN_TEMPLATES_PATH', __DIR__ . '/templates/admin/');
define('ASSETS_PATH', __DIR__ . '/assets/');

// Допустимые форматы изображений
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/webp',
    'image/svg+xml'
]);

// Допустимые расширения файлов
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
?>
