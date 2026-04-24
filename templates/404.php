<?php
/**
 * Шаблон страницы 404 ошибки NewCSSLearn
 * Отображается когда страница не найдена
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}

// Установка мета-данных
$pageTitle = 'Страница не найдена';
$pageDescription = 'Запрошенная страница не существует';
$pageHeader = '404 - Страница не найдена';

// Подключение шапки
require_once TEMPLATES_PATH . 'header.php';
?>

<div class="error-page">
    <div class="error-page__container">
        <div class="error-page__content">
            <!-- Иконка ошибки -->
            <div class="error-page__icon">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <!-- Заголовок ошибки -->
            <h1 class="error-page__title">404</h1>
            <h2 class="error-page__subtitle">Страница не найдена</h2>
            
            <!-- Описание ошибки -->
            <p class="error-page__description">
                К сожалению, запрошенная вами страница не существует. 
                Возможно, она была удалена, переименована или вы ввели неправильный адрес.
            </p>
            
            <!-- Возможные действия -->
            <div class="error-page__actions">
                <a href="<?php echo Router::getHomeUrl(); ?>" class="button button--primary button--large">
                    <span class="button__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    На главную страницу
                </a>
                
                <button class="button button--secondary button--large" data-action="go-back">
                    <span class="button__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Вернуться назад
                </button>
            </div>
            
            <!-- Полезные ссылки -->
            <div class="error-page__links">
                <h3 class="error-page__links-title">Возможно, вы искали:</h3>
                <div class="error-page__links-grid">
                    <a href="<?php echo Router::getHomeUrl(); ?>" class="error-page__link">
                        <span class="error-page__link-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="error-page__link-text">Разделы курса</span>
                    </a>
                    
                    <a href="<?php echo Router::getAdminUrl(); ?>" class="error-page__link">
                        <span class="error-page__link-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="error-page__link-text">Админ-панель</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Временные стили для 404 страницы (позже будут перенесены в main.css) */
.error-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.error-page__container {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.error-page__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.error-page__icon {
    color: var(--color-error);
    opacity: 0.8;
}

.error-page__title {
    font-size: 6rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0;
    line-height: 1;
}

.error-page__subtitle {
    font-size: 1.5rem;
    font-weight: 500;
    color: var(--color-text-primary);
    margin: 0;
}

.error-page__description {
    font-size: 1rem;
    color: var(--color-text-secondary);
    line-height: 1.6;
    max-width: 400px;
    margin: 0;
}

.error-page__actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    max-width: 300px;
}

.error-page__links {
    width: 100%;
    margin-top: 2rem;
}

.error-page__links-title {
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--color-text-primary);
    margin-bottom: 1rem;
}

.error-page__links-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.error-page__link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    text-decoration: none;
    color: var(--color-text-primary);
    transition: all 0.2s ease;
}

.error-page__link:hover {
    background: var(--color-surface-hover);
    transform: translateY(-1px);
}

.error-page__link-icon {
    color: var(--color-primary);
}

.error-page__link-text {
    font-weight: 500;
}

/* Адаптивность */
@media (width >= 600px) {
    .error-page__actions {
        flex-direction: row;
        max-width: none;
        justify-content: center;
    }
    
    .error-page__links-grid {
        flex-direction: row;
        justify-content: center;
    }
}

@media (width >= 900px) {
    .error-page__title {
        font-size: 8rem;
    }
    
    .error-page__subtitle {
        font-size: 2rem;
    }
}
</style>

<script>
// Обработчик кнопки "Вернуться назад"
document.addEventListener('DOMContentLoaded', function() {
    const goBackButton = document.querySelector('[data-action="go-back"]');
    if (goBackButton) {
        goBackButton.addEventListener('click', function() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        });
    }
});
</script>

<?php
// Подключение подвала
require_once TEMPLATES_PATH . 'footer.php';
?>
