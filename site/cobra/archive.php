<?php
/**
 * The template for displaying archive pages
 *
 * @package Cobra
 * @since 2.0
 */

get_header();
?>

<div class="archive-container">
    <!-- Archive Header -->
    <div class="archive-header-wrapper">
        <div class="container">
            <header class="page-header reveal reveal-up">
                <?php
                the_archive_title('<h1 class="archive-title animate-fadeIn">', '</h1>');
                
                // Display archive description if available
                if (the_archive_description()) {
                    echo '<div class="archive-description animate-fadeInUp delay-100">';
                    the_archive_description();
                    echo '</div>';
                }
                
                // Category/Tag count
                $term = get_queried_object();
                if (isset($term->count) && $term->count > 0) {
                    echo '<div class="archive-meta animate-fadeInUp delay-200">';
                    
                    // Post count
                    echo '<div class="post-count">';
                    echo '<i class="fas fa-file-alt"></i> ';
                    echo sprintf(_n('%s Post', '%s Posts', $term->count, 'cobra'), number_format_i18n($term->count));
                    echo '</div>';
                    
                    echo '</div>';
                }
                ?>
            </header>
        </div>
    </div>
    
    <!-- Archive Content -->
    <div class="container">
        <!-- Main Content -->
        <main class="archive-content">
            <?php if (have_posts()) : ?>
                <div class="posts-grid">
                    <?php
                    // Start the Loop
                    $counter = 0;
                    while (have_posts()) : the_post();
                        $counter++;
                        $delay_class = 'delay-' . (($counter - 1) % 3 + 1) . '00';
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-card reveal reveal-up ' . $delay_class); ?>>
                            <div class="post-card-inner">
                                <!-- Featured Image -->
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                        <?php the_post_thumbnail('medium_large'); ?>
                                        
                                        <?php
                                        // Display first category
                                        $categories = get_the_category();
                                        if (!empty($categories)) {
                                            echo '<span class="post-category">' . esc_html($categories[0]->name) . '</span>';
                                        }
                                        ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>" class="post-thumbnail no-image">
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        
                                        <?php
                                        // Display first category
                                        $categories = get_the_category();
                                        if (!empty($categories)) {
                                            echo '<span class="post-category">' . esc_html($categories[0]->name) . '</span>';
                                        }
                                        ?>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <!-- Post Meta -->
                                    <div class="post-meta">
                                        <span class="post-date">
                                            <i class="fas fa-calendar-alt"></i> <?php echo get_the_date(); ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Post Title -->
                                    <h2 class="post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    
                                    <!-- Post Excerpt -->
                                    <div class="post-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                    </div>
                                    
                                    <!-- Read More Link -->
                                    <a href="<?php the_permalink(); ?>" class="read-more-link">
                                        <?php esc_html_e('Read More', 'cobra'); ?> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container reveal reveal-up">
                    <?php
                    $pagination_args = array(
                        'prev_text' => '<i class="fas fa-arrow-left"></i> ' . esc_html__('Previous', 'cobra'),
                        'next_text' => esc_html__('Next', 'cobra') . ' <i class="fas fa-arrow-right"></i>',
                        'mid_size'  => 2,
                        'screen_reader_text' => esc_html__('Posts Navigation', 'cobra'),
                    );
                    
                    echo '<div class="pagination">';
                    echo paginate_links($pagination_args);
                    echo '</div>';
                    ?>
                </div>
                
            <?php else : ?>
                <!-- No Posts Found -->
                <div class="no-results reveal reveal-up">
                    <div class="no-results-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h2><?php esc_html_e('No Posts Found', 'cobra'); ?></h2>
                    <p><?php esc_html_e('Sorry, but no posts match your criteria.', 'cobra'); ?></p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn home-btn">
                        <i class="fas fa-home"></i> <?php esc_html_e('Back to Home', 'cobra'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>