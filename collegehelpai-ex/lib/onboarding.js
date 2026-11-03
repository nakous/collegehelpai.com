// Onboarding page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize translations
    if (typeof translatePage === 'function') {
        translatePage();
    }

    // Add detection for Mac users to show Cmd instead of Ctrl
    if (navigator.platform.toUpperCase().indexOf('MAC') >= 0) {
        const shortcuts = document.querySelectorAll('.shortcut-key');
        shortcuts.forEach(shortcut => {
            shortcut.textContent = shortcut.textContent.replace('Ctrl', 'Cmd');
        });
    }

    // Add smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add tracking for external links (optional)
    document.querySelectorAll('a[href^="https://collegehelpai.com"]').forEach(link => {
        link.addEventListener('click', function() {
            console.log('External link clicked:', this.href);
            // Add analytics tracking here if needed
        });
    });
});
