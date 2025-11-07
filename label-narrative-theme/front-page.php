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
        // HERO SECTION - The Hook
        // ============================================
        $hero_headline = get_theme_mod( 'hero_headline', __( 'Your Brand Deserves to Stand Out', 'label-narrative' ) );
        $hero_subheadline = get_theme_mod( 'hero_subheadline', __( 'Premium 9×9cm labels that tell your story and grow your business', 'label-narrative' ) );
        $hero_cta_text = get_theme_mod( 'hero_cta_text', __( 'Start Your Label Journey', 'label-narrative' ) );
        $hero_cta_link = get_theme_mod( 'hero_cta_link', '#product' );
        $hero_bg_image_id = get_theme_mod( 'hero_background_image', '' );
        $hero_bg_image = $hero_bg_image_id ? wp_get_attachment_image_url( $hero_bg_image_id, 'full' ) : '';
        ?>
        <section class="hero-section" <?php if ( $hero_bg_image ) echo 'style="background-image: url(' . esc_url( $hero_bg_image ) . ');"'; ?>>
            <div class="hero-overlay">
                <div class="container">
                    <div class="hero-content">
                        <h1 class="hero-headline"><?php echo esc_html( $hero_headline ); ?></h1>
                        <p class="hero-subheadline"><?php echo esc_html( $hero_subheadline ); ?></p>
                        <div class="hero-cta">
                            <a href="<?php echo esc_url( $hero_cta_link ); ?>" class="btn btn-primary btn-large">
                                <?php echo esc_html( $hero_cta_text ); ?>
                            </a>
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
