<?php
/**
 * Класс для работы с базой данных NewCSSLearn
 * Реализует подключение к MySQL и базовые CRUD операции
 */

// Предотвращение прямого доступа к файлу
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

class Database {
    private $connection;
    private static $instance = null;
    
    /**
     * Приватный конструктор для реализации Singleton
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Получение экземпляра класса (Singleton)
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Подключение к базе данных
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    
    /**
     * Получение соединения с базой данных
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Выполнение SQL запроса с параметрами
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Ошибка выполнения запроса: " . $e->getMessage());
        }
    }
    
    /**
     * Получение одной записи
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return array|null
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Получение всех записей
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение последнего вставленного ID
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Начало транзакции
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Подтверждение транзакции
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Откат транзакции
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Получение всех разделов
     * @return array
     */
    public function getSections() {
        $sql = "SELECT * FROM sections ORDER BY section_order ASC";
        return $this->fetchAll($sql);
    }
    
    /**
     * Получение раздела по slug
     * @param string $slug
     * @return array|null
     */
    public function getSectionBySlug($slug) {
        $sql = "SELECT * FROM sections WHERE slug = ?";
        return $this->fetch($sql, [$slug]);
    }
    
    /**
     * Получение уроков раздела
     * @param int $sectionId
     * @param bool $onlyPublished Только опубликованные уроки
     * @return array
     */
    public function getLessonsBySection($sectionId, $onlyPublished = true) {
        $sql = "SELECT * FROM lessons WHERE section_id = ?";
        $params = [$sectionId];
        
        if ($onlyPublished) {
            $sql .= " AND is_published = 1";
        }
        
        $sql .= " ORDER BY lesson_order ASC";
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Получение урока по slug и ID раздела
     * @param string $slug
     * @param int $sectionId
     * @param bool $onlyPublished Только опубликованные уроки
     * @return array|null
     */
    public function getLessonBySlug($slug, $sectionId, $onlyPublished = true) {
        $sql = "SELECT * FROM lessons WHERE slug = ? AND section_id = ?";
        $params = [$slug, $sectionId];
        
        if ($onlyPublished) {
            $sql .= " AND is_published = 1";
        }
        
        return $this->fetch($sql, $params);
    }
    
    /**
     * Получение урока по ID
     * @param int $id
     * @return array|null
     */
    public function getLessonById($id) {
        $sql = "SELECT l.*, s.slug as section_slug FROM lessons l 
                JOIN sections s ON l.section_id = s.id 
                WHERE l.id = ?";
        return $this->fetch($sql, [$id]);
    }
    
    /**
     * Проверка уникальности slug для раздела
     * @param string $slug
     * @param int|null $excludeId Исключить ID при проверке (для редактирования)
     * @return bool
     */
    public function isSectionSlugUnique($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM sections WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Проверка уникальности slug для урока
     * @param string $slug
     * @param int $sectionId
     * @param int|null $excludeId Исключить ID при проверке (для редактирования)
     * @return bool
     */
    public function isLessonSlugUnique($slug, $sectionId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM lessons WHERE slug = ? AND section_id = ?";
        $params = [$slug, $sectionId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Проверка уникальности порядкового номера для раздела
     * @param int $order
     * @param int|null $excludeId Исключить ID при проверке (для редактирования)
     * @return bool
     */
    public function isSectionOrderUnique($order, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM sections WHERE section_order = ?";
        $params = [$order];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Проверка уникальности порядкового номера для урока
     * @param int $order
     * @param int $sectionId
     * @param int|null $excludeId Исключить ID при проверке (для редактирования)
     * @return bool
     */
    public function isLessonOrderUnique($order, $sectionId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM lessons WHERE lesson_order = ? AND section_id = ?";
        $params = [$order, $sectionId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Валидация slug (только маленькие английские буквы и дефисы)
     * @param string $slug
     * @return bool
     */
    public function isValidSlug($slug) {
        return preg_match('/^[a-z-]+$/', $slug) && strlen($slug) > 0;
    }
}
?>
