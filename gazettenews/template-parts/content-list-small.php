<?php
/**
 * Compact list row.
 *
 * @package GazetteNews
 */
?>
<article <?php post_class( 'card-list-small' ); ?>>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-small' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
	<div class="card-body">
		<h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		<span class="list-date"><?php echo esc_html( get_the_date() ); ?></span>
	</div>
</article>
