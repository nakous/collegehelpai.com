<?php

/**
 * Cobra Theme Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cobra_Theme_Settings
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_cobra-settings' !== $hook) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('cobra-admin', get_template_directory_uri() . '/assets/css/admin.css');
        wp_enqueue_script('cobra-admin', get_template_directory_uri() . '/assets/js/admin.js', array('jquery'), '1.0', true);
    }

    public function add_menu_item()
    {
        add_menu_page(
            'Cobra Theme Settings',
            'Cobra Theme',
            'manage_options',
            'cobra-settings',
            array($this, 'settings_page'),
            'dashicons-admin-generic',
            60
        );
    }


    public function settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current tab
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>
        <div class="wrap">
            <h1>Cobra Theme Settings</h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=cobra-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    General
                </a>
                <a href="?page=cobra-settings&tab=social" class="nav-tab <?php echo $active_tab == 'social' ? 'nav-tab-active' : ''; ?>">
                    Social
                </a>
                <a href="?page=cobra-settings&tab=style" class="nav-tab <?php echo $active_tab == 'style' ? 'nav-tab-active' : ''; ?>">
                    Style
                </a>
                <a href="?page=cobra-settings&tab=cache" class="nav-tab <?php echo $active_tab == 'cache' ? 'nav-tab-active' : ''; ?>">
                    Cache
                </a>
            </h2>

            <form method="post" action="options.php">
                <?php
                // Set the correct option group based on active tab
                switch ($active_tab) {
                    case 'social':
                        settings_fields('cobra_social_settings');
                        $this->social_settings_section();
                        break;
                    case 'style':
                        settings_fields('cobra_style_settings');
                        $this->style_settings_section();
                        break;
                    case 'cache':
                        $this->handle_cache_actions();
                        $this->cache_settings_section();
                        break;
                    default: // 'general'
                        settings_fields('cobra_general_settings');
                        $this->general_settings_section();
                        break;
                }
                
                if ($active_tab !== 'cache') {
                    submit_button();
                }
                ?>
            </form>

            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved successfully!', 'cobra'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php
    }

    public function register_settings()
    {
        // General Settings
        register_setting('cobra_general_settings', 'cobra_logo', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => get_theme_file_uri('assets/images/default-logo.png'),
        ));
        register_setting('cobra_general_settings', 'cobra_name', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => get_bloginfo('name'),
        ));
        register_setting('cobra_general_settings', 'cobra_description', array(
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => get_bloginfo('description'),
        ));

        register_setting('cobra_general_settings', 'cobra_contact_email', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => get_option('admin_email'),
        ));
        // field copy_right
        register_setting('cobra_general_settings', 'cobra_copy_right', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Cobra Theme. All rights reserved ' . date('Y'),
        ));
        register_setting('cobra_general_settings', 'cobra_login_page' , array(
            'type' => 'string',
            'sanitize_callback' => 'absint',
            'default' => 0
        ));
        register_setting('cobra_general_settings', 'cobra_register_page' , array(
            'type' => 'string',
            'sanitize_callback' => 'absint',
            'default' => 0
        ));
        register_setting('cobra_general_settings', 'cobra_account_page' , array(
            'type' => 'string',
            'sanitize_callback' => 'absint',
            'default' => 0
        ));
        // Social Settings
        register_setting('cobra_social_settings', 'cobra_social_links', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_social_links'),
            'default' => array()
        ));
        register_setting('cobra_social_settings', 'cobra_video_promo', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));
        register_setting('cobra_social_settings', 'cobra_chrome_extension', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));
        register_setting('cobra_social_settings', 'cobra_edge_extension', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));
        register_setting('cobra_social_settings', 'cobra_android_app', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));
        register_setting('cobra_social_settings', 'cobra_ios_app', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));

        // Style Settings
        register_setting('cobra_style_settings', 'cobra_theme_style', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        register_setting('cobra_style_settings', 'cobra_custom_css', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_custom_css'),
            'default' => ''
        ));

        
    }

    public function sanitize_social_links($input)
    {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();
        foreach ($input as $link) {
            if (empty($link['platform']) || empty($link['url'])) {
                continue;
            }

            $sanitized[] = array(
                'platform' => sanitize_text_field($link['platform']),
                'url' => esc_url_raw($link['url'])
            );
        }

        return $sanitized;
    }

    public function sanitize_custom_css($input)
    {
        return wp_strip_all_tags($input);
    }

    private function general_settings_section()
    {
    ?>
        <table class="form-table">
            <tr>
                <th scope="row">Logo</th>
                <td>
                    <?php $logo = get_option('cobra_logo'); ?>
                    <div class="logo-preview">
                        <?php if ($logo): ?>
                            <img src="<?php echo esc_url($logo); ?>" id="logo-preview" alt="Site Logo">
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="cobra_logo" id="cobra_logo" value="<?php echo esc_attr($logo); ?>">
                    <button type="button" class="button upload-logo-button">Upload Logo</button>
                    <?php if ($logo): ?>
                        <button type="button" class="button remove-logo">Remove Logo</button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Name</th>
                <td>
                    <input type="text" name="cobra_name" value="<?php echo esc_attr(get_option('cobra_name')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Description</th>
                <td>
                    <textarea name="cobra_description" rows="3" class="large-text"><?php echo esc_textarea(get_option('cobra_description')); ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row">Contact Email</th>
                <td>
                    <input type="email" name="cobra_contact_email" value="<?php echo esc_attr(get_option('cobra_contact_email')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Copy Right</th>
                <td>
                    <input type="text" name="cobra_copy_right" value="<?php echo esc_attr(get_option('cobra_copy_right')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Login Page</th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'cobra_login_page',
                        'show_option_none' => 'Select a page',
                        'option_none_value' => '0',
                        'selected' => get_option('cobra_login_page')
                    ));
                    ?>
                    <p class="description">Select the page where your login form is located</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Register Page</th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'cobra_register_page',
                        'show_option_none' => 'Select a page',
                        'option_none_value' => '0',
                        'selected' => get_option('cobra_register_page')
                    ));
                    ?>
                    <p class="description">Select the page where your registration form is located</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Account Page</th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'cobra_account_page',
                        'show_option_none' => 'Select a page',
                        'option_none_value' => '0',
                        'selected' => get_option('cobra_account_page')
                    ));
                    ?>
                    <p class="description">Select the page where your account form is located</p>
                </td>
            </tr>
        </table>
    <?php
    }

    private function social_settings_section()
    {
        $icons = array(
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin-in',
            'youtube' => 'fab fa-youtube',
            'custom' => 'fas fa-link'
        );
    ?>
        <div id="social-links-container">
            <?php
            $social_links = get_option('cobra_social_links', array());
            if (!empty($social_links)) {
                foreach ($social_links as $index => $link) {
            ?>
                    <div class="social-link-field">
                        <select name="cobra_social_links[<?php echo $index; ?>][platform]">
                            <option value="facebook" <?php selected($link['platform'], 'facebook'); ?>>Facebook</option>
                            <option value="twitter" <?php selected($link['platform'], 'twitter'); ?>>Twitter</option>
                            <option value="instagram" <?php selected($link['platform'], 'instagram'); ?>>Instagram</option>
                            <option value="linkedin" <?php selected($link['platform'], 'linkedin'); ?>>LinkedIn</option>
                            <option value="youtube" <?php selected($link['platform'], 'youtube'); ?>>YouTube</option>
                            <option value="custom" <?php selected($link['platform'], 'custom'); ?>>Custom</option>
                        </select>
                        <input type="text" name="cobra_social_links[<?php echo $index; ?>][url]"
                            value="<?php echo esc_url($link['url']); ?>" placeholder="URL">
                        <button type="button" class="button remove-social-link">Remove</button>
                    </div>
            <?php
                }
            }
            ?>
        </div>
        <button type="button" class="button" id="add-social-link">Add Social Link</button>

        <h3>Additional Links</h3>
        <table class="form-table">
            <tr>
                <th scope="row">Video Promo URL</th>
                <td>
                    <input type="url" name="cobra_video_promo" value="<?php echo esc_url(get_option('cobra_video_promo')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Chrome Extension</th>
                <td>
                    <input type="url" name="cobra_chrome_extension" value="<?php echo esc_url(get_option('cobra_chrome_extension')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Edge Extension</th>
                <td>
                    <input type="url" name="cobra_edge_extension" value="<?php echo esc_url(get_option('cobra_edge_extension')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Android App</th>
                <td>
                    <input type="url" name="cobra_android_app" value="<?php echo esc_url(get_option('cobra_android_app')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">iOS App</th>
                <td>
                    <input type="url" name="cobra_ios_app" value="<?php echo esc_url(get_option('cobra_ios_app')); ?>" class="regular-text">
                </td>
            </tr>
        </table>
    <?php
    }

    private function style_settings_section()
    {
    ?>
        <table class="form-table">
            <tr>
                <th scope="row">Theme Style</th>
                <td>
                    <select name="cobra_theme_style">
                        <?php
                        $current_style = get_option('cobra_theme_style');
                        $style_dir = get_template_directory() . '/assets/css/';
                        if (is_dir($style_dir)) {
                            $styles = glob($style_dir . 'style-*.css');
                            foreach ($styles as $style) {
                                $style_name = basename($style, '.css');
                                $style_label = ucwords(str_replace('-', ' ', $style_name));
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr($style_name),
                                    selected($current_style, $style_name, false),
                                    esc_html($style_label)
                                );
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">Custom CSS</th>
                <td>
                    <textarea name="cobra_custom_css" rows="10" class="large-text code"><?php echo esc_textarea(get_option('cobra_custom_css')); ?></textarea>
                </td>
            </tr>
        </table>
<?php
    }

    private function handle_cache_actions()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Vérifier le nonce pour les actions de cache
        if (isset($_POST['cobra_cache_nonce']) && !wp_verify_nonce($_POST['cobra_cache_nonce'], 'cobra_cache_action')) {
            return;
        }

        if (isset($_POST['clear_cache'])) {
            $this->clear_all_cache();
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Cache vidé avec succès!</p></div>';
            });
        }

        if (isset($_POST['toggle_cache'])) {
            $current_status = get_option('cobra_cache_enabled', '1');
            $new_status = $current_status === '1' ? '0' : '1';
            update_option('cobra_cache_enabled', $new_status);
            
            $message = $new_status === '1' ? 'Cache activé!' : 'Cache désactivé!';
            add_action('admin_notices', function() use ($message) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            });
        }
    }

    private function clear_all_cache()
    {
        global $wpdb;
        
        // Clear WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Clear WordPress transients
        if ($wpdb) {
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
        }

        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Clear theme cache files if they exist
        $cache_dir = wp_upload_dir()['basedir'] . '/cache/cobra-theme/';
        if (is_dir($cache_dir)) {
            $this->delete_cache_directory($cache_dir);
        }

        // Clear CSS/JS cache
        delete_option('cobra_css_cache_version');
        delete_option('cobra_js_cache_version');
        
        // Update cache version to force refresh
        update_option('cobra_cache_version', time());
    }

    private function delete_cache_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $file_path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file_path)) {
                $this->delete_cache_directory($file_path);
            } else {
                unlink($file_path);
            }
        }
        rmdir($dir);
    }

    private function get_cache_status()
    {
        global $wpdb;
        
        $status = array();
        
        // Cache général
        $cache_enabled = get_option('cobra_cache_enabled', '1') === '1';
        $status['general'] = $cache_enabled ? 'Activé' : 'Désactivé';
        
        // Object Cache
        $status['object_cache'] = wp_using_ext_object_cache() ? 'Activé (External)' : 'Intégré WordPress';
        
        // OPCache
        $status['opcache'] = function_exists('opcache_get_status') && opcache_get_status() ? 'Activé' : 'Désactivé';
        
        // Transients count
        $transients_count = 0;
        if ($wpdb) {
            $transients_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
        }
        $status['transients'] = $transients_count . ' transients';
        
        // Cache files size
        $cache_dir = wp_upload_dir()['basedir'] . '/cache/cobra-theme/';
        $cache_size = is_dir($cache_dir) ? $this->get_directory_size($cache_dir) : 0;
        $status['files'] = $this->format_bytes($cache_size);
        
        // Environment
        $status['environment'] = wp_get_environment_type();
        
        return $status;
    }

    private function get_directory_size($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    private function format_bytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    private function cache_settings_section()
    {
        $cache_status = $this->get_cache_status();
        $cache_enabled = get_option('cobra_cache_enabled', '1') === '1';
    ?>
        <div class="cache-management">
            <h3>État du Cache</h3>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Type de Cache</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Cache Général</strong></td>
                        <td>
                            <span class="cache-status <?php echo $cache_enabled ? 'enabled' : 'disabled'; ?>">
                                <?php echo $cache_status['general']; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Object Cache</td>
                        <td><?php echo esc_html($cache_status['object_cache']); ?></td>
                    </tr>
                    <tr>
                        <td>OPCache PHP</td>
                        <td><?php echo esc_html($cache_status['opcache']); ?></td>
                    </tr>
                    <tr>
                        <td>Transients WordPress</td>
                        <td><?php echo esc_html($cache_status['transients']); ?></td>
                    </tr>
                    <tr>
                        <td>Fichiers Cache</td>
                        <td><?php echo esc_html($cache_status['files']); ?></td>
                    </tr>
                    <tr>
                        <td>Environnement</td>
                        <td><code><?php echo esc_html($cache_status['environment']); ?></code></td>
                    </tr>
                </tbody>
            </table>

            <h3>Actions Cache</h3>
            <div class="cache-actions">
                <form method="post" style="display: inline-block; margin-right: 10px;">
                    <?php wp_nonce_field('cobra_cache_action', 'cobra_cache_nonce'); ?>
                    <input type="submit" name="clear_cache" class="button button-primary" 
                           value="Vider Tout le Cache" 
                           onclick="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?');">
                </form>

                <form method="post" style="display: inline-block;">
                    <?php wp_nonce_field('cobra_cache_action', 'cobra_cache_nonce'); ?>
                    <input type="submit" name="toggle_cache" class="button button-secondary" 
                           value="<?php echo $cache_enabled ? 'Désactiver Cache' : 'Activer Cache'; ?>">
                </form>
            </div>

            <h3>Informations Détaillées</h3>
            <div class="cache-info">
                <h4>Configuration Actuelle</h4>
                <ul>
                    <li><strong>WP_CACHE:</strong> <?php echo defined('WP_CACHE') && WP_CACHE ? 'Activé' : 'Désactivé'; ?></li>
                    <li><strong>WP_DEBUG:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé'; ?></li>
                    <li><strong>SCRIPT_DEBUG:</strong> <?php echo defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Activé' : 'Désactivé'; ?></li>
                    <li><strong>Environnement:</strong> <?php echo esc_html(wp_get_environment_type()); ?></li>
                </ul>

                <h4>Cache de Développement</h4>
                <p>En mode développement, le cache des CSS/JS est automatiquement désactivé pour faciliter les modifications.</p>
                
                <?php if (wp_get_environment_type() === 'development'): ?>
                    <div class="notice notice-info inline">
                        <p><strong>Mode Développement Détecté:</strong> Cache CSS/JS désactivé automatiquement.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
        .cache-management .cache-status.enabled {
            color: #46b450;
            font-weight: bold;
        }
        .cache-management .cache-status.disabled {
            color: #dc3232;
            font-weight: bold;
        }
        .cache-actions {
            margin: 20px 0;
        }
        .cache-info {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin-top: 20px;
        }
        .cache-info ul {
            margin: 0;
        }
        .cache-info li {
            margin-bottom: 5px;
        }
        </style>
<?php
    }
}

// Initialize the settings
add_action('init', function () {
    Cobra_Theme_Settings::get_instance();
});
