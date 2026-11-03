/*
Search Page JavaScript
Theme: Cobra
Version: 3.0.1
*/

document.addEventListener('DOMContentLoaded', function() {
    // Search page specific functionality
    initSearchPage();
    
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
});

function initSearchPage() {
    // Highlight search terms in results
    const searchQuery = getSearchQuery();
    if (searchQuery) {
        highlightSearchTerms(searchQuery);
    }
    
    // Enhance search form
    enhanceSearchForm();
    
    // Add smooth animations to search results
    animateSearchResults();
}

function getSearchQuery() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('s');
}

function highlightSearchTerms(query) {
    const terms = query.toLowerCase().split(' ').filter(term => term.length > 2);
    const resultExcerpts = document.querySelectorAll('.result-excerpt');
    
    resultExcerpts.forEach(excerpt => {
        let content = excerpt.innerHTML;
        
        terms.forEach(term => {
            const regex = new RegExp(`(${term})`, 'gi');
            content = content.replace(regex, '<mark>$1</mark>');
        });
        
        excerpt.innerHTML = content;
    });
}

function enhanceSearchForm() {
    const searchForm = document.querySelector('.search-form-container form');
    const searchInput = searchForm?.querySelector('input[type="search"]');
    
    if (searchInput) {
        // Add search suggestions (simple implementation)
        searchInput.addEventListener('input', function() {
            // This could be enhanced with AJAX search suggestions
            const value = this.value.trim();
            if (value.length > 2) {
                // Show search suggestions logic here
            }
        });
        
        // Focus search input on page load if no results
        if (document.querySelector('.no-results')) {
            searchInput.focus();
        }
    }
}

function animateSearchResults() {
    const searchResults = document.querySelectorAll('.search-result-item');
    
    // Add staggered animation to search results
    searchResults.forEach((result, index) => {
        result.style.opacity = '0';
        result.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            result.style.transition = 'all 0.4s ease';
            result.style.opacity = '1';
            result.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Enhanced hover effects
    searchResults.forEach(result => {
        result.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        result.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}