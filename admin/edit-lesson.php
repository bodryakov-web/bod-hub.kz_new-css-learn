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
            
            // Перенаправление на список уроков
            header('Location: lessons.php?message=' . urlencode($message) . '&type=' . $messageType);
            exit;
            
        } catch (Exception $e) {
            $message = 'Ошибка при сохранении урока: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

// Установка мета-данных
$pageTitle = $isEdit ? 'Редактирование урока' : 'Создание урока';
$pageDescription = $isEdit ? 'Изменение данных урока' : 'Добавление нового урока в курс';
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
            <h3 class="form-section__title">Основная информация</h3>
            
            <div class="form-group">
                <label for="section_id" class="form-label">Раздел *</label>
                <select id="section_id" name="section_id" class="form-select" required>
                    <option value="">Выберите раздел</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?php echo (int)$section['id']; ?>" 
                                <?php echo (isset($lesson['section_id']) && $lesson['section_id'] == $section['id']) ? 'selected' : ''; ?>>
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
                       value="<?php echo htmlspecialchars($lesson['title_ru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       placeholder="Например: CSS Grid Layout - основы">
            </div>
            
            <div class="form-group">
                <label for="slug" class="form-label">Slug *</label>
                <input type="text" 
                       id="slug" 
                       name="slug" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($lesson['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required 
                       pattern="[a-z-]+"
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
                       value="<?php echo (int)($lesson['lesson_order'] ?? 1); ?>"
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
                           <?php echo (isset($lesson['is_published']) && $lesson['is_published']) ? 'checked' : ''; ?>>
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
                <label for="theory" class="form-label">Содержание урока *</label>
                <div id="editor" style="height: 400px;">
                    <?php echo $lessonContent['theory'] ?? ''; ?>
                </div>
                <input type="hidden" name="theory" id="theory" value="<?php echo htmlspecialchars($lessonContent['theory'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-help">
                    Используйте текстовый редактор для форматирования содержания урока.
                </div>
            </div>
        </div>
        
        <!-- Тесты -->
        <div class="form-section">
            <h3 class="form-section__title">Тесты</h3>
            
            <div id="testsContainer">
                <?php 
                $tests = $lessonContent['tests'] ?? [];
                if (empty($tests)) {
                    // Добавляем один пустой тест для новых уроков
                    $tests = [[]];
                }
                
                foreach ($tests as $index => $test): 
                ?>
                <div class="test-item" data-test-index="<?php echo $index; ?>">
                    <div class="test-header">
                        <h4 class="test-title">Вопрос <?php echo $index + 1; ?></h4>
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
                        <div class="form-help">Отметьте правильный ответ, выбрав радиокнопку слева от варианта</div>
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
                $tasks = $lessonContent['tasks'] ?? [];
                if (empty($tasks)) {
                    // Добавляем одну пустую задачу для новых уроков
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
                        <div class="task-editor" data-task-index="<?php echo $index; ?>">
                            <?php echo $task['description'] ?? ''; ?>
                        </div>
                        <input type="hidden" 
                               name="task_description_<?php echo $index; ?>" 
                               value="<?php echo htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
            <a href="lessons.php" class="button button--secondary">
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
            
            <button type="submit" class="button button--primary">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <?php echo $isEdit ? 'Сохранить изменения' : 'Создать урок'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Подключение Quill редактора -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
// Инициализация Quill редактора
var quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Напишите содержание урока...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Синхронизация содержимого редактора с hidden полем
quill.on('text-change', function() {
    document.getElementById('theory').value = quill.root.innerHTML;
});

// Инициализация редакторов для задач
var taskEditors = {};
document.querySelectorAll('.task-editor').forEach(function(element, index) {
    var taskIndex = element.getAttribute('data-task-index');
    taskEditors[taskIndex] = new Quill(element, {
        theme: 'snow',
        placeholder: 'Описание задачи...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });
    
    taskEditors[taskIndex].on('text-change', function() {
        var hiddenInput = document.querySelector('input[name="task_description_' + taskIndex + '"]');
        if (hiddenInput) {
            hiddenInput.value = taskEditors[taskIndex].root.innerHTML;
        }
    });
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
                <div class="form-help">Отметьте правильный ответ, выбрав радиокнопку слева от варианта</div>
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
                <div class="task-editor" data-task-index="${taskIndex}"></div>
                <input type="hidden" name="task_description_${taskIndex}" value="">
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', taskHtml);
    
    // Инициализация редактора для новой задачи
    var newTaskEditor = new Quill(`[data-task-index="${taskIndex}"]`, {
        theme: 'snow',
        placeholder: 'Описание задачи...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });
    
    newTaskEditor.on('text-change', function() {
        var hiddenInput = document.querySelector(`input[name="task_description_${taskIndex}"]`);
        if (hiddenInput) {
            hiddenInput.value = newTaskEditor.root.innerHTML;
        }
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
</script>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
