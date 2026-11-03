/**
 * Accessibility JavaScript Enhancements
 * Cobra Theme - WordPress
 * 
 * This file contains JavaScript functionality to improve accessibility:
 * - Keyboard navigation
 * - Screen reader announcements
 * - ARIA states management
 * - Focus management
 */

(function($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        initAccessibility();
    });

    /**
     * Initialize all accessibility enhancements
     */
    function initAccessibility() {
        // Initialize keyboard navigation
        initKeyboardNavigation();
        
        // Initialize dropdown menus
        initDropdownMenus();
        
        // Initialize focus management
        initFocusManagement();
        
        // Initialize screen reader announcements
        initScreenReaderAnnouncements();
        
        // Initialize skip links
        initSkipLinks();
        
        // Initialize mobile menu accessibility
        initMobileMenuAccessibility();
    }

    /**
     * Enhanced keyboard navigation
     */
    function initKeyboardNavigation() {
        // Handle ESC key to close dropdowns
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                closeAllDropdowns();
            }
        });

        // Arrow key navigation for menus
        $('.primary-menu, .user-dropdown, .footer-menu-list').on('keydown', 'a', function(e) {
            var $menu = $(this).closest('ul');
            var $menuItems = $menu.find('a');
            var currentIndex = $menuItems.index(this);

            switch(e.keyCode) {
                case 40: // Down arrow
                    e.preventDefault();
                    var nextIndex = (currentIndex + 1) % $menuItems.length;
                    $menuItems.eq(nextIndex).focus();
                    break;
                    
                case 38: // Up arrow
                    e.preventDefault();
                    var prevIndex = currentIndex - 1;
                    if (prevIndex < 0) prevIndex = $menuItems.length - 1;
                    $menuItems.eq(prevIndex).focus();
                    break;
                    
                case 36: // Home key
                    e.preventDefault();
                    $menuItems.first().focus();
                    break;
                    
                case 35: // End key
                    e.preventDefault();
                    $menuItems.last().focus();
                    break;
            }
        });
    }

    /**
     * Initialize dropdown menu accessibility
     */
    function initDropdownMenus() {
        // User menu toggle
        $('.user-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling to document
            toggleUserMenu($(this));
        });

        // Handle keyboard activation
        $('.user-toggle').on('keydown', function(e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
                e.preventDefault();
                e.stopPropagation(); // Prevent event bubbling to document
                toggleUserMenu($(this));
            }
        });

        // Prevent closing when clicking inside the dropdown
        $('.user-dropdown').on('click', function(e) {
            e.stopPropagation();
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            var $userMenu = $('.user-menu');
            var $mobileMenu = $('.primary-menu-container');
            var $target = $(e.target);
            
            // Close user dropdown if open and click is outside user menu
            if ($('.user-toggle').attr('aria-expanded') === 'true' && 
                !$target.closest('.user-menu').length) {
                closeUserMenu();
            }
            
            // Close mobile menu if open and click is outside mobile menu
            if ($('.menu-toggle').attr('aria-expanded') === 'true' && 
                !$target.closest('.main-navigation').length) {
                closeMobileMenu();
            }
        });
    }

    /**
     * Toggle user menu with proper ARIA states
     */
    function toggleUserMenu($toggle) {
        var $dropdown = $toggle.siblings('.user-dropdown');
        var isExpanded = $toggle.attr('aria-expanded') === 'true';
        
        if (isExpanded) {
            closeUserMenu();
        } else {
            openUserMenu($toggle, $dropdown);
        }
    }

    /**
     * Open user menu
     */
    function openUserMenu($toggle, $dropdown) {
        $toggle.attr('aria-expanded', 'true');
        $dropdown.attr('aria-hidden', 'false');
        
        // Enable tabbing for dropdown items
        $dropdown.find('a').attr('tabindex', '0');
        
        // Focus first item
        $dropdown.find('a').first().focus();
        
        // Announce to screen readers
        announceToScreenReader('User menu opened');
    }

    /**
     * Close user menu
     */
    function closeUserMenu() {
        var $toggle = $('.user-toggle');
        var $dropdown = $('.user-dropdown');
        
        // Only close if menu is actually open
        if ($toggle.attr('aria-expanded') === 'true') {
            $toggle.attr('aria-expanded', 'false');
            $dropdown.attr('aria-hidden', 'true');
            
            // Disable tabbing for dropdown items
            $dropdown.find('a').attr('tabindex', '-1');
            
            // Don't automatically return focus unless user was navigating with keyboard
            // $toggle.focus(); // Commented out to prevent focus issues
        }
    }

    /**
     * Close all dropdowns
     */
    function closeAllDropdowns() {
        closeUserMenu();
        
        // Close mobile menu if open
        if ($('.menu-toggle').attr('aria-expanded') === 'true') {
            toggleMobileMenu();
        }
    }

    /**
     * Initialize focus management
     */
    function initFocusManagement() {
        // Trap focus within dropdowns
        $('.user-dropdown').on('keydown', function(e) {
            if (e.keyCode === 9) { // Tab key
                var $focusable = $(this).find('a');
                var $first = $focusable.first();
                var $last = $focusable.last();
                
                if (e.shiftKey && document.activeElement === $first[0]) {
                    e.preventDefault();
                    $last.focus();
                } else if (!e.shiftKey && document.activeElement === $last[0]) {
                    e.preventDefault();
                    $first.focus();
                }
            }
        });

        // Visible focus indicators
        $('a, button, input, textarea, select').on('focus', function() {
            $(this).addClass('has-focus');
        }).on('blur', function() {
            $(this).removeClass('has-focus');
        });
    }

    /**
     * Initialize screen reader announcements
     */
    function initScreenReaderAnnouncements() {
        // Create live region for announcements
        if (!$('#accessibility-announcements').length) {
            $('body').append('<div id="accessibility-announcements" aria-live="polite" aria-atomic="true" class="screen-reader-text"></div>');
        }
    }

    /**
     * Announce message to screen readers
     */
    function announceToScreenReader(message) {
        var $announcements = $('#accessibility-announcements');
        $announcements.text(message);
        
        // Clear after announcement
        setTimeout(function() {
            $announcements.empty();
        }, 1000);
    }

    /**
     * Initialize skip links
     */
    function initSkipLinks() {
        $('.skip-link').on('click', function(e) {
            var target = $(this).attr('href');
            var $target = $(target);
            
            if ($target.length) {
                e.preventDefault();
                $target.focus();
                
                // Ensure target is focusable
                if (!$target.attr('tabindex')) {
                    $target.attr('tabindex', '-1');
                }
                
                // Scroll to target
                $('html, body').animate({
                    scrollTop: $target.offset().top - 100
                }, 300);
            }
        });
    }

    /**
     * Initialize mobile menu accessibility
     */
    function initMobileMenuAccessibility() {
        $('.menu-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            toggleMobileMenu();
        });

        $('.menu-toggle').on('keydown', function(e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
                e.preventDefault();
                e.stopPropagation(); // Prevent event bubbling
                toggleMobileMenu();
            }
        });

        // Prevent closing when clicking inside the mobile menu
        $('.primary-menu-container').on('click', function(e) {
            e.stopPropagation();
        });

        // Close mobile menu when clicking outside (update the existing document click handler)
    }

    /**
     * Toggle mobile menu
     */
    function toggleMobileMenu() {
        var $toggle = $('.menu-toggle');
        var $menu = $('.primary-menu-container');
        var isExpanded = $toggle.attr('aria-expanded') === 'true';
        
        if (isExpanded) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }

    /**
     * Open mobile menu
     */
    function openMobileMenu() {
        var $toggle = $('.menu-toggle');
        var $menu = $('.primary-menu-container');
        
        $toggle.attr('aria-expanded', 'true');
        $menu.addClass('active'); // Use CSS class instead of show()
        
        // Focus first menu item after animation
        setTimeout(function() {
            $menu.find('a').first().focus();
        }, 100);
        
        announceToScreenReader('Navigation menu opened');
    }

    /**
     * Close mobile menu
     */
    function closeMobileMenu() {
        var $toggle = $('.menu-toggle');
        var $menu = $('.primary-menu-container');
        
        $toggle.attr('aria-expanded', 'false');
        $menu.removeClass('active'); // Use CSS class instead of hide()
        
        // Return focus to toggle
        $toggle.focus();
        
        announceToScreenReader('Navigation menu closed');
    }

    /**
     * Handle video accessibility
     */
    function initVideoAccessibility() {
        // Add keyboard controls to videos
        $('video').each(function() {
            var $video = $(this);
            
            // Make video focusable
            $video.attr('tabindex', '0');
            
            // Add keyboard controls
            $video.on('keydown', function(e) {
                switch(e.keyCode) {
                    case 32: // Space bar - play/pause
                        e.preventDefault();
                        if (this.paused) {
                            this.play();
                            announceToScreenReader('Video playing');
                        } else {
                            this.pause();
                            announceToScreenReader('Video paused');
                        }
                        break;
                        
                    case 77: // M key - mute/unmute
                        e.preventDefault();
                        this.muted = !this.muted;
                        announceToScreenReader(this.muted ? 'Video muted' : 'Video unmuted');
                        break;
                }
            });
        });
    }

    /**
     * Form accessibility enhancements
     */
    function initFormAccessibility() {
        // Add required field indicators
        $('input[required], textarea[required], select[required]').each(function() {
            var $field = $(this);
            var $label = $('label[for="' + $field.attr('id') + '"]');
            
            if ($label.length && !$label.find('.required').length) {
                $label.addClass('required');
            }
        });

        // Form validation announcements
        $('form').on('submit', function(e) {
            var $form = $(this);
            var $errors = $form.find('.error, :invalid');
            
            if ($errors.length > 0) {
                e.preventDefault();
                
                // Announce errors
                var errorCount = $errors.length;
                announceToScreenReader(errorCount + ' error' + (errorCount > 1 ? 's' : '') + ' found in form');
                
                // Focus first error
                $errors.first().focus();
            }
        });
    }

    // Initialize additional accessibility features
    initVideoAccessibility();
    initFormAccessibility();

})(jQuery);