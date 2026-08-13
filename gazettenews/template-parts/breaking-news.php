<?php
/**
 * Breaking news ticker.
 *
 * @package GazetteNews
 */

$count = max( 3, absint( get_theme_mod( 'gazettenews_breaking_count', 8 ) ) );
$q     = gazettenews_query( array( 'posts_per_page' => $count ) );
if ( ! $q->have_posts() ) {
	return;
}
$label = get_theme_mod( 'gazettenews_breaking_label', __( 'BREAKING NEWS', 'gazettenews' ) );
?>
<div class="breaking-bar">
	<div class="gn-container breaking-inner">
		<span class="breaking-label"><?php echo esc_html( $label ); ?></span>
		<div class="breaking-track" aria-live="polite">
			<ul class="breaking-list">
				<?php
				while ( $q->have_posts() ) {
					$q->the_post();
					echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
				}
				wp_reset_postdata();
				?>
			</ul>
		</div>
	</div>
</div>
