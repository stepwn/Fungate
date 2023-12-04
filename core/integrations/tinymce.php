<?php
function fungate_enqueue_tinymce_plugin() {
    if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
        add_filter('mce_external_plugins', 'add_fungate_tinymce_plugin');
        add_filter('mce_buttons', 'register_fungate_tinymce_button');
    }
}
add_action('admin_head', 'fungate_enqueue_tinymce_plugin');

function add_fungate_tinymce_plugin($plugin_array) {
    $plugin_array['fungate_button_script'] = plugins_url('../js/fungate-tinymce-plugin.js', __FILE__);
    return $plugin_array;
}

function register_fungate_tinymce_button($buttons) {
    array_push($buttons, 'fungate');
    return $buttons;
}

function fungate_add_modal_to_footer() {
    ?>
    <style>
        #fungate_editorModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index:10000;
}
#fungate_modalContent {
    background: #fff;
    padding: 20px;
    position: relative;
}
#fungate_closeEditorBtn {
    position: absolute;
    top: 10px;
    right: 10px;
}

        </style>
    <script>
        // Listen for messages from the iframe
        var lastMessageData = null;
        var clickedElement = null;
        function sendShortcodeToIframe(element) {
            // Traverse up the DOM to find the nearest ancestor with a 'data-shortcode' attribute
            while (element && !element.dataset.shortcode) {
                element = element.parentElement;
            }
            clickedElement = element;
            // Ensure an element with a 'data-shortcode' attribute was found
            if (element && element.dataset.shortcode) {
                var shortcode = element.dataset.shortcode;

                // Display the modal
                document.getElementById('fungate_editorModal').style.display='flex';
                
                // Send the shortcode to the iframe
                var iframe = document.getElementById('fungate_visual_editor');
                iframe.contentWindow.postMessage(shortcode, '*');
        }
}
document.addEventListener('DOMContentLoaded', function() {
    // Ensure TinyMCE is loaded and editors are initialized
    if (typeof tinyMCE !== 'undefined') {
        tinyMCE.on('AddEditor', function(e) {
            e.editor.on('init', function() {
                // Get the iframe's document
                var iframeDoc = this.getDoc();

                // Add click event listener to the iframe's document
                iframeDoc.body.addEventListener('click', function(e) {
                    if(e.target.classList.contains('editableShortcode')) {
                        sendShortcodeToIframe(e.target);
                    }
                });
            });
        });
    }
});

window.addEventListener('message', function(event) {
    if (typeof event.data === 'string' && event.data.startsWith('[fungate')) {
        if (event.data === lastMessageData) {
            console.log('Ignoring duplicate message');
            lastMessageData = '';
            return;
        }
        lastMessageData = event.data;

        console.log('Received shortcode:', event.data);
        document.getElementById('fungate_editorModal').style.display = 'none';
        console.log('Received message:', event.data, 'from origin:', event.origin, 'and source:', event.source);
        
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
        // Generate a random color
        function getRandomColor() {
        // Minimum RGB values to ensure the color is not too dark
        var minRed = 100;
        var minGreen = 100;
        var minBlue = 100;

        // Generate random RGB values
        var red = Math.floor(Math.random() * (256 - minRed) + minRed).toString(16);
        var green = Math.floor(Math.random() * (256 - minGreen) + minGreen).toString(16);
        var blue = Math.floor(Math.random() * (256 - minBlue) + minBlue).toString(16);

        // Ensure each color component is two digits
        if (red.length === 1) red = '0' + red;
        if (green.length === 1) green = '0' + green;
        if (blue.length === 1) blue = '0' + blue;

        return '#' + red + green + blue;
    }

    var randomColor = getRandomColor();

     // Check if clickedElement is not null
     var escapedData = event.data.replace(/"/g, '&quot;');
     if (clickedElement) {
        // Update the data-shortcode attribute of the clicked element
        clickedElement.setAttribute('data-shortcode', escapedData);

        // Reset clickedElement to null
        clickedElement = null;
    }
    else{
        // Insert the shortcode and highlighted text into the editor
    tinyMCE.activeEditor.execCommand('mceInsertContent', false, 
    '<div contenteditable="false" data-shortcode="'+escapedData+'" style="border-bottom:2px solid '+randomColor+';border-top:2px solid '+randomColor+'">' +
        '<span style="background-color: ' + randomColor + ';"><button contenteditable="false" class="editableShortcode" style="background-color:'+randomColor+'"><i class="editableShortcode dashicons dashicons-lock"></button><br></span>' +
        '<div contenteditable="true">Token Gated Content goes HERE</div>' +
        '<span style="background-color: ' + randomColor + ';"><button contenteditable="false" class="editableShortcode" style="background-color:'+randomColor+'"><i class="editableShortcode dashicons dashicons-lock"></button><br></span>' +
    '</div><p>&nbsp;</p>'
    );
    }
    

    }

    }
}, true);



    </script>
    <div id="fungate_editorModal" style="display:none;width:100%">
        <div id="fungate_modalContent" style="width:90%">
            <button id="fungate_closeEditorBtn" onclick="document.getElementById('fungate_editorModal').style.display='none';">Close</button>
            <iframe id='fungate_visual_editor' src="<?php echo plugins_url('../html/editor.html', __FILE__); ?>" width="100%" height="90%"></iframe>
        </div>
    </div>
    <?php
}
add_action('admin_print_footer_scripts', 'fungate_add_modal_to_footer');

