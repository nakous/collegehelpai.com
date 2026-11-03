<?php
/**
 * Cobra Theme Functions
 *
 * @package Cobra
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// ===== DEVELOPMENT CACHE DISABLE =====
// Disable all caching during development
if (wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development') {
    // Disable object cache
    if (!defined('WP_CACHE')) {
        define('WP_CACHE', false);
    }
    
    // Disable page caching
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    
    // Disable script concatenation
    if (!defined('CONCATENATE_SCRIPTS')) {
        define('CONCATENATE_SCRIPTS', false);
    }
    
    // Force fresh CSS/JS by using time() instead of filemtime for cache busting
    add_filter('style_loader_src', 'cobra_remove_css_js_cache', 10, 2);
    add_filter('script_loader_src', 'cobra_remove_css_js_cache', 10, 2);
}

function cobra_remove_css_js_cache($src, $handle) {
    // Only apply to our theme files
    if (strpos($src, get_template_directory_uri()) !== false) {
        $src = remove_query_arg('ver', $src);
        $src = add_query_arg('ver', time(), $src);
    }
    return $src;
}

require_once get_template_directory() . '/inc/wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/inc/admin.php';
require_once get_template_directory() . '/inc/theme-settings.php';
// require_once get_template_directory() . '/inc/sitemap.php';

// ===== DIRECT LOGOUT FUNCTIONALITY =====
/**
 * Handle direct logout without confirmation page
 */
function cobra_handle_direct_logout() {
    if (isset($_GET['cobra_logout']) && $_GET['cobra_logout'] === '1') {
        // Verify nonce for security
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'cobra-logout')) {
            wp_logout();
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('init', 'cobra_handle_direct_logout');

/**
 * Create direct logout URL
 */
function cobra_get_direct_logout_url($redirect_to = '') {
    if (empty($redirect_to)) {
        $redirect_to = home_url();
    }
    
    $logout_url = add_query_arg(array(
        'cobra_logout' => '1',
        '_wpnonce' => wp_create_nonce('cobra-logout'),
        'redirect_to' => urlencode($redirect_to)
    ), home_url());
    
    return $logout_url;
}

function cobra_enqueue_scripts()
{
    // Bootstrap CSS is now preloaded in header.php for better Core Web Vitals
    // No need to enqueue here as it's handled by preload directives
    
    // Add main stylesheet with version for cache busting
    wp_enqueue_style(
        'cobra-style', 
        get_stylesheet_uri(),
        array(), // Remove bootstrap-css dependency since it's preloaded
        filemtime(get_template_directory() . '/style.css')
    );

    // Enqueue Core Web Vitals optimization CSS
    wp_enqueue_style(
        'cobra-core-web-vitals-css',
        get_template_directory_uri() . '/assets/css/core-web-vitals.css',
        array('cobra-style'),
        filemtime(get_template_directory() . '/assets/css/core-web-vitals.css')
    );

    // Enqueue typography CSS with reliable fonts
    wp_enqueue_style(
        'cobra-typography',
        get_template_directory_uri() . '/assets/css/typography.css',
        array('cobra-style'),
        filemtime(get_template_directory() . '/assets/css/typography.css')
    );

    // Dequeue any existing Font Awesome versions to avoid conflicts
    wp_dequeue_style('font-awesome');
    wp_deregister_style('font-awesome');
    
    // Add Font Awesome with correct integrity hash
    wp_enqueue_style(
        'cobra-font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );
    
    // Add integrity attribute via filter
    add_filter('style_loader_tag', function($html, $handle) {
        if ($handle === 'cobra-font-awesome') {
            $html = str_replace(
                'rel=\'stylesheet\'',
                'rel=\'stylesheet\' integrity=\'sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==\' crossorigin=\'anonymous\'',
                $html
            );
        }
        return $html;
    }, 10, 2);
    
    // Enqueue Bootstrap JS with integrity check
    wp_enqueue_script(
        'bootstrap-js', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', 
        array('jquery'), 
        '5.1.3', 
        true
    );
    
    // Enqueue custom script with version for cache busting
    wp_enqueue_script(
        'cobra-script', 
        get_template_directory_uri() . '/assets/js/script.js', 
        array('jquery', 'bootstrap-js'), 
        filemtime(get_template_directory() . '/assets/js/script.js'), 
        true
    );
    
    // Localize script for AJAX calls
    wp_localize_script('cobra-script', 'cobra_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cobra_nonce'),
        'home_url' => home_url('/')
    ));

    // Enqueue Core Web Vitals optimization script
    wp_enqueue_script(
        'cobra-core-web-vitals',
        get_template_directory_uri() . '/assets/js/core-web-vitals.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/core-web-vitals.js'),
        true
    );

    // Enqueue accessibility CSS and JS
    wp_enqueue_style(
        'cobra-accessibility-css',
        get_template_directory_uri() . '/assets/css/accessibility.css',
        array('cobra-style'),
        filemtime(get_template_directory() . '/assets/css/accessibility.css')
    );

    // Enqueue performance boost CSS for Core Web Vitals optimization
    wp_enqueue_style(
        'cobra-performance-boost',
        get_template_directory_uri() . '/assets/css/performance-boost.css',
        array('cobra-style'),
        filemtime(get_template_directory() . '/assets/css/performance-boost.css')
    );

    // Enqueue YouTube Facade JS for lazy loading iframes
    wp_enqueue_script(
        'cobra-youtube-facade',
        get_template_directory_uri() . '/assets/js/youtube-facade.js',
        array(),
        filemtime(get_template_directory() . '/assets/js/youtube-facade.js'),
        true // In footer
    );

    wp_enqueue_script(
        'cobra-accessibility-js',
        get_template_directory_uri() . '/assets/js/accessibility.js',
        array('jquery', 'cobra-script'),
        filemtime(get_template_directory() . '/assets/js/accessibility.js'),
        true
    );
    
    // Enqueue page-specific CSS and JS
    cobra_enqueue_page_specific_assets();
}

