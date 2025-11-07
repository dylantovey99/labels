/**
 * Label Narrative Theme - Main JavaScript
 * Modern, sophisticated interactions for 2025 e-commerce
 */

(function() {
    'use strict';

    // ============================================
    // 1. MOBILE MENU
    // ============================================
    function initMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navigation = document.querySelector('.main-navigation');

        if (menuToggle && navigation) {
            menuToggle.addEventListener('click', function() {
                const expanded = this.getAttribute('aria-expanded') === 'true' || false;
                this.setAttribute('aria-expanded', !expanded);
                navigation.classList.toggle('toggled');
            });

            // Close on outside click
            document.addEventListener('click', function(event) {
                const isClickInside = navigation.contains(event.target) || menuToggle.contains(event.target);
                if (!isClickInside && navigation.classList.contains('toggled')) {
                    navigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Close on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && navigation.classList.contains('toggled')) {
                    navigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    }

    // ============================================
    // 2. SMOOTH SCROLLING
    // ============================================
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');

        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 80;
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
                        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    }

    // ============================================
    // 3. STICKY HEADER WITH SCROLL EFFECTS
    // ============================================
    function initStickyHeader() {
        const header = document.querySelector('.site-header');
        if (!header) return;

        let lastScroll = 0;
        let ticking = false;

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    const currentScroll = window.pageYOffset;

                    // Add scrolled class for shadow
                    if (currentScroll > 50) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }

                    lastScroll = currentScroll;
                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    // ============================================
    // 4. INTERSECTION OBSERVER FOR SCROLL REVEALS
    // ============================================
    function initScrollReveal() {
        if (!('IntersectionObserver' in window)) {
            // Fallback for older browsers
            return;
        }

        const revealElements = document.querySelectorAll('.benefit-item, .review-item, .story-section, .product-section');

        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            }
        );

        revealElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            revealObserver.observe(element);
        });
    }

    // ============================================
    // 5. ADD TO CART ANIMATION
    // ============================================
    function initAddToCartAnimation() {
        const addToCartButtons = document.querySelectorAll('.single_add_to_cart_button');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Only add visual feedback, don't prevent default
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    const originalText = this.textContent;
                    this.textContent = 'Adding to cart...';

                    // Ripple effect
                    createRipple(e, this);

                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.textContent = originalText;
                    }, 2000);
                }
            });
        });
    }

    // ============================================
    // 6. RIPPLE EFFECT ON BUTTONS
    // ============================================
    function createRipple(event, button) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    function initButtonRipples() {
        const buttons = document.querySelectorAll('.btn, button.button');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Only create ripple if button doesn't already have one
                if (!this.querySelector('.ripple')) {
                    createRipple(e, this);
                }
            });
        });

        // Add ripple styles dynamically
        if (!document.querySelector('#ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple-animation 0.6s ease-out;
                    pointer-events: none;
                }
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // ============================================
    // 7. CART COUNT ANIMATION
    // ============================================
    function initCartCountAnimation() {
        const cartCount = document.querySelector('.cart-count');

        if (cartCount) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        cartCount.style.transform = 'scale(1.3)';
                        setTimeout(() => {
                            cartCount.style.transform = 'scale(1)';
                        }, 200);
                    }
                });
            });

            observer.observe(cartCount, {
                childList: true,
                characterData: true,
                subtree: true
            });

            // Add transition style
            cartCount.style.transition = 'transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1)';
        }
    }

    // ============================================
    // 8. PARALLAX EFFECT ON HERO
    // ============================================
    function initParallax() {
        const hero = document.querySelector('.hero-section');
        if (!hero) return;

        let ticking = false;

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    const scrolled = window.pageYOffset;
                    const parallaxSpeed = 0.5;

                    if (scrolled < hero.offsetHeight) {
                        hero.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
                    }

                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    // ============================================
    // 9. IMAGE LAZY LOADING
    // ============================================
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
            });
        } else {
            // Intersection Observer fallback
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.classList.add('loaded');
                                observer.unobserve(img);
                            }
                        }
                    });
                });

                const lazyImages = document.querySelectorAll('img[data-src]');
                lazyImages.forEach(img => imageObserver.observe(img));
            }
        }
    }

    // ============================================
    // 10. PRODUCT GALLERY ENHANCEMENTS
    // ============================================
    function initProductGallery() {
        const galleryImages = document.querySelectorAll('.woocommerce-product-gallery__image');

        galleryImages.forEach(image => {
            image.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });

            image.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }

    // ============================================
    // 11. FORM FOCUS ENHANCEMENTS
    // ============================================
    function initFormEnhancements() {
        const inputs = document.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    }

    // ============================================
    // 12. SCROLL PROGRESS INDICATOR
    // ============================================
    function initScrollProgress() {
        // Only on product pages or long pages
        if (document.body.classList.contains('single-product')) {
            const progressBar = document.createElement('div');
            progressBar.className = 'scroll-progress';
            progressBar.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 0%;
                height: 3px;
                background: linear-gradient(90deg, #0891b2, #db2777);
                z-index: 9999;
                transition: width 0.1s ease-out;
            `;
            document.body.appendChild(progressBar);

            let ticking = false;

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        const scrolled = (window.scrollY / windowHeight) * 100;
                        progressBar.style.width = scrolled + '%';
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        }
    }

    // ============================================
    // 13. PERFORMANCE: DEBOUNCE UTILITY
    // ============================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ============================================
    // 14. ACCESSIBILITY: FOCUS VISIBLE
    // ============================================
    function initFocusVisible() {
        let hadKeyboardEvent = true;
        let hadFocusVisibleRecently = false;
        let hadFocusVisibleRecentlyTimeout;

        const inputTypesWhitelist = {
            text: true,
            search: true,
            url: true,
            tel: true,
            email: true,
            password: true,
            number: true,
            date: true,
            month: true,
            week: true,
            time: true,
            datetime: true,
            'datetime-local': true
        };

        function onKeyDown(e) {
            if (e.metaKey || e.altKey || e.ctrlKey) {
                return;
            }
            hadKeyboardEvent = true;
        }

        function onPointerDown() {
            hadKeyboardEvent = false;
        }

        function onFocus(e) {
            if (e.target.classList.contains('focus-visible')) {
                return;
            }

            if (hadKeyboardEvent || focusTriggersKeyboardModality(e.target)) {
                e.target.classList.add('focus-visible');
                hadFocusVisibleRecently = true;
                clearTimeout(hadFocusVisibleRecentlyTimeout);
                hadFocusVisibleRecentlyTimeout = setTimeout(() => {
                    hadFocusVisibleRecently = false;
                }, 100);
            }
        }

        function onBlur(e) {
            if (e.target.classList.contains('focus-visible')) {
                e.target.classList.remove('focus-visible');
            }
        }

        function focusTriggersKeyboardModality(el) {
            const type = el.type;
            const tagName = el.tagName;

            if (tagName === 'INPUT' && inputTypesWhitelist[type] && !el.readOnly) {
                return true;
            }

            if (tagName === 'TEXTAREA' && !el.readOnly) {
                return true;
            }

            if (el.isContentEditable) {
                return true;
            }

            return false;
        }

        document.addEventListener('keydown', onKeyDown, true);
        document.addEventListener('mousedown', onPointerDown, true);
        document.addEventListener('pointerdown', onPointerDown, true);
        document.addEventListener('touchstart', onPointerDown, true);
        document.addEventListener('focus', onFocus, true);
        document.addEventListener('blur', onBlur, true);

        // Add CSS for focus-visible
        if (!document.querySelector('#focus-visible-styles')) {
            const style = document.createElement('style');
            style.id = 'focus-visible-styles';
            style.textContent = `
                .focus-visible {
                    outline: 2px solid #0891b2;
                    outline-offset: 2px;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // ============================================
    // INITIALIZE ALL FUNCTIONS
    // ============================================
    function init() {
        initMobileMenu();
        initSmoothScroll();
        initStickyHeader();
        initScrollReveal();
        initAddToCartAnimation();
        initButtonRipples();
        initCartCountAnimation();
        initParallax();
        initLazyLoading();
        initProductGallery();
        initFormEnhancements();
        initScrollProgress();
        initFocusVisible();

        console.log('Label Narrative Theme initialized - Premium 2025 Design');
    }

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose debounce utility globally
    window.labelNarrativeDebounce = debounce;

})();
