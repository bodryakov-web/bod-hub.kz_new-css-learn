<?php
/**
 * Страница создания и редактирования урока NewCSSLearn
 * Обрабатывает форму добавления/изменения урока с Quill редактором
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
$lesson = null;
$lessonContent = null;
$isEdit = false;

// Restore form data from session if available (after validation errors)
$savedFormData = null;
if (isset($_SESSION['lesson_form_data'])) {
    $savedFormData = $_SESSION['lesson_form_data'];
    // Clear the session data after retrieving it
    unset($_SESSION['lesson_form_data']);
}

// Получение разделов для выпадающего списка
$sections = $db->fetchAll("SELECT * FROM sections ORDER BY section_order ASC");

// Проверка режима редактирования
if (isset($_GET['id'])) {
    $lessonId = (int)$_GET['id'];
    $lesson = $db->getLessonById($lessonId);
    
    if (!$lesson) {
        $message = 'Урок не найден';
        $messageType = 'error';
    } else {
        $isEdit = true;
        $lessonContent = jsonDecode($lesson['content']);
        
        // Проверка доступа к редактированию урока только через раздел
        // Если это не создание нового урока и нет параметра section, проверяем реферер
        if (!isset($_GET['section']) && !isset($_SERVER['HTTP_REFERER'])) {
            // Прямой доступ без реферера - разрешаем только если это внутренний переход
            $allowedReferers = [
                'lessons.php',
                'index.php'
            ];
            
            $refererFound = false;
            if (isset($_SERVER['HTTP_REFERER'])) {
                foreach ($allowedReferers as $allowedReferer) {
                    if (strpos($_SERVER['HTTP_REFERER'], $allowedReferer) !== false) {
                        $refererFound = true;
                        break;
                    }
                }
            }
            
            if (!$refererFound) {
                // Перенаправляем на страницу уроков раздела
                Router::redirect(Router::getLessonsUrl($lesson['section_id']));
                exit;
            }
        }
    }
} else {
    // Для создания нового урока проверяем, что есть параметр section
    if (!isset($_GET['section'])) {
        // Перенаправляем на главную страницу админки, если нет параметра section
        Router::redirect(Router::getAdminUrl());
        exit;
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sectionId = (int)($_POST['section_id'] ?? 0);
    $title = Router::sanitize($_POST['title'] ?? '');
    $slug = Router::sanitize($_POST['slug'] ?? '');
    $order = (int)($_POST['order'] ?? 0);
    $theory = $_POST['theory'] ?? '';
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    
    // Обработка тестов
    $tests = [];
    if (isset($_POST['test_question'])) {
        foreach ($_POST['test_question'] as $index => $question) {
            if (!empty(trim($question))) {
                $answers = [
                    Router::sanitize($_POST['test_answer_' . $index . '_0'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_1'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_2'] ?? ''),
                    Router::sanitize($_POST['test_answer_' . $index . '_3'] ?? '')
                ];
                $correct = (int)($_POST['test_correct_' . $index] ?? 0);
                
                $tests[] = [
                    'question' => Router::sanitize($question),
                    'answers' => array_filter($answers),
                    'correct' => $correct
                ];
            }
        }
    }
    
    // Обработка задач
    $tasks = [];
    if (isset($_POST['task_title'])) {
        foreach ($_POST['task_title'] as $index => $taskTitle) {
            if (!empty(trim($taskTitle))) {
                $tasks[] = [
                    'title' => Router::sanitize($taskTitle),
                    'description' => $_POST['task_description_' . $index] ?? ''
                ];
            }
        }
    }
    
    // Формирование контента урока
    $content = [
        'theory' => $theory,
        'tests' => $tests,
        'tasks' => $tasks
    ];
    
    // Валидация
    $errors = [];
    
    if ($sectionId <= 0) {
        $errors[] = 'Выберите раздел';
    }
    
    if (empty($title)) {
        $errors[] = 'Название урока обязательно для заполнения';
    }
    
    if (empty($slug)) {
        $errors[] = 'Slug обязателен для заполнения';
    } elseif (!$db->isValidSlug($slug)) {
        $errors[] = 'Slug может содержать только маленькие английские буквы и дефисы';
    } elseif (!$db->isLessonSlugUnique($slug, $sectionId, $isEdit ? $lesson['id'] : null)) {
        $errors[] = 'Такой slug уже существует в этом разделе';
    }
    
    if ($order <= 0) {
        $errors[] = 'Порядковый номер должен быть положительным числом';
    } elseif (!$db->isLessonOrderUnique($order, $sectionId, $isEdit ? $lesson['id'] : null)) {
        $errors[] = 'Такой порядковый номер уже существует в этом разделе';
    }
    
    if (empty($theory)) {
        $errors[] = 'Теоретический материал обязателен для заполнения';
    }
    
    if (empty($errors)) {
        try {
            $contentJson = jsonEncode($content);
            
            if ($isEdit) {
                // Обновление урока
                $db->query(
                    "UPDATE lessons SET section_id = ?, title_ru = ?, slug = ?, lesson_order = ?, content = ?, is_published = ? WHERE id = ?",
                    [$sectionId, $title, $slug, $order, $contentJson, $isPublished, $lesson['id']]
                );
                $message = 'Урок успешно обновлен';
            } else {
                // Создание урока
                $db->query(
                    "INSERT INTO lessons (section_id, title_ru, slug, lesson_order, content, is_published) VALUES (?, ?, ?, ?, ?, ?)",
                    [$sectionId, $title, $slug, $order, $contentJson, $isPublished]
                );
                $message = 'Урок успешно создан';
            }
            
            $messageType = 'success';
            
            // Clear session data after successful save
            unset($_SESSION['lesson_form_data']);
            
            // Redirect to lessons list of the section
            $targetSectionId = $isEdit ? $lesson['section_id'] : $sectionId;
            Router::redirect(Router::getLessonsUrl($targetSectionId) . '?message=' . urlencode($message) . '&type=' . $messageType);
            exit;
            
        } catch (Exception $e) {
            $message = 'Ошибка при сохранении урока: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        // Save form data to session for restoration after validation errors
        $_SESSION['lesson_form_data'] = [
            'section_id' => $sectionId,
            'title' => $title,
            'slug' => $slug,
            'order' => $order,
            'theory' => $theory,
            'is_published' => $isPublished,
            'tests' => $tests,
            'tasks' => $tasks
        ];
        
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

// Установка мета-данных
$pageTitle = $isEdit ? 'Редактирование урока' : 'Создание урока';
$pageDescription = $isEdit ? '' : 'Dobavlenie novogo uroka v kurs';
$pageHeader = $isEdit ? 'Редактирование урока' : 'Новый урок';
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

<!-- Форма урока -->
<div class="admin-form-container">
    <form method="POST" class="admin-form" id="lessonForm">
        <!-- Основная информация -->
        <div class="form-section">
            
            <div class="form-group">
                <label for="section_id" class="form-label">Раздел *</label>
                <select id="section_id" name="section_id" class="form-select" required>
                    <option value="">Выберите раздел</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?php echo (int)$section['id']; ?>" 
                                <?php 
                                $selected = false;
                                if (isset($lesson['section_id']) && $lesson['section_id'] == $section['id']) {
                                    $selected = true;
                                } elseif (isset($_GET['section']) && (int)$_GET['section'] == $section['id']) {
                                    $selected = true;
                                } elseif ($savedFormData && $savedFormData['section_id'] == $section['id']) {
                                    $selected = true;
                                }
                                echo $selected ? 'selected' : ''; 
                                ?>>
                            <?php echo htmlspecialchars($section['title_ru'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="title" class="form-label">Название урока *</label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($savedFormData['title'] ?? $lesson['title_ru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       placeholder="Например: CSS Grid Layout - основы">
            </div>
            
            <div class="form-group">
                <label for="slug" class="form-label">Slug *</label>
                <input type="text" 
                       id="slug" 
                       name="slug" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($savedFormData['slug'] ?? $lesson['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       pattern="[a-z0-9\-]+"
                       placeholder="css-grid-layout-osnovy">
                <div class="form-help">
                    URL-псевдоним урока. Только маленькие английские буквы и дефисы.
                </div>
            </div>
            
            <div class="form-group">
                <label for="order" class="form-label">Порядковый номер *</label>
                <input type="number" 
                       id="order" 
                       name="order" 
                       class="form-input" 
                       value="<?php echo (int)($savedFormData['order'] ?? $lesson['lesson_order'] ?? 1); ?>"
                       required 
                       min="1"
                       placeholder="1">
                <div class="form-help">
                    Порядок отображения урока внутри раздела. Уникальное значение.
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_published" value="1" 
                           <?php echo ($savedFormData && $savedFormData['is_published']) || (isset($lesson['is_published']) && $lesson['is_published']) ? 'checked' : ''; ?>>
                    <span class="checkbox-text">Опубликовать урок</span>
                </label>
                <div class="form-help">
                    Если не отмечено, урок будет сохранен как черновик.
                </div>
            </div>
        </div>
        
        <!-- Теоретический материал -->
        <div class="form-section">
            <h3 class="form-section__title">Теоретический материал</h3>
            
            <div class="form-group">
                <label for="theory" class="form-label"></label>
                <textarea id="editor" name="theory" style="height: 75vh; font-size: 19.2px;"><?php echo htmlspecialchars($savedFormData['theory'] ?? $lessonContent['theory'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        
        <!-- Tests -->
        <div class="form-section">
            <h3 class="form-section__title">Tests</h3>
            
            <div id="testsContainer">
                <?php 
                // Use saved tests data if available, otherwise use lesson data
                $tests = $savedFormData['tests'] ?? $lessonContent['tests'] ?? [];
                if (empty($tests)) {
                    // Add one empty test for new lessons
                    $tests = [[]];
                }
                
                foreach ($tests as $index => $test): 
                ?>
                <div class="test-item" data-test-index="<?php echo $index; ?>">
                    <div class="test-header">
                        <h4 class="test-title">Question <?php echo $index + 1; ?></h4>
                        <?php if ($index > 0): ?>
                        <button type="button" class="button button--small button--danger" onclick="removeTest(<?php echo $index; ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Вопрос</label>
                        <input type="text" 
                               name="test_question[<?php echo $index; ?>]" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($test['question'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="Введите текст вопроса">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Варианты ответов</label>
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="answer-option">
                            <label class="radio-label">
                                <input type="radio" 
                                       name="test_correct_<?php echo $index; ?>" 
                                       value="<?php echo $i; ?>"
                                       <?php echo (isset($test['correct']) && $test['correct'] == $i) ? 'checked' : ''; ?>>
                                <input type="text" 
                                       name="test_answer_<?php echo $index; ?>_<?php echo $i; ?>" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($test['answers'][$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       placeholder="Вариант ответа <?php echo $i + 1; ?>">
                            </label>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button button--secondary" onclick="addTest()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Добавить вопрос
            </button>
        </div>
        
        <!-- Задачи -->
        <div class="form-section">
            <h3 class="form-section__title">Задачи</h3>
            
            <div id="tasksContainer">
                <?php 
                // Use saved tasks data if available, otherwise use lesson data
                $tasks = $savedFormData['tasks'] ?? $lessonContent['tasks'] ?? [];
                if (empty($tasks)) {
                    // Add one empty task for new lessons
                    $tasks = [[]];
                }
                
                foreach ($tasks as $index => $task): 
                ?>
                <div class="task-item" data-task-index="<?php echo $index; ?>">
                    <div class="task-header">
                        <h4 class="task-title">Задача <?php echo $index + 1; ?></h4>
                        <?php if ($index > 0): ?>
                        <button type="button" class="button button--small button--danger" onclick="removeTask(<?php echo $index; ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Название задачи</label>
                        <input type="text" 
                               name="task_title[<?php echo $index; ?>]" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="Введите название задачи">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Описание задачи</label>
                        <textarea class="task-editor" name="task_description_<?php echo $index; ?>" data-task-index="<?php echo $index; ?>" style="height: 120px;"><?php echo htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button button--secondary" onclick="addTask()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Добавить задачу
            </button>
        </div>
        
        <!-- Кнопки действий -->
        <div class="form-actions">
            <a href="<?php echo isset($_GET['section']) ? Router::getLessonsUrl((int)$_GET['section']) : ($isEdit ? Router::getLessonsUrl($lesson['section_id']) : Router::getAdminUrl()); ?>" class="button button--secondary">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Отмена
            </a>
            
            <button type="button" class="button button--outline" onclick="saveDraft()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Сохранить черновик
            </button>
            
            <button type="submit" class="button button--primary" onclick="publishLesson()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <?php echo $isEdit ? 'Publish' : 'Publish'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Подключение CKEditor 4 -->
<script src="<?php echo getAssetUrl('ckeditor/ckeditor.js'); ?>"></script>
<script>
// Инициализация CKEditor 4 для основного редактора
CKEDITOR.replace('editor', {
    language: 'ru',
    height: '75vh',
    versionCheck: false,
    removePlugins: 'resize',
    extraPlugins: 'codeblock',
    format_tags: 'p;h1;h2;h3;h4;h5;h6;pre',
    fontSize_defaultLabel: '19px',
    fontSize_sizes: '16/16px;18/18px;19/19px;20/20px;22/22px;24/24px',
    font_names: 'Arial/Arial, Helvetica, sans-serif; Courier New/Courier New, Courier, monospace; Times New Roman/Times New Roman, Times, serif; Verdana/Verdana, Geneva, sans-serif',
    extraAllowedContent: 'body(font-size);p(font-size);div(font-size);span(font-size);code(font-family);pre(font-family);pre(background-color);pre(border);pre(padding);pre(border-radius)',
    contentsCss: '<?php echo getAssetUrl('css/ckeditor-content.css'); ?>',
    contentsStyles: 'body { font-size: 19.2px; line-height: 1.2; } p { font-size: 19.2px; margin: 0; padding: 0; line-height: 1.2; } div { font-size: 19.2px; } span { font-size: 19.2px; }',
    bodyId: 'editor-body',
    bodyClass: 'editor-content',
    stylesSet: [
        { name: 'Large Text', element: 'span', attributes: { 'style': 'font-size: 19.2px;' } },
        { name: 'Monospace', element: 'code', attributes: { 'style': 'font-family: "Courier New", monospace; background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px;' } },
        { name: 'Code Block', element: 'pre', attributes: { 'style': 'background-color: #f4f4f4; border: 1px solid #ddd; border-radius: 4px; padding: 16px; margin: 16px 0; overflow-x: auto; font-family: "Fira Code", "Monaco", "Consolas", monospace; font-size: 14px; line-height: 1.5; position: relative;' } },
        { name: 'Inline Code', element: 'code', attributes: { 'style': 'font-family: "Fira Code", "Monaco", "Consolas", monospace; background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; font-size: 14px;' } }
    ],
    on: {
        loaded: function() {
            var editor = this;
            var applyStyles = function() {
                try {
                    var style = editor.document.$.createElement('style');
                    style.type = 'text/css';
                    style.id = 'custom-editor-styles';
                    style.innerHTML = 'body { font-size: 19.2px !important; line-height: 1.2 !important; } p { font-size: 19.2px !important; margin: 0 !important; padding: 0 !important; line-height: 1.2 !important; } div { font-size: 19.2px !important; } span { font-size: 19.2px !important; } * { font-size: inherit !important; }';
                    
                    var existingStyle = editor.document.$.getElementById('custom-editor-styles');
                    if (existingStyle) {
                        existingStyle.parentNode.removeChild(existingStyle);
                    }
                    editor.document.$.head.appendChild(style);
                    
                    if (editor.document && editor.document.$ && editor.document.$.body) {
                        editor.document.$.body.style.fontSize = '19.2px';
                        editor.document.$.body.style.fontFamily = 'Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    }
                } catch(e) {
                    console.log('Error applying styles:', e);
                }
            };
            setTimeout(applyStyles, 500);
            setInterval(applyStyles, 2000);
            setTimeout(function() {
                var parentWidth = editor.container.getParent().$.clientWidth;
                editor.resize(parentWidth, '75vh');
            }, 100);
        },
        contentDom: function() {
            var editor = this;
            var applyStyles = function() {
                try {
                    var style = editor.document.$.createElement('style');
                    style.type = 'text/css';
                    style.id = 'custom-editor-styles';
                    style.innerHTML = 'body { font-size: 19.2px !important; line-height: 1.2 !important; } p { font-size: 19.2px !important; margin: 0 !important; padding: 0 !important; line-height: 1.2 !important; } div { font-size: 19.2px !important; } span { font-size: 19.2px !important; } * { font-size: inherit !important; }';
                    
                    var existingStyle = editor.document.$.getElementById('custom-editor-styles');
                    if (existingStyle) {
                        existingStyle.parentNode.removeChild(existingStyle);
                    }
                    editor.document.$.head.appendChild(style);
                    
                    if (editor.document && editor.document.$ && editor.document.$.body) {
                        editor.document.$.body.style.fontSize = '19.2px';
                        editor.document.$.body.style.fontFamily = 'Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    }
                } catch(e) {
                    console.log('Error applying styles in contentDom:', e);
                }
            };
            setTimeout(applyStyles, 100);
            var observer = new MutationObserver(function(mutations) {
                applyStyles();
            });
            observer.observe(editor.document.$.body, { childList: true, subtree: true });
        }
    },
    toolbar: [
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates', 'CodeBlock', 'RemoveCodeBlock' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll' ] },
        { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Subscript', 'Superscript' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
        { name: 'links', items: [ 'Link', 'Anchor' ] },
        { name: 'insert', items: [ 'Image', 'Flash', 'Table', 'Smiley', 'PageBreak', 'Iframe' ] },
        { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'tools', items: [ 'ShowBlocks' ] }
    ],
    pasteFromWordPromptCleanup: false,
    pasteFromWordRemoveFontStyles: false,
    pasteFromWordRemoveStyles: false,
});

// Глобальная переменная для доступа к редактору
window.mainEditor = CKEDITOR.instances.editor;

// Дополнительное применение стилей через iframe после полной загрузки страницы
window.addEventListener('load', function() {
    setTimeout(function() {
        var iframe = document.querySelector('iframe.cke_wysiwyg_frame');
        if (iframe && iframe.contentDocument) {
            var style = iframe.contentDocument.createElement('style');
            style.type = 'text/css';
            style.id = 'custom-editor-styles-iframe';
            style.innerHTML = 'body { font-size: 19.2px !important; line-height: 1 !important; } p { font-size: 19.2px !important; margin: 0 !important; padding: 0 !important; line-height: 1 !important; } div { font-size: 19.2px !important; } span { font-size: 19.2px !important; } * { font-size: inherit !important; }';
            
            var existingStyle = iframe.contentDocument.getElementById('custom-editor-styles-iframe');
            if (existingStyle) {
                existingStyle.parentNode.removeChild(existingStyle);
            }
            iframe.contentDocument.head.appendChild(style);
            
            if (iframe.contentDocument.body) {
                iframe.contentDocument.body.style.fontSize = '19.2px';
                iframe.contentDocument.body.style.fontFamily = 'Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
            }
        }
    }, 1000);
    
    setInterval(function() {
        var iframe = document.querySelector('iframe.cke_wysiwyg_frame');
        if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
            iframe.contentDocument.body.style.fontSize = '19.2px';
            iframe.contentDocument.body.style.fontFamily = 'Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        }
    }, 3000);
});

// Инициализация CKEditor для существующих редакторов задач
document.querySelectorAll('.task-editor').forEach(function(textarea) {
    var editorId = textarea.getAttribute('name');
    if (editorId && !CKEDITOR.instances[editorId]) {
        CKEDITOR.replace(editorId, {
            language: 'ru',
            height: 120,
            versionCheck: false,
            toolbar: [
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
                { name: 'links', items: [ 'Link' ] },
                { name: 'insert', items: [ 'Image' ] }
            ],
            pasteFromWordPromptCleanup: false,
            pasteFromWordRemoveFontStyles: false,
            pasteFromWordRemoveStyles: false
        });
    }
});

// Функции для управления тестами
var testCount = <?php echo max(1, count($tests)); ?>;

function addTest() {
    var container = document.getElementById('testsContainer');
    var testIndex = testCount++;
    
    var testHtml = `
        <div class="test-item" data-test-index="${testIndex}">
            <div class="test-header">
                <h4 class="test-title">Вопрос ${testIndex + 1}</h4>
                <button type="button" class="button button--small button--danger" onclick="removeTest(${testIndex})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="form-group">
                <label class="form-label">Вопрос</label>
                <input type="text" name="test_question[${testIndex}]" class="form-input" placeholder="Введите текст вопроса">
            </div>
            
            <div class="form-group">
                <label class="form-label">Варианты ответов</label>
                ${[0,1,2,3].map(i => `
                    <div class="answer-option">
                        <label class="radio-label">
                            <input type="radio" name="test_correct_${testIndex}" value="${i}">
                            <input type="text" name="test_answer_${testIndex}_${i}" class="form-input" placeholder="Вариант ответа ${i + 1}">
                        </label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', testHtml);
}

function removeTest(index) {
    var testItem = document.querySelector(`[data-test-index="${index}"]`);
    if (testItem) {
        testItem.remove();
    }
}

// Функции для управления задачами
var taskCount = <?php echo max(1, count($tasks)); ?>;

function addTask() {
    var container = document.getElementById('tasksContainer');
    var taskIndex = taskCount++;
    
    var taskHtml = `
        <div class="task-item" data-task-index="${taskIndex}">
            <div class="task-header">
                <h4 class="task-title">Задача ${taskIndex + 1}</h4>
                <button type="button" class="button button--small button--danger" onclick="removeTask(${taskIndex})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="form-group">
                <label class="form-label">Название задачи</label>
                <input type="text" name="task_title[${taskIndex}]" class="form-input" placeholder="Введите название задачи">
            </div>
            
            <div class="form-group">
                <label class="form-label">Описание задачи</label>
                <textarea class="task-editor" name="task_description_${taskIndex}" data-task-index="${taskIndex}" style="height: 120px;"></textarea>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', taskHtml);
    
    // Инициализация CKEditor для новой задачи
    var taskEditorId = `task_description_${taskIndex}`;
    CKEDITOR.replace(taskEditorId, {
        language: 'ru',
        height: 120,
        versionCheck: false,
        toolbar: [
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
            { name: 'links', items: [ 'Link' ] },
            { name: 'insert', items: [ 'Image' ] }
        ],
        pasteFromWordPromptCleanup: false,
        pasteFromWordRemoveFontStyles: false,
        pasteFromWordRemoveStyles: false
    });
}

function removeTask(index) {
    var taskItem = document.querySelector(`[data-task-index="${index}"]`);
    if (taskItem) {
        taskItem.remove();
    }
}

// Функция сохранения черновика
function saveDraft() {
    document.querySelector('input[name="is_published"]').checked = false;
    document.getElementById('lessonForm').submit();
}

// Функция публикации урока
function publishLesson() {
    document.querySelector('input[name="is_published"]').checked = true;
    document.getElementById('lessonForm').submit();
}
</script>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
