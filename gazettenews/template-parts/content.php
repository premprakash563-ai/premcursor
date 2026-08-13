<?php
/**
 * Standard content.
 *
 * @package GazetteNews
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card-default' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'gazettenews-module' ); ?></a>
	<?php endif; ?>
	<div class="card-body">
		<?php gazettenews_first_category(); ?>
		<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php gazettenews_entry_meta(); ?>
		<div class="entry-excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
