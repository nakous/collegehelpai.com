<?php
/**
 * Comments Template
 *
 * @package Cobra
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Don't load if password required
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h3 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            printf(
                _nx(
                    'One comment on "%2$s"',
                    '%1$s comments on "%2$s"',
                    $comments_number,
                    'comments title',
                    'cobra'
                ),
                number_format_i18n($comments_number),
                get_the_title()
            );
            ?>
        </h3>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 42,
            ));
            ?>
        </ol>

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation" role="navigation">
                <div class="nav-previous"><?php previous_comments_link(__('&larr; Older Comments', 'cobra')); ?></div>
                <div class="nav-next"><?php next_comments_link(__('Newer Comments &rarr;', 'cobra')); ?></div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments"><?php _e('Comments are closed.', 'cobra'); ?></p>
    <?php endif; ?>

    <?php comment_form(array(
        'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
        'title_reply_after'  => '</h3>',
        'class_form'         => 'comment-form',
        'class_submit'       => 'btn btn-primary',
    )); ?>
</div>
