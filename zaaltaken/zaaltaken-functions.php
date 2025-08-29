<?php 

require_once(dirname(__FILE__) . '/shortcode.php');

function create_zaaltaken_page() {
    // Check if page already exists
    $page_title = 'zaaltaken';
    $page_slug = 'zaaltaken';
    
    if (!get_page_by_path($page_slug)) {
        $page_id = wp_insert_post([
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_content'  => '', // Can be empty
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'meta_input'    => [
                '_wp_page_template' => 'zaaltaken-template.php'
            ]
        ]);
        
        // Store template file
        if ($page_id) {
            update_option('zaaltaken_page_id', $page_id);
        }
    }
}

function disable_zaaltaken_page() {
    // Optional: Remove the page when plugin is deactivated
    $page_id = get_option('zaaltaken_page_id');
    if ($page_id) {
        wp_delete_post($page_id, true);
        delete_option('zaaltaken_page_id');
    }
}

function load_zaaltaken_plugin_template($template) {
    global $post;
    
    $custom_template = plugin_dir_path(__FILE__) . 'zaaltaken-template.php';
    
    if ($post && $post->ID == get_option('zaaltaken_page_id') && file_exists($custom_template)) {
        return $custom_template;
    }
    
    return $template;
}

