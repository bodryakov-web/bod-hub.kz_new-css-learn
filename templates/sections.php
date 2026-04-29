<?php
/**
 * Шаблон главной страницы с разделами NewCSSLearn
 * Отображает карточки разделов с уроками
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Установка мета-данных
$pageTitle = 'Разделы курса';
$pageDescription = 'Выберите раздел для изучения современных возможностей CSS';
$pageHeader = 'Разделы учебного курса';

// Подключение шапки
require_once TEMPLATES_PATH . 'header.php';
?>

<!-- Сетка разделов -->
<div class="sections-grid">
    <?php if (!empty($sections)): ?>
        <?php foreach ($sections as $section): ?>
            <div class="section-card" id="<?php echo htmlspecialchars($section['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="section-card__header">
                    <h2 class="section-card__title">
                        <?php echo htmlspecialchars($section['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                    </h2>
                    <div class="section-card__meta">
                        <span class="section-card__order">
                            Раздел <?php echo (int)$section['section_order']; ?>
                        </span>
                        <span class="section-card__lessons-count">
                            <?php echo count($section['lessons']); ?> уроков
                        </span>
                    </div>
                </div>
                
                <div class="section-card__content">
                    <?php if (!empty($section['lessons'])): ?>
                        <div class="lessons-list">
                            <?php foreach ($section['lessons'] as $lesson): ?>
                                <a href="<?php echo Router::getLessonUrl($section, $lesson); ?>" 
                                   class="lesson-link"
                                   data-lesson-id="<?php echo (int)$lesson['id']; ?>">
                                    <div class="lesson-link__content">
                                        <span class="lesson-link__order">
                                            <?php echo (int)$lesson['lesson_order']; ?>.
                                        </span>
                                        <span class="lesson-link__title">
                                            <?php echo htmlspecialchars($lesson['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="lesson-link__arrow">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="section-card__empty">
                            <p class="section-card__empty-text">
                                В этом разделе пока нет уроков
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="empty-state__title">Разделы не найдены</h3>
            <p class="empty-state__description">
                На данный момент в курсе нет разделов. Пожалуйста, зайдите позже.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php
// Подключение подвала
require_once TEMPLATES_PATH . 'footer.php';
?>
