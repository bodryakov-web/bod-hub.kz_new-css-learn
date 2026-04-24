-- SQL скрипт создания структуры базы данных для NewCSSLearn
-- Кодировка: UTF-8 без BOM
-- База данных: p-351366_php-docker
-- Таблицы: sections, lessons

-- Установка кодировки по умолчанию
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Создание таблицы разделов (sections)
-- Хранит информацию о разделах учебного курса
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор раздела',
  `title_ru` varchar(255) NOT NULL COMMENT 'Название раздела на русском языке',
  `slug` varchar(100) NOT NULL COMMENT 'URL-псевдоним раздела (только маленькие английские буквы и дефисы)',
  `section_order` int(11) NOT NULL COMMENT 'Порядковый номер раздела для сортировки',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`slug`) COMMENT 'Уникальность slug для разделов',
  UNIQUE KEY `unique_order` (`section_order`) COMMENT 'Уникальность порядкового номера'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Разделы учебного курса';

-- Создание таблицы уроков (lessons)
-- Хранит информацию об уроках с контентом в формате JSON
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор урока',
  `section_id` int(11) NOT NULL COMMENT 'ID раздела, к которому относится урок',
  `title_ru` varchar(255) NOT NULL COMMENT 'Название урока на русском языке',
  `slug` varchar(100) NOT NULL COMMENT 'URL-псевдоним урока (только маленькие английские буквы и дефисы)',
  `lesson_order` int(11) NOT NULL COMMENT 'Порядковый номер урока внутри раздела',
  `content` longtext NOT NULL COMMENT 'Контент урока в формате JSON (теория, тесты, задачи)',
  `is_published` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Флаг публикации (0 - черновик, 1 - опубликован)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_lesson` (`section_id`, `slug`) COMMENT 'Уникальность slug в пределах раздела',
  UNIQUE KEY `unique_section_order` (`section_id`, `lesson_order`) COMMENT 'Уникальность порядкового номера в пределах раздела',
  KEY `fk_section_id` (`section_id`),
  CONSTRAINT `fk_lessons_sections` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Уроки учебного курса';

-- Включение проверки внешних ключей
SET FOREIGN_KEY_CHECKS = 1;

-- Описание структуры JSON контента урока в поле content:
-- {
--   "theory": "HTML контент теоретического материала",
--   "tests": [
--     {
--       "question": "Текст вопроса",
--       "answers": ["Ответ 1", "Ответ 2", "Ответ 3", "Ответ 4"],
--       "correct": 2
--     }
--   ],
--   "tasks": [
--     {
--       "title": "Название задачи",
--       "description": "HTML контент условия задачи"
--     }
--   ]
-- }
