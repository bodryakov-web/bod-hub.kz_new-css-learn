<?php
/**
 * Страница управления уроками NewCSSLearn
 * Отображает список уроков с возможностью редактирования и удаления
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

// Фильтрация по разделу
$sectionFilter = isset($_GET['section']) ? (int)$_GET['section'] : null;

// Обработка действий (удаление, публикация)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $lessonId = (int)$_POST['id'];
        
        try {
            // Получение информации об уроке для удаления изображений
            $lesson = $db->getLessonById($lessonId);
            
            if ($lesson) {
                // Удаление изображений урока
                removeLessonImagesDirectory($lessonId);
                
                // Удаление урока из базы данных
                $db->query("DELETE FROM lessons WHERE id = ?", [$lessonId]);
                
                $message = 'Урок успешно удален';
                $messageType = 'success';
            } else {
                $message = 'Урок не найден';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Ошибка при удалении урока: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'toggle_publish' && isset($_POST['id'])) {
        $lessonId = (int)$_POST['id'];
        
        try {
            // Получение текущего статуса
            $currentStatus = $db->fetch("SELECT is_published FROM lessons WHERE id = ?", [$lessonId])['is_published'];
            $newStatus = $currentStatus ? 0 : 1;
            
            // Обновление статуса
            $db->query("UPDATE lessons SET is_published = ? WHERE id = ?", [$newStatus, $lessonId]);
            
            $message = $newStatus ? 'Урок опубликован' : 'Урок переведен в черновики';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Ошибка при изменении статуса урока: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Получение разделов для фильтра
$sections = $db->fetchAll("SELECT * FROM sections ORDER BY section_order ASC");

// Построение запроса для уроков
$sql = "SELECT l.*, s.title_ru as section_title, s.slug as section_slug 
        FROM lessons l 
        JOIN sections s ON l.section_id = s.id";
$params = [];

if ($sectionFilter) {
    $sql .= " WHERE l.section_id = ?";
    $params[] = $sectionFilter;
}

$sql .= " ORDER BY s.section_order ASC, l.lesson_order ASC";

$lessons = $db->fetchAll($sql, $params);

// Установка мета-данных
$pageTitle = 'Управление уроками';
$pageDescription = 'Создание, редактирование и удаление уроков курса';
$pageHeader = 'Уроки курса';
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
            <span class="admin-message__text"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Панель действий и фильтров -->
<div class="admin-filters">
    <div class="admin-filters__left">
        <a href="edit-lesson.php" class="button button--primary">
            <span class="button__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            Добавить урок
        </a>
    </div>
    
    <div class="admin-filters__right">
        <form method="GET" class="filter-form">
            <label for="section" class="filter-label">Раздел:</label>
            <select id="section" name="section" class="filter-select" onchange="this.form.submit()">
                <option value="">Все разделы</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo (int)$section['id']; ?>" 
                            <?php echo $sectionFilter === $section['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($section['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Таблица уроков -->
<div class="admin-table-container">
    <?php if (!empty($lessons)): ?>
        <table class="admin-table">
            <thead class="admin-table__head">
                <tr>
                    <th class="admin-table__header">ID</th>
                    <th class="admin-table__header">Название</th>
                    <th class="admin-table__header">Раздел</th>
                    <th class="admin-table__header">Slug</th>
                    <th class="admin-table__header">Порядок</th>
                    <th class="admin-table__header">Статус</th>
                    <th class="admin-table__header admin-table__header--actions">Действия</th>
                </tr>
            </thead>
            <tbody class="admin-table__body">
                <?php foreach ($lessons as $lesson): ?>
                    <tr class="admin-table__row">
                        <td class="admin-table__cell"><?php echo (int)$lesson['id']; ?></td>
                        <td class="admin-table__cell">
                            <strong><?php echo htmlspecialchars($lesson['title_ru'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </td>
                        <td class="admin-table__cell">
                            <span class="badge badge--info">
                                <?php echo htmlspecialchars($lesson['section_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td class="admin-table__cell">
                            <code class="admin-table__code"><?php echo htmlspecialchars($lesson['slug'], ENT_QUOTES, 'UTF-8'); ?></code>
                        </td>
                        <td class="admin-table__cell">
                            <span class="badge badge--primary"><?php echo (int)$lesson['lesson_order']; ?></span>
                        </td>
                        <td class="admin-table__cell">
                            <span class="status-badge status-badge--<?php echo $lesson['is_published'] ? 'published' : 'draft'; ?>">
                                <?php echo $lesson['is_published'] ? 'Опубликовано' : 'Черновик'; ?>
                            </span>
                        </td>
                        <td class="admin-table__cell admin-table__cell--actions">
                            <div class="admin-table__actions">
                                <a href="edit-lesson.php?id=<?php echo (int)$lesson['id']; ?>" 
                                   class="button button--small button--secondary"
                                   title="Редактировать урок">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Вы уверены, что хотите <?php echo $lesson['is_published'] ? 'снять с публикации' : 'опубликовать'; ?> этот урок?');">
                                    <input type="hidden" name="action" value="toggle_publish">
                                    <input type="hidden" name="id" value="<?php echo (int)$lesson['id']; ?>">
                                    <button type="submit" 
                                            class="button button--small <?php echo $lesson['is_published'] ? 'button--warning' : 'button--success'; ?>"
                                            title="<?php echo $lesson['is_published'] ? 'Снять с публикации' : 'Опубликовать'; ?>">
                                        <?php if ($lesson['is_published']): ?>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот урок? Все изображения урока будут удалены.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$lesson['id']; ?>">
                                    <button type="submit" 
                                            class="button button--small button--danger"
                                            title="Удалить урок">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="empty-state__title">Уроки не найдены</h3>
            <p class="empty-state__description">
                <?php if ($sectionFilter): ?>
                    В выбранном разделе пока нет уроков. 
                    <a href="edit-lesson.php?section=<?php echo $sectionFilter; ?>">Создайте первый урок</a> в этом разделе.
                <?php else: ?>
                    В курсе пока нет уроков. <a href="edit-lesson.php">Создайте первый урок</a>, чтобы начать наполнение курса.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
