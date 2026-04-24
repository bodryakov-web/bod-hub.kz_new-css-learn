/**
 * Основной JavaScript файл NewCSSLearn
 * Содержит общую функциональность для всего сайта
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initializeNavigation();
    initializeSmoothScroll();
    initializeExternalLinks();
    initializeTooltips();
    initializeCopyCode();
    initializeImageLazyLoading();
    
    /**
     * Инициализация навигации и мобильного меню
     */
    function initializeNavigation() {
        // Плавная прокрутка к якорям
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        
        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Обработка хлебных крошек на мобильных устройствах
        const breadcrumbs = document.querySelector('.breadcrumbs');
        if (breadcrumbs) {
            const isMobile = window.innerWidth < 600;
            
            if (isMobile) {
                // Добавляем возможность горизонтальной прокрутки
                breadcrumbs.style.overflowX = 'auto';
                breadcrumbs.style.webkitOverflowScrolling = 'touch';
            }
        }
    }
    
    /**
     * Инициализация плавной прокрутки
     */
    function initializeSmoothScroll() {
        // Улучшенная плавная прокрутка для всех браузеров
        if ('scrollBehavior' in document.documentElement.style) {
            // Браузер поддерживает нативную плавную прокрутку
            return;
        }
        
        // Полифилл для старых браузеров
        document.documentElement.style.scrollBehavior = 'smooth';
    }
    
    /**
     * Инициализация внешних ссылок
     */
    function initializeExternalLinks() {
        const externalLinks = document.querySelectorAll('a[href^="http"]:not([href*="' + window.location.hostname + '"])');
        
        externalLinks.forEach(function(link) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
            
            // Добавляем иконку внешней ссылки
            if (!link.querySelector('.external-link-icon')) {
                const icon = document.createElement('span');
                icon.className = 'external-link-icon';
                icon.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="15,3 21,3 21,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;
                link.appendChild(icon);
            }
        });
    }
    
    /**
     * Инициализация всплывающих подсказок
     */
    function initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[title], [data-tooltip]');
        
        tooltipElements.forEach(function(element) {
            const title = element.getAttribute('title') || element.getAttribute('data-tooltip');
            
            if (!title) return;
            
            // Удаляем стандартный title чтобы избежать двойных подсказок
            element.removeAttribute('title');
            
            let tooltip = null;
            let timeout = null;
            
            // Показ подсказки
            function showTooltip() {
                clearTimeout(timeout);
                
                tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = title;
                document.body.appendChild(tooltip);
                
                // Позиционирование подсказки
                const rect = element.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                
                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                let top = rect.top - tooltipRect.height - 10;
                
                // Проверка границ экрана
                if (left < 10) left = 10;
                if (left + tooltipRect.width > window.innerWidth - 10) {
                    left = window.innerWidth - tooltipRect.width - 10;
                }
                if (top < 10) {
                    top = rect.bottom + 10;
                }
                
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(-5px)';
                
                // Анимация появления
                requestAnimationFrame(function() {
                    tooltip.style.transition = 'all 0.2s ease';
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateY(0)';
                });
            }
            
            // Скрытие подсказки
            function hideTooltip() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    if (tooltip) {
                        tooltip.style.opacity = '0';
                        tooltip.style.transform = 'translateY(-5px)';
                        
                        setTimeout(function() {
                            if (tooltip && tooltip.parentNode) {
                                tooltip.parentNode.removeChild(tooltip);
                                tooltip = null;
                            }
                        }, 200);
                    }
                }, 100);
            }
            
            // Обработчики событий
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
            element.addEventListener('focus', showTooltip);
            element.addEventListener('blur', hideTooltip);
        });
    }
    
    /**
     * Инициализация копирования кода
     */
    function initializeCopyCode() {
        const codeBlocks = document.querySelectorAll('pre code');
        
        codeBlocks.forEach(function(codeBlock) {
            const pre = codeBlock.parentElement;
            
            // Создаем кнопку копирования
            const copyButton = document.createElement('button');
            copyButton.className = 'copy-button';
            copyButton.setAttribute('data-action', 'copy-code');
            copyButton.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="copy-button__text">Копировать</span>
            `;
            
            // Добавляем кнопку в контейнер
            const container = document.createElement('div');
            container.className = 'code-container';
            container.appendChild(copyButton);
            pre.parentNode.insertBefore(container, pre);
            container.appendChild(pre);
            
            // Обработчик копирования
            copyButton.addEventListener('click', async function() {
                try {
                    const text = codeBlock.textContent;
                    
                    // Используем современный Clipboard API
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(text);
                    } else {
                        // Fallback для старых браузеров
                        const textArea = document.createElement('textarea');
                        textArea.value = text;
                        textArea.style.position = 'fixed';
                        textArea.style.opacity = '0';
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                    }
                    
                    // Показываем успешное копирование
                    copyButton.classList.add('copy-button--success');
                    copyButton.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="copy-button__text">Скопировано!</span>
                    `;
                    
                    // Возвращаем исходное состояние через 2 секунды
                    setTimeout(function() {
                        copyButton.classList.remove('copy-button--success');
                        copyButton.innerHTML = `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="copy-button__text">Копировать</span>
                        `;
                    }, 2000);
                    
                } catch (err) {
                    console.error('Ошибка при копировании:', err);
                    
                    // Показываем ошибку
                    copyButton.classList.add('copy-button--error');
                    copyButton.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="copy-button__text">Ошибка</span>
                    `;
                    
                    setTimeout(function() {
                        copyButton.classList.remove('copy-button--error');
                        copyButton.innerHTML = `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="copy-button__text">Копировать</span>
                        `;
                    }, 2000);
                }
            });
        });
    }
    
    /**
     * Инициализация ленивой загрузки изображений
     */
    function initializeImageLazyLoading() {
        // Проверяем поддержку нативной ленивой загрузки
        if ('loading' in HTMLImageElement.prototype) {
            // Браузер поддерживает нативную ленивую загрузку
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(function(img) {
                img.src = img.getAttribute('data-src');
                img.removeAttribute('data-src');
            });
            return;
        }
        
        // Полифилл для старых браузеров
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('data-src');
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });
            
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }
    
    /**
     * Обработка ошибок загрузки изображений
     */
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG') {
            e.target.classList.add('image-error');
            
            // Добавляем плейсхолдер для ошибочных изображений
            if (!e.target.classList.contains('error-placeholder-added')) {
                e.target.classList.add('error-placeholder-added');
                e.target.alt = 'Изображение не загрузилось';
                e.target.style.backgroundColor = 'var(--color-surface-hover)';
                e.target.style.border = '2px dashed var(--color-border)';
            }
        }
    }, true);
    
    /**
     * Улучшение доступности клавиатурной навигации
     */
    document.addEventListener('keydown', function(e) {
        // Escape для закрытия модальных окон
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.active');
            modals.forEach(function(modal) {
                modal.classList.remove('active');
            });
        }
        
        // Tab для улучшенной навигации
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    /**
     * Обработка изменения размера окна
     */
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Обновляем позиционирование подсказок
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(function(tooltip) {
                if (tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            });
        }, 250);
    });
    
    // Экспортируем полезные функции
    window.siteUtils = {
        scrollToElement: function(element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        },
        
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };
});
