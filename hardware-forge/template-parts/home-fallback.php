<?php
/**
 * PHP fallback home markup — only used when the front page has no
 * Elementor / editor content yet.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uri      = HARDWARE_FORGE_URI . '/assets/images';
$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$about    = home_url( '/about/' );

$categories = array(
	array( 'label' => __( 'Power Tools', 'hardware-forge' ), 'img' => 'cat-power-tools.jpg', 'slug' => 'power-tools' ),
	array( 'label' => __( 'Welding Machines & Accessories', 'hardware-forge' ), 'img' => 'cat-welding.jpg', 'slug' => 'welding' ),
	array( 'label' => __( 'Generators', 'hardware-forge' ), 'img' => 'cat-generators.jpg', 'slug' => 'generators' ),
	array( 'label' => __( 'Pumps & Motors', 'hardware-forge' ), 'img' => 'cat-pumps.jpg', 'slug' => 'pumps-motors' ),
	array( 'label' => __( 'Hand Tools', 'hardware-forge' ), 'img' => 'cat-hand-tools.jpg', 'slug' => 'hand-tools' ),
	array( 'label' => __( 'Lighting & Electrical', 'hardware-forge' ), 'img' => 'cat-electrical.jpg', 'slug' => 'lighting-electrical' ),
	array( 'label' => __( 'Water Systems', 'hardware-forge' ), 'img' => 'cat-water.jpg', 'slug' => 'water-systems' ),
	array( 'label' => __( 'Accessories & Abrasives', 'hardware-forge' ), 'img' => 'cat-accessories.jpg', 'slug' => 'accessories' ),
);

$demo_products = array(
	array( 'title' => __( 'Emergency Saltwater LED Lamp', 'hardware-forge' ), 'price' => '₱399.00', 'img' => 'cat-electrical.jpg' ),
	array( 'title' => __( 'Heavy-Duty Long Rain Coat', 'hardware-forge' ), 'price' => '₱690.00', 'img' => 'cat-accessories.jpg' ),
	array( 'title' => __( 'PVC Rain Boots — Lined', 'hardware-forge' ), 'price' => '₱650.00', 'img' => 'cat-water.jpg' ),
	array( 'title' => __( 'Smart Inverter Generator 2.0KW', 'hardware-forge' ), 'price' => '₱26,500.00', 'img' => 'cat-generators.jpg' ),
	array( 'title' => __( 'Electric-Start Gasoline Generator 7.5KW', 'hardware-forge' ), 'price' => '₱39,500.00', 'img' => 'cat-generators.jpg' ),
	array( 'title' => __( 'Diesel Generator Electric Start 7.5KVA', 'hardware-forge' ), 'price' => '₱69,000.00', 'img' => 'cat-pumps.jpg' ),
);
?>

<section class="hf-hero" aria-label="<?php esc_attr_e( 'Featured campaign', 'hardware-forge' ); ?>">
	<div class="hf-hero__media" style="background-image:url('<?php echo esc_url( $uri . '/hero-power.jpg' ); ?>')" aria-hidden="true">
		<div class="hf-hero__veil"></div>
	</div>
	<div class="hf-hero__content hf-container">
		<p class="hf-hero__brand"><?php bloginfo( 'name' ); ?></p>
		<h1 class="hf-hero__headline"><?php esc_html_e( 'Register your tools. Lock in warranty that lasts.', 'hardware-forge' ); ?></h1>
		<p class="hf-hero__support"><?php esc_html_e( 'Online product warranty for power tools, generators, and jobsite gear — built for crews who work hard every day.', 'hardware-forge' ); ?></p>
		<div class="hf-hero__actions">
			<a class="hf-btn hf-btn--light" href="<?php echo esc_url( home_url( '/warranty/' ) ); ?>"><?php esc_html_e( 'Register warranty', 'hardware-forge' ); ?></a>
			<a class="hf-btn hf-btn--ghost-light" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop catalogue', 'hardware-forge' ); ?></a>
		</div>
	</div>
</section>

<section id="hf-products" class="hf-section hf-categories">
	<div class="hf-container">
		<header class="hf-section__header hf-section__header--center">
			<p class="hf-eyebrow"><?php esc_html_e( 'Catalogue', 'hardware-forge' ); ?></p>
			<h2 class="hf-section__title"><?php esc_html_e( 'Our Products', 'hardware-forge' ); ?></h2>
		</header>
		<ul class="hf-cat-grid">
			<?php foreach ( $categories as $cat ) : ?>
				<?php
				$link = $shop_url;
				if ( class_exists( 'WooCommerce' ) ) {
					$term = get_term_by( 'slug', $cat['slug'], 'product_cat' );
					if ( $term && ! is_wp_error( $term ) ) {
						$link = get_term_link( $term );
					}
				}
				?>
				<li>
					<a class="hf-cat" href="<?php echo esc_url( $link ); ?>">
						<span class="hf-cat__label"><?php echo esc_html( $cat['label'] ); ?></span>
						<span class="hf-cat__media">
							<img src="<?php echo esc_url( $uri . '/' . $cat['img'] ); ?>" alt="" loading="lazy" width="640" height="640">
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="hf-section__cta hf-section__cta--center">
			<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View more', 'hardware-forge' ); ?> <span aria-hidden="true">→</span></a>
		</p>
	</div>
</section>

<section class="hf-section hf-story">
	<div class="hf-container hf-story__grid">
		<figure class="hf-story__media">
			<img src="<?php echo esc_url( $uri . '/storefront.jpg' ); ?>" alt="<?php esc_attr_e( 'Hardware storefront', 'hardware-forge' ); ?>" loading="lazy" width="1200" height="900">
		</figure>
		<div class="hf-story__copy">
			<p class="hf-eyebrow"><?php esc_html_e( 'Since 2009', 'hardware-forge' ); ?></p>
			<h2 class="hf-section__title"><?php esc_html_e( 'A hardware brand built for real work.', 'hardware-forge' ); ?></h2>
			<p><?php esc_html_e( 'From power tools to generators, we supply tradespeople and serious DIYers with dependable equipment — stocked nationwide and backed by service centers that keep your tools on the job.', 'hardware-forge' ); ?></p>
			<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( $about ); ?>"><?php esc_html_e( 'About us', 'hardware-forge' ); ?></a>
		</div>
	</div>
</section>

<section class="hf-section hf-featured">
	<div class="hf-container">
		<header class="hf-section__header hf-section__header--center">
			<p class="hf-eyebrow"><?php esc_html_e( 'Best sellers', 'hardware-forge' ); ?></p>
			<h2 class="hf-section__title"><?php esc_html_e( 'Featured Products', 'hardware-forge' ); ?></h2>
		</header>
		<?php
		$show_demo = true;
		if ( class_exists( 'WooCommerce' ) ) {
			$featured = wc_get_products( array( 'status' => 'publish', 'featured' => true, 'limit' => 1, 'return' => 'ids' ) );
			if ( ! empty( $featured ) ) {
				$show_demo = false;
				echo '<div class="hf-woo-featured">' . do_shortcode( '[products limit="6" columns="3" visibility="featured" orderby="popularity"]' ) . '</div>';
			}
		}
		if ( $show_demo ) :
			?>
			<ul class="hf-product-grid">
				<?php foreach ( $demo_products as $product ) : ?>
					<li class="hf-product">
						<a class="hf-product__media" href="<?php echo esc_url( $shop_url ); ?>">
							<img src="<?php echo esc_url( $uri . '/' . $product['img'] ); ?>" alt="" loading="lazy" width="640" height="640">
						</a>
						<h3 class="hf-product__title"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( $product['title'] ); ?></a></h3>
						<p class="hf-product__price"><?php echo esc_html( $product['price'] ); ?></p>
						<a class="hf-btn hf-btn--primary hf-btn--block" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Quick view', 'hardware-forge' ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<section class="hf-section hf-locate">
	<div class="hf-container hf-locate__inner">
		<div>
			<h2 class="hf-section__title"><?php esc_html_e( 'Find a store near you', 'hardware-forge' ); ?></h2>
			<p><?php esc_html_e( 'Visit a branch for hands-on demos, spare parts, and after-sales support.', 'hardware-forge' ); ?></p>
			<a class="hf-btn hf-btn--dark" href="<?php echo esc_url( home_url( '/store-locator/' ) ); ?>"><?php esc_html_e( 'Store locator', 'hardware-forge' ); ?></a>
		</div>
		<div class="hf-marketplaces">
			<p class="hf-eyebrow"><?php esc_html_e( 'You may also shop online', 'hardware-forge' ); ?></p>
			<ul class="hf-marketplaces__list">
				<li><span><?php esc_html_e( 'Lazada', 'hardware-forge' ); ?></span></li>
				<li><span><?php esc_html_e( 'Shopee', 'hardware-forge' ); ?></span></li>
				<li><span><?php esc_html_e( 'TikTok Shop', 'hardware-forge' ); ?></span></li>
			</ul>
		</div>
	</div>
</section>

<section class="hf-section hf-club">
	<div class="hf-container hf-club__inner">
		<p class="hf-eyebrow"><?php esc_html_e( 'Stay sharp', 'hardware-forge' ); ?></p>
		<h2 class="hf-section__title"><?php esc_html_e( 'Join the Tough Club', 'hardware-forge' ); ?></h2>
		<p><?php esc_html_e( 'Get new releases, franchise openings, and exclusive tool drops — straight to your inbox.', 'hardware-forge' ); ?></p>
		<form class="hf-club__form" action="#" method="post" onsubmit="return false;">
			<div class="hf-club__row">
				<input type="text" name="fname" placeholder="<?php esc_attr_e( 'First name', 'hardware-forge' ); ?>">
				<input type="text" name="lname" placeholder="<?php esc_attr_e( 'Last name', 'hardware-forge' ); ?>">
			</div>
			<input type="email" name="email" placeholder="<?php esc_attr_e( 'Email address', 'hardware-forge' ); ?>" required>
			<button class="hf-btn hf-btn--primary hf-btn--block" type="submit"><?php esc_html_e( 'Submit', 'hardware-forge' ); ?></button>
			<p class="hf-club__hint"><?php esc_html_e( 'Replace this block with an Elementor Form / Mailchimp widget.', 'hardware-forge' ); ?></p>
		</form>
	</div>
</section>

<section class="hf-section hf-community">
	<div class="hf-container hf-community__grid">
		<div class="hf-community__copy">
			<p class="hf-eyebrow"><?php esc_html_e( 'Campaign', 'hardware-forge' ); ?></p>
			<h2 class="hf-section__title"><?php esc_html_e( 'When the crew shows up, the job gets done.', 'hardware-forge' ); ?></h2>
			<p><?php esc_html_e( 'See how builders, partners, and training academies put durable tools to work — from woodshop demos to full jobsite builds.', 'hardware-forge' ); ?></p>
			<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( home_url( '/campaigns/' ) ); ?>"><?php esc_html_e( 'Watch highlights', 'hardware-forge' ); ?></a>
		</div>
		<figure class="hf-community__media">
			<img src="<?php echo esc_url( $uri . '/community.jpg' ); ?>" alt="" loading="lazy" width="1600" height="900">
		</figure>
	</div>
</section>

<section class="hf-franchise">
	<div class="hf-container hf-franchise__grid">
		<figure class="hf-franchise__media">
			<img src="<?php echo esc_url( $uri . '/storefront.jpg' ); ?>" alt="" loading="lazy" width="1200" height="900">
		</figure>
		<div class="hf-franchise__copy">
			<p class="hf-eyebrow"><?php esc_html_e( 'Partnership', 'hardware-forge' ); ?></p>
			<h2 class="hf-section__title"><?php esc_html_e( 'Be your own boss.', 'hardware-forge' ); ?></h2>
			<p><?php esc_html_e( 'Own a hardware franchise with proven branding, training, and supply lines.', 'hardware-forge' ); ?></p>
			<p class="hf-franchise__phone"><a href="tel:+630000000000">(+63) 000 000 0000</a></p>
			<a class="hf-btn hf-btn--dark" href="<?php echo esc_url( home_url( '/franchising/' ) ); ?>"><?php esc_html_e( 'Franchise now', 'hardware-forge' ); ?></a>
		</div>
	</div>
</section>
