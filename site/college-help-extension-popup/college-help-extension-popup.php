<?php
/**
 * Plugin Name: College Help Extension Popup
 * Plugin URI: https://collegehelpai.com
 * Description: Smart popup to promote browser extension installation with analytics tracking
 * Version: 3.0.1
 * Author: CollegeHelp AI
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CollegeHelpExtensionPopup {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'extension_popup_stats';
        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_popup_html'));
        add_action('wp_ajax_extension_popup_track', array($this, 'track_popup_action'));
        add_action('wp_ajax_nopriv_extension_popup_track', array($this, 'track_popup_action'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        register_activation_hook(__FILE__, array($this, 'create_stats_table'));
        register_deactivation_hook(__FILE__, array($this, 'cleanup'));
    }
    
    public function init() {
        // Plugin initialization
    }
    
    // Create stats table on activation
    public function create_stats_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action_type varchar(20) NOT NULL,
            browser varchar(20) NOT NULL,
            user_ip varchar(45) NOT NULL,
            user_agent text NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Enqueue scripts and styles
    public function enqueue_scripts() {
        if (!is_admin() && get_option('college_help_popup_enabled', '1') == '1') {
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
            wp_enqueue_script('college-help-popup', plugin_dir_url(__FILE__) . 'assets/popup.js', array('jquery'), '3.0.1', true);
            wp_enqueue_style('college-help-popup-style', plugin_dir_url(__FILE__) . 'assets/popup.css', array(), '3.0.1');
            
            // Localize script for AJAX
            wp_localize_script('college-help-popup', 'collegeHelpAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('extension_popup_nonce'),
                'chrome_link' => get_option('college_help_chrome_link', 'LINK_CHROME'),
                'edge_link' => get_option('college_help_edge_link', 'LINK_EDGE'),
                'github_link' => get_option('college_help_github_link', 'https://github.com/collegehelpAI-com/homework-ai-helper')
            ));
        }
    }
    
    // Add popup HTML to footer
    public function add_popup_html() {
        if (is_admin() || get_option('college_help_popup_enabled', '1') != '1') return;
        ?>
        <!-- College Help Extension Popup -->
        <div id="extensionPopup" class="college-help-popup-overlay">
            <div class="college-help-popup-container">
                <!-- Close Button -->
                <button class="college-help-close-btn" onclick="collegeHelpPopup.closePopup()">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Logo -->
                <div class="college-help-logo-container">
                    <img src="https://collegehelpai.com/wp-content/uploads/2025/04/Logo-line.png" alt="CollegeHelp AI" class="college-help-logo">
                    <div class="college-help-pulse-ring"></div>
                    <div class="college-help-pulse-ring college-help-pulse-ring-2"></div>
                </div>

                <!-- Content -->
                <div class="college-help-popup-content">
                    <h2 class="college-help-popup-title">🚀 Supercharge Your Studies!</h2>
                    <p class="college-help-popup-text">
                        Get instant help with homework, quizzes, and exams! Our smart extension works seamlessly in your browser to make studying faster and easier.
                    </p>
                    
                    <!-- Features List -->
                    <div class="college-help-features">
                        <div class="college-help-feature-item">
                            <i class="fas fa-bolt"></i>
                            <span>Instant Solutions</span>
                        </div>
                        <div class="college-help-feature-item">
                            <i class="fas fa-brain"></i>
                            <span>Smart AI Help</span>
                        </div>
                        <div class="college-help-feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Save Time</span>
                        </div>
                    </div>

                    <!-- Install Button Container -->
                    <div id="collegeHelpInstallButtonContainer" class="college-help-install-container">
                        <!-- Button will be inserted here by JavaScript -->
                    </div>

                    <!-- 5 Star Rating -->
                    <div class="college-help-rating">
                        <div class="college-help-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="college-help-popup-subtitle">Join thousands of students already using our extension!</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Track popup actions
    public function track_popup_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'extension_popup_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        $action_type = sanitize_text_field($_POST['action_type']);
        $browser = sanitize_text_field($_POST['browser']);
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $wpdb->insert(
            $this->table_name,
            array(
                'action_type' => $action_type,
                'browser' => $browser,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent
            )
        );
        
        wp_send_json_success('Action tracked');
    }
    
    // Add admin menu
    public function add_admin_menu() {
        add_options_page(
            'Extension Popup Settings',
            'Extension Popup',
            'manage_options',
            'college-help-popup',
            array($this, 'admin_page')
        );
    }
    
    // Admin page
    public function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('college_help_popup_enabled', isset($_POST['popup_enabled']) ? '1' : '0');
            update_option('college_help_chrome_link', sanitize_url($_POST['chrome_link']));
            update_option('college_help_edge_link', sanitize_url($_POST['edge_link']));
            update_option('college_help_github_link', sanitize_url($_POST['github_link']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $popup_enabled = get_option('college_help_popup_enabled', '1');
        $chrome_link = get_option('college_help_chrome_link', '');
        $edge_link = get_option('college_help_edge_link', '');
        $github_link = get_option('college_help_github_link', 'https://github.com/collegehelpAI-com/homework-ai-helper');
        
        // Get stats
        global $wpdb;
        $stats = $wpdb->get_results("
            SELECT action_type, browser, COUNT(*) as count 
            FROM $this->table_name 
            GROUP BY action_type, browser 
            ORDER BY action_type, browser
        ");
        
        $total_views = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name WHERE action_type = 'view'");
        $total_closes = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name WHERE action_type = 'close'");
        $total_installs = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name WHERE action_type = 'install'");
        
        ?>
        <div class="wrap">
            <h1>Extension Popup Settings & Stats</h1>
            
            <!-- Settings Form -->
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Popup</th>
                        <td>
                            <label>
                                <input type="checkbox" name="popup_enabled" value="1" <?php checked($popup_enabled, '1'); ?> />
                                Show popup to visitors
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Chrome Store Link</th>
                        <td><input type="url" name="chrome_link" value="<?php echo esc_attr($chrome_link); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Edge Store Link</th>
                        <td><input type="url" name="edge_link" value="<?php echo esc_attr($edge_link); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">GitHub Link</th>
                        <td><input type="url" name="github_link" value="<?php echo esc_attr($github_link); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            </form>
            
            <!-- Stats Dashboard -->
            <h2>Popup Statistics</h2>
            <div class="college-help-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
                <div class="college-help-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: center;">
                    <h3 style="color: #69b8db; margin: 0; font-size: 2em;"><?php echo $total_views; ?></h3>
                    <p style="color: #4c5e87; margin: 5px 0 0 0;">Total Views</p>
                </div>
                <div class="college-help-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: center;">
                    <h3 style="color: #69b8db; margin: 0; font-size: 2em;"><?php echo $total_closes; ?></h3>
                    <p style="color: #4c5e87; margin: 5px 0 0 0;">Total Closes</p>
                </div>
                <div class="college-help-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: center;">
                    <h3 style="color: #69b8db; margin: 0; font-size: 2em;"><?php echo $total_installs; ?></h3>
                    <p style="color: #4c5e87; margin: 5px 0 0 0;">Install Clicks</p>
                </div>
            </div>
            
            <!-- Conversion Rate -->
            <?php if ($total_views > 0): ?>
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
                <h3>Conversion Rates</h3>
                <p><strong>Install Rate:</strong> <?php echo round(($total_installs / $total_views) * 100, 2); ?>%</p>
                <p><strong>Close Rate:</strong> <?php echo round(($total_closes / $total_views) * 100, 2); ?>%</p>
            </div>
            <?php endif; ?>
            
            <!-- Detailed Stats Table -->
            <?php if ($stats): ?>
            <h3>Detailed Statistics</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Browser</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $stat): ?>
                    <tr>
                        <td><?php echo ucfirst($stat->action_type); ?></td>
                        <td><?php echo ucfirst($stat->browser); ?></td>
                        <td><?php echo $stat->count; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }
    
    // Cleanup on deactivation
    public function cleanup() {
        // Optional: Remove options
        // delete_option('college_help_chrome_link');
        // delete_option('college_help_edge_link');
    }
}

// Initialize the plugin
new CollegeHelpExtensionPopup();
 
?>