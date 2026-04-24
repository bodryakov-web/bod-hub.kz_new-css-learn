-- SQL скрипт заполнения базы данных тестовыми данными для NewCSSLearn
-- Кодировка: UTF-8 без BOM
-- Создает 2 раздела по 2 урока в каждом для демонстрации функционала

-- Установка кодировки по умолчанию
SET NAMES utf8mb4;

-- Вставка тестовых разделов
INSERT INTO `sections` (`id`, `title_ru`, `slug`, `section_order`) VALUES
(1, 'Основы современного CSS', 'osnovy-sovremennogo-css', 1),
(2, 'Продвинутые техники CSS', 'prodvinutye-tehniki-css', 2);

-- Вставка тестовых уроков для раздела 1
INSERT INTO `lessons` (`id`, `section_id`, `title_ru`, `slug`, `lesson_order`, `content`, `is_published`) VALUES
-- Урок 1.1: CSS Grid Layout
(1, 1, 'CSS Grid Layout - основы сеточной верстки', 'css-grid-layout-osnovy', 1, '{
  "theory": "<h2>CSS Grid Layout - современная система сеточной верстки</h2><p>CSS Grid Layout это мощная система для создания двухмерных сеток, которая позволяет легко создавать сложные макеты страниц. В отличие от Flexbox, который работает в одном измерении, Grid работает в двух измерениях одновременно.</p><h3>Основные концепции Grid</h3><p><strong>Grid Container:</strong> Родительский элемент с display: grid</p><p><strong>Grid Items:</strong> Прямые дочерние элементы grid контейнера</p><p><strong>Grid Lines:</strong> Линии, которые разделяют сетку на ячейки</p><h3>Базовый пример:</h3><pre><code class=\"language-css\">.container {\n  display: grid;\n  grid-template-columns: 1fr 1fr 1fr;\n  grid-template-rows: 100px 100px;\n  gap: 10px;\n}</code></pre><p>Этот код создает сетку 3x2 с равными колонками и промежутками в 10px между элементами.</p>",
  "tests": [
    {
      "question": "Что такое CSS Grid Layout?",
      "answers": [
        "Одномерная система верстки",
        "Двухмерная система сеточной верстки",
        "Метод позиционирования элементов",
        "Тип анимации CSS"
      ],
      "correct": 1
    },
    {
      "question": "Какое свойство создает grid контейнер?",
      "answers": [
        "display: flex",
        "display: grid",
        "position: grid",
        "layout: grid"
      ],
      "correct": 1
    },
    {
      "question": "В чем основное отличие Grid от Flexbox?",
      "answers": [
        "Grid работает только с колонками",
        "Grid работает в двух измерениях, Flexbox в одном",
        "Flexbox работает только с рядами",
        "Нет отличий"
      ],
      "correct": 1
    }
  ],
  "tasks": [
    {
      "title": "Создание простой сетки",
      "description": "<p>Создайте HTML страницу с grid контейнером, который содержит 6 элементов. Настройте сетку так, чтобы она имела 3 колонки и 2 ряда. Добавьте промежутки между элементами в 15px. Каждый элемент должен иметь разный фон для наглядности.</p><p><strong>Требования:</strong></p><ul><li>Используйте display: grid</li><li>3 колонки равной ширины</li><li>2 ряда высотой 100px каждый</li><li>Промежутки 15px</li></ul>"
    }
  ]
}', 1),

-- Урок 1.2: CSS Custom Properties
(2, 1, 'CSS Custom Properties - переменные в CSS', 'css-custom-properties-peremennye', 2, '{
  "theory": "<h2>CSS Custom Properties (переменные)</h2><p>CSS Custom Properties, также известные как CSS переменные, позволяют определять повторяющиеся значения в одном месте и использовать их throughout документе. Это делает CSS более поддерживаемым и легко изменяемым.</p><h3>Объявление переменных</h3><p>Переменные объявляются с префиксом -- и обычно определяются в :root для глобальной доступности:</p><pre><code class=\"language-css\">:root {\n  --primary-color: #3498db;\n  --secondary-color: #2ecc71;\n  --font-size-base: 16px;\n  --spacing-unit: 8px;\n}</code></pre><h3>Использование переменных</h3><p>Для использования переменной применяется функция var():</p><pre><code class=\"language-css\">.button {\n  background-color: var(--primary-color);\n  font-size: var(--font-size-base);\n  padding: calc(var(--spacing-unit) * 2);\n}</code></pre><h3>Преимущества переменных</h3><ul><li>Динамическое изменение через JavaScript</li><li>Темизация приложений</li><li>Уменьшение дублирования кода</li><li>Легкое поддержание консистентности дизайна</li></ul>",
  "tests": [
    {
      "question": "С каким префиксом объявляются CSS переменные?",
      "answers": [
        "$",
        "@",
        "--",
        "%"
      ],
      "correct": 2
    },
    {
      "question": "Какая функция используется для вызова CSS переменной?",
      "answers": [
        "get()",
        "var()",
        "use()",
        "calc()"
      ],
      "correct": 1
    },
    {
      "question": "Где обычно определяют глобальные CSS переменные?",
      "answers": [
        "В body",
        "В :root",
        "В html",
        "В head"
      ],
      "correct": 1
    }
  ],
  "tasks": [
    {
      "title": "Создание тематической цветовой схемы",
      "description": "<p>Создайте CSS файл с переменными для цветовой схемы веб-сайта. Определите переменные для основных цветов: primary, secondary, background, text, accent. Затем создайте стили для кнопок, заголовков и фона используя эти переменные.</p><p><strong>Требования:</strong></p><ul><li>Определите минимум 5 цветовых переменных</li><li>Используйте переменные в разных элементах</li><li>Добавьте переменные для размеров шрифтов и отступов</li></ul>"
    }
  ]
}', 1);

