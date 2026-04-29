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
        
        // Remove slashes at beginning and end
        $requestUri = trim($requestUri, '/');
        
        // Remove subdirectory prefix if it exists
        $subdirectory = $this->getSubdirectory();
        if ($subdirectory && strpos($requestUri, $subdirectory) === 0) {
            $requestUri = substr($requestUri, strlen($subdirectory));
            $requestUri = trim($requestUri, '/');
        }
        
        // If empty request - main page
        if (empty($requestUri)) {
            $requestUri = 'home';
        }
        
        // Поиск подходящего маршрута
        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($pattern, $requestUri, $params)) {
                call_user_func($handler, $params);
                return;
            }
        }
        
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
        
        if (preg_match($regexPattern, $requestUri, $matches)) {
            // Удаление первого элемента (полное совпадение)
            array_shift($matches);
            $params = $matches;
            return true;
        }
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
     * Получение URL для разделов админ-панели
     * @return string
     */
    public static function getSectionsUrl() {
        return APP_URL . '/bod/sections';
    }
    
    /**
     * Получение URL для уроков админ-панели
     * @param int $sectionId
     * @return string
     */
    public static function getLessonsUrl($sectionId) {
        return APP_URL . '/bod/lessons/' . (int)$sectionId;
    }
    
    /**
     * Получение URL для редактирования раздела
     * @param int $sectionId
     * @return string
     */
    public static function getEditSectionUrl($sectionId) {
        return APP_URL . '/bod/section/edit/' . (int)$sectionId;
    }
    
    /**
     * Получение URL для создания нового раздела
     * @return string
     */
    public static function getNewSectionUrl() {
        return APP_URL . '/bod/section/new';
    }
    
    /**
     * Получение URL для редактирования урока
     * @param int $lessonId
     * @return string
     */
    public static function getEditLessonUrl($lessonId) {
        return APP_URL . '/bod/lesson/edit/' . (int)$lessonId;
    }
    
    /**
     * Получение URL для создания нового урока
     * @param int $sectionId
     * @return string
     */
    public static function getNewLessonUrl($sectionId) {
        return APP_URL . '/bod/lesson/new/' . (int)$sectionId;
    }
    
    /**
     * Получение URL для входа в админ-панель
     * @return string
     */
    public static function getLoginUrl() {
        return APP_URL . '/bod/login';
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
            self::redirect(APP_URL . '/bod/login');
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
    
    /**
     * Получение имени подкаталога из APP_URL
     * @return string|null
     */
    private function getSubdirectory() {
        $appUrl = defined('APP_URL') ? APP_URL : '';
        $parsedUrl = parse_url($appUrl);
        
        if (isset($parsedUrl['path'])) {
            $path = trim($parsedUrl['path'], '/');
            return !empty($path) ? $path : null;
        }
        
        return null;
    }
    
    /**
     * Получение базового пути для URL
     * @return string
     */
    private function getBasePath() {
        $subdirectory = $this->getSubdirectory();
        return $subdirectory ? '/' . $subdirectory : '';
    }
}
?>
