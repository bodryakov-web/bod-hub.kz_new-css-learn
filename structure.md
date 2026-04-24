# Структура проекта NewCSSLearn

## Корневая структура
```
NewCSSLearn/
├── Dockerfile
├── docker-compose.yml
├── db-init.sql
├── db-test-data.sql
├── .htaccess
├── index.php
├── config.php
├── Database.php
├── Router.php
├── functions.php
├── uploads/
│   └── lessons/
│       └── {lesson_id}/
│           └── images/
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── sections.php
│   ├── lessons.php
│   ├── edit-section.php
│   ├── edit-lesson.php
│   ├── upload.php
│   └── logout.php
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   ├── admin.css
│   │   └── themes.css
│   ├── js/
│   │   ├── main.js
│   │   ├── admin.js
│   │   ├── theme-toggle.js
│   │   └── quiz.js
│   └── images/
│       └── (статичные изображения сайта)
├── templates/
│   ├── header.php
│   ├── footer.php
│   ├── sections.php
│   ├── lesson.php
│   ├── 404.php
│   └── admin/
│       ├── header.php
│       ├── footer.php
│       ├── login-form.php
│       ├── sections-list.php
│       ├── lessons-list.php
│       ├── section-form.php
│       └── lesson-form.php
└── README.md
```

## Описание файлов и папок

### Корневые файлы
- **Dockerfile** - Конфигурация Docker контейнера с PHP 8.2 и Apache
- **docker-compose.yml** - Оркестрация Docker сервисов (PHP + MySQL)
- **db-init.sql** - SQL скрипт создания структуры базы данных
- **db-test-data.sql** - SQL скрипт заполнения тестовыми данными
- **.htaccess** - Конфигурация Apache для URL роутинга
- **index.php** - Главный файл приложения с роутером
- **config.php** - Конфигурация базы данных и приложения
- **Database.php** - Класс для работы с базой данных
- **Router.php** - Класс для обработки URL маршрутизации
- **functions.php** - Вспомогательные функции

### Папка uploads/
- **lessons/{lesson_id}/images/** - Хранение изображений уроков

### Папка admin/
- **index.php** - Главная страница админ-панели
- **login.php** - Страница авторизации
- **sections.php** - Управление разделами
- **lessons.php** - Управление уроками
- **edit-section.php** - Редактирование раздела
- **edit-lesson.php** - Редактирование урока
- **upload.php** - Обработка загрузки изображений
- **logout.php** - Выход из админ-панели

### Папка assets/
- **css/** - Стили сайта (Material Design, адаптивность, темы)
- **js/** - JavaScript функциональность
- **images/** - Статичные изображения сайта

### Папка templates/
- **header.php** - Шапка сайта с навигацией
- **footer.php** - Подвал сайта
- **sections.php** - Шаблон главной страницы с разделами
- **lesson.php** - Шаблон страницы урока
- **404.php** - Шаблон страницы ошибки
- **admin/** - Шаблоны админ-панели

## Особенности структуры

1. **Чистая архитектура** - Без фреймворков, только чистый PHP, HTML, CSS, JS
2. **Разделение concerns** - Логика, представление и данные разделены
3. **Адаптивность** - CSS с современными media queries
4. **Material Design** - Современный плоский дизайн
5. **UTF-8 без BOM** - Все файлы в правильной кодировке
6. **БЭМ методология** - CSS классы по БЭМ
7. **Data-атрибуты** - Для JavaScript вместо классов/ID
8. **Docker готовность** - Полная конфигурация для локальной разработки
