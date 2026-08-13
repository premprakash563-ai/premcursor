<?php
/**
 * Overlay featured card (screenshot-style).
 *
 * @package GazetteNews
 */
?>
<article <?php post_class( 'card-overlay' ); ?>>
	<a class="overlay-link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-featured' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
		<span class="overlay-panel">
			<?php
			$cats = get_the_category();
			if ( $cats ) {
				echo '<span class="gn-cat">' . esc_html( $cats[0]->name ) . '</span>';
			}
			?>
			<h2><?php the_title(); ?></h2>
			<span class="overlay-meta">
				<?php echo esc_html( get_the_author() ); ?>
				· <?php echo esc_html( get_the_date() ); ?>
				<?php if ( gazettenews_show_meta( 'comments' ) ) : ?>
					· <?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?>
				<?php endif; ?>
				<?php if ( gazettenews_show_meta( 'views' ) ) : ?>
					· <?php echo esc_html( number_format_i18n( gazettenews_get_views() ) ); ?>
				<?php endif; ?>
			</span>
		</span>
	</a>
</article>
