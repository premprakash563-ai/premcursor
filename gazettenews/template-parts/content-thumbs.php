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
		<div class="entry-meta">
			<?php gazettenews_posted_by( true ); ?>
			<span class="meta-sep">·</span>
			<?php gazettenews_comments_count(); ?>
			<span class="meta-sep">·</span>
			<?php gazettenews_views_count(); ?>
		</div>
	</div>
	<a class="thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'gazettenews-small' ); ?>
		<?php else : ?>
			<span class="thumb-fallback"></span>
		<?php endif; ?>
	</a>
</article>
