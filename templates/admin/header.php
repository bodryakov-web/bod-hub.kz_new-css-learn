<?php
/**
 * Шаблон шапки админ-панели NewCSSLearn
 * Содержит навигацию и общую структуру для административных страниц
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Проверка авторизации (кроме страницы логина)
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$loginPath = parse_url(Router::getLoginUrl(), PHP_URL_PATH);
if (!Router::isAdmin() && $currentPath !== $loginPath) {
    Router::redirect(Router::getLoginUrl());
}

$currentTheme = getCurrentTheme();
$pageTitle = isset($pageTitle) ? $pageTitle . ' - Админ-панель' : 'Админ-панель';
?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') : 'Панель управления сайтом NewCSSLearn'; ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Подключение шрифта Roboto из Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Подключение CSS стилей -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/themes.css">
    
    <!-- Иконка сайта -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
</head>
<body class="admin-body">
    <!-- Шапка админ-панели -->
    <header class="admin-header">
        <div class="admin-header__container">
            <!-- Левая часть с названием и навигацией -->
            <div class="admin-header__left">
                <a href="<?php echo Router::getAdminUrl(); ?>" class="admin-header__logo">
                    <h1 class="admin-header__title">
                        <?php echo APP_NAME; ?>
                        <span class="admin-header__subtitle">Админ-панель</span>
                    </h1>
                </a>
                
                <!-- Навигационное меню -->
                <nav class="admin-nav" aria-label="Основная навигация">
                    <ul class="admin-nav__list">
                    </ul>
                </nav>
            </div>
            
            <!-- Правая часть с действиями -->
            <div class="admin-header__right">
                <!-- Переключатель темы -->
                <button class="theme-toggle theme-toggle--admin" data-action="toggle-theme" aria-label="Переключить тему">
                    <span class="theme-toggle__icon theme-toggle__icon--sun" data-theme-icon="light">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 2v2M12 20v2M4 12h2M16 12h2M6.34 6.34l1.42 1.42M16.24 16.24l1.42 1.42M6.34 17.66l1.42-1.42M16.24 7.76l1.42-1.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="theme-toggle__icon theme-toggle__icon--moon" data-theme-icon="dark">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
                
                <!-- Ссылка на сайт -->
                <a href="<?php echo Router::getHomeUrl(); ?>" 
                   class="admin-header__site-link" 
                   target="_blank"
                   title="Открыть сайт в новой вкладке">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="15,3 21,3 21,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                
                <!-- Выход -->
                <a href="logout.php" 
                   class="admin-header__logout" 
                   title="Выйти из админ-панели">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Основной контент админ-панели -->
    <main class="admin-main">
        <?php if (isset($pageHeader)): ?>
        <!-- Заголовок страницы -->
        <div class="admin-page-header">
            <div class="admin-page-header__container">
                <h1 class="admin-page-header__title"><?php echo htmlspecialchars($pageHeader, ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if (isset($pageDescription)): ?>
                <p class="admin-page-header__description"><?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="admin-container">
