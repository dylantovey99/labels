<?php
/**
 * Label Narrative Theme Functions
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define theme version
define( 'LABEL_NARRATIVE_VERSION', '1.0.0' );

/**
 * Theme Setup
 */
function label_narrative_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'label-narrative' ),
        'footer'  => __( 'Footer Menu', 'label-narrative' ),
    ) );

    // Switch default core markup to output valid HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Add theme support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for responsive embedded content
    add_theme_support( 'responsive-embeds' );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );

    // WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'label_narrative_setup' );

/**
 * Set the content width in pixels
 */
function label_narrative_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'label_narrative_content_width', 1200 );
}
add_action( 'after_setup_theme', 'label_narrative_content_width', 0 );

/**
 * Enqueue scripts and styles
 */
function label_narrative_scripts() {
    // Main stylesheet
    wp_enqueue_style( 'label-narrative-style', get_stylesheet_uri(), array(), LABEL_NARRATIVE_VERSION );

    // Main CSS
    wp_enqueue_style( 'label-narrative-main', get_template_directory_uri() . '/assets/css/main.css', array(), LABEL_NARRATIVE_VERSION );

    // Main JavaScript
    wp_enqueue_script( 'label-narrative-main', get_template_directory_uri() . '/assets/js/main.js', array(), LABEL_NARRATIVE_VERSION, true );

    // Localize script for AJAX
    wp_localize_script( 'label-narrative-main', 'labelNarrative', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'label-narrative-nonce' ),
    ) );

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'label_narrative_scripts' );

/**
 * Register widget areas
 */
function label_narrative_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Footer Area 1', 'label-narrative' ),
        'id'            => 'footer-1',
        'description'   => __( 'Add widgets here to appear in your footer.', 'label-narrative' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer Area 2', 'label-narrative' ),
        'id'            => 'footer-2',
        'description'   => __( 'Add widgets here to appear in your footer.', 'label-narrative' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer Area 3', 'label-narrative' ),
        'id'            => 'footer-3',
        'description'   => __( 'Add widgets here to appear in your footer.', 'label-narrative' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'label_narrative_widgets_init' );

/**
 * Include additional files
 */
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/woocommerce-customizations.php';

/**
 * Helper function to get customizer option
 */
function label_narrative_get_option( $option_name, $default = '' ) {
    return get_theme_mod( $option_name, $default );
}
