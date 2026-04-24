<?php
/**
 * Класс роутера NewCSSLearn
 * Обрабатывает URL и направляет запросы соответствующим обработчикам
 */

// Предотвращение прямого доступа к файлу
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

class Router {
    private $routes = [];
    private $notFoundHandler = null;
    
    /**
     * Добавление маршрута
     * @param string $pattern Шаблон URL
     * @param callable $handler Обработчик маршрута
     */
    public function addRoute($pattern, $handler) {
        $this->routes[$pattern] = $handler;
    }
    
    /**
     * Установка обработчика 404 ошибки
     * @param callable $handler
     */
    public function setNotFoundHandler($handler) {
        $this->notFoundHandler = $handler;
    }
    
    /**
     * Обработка текущего запроса
     */
    public function dispatch() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        
        // Удаление слешей в начале и конце
        $requestUri = trim($requestUri, '/');
        
        // Если пустой запрос или имя поддиректории - главная страница
        if (empty($requestUri) || $requestUri === 'new-css-learn') {
            $requestUri = 'home';
        }
        
        // Временная отладка
        error_log("Router: Original URI: " . $_SERVER['REQUEST_URI'] . ", Processed URI: '$requestUri'");
        error_log("Router: Available routes: " . implode(', ', array_keys($this->routes)));
        
        // Поиск подходящего маршрута
        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($pattern, $requestUri, $params)) {
                error_log("Router: Matched pattern '$pattern'");
                call_user_func($handler, $params);
                return;
            }
        }
        
        error_log("Router: No route matched, calling 404 handler");
        
        // Если маршрут не найден
        if ($this->notFoundHandler) {
            call_user_func($this->notFoundHandler);
        } else {
            $this->defaultNotFound();
        }
    }
    
    /**
     * Проверка соответствия маршрута запросу
     * @param string $pattern Шаблон маршрута
     * @param string $requestUri Запрос
     * @param array &$params Параметры из URL
     * @return bool
     */
    private function matchRoute($pattern, $requestUri, &$params) {
        // Преобразование шаблона в регулярное выражение
        $regexPattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regexPattern = '#^' . $regexPattern . '$#';
        
        error_log("Router: Matching pattern '$pattern' against URI '$requestUri' as regex '$regexPattern'");
        
        if (preg_match($regexPattern, $requestUri, $matches)) {
            error_log("Router: Pattern matched! Matches: " . json_encode($matches));
            // Удаление первого элемента (полное совпадение)
            array_shift($matches);
            $params = $matches;
            return true;
        }
        
        error_log("Router: Pattern did not match");
        return false;
    }
    
    /**
     * Обработчик 404 ошибки по умолчанию
     */
    private function defaultNotFound() {
        http_response_code(404);
        include TEMPLATES_PATH . '404.php';
        exit;
    }
    
    /**
     * Получение URL для урока
     * @param array $section Раздел
     * @param array $lesson Урок
     * @return string
     */
    public static function getLessonUrl($section, $lesson) {
        return APP_URL . '/' . $section['section_order'] . '-' . $section['slug'] . '/' . 
               $lesson['lesson_order'] . '-' . $lesson['slug'];
    }
    
    /**
     * Получение URL для админ-панели
     * @return string
     */
    public static function getAdminUrl() {
        return APP_URL . '/bod';
    }
    
    /**
     * Получение URL главной страницы
     * @return string
     */
    public static function getHomeUrl() {
        return APP_URL . '/';
    }
    
    /**
     * Парсинг URL урока для получения параметров
     * @param string $url URL урока
     * @return array|null Массив с section_order, section_slug, lesson_order, lesson_slug
     */
    public static function parseLessonUrl($url) {
        // Ожидаемый формат: section_number-slug/lesson_number-slug
        $parts = explode('/', $url);
        
        if (count($parts) !== 2) {
            return null;
        }
        
        $sectionPart = $parts[0];
        $lessonPart = $parts[1];
        
        // Парсинг части раздела
        if (!preg_match('/^(\d+)-([a-z-]+)$/', $sectionPart, $sectionMatches)) {
            return null;
        }
        
        // Парсинг части урока
        if (!preg_match('/^(\d+)-([a-z-]+)$/', $lessonPart, $lessonMatches)) {
            return null;
        }
        
        return [
            'section_order' => (int)$sectionMatches[1],
            'section_slug' => $sectionMatches[2],
            'lesson_order' => (int)$lessonMatches[1],
            'lesson_slug' => $lessonMatches[2]
        ];
    }
    
    /**
     * Перенаправление на указанный URL
     * @param string $url
     * @param int $statusCode Код статуса (по умолчанию 302)
     */
    public static function redirect($url, $statusCode = 302) {
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    /**
     * Проверка авторизации администратора
     * @return bool
     */
    public static function isAdmin() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * Требование авторизации администратора
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            self::redirect(self::getAdminUrl() . '/login');
        }
    }
    
    /**
     * Очистка входных данных
     * @param string $data
     * @return string
     */
    public static function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Получение текущего URL
     * @return string
     */
    public static function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . '://' . $host . $uri;
    }
}
?>
