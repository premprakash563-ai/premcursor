<?php
/**
 * Rounded card with excerpt (4-col / slider).
 *
 * @package GazetteNews
 */
$cats = get_the_category();
$loc  = $cats ? $cats[0]->name : '';
?>
<article <?php post_class( 'card-round' ); ?>>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-module' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
	<div class="card-body">
		<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="round-meta">
			<?php if ( $loc ) : ?>
				<span class="loc"><?php echo esc_html( $loc ); ?></span>
			<?php endif; ?>
			<?php gazettenews_compact_meta(); ?>
		</div>
		<div class="entry-excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
