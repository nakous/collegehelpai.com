<?php
/**
 * The main template file.
 *
 * This is the fallback template for the theme. It is used to display a page when no other specific template is available.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Cobra
 */

get_header(); ?>

 
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            get_template_part( 'template-parts/content', get_post_type() );
        endwhile;
    else :
        get_template_part( 'template-parts/content', 'none' );
    endif;
    ?>


<?php
 
get_footer();