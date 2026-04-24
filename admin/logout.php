<?php
/**
 * Файл выхода из админ-панели NewCSSLearn
 * Завершает сессию администратора и перенаправляет на страницу входа
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Завершение сессии администратора
session_unset();
session_destroy();

// Удаление cookie сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Перенаправление на страницу входа
Router::redirect(Router::getAdminUrl() . '/login');
?>
