(function() {
    tinymce.create('tinymce.plugins.Fungate', {
        init: function(editor, url) {
            editor.addButton('fungate', {
                title: 'Token Gate Settings',
                icon:'lock',
                onclick: function() {
                    document.getElementById('fungate_editorModal').style.display='flex';
                    sendShortcodeToIframe(document.getElementById('fungate_visual_editor'),'[fungate schedule={}');
                }
            });
        },
        createControl: function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('fungate_button_script', tinymce.plugins.Fungate);
})();
