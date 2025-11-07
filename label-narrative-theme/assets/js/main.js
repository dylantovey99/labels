/**
 * Label Narrative Theme - Main JavaScript
 * Handles mobile menu, smooth scrolling, and conversion optimizations
 */

(function() {
    'use strict';

    // Mobile Menu Toggle
    function initMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navigation = document.querySelector('.main-navigation');

        if (menuToggle && navigation) {
            menuToggle.addEventListener('click', function() {
                const expanded = this.getAttribute('aria-expanded') === 'true' || false;
                this.setAttribute('aria-expanded', !expanded);
                navigation.classList.toggle('toggled');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInside = navigation.contains(event.target) || menuToggle.contains(event.target);
                if (!isClickInside && navigation.classList.contains('toggled')) {
                    navigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Close menu on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && navigation.classList.contains('toggled')) {
                    navigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    }

    // Smooth Scrolling for Anchor Links
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');

        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Skip if it's just '#'
                if (href === '#') return;

                const target = document.querySelector(href);

                if (target) {
                    e.preventDefault();

                    const headerOffset = 80; // Account for sticky header
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    const navigation = document.querySelector('.main-navigation');
                    const menuToggle = document.querySelector('.menu-toggle');
                    if (navigation && navigation.classList.contains('toggled')) {
                        navigation.classList.remove('toggled');
                        if (menuToggle) {
                            menuToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });
        });
    }

    // Add to Cart Animation
    function initAddToCartAnimation() {
        const addToCartButtons = document.querySelectorAll('.single_add_to_cart_button');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Add loading state
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    const originalText = this.textContent;
                    this.textContent = 'Adding...';

                    // Reset after 2 seconds
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.textContent = originalText;
                    }, 2000);
                }
            });
        });
    }

    // Sticky Header on Scroll
    function initStickyHeader() {
        const header = document.querySelector('.site-header');
        let lastScroll = 0;

        if (!header) return;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;

            // Add shadow when scrolled
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    }

    // Product Image Gallery Enhancement
    function initProductGallery() {
        const productImages = document.querySelectorAll('.woocommerce-product-gallery__image');

        productImages.forEach(image => {
            image.addEventListener('click', function(e) {
                // Prevent default if you want custom lightbox behavior
                // e.preventDefault();
            });
        });
    }

    // Cart Count Animation
    function updateCartCount() {
        const cartCount = document.querySelector('.cart-count');

        if (cartCount) {
            // Trigger animation when cart count changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        cartCount.style.animation = 'none';
                        setTimeout(() => {
                            cartCount.style.animation = 'cartBounce 0.5s ease';
                        }, 10);
                    }
                });
            });

            observer.observe(cartCount, {
                childList: true,
                characterData: true
            });
        }
    }

    // Scroll Reveal Animation (for elements coming into view)
    function initScrollReveal() {
        const reveals = document.querySelectorAll('.benefit-item, .review-item, .trust-badge-item');

        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    threshold: 0.15
                }
            );

            reveals.forEach(reveal => {
                reveal.style.opacity = '0';
                reveal.style.transform = 'translateY(20px)';
                reveal.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

                revealObserver.observe(reveal);

                // Add revealed class handler
                reveal.addEventListener('transitionend', function() {
                    if (this.classList.contains('revealed')) {
                        this.style.opacity = '1';
                        this.style.transform = 'translateY(0)';
                    }
                });
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            reveals.forEach(reveal => {
                reveal.style.opacity = '1';
                reveal.style.transform = 'translateY(0)';
            });
        }
    }

    // Detect when user is about to leave (exit intent)
    function initExitIntent() {
        let hasShownExitIntent = false;

        document.addEventListener('mouseleave', function(e) {
            // Only trigger on desktop and if cursor is moving towards top of page
            if (e.clientY <= 0 && !hasShownExitIntent && window.innerWidth > 768) {
                hasShownExitIntent = true;

                // You can trigger a popup or special offer here
                // For now, we'll just log it
                console.log('User showing exit intent');

                // Example: Show a discount popup
                // showExitIntentPopup();
            }
        });
    }

    // Add CSS animation for cart bounce
    function addCartAnimation() {
        if (!document.querySelector('#cart-bounce-animation')) {
            const style = document.createElement('style');
            style.id = 'cart-bounce-animation';
            style.textContent = `
                @keyframes cartBounce {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.2); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Apply theme colors from customizer
    function applyCustomizerColors() {
        // This would be populated by wp_localize_script in a real implementation
        // For now, we'll use CSS variables which are already set
    }

    // Performance: Lazy load images
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
            document.body.appendChild(script);
        }
    }

    // Initialize all functions when DOM is ready
    function init() {
        initMobileMenu();
        initSmoothScroll();
        initAddToCartAnimation();
        initStickyHeader();
        initProductGallery();
        updateCartCount();
        initScrollReveal();
        initExitIntent();
        addCartAnimation();
        applyCustomizerColors();
        initLazyLoading();

        console.log('Label Narrative Theme initialized');
    }

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
