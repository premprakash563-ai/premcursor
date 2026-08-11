<?php
/**
 * Plugin Name: Thermolec Brand-Matching WooCommerce Advanced Slider
 * Description: Slider with dynamically sizeable category tabs, sorted products, and absolute side-centered layout navigation based on image_199f76.jpg.
 * Version: 4.2.7
 * Author: Custom Developer
 * Text Domain: thermolec-tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Thermolec_Advanced_Tabs {

    public function __construct() {
        add_shortcode( 'wc_tabs_filter', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        add_action( 'wp_ajax_thermolec_filter_products', array( $this, 'ajax_get_products' ) );
        add_action( 'wp_ajax_nopriv_thermolec_filter_products', array( $this, 'ajax_get_products' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_script( 'jquery' );
        wp_localize_script( 'jquery', 'thermolec_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ) );
    }

    public function render_shortcode( $atts ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return '<p>WooCommerce is required.</p>';
        }

        $parsed_atts = shortcode_atts( array(
            'pill_size' => '15px',
        ), $atts );

        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
        ) );

        ob_start();
        ?>
        <div class="tl-advanced-container" style="--tl-pill-font-size: <?php echo esc_attr( $parsed_atts['pill_size'] ); ?>;">

            <!-- Brand Button Row Configuration matching image_199f76.jpg -->
            <div class="tl-pills-row">
                <button class="tl-brand-pill active" data-category="all">ALL</button>
                <?php foreach ( $categories as $cat ) : ?>
                    <button class="tl-brand-pill" data-category="<?php echo esc_attr( $cat->slug ); ?>">
                        <?php echo esc_html( strtoupper( $cat->name ) ); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Carousel Sliding Area with Left/Right Edge Mounted Buttons -->
            <div class="tl-carousel-viewport">

                <!-- Positioned absolutely at left/right middle edges -->
                <button class="tl-control-arrow arrow-left" aria-label="Previous">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="tl-control-arrow arrow-right" aria-label="Next">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><path d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="tl-grid-scroll-track">
                    <div class="tl-product-grid">
                        <?php $this->render_products_html( 'all' ); ?>
                    </div>
                </div>

                <!-- Custom Blurry Overlay Loader -->
                <div class="tl-blur-loader" style="display: none;">
                    <div class="tl-spinner"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_products_html( $category_slug ) {
        // Safe standard query configuration with custom ordering
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 100,
            'status'         => 'publish',
            'orderby'        => 'date',
            'order'          => 'ASC',
        );

        if ( $category_slug !== 'all' && ! empty( $category_slug ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ),
            );
        }

        $loop = new WP_Query( $args );

        if ( $loop->have_posts() ) {
            while ( $loop->have_posts() ) :
                $loop->the_post();
                $product = wc_get_product( get_the_ID() );
                if ( ! $product ) {
                    continue;
                }

                $img_id  = $product->get_image_id();
                $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : wc_placeholder_img_src();
                $img_alt = $img_id ? get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';
                if ( empty( $img_alt ) ) {
                    $img_alt = get_the_title();
                }
                ?>
                <div class="tl-product-card">
                    <div class="tl-card-image">
                        <img class="tl-card-img" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" />
                        <div class="tl-card-overlay">
                            <a href="<?php echo esc_url( get_permalink() ); ?>" class="tl-details-link">VIEW DETAILS</a>
                        </div>
                    </div>
                    <div class="tl-card-info">
                        <h4 class="tl-product-name"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h4>
                        <div class="tl-price-box"><?php echo $product->get_price_html(); ?></div>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        } else {
            echo '<div class="tl-empty-notice"><p>No products found in this category.</p></div>';
        }
    }

    public function ajax_get_products() {
        $category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : 'all';

        ob_start();
        $this->render_products_html( $category );
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html' => $html,
        ) );
    }
}

// Styling Interface
add_action( 'wp_footer', function() {
    ?>
<style>
.tl-advanced-container {
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* --- Category Pills Section --- */
.tl-pills-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 14px 16px;
    flex-wrap: wrap;
    margin-bottom: 2.5rem;
    width: 100%;
}

.tl-brand-pill {
    background: #ffffff;
    border: 2px solid #205393;
    color: #111111;
    padding: 14px 32px;
    font-size: var(--tl-pill-font-size, 15px);
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    border-radius: 50px;
    transition: all 0.2s ease-in-out;
    text-transform: uppercase;
    outline: none;
}
.tl-brand-pill:hover {
    background: #f4f7fc;
    color: #205393;
}

.tl-brand-pill.active {
    background: #205393 !important;
    color: #ffffff !important;
    border-color: #205393 !important;
    box-shadow: 0 4px 12px rgba(32, 83, 147, 0.2);
}

/* --- Carousel Layout Section --- */
.tl-carousel-viewport {
    position: relative;
    width: 100%;
    padding: 0 10px;
    box-sizing: border-box;
}

