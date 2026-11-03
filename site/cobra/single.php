<?php
/**
 * The template for displaying all single posts
 *
 * @package Cobra
 * @since 2.0
 */

get_header();
?>

<div class="single-post-container">
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Post Header Section -->
        <div class="post-header-wrapper">
            <div class="container">
                <header class="post-header reveal reveal-up">
                    <!-- Post Categories -->
                    <div class="post-categories animate-fadeIn">
                        <?php
                        $categories = get_the_category();
                        if (!empty($categories)) {
                            echo '<div class="category-list">';
                            foreach ($categories as $category) {
                                echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="category-badge">' . 
                                     '<i class="fas fa-tag"></i> ' . esc_html($category->name) . '</a>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <!-- Post Title -->
                    <?php the_title('<h1 class="post-title animate-fadeInUp delay-100">', '</h1>'); ?>
                    
                    <!-- Post Meta -->
                    <div class="post-meta animate-fadeInUp delay-200">
                        <!-- Author Info -->
                        <div class="post-author">
                           
                            <div class="author-info">
                           
                                <span class="post-date">
                                    <i class="fas fa-calendar-alt"></i> <?php echo get_the_date(); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Reading Time -->
                        <div class="reading-time">
                            <?php
                            $content = get_post_field('post_content', get_the_ID());
                            $word_count = str_word_count(strip_tags($content));
                            $reading_time = ceil($word_count / 200); // Assuming average reading speed of 200 words per minute
                            ?>
                            <i class="fas fa-clock"></i> <?php echo $reading_time; ?> min read
                        </div>
                    </div>
                </header>
            </div>
        </div>
        
        <!-- Featured Image -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="featured-image-wrapper">
                <div class="container">
                    <div class="post-featured-image reveal reveal-up delay-300">
                        <?php the_post_thumbnail('full', array('class' => 'animate-fadeIn')); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Main Content Section -->
        <div class="container">
            
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-content reveal reveal-up delay-300'); ?>>
                        <!-- Post Content -->
                        <div class="entry-content">
                            <?php the_content(); ?>
                            
                            <?php
                            wp_link_pages(array(
                                'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__('Pages:', 'cobra') . '</span>',
                                'after'  => '</div>',
                                'link_before' => '<span>',
                                'link_after'  => '</span>',
                            ));
                            ?>
                        </div>
                        
                        <!-- Post Tags -->
                        <?php if (has_tag()) : ?>
                            <div class="post-tags">
                                <?php the_tags('<span class="tags-title"><i class="fas fa-tags"></i> ' . esc_html__('Tags:', 'cobra') . '</span> ', ', '); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Post Navigation -->
                        <div class="post-navigation">
                            <?php
                            $prev_post = get_previous_post();
                            $next_post = get_next_post();
                            ?>
                            
                            <div class="nav-links">
                                <?php if ($prev_post) : ?>
                                    <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="nav-previous">
                                        <div class="nav-title">
                                            <i class="fas fa-arrow-left"></i>
                                            <span class="nav-label"><?php esc_html_e('Previous Post', 'cobra'); ?></span>
                                            <span class="nav-post-title"><?php echo esc_html(get_the_title($prev_post->ID)); ?></span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($next_post) : ?>
                                    <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="nav-next">
                                        <div class="nav-title">
                                            <span class="nav-label"><?php esc_html_e('Next Post', 'cobra'); ?></span>
                                            <span class="nav-post-title"><?php echo esc_html(get_the_title($next_post->ID)); ?></span>
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Author Box -->
                        <!-- <div class="author-box">
                            <div class="author-avatar">
                                <?php echo get_avatar(get_the_author_meta('ID'), 100); ?>
                            </div>
                            <div class="author-details">
                                <h3 class="author-title"><?php the_author(); ?></h3>
                                <?php if (get_the_author_meta('description')) : ?>
                                    <p class="author-description"><?php the_author_meta('description'); ?></p>
                                <?php else : ?>
                                    <p class="author-description"><?php esc_html_e('This author has not provided a biography.', 'cobra'); ?></p>
                                <?php endif; ?>
                                <div class="author-social">
                                    <?php 
                                    $website = get_the_author_meta('user_url');
                                    if (!empty($website)) : ?>
                                        <a href="<?php echo esc_url($website); ?>" class="author-link website" target="_blank">
                                            <i class="fas fa-globe"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div> -->
                        
                        <!-- Related Posts -->
                        <?php
                        $categories = get_the_category();
                        if ($categories) {
                            $category_ids = array();
                            foreach ($categories as $category) {
                                $category_ids[] = $category->term_id;
                            }
                            
                            $related_posts_args = array(
                                'category__in'      => $category_ids,
                                'post__not_in'      => array(get_the_ID()),
                                'posts_per_page'    => 3,
                                'orderby'           => 'rand',
                            );
                            
                            $related_posts = new WP_Query($related_posts_args);
                            
                            if ($related_posts->have_posts()) : ?>
                                <div class="related-posts">
                                    <h3 class="related-title"><?php esc_html_e('You might also like', 'cobra'); ?></h3>
                                    <div class="related-posts-grid">
                                        <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
                                            <div class="related-post-card">
                                                <a href="<?php the_permalink(); ?>" class="related-post-image">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <?php the_post_thumbnail('medium'); ?>
                                                    <?php else : ?>
                                                        <div class="no-thumbnail">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                                <div class="related-post-content">
                                                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                                    <div class="related-post-meta">
                                                        <span class="related-post-date">
                                                            <i class="fas fa-calendar-alt"></i> <?php echo get_the_date(); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            <?php endif;
                            wp_reset_postdata();
                        }
                        ?>
                        
                        <!-- Comments -->
                        <?php
                        if (comments_open() || get_comments_number()) {
                            comments_template();
                        }
                        ?>
                    </article>
              
            
        </div>
        
    <?php endwhile; ?>
</div>

<!-- Social Share Floating Bar -->
<div class="social-share-floating">
    <div class="share-title"><?php esc_html_e('Share', 'cobra'); ?></div>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" class="share-link facebook" target="_blank">
        <i class="fab fa-facebook-f"></i>
    </a>
    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" class="share-link twitter" target="_blank">
        <i class="fab fa-twitter"></i>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" class="share-link linkedin" target="_blank">
        <i class="fab fa-linkedin-in"></i>
    </a>
    <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" class="share-link email">
        <i class="fas fa-envelope"></i>
    </a>
</div>

<!-- Progress Bar -->
<div class="reading-progress-container">
    <div class="reading-progress-bar"></div>
</div>



<?php get_footer(); ?> 