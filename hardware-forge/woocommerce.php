<?php
/**
 * WooCommerce fallback template.
 * WooCommerce will use this when theme templates are not more specific.
 *
 * @package HardwareForge
 */

get_header( 'shop' );
?>

<?php
/**
 * Hook: woocommerce_before_main_content
 */
do_action( 'woocommerce_before_main_content' );
?>

<?php if ( is_singular( 'product' ) ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	endwhile;
	?>
<?php else : ?>
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<header class="hf-page-header woocommerce-products-header">
			<h1 class="hf-page-title woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		</header>
	<?php endif; ?>

	<?php do_action( 'woocommerce_archive_description' ); ?>

	<?php if ( woocommerce_product_loop() ) : ?>
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		<?php woocommerce_product_loop_start(); ?>

		<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			endwhile;
			?>
		<?php endif; ?>

		<?php woocommerce_product_loop_end(); ?>
		<?php do_action( 'woocommerce_after_shop_loop' ); ?>
	<?php else : ?>
		<?php do_action( 'woocommerce_no_products_found' ); ?>
	<?php endif; ?>
<?php endif; ?>

<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
?>

<?php
get_footer( 'shop' );
