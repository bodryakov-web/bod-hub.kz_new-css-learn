/**
 * JavaScript для переключения тем NewCSSLearn
 * Обрабатывает переключение между светлой и темной темами
 */

document.addEventListener('DOMContentLoaded', function() {
    // Находим кнопку переключения темы
    const themeToggle = document.querySelector('[data-action="toggle-theme"]');
    
    if (!themeToggle) {
        return;
    }
    
    /**
     * Получение текущей темы
     * @returns {string} 'light' или 'dark'
     */
    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }
    
    /**
     * Установка темы
     * @param {string} theme - 'light' или 'dark'
     */
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Обновляем иконки кнопки
        const icons = {
            light: themeToggle.querySelector('[data-theme-icon="light"]'),
            dark: themeToggle.querySelector('[data-theme-icon="dark"]')
        };
        
        if (icons.light && icons.dark) {
            if (theme === 'dark') {
                icons.light.style.display = 'none';
                icons.dark.style.display = 'block';
            } else {
                icons.light.style.display = 'block';
                icons.dark.style.display = 'none';
            }
        }
        
        // Обновляем aria-label
        themeToggle.setAttribute('aria-label', 
            theme === 'dark' ? 'Переключить на светлую тему' : 'Переключить на темную тему'
        );
        
        // Сохраняем выбор в localStorage
        localStorage.setItem('theme', theme);
        
        // Устанавливаем cookie для PHP
        document.cookie = `theme=${theme}; path=/; max-age=${365 * 24 * 60 * 60}; SameSite=Strict`;
    }
    
    /**
     * Переключение темы
     */
    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    }
    
    /**
     * Инициализация темы при загрузке страницы
     */
    function initializeTheme() {
        // Приоритет: cookie > localStorage > системные настройки > светлая тема
        let theme = 'light';
        
        // Проверяем cookie
        const cookieMatch = document.cookie.match(/theme=([^;]+)/);
        if (cookieMatch) {
            theme = cookieMatch[1];
        } else if (localStorage.getItem('theme')) {
            // Проверяем localStorage
            theme = localStorage.getItem('theme');
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // Используем системные настройки
            theme = 'dark';
        }
        
        setTheme(theme);
    }
    
    /**
     * Отслеживание системных настроек темы
     */
    function watchSystemTheme() {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Изменяем тему только если пользователь не выбирал ее вручную
            mediaQuery.addEventListener('change', function(e) {
                if (!localStorage.getItem('theme') && !document.cookie.match(/theme=/)) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
    
    // Обработчик клика по кнопке
    themeToggle.addEventListener('click', function(e) {
        e.preventDefault();
        toggleTheme();
    });
    
    // Обработчик клавиатуры для доступности
    themeToggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleTheme();
        }
    });
    
    // Инициализация
    initializeTheme();
    watchSystemTheme();
    
    // Добавляем возможность переключения темы горячими клавишами
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Shift + T для переключения темы
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
            e.preventDefault();
            toggleTheme();
        }
    });
    
    // Экспортируем функции для использования в других скриптах
    window.themeManager = {
        getCurrentTheme: getCurrentTheme,
        setTheme: setTheme,
        toggleTheme: toggleTheme
    };
});
