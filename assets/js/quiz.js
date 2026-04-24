/**
 * JavaScript для системы тестов NewCSSLearn
 * Обрабатывает взаимодействие с тестами и показывает результаты
 */

document.addEventListener('DOMContentLoaded', function() {
    // Обработка кликов на варианты ответов
    const quizContainer = document.querySelector('.quiz-container');
    
    if (!quizContainer) {
        return;
    }
    
    // Находим все вопросы теста
    const questions = quizContainer.querySelectorAll('.quiz-question');
    
    questions.forEach(function(question) {
        const answers = question.querySelectorAll('.quiz-answer');
        const questionId = question.getAttribute('data-question-id');
        const resultElement = question.querySelector('.quiz-question__result');
        
        answers.forEach(function(answer) {
            const radioInput = answer.querySelector('.quiz-answer__input');
            
            radioInput.addEventListener('change', function() {
                // Проверяем ответ
                checkAnswer(question, answer, resultElement);
                
                // Блокируем остальные варианты в этом вопросе
                answers.forEach(function(otherAnswer) {
                    if (otherAnswer !== answer) {
                        const otherRadio = otherAnswer.querySelector('.quiz-answer__input');
                        otherRadio.disabled = true;
                    }
                });
            });
        });
    });
    
    /**
     * Проверка ответа на вопрос
     * @param {HTMLElement} question - элемент вопроса
     * @param {HTMLElement} selectedAnswer - выбранный ответ
     * @param {HTMLElement} resultElement - элемент для отображения результата
     */
    function checkAnswer(question, selectedAnswer, resultElement) {
        const radioInput = selectedAnswer.querySelector('.quiz-answer__input');
        const isCorrect = radioInput.getAttribute('data-correct') === 'true';
        
        // Показываем иконку результата
        if (isCorrect) {
            resultElement.setAttribute('data-result', 'correct');
            resultElement.innerHTML = `
                <span class="quiz-question__icon quiz-question__icon--correct">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            `;
            
            // Добавляем класс правильного ответа
            selectedAnswer.classList.add('quiz-answer--correct');
        } else {
            resultElement.setAttribute('data-result', 'incorrect');
            resultElement.innerHTML = `
                <span class="quiz-question__icon quiz-question__icon--incorrect">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            `;
            
            // Добавляем класс неправильного ответа
            selectedAnswer.classList.add('quiz-answer--incorrect');
            
            // Показываем правильный ответ
            const answers = question.querySelectorAll('.quiz-answer');
            answers.forEach(function(answer) {
                const answerRadio = answer.querySelector('.quiz-answer__input');
                if (answerRadio.getAttribute('data-correct') === 'true') {
                    answer.classList.add('quiz-answer--correct');
                }
            });
        }
        
        // Добавляем анимацию появления результата
        resultElement.style.opacity = '0';
        resultElement.style.transform = 'scale(0.8)';
        
        setTimeout(function() {
            resultElement.style.transition = 'all 0.3s ease';
            resultElement.style.opacity = '1';
            resultElement.style.transform = 'scale(1)';
        }, 100);
    }
    
    /**
     * Подсчет статистики прохождения теста
     */
    function updateQuizStatistics() {
        const totalQuestions = questions.length;
        const correctAnswers = quizContainer.querySelectorAll('.quiz-question__result[data-result="correct"]').length;
        const incorrectAnswers = quizContainer.querySelectorAll('.quiz-question__result[data-result="incorrect"]').length;
        const answeredQuestions = correctAnswers + incorrectAnswers;
        
        // Если есть контейнер для статистики, обновляем его
        const statsContainer = document.querySelector('.quiz-stats');
        if (statsContainer) {
            statsContainer.innerHTML = `
                <div class="quiz-stats__item">
                    <span class="quiz-stats__label">Отвечено:</span>
                    <span class="quiz-stats__value">${answeredQuestions} / ${totalQuestions}</span>
                </div>
                <div class="quiz-stats__item">
                    <span class="quiz-stats__label">Правильно:</span>
                    <span class="quiz-stats__value quiz-stats__value--correct">${correctAnswers}</span>
                </div>
                <div class="quiz-stats__item">
                    <span class="quiz-stats__label">Неправильно:</span>
                    <span class="quiz-stats__value quiz-stats__value--incorrect">${incorrectAnswers}</span>
                </div>
            `;
        }
    }
    
    // Обновляем статистику при каждом ответе
    questions.forEach(function(question) {
        const resultElement = question.querySelector('.quiz-question__result');
        
        // Создаем MutationObserver для отслеживания изменений
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-result') {
                    updateQuizStatistics();
                }
            });
        });
        
        observer.observe(resultElement, {
            attributes: true,
            attributeFilter: ['data-result']
        });
    });
    
    // Инициализация статистики
    updateQuizStatistics();
    
    /**
     * Функция сброса теста (для перезапуска)
     */
    window.resetQuiz = function() {
        questions.forEach(function(question) {
            const answers = question.querySelectorAll('.quiz-answer');
            const resultElement = question.querySelector('.quiz-question__result');
            
            // Сбрасываем радио-кнопки
            answers.forEach(function(answer) {
                const radioInput = answer.querySelector('.quiz-answer__input');
                radioInput.checked = false;
                radioInput.disabled = false;
                
                // Удаляем классы результатов
                answer.classList.remove('quiz-answer--correct', 'quiz-answer--incorrect');
            });
            
            // Скрываем результат
            resultElement.setAttribute('data-result', 'pending');
            resultElement.innerHTML = `
                <span class="quiz-question__icon quiz-question__icon--pending">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" fill="currentColor">?</text>
                    </svg>
                </span>
            `;
        });
        
        // Обновляем статистику
        updateQuizStatistics();
        
        // Прокручиваем к началу теста
        quizContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    
    /**
     * Функция показа всех правильных ответов (для подсказки)
     */
    window.showAllAnswers = function() {
        questions.forEach(function(question) {
            const answers = question.querySelectorAll('.quiz-answer');
            
            answers.forEach(function(answer) {
                const radioInput = answer.querySelector('.quiz-answer__input');
                if (radioInput.getAttribute('data-correct') === 'true') {
                    answer.classList.add('quiz-answer--show-correct');
                }
            });
        });
    };
    
    // Добавляем кнопки управления тестом (если есть контейнер)
    const controlsContainer = document.querySelector('.quiz-controls');
    if (controlsContainer) {
        controlsContainer.innerHTML = `
            <button class="button button--secondary" onclick="resetQuiz()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 4v6h6M23 20v-6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Начать заново
            </button>
            <button class="button button--outline" onclick="showAllAnswers()">
                <span class="button__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Показать ответы
            </button>
        `;
    }
});
