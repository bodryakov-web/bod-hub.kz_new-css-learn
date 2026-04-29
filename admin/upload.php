<?php
/**
 * Обработчик загрузки изображений NewCSSLearn
 * Принимает файлы изображений и сохраняет их в директорию урока
 */

// Определяем константу для безопасности, если она не определена
if (!defined('NEW_CSS_LEARN')) {
    define('NEW_CSS_LEARN', true);
}

// Подключение конфигурации
require_once '../config.php';

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение вспомогательных функций
require_once '../functions.php';

// Подключение классов
require_once '../Database.php';
require_once '../Router.php';

// Требование авторизации администратора
Router::requireAdmin();

// Проверка AJAX запроса
if (!isAjaxRequest()) {
    sendJSONError('Доступ разрешен только через AJAX', 403);
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONError('Неверный метод запроса', 405);
}

// Проверка наличия файла
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    sendJSONError('Ошибка загрузки файла', 400);
}

$file = $_FILES['image'];

// Валидация файла
$errors = [];

// Проверка размера файла
if ($file['size'] > MAX_FILE_SIZE) {
    $errors[] = 'Размер файла превышает ' . formatFileSize(MAX_FILE_SIZE);
}

// Проверка типа MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
    $errors[] = 'Неподдерживаемый тип файла. Разрешены: ' . implode(', ', array_map(function($type) {
        return explode('/', $type)[1];
    }, ALLOWED_IMAGE_TYPES));
}

// Проверка расширения файла
$extension = getFileExtension($file['name']);
if (!in_array($extension, ALLOWED_EXTENSIONS)) {
    $errors[] = 'Неподдерживаемое расширение файла';
}

// Проверка, что файл действительно является изображением
if (!getimagesize($file['tmp_name'])) {
    $errors[] = 'Файл не является изображением';
}

if (!empty($errors)) {
    sendJSONError(implode(', ', $errors), 400);
}

// Получение ID урока
$lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;

if ($lessonId <= 0) {
    sendJSONError('Не указан ID урока', 400);
}

// Проверка существования урока
$db = Database::getInstance();
$lesson = $db->getLessonById($lessonId);

if (!$lesson) {
    sendJSONError('Урок не найден', 404);
}

try {
    // Создание директории для изображений урока
    $uploadDir = createLessonImagesDirectory($lessonId);
    
    // Генерация уникального имени файла
    $filename = generateUniqueFilename($file['name']);
    $filepath = $uploadDir . $filename;
    
    // Перемещение файла
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        sendJSONError('Ошибка сохранения файла', 500);
    }
    
    // Формирование URL для доступа к файлу
    $fileUrl = getUploadUrl('lessons/' . $lessonId . '/images/' . $filename);
    
    // Возврат успешного ответа
    sendJSONResponse([
        'success' => true,
        'message' => 'Файл успешно загружен',
        'filename' => $filename,
        'url' => $fileUrl,
        'size' => $file['size'],
        'type' => $mimeType
    ]);
    
} catch (Exception $e) {
    sendJSONError('Ошибка при загрузке файла: ' . $e->getMessage(), 500);
}
?>
