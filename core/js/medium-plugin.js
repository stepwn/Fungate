var FungateExtension = MediumEditor.Extension.extend({
    name: 'fungate',

    init: function() {
        this.button = this.document.createElement('button');
        this.button.classList.add('medium-editor-action');
        this.button.innerHTML = '<i class="dashicons dashicons-lock"></i>'; // Use WordPress dashicon for lock
        this.button.title = 'Insert Fungate Shortcode';

        this.on(this.button, 'click', this.handleClick.bind(this));
    },

    getButton: function() {
        return this.button;
    },

    handleClick: function(event) {
        // Display your modal here
        document.getElementById('fungate_editorModal').style.display = 'flex';
    }
});
document.addEventListener("DOMContentLoaded", function() {
    var editor = new MediumEditor('textarea', {
        toolbar: {
            buttons: ['bold', 'italic', 'quote', 'fungate'],
            static: true,
            sticky: true
        },
        extensions: {
            fungate: new FungateExtension()
        }
    });
});