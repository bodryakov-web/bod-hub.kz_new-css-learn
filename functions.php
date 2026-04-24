<?php
/**
 * Вспомогательные функции NewCSSLearn
 * Содержит утилитарные функции для работы приложения
 * 
 * Функции безопасности:
 * - getArrayValue() - безопасное получение значения из массива
 * - generateCSRFToken() / verifyCSRFToken() - генерация и проверка CSRF токенов
 * - cleanHTML() - базовая очистка HTML контента
 * 
 * Функции валидации:
 * - isValidEmail() - валидация email адреса
 * - isAllowedImageType() - проверка типа изображения
 * 
 * Функции работы со строками:
 * - truncateString() - ограничение длины строки
 * - getFileExtension() - получение расширения файла
 * - jsonEncode() / jsonDecode() - безопасная работа с JSON
 * 
 * Функции файловой системы:
 * - ensureDirectoryExists() - создание директории
 * - generateUniqueFilename() - генерация уникального имени файла
 * - formatFileSize() - форматирование размера файла
 * - createLessonImagesDirectory() / removeLessonImagesDirectory() - управление изображениями уроков
 * 
 * Функции логирования и ошибок:
 * - logError() - логирование ошибок
 * - sendJSONResponse() / sendJSONError() - отправка JSON ответов
 * 
 * Функции HTTP и AJAX:
 * - isAjaxRequest() - проверка AJAX запроса
 * 
 * Функции темы оформления:
 * - getCurrentTheme() / setTheme() - работа с темами
 * 
 * Функции навигации:
 * - getBreadcrumbs() - формирование хлебных крошек
 * - getLessonNavigation() - навигационные кнопки уроков
 */

// Предотвращение прямого доступа к файлу
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

/**
 * Безопасное получение значения из массива
 * @param array $array Массив
 * @param string $key Ключ
 * @param mixed $default Значение по умолчанию
 * @return mixed
 */
function getArrayValue($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Генерация CSRF токена
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF токена
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Валидация email адреса
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Ограничение длины строки
 * @param string $string
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateString($string, $length, $suffix = '...') {
    if (mb_strlen($string, 'UTF-8') <= $length) {
        return $string;
    }
    return mb_substr($string, 0, $length, 'UTF-8') . $suffix;
}

/**
 * Создание директории если она не существует
 * @param string $path
 * @return bool
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * Получение расширения файла
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Проверка типа изображения
 * @param string $filename
 * @return bool
 */
function isAllowedImageType($filename) {
    $extension = getFileExtension($filename);
    return in_array($extension, ALLOWED_EXTENSIONS);
}

/**
 * Генерация уникального имени файла
 * @param string $filename
 * @return string
 */
function generateUniqueFilename($filename) {
    $extension = getFileExtension($filename);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    $timestamp = time();
    $random = bin2hex(random_bytes(4));
    return $basename . '_' . $timestamp . '_' . random_int(1000, 9999) . '.' . $extension;
}

/**
 * Форматирование размера файла
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Очистка HTML контента (базовая защита)
 * @param string $html
 * @return string
 */
function cleanHTML($html) {
    // Разрешенные теги для контента уроков
    $allowedTags = '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><ul><ol><li><a><img><code><pre><blockquote><div><span>';
    
    return strip_tags($html, $allowedTags);
}

/**
 * Конвертация массива в JSON с правильными настройками
 * @param array $data
 * @return string
 */
function jsonEncode($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT | JSON_HEX_APOS);
}

/**
 * Декодирование JSON с обработкой ошибок
 * @param string $json
 * @return array|null
 */
function jsonDecode($json) {
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        return null;
    }
    return $data;
}

/**
 * Логирование ошибок
 * @param string $message
 * @param string $level
 */
function logError($message, $level = 'ERROR') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/logs/error.log');
}

/**
 * Отправка JSON ответа
 * @param mixed $data
 * @param int $statusCode
 */
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo jsonEncode($data);
    exit;
}

/**
 * Отправка ошибки в JSON формате
 * @param string $message
 * @param int $statusCode
 */
function sendJSONError($message, $statusCode = 400) {
    sendJSONResponse(['error' => $message], $statusCode);
}

/**
 * Проверка AJAX запроса
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Получение текущей темы оформления
 * @return string
 */
function getCurrentTheme() {
    return isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : 'light';
}

/**
 * Установка темы оформления
 * @param string $theme
 */
function setTheme($theme) {
    $validThemes = ['light', 'dark'];
    if (in_array($theme, $validThemes)) {
        setcookie('theme', $theme, [
            'expires' => time() + (365 * 24 * 60 * 60), // 1 год
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

/**
 * Получение хлебных крошек для урока
 * @param array $section
 * @param array $lesson
 * @return array
 */
function getBreadcrumbs($section = null, $lesson = null) {
    $breadcrumbs = [
        ['title' => 'Главная', 'url' => Router::getHomeUrl()]
    ];
    
    if ($section) {
        $breadcrumbs[] = [
            'title' => $section['title_ru'],
            'url' => Router::getHomeUrl() . '#' . $section['slug']
        ];
    }
    
    if ($lesson) {
        $breadcrumbs[] = [
            'title' => $lesson['title_ru'],
            'url' => Router::getLessonUrl($section, $lesson)
        ];
    }
    
    return $breadcrumbs;
}

/**
 * Получение навигационных кнопок для урока
 * @param array $section
 * @param array $lesson
 * @param array $allLessons
 * @return array
 */
function getLessonNavigation($section, $lesson, $allLessons) {
    $navigation = [];
    $currentKey = null;
    
    // Поиск текущего урока в массиве
    foreach ($allLessons as $key => $l) {
        if ($l['id'] == $lesson['id']) {
            $currentKey = $key;
            break;
        }
    }
    
    // Предыдущий урок
    if ($currentKey > 0) {
        $prevLesson = $allLessons[$currentKey - 1];
        $navigation['prev'] = [
            'title' => 'Предыдущий урок',
            'url' => Router::getLessonUrl($section, $prevLesson),
            'lesson_title' => $prevLesson['title_ru']
        ];
    }
    
    // Следующий урок
    if ($currentKey < count($allLessons) - 1) {
        $nextLesson = $allLessons[$currentKey + 1];
        $navigation['next'] = [
            'title' => 'Следующий урок',
            'url' => Router::getLessonUrl($section, $nextLesson),
            'lesson_title' => $nextLesson['title_ru']
        ];
    } else {
        // Если это последний урок, добавляем кнопку на главную
        $navigation['home'] = [
            'title' => 'На главную',
            'url' => Router::getHomeUrl(),
            'lesson_title' => 'Вернуться к оглавлению'
        ];
    }
    
    return $navigation;
}

/**
 * Создание директории для изображений урока
 * @param int $lessonId
 * @return string
 */
function createLessonImagesDirectory($lessonId) {
    $directory = UPLOADS_PATH . $lessonId . '/images/';
    ensureDirectoryExists($directory);
    return $directory;
}

/**
 * Удаление директории с изображениями урока
 * @param int $lessonId
 * @return bool
 */
function removeLessonImagesDirectory($lessonId) {
    $directory = UPLOADS_PATH . $lessonId . '/';
    
    if (is_dir($directory)) {
        $files = glob($directory . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return rmdir($directory);
    }
    
    return true;
}
?>
