<?php
/**
 * Шаблон подвала админ-панели NewCSSLearn
 * Содержит скрипты и закрывающие теги
 */

// Предотвращение прямого доступа
if (!defined('NEW_CSS_LEARN')) {
    exit('Прямой доступ запрещен');
}
?>
        </div>
    </main>

    <!-- Подвал админ-панели -->
    <footer class="admin-footer">
        <div class="admin-footer__container">
            <div class="admin-footer__content">
                <p class="admin-footer__copyright">
                    © <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Админ-панель
                </p>
            </div>
        </div>
    </footer>

    <!-- Подключение JavaScript -->
    <script src="/assets/js/theme-toggle.js"></script>
    <script src="/assets/js/admin.js"></script>

</body>
</html>
