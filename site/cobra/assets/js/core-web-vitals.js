/*
Core Web Vitals Optimization Script
Theme: Cobra
Version: 3.0.1
*/

// Intersection Observer for lazy loading videos and heavy content
document.addEventListener('DOMContentLoaded', function() {
    
    // Lazy load YouTube iframes
    const lazyYouTubeVideos = document.querySelectorAll('iframe[data-src]');
    
    if (lazyYouTubeVideos.length > 0) {
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    iframe.src = iframe.dataset.src;
                    iframe.removeAttribute('data-src');
                    videoObserver.unobserve(iframe);
                }
            });
        }, {
            rootMargin: '50px'
        });

        lazyYouTubeVideos.forEach(video => {
            videoObserver.observe(video);
        });
    }

    // Optimize video loading based on connection speed
    if ('connection' in navigator) {
        const connection = navigator.connection;
        const videos = document.querySelectorAll('video');
        
        videos.forEach(video => {
            // For slow connections, reduce video quality or pause autoplay
            if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                video.preload = 'none';
                if (video.hasAttribute('autoplay')) {
                    video.removeAttribute('autoplay');
                    video.controls = true;
                }
            }
        });
    }

    // Preload critical resources based on user interaction
    let hasInteracted = false;
    
    function preloadOnInteraction() {
        if (!hasInteracted) {
            hasInteracted = true;
            
            // Preload next page if user shows engagement
            const nextPageLink = document.querySelector('link[rel="prefetch"]');
            if (nextPageLink) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = nextPageLink.href;
                document.head.appendChild(link);
            }
            
            // Remove event listeners after first interaction
            window.removeEventListener('scroll', preloadOnInteraction);
            window.removeEventListener('click', preloadOnInteraction);
            window.removeEventListener('touchstart', preloadOnInteraction);
        }
    }
    
    // Listen for user engagement
    window.addEventListener('scroll', preloadOnInteraction, { passive: true });
    window.addEventListener('click', preloadOnInteraction, { passive: true });
    window.addEventListener('touchstart', preloadOnInteraction, { passive: true });

    // Optimize image loading based on viewport
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if (images.length > 0 && 'IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Function to show image with fade-in
                    function showImage() {
                        img.style.opacity = '1';
                        img.style.transition = 'opacity 0.3s ease-in-out';
                    }
                    
                    // Check if image is already loaded (from cache)
                    if (img.complete && img.naturalWidth > 0) {
                        showImage();
                    } else {
                        // Add fade-in animation when image loads
                        img.addEventListener('load', showImage, { once: true });
                        
                        // Fallback in case load event doesn't fire
                        setTimeout(() => {
                            if (img.style.opacity !== '1') {
                                showImage();
                            }
                        }, 3000);
                    }
                    
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '100px'
        });

        images.forEach(img => {
            // Only set opacity to 0 if image is not already loaded
            if (!img.complete || img.naturalWidth === 0) {
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease-in-out';
            }
            imageObserver.observe(img);
        });
    }

    // Critical CSS injection for above-the-fold optimization
    function injectCriticalCSS() {
        const criticalStyles = `
            /* Above-the-fold critical styles */
            .hero-section {
                background: linear-gradient(135deg, #69b8db 0%, #4c5e87 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                will-change: transform;
            }
            .hero-content {
                color: white;
                text-align: center;
                transform: translateZ(0);
            }
            .install-btn {
                transform: translateZ(0);
                will-change: transform;
            }
        `;
        
        if (!document.getElementById('critical-styles')) {
            const style = document.createElement('style');
            style.id = 'critical-styles';
            style.textContent = criticalStyles;
            document.head.appendChild(style);
        }
    }
    
    // Inject critical CSS immediately
    injectCriticalCSS();

    // Performance monitoring
    if ('PerformanceObserver' in window) {
        // Monitor Largest Contentful Paint
        const lcpObserver = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            entries.forEach(entry => {
                if (entry.entryType === 'largest-contentful-paint') {
                    // console.log('LCP:', entry.startTime);
                }
            });
        });
        
        try {
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        } catch (e) {
            // Browser doesn't support this entry type
        }

        // Monitor Cumulative Layout Shift
        const clsObserver = new PerformanceObserver((list) => {
            let clsValue = 0;
            list.getEntries().forEach(entry => {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            });
            // if (clsValue > 0) {
            //     console.log('CLS:', clsValue);
            // }
        });
        
        try {
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        } catch (e) {
            // Browser doesn't support this entry type
        }
    }

    // Optimize scroll performance
    let scrollTimeout;
    let isScrolling = false;
    
    function optimizeScroll() {
        if (!isScrolling) {
            isScrolling = true;
            requestAnimationFrame(() => {
                // Scroll-based animations here
                isScrolling = false;
            });
        }
    }
    
    window.addEventListener('scroll', optimizeScroll, { passive: true });

    // Prefetch resources on hover for better perceived performance
    const importantLinks = document.querySelectorAll('a[href^="/"], a[href*="' + location.hostname + '"]');
    
    importantLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const href = this.getAttribute('href');
            if (href && !document.querySelector(`link[rel="prefetch"][href="${href}"]`)) {
                const prefetchLink = document.createElement('link');
                prefetchLink.rel = 'prefetch';
                prefetchLink.href = href;
                document.head.appendChild(prefetchLink);
            }
        }, { passive: true });
    });
});

// Service Worker registration for caching (if available)
// Disable Service Worker in development to avoid caching issues
if ('serviceWorker' in navigator && window.location.hostname !== 'localhost' && !window.location.hostname.includes('.localhost')) {
    window.addEventListener('load', () => {
        // First, unregister any existing service workers to avoid conflicts
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => {
                if (registration.scope.includes('sw.js')) {
                    registration.unregister();
                }
            });
        });

        // Wait a bit before registering the new one
        setTimeout(() => {
            // Only register if service worker file exists
            fetch('/sw.js', { method: 'HEAD' })
                .then(response => {
                    if (response.ok) {
                        navigator.serviceWorker.register('/sw.js')
                            .then(registration => {
                                // console.log('SW registered successfully:', registration);
                                
                                // Force update if there's a waiting service worker
                                if (registration.waiting) {
                                    registration.waiting.postMessage({type: 'SKIP_WAITING'});
                                }
                            })
                            .catch(registrationError => {
                                console.log('SW registration failed:', registrationError);
                            });
                    }
                })
                .catch(() => {
                    // Service worker file doesn't exist, ignore
                    console.log('Service worker file not found, skipping registration');
                });
        }, 1000);
    });

    // Listen for service worker updates
    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data && event.data.type === 'SW_UPDATED') {
            // Optionally show user a notification about the update
            console.log('Service worker updated');
        }
    });
} else {
    // Service Worker disabled in development - this is normal
    console.info('🔧 Development mode: Service Worker disabled for easier debugging');
    
    // Clear any existing service workers in development
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => {
                registration.unregister();
            });
        });
    }
}