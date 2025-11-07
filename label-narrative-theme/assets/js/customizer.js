/**
 * Theme Customizer Live Preview
 * Updates the theme in real-time as you customize
 */

(function($) {
    'use strict';

    // Site title and description
    wp.customize('blogname', function(value) {
        value.bind(function(newval) {
            $('.site-title a').text(newval);
        });
    });

    wp.customize('blogdescription', function(value) {
        value.bind(function(newval) {
            $('.site-description').text(newval);
        });
    });

    // Hero Section
    wp.customize('hero_headline', function(value) {
        value.bind(function(newval) {
            $('.hero-headline').text(newval);
        });
    });

    wp.customize('hero_subheadline', function(value) {
        value.bind(function(newval) {
            $('.hero-subheadline').text(newval);
        });
    });

    wp.customize('hero_cta_text', function(value) {
        value.bind(function(newval) {
            $('.hero-cta .btn').text(newval);
        });
    });

    // Colors
    wp.customize('primary_color', function(value) {
        value.bind(function(newval) {
            $(':root').css('--primary-color', newval);
        });
    });

    wp.customize('secondary_color', function(value) {
        value.bind(function(newval) {
            $(':root').css('--secondary-color', newval);
        });
    });

    wp.customize('text_color', function(value) {
        value.bind(function(newval) {
            $(':root').css('--text-color', newval);
        });
    });

    wp.customize('cta_bg_color', function(value) {
        value.bind(function(newval) {
            $('.cta-section').css('background-color', newval);
        });
    });

})(jQuery);
