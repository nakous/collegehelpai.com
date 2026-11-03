/**
 * YouTube Facade - Lazy Load Optimization
 * Économise ~500KB de JavaScript YouTube jusqu'au clic utilisateur
 * Améliore LCP et TBT significativement
 */

(function() {
    'use strict';

    /**
     * Charger l'iframe YouTube au clic sur la façade
     */
    function loadYouTubeIframe(facade) {
        const videoId = facade.getAttribute('data-youtube-id');
        
        if (!videoId) {
            console.warn('YouTube Facade: Missing data-youtube-id attribute');
            return;
        }

        // Créer l'iframe YouTube
        const iframe = document.createElement('iframe');
        iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`);
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('title', 'YouTube video player');
        
        // Remplacer la façade par l'iframe
        facade.innerHTML = '';
        facade.appendChild(iframe);
        
        // Retirer le cursor pointer et le click handler
        facade.style.cursor = 'default';
        facade.removeEventListener('click', handleFacadeClick);
    }

    /**
     * Handler pour le clic sur la façade
     */
    function handleFacadeClick(event) {
        event.preventDefault();
        loadYouTubeIframe(this);
    }

    /**
     * Initialiser toutes les façades YouTube sur la page
     */
    function initYouTubeFacades() {
        const facades = document.querySelectorAll('.youtube-facade');
        
        facades.forEach(function(facade) {
            // Vérifier que l'iframe n'est pas déjà chargé
            if (facade.querySelector('iframe')) {
                return;
            }

            // Ajouter le click handler
            facade.addEventListener('click', handleFacadeClick);
            
            // Amélioration accessibilité : support clavier
            facade.setAttribute('tabindex', '0');
            facade.setAttribute('role', 'button');
            
            // Support Enter/Space pour accessibilité
            facade.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    loadYouTubeIframe(facade);
                }
            });
        });

        console.log(`YouTube Facade: ${facades.length} facade(s) initialized`);
    }

    /**
     * Initialiser au chargement du DOM
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYouTubeFacades);
    } else {
        initYouTubeFacades();
    }

    /**
     * Support pour chargement dynamique de contenu (AJAX, Infinite Scroll, etc.)
     */
    if (typeof window.cobra_youtube_facade_init === 'undefined') {
        window.cobra_youtube_facade_init = initYouTubeFacades;
    }

})();
