/**
 * Cobra Theme JavaScript
 * Main script file for the Cobra theme
 * 
 * @package Cobra
 * @version 2.1
 */

// Wait for jQuery to be available
(function() {
    'use strict';
    
    function initCobraTheme() {
        if (typeof jQuery === 'undefined') {
            // If jQuery is not loaded yet, wait a bit and try again
            setTimeout(initCobraTheme, 50);
            return;
        }
        
        (function($) {

    // Theme Object
    const CobraTheme = {
        
        // Initialize all functions
        init: function() {
            // this.mobileMenu(); // Disabled - handled by accessibility.js
            this.scrollEffects();
            this.backToTop();
            this.smoothScrolling();
            this.lazyLoading();
            this.pricingToggle();
            this.formValidation();
            this.logoSlider();
            this.revealAnimations();
        },

        // Mobile menu functionality - DISABLED
        // Note: Mobile menu is now handled by accessibility.js to avoid conflicts
        mobileMenu: function() {
            // This function has been disabled to prevent conflicts with accessibility.js
            // All mobile menu functionality is now handled in accessibility.js
            console.log('Mobile menu handled by accessibility.js');
        },

        // Scroll effects for header
        scrollEffects: function() {
            const $header = $('.the-header');
            let lastScrollTop = 0;

            $(window).on('scroll', function() {
                const scrollTop = $(this).scrollTop();
                
                // Add scrolled class when scrolling down
                if (scrollTop > 100) {
                    $header.addClass('scrolled');
                } else {
                    $header.removeClass('scrolled');
                }

                // Hide/show header on scroll
                if (scrollTop > lastScrollTop && scrollTop > 200) {
                    $header.addClass('header-hidden');
                } else {
                    $header.removeClass('header-hidden');
                }
                
                lastScrollTop = scrollTop;
            });
        },

        // Back to top button
        backToTop: function() {
            const $backToTop = $('<button class="back-to-top" aria-label="Back to top"><i class="fas fa-chevron-up"></i></button>');
            $('body').append($backToTop);

            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 300) {
                    $backToTop.addClass('show');
                } else {
                    $backToTop.removeClass('show');
                }
            });

            $backToTop.on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 600);
            });
        },

        // Smooth scrolling for anchor links
        smoothScrolling: function() {
            $('a[href*="#"]:not([href="#"])').on('click', function() {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    let target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    
                    if (target.length) {
                        const offset = $('.the-header').height() || 0;
                        $('html, body').animate({
                            scrollTop: target.offset().top - offset - 20
                        }, 800);
                        return false;
                    }
                }
            });
        },

        // Lazy loading for images
        lazyLoading: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback for older browsers
                $('img[data-src]').each(function() {
                    $(this).attr('src', $(this).data('src'));
                });
            }
        },

        // Pricing toggle functionality
        pricingToggle: function() {
            $('.pricing-toggle').on('change', function() {
                const isAnnual = $(this).is(':checked');
                $('.pricing-card').each(function() {
                    const monthlyPrice = $(this).data('monthly');
                    const annualPrice = $(this).data('annual');
                    const $priceEl = $(this).find('.price-amount');
                    
                    if (isAnnual && annualPrice) {
                        $priceEl.text(annualPrice);
                    } else if (monthlyPrice) {
                        $priceEl.text(monthlyPrice);
                    }
                });
            });
        },

        // Form validation
        formValidation: function() {
            $('form').on('submit', function(e) {
                const $form = $(this);
                const $requiredFields = $form.find('[required]');
                let isValid = true;

                $requiredFields.each(function() {
                    const $field = $(this);
                    const value = $field.val().trim();
                    
                    $field.removeClass('error');
                    
                    if (!value) {
                        $field.addClass('error');
                        isValid = false;
                    }
                    
                    // Email validation
                    if ($field.attr('type') === 'email' && value) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            $field.addClass('error');
                            isValid = false;
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const $firstError = $form.find('.error').first();
                    $firstError.focus();
                }
            });

            // Real-time validation
            $('input, textarea').on('blur', function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if ($field.prop('required') && !value) {
                    $field.addClass('error');
                } else {
                    $field.removeClass('error');
                }
            });
        },

        // Logo slider animation
        logoSlider: function() {
            const $logoSlider = $('.logo-slider');
            if ($logoSlider.length) {
                $logoSlider.each(function() {
                    const $slider = $(this);
                    const $logos = $slider.find('.logo-item');
                    let currentIndex = 0;

                    if ($logos.length > 1) {
                        setInterval(function() {
                            $logos.removeClass('active');
                            $logos.eq(currentIndex).addClass('active');
                            currentIndex = (currentIndex + 1) % $logos.length;
                        }, 3000);
                    }
                });
            }
        },

        // Reveal animations on scroll
        revealAnimations: function() {
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            entry.target.classList.add('active'); // Support for .reveal elements
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });

                // Support both .reveal-on-scroll and .reveal classes
                document.querySelectorAll('.reveal-on-scroll, .reveal').forEach(el => {
                    revealObserver.observe(el);
                });
            } else {
                // Fallback for older browsers
                $(window).on('scroll', function() {
                    $('.reveal-on-scroll, .reveal').each(function() {
                        const elementTop = $(this).offset().top;
                        const elementBottom = elementTop + $(this).outerHeight();
                        const viewportTop = $(window).scrollTop();
                        const viewportBottom = viewportTop + $(window).height();

                        if (elementBottom > viewportTop && elementTop < viewportBottom) {
                            $(this).addClass('revealed active');
                        }
                    });
                });
            }
        },

        // Utility function for debouncing
        debounce: function(func, wait) {
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
    };

    // Initialize theme when document is ready
    $(document).ready(function() {
        CobraTheme.init();
    });

    // Handle window load events
    $(window).on('load', function() {
        // Remove loading class if present
        $('body').removeClass('loading');
        
        // Trigger reveal animations for immediate visibility
        $('.reveal-on-scroll, .reveal').addClass('revealed active');
        
        // Trigger social links animations
        $('.animate-fadeIn').each(function(index) {
            const $element = $(this);
            setTimeout(function() {
                $element.css('animation-play-state', 'running');
            }, index * 100);
        });
    });

    // Expose CobraTheme globally for external access
    window.CobraTheme = CobraTheme;

        })(jQuery);
    }
    
    // Start initialization
    initCobraTheme();
})();