<?php
/**
 * The header template
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'label-narrative' ); ?></a>

    <header id="masthead" class="site-header">
        <div class="header-container">
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    ?>
                    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description || is_customize_preview() ) :
                        ?>
                        <p class="site-description"><?php echo $description; ?></p>
                    <?php endif; ?>
                    <?php
                }
                ?>
            </div>

            <nav id="site-navigation" class="main-navigation">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="menu-toggle-icon"></span>
                    <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'label-narrative' ); ?></span>
                </button>
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'fallback_cb'    => false,
                ) );
                ?>
            </nav>

            <?php if ( function_exists( 'WC' ) ) : ?>
                <div class="header-cart">
                    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-icon">
                        <span class="cart-icon-svg">🛒</span>
                        <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
                            <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
