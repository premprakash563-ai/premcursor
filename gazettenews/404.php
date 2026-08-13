<?php
/**
 * 404.
 *
 * @package GazetteNews
 */

get_header();
?>

<div class="gn-container">
	<main id="primary" class="content-area error-404">
		<h1>404</h1>
		<p><?php esc_html_e( 'This page could not be found. Try searching or go back to the homepage.', 'gazettenews' ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="btn-accent" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'gazettenews' ); ?></a></p>
	</main>
</div>

<?php
get_footer();
