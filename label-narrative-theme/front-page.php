<?php
/**
 * Front Page Template - Narrative-Driven Homepage
 *
 * This template uses a storytelling approach to guide customers
 * through their label journey, making them the hero of the story.
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        // ============================================
        // HERO SECTION - Instant Quote Calculator
        // ============================================
        $hero_headline = get_theme_mod( 'hero_headline', __( 'Premium Labels. Instant Quote. Zero Fuss.', 'label-narrative' ) );
        $hero_subheadline = get_theme_mod( 'hero_subheadline', __( 'Professional 9×9cm labels for Australian small businesses. See your price instantly—no email, no waiting.', 'label-narrative' ) );
        $hero_bg_image_id = get_theme_mod( 'hero_background_image', '' );
        $hero_bg_image = $hero_bg_image_id ? wp_get_attachment_image_url( $hero_bg_image_id, 'full' ) : '';
        ?>
        <section class="hero-section hero-calculator-section" <?php if ( $hero_bg_image ) echo 'style="background-image: url(' . esc_url( $hero_bg_image ) . ');"'; ?>>
            <div class="hero-overlay">
                <div class="container">
                    <div class="hero-calculator-grid">
                        <!-- Left: Headline & Value Props -->
                        <div class="hero-content">
                            <h1 class="hero-headline"><?php echo esc_html( $hero_headline ); ?></h1>
                            <p class="hero-subheadline"><?php echo esc_html( $hero_subheadline ); ?></p>

                            <ul class="hero-trust-points">
                                <li><span class="trust-icon">✓</span> Aussie-made, professionally printed</li>
                                <li><span class="trust-icon">✓</span> From 50 labels (no huge minimum orders)</li>
                                <li><span class="trust-icon">✓</span> Delivered in 3-7 business days</li>
                                <li><span class="trust-icon">✓</span> Waterproof & scratch-resistant</li>
                            </ul>
                        </div>

                        <!-- Right: Instant Quote Calculator -->
                        <div class="quote-calculator-wrapper">
                            <div class="quote-calculator">
                                <div class="calculator-header">
                                    <h3>Get Your Instant Quote</h3>
                                    <p>Select your options below</p>
                                </div>

                                <!-- Quantity Selection -->
                                <div class="calculator-section">
                                    <label class="calculator-label">Quantity</label>
                                    <div class="option-grid quantity-grid">
                                        <button type="button" class="quantity-option" data-quantity="50">
                                            <span class="option-value">50</span>
                                            <span class="option-label">labels</span>
                                        </button>
                                        <button type="button" class="quantity-option" data-quantity="100">
                                            <span class="option-value">100</span>
                                            <span class="option-label">labels</span>
                                        </button>
                                        <button type="button" class="quantity-option active" data-quantity="250">
                                            <span class="option-value">250</span>
                                            <span class="option-label">labels</span>
                                            <span class="popular-badge">Popular</span>
                                        </button>
                                        <button type="button" class="quantity-option" data-quantity="500">
                                            <span class="option-value">500</span>
                                            <span class="option-label">labels</span>
                                        </button>
                                        <button type="button" class="quantity-option" data-quantity="1000">
                                            <span class="option-value">1000</span>
                                            <span class="option-label">labels</span>
                                            <span class="best-value-badge">Best Value</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Shape Selection -->
                                <div class="calculator-section">
                                    <label class="calculator-label">Shape</label>
                                    <div class="option-grid shape-grid">
                                        <button type="button" class="shape-option active" data-shape="square">
                                            <svg class="shape-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="5" y="5" width="30" height="30" rx="2"/>
                                            </svg>
                                            <span class="option-label">Square</span>
                                        </button>
                                        <button type="button" class="shape-option" data-shape="circle">
                                            <svg class="shape-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="20" cy="20" r="15"/>
                                            </svg>
                                            <span class="option-label">Circle</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Finish Selection -->
                                <div class="calculator-section">
                                    <label class="calculator-label">Finish</label>
                                    <div class="option-grid finish-grid">
                                        <button type="button" class="finish-option active" data-finish="glossy">
                                            <span class="finish-sample glossy-sample"></span>
                                            <span class="option-label">Glossy</span>
                                            <span class="option-description">Vibrant & shiny</span>
                                        </button>
                                        <button type="button" class="finish-option" data-finish="matte">
                                            <span class="finish-sample matte-sample"></span>
                                            <span class="option-label">Matte</span>
                                            <span class="option-description">Elegant & smooth</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Price Display -->
                                <div class="price-summary">
                                    <div class="price-row">
                                        <div class="price-main">
                                            <span class="price-label">Your Price</span>
                                            <span class="price-display">$875.00</span>
                                        </div>
                                        <div class="price-details">
                                            <span class="per-label-price">$3.50 each</span>
                                            <span class="delivery-time">3-5 business days</span>
                                        </div>
                                    </div>
                                    <div class="savings-row" style="display: none;">
                                        <span class="savings-label">💰 You save</span>
                                        <span class="savings-amount">$375.00</span>
                                    </div>
                                </div>

                                <!-- CTA Button -->
                                <div class="calculator-cta">
                                    <button type="button" class="btn btn-primary btn-large btn-block get-quote-btn">
                                        Get This Quote →
                                    </button>
                                    <p class="calculator-note">See full details & place order below</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        // ============================================
        // STORY SECTION - Make the Customer the Hero
        // ============================================
        if ( get_theme_mod( 'story_enable', true ) ) :
            $story_headline = get_theme_mod( 'story_headline', __( 'Every Great Business Has a Story', 'label-narrative' ) );
            $story_content = get_theme_mod( 'story_content', '' );
            $story_image_id = get_theme_mod( 'story_image', '' );
            $story_image = $story_image_id ? wp_get_attachment_image_url( $story_image_id, 'large' ) : '';
            ?>
            <section class="story-section">
                <div class="container">
                    <div class="story-grid">
                        <?php if ( $story_image ) : ?>
                            <div class="story-image">
                                <img src="<?php echo esc_url( $story_image ); ?>" alt="<?php echo esc_attr( $story_headline ); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="story-content">
                            <h2><?php echo esc_html( $story_headline ); ?></h2>
                            <div class="story-text">
                                <?php echo wp_kses_post( wpautop( $story_content ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // ============================================
        // BENEFITS SECTION - Show the Value
        // ============================================
        if ( get_theme_mod( 'benefits_enable', true ) ) :
            $benefits_headline = get_theme_mod( 'benefits_headline', __( 'Why Australian Small Businesses Choose Us', 'label-narrative' ) );
            ?>
            <section class="benefits-section">
                <div class="container">
                    <h2 class="section-headline"><?php echo esc_html( $benefits_headline ); ?></h2>
                    <div class="benefits-grid">
                        <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <?php echo esc_html( get_theme_mod( "benefit_{$i}_icon", '✨' ) ); ?>
                                </div>
                                <h3 class="benefit-title"><?php echo esc_html( get_theme_mod( "benefit_{$i}_title", '' ) ); ?></h3>
                                <p class="benefit-description"><?php echo esc_html( get_theme_mod( "benefit_{$i}_description", '' ) ); ?></p>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // ============================================
        // PRODUCT SECTION - The Solution
        // ============================================
        // Display WooCommerce products (typically one main product)
        if ( function_exists( 'woocommerce' ) ) :
            ?>
            <section id="product" class="product-section">
                <div class="container">
                    <h2 class="section-headline"><?php esc_html_e( 'Choose Your Perfect Labels', 'label-narrative' ); ?></h2>
                    <?php
                    // Display products - you can customize this query
                    echo do_shortcode( '[products limit="4" columns="4" orderby="popularity"]' );
                    ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // ============================================
        // TRUST SECTION - Social Proof
        // ============================================
        if ( get_theme_mod( 'trust_enable', true ) ) :
            $trust_headline = get_theme_mod( 'trust_headline', __( 'Trusted by Australian Small Businesses', 'label-narrative' ) );
            ?>
            <section class="trust-section">
                <div class="container">
                    <h2 class="section-headline"><?php echo esc_html( $trust_headline ); ?></h2>
                    <div class="trust-badges-grid">
                        <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                            <div class="trust-badge-item">
                                <span class="trust-badge-text"><?php echo esc_html( get_theme_mod( "trust_badge_{$i}_text", '' ) ); ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <?php
                    // Display product reviews if available
                    if ( function_exists( 'woocommerce' ) && comments_open() ) :
                        ?>
                        <div class="reviews-preview">
                            <?php
                            // Get recent product reviews
                            $args = array(
                                'status' => 'approve',
                                'post_type' => 'product',
                                'number' => 3,
                            );
                            $reviews = get_comments( $args );

                            if ( $reviews ) :
                                ?>
                                <div class="reviews-grid">
                                    <?php foreach ( $reviews as $review ) : ?>
                                        <div class="review-item">
                                            <div class="review-rating">
                                                <?php
                                                $rating = intval( get_comment_meta( $review->comment_ID, 'rating', true ) );
                                                for ( $j = 0; $j < 5; $j++ ) {
                                                    echo $j < $rating ? '⭐' : '☆';
                                                }
                                                ?>
                                            </div>
                                            <div class="review-content">
                                                <?php echo wp_kses_post( wpautop( $review->comment_content ) ); ?>
                                            </div>
                                            <div class="review-author">
                                                — <?php echo esc_html( $review->comment_author ); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // ============================================
        // CTA SECTION - The Call to Action
        // ============================================
        if ( get_theme_mod( 'cta_enable', true ) ) :
            $cta_headline = get_theme_mod( 'cta_headline', __( 'Ready to Transform Your Product Packaging?', 'label-narrative' ) );
            $cta_subheadline = get_theme_mod( 'cta_subheadline', __( 'Join hundreds of Australian small businesses who have elevated their brand with our premium labels.', 'label-narrative' ) );
            $cta_button_text = get_theme_mod( 'cta_button_text', __( 'Get Your Labels Now', 'label-narrative' ) );
            $cta_button_link = get_theme_mod( 'cta_button_link', '#product' );
            $cta_bg_color = get_theme_mod( 'cta_bg_color', '#f8f9fa' );
            ?>
            <section class="cta-section" style="background-color: <?php echo esc_attr( $cta_bg_color ); ?>;">
                <div class="container">
                    <div class="cta-content">
                        <h2 class="cta-headline"><?php echo esc_html( $cta_headline ); ?></h2>
                        <p class="cta-subheadline"><?php echo esc_html( $cta_subheadline ); ?></p>
                        <div class="cta-button-wrap">
                            <a href="<?php echo esc_url( $cta_button_link ); ?>" class="btn btn-primary btn-large">
                                <?php echo esc_html( $cta_button_text ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>

<?php
get_footer();
