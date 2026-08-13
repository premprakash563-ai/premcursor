<?php
/**
 * Horizontal list card.
 *
 * @package GazetteNews
 */
?>
<article <?php post_class( 'card-list' ); ?>>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-thumb' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
	<div class="card-body">
		<?php gazettenews_first_category(); ?>
		<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php gazettenews_entry_meta(); ?>
		<div class="entry-excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