// Function to enqueue page-specific CSS and JS files
function cobra_enqueue_page_specific_assets() {
    $template = get_page_template_slug();
    $current_page = '';
    
    // Determine current page type
    if (is_archive() || is_category() || is_tag() || is_author() || is_date()) {
        $current_page = 'archive';
    } elseif (is_single()) {
        // Check if it's a special single template
        $post_type = get_post_type();
        $template_name = get_page_template_slug();
        
        if ($post_type === 'stripe_plan' || basename(get_page_template()) === 'single-stripe_plan.php') {
            $current_page = 'single-stripe_plan';
        } else {
            $current_page = 'single';
        }
    } elseif (is_page()) {
        $current_page = 'page';
    } elseif (is_search()) {
        $current_page = 'search';
    } elseif (is_404()) {
        $current_page = '404';
    } elseif (is_front_page()) {
        $current_page = 'front-page';
    }
    
    // Enqueue page-specific CSS if file exists
    if ($current_page) {
        $css_path = get_template_directory() . '/assets/css/' . $current_page . '.css';
        $js_path = get_template_directory() . '/assets/js/' . $current_page . '.js';
        
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'cobra-' . $current_page . '-css',
                get_template_directory_uri() . '/assets/css/' . $current_page . '.css',
                array('cobra-style'),
                filemtime($css_path)
            );
        }
        
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'cobra-' . $current_page . '-js',
                get_template_directory_uri() . '/assets/js/' . $current_page . '.js',
                array('cobra-script'),
                filemtime($js_path),
                true
            );
        }
    }
}

add_action('wp_enqueue_scripts', 'cobra_enqueue_scripts', 10);

// Clean up WordPress default enqueues that might conflict
function cobra_cleanup_wp_defaults() {
    // Remove WordPress default Font Awesome if loaded by plugins
    wp_dequeue_style('fontawesome');
    wp_dequeue_style('font-awesome');
    wp_dequeue_style('fa');
    
    // Remove WordPress emoji scripts for better performance
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('init', 'cobra_cleanup_wp_defaults');

function cobra_theme_setup()
{
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'cobra'),
        'footer-menu' => __('Footer Menu', 'cobra')
    ));

    // Add theme supports for better SEO and functionality
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => array('site-title', 'site-description'),
    ));
    add_theme_support('html5', array(
        'search-form', 
        'comment-form', 
        'comment-list', 
        'gallery', 
        'caption', 
        'style', 
        'script'
    ));

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');
    
    // Add support for custom line height
    add_theme_support('custom-line-height');
    
    // Add support for custom spacing
    add_theme_support('custom-spacing');
    
    // Add support for block styles
    add_theme_support('wp-block-styles');
    
    // Add support for wide alignment
    add_theme_support('align-wide');
    
    // Add editor styles
    add_theme_support('editor-styles');
    
    // Set image sizes for better performance
    add_image_size('cobra-featured', 800, 450, true);
    add_image_size('cobra-thumbnail', 300, 200, true);
    add_image_size('cobra-medium', 600, 400, true);
    
    // Set content width for better responsive images
    if (!isset($content_width)) {
        $content_width = 1200;
    }
}
add_action('after_setup_theme', 'cobra_theme_setup');


