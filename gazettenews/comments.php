<?php
/**
 * Comments template.
 *
 * @package GazetteNews
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title">
			<?php
			printf(
				esc_html( _n( '%s Comment', '%s Comments', get_comments_number(), 'gazettenews' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h3>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
			) );
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() ) {
		echo '<p class="no-comments">' . esc_html__( 'Comments are closed.', 'gazettenews' ) . '</p>';
	}
	comment_form();
	?>
</div>
