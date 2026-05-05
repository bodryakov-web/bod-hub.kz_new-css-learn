/**
 * JavaScript для админ-панели NewCSSLearn
 * Обрабатывает взаимодействие с формами, загрузку файлов и другие функции
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAdminFeatures();
    initializeImageUpload();
    initializeFormValidation();
    initializeDataTables();
    initializeConfirmations();
    
    /**
     * Инициализация общих функций админ-панели
     */
    function initializeAdminFeatures() {
        // Обработка сообщений об операциях
        const messages = document.querySelectorAll('.admin-message');
        messages.forEach(function(message) {
            // Автоматическое скрытие успешных сообщений
            if (message.classList.contains('admin-message--success')) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    
                    setTimeout(function() {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 300);
                }, 5000);
            }
            
            // Клик для закрытия сообщения
            message.addEventListener('click', function() {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                
                setTimeout(function() {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 300);
            });
        });
        
        // Инициализация боковой навигации на мобильных устройствах
        const navToggle = document.querySelector('.admin-nav__toggle');
        const adminNav = document.querySelector('.admin-nav');
        
        if (navToggle && adminNav) {
            navToggle.addEventListener('click', function() {
                adminNav.classList.toggle('admin-nav--open');
                navToggle.classList.toggle('admin-nav__toggle--active');
            });
        }
        
        // Закрытие навигации при клике вне ее
        document.addEventListener('click', function(e) {
            if (adminNav && navToggle && !adminNav.contains(e.target) && !navToggle.contains(e.target)) {
                adminNav.classList.remove('admin-nav--open');
                navToggle.classList.remove('admin-nav__toggle--active');
            }
        });
    }
    
    /**
     * Инициализация загрузки изображений
     */
    function initializeImageUpload() {
        const uploadButtons = document.querySelectorAll('[data-action="upload-image"]');
        
        uploadButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Создаем скрытый input для выбора файла
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/*';
                fileInput.multiple = false;
                
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length > 0) {
                        uploadImage(fileInput.files[0], button);
                    }
                });
                
                fileInput.click();
            });
        });
    }
    
    /**
     * Загрузка изображения на сервер
     * @param {File} file - файл изображения
     * @param {HTMLElement} button - кнопка загрузки
     */
    function uploadImage(file, button) {
        // Проверка размера файла
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            showNotification('Размер файла не должен превышать 5 МБ', 'error');
            return;
        }
        
        // Проверка типа файла
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Разрешены только изображения форматов JPG, PNG, WebP, SVG', 'error');
            return;
        }
        
        // Создаем FormData
        const formData = new FormData();
        formData.append('image', file);
        
        // Добавляем ID урока, если он есть
        const lessonIdInput = document.querySelector('input[name="lesson_id"]');
        if (lessonIdInput) {
            formData.append('lesson_id', lessonIdInput.value);
        }
        
        // Показываем индикатор загрузки
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <span class="loading-spinner"></span>
            Загрузка...
        `;
        
        // Отправляем запрос
        fetch('upload.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Изображение успешно загружено', 'success');
                
                // Вставляем изображение в редактор Quill
                if (window.quill) {
                    const range = window.quill.getSelection(true);
                    window.quill.insertEmbed(range.index, 'image', data.url);
                    window.quill.setSelection(range.index + 1);
                } else if (window.taskEditors) {
                    // Для редакторов задач
                    const activeEditor = Object.values(window.taskEditors)[0];
                    if (activeEditor) {
                        const range = activeEditor.getSelection(true);
                        activeEditor.insertEmbed(range.index, 'image', data.url);
                        activeEditor.setSelection(range.index + 1);
                    }
                }
            } else {
                showNotification(data.message || 'Ошибка при загрузке изображения', 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showNotification('Ошибка при загрузке изображения', 'error');
        })
        .finally(function() {
            // Восстанавливаем кнопку
            button.disabled = false;
            button.innerHTML = originalContent;
        });
    }
    
    /**
     * Инициализация валидации форм
     */
    function initializeFormValidation() {
        const forms = document.querySelectorAll('.admin-form');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('form-input--error');
                        
                        // Показываем сообщение об ошибке
                        let errorMessage = field.parentNode.querySelector('.field-error');
                        if (!errorMessage) {
                            errorMessage = document.createElement('div');
                            errorMessage.className = 'field-error';
                            field.parentNode.appendChild(errorMessage);
                        }
                        errorMessage.textContent = 'Это поле обязательно для заполнения';
                    } else {
                        field.classList.remove('form-input--error');
                        const errorMessage = field.parentNode.querySelector('.field-error');
                        if (errorMessage) {
                            errorMessage.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    showNotification('Пожалуйста, заполните все обязательные поля', 'error');
                    
                    // Прокручиваем к первой ошибке
                    const firstError = form.querySelector('.form-input--error');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                }
            });
            
            // Удаление ошибок при вводе
            const inputs = form.querySelectorAll('.form-input, .form-select');
            inputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    input.classList.remove('form-input--error');
                    const errorMessage = input.parentNode.querySelector('.field-error');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });
        });
    }
    
    /**
     * Инициализация таблиц с данными
     */
    function initializeDataTables() {
        const tables = document.querySelectorAll('.admin-table');
        
        tables.forEach(function(table) {
            // Добавляем сортировку по клику на заголовок
            const headers = table.querySelectorAll('.admin-table__header');
            headers.forEach(function(header) {
                if (header.classList.contains('admin-table__header--actions')) {
                    return; // Пропускаем колонку действий
                }
                
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    sortTable(table, header);
                });
            });
            
            // Добавляем поиск
            const container = table.closest('.admin-table-container');
            if (container && !container.querySelector('.table-search')) {
                const searchBox = document.createElement('div');
                searchBox.className = 'table-search';
                searchBox.innerHTML = `
                    <input type="text" class="table-search__input" placeholder="Поиск...">
                    <button class="table-search__clear" data-action="clear-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                `;
                
                container.insertBefore(searchBox, table);
                
                // Обработчик поиска
                const searchInput = searchBox.querySelector('.table-search__input');
                searchInput.addEventListener('input', function() {
                    filterTable(table, searchInput.value);
                });
                
                // Очистка поиска
                const clearButton = searchBox.querySelector('[data-action="clear-search"]');
                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    filterTable(table, '');
                });
            }
        });
    }
    
    /**
     * Сортировка таблицы
     * @param {HTMLElement} table - таблица
     * @param {HTMLElement} header - заголовок колонки
     */
    function sortTable(table, header) {
        const tbody = table.querySelector('.admin-table__body');
        const rows = Array.from(tbody.querySelectorAll('.admin-table__row'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = !header.classList.contains('sort-desc');
        
        // Удаляем классы сортировки
        header.parentNode.querySelectorAll('.admin-table__header').forEach(function(h) {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Добавляем класс сортировки
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        
        // Сортируем строки
        rows.sort(function(a, b) {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            
            let comparison = 0;
            if (!isNaN(aValue) && !isNaN(bValue)) {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            } else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return isAscending ? comparison : -comparison;
        });
        
        // Перестраиваем DOM
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });
    }
    
    /**
     * Фильтрация таблицы
     * @param {HTMLElement} table - таблица
     * @param {string} searchTerm - поисковый запрос
     */
    function filterTable(table, searchTerm) {
        const tbody = table.querySelector('.admin-table__body');
        const rows = tbody.querySelectorAll('.admin-table__row');
        const searchLower = searchTerm.toLowerCase();
        
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(searchLower);
            row.style.display = isVisible ? '' : 'none';
        });
    }
    
    /**
     * Инициализация подтверждений действий
     */
    function initializeConfirmations() {
        // Добавляем подтверждение для опасных действий
        const dangerousButtons = document.querySelectorAll('[data-confirm]');
        
        dangerousButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                const message = button.getAttribute('data-confirm') || 'Вы уверены?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
    
    /**
     * Показ уведомления
     * @param {string} message - сообщение
     * @param {string} type - тип (success, error, warning)
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <div class="notification__content">
                <span class="notification__icon notification__icon--${type}">
                    ${getNotificationIcon(type)}
                </span>
                <span class="notification__text">${message}</span>
                <button class="notification__close" data-action="close-notification">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        `;
        
        // Добавляем на страницу
        document.body.appendChild(notification);
        
        // Анимация появления
        requestAnimationFrame(function() {
            notification.classList.add('notification--show');
        });
        
        // Обработчик закрытия
        const closeButton = notification.querySelector('[data-action="close-notification"]');
        closeButton.addEventListener('click', function() {
            closeNotification(notification);
        });
        
        // Автоматическое закрытие
        if (type === 'success') {
            setTimeout(function() {
                closeNotification(notification);
            }, 5000);
        }
    }
    
    /**
     * Закрытие уведомления
     * @param {HTMLElement} notification - элемент уведомления
     */
    function closeNotification(notification) {
        notification.classList.remove('notification--show');
        
        setTimeout(function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    /**
     * Получение иконки для уведомления
     * @param {string} type - тип уведомления
     * @returns {string} SVG иконки
     */
    function getNotificationIcon(type) {
        const icons = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="8" x2="12.01" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        };
        
        return icons[type] || icons.info;
    }
    
    /**
     * Инициализация всплывающих подсказок для Quill редактора
     */
    function initializeQuillTooltips() {
        // Находим все панели инструментов Quill
        const toolbars = document.querySelectorAll('.ql-toolbar');
        
        toolbars.forEach((toolbar, index) => {
            // Добавляем подсказки к кнопкам
            const allButtons = toolbar.querySelectorAll('button');
            allButtons.forEach(button => {
                const classes = button.className;
                const value = button.value || '';
                
                // Простые кнопки без значения
                if (classes.includes('ql-bold')) {
                    button.setAttribute('data-tooltip', 'Жирный текст (Ctrl+B)');
                    button.setAttribute('aria-label', 'Жирный текст (Ctrl+B)');
                } else if (classes.includes('ql-italic')) {
                    button.setAttribute('data-tooltip', 'Курсив (Ctrl+I)');
                    button.setAttribute('aria-label', 'Курсив (Ctrl+I)');
                } else if (classes.includes('ql-underline')) {
                    button.setAttribute('data-tooltip', 'Подчеркнутый текст (Ctrl+U)');
                    button.setAttribute('aria-label', 'Подчеркнутый текст (Ctrl+U)');
                } else if (classes.includes('ql-strike')) {
                    button.setAttribute('data-tooltip', 'Зачеркнутый текст');
                    button.setAttribute('aria-label', 'Зачеркнутый текст');
                } else if (classes.includes('ql-blockquote')) {
                    button.setAttribute('data-tooltip', 'Цитата');
                    button.setAttribute('aria-label', 'Цитата');
                } else if (classes.includes('ql-code-block')) {
                    button.setAttribute('data-tooltip', 'Блок кода');
                    button.setAttribute('aria-label', 'Блок кода');
                } else if (classes.includes('ql-link')) {
                    button.setAttribute('data-tooltip', 'Вставить ссылку');
                    button.setAttribute('aria-label', 'Вставить ссылку');
                } else if (classes.includes('ql-image')) {
                    button.setAttribute('data-tooltip', 'Вставить изображение');
                    button.setAttribute('aria-label', 'Вставить изображение');
                } else if (classes.includes('ql-clean')) {
                    button.setAttribute('data-tooltip', 'Удалить форматирование');
                    button.setAttribute('aria-label', 'Удалить форматирование');
                }
                
                // Кнопки со значениями
                if (classes.includes('ql-list')) {
                    if (value === 'ordered') {
                        button.setAttribute('data-tooltip', 'Нумерованный список');
                        button.setAttribute('aria-label', 'Нумерованный список');
                    } else if (value === 'bullet') {
                        button.setAttribute('data-tooltip', 'Маркированный список');
                        button.setAttribute('aria-label', 'Маркированный список');
                    }
                } else if (classes.includes('ql-script')) {
                    if (value === 'sub') {
                        button.setAttribute('data-tooltip', 'Подстрочный индекс');
                        button.setAttribute('aria-label', 'Подстрочный индекс');
                    } else if (value === 'super') {
                        button.setAttribute('data-tooltip', 'Надстрочный индекс');
                        button.setAttribute('aria-label', 'Надстрочный индекс');
                    }
                } else if (classes.includes('ql-indent')) {
                    if (value === '-1') {
                        button.setAttribute('data-tooltip', 'Уменьшить отступ');
                        button.setAttribute('aria-label', 'Уменьшить отступ');
                    } else if (value === '+1') {
                        button.setAttribute('data-tooltip', 'Увеличить отступ');
                        button.setAttribute('aria-label', 'Увеличить отступ');
                    }
                } else if (classes.includes('ql-direction')) {
                    if (value === 'rtl') {
                        button.setAttribute('data-tooltip', 'Направление текста справа налево');
                        button.setAttribute('aria-label', 'Направление текста справа налево');
                    }
                }
            });
            
            // Инициализация обработчиков событий для подсказок
            const buttons = toolbar.querySelectorAll('button[data-tooltip]');
            
            buttons.forEach((button, btnIndex) => {
                let timeout = null;
                
                button.addEventListener('mouseenter', function(e) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        showQuillTooltip(button, e);
                    }, 300);
                });
                
                button.addEventListener('mouseleave', function() {
                    clearTimeout(timeout);
                    hideQuillTooltip();
                });
                
                button.addEventListener('focus', function(e) {
                    showQuillTooltip(button, e);
                });
                
                button.addEventListener('blur', function() {
                    hideQuillTooltip();
                });
            });
        });
    }
    
    /**
     * Показать всплывающую подсказку
     * @param {HTMLElement} button - кнопка
     * @param {Event} event - событие
     */
    function showQuillTooltip(button, event) {
        hideQuillTooltip(); // Сначала скрываем существующие подсказки
        
        const tooltipText = button.getAttribute('data-tooltip');
        if (!tooltipText) return;
        
        const tooltip = document.createElement('div');
        tooltip.className = 'quill-tooltip';
        tooltip.textContent = tooltipText;
        
        // Добавляем подсказку в DOM
        document.body.appendChild(tooltip);
        
        // Простое позиционирование под курсором
        const mouseX = event.clientX || 0;
        const mouseY = event.clientY || 0;
        
        // Позиционируем подсказку под курсором
        tooltip.style.position = 'fixed';
        tooltip.style.left = (mouseX + 5) + 'px';
        tooltip.style.top = (mouseY + 15) + 'px';
        
        // Показываем подсказку с анимацией
        requestAnimationFrame(() => {
            tooltip.classList.add('quill-tooltip--show');
        });
        
        // Сохраняем ссылку для последующего скрытия
        window.currentQuillTooltip = tooltip;
    }
    
    /**
     * Скрыть всплывающую подсказку
     */
    function hideQuillTooltip() {
        if (window.currentQuillTooltip) {
            window.currentQuillTooltip.classList.remove('quill-tooltip--show');
            
            setTimeout(() => {
                if (window.currentQuillTooltip && window.currentQuillTooltip.parentNode) {
                    window.currentQuillTooltip.parentNode.removeChild(window.currentQuillTooltip);
                }
                window.currentQuillTooltip = null;
            }, 200);
        }
    }
    
        
    // Экспортируем функции для использования в других скриптах
    window.adminUtils = {
        showNotification: showNotification,
        uploadImage: uploadImage,
        sortTable: sortTable,
        filterTable: filterTable,
        initializeQuillTooltips: initializeQuillTooltips,
        showQuillTooltip: showQuillTooltip,
        hideQuillTooltip: hideQuillTooltip
    };
});