// Theme activation hook
function cobra_theme_activation()
{
    // Get WordPress default values
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');

    // Get default logo (you might want to set a path to a default logo image in your theme)
    $default_logo = get_theme_file_uri('assets/images/default-logo.png');

    // Only set these values if they don't already exist
    if (!get_option('cobra_name')) {
        update_option('cobra_name', $site_name);
    }

    if (!get_option('cobra_contact_email')) {
        update_option('cobra_contact_email', $admin_email);
    }

    if (!get_option('cobra_logo')) {
        update_option('cobra_logo', $default_logo);
    }

    // Set default description if it doesn't exist
    if (!get_option('cobra_description')) {
        update_option('cobra_description', get_bloginfo('description'));
    }

    if (!get_option('cobra_copy_right')) {
        update_option('cobra_copy_right', 'Cobra, All rights reserved ' . date('Y'));
    }
}

// Register the activation hook
add_action('after_switch_theme', 'cobra_theme_activation');

// Optional: Add a function to get theme settings with defaults
function get_cobra_setting($setting_name)
{
    $defaults = array(
        'cobra_name' => get_bloginfo('name'),
        'cobra_logo' => get_theme_file_uri('assets/images/default-logo.png'),
        'cobra_contact_email' => get_option('admin_email'),
        'cobra_description' => get_bloginfo('description'),
        'cobra_copy_right' => get_bloginfo('copy_right')
    );

    $value = get_option($setting_name);
    return $value !== false ? $value : (isset($defaults[$setting_name]) ? $defaults[$setting_name] : '');
}
// Custom login URL
function cobra_custom_login_url($login_url)
{
    $login_page = get_option('cobra_login_page');
    if (!empty($login_page)) {
        return get_permalink($login_page);
    }
    return $login_url;
}
add_filter('login_url', 'cobra_custom_login_url');

// Custom registration URL
function cobra_custom_register_url($register_url)
{
    $register_page = get_option('cobra_register_page');
    if (!empty($register_page)) {
        return get_permalink($register_page);
    }
    return $register_url;
}
add_filter('register_url', 'cobra_custom_register_url');

// Redirect default WordPress login/register pages to custom ones
function cobra_redirect_login_register()
{
    $login_page = get_option('cobra_login_page');
    $register_page = get_option('cobra_register_page');

    // Get current page
    $page = basename($_SERVER['REQUEST_URI']);

    if ($page === 'wp-login.php' && !isset($_GET['action']) && !empty($login_page)) {
        wp_redirect(get_permalink($login_page));
        exit();
    }

    if ($page === 'wp-login.php' && isset($_GET['action']) && $_GET['action'] === 'register' && !empty($register_page)) {
        wp_redirect(get_permalink($register_page));
        exit();
    }
}
add_action('init', 'cobra_redirect_login_register');



// costum function
function _cobra_custom_footer_download()
{

    echo '
    <div class="download-section">';

    $download_options = array(
        'chrome_extension' => array(
            'option' => get_option('cobra_chrome_extension'),
            'icon' => '<i class="fab fa-chrome"></i>',
            'text' => 'Add to Chrome',
            'subtext' => 'Free Extension'
        ),
        'android_app' => array(
            'option' => get_option('cobra_android_app'),
            'icon' => '<i class="fab fa-android"></i>',
            'text' => 'Play Store',
            'subtext' => 'Android App'
        ),
        'ios_app' => array(
            'option' => get_option('cobra_ios_app'),
            'icon' => '<i class="fab fa-app-store-ios"></i>',
            'text' => 'App Store',
            'subtext' => 'iOS App'
        )
    );

    foreach ($download_options as $key => $option) {
        if ($option['option']) {
            echo sprintf(
                '<a href="%s" class="download-button %s" target="_blank">
                    <div class="button-icon">%s</div>
                    <div class="button-text">
                        <span class="button-subtext">%s</span>
                        <span class="button-main">%s</span>
                    </div>
                </a>',
                esc_url($option['option']),
                esc_attr($key),
                $option['icon'],
                esc_html($option['subtext']),
                esc_html($option['text'])
            );
        }
    }

    echo '</div>';
}

