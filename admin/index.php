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


// Установка мета-данных
$isAdmin = true;

// Подключение шапки админ-панели
require_once ADMIN_TEMPLATES_PATH . 'header.php';
?>

<!-- Список разделов -->
    <div class="admin-sections">
        <div class="admin-sections__header">
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
        
        <?php
        // Получение разделов с количеством уроков
        $sections = $db->fetchAll("
            SELECT s.*, 
                   COUNT(l.id) as lessons_count,
                   COUNT(CASE WHEN l.is_published = 1 THEN 1 END) as published_count
            FROM sections s 
            LEFT JOIN lessons l ON s.id = l.section_id 
            GROUP BY s.id 
            ORDER BY s.section_order ASC
        ");
        ?>
        
        <?php if (!empty($sections)): ?>
            <div class="sections-grid" style="margin-top: min(5vh, 20px);">
                <?php foreach ($sections as $section): ?>
                    <div class="section-card">
                        <div class="section-card__header">
                            <h3 class="section-card__title">
                                <?php echo htmlspecialchars($section['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <div class="section-card__stats">
                                <span class="stat-badge stat-badge--lessons">
                                    <?php echo (int)$section['lessons_count']; ?> <?php echo getNumWord($section['lessons_count'], ['урок', 'урока', 'уроков']); ?>
                                </span>
                                <?php if ($section['published_count'] > 0): ?>
                                    <span class="stat-badge stat-badge--published">
                                        <?php echo (int)$section['published_count']; ?> опубликовано
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="section-card__content">
                            <div class="section-card__info">
                                <div class="info-item">
                                    <span class="info-label">Slug:</span>
                                    <code class="info-value"><?php echo htmlspecialchars($section['slug'], ENT_QUOTES, 'UTF-8'); ?></code>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Порядок:</span>
                                    <span class="info-value"><?php echo (int)$section['section_order']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-card__actions">
                            <a href="<?php echo Router::getLessonsUrl($section['id']); ?>" 
                               class="button button--small button--primary"
                               title="Управление уроками раздела">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Уроки
                            </a>
                            
                                                        
                            <a href="<?php echo Router::getEditSectionUrl($section['id']); ?>" 
                               class="button button--small button--outline"
                               title="Редактировать раздел">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Изменить
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Создать раздел
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    </div>

