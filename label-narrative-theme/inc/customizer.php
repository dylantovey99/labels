<?php
/**
 * Theme Customizer
 * All content is configurable via the WordPress Customizer
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add postMessage support for site title and description
 */
function label_narrative_customize_register( $wp_customize ) {
    $wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->add_partial( 'blogname', array(
            'selector'        => '.site-title a',
            'render_callback' => 'label_narrative_customize_partial_blogname',
        ) );
        $wp_customize->selective_refresh->add_partial( 'blogdescription', array(
            'selector'        => '.site-description',
            'render_callback' => 'label_narrative_customize_partial_blogdescription',
        ) );
    }

    // ============================================
    // HERO SECTION
    // ============================================
    $wp_customize->add_section( 'label_narrative_hero', array(
        'title'    => __( 'Hero Section', 'label-narrative' ),
        'priority' => 30,
    ) );

    // Hero Headline
    $wp_customize->add_setting( 'hero_headline', array(
        'default'           => __( 'Premium Labels. Instant Quote. Zero Fuss.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_headline', array(
        'label'   => __( 'Hero Headline', 'label-narrative' ),
        'section' => 'label_narrative_hero',
        'type'    => 'text',
    ) );

    // Hero Subheadline
    $wp_customize->add_setting( 'hero_subheadline', array(
        'default'           => __( 'Professional 9×9cm labels for Australian small businesses. See your price instantly—no email, no waiting.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_subheadline', array(
        'label'   => __( 'Hero Subheadline', 'label-narrative' ),
        'section' => 'label_narrative_hero',
        'type'    => 'textarea',
    ) );

    // Hero CTA Text
    $wp_customize->add_setting( 'hero_cta_text', array(
        'default'           => __( 'Get Your Custom Labels', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'hero_cta_text', array(
        'label'   => __( 'Hero CTA Button Text', 'label-narrative' ),
        'section' => 'label_narrative_hero',
        'type'    => 'text',
    ) );

    // Hero CTA Link
    $wp_customize->add_setting( 'hero_cta_link', array(
        'default'           => '#product',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'hero_cta_link', array(
        'label'   => __( 'Hero CTA Button Link', 'label-narrative' ),
        'section' => 'label_narrative_hero',
        'type'    => 'url',
    ) );

    // Hero Background Image
    $wp_customize->add_setting( 'hero_background_image', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hero_background_image', array(
        'label'     => __( 'Hero Background Image', 'label-narrative' ),
        'section'   => 'label_narrative_hero',
        'mime_type' => 'image',
    ) ) );

    // ============================================
    // STORY SECTION
    // ============================================
    $wp_customize->add_section( 'label_narrative_story', array(
        'title'    => __( 'Your Story Section', 'label-narrative' ),
        'priority' => 31,
    ) );

    // Story Section Enable
    $wp_customize->add_setting( 'story_enable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'story_enable', array(
        'label'   => __( 'Enable Story Section', 'label-narrative' ),
        'section' => 'label_narrative_story',
        'type'    => 'checkbox',
    ) );

    // Story Headline
    $wp_customize->add_setting( 'story_headline', array(
        'default'           => __( 'Your Product Is Amazing. Your Labels Should Be Too.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'story_headline', array(
        'label'   => __( 'Story Headline', 'label-narrative' ),
        'section' => 'label_narrative_story',
        'type'    => 'text',
    ) );

    // Story Content
    $wp_customize->add_setting( 'story_content', array(
        'default'           => __( 'You\'ve spent countless hours perfecting your product. You source the best ingredients. You test and refine. You pour your passion into every single item that leaves your hands.

But here\'s the harsh truth: in a crowded farmers market, online marketplace, or retail shelf, customers judge your product in 3 seconds or less. And that judgment starts with your label.

A cheap, unclear, or amateurish label tells customers your product is cheap and amateurish—no matter how incredible it actually is.

That\'s where Label Up changes everything. Our professional 9×9cm labels give your handcrafted products the premium presentation they deserve. Vibrant colours. Razor-sharp printing. Durable materials that won\'t peel, fade, or let you down.

From artisan food producers to cosmetics creators, from craft brewers to specialty retailers—Australian small businesses trust us to make their products look as good as they taste, smell, and feel.

Small batches? No problem. We specialize in short-run printing starting from just 50 labels. Get exactly what you need, when you need it, without breaking the bank on massive minimum orders.', 'label-narrative' ),
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'story_content', array(
        'label'   => __( 'Story Content', 'label-narrative' ),
        'section' => 'label_narrative_story',
        'type'    => 'textarea',
    ) );

    // Story Image
    $wp_customize->add_setting( 'story_image', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'story_image', array(
        'label'     => __( 'Story Image', 'label-narrative' ),
        'section'   => 'label_narrative_story',
        'mime_type' => 'image',
    ) ) );

    // ============================================
    // BENEFITS SECTION
    // ============================================
    $wp_customize->add_section( 'label_narrative_benefits', array(
        'title'    => __( 'Benefits Section', 'label-narrative' ),
        'priority' => 32,
    ) );

    // Benefits Section Enable
    $wp_customize->add_setting( 'benefits_enable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'benefits_enable', array(
        'label'   => __( 'Enable Benefits Section', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'checkbox',
    ) );

    // Benefits Headline
    $wp_customize->add_setting( 'benefits_headline', array(
        'default'           => __( 'Why Australian Small Businesses Choose Label Up', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefits_headline', array(
        'label'   => __( 'Benefits Headline', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    // Benefit 1
    $wp_customize->add_setting( 'benefit_1_icon', array(
        'default'           => '🏭',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_1_icon', array(
        'label'       => __( 'Benefit 1 Icon (emoji or class)', 'label-narrative' ),
        'section'     => 'label_narrative_benefits',
        'type'        => 'text',
        'description' => __( 'Enter an emoji or CSS class', 'label-narrative' ),
    ) );

    $wp_customize->add_setting( 'benefit_1_title', array(
        'default'           => __( 'Make Your Brand Unforgettable', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_1_title', array(
        'label'   => __( 'Benefit 1 Title', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_1_description', array(
        'default'           => __( 'Your label is your silent brand ambassador. Professional printing with vibrant, fade-resistant colours creates the emotional connection that turns one-time buyers into lifelong customers.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'benefit_1_description', array(
        'label'   => __( 'Benefit 1 Description', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'textarea',
    ) );

    // Benefit 2
    $wp_customize->add_setting( 'benefit_2_icon', array(
        'default'           => '📦',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_2_icon', array(
        'label'   => __( 'Benefit 2 Icon (emoji or class)', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_2_title', array(
        'default'           => __( 'Small Batches, Zero Waste', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_2_title', array(
        'label'   => __( 'Benefit 2 Title', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_2_description', array(
        'default'           => __( 'Order from just 50 labels. Test new products, try seasonal flavours, or adapt to customer feedback without being stuck with thousands of labels you can\'t use. Perfect for the agile Australian small business.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'benefit_2_description', array(
        'label'   => __( 'Benefit 2 Description', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'textarea',
    ) );

    // Benefit 3
    $wp_customize->add_setting( 'benefit_3_icon', array(
        'default'           => '⚡',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_3_icon', array(
        'label'   => __( 'Benefit 3 Icon (emoji or class)', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_3_title', array(
        'default'           => __( 'Ready When You Need Them', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_3_title', array(
        'label'   => __( 'Benefit 3 Title', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_3_description', array(
        'default'           => __( '5-7 business day turnaround Australia-wide. From order to doorstep in less than a week, so you can launch that new product line, restock before the market, or seize that retail opportunity without the wait.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'benefit_3_description', array(
        'label'   => __( 'Benefit 3 Description', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'textarea',
    ) );

    // Benefit 4
    $wp_customize->add_setting( 'benefit_4_icon', array(
        'default'           => '✨',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_4_icon', array(
        'label'   => __( 'Benefit 4 Icon (emoji or class)', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_4_title', array(
        'default'           => __( 'Proudly Australian. Seriously Reliable.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'benefit_4_title', array(
        'label'   => __( 'Benefit 4 Title', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'benefit_4_description', array(
        'default'           => __( 'Printed right here in Australia with premium materials and digital printing technology. Support local, reduce your carbon footprint, and get labels that won\'t peel, fade, or fail when it matters most.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'benefit_4_description', array(
        'label'   => __( 'Benefit 4 Description', 'label-narrative' ),
        'section' => 'label_narrative_benefits',
        'type'    => 'textarea',
    ) );

    // ============================================
    // TRUST SECTION
    // ============================================
    $wp_customize->add_section( 'label_narrative_trust', array(
        'title'    => __( 'Trust & Social Proof', 'label-narrative' ),
        'priority' => 33,
    ) );

    // Trust Section Enable
    $wp_customize->add_setting( 'trust_enable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'trust_enable', array(
        'label'   => __( 'Enable Trust Section', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'checkbox',
    ) );

    // Trust Headline
    $wp_customize->add_setting( 'trust_headline', array(
        'default'           => __( 'Trusted by Australian Small Businesses', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'trust_headline', array(
        'label'   => __( 'Trust Headline', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'text',
    ) );

    // Trust Badge 1
    $wp_customize->add_setting( 'trust_badge_1_text', array(
        'default'           => __( 'Australian Owned & Operated', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'trust_badge_1_text', array(
        'label'   => __( 'Trust Badge 1 Text', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'text',
    ) );

    // Trust Badge 2
    $wp_customize->add_setting( 'trust_badge_2_text', array(
        'default'           => __( '5-7 Day Delivery Nationwide', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'trust_badge_2_text', array(
        'label'   => __( 'Trust Badge 2 Text', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'text',
    ) );

    // Trust Badge 3
    $wp_customize->add_setting( 'trust_badge_3_text', array(
        'default'           => __( 'Minimum Order: Just 50 Labels', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'trust_badge_3_text', array(
        'label'   => __( 'Trust Badge 3 Text', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'text',
    ) );

    // Trust Badge 4
    $wp_customize->add_setting( 'trust_badge_4_text', array(
        'default'           => __( 'Premium Quality Guaranteed', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'trust_badge_4_text', array(
        'label'   => __( 'Trust Badge 4 Text', 'label-narrative' ),
        'section' => 'label_narrative_trust',
        'type'    => 'text',
    ) );

    // ============================================
    // CTA SECTION
    // ============================================
    $wp_customize->add_section( 'label_narrative_cta', array(
        'title'    => __( 'Call to Action Section', 'label-narrative' ),
        'priority' => 34,
    ) );

    // CTA Section Enable
    $wp_customize->add_setting( 'cta_enable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'cta_enable', array(
        'label'   => __( 'Enable CTA Section', 'label-narrative' ),
        'section' => 'label_narrative_cta',
        'type'    => 'checkbox',
    ) );

    // CTA Headline
    $wp_customize->add_setting( 'cta_headline', array(
        'default'           => __( 'Your Products Deserve Professional Labels', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'cta_headline', array(
        'label'   => __( 'CTA Headline', 'label-narrative' ),
        'section' => 'label_narrative_cta',
        'type'    => 'text',
    ) );

    // CTA Subheadline
    $wp_customize->add_setting( 'cta_subheadline', array(
        'default'           => __( 'From 50 labels to 5,000, we make it easy to get premium 9×9cm labels delivered to your door in 5-7 days. Square or circle. Glossy or matte. Made in Australia for Australian businesses.', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'cta_subheadline', array(
        'label'   => __( 'CTA Subheadline', 'label-narrative' ),
        'section' => 'label_narrative_cta',
        'type'    => 'textarea',
    ) );

    // CTA Button Text
    $wp_customize->add_setting( 'cta_button_text', array(
        'default'           => __( 'Order Your Labels Today', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'cta_button_text', array(
        'label'   => __( 'CTA Button Text', 'label-narrative' ),
        'section' => 'label_narrative_cta',
        'type'    => 'text',
    ) );

    // CTA Button Link
    $wp_customize->add_setting( 'cta_button_link', array(
        'default'           => '#product',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'cta_button_link', array(
        'label'   => __( 'CTA Button Link', 'label-narrative' ),
        'section' => 'label_narrative_cta',
        'type'    => 'url',
    ) );

    // CTA Background Color
    $wp_customize->add_setting( 'cta_bg_color', array(
        'default'           => '#f8f9fa',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cta_bg_color', array(
        'label'   => __( 'CTA Background Color', 'label-narrative' ),
        'section' => 'label_narrative_cta',
    ) ) );

    // ============================================
    // COLORS
    // ============================================
    $wp_customize->add_section( 'label_narrative_colors', array(
        'title'    => __( 'Theme Colors', 'label-narrative' ),
        'priority' => 40,
    ) );

    // Primary Color
    $wp_customize->add_setting( 'primary_color', array(
        'default'           => '#2563eb',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary_color', array(
        'label'   => __( 'Primary Color', 'label-narrative' ),
        'section' => 'label_narrative_colors',
    ) ) );

    // Secondary Color
    $wp_customize->add_setting( 'secondary_color', array(
        'default'           => '#10b981',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'secondary_color', array(
        'label'   => __( 'Secondary Color', 'label-narrative' ),
        'section' => 'label_narrative_colors',
    ) ) );

    // Text Color
    $wp_customize->add_setting( 'text_color', array(
        'default'           => '#1f2937',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'text_color', array(
        'label'   => __( 'Text Color', 'label-narrative' ),
        'section' => 'label_narrative_colors',
    ) ) );

    // ============================================
    // FOOTER
    // ============================================
    $wp_customize->add_section( 'label_narrative_footer', array(
        'title'    => __( 'Footer Settings', 'label-narrative' ),
        'priority' => 50,
    ) );

    // Footer Copyright Text
    $wp_customize->add_setting( 'footer_copyright', array(
        'default'           => __( '© 2025 Your Label Business. All rights reserved.', 'label-narrative' ),
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'footer_copyright', array(
        'label'   => __( 'Footer Copyright Text', 'label-narrative' ),
        'section' => 'label_narrative_footer',
        'type'    => 'textarea',
    ) );

    // Footer Contact Info
    $wp_customize->add_setting( 'footer_contact', array(
        'default'           => __( 'Email: hello@yourlabels.com.au | Phone: 1300 XXX XXX', 'label-narrative' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'footer_contact', array(
        'label'   => __( 'Footer Contact Info', 'label-narrative' ),
        'section' => 'label_narrative_footer',
        'type'    => 'text',
    ) );
}
add_action( 'customize_register', 'label_narrative_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 */
function label_narrative_customize_partial_blogname() {
    bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 */
function label_narrative_customize_partial_blogdescription() {
    bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function label_narrative_customize_preview_js() {
    wp_enqueue_script( 'label-narrative-customizer', get_template_directory_uri() . '/assets/js/customizer.js', array( 'customize-preview' ), LABEL_NARRATIVE_VERSION, true );
}
add_action( 'customize_preview_init', 'label_narrative_customize_preview_js' );