-- Вставка тестовых уроков для раздела 2
INSERT INTO `lessons` (`id`, `section_id`, `title_ru`, `slug`, `lesson_order`, `content`, `is_published`) VALUES
-- Урок 2.1: CSS Container Queries
(3, 2, 'CSS Container Queries - адаптивность на уровне компонентов', 'css-container-queries-adaptivnost-komponentov', 1, '{
  "theory": "<h2>CSS Container Queries - революция в адаптивном дизайне</h2><p>CSS Container Queries это новая технология, которая позволяет применять стили на основе размера контейнера элемента, а не viewport. Это открывает возможности для создания по-настоящему адаптивных компонентов.</p><h3>Как работают Container Queries</h3><p>Вместо того чтобы реагировать на размер экрана, элементы реагируют на размер своего родительского контейнера:</p><pre><code class=\"language-css\">.container {\n  container-type: inline-size;\n}\n\n@container (min-width: 400px) {\n  .card {\n    display: grid;\n    grid-template-columns: 1fr 1fr;\n  }\n}</code></pre><h3>Типы контейнеров</h3><p><strong>size:</strong> Отслеживает изменения и inline-size, и block-size</p><p><strong>inline-size:</strong> Отслеживает только изменения в inline направлении</p><p><strong>normal:</strong> Контейнер не создает контекст для запросов</p>",
  "tests": [
    {
      "question": "На основе чего работают CSS Container Queries?",
      "answers": [
        "Размера viewport",
        "Размера контейнера элемента",
        "Размера экрана устройства",
        "Размера окна браузера"
      ],
      "correct": 1
    },
    {
      "question": "Какое свойство создает контекст для container queries?",
      "answers": [
        "container-type",
        "container-context",
        "container-mode",
        "container-query"
      ],
      "correct": 0
    },
    {
      "question": "Что отслеживает container-type: inline-size?",
      "answers": [
        "Только высоту контейнера",
        "Только ширину контейнера",
        "Оба измерения",
        "Позицию контейнера"
      ],
      "correct": 1
    }
  ],
  "tasks": [
    {
      "title": "Адаптивная карточка товара",
      "description": "<p>Создайте компонент карточки товара, который меняет свою структуру в зависимости от размера контейнера. В узком контейнере карточка должна быть вертикальной, в широком - горизонтальной с изображением слева и контентом справа.</p><p><strong>Требования:</strong></p><ul><li>Используйте container-type: inline-size</li><li>Создайте минимум 2 брейкпоинта для контейнера</li><li>Добавьте плавные переходы между состояниями</li></ul>"
    }
  ]
}', 1),

-- Урок 2.2: CSS Scroll-driven Animations
(4, 2, 'CSS Scroll-driven Animations - анимации управляемые скроллом', 'css-scroll-driven-animations-animatsii-skroll', 2, '{
  "theory": "<h2>CSS Scroll-driven Animations</h2><p>Scroll-driven Animations это новая спецификация CSS, которая позволяет создавать анимации, привязанные к прогрессу скролла страницы. Это открывает возможности для создания впечатляющих эффектов без JavaScript.</p><h3>Типы scroll-driven анимаций</h3><p><strong>Progress Timeline:</strong> Анимация привязана к прогрессу скролла в определенном диапазоне</p><p><strong>View Timeline:</strong> Анимация привязана к видимости элемента в viewport</p><h3>Пример использования:</h3><pre><code class=\"css\">@keyframes fade-in {\n  from { opacity: 0; transform: translateY(50px); }\n  to { opacity: 1; transform: translateY(0); }\n}\n\n.element {\n  animation: fade-in linear;\n  animation-timeline: view();\n  animation-range: entry 0% entry 100%;\n}</code></pre><h3>Преимущества</h3><ul><li>Анимации синхронизированы со скроллом</li><li>Производительность оптимизирована браузером</li><li>Не требуется JavaScript</li><li>Плавная работа на мобильных устройствах</li></ul>",
  "tests": [
    {
      "question": "Что такое scroll-driven animations?",
      "answers": [
        "Анимации запускаемые по таймеру",
        "Анимации привязанные к прогрессу скролла",
        "CSS переходы",
        "JavaScript анимации"
      ],
      "correct": 1
    },
    {
      "question": "Какое свойство задает timeline для анимации?",
      "answers": [
        "scroll-timeline",
        "animation-timeline",
        "timeline-animation",
        "scroll-animation"
      ],
      "correct": 1
    },
    {
      "question": "Что делает view() timeline?",
      "answers": [
        "Привязывает к скроллу всей страницы",
        "Привязывает к видимости элемента",
        "Создает бесконечную анимацию",
        "Останавливает анимацию"
      ],
      "correct": 1
    }
  ],
  "tasks": [
    {
      "title": "Параллакс эффекты на чистом CSS",
      "description": "<p>Создайте страницу с несколькими секциями, где элементы появляются с анимацией по мере скролла. Используйте разные типы анимаций: появление, масштабирование, перемещение. Каждый элемент должен анимироваться когда он входит в область видимости.</p><p><strong>Требования:</strong></p><ul><li>Используйте @keyframes для определения анимаций</li><li>Примените animation-timeline: view()</li><li>Создайте минимум 3 разных типа анимаций</li><li>Добавьте разные animation-range для разнообразия эффектов</li></ul>"
    }
  ]
}', 1);

