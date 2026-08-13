<?php
/**
 * Grid card.
 *
 * @package GazetteNews
 */
?>
<article <?php post_class( 'card-grid' ); ?>>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-module' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
	<div class="card-body">
		<?php gazettenews_first_category(); ?>
		<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php gazettenews_entry_meta(); ?>
	</div>
</article>
