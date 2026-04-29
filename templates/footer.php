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
                    <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Учебный курс по современному CSS
                </p>
            </div>
        </div>
    </footer>

    <!-- Подключение JavaScript -->
    <script src="<?php echo getAssetUrl('js/theme-toggle.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/main.js'); ?>"></script>
    
    <?php if (isset($requireQuiz) && $requireQuiz): ?>
    <script src="<?php echo getAssetUrl('js/quiz.js'); ?>"></script>
    <?php endif; ?>

    <!-- Дополнительные скрипты для админ-панели -->
    <?php if (isset($isAdmin) && $isAdmin): ?>
    <script src="<?php echo getAssetUrl('js/admin.js'); ?>"></script>
    <?php endif; ?>

</body>
</html>
