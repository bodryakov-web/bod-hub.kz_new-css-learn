<?php
/**
 * Страница создания и редактирования раздела NewCSSLearn
 * Обрабатывает форму добавления/изменения раздела
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

// Подключение к базе данных
$db = Database::getInstance();

$message = '';
$messageType = '';
$section = null;
$isEdit = false;

// Проверка режима редактирования
if (isset($_GET['id'])) {
    $sectionId = (int)$_GET['id'];
    $section = $db->fetch("SELECT * FROM sections WHERE id = ?", [$sectionId]);
    
    if (!$section) {
        $message = 'Раздел не найден';
        $messageType = 'error';
    } else {
        $isEdit = true;
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = Router::sanitize($_POST['title'] ?? '');
    $slug = Router::sanitize($_POST['slug'] ?? '');
    $order = (int)($_POST['order'] ?? 0);
    
    // Валидация
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Название раздела обязательно для заполнения';
    }
    
    if (empty($slug)) {
        $errors[] = 'Slug обязателен для заполнения';
    } elseif (!$db->isValidSlug($slug)) {
        $errors[] = 'Slug может содержать только маленькие английские буквы и дефисы';
    } elseif (!$db->isSectionSlugUnique($slug, $isEdit ? $section['id'] : null)) {
        $errors[] = 'Такой slug уже существует';
    }
    
    if ($order <= 0) {
        $errors[] = 'Порядковый номер должен быть положительным числом';
    } elseif (!$db->isSectionOrderUnique($order, $isEdit ? $section['id'] : null)) {
        $errors[] = 'Такой порядковый номер уже существует';
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Обновление раздела
                $db->query(
                    "UPDATE sections SET title_ru = ?, slug = ?, section_order = ? WHERE id = ?",
                    [$title, $slug, $order, $section['id']]
                );
                $message = 'Раздел успешно обновлен';
            } else {
                // Создание раздела
                $db->query(
                    "INSERT INTO sections (title_ru, slug, section_order) VALUES (?, ?, ?)",
                    [$title, $slug, $order]
                );
                $message = 'Раздел успешно создан';
            }
            
            $messageType = 'success';
            
            // Перенаправление на список разделов
            header('Location: sections.php?message=' . urlencode($message) . '&type=' . $messageType);
            exit;
            
        } catch (Exception $e) {
            $message = 'Ошибка при сохранении раздела: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

// Установка мета-данных
$pageTitle = $isEdit ? 'Редактирование раздела' : 'Создание раздела';
$pageDescription = $isEdit ? 'Изменение данных раздела' : 'Добавление нового раздела в курс';
$pageHeader = $isEdit ? 'Редактирование раздела' : 'Новый раздел';
$isAdmin = true;

// Подключение шапки админ-панели
require_once ADMIN_TEMPLATES_PATH . 'header.php';
?>

<!-- Сообщения об операциях -->
<?php if (!empty($message)): ?>
    <div class="admin-message admin-message--<?php echo $messageType; ?>">
        <div class="admin-message__content">
            <span class="admin-message__icon admin-message__icon--<?php echo $messageType; ?>">
                <?php if ($messageType === 'success'): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php else: ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </span>
            <span class="admin-message__text"><?php echo $message; ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Форма раздела -->
<div class="admin-form-container">
    <form method="POST" class="admin-form">
        <!-- Основная информация -->
        <div class="form-section">
            <h3 class="form-section__title">Основная информация</h3>
            
            <div class="form-group">
                <label for="title" class="form-label">Название раздела *</label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($section['title_ru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       placeholder="Например: Основы современного CSS">
                <div class="form-help">
                    Название раздела будет отображаться на главной странице курса
                </div>
            </div>
            
            <div class="form-group">
                <label for="slug" class="form-label">Slug *</label>
                <input type="text" 
                       id="slug" 
                       name="slug" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($section['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       pattern="[a-z-]+"
                       placeholder="osnovy-sovremennogo-css">
                <div class="form-help">
                    URL-псевдоним раздела. Только маленькие английские буквы и дефисы. Будет использоваться в URL адресе.
                </div>
            </div>
            
            <div class="form-group">
                <label for="order" class="form-label">Порядковый номер *</label>
                <input type="number" 
                       id="order" 
                       name="order" 
                       class="form-input" 
                       value="<?php echo (int)($section['section_order'] ?? 1); ?>"
                       required 
                       min="1"
                       placeholder="1">
                <div class="form-help">
                    Порядок отображения раздела на главной странице. Уникальное значение.
                </div>
            </div>
        </div>
        
        <!-- Кнопки действий -->
        <div class="form-actions">
            <a href="sections.php" class="button button--secondary">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Отмена
            </a>
            
            <button type="submit" class="button button--primary">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <?php echo $isEdit ? 'Сохранить изменения' : 'Создать раздел'; ?>
            </button>
        </div>
    </form>
</div>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
