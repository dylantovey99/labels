<?php
/**
 * The footer template
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    <footer id="colophon" class="site-footer">
        <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
            <div class="footer-widgets">
                <div class="container">
                    <div class="footer-widget-area">
                        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                            <div class="footer-widget">
                                <?php dynamic_sidebar( 'footer-1' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                            <div class="footer-widget">
                                <?php dynamic_sidebar( 'footer-2' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                            <div class="footer-widget">
                                <?php dynamic_sidebar( 'footer-3' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-info">
                    <?php
                    $footer_contact = get_theme_mod( 'footer_contact', '' );
                    if ( $footer_contact ) :
                        ?>
                        <div class="footer-contact">
                            <?php echo esc_html( $footer_contact ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="footer-copyright">
                        <?php
                        $copyright = get_theme_mod( 'footer_copyright', sprintf( __( '© %d %s. All rights reserved.', 'label-narrative' ), date( 'Y' ), get_bloginfo( 'name' ) ) );
                        echo wp_kses_post( $copyright );
                        ?>
                    </div>

                    <?php
                    if ( has_nav_menu( 'footer' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'menu_id'        => 'footer-menu',
                            'container'      => 'nav',
                            'container_class' => 'footer-navigation',
                            'depth'          => 1,
                        ) );
                    }
                    ?>
                </div>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
