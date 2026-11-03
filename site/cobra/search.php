<?php
/**
 * The template for displaying search results (posts only)
 *
 * @package Cobra
 * @since 2.0
 */

get_header();

// Get search query
$search_query = get_search_query();

// Modify the main query to include only posts
global $wp_query;
$wp_query->set('post_type', 'post');
$wp_query->get_posts();

// Count only post results
$post_count = $wp_query->found_posts;
?>

<div class="search-container">
    <!-- Search Header -->
    <div class="search-header-wrapper">
        <div class="container">
            <header class="search-header">
                <h1 class="search-title animate-fadeIn">
                    <?php
                    printf(
                        esc_html__('Search Results for: %s', 'cobra'),
                        '<span class="search-query">' . esc_html($search_query) . '</span>'
                    );
                    ?>
                </h1>
                
                <!-- Search Form -->
                <div class="search-form-container animate-fadeInUp delay-100">
                    <?php get_search_form(); ?>
                    <input type="hidden" name="post_type" value="post" form="searchform" />
                </div>
                
                <!-- Search Statistics -->
                <div class="search-stats animate-fadeInUp delay-200">
                    <?php
                    printf(
                        esc_html(_n(
                            'Found %s blog post',
                            'Found %s blog posts',
                            $post_count,
                            'cobra'
                        )),
                        '<span class="result-count">' . number_format_i18n($post_count) . '</span>'
                    );
                    ?>
                </div>
            </header>
        </div>
    </div>
    
    <!-- Search Results -->
    <div class="container">
        <div class="search-results-container">
            <?php if (have_posts()) : ?>
                <div class="search-results-list">
                    <?php
                    // Start the Loop
                    $counter = 0;
                    while (have_posts()) : the_post();
                        $counter++;
                        $delay_class = 'delay-' . (($counter - 1) % 3 + 1) . '00';
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-item ' . $delay_class); ?>>
                            <div class="result-inner">
                                <!-- Featured Image -->
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>" class="result-thumbnail">
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="result-content">
                                    <!-- Result Meta -->
                                    <div class="result-meta">
                                        <span class="result-date">
                                            <i class="fas fa-calendar-alt"></i> <?php echo get_the_date(); ?>
                                        </span>
                                        
                                        <?php if (has_category()) : ?>
                                            <span class="result-category">
                                                <i class="fas fa-folder"></i>
                                                <?php
                                                $categories = get_the_category();
                                                if (!empty($categories)) {
                                                    echo esc_html($categories[0]->name);
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Result Title -->
                                    <h2 class="result-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    
                                    <!-- Result Excerpt -->
                                    <div class="result-excerpt">
                                        <?php 
                                        // Custom excerpt with highlighted search terms
                                        $excerpt = wp_strip_all_tags(get_the_excerpt());
                                        $keys = explode(" ", $search_query);
                                        $excerpt = preg_replace('/(' . implode('|', $keys) . ')/iu', '<mark>\0</mark>', $excerpt);
                                        
                                        echo wp_kses_post($excerpt);
                                        ?>
                                    </div>
                                    
                                    <!-- Read More Link -->
                                    <a href="<?php the_permalink(); ?>" class="read-more-link">
                                        <?php esc_html_e('Read Full Post', 'cobra'); ?> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container">
                    <?php
                    $pagination_args = array(
                        'prev_text' => '<i class="fas fa-arrow-left"></i> ' . esc_html__('Previous', 'cobra'),
                        'next_text' => esc_html__('Next', 'cobra') . ' <i class="fas fa-arrow-right"></i>',
                        'mid_size'  => 2,
                        'screen_reader_text' => esc_html__('Search Results Navigation', 'cobra'),
                    );
                    
                    echo '<div class="pagination">';
                    echo paginate_links($pagination_args);
                    echo '</div>';
                    ?>
                </div>
                
            <?php else : ?>
                <!-- No Results Found -->
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2><?php esc_html_e('No Blog Posts Found', 'cobra'); ?></h2>
                    <p><?php esc_html_e('Sorry, but no blog posts matched your search terms. Please try again with different keywords.', 'cobra'); ?></p>
                    
                    <!-- Search Tips -->
                    <div class="search-tips">
                        <h3><?php esc_html_e('Search Tips:', 'cobra'); ?></h3>
                        <ul>
                            <li><?php esc_html_e('Check your spelling.', 'cobra'); ?></li>
                            <li><?php esc_html_e('Try using more general keywords.', 'cobra'); ?></li>
                            <li><?php esc_html_e('Try using fewer keywords.', 'cobra'); ?></li>
                            <li><?php esc_html_e('Try different keywords that mean the same thing.', 'cobra'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="no-results-actions">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn home-btn">
                            <i class="fas fa-home"></i> <?php esc_html_e('Back to Home', 'cobra'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="btn blog-btn">
                            <i class="fas fa-newspaper"></i> <?php esc_html_e('View All Blog Posts', 'cobra'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>