<?php
/**
 * Главный файл приложения NewCSSLearn
 * Точка входа в приложение, инициализация и роутинг
 */

// Определение константы для безопасности
define('NEW_CSS_LEARN', true);

// Подключение конфигурации
require_once 'config.php';

// Запуск сессии
session_start();

// Подключение вспомогательных функций
require_once 'functions.php';

// Подключение классов
require_once 'Database.php';
require_once 'Router.php';

// Инициализация базы данных
$db = Database::getInstance();

// Инициализация роутера
$router = new Router();

// Добавление маршрутов

// Главная страница - отображение разделов
$router->addRoute('home', function() use ($db) {
    $sections = $db->getSections();
    
    // Получение уроков для каждого раздела
    foreach ($sections as &$section) {
        $section['lessons'] = $db->getLessonsBySection($section['id'], true); // Только опубликованные
    }
    
    include TEMPLATES_PATH . 'sections.php';
});

// Страница урока - формат: section_number-slug/lesson_number-slug
$router->addRoute('{section_part}/{lesson_part}', function($params) use ($db) {
    $url = $params[0] . '/' . $params[1];
    $urlParams = Router::parseLessonUrl($url);
    
    if (!$urlParams) {
        http_response_code(404);
        include TEMPLATES_PATH . '404.php';
        return;
    }
    
    // Поиск раздела по порядковому номеру и slug
    $section = $db->fetch(
        "SELECT * FROM sections WHERE section_order = ? AND slug = ?",
        [$urlParams['section_order'], $urlParams['section_slug']]
    );
    
    if (!$section) {
        http_response_code(404);
        include TEMPLATES_PATH . '404.php';
        return;
    }
    
    // Поиск урока
    $lesson = $db->getLessonBySlug($urlParams['lesson_slug'], $section['id'], true);
    
    if (!$lesson) {
        http_response_code(404);
        include TEMPLATES_PATH . '404.php';
        return;
    }
    
    // Получение всех уроков раздела для навигации
    $allLessons = $db->getLessonsBySection($section['id'], true);
    
    // Декодирование контента урока
    $lessonContent = jsonDecode($lesson['content']);
    
    // Получение навигационных кнопок
    $navigation = getLessonNavigation($section, $lesson, $allLessons);
    
    // Получение хлебных крошек
    $breadcrumbs = getBreadcrumbs($section, $lesson);
    
    include TEMPLATES_PATH . 'lesson.php';
});

// Обработчик 404 ошибки
$router->setNotFoundHandler(function() {
    http_response_code(404);
    include TEMPLATES_PATH . '404.php';
});

// Обработка запроса
$router->dispatch();
?>
