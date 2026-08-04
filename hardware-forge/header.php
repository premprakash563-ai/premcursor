<?php
/**
 * Header template.
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
// Elementor Pro Theme Builder can replace the header entirely.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
	?>
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'hardware-forge' ); ?></a>

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
					<?php hardware_forge_cart_link(); ?>
				<?php endif; ?>
			</div>
		</div>
	</header>
<?php endif; ?>

<div id="page" class="hf-site">