// ===== ANALYTICS (MATOMO) =====

// Matomo tracking code (replaces Google Analytics)
function cobra_add_matomo_tracking() {
    if (is_customize_preview()) {
        return;
    }
    ?>
<!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="https://stats.onlevelup.com/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '7']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
    <?php
}
add_action('wp_head', 'cobra_add_matomo_tracking');

// ===== SEO ENHANCEMENTS =====

// Add Schema.org structured data
function cobra_add_schema_markup() {
    if (is_home() || is_front_page()) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_cobra_setting('cobra_name'),
            'description' => get_cobra_setting('cobra_description'),
            'url' => home_url('/'),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string'
            )
        );
        
        if (get_cobra_setting('cobra_logo')) {
            $schema['logo'] = get_cobra_setting('cobra_logo');
        }
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'cobra_add_schema_markup');

// Add breadcrumb schema
function cobra_breadcrumb_schema() {
    if (!is_home() && !is_front_page()) {
        $breadcrumbs = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        $breadcrumbs['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url('/')
        );
        
        if (is_single() || is_page()) {
            $breadcrumbs['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title(),
                'item' => get_permalink()
            );
        }
        
        echo '<script type="application/ld+json">' . json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'cobra_breadcrumb_schema');

// // Optimize meta descriptions
// function cobra_custom_meta_description() {
//     if (is_home() || is_front_page()) {
//         echo '<meta name="description" content="' . esc_attr(get_cobra_setting('cobra_description')) . '">';
//     } elseif (is_single() || is_page()) {
//         $excerpt = get_the_excerpt();
//         if ($excerpt) {
//             echo '<meta name="description" content="' . esc_attr(wp_trim_words($excerpt, 25)) . '">';
//         }
//     }
// }
// add_action('wp_head', 'cobra_custom_meta_description', 1);

// // Add canonical URLs
// function cobra_add_canonical() {
//     if (is_home() || is_front_page()) {
//         echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '">';
//     } elseif (is_single() || is_page()) {
//         echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '">';
//     }
// }
// add_action('wp_head', 'cobra_add_canonical');

// ===== PERFORMANCE ENHANCEMENTS =====

// Optimize jQuery loading
function cobra_optimize_jquery() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0', false);
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'cobra_optimize_jquery', 5);

// Remove unnecessary WordPress features for performance
function cobra_clean_wp_head() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
}
add_action('after_setup_theme', 'cobra_clean_wp_head');

// Add preload for critical resources
function cobra_add_preload() {
    // echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/style-default.css" as="style">';
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/Inter-VariableFont.ttf" as="font" type="font/ttf" crossorigin>';
}
add_action('wp_head', 'cobra_add_preload', 1);

// ===== ACCESSIBILITY ENHANCEMENTS =====

// Add skip to content link
// function cobra_skip_link() {
//     echo '<a class="screen-reader-text skip-link" href="#main-content">' . __('Skip to content', 'cobra') . '</a>';
// }
// add_action('wp_body_open', 'cobra_skip_link');

// Improve form accessibility
function cobra_improve_form_accessibility($output, $args) {
    // Add proper labels and ARIA attributes to forms
    $output = str_replace('<input type="search"', '<input type="search" aria-label="Search"', $output);
    return $output;
}
add_filter('get_search_form', 'cobra_improve_form_accessibility', 10, 2);

// ===== WEB CORE VITALS OPTIMIZATIONS =====

// Add lazy loading to all images except above-the-fold
function cobra_add_lazy_loading($html, $id, $size, $permalink) {
    // Skip lazy loading for images that are likely above the fold
    $skip_lazy = false;
    
    // Check if we're on the front page and this might be a hero image
    if (is_front_page()) {
        // Skip lazy loading for the first few images on front page
        static $image_count = 0;
        $image_count++;
        if ($image_count <= 2) {
            $skip_lazy = true;
        }
    }
    
    // Add lazy loading if not skipped
    if (!$skip_lazy && strpos($html, 'loading=') === false) {
        $html = str_replace('<img ', '<img loading="lazy" ', $html);
    }
    
    return $html;
}
add_filter('wp_get_attachment_image', 'cobra_add_lazy_loading', 10, 4);

