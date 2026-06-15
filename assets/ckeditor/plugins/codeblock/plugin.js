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
                var element = selection.getStartElement();
                
                while (element && element.getName() !== 'pre' && element.getName() !== 'code') {
                    element = element.getParent();
                }
                
                if (element && (element.getName() === 'pre' || element.getName() === 'code')) {
                    var text = element.getText();
                    editor.insertElement(new CKEDITOR.dom.element('p', editor.document)).setText(text);
                    element.remove();
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
