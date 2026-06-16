CKEDITOR.plugins.add('codeblock', {
    init: function(editor) {
        // Добавляем команду для блока кода
        editor.addCommand('insertCodeBlock', {
            exec: function(editor) {
                var selection = editor.getSelection();
                var ranges = selection.getRanges();
                
                if (ranges.length > 0) {
                    var range = ranges[0];
                    var selectedText = selection.getSelectedText();
                    
                    if (selectedText) {
                        // Удаляем лишние пустые строки
                        var cleanedText = selectedText.replace(/\n\s*\n/g, '\n');
                        
                        // Экранируем HTML символы
                        var escapedText = cleanedText
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;');
                        
                        var html = '<pre><code>' + escapedText + '</code></pre>';
                        editor.insertHtml(html);
                    }
                }
            }
        });

        // Добавляем команду для снятия форматирования
        editor.addCommand('removeCodeBlock', {
            exec: function(editor) {
                var selection = editor.getSelection();
                
                // Ищем все элементы pre в документе
                var preElements = editor.document.find('pre');
                
                if (preElements.count() > 0) {
                    // Берём первый найденный элемент pre
                    var codeBlock = preElements.getItem(0);
                    
                    // Получаем текст из блока кода
                    var text = codeBlock.getText();
                    
                    // Экранируем HTML символы, чтобы теги отображались как текст
                    var escapedText = text
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    
                    // Создаем HTML для параграфа с экранированным текстом
                    var html = '<p>' + escapedText.replace(/\n/g, '<br>') + '</p>';
                    
                    // Удаляем блок кода
                    codeBlock.remove();
                    
                    // Вставляем параграф на место блока кода
                    editor.insertHtml(html);
                }
            }
        });

        // Добавляем кнопку "Блок кода" - первая кнопка в тулбаре
        editor.ui.addButton('CodeBlock', {
            label: 'Блок кода',
            command: 'insertCodeBlock',
            toolbar: 'document,1',
            icon: 'code'
        });

        // Добавляем кнопку "Убрать блок кода" - вторая кнопка в тулбаре
        editor.ui.addButton('RemoveCodeBlock', {
            label: 'Убрать',
            command: 'removeCodeBlock',
            toolbar: 'document,2',
            icon: 'unlink'
        });
    }
});