// Add lazy loading to content images
function cobra_lazy_load_content_images($content) {
    // Don't apply to admin area
    if (is_admin()) {
        return $content;
    }
    
    // Replace img tags with lazy loading
    $content = preg_replace_callback(
        '/<img([^>]+?)>/i',
        function($matches) {
            $img_tag = $matches[0];
            
            // Skip if already has loading attribute
            if (strpos($img_tag, 'loading=') !== false) {
                return $img_tag;
            }
            
            // Add lazy loading
            return str_replace('<img ', '<img loading="lazy" ', $img_tag);
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'cobra_lazy_load_content_images');

// Optimize image formats and add WebP support
function cobra_add_webp_support($mimes) {
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';
    return $mimes;
}
add_filter('upload_mimes', 'cobra_add_webp_support');

// Add critical CSS inline for above-the-fold content
function cobra_inline_critical_css() {
    if (is_front_page()) {
        echo '<style id="critical-css">
        /* Critical CSS for above-the-fold content */
        .hero-section { background: linear-gradient(135deg, #69b8db 0%, #4c5e87 100%); min-height: 100vh; display: flex; align-items: center; }
        .hero-content { color: white; text-align: center; }
        .hero-title { font-size: 3.5rem; font-weight: 700; margin-bottom: 1.5rem; }
        .hero-subtitle { font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9; }
        .install-btn { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        </style>';
    }
}
add_action('wp_head', 'cobra_inline_critical_css', 1);

// Preload next page for better navigation experience
function cobra_preload_next_page() {
    if (is_single()) {
        $next_post = get_next_post();
        if ($next_post) {
            echo '<link rel="prefetch" href="' . get_permalink($next_post->ID) . '">';
        }
    }
}
add_action('wp_head', 'cobra_preload_next_page');

// Add resource hints for better performance
function cobra_add_resource_hints($urls, $relation_type) {
    switch ($relation_type) {
        case 'dns-prefetch':
            $urls[] = '//fonts.googleapis.com';
            $urls[] = '//cdnjs.cloudflare.com';
            $urls[] = '//stats.onlevelup.com';
            // $urls[] = '//www.clarity.ms';
            break;
        case 'preconnect':
            $urls[] = 'https://fonts.googleapis.com';
            $urls[] = 'https://cdnjs.cloudflare.com';
            break;
    }
    return $urls;
}
add_filter('wp_resource_hints', 'cobra_add_resource_hints', 10, 2);

// Add performance headers via WordPress
function cobra_add_performance_headers() {
    // Skip if headers already sent or if admin/AJAX/cron request
    if (headers_sent() || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    
    // Skip for REST API and XML-RPC requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        return;
    }
    
    // Only add headers for actual page requests
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    
    // Development vs Production headers
    if (wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development') {
        // Development: Disable all caching
        @header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        @header('Pragma: no-cache');
        @header('Expires: 0');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    } else {
        // Production: Enable caching for static assets
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|avif|woff|woff2)$/', $_SERVER['REQUEST_URI'])) {
            @header('Cache-Control: public, max-age=31536000');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        } else {
            // HTML pages: No cache for dynamic content (user menu, etc.)
            @header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
            @header('Pragma: no-cache');
            @header('Expires: 0');
        }
    }
    
    // Service Worker headers
    if (strpos($_SERVER['REQUEST_URI'], 'sw.js') !== false) {
        @header('Content-Type: application/javascript');
        @header('Service-Worker-Allowed: /');
    }
    
    // Security headers
    @header('X-Content-Type-Options: nosniff');
    @header('X-Frame-Options: SAMEORIGIN');
    @header('X-XSS-Protection: 1; mode=block');
}
add_action('init', 'cobra_add_performance_headers');

// ===== SECURITY ENHANCEMENTS =====

// Remove WordPress version from head
function cobra_remove_version() {
    return '';
}
add_filter('the_generator', 'cobra_remove_version');

// Add security headers
function cobra_add_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'cobra_add_security_headers');

// ===== ACCESSIBILITY ENHANCEMENTS =====

/**
 * Add accessibility improvements to images
 */
function cobra_enhance_image_accessibility($html, $id, $caption, $title, $align, $url, $size, $alt) {
    // Ensure all images have alt text
    if (empty($alt)) {
        $post = get_post($id);
        if ($post) {
            // Use post title as fallback alt text
            $alt = $post->post_title;
            $html = str_replace('<img ', '<img alt="' . esc_attr($alt) . '" ', $html);
        } else {
            // Add empty alt for decorative images
            $html = str_replace('<img ', '<img alt="" ', $html);
        }
    }
    
    // Add loading="lazy" for better performance (except first image)
    if (!is_admin() && !cobra_is_first_image()) {
        $html = str_replace('<img ', '<img loading="lazy" ', $html);
    }
    
    return $html;
}
add_filter('get_image_tag', 'cobra_enhance_image_accessibility', 10, 8);

/**
 * Check if this is the first image on the page (for loading optimization)
 */
function cobra_is_first_image() {
    static $first_image = true;
    if ($first_image) {
        $first_image = false;
        return true;
    }
    return false;
}

/**
 * Add accessibility attributes to navigation menus
 */
function cobra_add_nav_accessibility($items, $args) {
    if ($args->theme_location === 'primary') {
        // Add current page indicator for screen readers
        $items = str_replace('current-menu-item', 'current-menu-item" aria-current="page', $items);
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'cobra_add_nav_accessibility', 10, 2);

/**
 * Improve form accessibility with JavaScript
 */
function cobra_enhance_form_accessibility_js() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Add aria-required to required fields
        $('input[required], textarea[required], select[required]').attr('aria-required', 'true');
        
        // Associate labels with form fields
        $('input, textarea, select').each(function() {
            var $field = $(this);
            var id = $field.attr('id');
            if (id) {
                var $label = $('label[for="' + id + '"]');
                if ($label.length === 0) {
                    // Look for nearby label
                    $label = $field.siblings('label').first();
                    if ($label.length) {
                        $label.attr('for', id);
                    }
                }
            }
        });
        
        // Add error announcements
        $('.error input, input.error').on('focus', function() {
            var errorText = $(this).siblings('.error-message').text() || 'This field has an error';
            $('<div id="error-announcement" aria-live="polite" class="screen-reader-text">' + errorText + '</div>').appendTo('body');
            setTimeout(function() {
                $('#error-announcement').remove();
            }, 3000);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'cobra_enhance_form_accessibility_js');

/**
 * Add language attributes to HTML elements
 */
function cobra_add_language_attributes($output) {
    // Add lang attribute to specific elements if needed
    return $output;
}

/**
 * Improve video accessibility
 */
function cobra_improve_video_accessibility($html, $url, $attr) {
    // Add title and aria-label to videos
    if (strpos($html, '<video') !== false) {
        // Add controls if not present
        if (strpos($html, 'controls') === false) {
            $html = str_replace('<video', '<video controls', $html);
        }
        
        // Add title if not present
        if (strpos($html, 'title=') === false) {
            $html = str_replace('<video', '<video title="Video content"', $html);
        }
    }
    
    return $html;
}
add_filter('wp_video_shortcode', 'cobra_improve_video_accessibility', 10, 3);

/**
 * Add accessibility settings to admin
 */
function cobra_add_accessibility_settings() {
    add_settings_section(
        'cobra_accessibility',
        __('Accessibility Settings', 'cobra'),
        'cobra_accessibility_section_callback',
        'cobra_accessibility_settings'
    );
    
    add_settings_field(
        'cobra_high_contrast',
        __('High Contrast Mode', 'cobra'),
        'cobra_high_contrast_callback',
        'cobra_accessibility_settings',
        'cobra_accessibility'
    );
    
    register_setting('cobra_accessibility_settings', 'cobra_high_contrast');
}
add_action('admin_init', 'cobra_add_accessibility_settings');

function cobra_accessibility_section_callback() {
    echo '<p>' . __('Configure accessibility features for your theme.', 'cobra') . '</p>';
}

function cobra_high_contrast_callback() {
    $value = get_option('cobra_high_contrast', 0);
    echo '<input type="checkbox" name="cobra_high_contrast" value="1" ' . checked(1, $value, false) . ' />';
    echo '<label for="cobra_high_contrast">' . __('Enable high contrast mode', 'cobra') . '</label>';
}

/**
 * Add accessibility CSS classes based on settings
 */
function cobra_add_accessibility_body_classes($classes) {
    if (get_option('cobra_high_contrast')) {
        $classes[] = 'high-contrast';
    }
    
    // Add reduced motion class if user preference detected
    $classes[] = 'supports-reduced-motion';
    
    return $classes;
}
add_filter('body_class', 'cobra_add_accessibility_body_classes');

/**
 * Add keyboard navigation indicators
 */
function cobra_add_focus_indicators() {
    ?>
    <style>
    /* Keyboard navigation indicators */
    .keyboard-navigation :focus {
        outline: 2px solid #0073aa !important;
        outline-offset: 2px !important;
    }
    
    /* Hide outline for mouse users */
    .mouse-navigation :focus {
        outline: none !important;
    }
    </style>
    
    <script>
    // Detect input method and add appropriate class
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
            document.body.classList.remove('mouse-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.add('mouse-navigation');
        document.body.classList.remove('keyboard-navigation');
    });
    </script>
    <?php
}
add_action('wp_head', 'cobra_add_focus_indicators');

/**
 * Add ARIA landmarks automatically
 */
function cobra_add_aria_landmarks($content) {
    // This would be expanded to add ARIA landmarks to content
    return $content;
}

/**
 * Accessibility helper function to check color contrast
 */
function cobra_check_color_contrast($foreground, $background) {
    // Convert hex to RGB
    $fg_rgb = cobra_hex_to_rgb($foreground);
    $bg_rgb = cobra_hex_to_rgb($background);
    
    // Calculate relative luminance
    $fg_luminance = cobra_calculate_luminance($fg_rgb);
    $bg_luminance = cobra_calculate_luminance($bg_rgb);
    
    // Calculate contrast ratio
    $contrast_ratio = ($fg_luminance + 0.05) / ($bg_luminance + 0.05);
    if ($contrast_ratio < 1) {
        $contrast_ratio = 1 / $contrast_ratio;
    }
    
    return $contrast_ratio;
}

function cobra_hex_to_rgb($hex) {
    $hex = ltrim($hex, '#');
    return array(
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    );
}

function cobra_calculate_luminance($rgb) {
    $r = $rgb['r'] / 255;
    $g = $rgb['g'] / 255;
    $b = $rgb['b'] / 255;
    
    $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
    $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
    $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
    
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

/**
 * Add accessibility notices for admin users
 */
function cobra_add_accessibility_notices() {
    if (current_user_can('manage_options')) {
        // Check for common accessibility issues
        $issues = array();
        
        // Check if images have alt text
        $images_without_alt = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'meta_query' => array(
                array(
                    'key' => '_wp_attachment_image_alt',
                    'value' => '',
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));
        
        if (!empty($images_without_alt)) {
            $issues[] = __('Some images are missing alt text.', 'cobra');
        }
        
        // Display notices
        foreach ($issues as $issue) {
            echo '<div class="notice notice-warning"><p><strong>Accessibility:</strong> ' . esc_html($issue) . '</p></div>';
        }
    }
}
add_action('admin_notices', 'cobra_add_accessibility_notices');

/**
 * Prevent duplication of SEO tags when Yoast SEO is active
 * 
 * @since 1.0.0
 */
function cobra_prevent_seo_duplication() {
    // If Yoast SEO is active, remove WordPress default canonical tags
    if (function_exists('wpseo_auto_load')) {
        // Remove WordPress default canonical tag
        remove_action('wp_head', 'rel_canonical');
        
        // Remove WordPress default meta description (handled by theme header with condition)
        remove_action('wp_head', 'wp_generator');
        
        // Remove adjacent posts rel links (Yoast handles this better)
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    }
}
add_action('init', 'cobra_prevent_seo_duplication');

/**
 * Remove duplicate Open Graph tags if Yoast SEO is active
 * Yoast SEO priority is 1, so we run after at priority 2
 * 
 * @since 1.0.0
 */
function cobra_remove_duplicate_og_tags() {
    if (function_exists('wpseo_auto_load')) {
        // Yoast handles all OG tags, so we don't need to add any
        // The theme's OG tags are already wrapped in a condition in header.php
        return;
    }
}
add_action('wp_head', 'cobra_remove_duplicate_og_tags', 2);
