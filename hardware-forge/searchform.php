<?php
/**
 * Search form.
 *
 * @package HardwareForge
 */
?>
<form role="search" method="get" class="hf-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="hf-search-field"><?php esc_html_e( 'Search for:', 'hardware-forge' ); ?></label>
	<input type="search" id="hf-search-field" class="hf-search-form__field" placeholder="<?php esc_attr_e( 'Search hardware…', 'hardware-forge' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="hf-btn hf-btn--primary"><?php esc_html_e( 'Search', 'hardware-forge' ); ?></button>
</form>
