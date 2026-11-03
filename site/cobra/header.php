<?php

/**
 * Header Template
 *
 * @package Cobra
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Yandex Verification -->
    <meta name="yandex-verification" content="286954029868be6e">
    <!-- DNS Prefetch and Preconnect managed by WordPress/Plugins -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" crossorigin>


    <!-- Critical CSS Inline for LCP/FCP optimization -->
    <style id="critical-inline-css">
    :root{--font-primary:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}
    body{margin:0;font-family:var(--font-primary);-webkit-font-smoothing:antialiased}
    .the-header{min-height:70px;display:flex;align-items:center;justify-content:space-between;padding:1rem}
    .site-logo{width:200px;height:50px}
    .hero-section{background:linear-gradient(135deg,#69b8db 0%,#4c5e87 100%);min-height:100vh;display:flex;align-items:center}
    .hero-content{color:#fff;text-align:center;max-width:800px}
    .hero-title{font-size:clamp(2rem,5vw,3.5rem);font-weight:700;margin-bottom:1.5rem}
    img{height:auto;max-width:100%}
    </style>

    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/Inter-VariableFont.ttf" as="font" type="font/ttf" crossorigin>

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_cobra_setting('cobra_logo')); ?>">
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(get_cobra_setting('cobra_logo')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url(get_cobra_setting('cobra_logo')); ?>">
    <!-- Theme Color -->
    <meta name="theme-color" content="#69b8db">
    <meta name="msapplication-TileColor" content="#69b8db">
    <!-- Performance hints -->
    <meta name="format-detection" content="telephone=no">
    <meta name="x-dns-prefetch-control" content="on">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Skip link for keyboard navigation -->
    <a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Skip to main content', 'cobra'); ?></a>

    <header class="the-header p-3" role="banner">
        <!-- Logo Section -->
        <?php
        $logo = get_cobra_setting('cobra_logo');
        $site_name = get_cobra_setting('cobra_name');
        ?>
            <div class="site-branding" id="logo-header">
            <?php if ($logo): ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" 
                   rel="home" 
                   class="custom-logo-link"
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Go to homepage', 'cobra'), $site_name)); ?>">
                    <img src="<?php echo esc_url($logo); ?>"
                        alt="<?php echo esc_attr($site_name); ?>"
                        class="site-logo"
                        width="200"
                        height="50"
                        loading="eager">
                </a>
            <?php else: ?>
                <h1 class="site-title">
                    <a href="<?php echo esc_url(home_url('/')); ?>" 
                       rel="home" 
                       class="site-name"
                       aria-label="<?php echo esc_attr(sprintf(__('%s - Homepage', 'cobra'), $site_name)); ?>">
                        <?php echo esc_html($site_name); ?>
                    </a>
                </h1>
            <?php endif; ?>
        </div>

        <!-- Primary Navigation -->
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary navigation', 'cobra'); ?>">
            <button class="menu-toggle"
                type="button"
                aria-controls="primary-menu"
                aria-expanded="false"
                aria-label="<?php esc_attr_e('Toggle navigation menu', 'cobra'); ?>">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="screen-reader-text"><?php esc_html_e('Menu', 'cobra'); ?></span>
            </button>

            <div class="primary-menu-container">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location'  => 'primary',
                        'menu_id'         => 'primary-menu',
                        'menu_class'      => 'primary-menu nav-menu',
                        'container'       => false,
                        'depth'           => 3,
                        'fallback_cb'     => false,
                        'walker'          => new WP_Bootstrap_Navwalker(),
                    ));
                } else {
                    // Show setup message for admin users
                    if (current_user_can('manage_options')) {
                        echo '<div class="no-menu-notice">';
                        printf(
                            /* translators: %s: URL to menu admin */
                            __('Please assign a menu to the <a href="%s">Primary Menu</a> location.', 'cobra'),
                            esc_url(admin_url('nav-menus.php'))
                        );
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </nav>

        <!-- User Authentication Section -->
        <div class="header-actions">
            <?php
            // Get page options with fallbacks
            $login_page = get_option('cobra_login_page');
            $register_page = get_option('cobra_register_page');
            $account_page = get_option('cobra_account_page');

            // Validate and build URLs
            $login_url = (!empty($login_page) && get_post_status($login_page) === 'publish')
                ? get_permalink($login_page)
                : wp_login_url(get_permalink());

            $register_url = (!empty($register_page) && get_post_status($register_page) === 'publish')
                ? get_permalink($register_page)
                : wp_registration_url();

            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                $display_name = $current_user->display_name;
                $logout_url = cobra_get_direct_logout_url(home_url());
                $account_url = (!empty($account_page) && get_post_status($account_page) === 'publish')
                    ? get_permalink($account_page)
                    : home_url();
            ?>
                <!-- Logged in user menu -->
                <div class="user-menu">
                    <button class="user-toggle"
                        type="button"
                        aria-expanded="false"
                        aria-haspopup="true"
                        aria-controls="user-dropdown-menu"
                        aria-label="<?php echo esc_attr(sprintf(__('User menu for %s', 'cobra'), $display_name)); ?>">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        <span class="user-name"><?php echo esc_html(wp_trim_words($display_name, 3, '...')); ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                    </button>

                    <ul class="user-dropdown" 
                        id="user-dropdown-menu" 
                        role="menu" 
                        aria-labelledby="user-menu-button"
                        aria-hidden="true">
                        <li role="none">
                            <a href="<?php echo esc_url($account_url); ?>" 
                               class="dropdown-link" 
                               role="menuitem"
                               tabindex="-1">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                <span><?php esc_html_e('My Account', 'cobra'); ?></span>
                            </a>
                        </li>
                        <?php
                        /**
                         * Hook for additional user menu items
                         * 
                         * @since 1.0.0
                         */
                        do_action('cobra_user_menu_items');
                        ?>
                        <li role="none">
                            <a href="<?php echo esc_url($logout_url); ?>" 
                               class="dropdown-link logout-link" 
                               role="menuitem"
                               tabindex="-1">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span><?php esc_html_e('Logout', 'cobra'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php } else { ?>
                <!-- Guest user authentication buttons -->
                <div class="auth-buttons">
                    <a href="<?php echo esc_url($login_url); ?>"
                        class="auth-button login-button"
                        aria-label="<?php esc_attr_e('Login to your account', 'cobra'); ?>">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                        <span><?php esc_html_e('Login', 'cobra'); ?></span>
                    </a>

                    <?php if (get_option('users_can_register')): ?>
                        <a href="<?php echo esc_url($register_url); ?>"
                            class="auth-button register-button"
                            aria-label="<?php esc_attr_e('Create new account', 'cobra'); ?>">
                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                            <span><?php esc_html_e('Register', 'cobra'); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>

    </header>

    <!-- Main content wrapper -->
    <div class="site-wrapper">
        <main id="main-content" class="site-main" role="main" tabindex="-1">
            <!-- Content will be inserted here by WordPress -->