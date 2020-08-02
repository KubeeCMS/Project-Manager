<?php
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$content_pages = new WP_Query(
	array(
		'post_type' => 'leco_content_page',
		'name'      => get_query_var( 'leco_content_page' ),
	)
);
while ( $content_pages->have_posts() ) : $content_pages->the_post();
	$comment_args = array( 'post_id' => get_the_ID(), 'include_unapproved' => false, 'status' => 'approve' );
	if ( is_user_logged_in() ) {
		$comment_args['include_unapproved'] = get_current_user_id();
	} else {
		$commenter = wp_get_current_commenter();
		if ( '' !== $commenter['comment_author_email'] ) {
			$comment_args['include_unapproved'] = $commenter['comment_author_email'];
		}
	}

//	$comments_query = new WP_Comment_Query;
	$comments = new WP_Comment_Query( $comment_args );

	$comments_number = count( $comments->comments );
	?>

	<div id="comments" class="comments-area">

		<?php if ( isset( $_GET['justposted'] ) ) : ?>
            <p class="callout-green comment-awaiting-moderation"><?php esc_html_e( 'Your comment has been posted. If you don\'t see it here, it means it\'s still awaiting moderation.', 'leco-cp' ); ?></p>
		<?php endif; ?>

		<?php
		// You can start editing here -- including this comment!
		if ( $comments_number > 0 ) : ?>
			<h2 class="comments-title">
				<?php
				if ( '1' === $comments_number ) {
					/* translators: %s: post title */
					printf( _x( 'One Reply to &ldquo;%s&rdquo;', 'comments title', 'leco-cp' ), get_the_title() );
				} else {
					printf(
					/* translators: 1: number of comments, 2: post title */
						_nx(
							'%1$s Reply to &ldquo;%2$s&rdquo;',
							'%1$s Replies to &ldquo;%2$s&rdquo;',
							$comments_number,
							'comments title',
							'leco-cp'
						),
						number_format_i18n( $comments_number ),
						get_the_title()
					);
				}
				?>
			</h2>

			<ol class="comment-list">
				<?php
				wp_list_comments( array(
					'avatar_size' => 100,
					'style'       => 'ol',
					'short_ping'  => true,
					'reply_text'  => __( 'Reply', 'leco-cp' ),
					'format'      => 'html5'
				), $comments->comments );
				?>
			</ol>

			<?php

		endif; // Check for have_comments().

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

			<p class="no-comments"><?php _e( 'Comments are closed.', 'leco-cp' ); ?></p>
		<?php
		endif;

		comment_form();
		?>

	</div><!-- #comments -->
<?php endwhile; ?>
<?php wp_reset_postdata(); ?>
