<?php
/**
 * WooCommerce Customizations
 * Optimizations for single product conversion
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Remove default WooCommerce wrappers
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Add theme wrappers
 */
function label_narrative_wrapper_start() {
    echo '<div id="primary" class="content-area">';
    echo '<main id="main" class="site-main">';
}
add_action( 'woocommerce_before_main_content', 'label_narrative_wrapper_start', 10 );

function label_narrative_wrapper_end() {
    echo '</main>';
    echo '</div>';
}
add_action( 'woocommerce_after_main_content', 'label_narrative_wrapper_end', 10 );

/**
 * Optimize product page for conversion
 */

// Change 'Add to cart' button text
function label_narrative_custom_add_to_cart_text( $text ) {
    return get_theme_mod( 'add_to_cart_text', __( 'Get Your Labels Now', 'label-narrative' ) );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'label_narrative_custom_add_to_cart_text' );

/**
 * Add trust badges below add to cart button
 */
function label_narrative_add_trust_badges() {
    ?>
    <div class="product-trust-badges">
        <div class="trust-badge">
            <span class="trust-badge-icon">🔒</span>
            <span class="trust-badge-text"><?php echo esc_html( get_theme_mod( 'product_trust_1', __( 'Secure Payment', 'label-narrative' ) ) ); ?></span>
        </div>
        <div class="trust-badge">
            <span class="trust-badge-icon">🚚</span>
            <span class="trust-badge-text"><?php echo esc_html( get_theme_mod( 'product_trust_2', __( 'Fast Delivery', 'label-narrative' ) ) ); ?></span>
        </div>
        <div class="trust-badge">
            <span class="trust-badge-icon">✓</span>
            <span class="trust-badge-text"><?php echo esc_html( get_theme_mod( 'product_trust_3', __( 'Quality Guaranteed', 'label-narrative' ) ) ); ?></span>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_after_add_to_cart_button', 'label_narrative_add_trust_badges', 10 );

/**
 * Add urgency/scarcity messaging
 */
function label_narrative_add_urgency_message() {
    global $product;

    $stock_quantity = $product->get_stock_quantity();

    if ( $stock_quantity && $stock_quantity <= 10 ) {
        echo '<div class="urgency-message">';
        echo '<span class="urgency-icon">🔥</span>';
        echo '<span class="urgency-text">' . sprintf( esc_html__( 'Only %d left in stock!', 'label-narrative' ), $stock_quantity ) . '</span>';
        echo '</div>';
    }
}
add_action( 'woocommerce_before_add_to_cart_button', 'label_narrative_add_urgency_message', 10 );

/**
 * Add product customizer options to WooCommerce
 */
function label_narrative_product_customizer( $wp_customize ) {
    // Product Page Section
    $wp_customize->add_section( 'label_narrative_product', array(
        'title'    => __( 'Product Page', 'label-narrative' ),
        'priority' => 35,
    ) );

    // Product Story
    $wp_customize->add_setting( 'product_story_enable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'product_story_enable', array(
        'label'   => __( 'Enable Product Story Section', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'product_story_headline', array(
        'default'           => __( 'Crafted for Your Success', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'product_story_headline', array(
        'label'   => __( 'Product Story Headline', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'product_story_content', array(
        'default'           => __( 'We understand that every label tells a story—your story. That\'s why we use premium paper stock and vibrant, fade-resistant inks to ensure your brand looks its best on every product.

Our 9×9cm labels are the perfect size for product packaging, jars, bottles, and boxes. Choose between classic square labels or eye-catching circle labels to match your brand aesthetic.', 'label-narrative' ),
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'product_story_content', array(
        'label'   => __( 'Product Story Content', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'textarea',
    ) );

    // Add to Cart Text
    $wp_customize->add_setting( 'add_to_cart_text', array(
        'default'           => __( 'Get Your Labels Now', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'add_to_cart_text', array(
        'label'   => __( 'Add to Cart Button Text', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    // Trust Badges
    $wp_customize->add_setting( 'product_trust_1', array(
        'default'           => __( 'Secure Payment', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'product_trust_1', array(
        'label'   => __( 'Product Trust Badge 1', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'product_trust_2', array(
        'default'           => __( 'Fast Delivery', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'product_trust_2', array(
        'label'   => __( 'Product Trust Badge 2', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'product_trust_3', array(
        'default'           => __( 'Quality Guaranteed', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'product_trust_3', array(
        'label'   => __( 'Product Trust Badge 3', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    // Specifications
    $wp_customize->add_setting( 'product_specs_headline', array(
        'default'           => __( 'Product Specifications', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'product_specs_headline', array(
        'label'   => __( 'Specifications Headline', 'label-narrative' ),
        'section' => 'label_narrative_product',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'product_specs', array(
        'default'           => "Size: 9×9cm (90mm × 90mm)\nShapes: Square or Circle\nMaterial: Premium paper stock\nFinish: Glossy or Matte\nAdhesive: Permanent, strong-stick\nPrinting: Full-colour, high-resolution\nMinimum Order: From 50 labels\nDelivery: Australia-wide",
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'product_specs', array(
        'label'       => __( 'Product Specifications', 'label-narrative' ),
        'section'     => 'label_narrative_product',
        'type'        => 'textarea',
        'description' => __( 'One specification per line', 'label-narrative' ),
    ) );
}
add_action( 'customize_register', 'label_narrative_product_customizer' );

/**
 * Display product specifications
 */
function label_narrative_display_specs() {
    $specs_headline = get_theme_mod( 'product_specs_headline', __( 'Product Specifications', 'label-narrative' ) );
    $specs = get_theme_mod( 'product_specs', '' );

    if ( empty( $specs ) ) {
        return;
    }

    $specs_array = explode( "\n", $specs );

    ?>
    <div class="product-specifications">
        <h3><?php echo esc_html( $specs_headline ); ?></h3>
        <ul class="specs-list">
            <?php foreach ( $specs_array as $spec ) : ?>
                <?php if ( ! empty( trim( $spec ) ) ) : ?>
                    <li><?php echo esc_html( trim( $spec ) ); ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
add_action( 'woocommerce_after_single_product_summary', 'label_narrative_display_specs', 15 );

/**
 * Display product story on single product page
 */
function label_narrative_display_product_story() {
    if ( ! get_theme_mod( 'product_story_enable', true ) ) {
        return;
    }

    $headline = get_theme_mod( 'product_story_headline', __( 'Crafted for Your Success', 'label-narrative' ) );
    $content = get_theme_mod( 'product_story_content', '' );

    if ( empty( $content ) ) {
        return;
    }

    ?>
    <div class="product-story-section">
        <div class="container">
            <h2><?php echo esc_html( $headline ); ?></h2>
            <div class="product-story-content">
                <?php echo wp_kses_post( wpautop( $content ) ); ?>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_after_single_product_summary', 'label_narrative_display_product_story', 20 );
