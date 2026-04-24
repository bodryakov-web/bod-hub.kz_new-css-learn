<?php
/**
 * Тестовый файл для проверки доступности CSS и JS файлов
 */

// Определяем константу для безопасности
define('NEW_CSS_LEARN', true);

// Подключение конфигурации
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест путей к CSS файлам</title>
    
    <!-- Пробуем разные варианты путей -->
    <link rel="stylesheet" href="/assets/css/main.css" title="Абсолютный путь от домена">
    <link rel="stylesheet" href="assets/css/admin.css" title="Относительный путь">
    <link rel="stylesheet" href="../assets/css/themes.css" title="Относительный путь на уровень вверх">
    
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .test-section { 
            margin: 20px 0; 
            padding: 15px; 
            border: 1px solid #ccc; 
            background: white;
            border-radius: 5px;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .path-test { margin: 10px 0; padding: 10px; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Тест доступности CSS файлов</h1>
    
    <div class="test-section">
        <h2>Информация о конфигурации:</h2>
        <p><strong>APP_URL:</strong> <?php echo APP_URL; ?></p>
        <p><strong>REQUEST_URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'не определен'; ?></p>
        <p><strong>SCRIPT_NAME:</strong> <?php echo $_SERVER['SCRIPT_NAME'] ?? 'не определен'; ?></p>
        <p><strong>Текущая директория:</strong> <?php echo __DIR__; ?></p>
    </div>
    
    <div class="test-section">
        <h2>Тест стилей:</h2>
        <div class="path-test">
            <p>Если стили admin.css загрузились, этот блок будет иметь стили админ-панели:</p>
            <div class="admin-header">
                <h3>Тест заголовка админ-панели</h3>
            </div>
        </div>
        
        <div class="path-test">
            <p>Если стили main.css загрузились, переменные CSS будут работать:</p>
            <div style="background: var(--color-primary, #ccc); color: white; padding: 10px;">
                Это блок должен быть синим, если CSS переменные загрузились
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Проверка загрузки CSS файлов через JavaScript:</h2>
        <div id="css-test-results"></div>
    </div>
    
    <script>
        // Проверяем, какие CSS файлы загрузились
        window.onload = function() {
            const results = document.getElementById('css-test-results');
            const links = document.querySelectorAll('link[rel="stylesheet"]');
            let html = '<h3>Результаты проверки CSS:</h3>';
            
            links.forEach((link, index) => {
                const title = link.getAttribute('title') || `CSS файл ${index + 1}`;
                const url = link.href;
                
                // Проверяем, загрузился ли CSS файл
                fetch(url, { method: 'HEAD' })
                    .then(response => {
                        const status = response.ok ? 
                            '<span class="success">✓ Загружен</span>' : 
                            '<span class="error">✗ Ошибка ' + response.status + '</span>';
                        
                        html += `<div class="path-test">
                            <strong>${title}:</strong><br>
                            URL: ${url}<br>
                            Статус: ${status}
                        </div>`;
                        
                        results.innerHTML = html;
                    })
                    .catch(error => {
                        html += `<div class="path-test">
                            <strong>${title}:</strong><br>
                            URL: ${url}<br>
                            Статус: <span class="error">✗ Ошибка сети</span><br>
                            Ошибка: ${error.message}
                        </div>`;
                        
                        results.innerHTML = html;
                    });
            });
        };
    </script>
</body>
</html>
