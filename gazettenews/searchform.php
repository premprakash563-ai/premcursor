<?php
/**
 * Search form.
 *
 * @package GazetteNews
 */
?>
<form role="search" method="get" class="search-form gn-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="gn-search-label">
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'gazettenews' ); ?></span>
		<?php echo gazettenews_svg_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Type a headline, topic, or city…', 'gazettenews' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off">
	</label>
	<button type="submit" class="search-submit"><?php esc_html_e( 'Search', 'gazettenews' ); ?></button>
</form>
