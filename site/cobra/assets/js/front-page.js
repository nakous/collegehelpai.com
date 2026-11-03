// Add JavaScript functionality for animations and interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Scroll animation for reveal elements
    function reveal() {
        const reveals = document.querySelectorAll('.reveal');

        for (let i = 0; i < reveals.length; i++) {
            const windowHeight = window.innerHeight;
            const elementTop = reveals[i].getBoundingClientRect().top;
            const elementVisible = 150;

            if (elementTop < windowHeight - elementVisible) {
                reveals[i].classList.add('active');
            }
        }
    }

    window.addEventListener('scroll', reveal);
    reveal(); // Initial check on page load

    // Header scroll effect
    const header = document.querySelector('.the-header');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Note: Mobile menu toggle is handled by accessibility.js
    // This avoids conflicts between the two JavaScript files

    // Video overlay play button
    const videoOverlay = document.querySelector('.video-overlay');
    const featureVideo = document.querySelector('.feature-video');

    if (videoOverlay && featureVideo) {
        videoOverlay.addEventListener('click', function() {
            featureVideo.controls = true;
            featureVideo.play();
            this.style.display = 'none';
        });
    }
});