.tl-control-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #111111;
    border: none;
    color: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    z-index: 10;
}
.tl-control-arrow:hover {
    background: #205393;
}

.tl-control-arrow.arrow-left { left: -15px; }
.tl-control-arrow.arrow-right { right: -15px; }

.tl-grid-scroll-track {
    overflow-x: auto;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    padding: 10px 5px 20px 5px;
}
.tl-grid-scroll-track::-webkit-scrollbar {
    height: 4px;
}
.tl-grid-scroll-track::-webkit-scrollbar-track {
    background: #f5f5f5;
}
.tl-grid-scroll-track::-webkit-scrollbar-thumb {
    background: #205393;
    border-radius: 10px;
}

.tl-product-grid {
    display: flex;
    gap: 24px;
}

/* Desktop sizing (No changes) */
.tl-product-card {
    flex: 0 0 calc(25% - 18px);
    min-width: 270px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
}

@media (max-width: 1024px) {
    .tl-product-card { flex: 0 0 calc(33.33% - 16px); }
}

/* 📱 MOBILE SPECIFIC RESPONSIVE REBOOT (Under 768px) */
@media (max-width: 768px) {
    /* 1. Category tabs single line scrollable row */
    .tl-pills-row {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        padding-bottom: 8px !important;
        gap: 10px !important;
    }
    .tl-pills-row::-webkit-scrollbar {
        display: none !important;
    }
    .tl-brand-pill {
        padding: 10px 22px !important;
        font-size: 13px !important;
        white-space: nowrap !important;
    }

    /* 2. Hide big desktop arrows on mobile view */
    .tl-control-arrow {
        display: none !important;
    }

    /* 3. Safe mobile viewport adjustments for card tracking */
    .tl-grid-scroll-track {
        scroll-snap-type: x mandatory !important;
        padding: 10px 0px 20px 0px !important;
    }
    .tl-product-card {
        flex: 0 0 calc(100% - 30px) !important;
        min-width: calc(100% - 30px) !important;
        scroll-snap-align: center !important;
        margin: 0 15px !important;
    }
}

/* --- Base Product Inner Card Styles --- */
.tl-card-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    background-color: #fafafa;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    border: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Full image visible — no cropping */
.tl-card-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
}

.tl-card-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(32, 83, 147, 0.15);
    opacity: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.2s ease;
}
.tl-product-card:hover .tl-card-overlay { opacity: 1; }

.tl-details-link {
    background: #ffffff;
    color: #205393;
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.tl-card-info { padding: 12px 0; }
.tl-product-name {
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0 0 4px 0;
    line-height: 1.2;
}
.tl-product-name a { color: #333333; text-decoration: none; }
.tl-product-name a:hover { color: #205393; }

.tl-price-box { font-size: 14px; font-weight: 700; color: #111111; }
.tl-price-box del { color: #aaaaaa; margin-right: 5px; font-weight: 400; }

.tl-blur-loader { position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(255,255,255,0.7); display: flex; align-items: center; justify-content: center; z-index: 5; }
.tl-spinner { width: 32px; height: 32px; border: 2px solid #e0e0e0; border-top: 2px solid #205393; border-radius: 50%; animation: tl_rot 0.6s linear infinite; }
@keyframes tl_rot { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.tl-empty-notice { width: 100%; text-align: center; padding: 40px 0; color: #666; }
</style>

    <script>
    jQuery(document).ready(function($) {
        let currentCategory = 'all';

        // Scroll controls
        $('.arrow-right').on('click', function() {
            let scrollLength = $('.tl-product-card').outerWidth() + 24;
            $('.tl-grid-scroll-track').animate({ scrollLeft: '+=' + scrollLength }, 300);
        });
        $('.arrow-left').on('click', function() {
            let scrollLength = $('.tl-product-card').outerWidth() + 24;
            $('.tl-grid-scroll-track').animate({ scrollLeft: '-=' + scrollLength }, 300);
        });

        // Filter Switch handling
        $('.tl-brand-pill').on('click', function(e) {
            e.preventDefault();
            if($(this).hasClass('active')) return;

            $('.tl-brand-pill').removeClass('active');
            $(this).addClass('active');

            currentCategory = $(this).data('category');
            $('.tl-blur-loader').fadeIn(100);

            fetchProducts(currentCategory);
        });

        function fetchProducts(category) {
            $.ajax({
                url: thermolec_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'thermolec_filter_products',
                    category: category
                },
                success: function(response) {
                    $('.tl-blur-loader').fadeOut(100);

                    if (response.success) {
                        $('.tl-product-grid').html(response.data.html);
                        $('.tl-grid-scroll-track').scrollLeft(0);
                    }
                },
                error: function() {
                    $('.tl-blur-loader').fadeOut(100);
                }
            });
        }
    });
    </script>
    <?php
} );

new Thermolec_Advanced_Tabs();
