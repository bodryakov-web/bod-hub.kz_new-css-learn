<?php
/**
 * Страница авторизации админ-панели NewCSSLearn
 * Обрабатывает вход администратора в систему
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Если администратор уже авторизован, перенаправляем на главную админ-панели
if (Router::isAdmin()) {
    Router::redirect(Router::getAdminUrl());
}

$error = '';
$login = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = Router::sanitize($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Проверка логина и пароля
    if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        // Перенаправление на главную админ-панели
        Router::redirect(Router::getAdminUrl());
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Установка мета-данных
$pageTitle = 'Вход в админ-панель';
$pageDescription = 'Форма авторизации администратора';
$pageHeader = 'Авторизация';
$isAdmin = true;

// Подключение шапки админ-панели
require_once ADMIN_TEMPLATES_PATH . 'header.php';
?>

<div class="admin-login">
    <div class="login-container">
        <div class="login-card">
            <!-- Логотип и заголовок -->
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
            
            <!-- Форма входа -->
            <form class="login-form" method="POST" action="">
                <?php if (!empty($error)): ?>
                    <div class="login-form__error">
                        <div class="error-message">
                            <span class="error-message__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="error-message__text"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" 
                           id="login" 
                           name="login" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($login, ENT_QUOTES, 'UTF-8'); ?>"
                           required 
                           autocomplete="username"
                           placeholder="Введите логин">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           required 
                           autocomplete="current-password"
                           placeholder="Введите пароль">
                </div>
                
                <button type="submit" class="button button--primary button--large button--full-width">
                    <span class="button__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="10,17 15,12 10,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Войти
                </button>
            </form>
            
            <!-- Информация -->
            <div class="login-card__info">
                <p class="login-card__info-text">
                    Доступ к админ-панели имеет только авторизованный администратор.
                </p>
                <a href="<?php echo Router::getHomeUrl(); ?>" class="login-card__back-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Вернуться на сайт
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Временные стили для страницы входа (позже будут перенесены в admin.css) */
.admin-login {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
}

.login-container {
    width: 100%;
    max-width: 400px;
    padding: 0 1rem;
}

.login-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.login-card__header {
    text-align: center;
    padding: 2rem 2rem 1.5rem;
    background: var(--color-primary);
    color: white;
}

.login-card__logo {
    margin-bottom: 1rem;
    color: white;
}

.login-card__title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.login-card__subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0;
}

.login-form {
    padding: 2rem;
}

.login-form__error {
    margin-bottom: 1.5rem;
}

.error-message {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--color-error-light);
    border: 1px solid var(--color-error);
    border-radius: 6px;
    color: var(--color-error);
    font-size: 0.9rem;
}

.error-message__icon {
    flex-shrink: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--color-text-primary);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.login-card__info {
    padding: 1.5rem 2rem;
    background: var(--color-background);
    border-top: 1px solid var(--color-border);
    text-align: center;
}

.login-card__info-text {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    margin: 0 0 1rem;
}

.login-card__back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-primary);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.2s ease;
}

.login-card__back-link:hover {
    color: var(--color-primary-dark);
}

/* Адаптивность */
@media (width >= 600px) {
    .login-container {
        max-width: 450px;
    }
}
</style>

<?php
// Подключение подвала админ-панели
require_once ADMIN_TEMPLATES_PATH . 'footer.php';
?>
