<?php
// Footer template for the Cobra theme
?>
    </main>
    </div>
    <footer class="the-footer py-4" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-start reveal reveal-left">
                    <div id="logo">
                        <?php
                        $logo = get_cobra_setting('cobra_logo');
                        $site_name = get_cobra_setting('cobra_name');
                        ?>
                        <?php if ($logo): ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>"
                               aria-label="<?php echo esc_attr(sprintf(__('%s - Return to homepage', 'cobra'), $site_name)); ?>">
                                <img src="<?php echo esc_url($logo); ?>" 
                                     alt="<?php echo esc_attr($site_name); ?>" 
                                     class="site-logo"
                                     width="150"
                                     height="auto">
                            </a>
                        <?php else: ?>
                            <a class="site-name" 
                               href="<?php echo esc_url(home_url('/')); ?>"
                               aria-label="<?php echo esc_attr(sprintf(__('%s - Return to homepage', 'cobra'), $site_name)); ?>">
                                <?php echo esc_html($site_name); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="mt-3"><?php echo esc_html(get_option('cobra_description')); ?></p>
                </div>
                
                <div class="col-md-4 text-start reveal reveal-up">
                    <h4 class="footer-heading"><?php esc_html_e('Quick Links', 'cobra'); ?></h4>
                    <nav class="footer-navigation" 
                         role="navigation" 
                         aria-label="<?php esc_attr_e('Footer navigation', 'cobra'); ?>">
                        <div class="" id="footermenu">
                            <?php
                            if (has_nav_menu('footer-menu')) {
                                wp_nav_menu(array(
                                    'theme_location'  => 'footer-menu',
                                    'depth'           => 2,
                                    'container'       => false,
                                    'menu_class'      => 'footer-menu-list',
                                    'fallback_cb'     => '__return_false',
                                    'walker'          => new WP_Bootstrap_Navwalker()
                                ));
                            } else {
                                if (current_user_can('manage_options')) {
                                    echo '<div class="no-menu-warning" role="alert">';
                                    printf(
                                        /* translators: %s: URL to menu admin */
                                        __('Please assign a menu to the <a href="%s">footer menu location</a>.', 'cobra'),
                                        esc_url(admin_url('nav-menus.php'))
                                    );
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </nav>
                </div>
                
                <div class="col-md-4 text-start reveal reveal-right">
                    <h4 class="footer-heading">Download</h4>
                    <?php
                    _cobra_custom_footer_download()
                    ?>
                </div>
            </div>

            <div class="row footer-bottom mt-4">
                <div class="col-md-6 text-start">
                    <p class="copyright">&copy; <?php echo get_option('cobra_copy_right'); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <?php
                    // Get social links from theme options
                    $social_links = get_option('cobra_social_links');
                    if (!empty($social_links)) {
                        echo '<div class="social-links" role="list" aria-label="' . esc_attr__('Social media links', 'cobra') . '">';
                        foreach ($social_links as $link) {
                            // Only display if we have both platform and URL
                            if (!empty($link['platform']) && !empty($link['url'])) {
                                $platform = esc_attr($link['platform']);
                                $url = esc_url($link['url']);
                                $platform_name = ucfirst($platform);

                                echo '<a href="' . $url . '" ';
                                echo 'class="social-link ' . $platform . ' animate-fadeIn" ';
                                echo 'target="_blank" ';
                                echo 'rel="noopener noreferrer" ';
                                echo 'aria-label="' . esc_attr(sprintf(__('Visit our %s page (opens in new window)', 'cobra'), $platform_name)) . '">';
                                echo '<i class="fab fa-' . $platform . '" aria-hidden="true"></i>';
                                echo '<span class="screen-reader-text">' . esc_html($platform_name) . '</span>';
                                echo '</a>';
                            }
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>

</body>
</html>