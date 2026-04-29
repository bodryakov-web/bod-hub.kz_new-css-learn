<?php
/**
 * Шаблон страницы урока NewCSSLearn
 * Отображает контент урока с теорией, тестами и задачами
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Установка мета-данных
$pageTitle = $lesson['title_ru'];
$pageDescription = $lesson['title_ru'];
$pageHeader = $lesson['title_ru'];
$requireQuiz = !empty($lessonContent) && !empty($lessonContent['tests']);

// Подключение шапки
require_once TEMPLATES_PATH . 'header.php';
?>


<!-- Контент урока -->
<div class="lesson-content">
    
    <!-- Секция теоретического материала -->
    <section class="lesson-section lesson-section--theory" id="theory">
        <div class="lesson-section__content">
            <?php 
            if (isset($lessonContent['theory'])) {
                echo $lessonContent['theory'];
            } else {
                echo '<p class="error-message">Контент урока暂时 недоступен. Пожалуйста, попробуйте позже.</p>';
            }
            ?>
        </div>
    </section>

    <!-- Секция тестирования -->
    <?php if (!empty($lessonContent) && !empty($lessonContent['tests'])): ?>
    <section class="lesson-section lesson-section--quiz" id="quiz">
        <h2 class="lesson-section__title">Тестирование</h2>
        <div class="quiz-container" data-quiz-id="<?php echo (int)$lesson['id']; ?>">
            <?php foreach ($lessonContent['tests'] as $index => $test): ?>
                <div class="quiz-question" data-question-id="<?php echo $index; ?>">
                    <div class="quiz-question__header">
                        <h3 class="quiz-question__title">
                            Вопрос <?php echo $index + 1; ?>
                        </h3>
                        <div class="quiz-question__result" data-result="pending">
                            <span class="quiz-question__icon quiz-question__icon--pending">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <text x="12" y="16" text-anchor="middle" font-size="12" fill="currentColor">?</text>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="quiz-question__content">
                        <p class="quiz-question__text">
                            <?php echo htmlspecialchars($test['question'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        
                        <div class="quiz-answers">
                            <?php foreach ($test['answers'] as $answerIndex => $answer): ?>
                                <label class="quiz-answer" data-answer-index="<?php echo $answerIndex; ?>">
                                    <input type="radio" 
                                           name="question_<?php echo $index; ?>" 
                                           value="<?php echo $answerIndex; ?>"
                                           class="quiz-answer__input"
                                           data-correct="<?php echo (int)$test['correct'] === $answerIndex ? 'true' : 'false'; ?>">
                                    <span class="quiz-answer__radio"></span>
                                    <span class="quiz-answer__text">
                                        <?php echo htmlspecialchars($answer, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span class="quiz-answer__icon quiz-answer__icon--correct">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="quiz-answer__icon quiz-answer__icon--incorrect">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Секция задач -->
    <?php if (!empty($lessonContent) && !empty($lessonContent['tasks'])): ?>
    <section class="lesson-section lesson-section--tasks" id="tasks">
        <h2 class="lesson-section__title">Задачи</h2>
        <div class="tasks-container">
            <?php foreach ($lessonContent['tasks'] as $index => $task): ?>
                <div class="task-card" data-task-id="<?php echo $index; ?>">
                    <div class="task-card__header">
                        <h3 class="task-card__title">
                            <?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                    </div>
                    <div class="task-card__content">
                        <?php echo $task['description']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Навигация по урокам -->
    <?php if (!empty($navigation)): ?>
    <nav class="lesson-navigation" aria-label="Навигация по урокам">
        <div class="lesson-navigation__container">
            <?php if (isset($navigation['prev'])): ?>
                <a href="<?php echo htmlspecialchars($navigation['prev']['url'], ENT_QUOTES, 'UTF-8'); ?>" 
                   class="lesson-navigation__button lesson-navigation__button--prev">
                    <span class="lesson-navigation__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div class="lesson-navigation__content">
                        <span class="lesson-navigation__title"><?php echo htmlspecialchars($navigation['prev']['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="lesson-navigation__subtitle"><?php echo htmlspecialchars($navigation['prev']['lesson_title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </a>
            <?php endif; ?>

            <a href="<?php echo Router::getHomeUrl(); ?>" 
               class="lesson-navigation__button lesson-navigation__button--home">
                <span class="lesson-navigation__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="lesson-navigation__title">В оглавление</span>
            </a>

            <?php if (isset($navigation['next'])): ?>
                <a href="<?php echo htmlspecialchars($navigation['next']['url'], ENT_QUOTES, 'UTF-8'); ?>" 
                   class="lesson-navigation__button lesson-navigation__button--next">
                    <div class="lesson-navigation__content">
                        <span class="lesson-navigation__title"><?php echo htmlspecialchars($navigation['next']['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="lesson-navigation__subtitle"><?php echo htmlspecialchars($navigation['next']['lesson_title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <span class="lesson-navigation__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            <?php endif; ?>

            <?php if (isset($navigation['home'])): ?>
                <a href="<?php echo htmlspecialchars($navigation['home']['url'], ENT_QUOTES, 'UTF-8'); ?>" 
                   class="lesson-navigation__button lesson-navigation__button--home lesson-navigation__button--primary">
                    <span class="lesson-navigation__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="lesson-navigation__title"><?php echo htmlspecialchars($navigation['home']['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>

<?php
// Подключение подвала
require_once TEMPLATES_PATH . 'footer.php';
?>
