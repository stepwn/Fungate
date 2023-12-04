<?php
function fungate_nft_custom_post_type() {
    register_post_type('fungate_nft', array(
        'labels' => array(
            'name' => 'Fungate NFTs',
            'singular_name' => 'Fungate NFT',
        ),
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-images-alt2', // Set a custom menu icon
        'rewrite' => array('slug' => 'fungate-nfts'), // Custom permalink structure
        'taxonomies' => array('category'), // You can add more taxonomies here
        'has_archive' => true, // Enable archive page
        'show_in_menu' => false,
    ));
}
add_action('init', 'fungate_nft_custom_post_type');

function fungate_custom_post_template($template) {
    if (is_singular('fungate_nft')) {
        ob_start();
        include(plugin_dir_path(__FILE__) . 'single-fungate_nft.php');
        $content = ob_get_clean();
        wp_head();
        echo $content;
        wp_footer();
        return '';
    }
    return $template;
}
add_filter('template_include', 'fungate_custom_post_template');

// Add custom meta box to select layout style
function fungate_add_layout_style_meta_box() {
    add_meta_box(
        'fungate_layout_style_meta_box',
        'Layout Style',
        'fungate_render_layout_style_meta_box',
        'fungate_nft', // You can change this to other post types if needed
        'normal', // Position of the meta box
        'default'
    );
}
add_action('add_meta_boxes', 'fungate_add_layout_style_meta_box');

// Render the content of the custom meta box
function fungate_render_layout_style_meta_box($post) {
    $layout_style = get_post_meta($post->ID, 'fungate_layout_style', true);

    // Define layout options
    $layout_options = array(
        'two-column' => 'Two-Column',
        'stacked' => 'Stacked',
    );

    // Output the radio buttons
    foreach ($layout_options as $value => $label) {
        echo '<label>';
        echo '<input type="radio" name="fungate_layout_style" value="' . esc_attr($value) . '" ' . checked($layout_style, $value, false) . '>';
        echo ' ' . esc_html($label); // Add a space before the label
        echo '</label><br>';
    }
}

// Save the selected layout style when the post is saved or updated
function fungate_save_layout_style_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['fungate_layout_style'])) {
        $layout_style = sanitize_key($_POST['fungate_layout_style']);
        update_post_meta($post_id, 'fungate_layout_style', $layout_style);
    }
}
add_action('save_post', 'fungate_save_layout_style_meta_box');

// Add custom meta fields to Fungate NFTs
function fungate_nft_meta_fields() {
    add_meta_box(
        'fungate_nft_meta',
        'Fungate NFT Details',
        'fungate_nft_meta_callback',
        'fungate_nft',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'fungate_nft_meta_fields');

// Callback function for rendering meta box content
function fungate_nft_meta_callback($post) {
    // Add nonce for security and authentication
    wp_nonce_field('fungate_nft_nonce', 'fungate_nft_nonce');

    // Get existing values from the database
    $nft_id = get_post_meta($post->ID, 'fungate_nft_id', true);
    $minter = get_post_meta($post->ID, 'fungate_minter', true);
    $token = get_post_meta($post->ID, 'fungate_token', true);
    $contract = get_post_meta($post->ID, 'fungate_contract', true);
    $desc = get_post_meta($post->ID, 'fungate_short_description', true);

    // Render the fields
    echo '<label for="fungate_nft_id">Fungate NFT ID:</label>';
    echo '<input type="text" id="fungate_nft_id" name="fungate_nft_id" value="' . esc_attr($nft_id) . '"><br>';

    echo '<label for="fungate_minter">Fungate Minter:</label>';
    echo '<input type="text" id="fungate_minter" name="fungate_minter" value="' . esc_attr($minter) . '"><br>';

    echo '<label for="fungate_contract">Fungate Contract:</label>';
    echo '<input type="text" id="fungate_contract" name="fungate_contract" value="' . esc_attr($token) . '"><br>';

    echo '<label for="fungate_short_description">Fungate Short Description:</label>';
    echo '<input type="text" id="fungate_short_description" name="fungate_short_description" value="' . esc_attr($desc) . '"><br>';
}

// Save custom meta fields
function save_fungate_nft_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['fungate_nft_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['fungate_nft_nonce'], 'fungate_nft_nonce')) {
        return;
    }

    // Check if the current user has permission to save
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and save the data
    if (isset($_POST['fungate_nft_id'])) {
        update_post_meta($post_id, 'fungate_nft_id', sanitize_text_field($_POST['fungate_nft_id']));
    }
    if (isset($_POST['fungate_minter'])) {
        update_post_meta($post_id, 'fungate_minter', sanitize_text_field($_POST['fungate_minter']));
    }
    if (isset($_POST['fungate_token'])) {
        update_post_meta($post_id, 'fungate_token', sanitize_text_field($_POST['fungate_token']));
    }
    if (isset($_POST['fungate_contract'])) {
        update_post_meta($post_id, 'fungate_contract', sanitize_text_field($_POST['fungate_contract']));
    }
    if (isset($_POST['fungate_short_description'])) {
        update_post_meta($post_id, 'fungate_short_description', sanitize_text_field($_POST['fungate_short_description']));
    }
}
add_action('save_post_fungate_nft', 'save_fungate_nft_meta');

function add_fungate_shortcode_to_posts( $content ) {
    // Get the current post ID
    $post_id = get_the_ID();
    
    // Check if the post has fungate_nft_id, fungate_minter, and fungate_token metadata
    $nft_id_metadata = get_post_meta( $post_id, 'fungate_nft_id', true );
    $minter_metadata = get_post_meta( $post_id, 'fungate_minter', true );
    $token_metadata = get_post_meta( $post_id, 'fungate_token', true );
    $contract_metadata = get_post_meta( $post_id, 'fungate_contract', true );
    // Check if the post content already contains [fungate] shortcode
    $has_fungate_shortcode = strpos( $content, '[fungate ' ) !== false;
    
    if ( $nft_id_metadata || $minter_metadata || $token_metadata || $contract_metadata ) {
        if(!$has_fungate_shortcode){
        // Add [fungate] shortcode with attributes to the post content
        $shortcode_attributes = 'nft_id="' . $nft_id_metadata . '" minter="' . $minter_metadata . '" token="' . $token_metadata . '" contract="' . $contract_metadata . '"';
        $content = '[fungate ' . $shortcode_attributes . ']' . $content . '[/fungate]';
        }
    }
    return $content;
}
add_filter( 'the_content', 'add_fungate_shortcode_to_posts' );