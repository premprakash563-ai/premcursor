<?php
/**
 * Header — modern Powerhouse-style chrome (Elementor-replaceable).
 *
 * @package HardwareForge
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

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
	?>
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'hardware-forge' ); ?></a>

	<div class="hf-topbar">
		<div class="hf-container hf-topbar__inner">
			<p>
				<?php esc_html_e( 'Nationwide service centers open — warranty support for every registered tool.', 'hardware-forge' ); ?>
				<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Learn more', 'hardware-forge' ); ?></a>
			</p>
		</div>
	</div>

	<header class="hf-site-header" data-hf-header>
		<div class="hf-site-header__inner hf-container">
			<?php hardware_forge_site_brand(); ?>

			<button
				class="hf-nav-toggle"
				type="button"
				aria-expanded="false"
				aria-controls="hf-primary-nav"
				data-hf-nav-toggle
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'hardware-forge' ); ?></span>
				<span class="hf-nav-toggle__bars" aria-hidden="true"></span>
			</button>

			<nav id="hf-primary-nav" class="hf-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'hardware-forge' ); ?>" data-hf-nav>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_class'     => 'hf-menu',
						'container'      => false,
						'fallback_cb'    => 'hardware_forge_fallback_menu',
					)
				);
				?>
			</nav>

			<div class="hf-header-actions">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<a class="hf-account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<?php esc_html_e( 'My Account', 'hardware-forge' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="hf-searchbar">
			<div class="hf-container hf-searchbar__inner">
				<form class="hf-searchbar__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<input type="hidden" name="post_type" value="product">
					<?php endif; ?>
					<label class="screen-reader-text" for="hf-product-search"><?php esc_html_e( 'Search products', 'hardware-forge' ); ?></label>
					<input
						id="hf-product-search"
						type="search"
						name="s"
						value="<?php echo esc_attr( get_search_query() ); ?>"
						placeholder="<?php esc_attr_e( 'What are you looking for?', 'hardware-forge' ); ?>"
					>
					<button type="submit" class="hf-searchbar__submit" aria-label="<?php esc_attr_e( 'Search', 'hardware-forge' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
				</form>

				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<div class="hf-searchbar__cart">
						<?php hardware_forge_cart_link(); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>
<?php endif; ?>

<div id="page" class="hf-site">
