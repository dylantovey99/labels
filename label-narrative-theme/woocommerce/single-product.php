<?php
/**
 * The Template for displaying single products
 *
 * Conversion-optimized product page with narrative elements
 *
 * @package Label_Narrative
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div id="primary" class="content-area product-page">
    <main id="main" class="site-main">

        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>

            <div class="product-hero">
                <div class="container">
                    <?php wc_get_template_part( 'content', 'single-product' ); ?>
                </div>
            </div>

        <?php endwhile; ?>

    </main>
</div>

<?php
get_footer();

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
