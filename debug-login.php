<?php
/**
 * Отладочный файл для проверки HTML вывода страницы входа
 */

// Определяем константу для безопасности
if (!defined('NEW_CSS_LEARN')) {
    define('NEW_CSS_LEARN', true);
}

// Подключение конфигурации
require_once 'config.php';

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение вспомогательных функций
require_once 'functions.php';

// Подключение классов
require_once 'Database.php';
require_once 'Router.php';

// Установка мета-данных
$pageTitle = 'Вход в админ-панель';
$pageDescription = 'Форма авторизации администратора';
$pageHeader = 'Авторизация';
$isAdmin = true;

// Включаем буферизацию вывода
ob_start();

// Подключение шапки админ-панели
require_once 'templates/admin/header.php';

// Получаем HTML шапки
$headerHtml = ob_get_clean();

// Включаем буферизацию для контента
ob_start();
?>

<div class="admin-login">
    <div class="login-container">
        <div class="login-card">
            <div class="login-card__header">
                <div class="login-card__logo">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="login-card__title">Админ-панель</h1>
                <p class="login-card__subtitle">
                    <?php echo APP_NAME; ?> - управление контентом
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$contentHtml = ob_get_clean();

// Включаем буферизацию для подвала
ob_start();
require_once 'templates/admin/footer.php';
$footerHtml = ob_get_clean();

// Выводим HTML для анализа
echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>DEBUG: HTML Output Analysis</title>\n";
echo "<style>\n";
echo "body { font-family: monospace; margin: 20px; }\n";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }\n";
echo ".section h3 { margin-top: 0; color: #333; }\n";
echo "pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }\n";
echo ".css-path { color: #0066cc; font-weight: bold; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<div class='section'>\n";
echo "<h3>1. Пути к CSS файлам в HEAD:</h3>\n";
preg_match_all('/href="([^"]+\.css[^"]*)"/', $headerHtml, $cssMatches);
if (!empty($cssMatches[1])) {
    foreach ($cssMatches[1] as $cssPath) {
        echo "<p class='css-path'>$cssPath</p>\n";
    }
} else {
    echo "<p>CSS файлы не найдены в HTML</p>\n";
}
echo "</div>\n";

echo "<div class='section'>\n";
echo "<h3>2. Пути к JS файлам:</h3>\n";
preg_match_all('/src="([^"]+\.js[^"]*)"/', $footerHtml, $jsMatches);
if (!empty($jsMatches[1])) {
    foreach ($jsMatches[1] as $jsPath) {
        echo "<p class='css-path'>$jsPath</p>\n";
    }
} else {
    echo "<p>JS файлы не найдены в HTML</p>\n";
}
echo "</div>\n";

echo "<div class='section'>\n";
echo "<h3>3. Полный HEAD секции:</h3>\n";
echo "<pre>" . htmlspecialchars($headerHtml) . "</pre>\n";
echo "</div>\n";

echo "<div class='section'>\n";
echo "<h3>4. Конфигурация APP_URL:</h3>\n";
echo "<p>APP_URL = " . APP_URL . "</p>\n";
echo "<p>Текущий REQUEST_URI = " . ($_SERVER['REQUEST_URI'] ?? 'не определен') . "</p>\n";
echo "</div>\n";

echo "</body>\n</html>";
?>
