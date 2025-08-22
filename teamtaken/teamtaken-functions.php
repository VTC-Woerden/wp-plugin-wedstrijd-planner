<?php 

function create_teamtaken_page() {
    // Check if page already exists
    $page_title = 'teamtaken';
    $page_slug = 'teamtaken';
    
    if (!get_page_by_path($page_slug)) {
        $page_id = wp_insert_post([
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_content'  => '', // Can be empty
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'meta_input'    => [
                '_wp_page_template' => 'teamtaken-template.php'
            ]
        ]);
        
        // Store template file
        if ($page_id) {
            update_option('teamtaken_page_id', $page_id);
        }
    }
}

function disable_teamtaken_page() {
    // Optional: Remove the page when plugin is deactivated
    $page_id = get_option('teamtaken_page_id');
    if ($page_id) {
        wp_delete_post($page_id, true);
        delete_option('teamtaken_page_id');
    }
}

function load_teamtaken_plugin_template($template) {
    global $post;
    
    $custom_template = plugin_dir_path(__FILE__) . 'teamtaken-template.php';
    
    if ($post && $post->ID == get_option('teamtaken_page_id') && file_exists($custom_template)) {
        return $custom_template;
    }
    
    return $template;
}
