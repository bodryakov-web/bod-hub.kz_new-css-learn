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











// Определение окружения (для хостинга всегда production)
// Для локальной разработки через Docker раскомментируйте следующую строку
define('ENVIRONMENT', 'development');






// Настройки вывода ошибок в зависимости от окружения
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 0);
} else {
    // Production настройки для хостинга
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
}

// Установка временной зоны
date_default_timezone_set('Asia/Almaty');

// Параметры подключения к базе данных и приложения
// Определяются в зависимости от окружения
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    // Настройки для Docker разработки
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'db');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'p-351366_new-css-learn');
    define('DB_USER', $_ENV['DB_USER'] ?? 'p-351366_new-css-learn');
    define('DB_PASS', $_ENV['DB_PASS'] ?? 'Anna-140275');
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8080');
} else {
    // Настройки для PRODUCTION хостинга
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'p-351366_new-css-learn');
    define('DB_USER', 'p-351366_new-css-learn');
    define('DB_PASS', 'Anna-140275'); // Замените на реальный пароль от БД
    define('APP_URL', 'https://bod-hub.kz/new-css-learn'); 
}

// Параметры базы данных
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME', 'NewCSSLearn');
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
