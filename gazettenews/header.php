<?php
/**
 * Theme header.
 *
 * @package GazetteNews
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'gazettenews' ); ?></a>

<div id="page" class="site">
	<header class="site-header">
		<div class="top-bar">
			<div class="gn-container top-bar-inner">
				<div class="top-left">
					<?php if ( get_theme_mod( 'gazettenews_show_date', true ) ) : ?>
						<span class="top-date"><?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></span>
					<?php endif; ?>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'top',
						'container'      => false,
						'menu_class'     => 'top-menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					) );
					?>
				</div>
				<div class="top-right">
					<?php echo gazettenews_social_links(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo gazettenews_cart_count(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>

		<div class="header-mid">
			<div class="gn-container header-mid-inner">
				<div class="site-branding">
					<?php
					if ( has_custom_logo() ) {
						the_custom_logo();
					} else {
						$line1 = get_theme_mod( 'gazettenews_logo_line1' );
						$line2 = get_theme_mod( 'gazettenews_logo_line2', 'NEWS 24x7' );
						if ( ! $line1 ) {
							$line1 = get_bloginfo( 'name' );
						}
						echo '<a class="brand-lockup" href="' . esc_url( home_url( '/' ) ) . '">';
						echo '<span class="brand-hi">' . esc_html( $line1 ) . '</span>';
						if ( $line2 ) {
							echo '<span class="brand-en">' . esc_html( $line2 ) . '</span>';
						}
						echo '</a>';
					}
					$tag = get_theme_mod( 'gazettenews_tagline', __( 'News & Magazine', 'gazettenews' ) );
					if ( $tag ) {
						echo '<span class="site-tagline">' . esc_html( $tag ) . '</span>';
					}
					?>
				</div>
				<?php gazettenews_header_ad(); ?>
			</div>
		</div>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'gazettenews' ); ?>">
			<div class="gn-container nav-inner">
				<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
					<span></span><span></span><span></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'gazettenews' ); ?></span>
				</button>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'menu',
					'fallback_cb'    => 'gazettenews_primary_fallback',
				) );
				?>
				<button class="search-toggle" type="button" aria-expanded="false" aria-controls="gn-search-modal">
					<?php echo gazettenews_svg_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'gazettenews' ); ?></span>
				</button>
			</div>
		</nav>

		<?php get_template_part( 'template-parts/breaking-news' ); ?>
	</header>

	<div id="content" class="site-content">
