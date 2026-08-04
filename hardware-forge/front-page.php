<?php
/**
 * Front page — hardware storefront composition.
 * Elementor can fully replace this by editing the page with Elementor.
 *
 * @package HardwareForge
 */

get_header();

// If the static front page is an Elementor canvas, render post content only.
if ( hardware_forge_is_elementor_page() ) :
	?>
	<main id="primary" class="site-main hf-elementor-canvas">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</main>
	<?php
	get_footer();
	return;
endif;

$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="primary" class="site-main">

	<section class="hf-hero" aria-label="<?php esc_attr_e( 'Storefront hero', 'hardware-forge' ); ?>">
		<div class="hf-hero__media" aria-hidden="true">
			<div class="hf-hero__grain"></div>
			<div class="hf-hero__sheen"></div>
		</div>

		<div class="hf-hero__content hf-container">
			<?php hardware_forge_site_brand( 'hf-brand--hero' ); ?>

			<h1 class="hf-hero__headline">
				<?php esc_html_e( 'Hardware that holds the job together.', 'hardware-forge' ); ?>
			</h1>

			<p class="hf-hero__support">
				<?php esc_html_e( 'Fasteners, power tools, and industrial supplies — stocked for tradespeople who do not wait.', 'hardware-forge' ); ?>
			</p>

			<div class="hf-hero__actions">
				<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
					<?php esc_html_e( 'Shop the catalogue', 'hardware-forge' ); ?>
				</a>
				<a class="hf-btn hf-btn--ghost" href="#hf-categories">
					<?php esc_html_e( 'Browse categories', 'hardware-forge' ); ?>
				</a>
			</div>
		</div>
	</section>

	<section id="hf-categories" class="hf-section hf-categories">
		<div class="hf-container">
			<header class="hf-section__header">
				<h2 class="hf-section__title"><?php esc_html_e( 'Built for the aisle.', 'hardware-forge' ); ?></h2>
				<p class="hf-section__lead"><?php esc_html_e( 'Find what the job needs — without the clutter.', 'hardware-forge' ); ?></p>
			</header>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<ul class="hf-category-rail">
					<?php
					$terms = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => false,
							'number'     => 6,
							'parent'     => 0,
						)
					);

					if ( ! is_wp_error( $terms ) && $terms ) :
						foreach ( $terms as $term ) :
							$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
							$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'hardware-forge-card' ) : '';
							?>
							<li class="hf-category-rail__item">
								<a class="hf-category-link" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
									<span class="hf-category-link__visual" <?php echo $image ? 'style="background-image:url(' . esc_url( $image ) . ')"' : ''; ?>></span>
									<span class="hf-category-link__name"><?php echo esc_html( $term->name ); ?></span>
								</a>
							</li>
							<?php
						endforeach;
					else :
						?>
						<li class="hf-category-rail__item hf-category-rail__item--empty">
							<p><?php esc_html_e( 'Add WooCommerce product categories to populate this section — or rebuild it in Elementor.', 'hardware-forge' ); ?></p>
						</li>
					<?php endif; ?>
				</ul>
			<?php else : ?>
				<p class="hf-note"><?php esc_html_e( 'Activate WooCommerce to show live product categories here.', 'hardware-forge' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="hf-section hf-featured">
		<div class="hf-container">
			<header class="hf-section__header">
				<h2 class="hf-section__title"><?php esc_html_e( 'On the rack now.', 'hardware-forge' ); ?></h2>
				<p class="hf-section__lead"><?php esc_html_e( 'Featured stock ready to move.', 'hardware-forge' ); ?></p>
			</header>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<?php
				echo do_shortcode( '[products limit="4" columns="4" visibility="featured" orderby="date"]' );
				?>
			<?php else : ?>
				<p class="hf-note"><?php esc_html_e( 'Featured products appear here once WooCommerce is active.', 'hardware-forge' ); ?></p>
			<?php endif; ?>

			<p class="hf-section__cta">
				<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
					<?php esc_html_e( 'View all products', 'hardware-forge' ); ?>
				</a>
			</p>
		</div>
	</section>

	<section class="hf-section hf-promise">
		<div class="hf-container hf-promise__inner">
			<h2 class="hf-section__title"><?php esc_html_e( 'Trade-ready fulfilment.', 'hardware-forge' ); ?></h2>
			<p class="hf-section__lead"><?php esc_html_e( 'Counter pickup, job-site delivery, and bulk hardware orders — configured in WooCommerce shipping zones and refined in Elementor.', 'hardware-forge' ); ?></p>
		</div>
	</section>

</main>

<?php
get_footer();
