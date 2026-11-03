<?php
/**
 * Simple XML Sitemap Generation
 * 
 * @package Cobra
 * @since 2.0
 * 
 * FULLY DISABLED - Yoast SEO manages all sitemap and robots.txt functionality
 */

// SITEMAP GENERATION - DISABLED
// All sitemap functionality is now handled by Yoast SEO plugin
// This theme no longer generates custom sitemaps

/*
function cobra_generate_sitemap() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Add homepage
    $sitemap .= '<url>' . "\n";
    $sitemap .= '<loc>' . home_url('/') . '</loc>' . "\n";
    $sitemap .= '<changefreq>daily</changefreq>' . "\n";
    $sitemap .= '<priority>1.0</priority>' . "\n";
    $sitemap .= '</url>' . "\n";
    
    // Add pages
    $pages = get_pages();
    foreach ($pages as $page) {
        $sitemap .= '<url>' . "\n";
        $sitemap .= '<loc>' . get_permalink($page->ID) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($page->post_modified)) . '</lastmod>' . "\n";
        $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '<priority>0.8</priority>' . "\n";
        $sitemap .= '</url>' . "\n";
    }
    
    // Add posts
    $posts = get_posts(array('numberposts' => -1, 'post_status' => 'publish'));
    foreach ($posts as $post) {
        $sitemap .= '<url>' . "\n";
        $sitemap .= '<loc>' . get_permalink($post->ID) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($post->post_modified)) . '</lastmod>' . "\n";
        $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
        $sitemap .= '<priority>0.6</priority>' . "\n";
        $sitemap .= '</url>' . "\n";
    }
    
    $sitemap .= '</urlset>';
    
    file_put_contents(ABSPATH . 'sitemap.xml', $sitemap);
}

add_action('save_post', 'cobra_generate_sitemap');
add_action('deleted_post', 'cobra_generate_sitemap');
*/

// ROBOTS.TXT ENHANCEMENT - ACTIVE
// Override Yoast SEO robots.txt with custom theme rules
// Priority 999 ensures theme rules override Yoast (Yoast uses priority 1)
function cobra_robots_txt($output) {
    // Replace entire output with custom rules (override Yoast completely)
    $custom_rules = "User-agent: *\n";
    $custom_rules .= "Disallow: /wp-admin/\n";
    $custom_rules .= "Disallow: /wp-includes/\n";
    $custom_rules .= "Disallow: /wp-content/plugins/\n";
    $custom_rules .= "Disallow: /wp-content/themes/\n";
    $custom_rules .= "Allow: /wp-admin/admin-ajax.php\n";
    $custom_rules .= "Allow: /wp-content/uploads/\n";
    $custom_rules .= "\n";
    $custom_rules .= "# Sitemap from Yoast SEO\n";
    $custom_rules .= "Sitemap: " . home_url('/sitemap.xml') . "\n";
    
    // Return custom rules (ignore Yoast output)
    return $custom_rules;
}
// Priority 999 to override Yoast SEO (Yoast uses priority 1)
add_filter('robots_txt', 'cobra_robots_txt', 99999);
?>