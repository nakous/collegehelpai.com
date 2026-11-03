// Toggle between plans
document.addEventListener('DOMContentLoaded', function() {
    const planToggle = document.getElementById('planToggle');
    
    if (planToggle) {
        planToggle.addEventListener('change', function() {
            // Get data attributes for yearly and monthly plan URLs
            const yearlyUrl = this.dataset.yearlyUrl;
            const monthlyUrl = this.dataset.monthlyUrl;
            
            if (this.checked && yearlyUrl) {
                // Yearly - redirect to yearly plan page
                window.location.href = yearlyUrl;
            } else if (!this.checked && monthlyUrl) {
                // Monthly - redirect to monthly plan page
                window.location.href = monthlyUrl;
            }
        });
    }
});