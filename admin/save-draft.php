<?php
/**
 * AJAX обработчик для сохранения черновика урока
 */

// Определяем константу для безопасности
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

// Устанавливаем заголовок для JSON ответа
header('Content-Type: application/json');

// Проверяем, что это POST запрос
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Подключение к базе данных
    $db = Database::getInstance();
    
    // Получение данных из формы
    $lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : null;
    $sectionId = (int)($_POST['section_id'] ?? 0);
    $title = Router::sanitize($_POST['title'] ?? '');
    $slug = Router::sanitize($_POST['slug'] ?? '');
    $order = (int)($_POST['order'] ?? 0);
    $theory = $_POST['theory'] ?? '';
    
    // Отладка: логируем полученные данные
    error_log("save-draft.php: lessonId=$lessonId, sectionId=$sectionId, title=$title, theory length=" . strlen($theory));
    
    // Обработка тестов
    $tests = [];
    if (isset($_POST['test_question'])) {
        foreach ($_POST['test_question'] as $index => $question) {
            if (!empty(trim($question))) {
                $answers = [
                    Router::sanitize($_POST['test_answer_' . $index . '_0'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_1'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_2'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_3'] ?? '')
                ];
                $correct = (int)($_POST['test_correct_' . $index] ?? 0);
                
                $tests[] = [
                    'question' => Router::sanitize($question),
                    'answers' => array_filter($answers),
                    'correct' => $correct
                ];
            }
        }
    }
    
    // Обработка задач
    $tasks = [];
    if (isset($_POST['task_title'])) {
        foreach ($_POST['task_title'] as $index => $taskTitle) {
            if (!empty(trim($taskTitle))) {
                $tasks[] = [
                    'title' => Router::sanitize($taskTitle),
                    'description' => $_POST['task_description_' . $index] ?? ''
                ];
            }
        }
    }
    
    // Формирование контента урока
    $content = [
        'theory' => $theory,
        'tests' => $tests,
        'tasks' => $tasks
    ];
    
    // Базовая валидация
    $errors = [];
    
    if ($sectionId <= 0) {
        $errors[] = 'Выберите раздел';
    }
    
    if (empty($title)) {
        $errors[] = 'Название урока обязательно для заполнения';
    }
    
    if (empty($slug)) {
        $errors[] = 'Slug обязателен для заполнения';
    } elseif (!$db->isValidSlug($slug)) {
        $errors[] = 'Slug может содержать только маленькие английские буквы и дефисы';
    } elseif (!$db->isLessonSlugUnique($slug, $sectionId, $lessonId)) {
        $errors[] = 'Такой slug уже существует в этом разделе';
    }
    
    if ($order <= 0) {
        $errors[] = 'Порядковый номер должен быть положительным числом';
    } elseif (!$db->isLessonOrderUnique($order, $sectionId, $lessonId)) {
        $errors[] = 'Такой порядковый номер уже существует в этом разделе';
    }
    
    if (empty($theory)) {
        $errors[] = 'Теоретический материал обязателен для заполнения';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false, 
            'message' => implode('<br>', $errors),
            'errors' => $errors
        ]);
        exit;
    }
    
    // Сохранение в базу данных
    $contentJson = jsonEncode($content);
    
    error_log("save-draft.php: About to save lesson, content length=" . strlen($contentJson));
    
    if ($lessonId) {
        // Обновление существующего урока
        error_log("save-draft.php: Updating lesson $lessonId");
        $db->query(
            "UPDATE lessons SET section_id = ?, title_ru = ?, slug = ?, lesson_order = ?, content = ?, is_published = 0 WHERE id = ?",
            [$sectionId, $title, $slug, $order, $contentJson, $lessonId]
        );
        $message = 'Черновик урока успешно обновлен';
        error_log("save-draft.php: Lesson $lessonId updated successfully");
    } else {
        // Создание нового урока
        error_log("save-draft.php: Creating new lesson");
        $db->query(
            "INSERT INTO lessons (section_id, title_ru, slug, lesson_order, content, is_published) VALUES (?, ?, ?, ?, ?, 0)",
            [$sectionId, $title, $slug, $order, $contentJson]
        );
        $lessonId = $db->getLastInsertId();
        $message = 'Черновик урока успешно создан';
        error_log("save-draft.php: New lesson created with ID $lessonId");
    }
    
    // Очистка сессионных данных
    unset($_SESSION['lesson_form_data']);
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'lesson_id' => $lessonId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при сохранении черновика: ' . $e->getMessage()
    ]);
}
