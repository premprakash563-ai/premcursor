<?php
/**
 * Front page: magazine layout or block-built page.
 *
 * @package GazetteNews
 */

get_header();

$mode = gazettenews_get_home_mode();
if ( 'gutenberg' === $mode && is_front_page() && ! is_home() ) {
	echo '<div class="gn-container front-page-content">';
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	echo '</div>';
} else {
	get_template_part( 'template-parts/magazine' );
}

get_footer();
