<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Cobra
 * @since 2.0
 */

get_header();
?>

<!-- 404 Error Section -->
<section class="error-section">
    <div class="container">
        <div class="error-container">
            <div class="error-content reveal reveal-up">
                <div class="error-number animate-fadeIn">404</div>
                <h1 class="error-title animate-fadeInUp delay-100"><?php esc_html_e('Oops! Page Not Found', 'cobra'); ?></h1>
                <p class="error-description animate-fadeInUp delay-200"><?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'cobra'); ?></p>
                
                <div class="error-search animate-fadeInUp delay-300">
                    <p><?php esc_html_e('Try searching for what you\'re looking for:', 'cobra'); ?></p>
                    <?php get_search_form(); ?>
                </div>
                
                <div class="error-actions animate-fadeInUp delay-400">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn home-btn">
                        <i class="fas fa-home"></i> <?php esc_html_e('Back to Home', 'cobra'); ?>
                    </a>
                    <a href="#" onclick="history.back(); return false;" class="btn back-btn">
                        <i class="fas fa-arrow-left"></i> <?php esc_html_e('Go Back', 'cobra'); ?>
                    </a>
                </div>
            </div>
            
            <div class="error-image reveal reveal-right delay-200">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/404-illustration.svg" alt="404 Error" class="animate-float">
                <?php // If the SVG doesn't exist, make sure to create it or replace with another suitable image ?>
            </div>
        </div>
        
        <div class="suggested-pages reveal reveal-up delay-500">
            <h3><?php esc_html_e('You might be interested in:', 'cobra'); ?></h3>
            <div class="pages-grid">
                <?php
                // Display some recent posts
                $recent_posts = wp_get_recent_posts(array(
                    'numberposts' => 3,
                    'post_status' => 'publish'
                ));
                
                foreach ($recent_posts as $post) {
                    ?>
                    <div class="page-card">
                        <div class="page-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4><a href="<?php echo get_permalink($post['ID']); ?>"><?php echo $post['post_title']; ?></a></h4>
                        <p><?php echo wp_trim_words(get_the_excerpt($post['ID']), 12, '...'); ?></p>
                    </div>
                    <?php
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
?>