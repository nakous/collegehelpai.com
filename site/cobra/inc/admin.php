<?php
// Add this to your functions.php
function cobra_admin_scripts($hook)
{
    if ('toplevel_page_cobra-settings' !== $hook) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('cobra-admin', get_template_directory_uri() . '/assets/js/admin.js', array('jquery', 'wp-color-picker'), '1.0.0', true);

    wp_localize_script('cobra-admin', 'cobra_settings', array(
        'nonce' => wp_create_nonce('cobra_settings_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'cobra_admin_scripts');

// function cobra_enqueue_pattern_styles() {
//     $patterns_css_path = get_template_directory_uri() . '/assets/css/patterns.css';
//     $version = filemtime(get_template_directory() . '/assets/css/patterns.css');
    
//     // Enqueue for frontend
//     wp_enqueue_style(
//         'cobra-patterns',
//         $patterns_css_path,
//         array(),
//         $version
//     );

//     // Enqueue for editor
//     // add_editor_style($patterns_css_path);
// }
// add_action('wp_enqueue_scripts', 'cobra_enqueue_pattern_styles');
// function cobra_enqueue_editor_styles() {
//     add_editor_style('assets/css/patterns.css');
// }
// add_action('after_setup_theme', 'cobra_enqueue_editor_styles');

// Additional editor styles for blocks
// function cobra_block_editor_styles() {
//     wp_enqueue_style(
//         'cobra-editor-patterns',
//         get_template_directory_uri() . '/assets/css/patterns.css',
//         array(),
//         filemtime(get_template_directory() . '/assets/css/patterns.css')
//     );
// }
// add_action('enqueue_block_editor_assets', 'cobra_block_editor_styles');