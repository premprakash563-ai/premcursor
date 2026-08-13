<?php
/**
 * Headline left, thumbnail right (sidebar news list).
 *
 * @package GazetteNews
 */
?>
<article <?php post_class( 'card-thumbs' ); ?>>
	<div class="card-body">
		<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php gazettenews_compact_meta(); ?>
	</div>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-small' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
</article>
