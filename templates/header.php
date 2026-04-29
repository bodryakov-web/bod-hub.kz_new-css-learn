<?php
/**
 * Шаблон шапки сайта NewCSSLearn
 * Содержит навигацию, переключение темы и общую структуру
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Получение текущей темы
$currentTheme = getCurrentTheme();
$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') : 'Новые возможности CSS с 2017 года - учебный курс'; ?>">
    <meta name="keywords" content="CSS, современные CSS, Grid, Flexbox, Custom Properties, Container Queries, веб-разработка">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Подключение шрифта Roboto из Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Подключение CSS стилей -->
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/themes.css'); ?>">
    
    <!-- Иконка сайта -->
    <link rel="icon" type="image/svg+xml" href="<?php echo getAssetUrl('images/favicon.svg'); ?>">
</head>
<body>
    <!-- Шапка сайта -->
    <header class="header">
        <div class="header__container">
            <!-- Левая часть с названием сайта -->
            <div class="header__left">
                <a href="<?php echo Router::getHomeUrl(); ?>" class="header__logo">
                    <h1 class="header__title"><?php echo APP_NAME; ?></h1>
                </a>
            </div>
            
            <!-- Центральная часть с подзаголовком (только на больших экранах) -->
            <div class="header__center">
                <p class="header__subtitle">новейший CSS c 2017 года</p>
            </div>
            
            <!-- Правая часть с переключателем темы -->
            <div class="header__right">
                <button class="theme-toggle" data-action="toggle-theme" aria-label="Переключить тему">
                    <span class="theme-toggle__icon theme-toggle__icon--sun" data-theme-icon="light">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 2v2M12 20v2M4 12h2M16 12h2M6.34 6.34l1.42 1.42M16.24 16.24l1.42 1.42M6.34 17.66l1.42-1.42M16.24 7.76l1.42-1.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="theme-toggle__icon theme-toggle__icon--moon" data-theme-icon="dark">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="main">
        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs" aria-label="Навигация">
            <div class="breadcrumbs__container">
                <ol class="breadcrumbs__list">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li class="breadcrumbs__item">
                            <?php if ($index < count($breadcrumbs) - 1): ?>
                                <a href="<?php echo htmlspecialchars($crumb['url'], ENT_QUOTES, 'UTF-8'); ?>" 
                                   class="breadcrumbs__link">
                                    <?php echo htmlspecialchars($crumb['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php else: ?>
                                <span class="breadcrumbs__current">
                                    <?php echo htmlspecialchars($crumb['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>
        <?php endif; ?>

        <div class="container">
            <?php if (isset($pageHeader)): ?>
            <!-- Заголовок страницы -->
            <div class="page-header">
                <h1 class="page-header__title"><?php echo htmlspecialchars($pageHeader, ENT_QUOTES, 'UTF-8'); ?></h1>
                            </div>
            <?php endif; ?>
