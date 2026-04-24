<?php
/**
 * Главная страница админ-панели NewCSSLearn
 * Отображает общую статистику и навигацию по разделам
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

// Получение статистики
$sectionsCount = $db->fetch("SELECT COUNT(*) as count FROM sections")['count'];
$lessonsCount = $db->fetch("SELECT COUNT(*) as count FROM lessons")['count'];
$publishedLessonsCount = $db->fetch("SELECT COUNT(*) as count FROM lessons WHERE is_published = 1")['count'];
$draftLessonsCount = $lessonsCount - $publishedLessonsCount;

// Получение последних уроков
$recentLessons = $db->fetchAll("
    SELECT l.*, s.title_ru as section_title 
    FROM lessons l 
    JOIN sections s ON l.section_id = s.id 
    ORDER BY l.id DESC 
    LIMIT 5
");

// Установка мета-данных
$pageTitle = 'Админ-панель';
$pageDescription = 'Панель управления сайтом NewCSSLearn';
$pageHeader = 'Панель управления';
$isAdmin = true;

// Подключение шапки админ-панели
require_once ADMIN_TEMPLATES_PATH . 'header.php';
?>

<!-- Статистика -->
<div class="admin-dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--sections">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <h3 class="stat-card__title">Разделы</h3>
                <p class="stat-card__value"><?php echo (int)$sectionsCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--lessons">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <h3 class="stat-card__title">Все уроки</h3>
                <p class="stat-card__value"><?php echo (int)$lessonsCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--published">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <h3 class="stat-card__title">Опубликовано</h3>
                <p class="stat-card__value"><?php echo (int)$publishedLessonsCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--drafts">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__content">
                <h3 class="stat-card__title">Черновики</h3>
                <p class="stat-card__value"><?php echo (int)$draftLessonsCount; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Навигация по разделам -->
    <div class="admin-nav">
        <h2 class="admin-nav__title">Управление</h2>
        <div class="admin-nav__grid">
            <a href="sections.php" class="admin-nav__card">
                <div class="admin-nav__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="admin-nav__card-title">Разделы</h3>
                <p class="admin-nav__card-description">Управление разделами курса</p>
            </a>
            
            <a href="lessons.php" class="admin-nav__card">
                <div class="admin-nav__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="admin-nav__card-title">Уроки</h3>
                <p class="admin-nav__card-description">Управление уроками и контентом</p>
            </a>
        </div>
    </div>
    
    <!-- Последние уроки -->
    <?php if (!empty($recentLessons)): ?>
    <div class="recent-lessons">
        <h2 class="recent-lessons__title">Последние уроки</h2>
        <div class="recent-lessons__list">
            <?php foreach ($recentLessons as $lesson): ?>
                <div class="recent-lesson-card">
                    <div class="recent-lesson-card__content">
                        <h4 class="recent-lesson-card__title">
                            <?php echo htmlspecialchars($lesson['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="recent-lesson-card__section">
                            <?php echo htmlspecialchars($lesson['section_title'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                    <div class="recent-lesson-card__status">
                        <span class="status-badge status-badge--<?php echo $lesson['is_published'] ? 'published' : 'draft'; ?>">
                            <?php echo $lesson['is_published'] ? 'Опубликовано' : 'Черновик'; ?>
                        </span>
                    </div>
                    <div class="recent-lesson-card__actions">
                        <a href="edit-lesson.php?id=<?php echo (int)$lesson['id']; ?>" 
                           class="button button--small button--secondary">
                            Редактировать
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
