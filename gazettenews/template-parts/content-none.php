<?php
/**
 * No results.
 *
 * @package GazetteNews
 */
?>
<section class="no-results">
	<h1><?php esc_html_e( 'Nothing found', 'gazettenews' ); ?></h1>
	<p><?php esc_html_e( 'Try a different search or browse the latest stories below.', 'gazettenews' ); ?></p>
	<?php get_search_form(); ?>
</section>
