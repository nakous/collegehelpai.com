<?php
/**
 * The template for displaying all pages
 *
 * @package Cobra
 * @since 2.0
 */

get_header();
?>

<div class="page-container">
    <?php while (have_posts()) : the_post(); ?>
    
        <!-- Page Header -->
        <div class="page-header-wrapper">
            <div class="container">
                <header class="page-header reveal reveal-up">
                    <?php the_title('<h1 class="page-title animate-fadeIn">', '</h1>'); ?>
                </header>
            </div>
        </div>
        
        <!-- Featured Image -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="featured-image-wrapper">
                <div class="container">
                    <div class="page-featured-image   delay-200">
                        <?php the_post_thumbnail('full', array('class' => 'animate-fadeIn')); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="container">
            <div class="page-content-container">
                <article id="post-<?php the_ID(); ?>" <?php post_class('page-content   delay-300'); ?>>
                    <!-- Page Content -->
                    <div class="entry-content">
                        <?php 
                        the_content();
                        
                        wp_link_pages(array(
                            'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__('Pages:', 'cobra') . '</span>',
                            'after'  => '</div>',
                            'link_before' => '<span>',
                            'link_after'  => '</span>',
                        ));
                        ?>
                    </div>
                    
                    <?php if (comments_open() || get_comments_number()) : ?>
                        <div class="page-comments-container reveal reveal-up delay-400">
                            <?php comments_template(); ?>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        </div>
        
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>