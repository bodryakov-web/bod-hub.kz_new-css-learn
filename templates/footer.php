<?php
/**
 * Шаблон подвала сайта NewCSSLearn
 * Содержит копирайт и скрипты
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}
?>
        </div>
    </main>

    <!-- Подвал сайта -->
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__content">
                <p class="footer__copyright">
                    © <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Учебный курс по современному CSS
                </p>
                <p class="footer__description">
                    Изучение новых возможностей CSS с 2017 года: Grid, Custom Properties, Container Queries и многое другое
                </p>
            </div>
        </div>
    </footer>

    <!-- Подключение JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/theme-toggle.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($requireQuiz) && $requireQuiz): ?>
    <script src="<?php echo APP_URL; ?>/assets/js/quiz.js"></script>
    <?php endif; ?>

    <!-- Дополнительные скрипты для админ-панели -->
    <?php if (isset($isAdmin) && $isAdmin): ?>
    <script src="<?php echo APP_URL; ?>/assets/js/admin.js"></script>
    <?php endif; ?>

</body>
</html>
