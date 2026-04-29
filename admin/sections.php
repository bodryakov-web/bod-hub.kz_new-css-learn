<?php
/**
 * Страница управления разделами NewCSSLearn
 * Отображает список разделов с возможностью редактирования и удаления
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

// Обработка действий (удаление)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $sectionId = (int)$_POST['id'];
        
        try {
            // Проверка, есть ли уроки в разделе
            $lessonsCount = $db->fetch("SELECT COUNT(*) as count FROM lessons WHERE section_id = ?", [$sectionId])['count'];
            
            if ($lessonsCount > 0) {
                $message = 'Нельзя удалить раздел, в котором есть уроки. Сначала удалите все уроки из этого раздела.';
                $messageType = 'error';
            } else {
                $db->query("DELETE FROM sections WHERE id = ?", [$sectionId]);
                $message = 'Раздел успешно удален';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Ошибка при удалении раздела: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Получение всех разделов
$sections = $db->fetchAll("SELECT * FROM sections ORDER BY section_order ASC");

// Установка мета-данных
$pageTitle = 'Управление разделами';
$pageHeader = 'Разделы курса';
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

<!-- Панель действий -->
<div class="admin-actions">
    <a href="<?php echo Router::getNewSectionUrl(); ?>" class="button button--primary">
        <span class="button__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        Добавить раздел
    </a>
</div>

<!-- Таблица разделов -->
<div class="admin-table-container">
    <?php if (!empty($sections)): ?>
        <table class="admin-table">
            <thead class="admin-table__head">
                <tr>
                    <th class="admin-table__header">ID</th>
                    <th class="admin-table__header">Название</th>
                    <th class="admin-table__header">Slug</th>
                    <th class="admin-table__header">Порядок</th>
                    <th class="admin-table__header">Уроков</th>
                    <th class="admin-table__header admin-table__header--actions">Действия</th>
                </tr>
            </thead>
            <tbody class="admin-table__body">
                <?php foreach ($sections as $section): ?>
                    <?php 
                    // Подсчет уроков в разделе
                    $lessonsCount = $db->fetch("SELECT COUNT(*) as count FROM lessons WHERE section_id = ?", [$section['id']])['count'];
                    ?>
                    <tr class="admin-table__row">
                        <td class="admin-table__cell"><?php echo (int)$section['id']; ?></td>
                        <td class="admin-table__cell">
                            <strong><?php echo htmlspecialchars($section['title_ru'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </td>
                        <td class="admin-table__cell">
                            <code class="admin-table__code"><?php echo htmlspecialchars($section['slug'], ENT_QUOTES, 'UTF-8'); ?></code>
                        </td>
                        <td class="admin-table__cell">
                            <span class="badge badge--primary"><?php echo (int)$section['section_order']; ?></span>
                        </td>
                        <td class="admin-table__cell">
                            <span class="badge badge--secondary"><?php echo (int)$lessonsCount; ?></span>
                        </td>
                        <td class="admin-table__cell admin-table__cell--actions">
                            <div class="admin-table__actions">
                                <a href="<?php echo Router::getLessonsUrl($section['id']); ?>" 
                                   class="button button--small button--primary"
                                   title="Перейти к урокам раздела">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 11H3v2h6v-2zm0-4H3v2h6V7zm0 8H3v2h6v-2zm12-8h-6v2h6V7zm0 4h-6v2h6v-2zm0 4h-6v2h6v-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                
                                <a href="<?php echo Router::getEditSectionUrl($section['id']); ?>" 
                                   class="button button--small button--secondary"
                                   title="Редактировать раздел">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот раздел?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$section['id']; ?>">
                                    <button type="submit" 
                                            class="button button--small button--danger"
                                            title="Удалить раздел"
                                            <?php echo $lessonsCount > 0 ? 'disabled' : ''; ?>>
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
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="empty-state__title">Разделы не найдены</h3>
            <p class="empty-state__description">
                В курсе пока нет разделов. Создайте первый раздел, чтобы начать наполнение курса.
            </p>
            <a href="<?php echo Router::getNewSectionUrl(); ?>" class="button button--primary">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Создать раздел
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