function strip_out_shortcode_junk($content) {
    // Use a regular expression to match the structure you've added
    $pattern = '/<div .*?data-shortcode=\\\"(.*?)\\\".*?>.*?<div contenteditable=\\\"true\\\">(.*?)<\/div>.*?<\/div>/s';

    // Replace the matched structure with just the shortcode wrapped around the content
    $replacement = '$1$2[/fungate]'; // Assuming [fungate] is the opening tag and [/fungate] is the closing tag

    $content = preg_replace($pattern, $replacement, $content);

    return $content;
}

add_filter('content_save_pre', 'strip_out_shortcode_junk');

function restore_shortcode_to_div_tinymce($content) {
    // Use a regular expression to match the shortcode structure
    $pattern = '/\[fungate schedule=\'(.*?)\'\](.*?)\[\/fungate\]/';
    

    // Generate a random color
    function getRandomColor() {
        $minRed = 100;
        $minGreen = 100;
        $minBlue = 100;

        $red = str_pad(dechex(mt_rand($minRed, 255)), 2, '0', STR_PAD_LEFT);
        $green = str_pad(dechex(mt_rand($minGreen, 255)), 2, '0', STR_PAD_LEFT);
        $blue = str_pad(dechex(mt_rand($minBlue, 255)), 2, '0', STR_PAD_LEFT);

        return '#' . $red . $green . $blue;
    }

    // Callback function for preg_replace_callback
    function replace_shortcode($matches) {
        $randomColor = getRandomColor();
        // Encode the JSON string
        $encodedJson = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
        return '<div contenteditable="false" data-shortcode="[fungate schedule=\'' . $encodedJson . '\']" style="border-bottom:2px solid ' . $randomColor . ';border-top:2px solid ' . $randomColor . '">' .
            '<span style="background-color: ' . $randomColor . ';"><button contenteditable="false" class="editableShortcode" style="background-color:' . $randomColor . '"><i class="editableShortcode dashicons dashicons-lock"></i></button><br></span>' .
            '<div contenteditable="true">' . $matches[2] . '</div>' .  // This is where the content should be placed
            '<span style="background-color: ' . $randomColor . ';"><button contenteditable="false" class="editableShortcode" style="background-color:' . $randomColor . '"><i class="editableShortcode dashicons dashicons-lock"></i></button><br></span>' .
        '</div><p>&nbsp;</p>';
    }
    
    

    if (is_array($content)) {
        $content = implode(" ", $content);
    }

    $content = html_entity_decode($content);

    if (preg_match($pattern, $content)) {
        error_log("Match found!");
    } else {
        error_log("No match found.");
    }

    $content = preg_replace_callback($pattern, 'replace_shortcode', $content);

    return $content;
}

add_filter('content_edit_pre', 'restore_shortcode_to_div_tinymce');

