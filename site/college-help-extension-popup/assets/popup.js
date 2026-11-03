// College Help Extension Popup JavaScript
var collegeHelpPopup = {
    
    // Function to detect browser
    detectBrowser: function() {
        const userAgent = navigator.userAgent;
        
        if (userAgent.includes("Chrome") && !userAgent.includes("Edg")) {
            return "chrome";
        } else if (userAgent.includes("Edg")) {
            return "edge";
        }
        return "other";
    },
    
    // Function to create install button based on browser
    createInstallButton: function() {
        const browser = this.detectBrowser();
        const container = document.getElementById("collegeHelpInstallButtonContainer");
        
        let buttonHTML = "";
        
        if (browser === "chrome") {
            buttonHTML = `
                <a href="${collegeHelpAjax.chrome_link}" class="college-help-install-btn chrome-style" target="_blank" onclick="collegeHelpPopup.trackAction('install', 'chrome')">
                    <svg class="college-help-browser-logo" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C8.21 0 4.831 1.757 2.632 4.501l3.953 6.848A5.454 5.454 0 0 1 12 6.545h10.691A12 12 0 0 0 12 0zM1.931 5.47A11.943 11.943 0 0 0 0 12c0 6.012 4.42 10.991 10.189 11.864l3.953-6.847a5.45 5.45 0 0 1-6.865-2.29zm13.342 2.166a5.446 5.446 0 0 1 1.45 7.09l-3.953-6.847c.814-.064 1.645 0 2.503.757zm.618 14.943A11.94 11.94 0 0 0 24 12c0-1.357-.229-2.66-.639-3.873H12c-.304 0-.6.017-.89.049z"/>
                    </svg>
                    Add to Chrome
                    <i class="fas fa-plus"></i>
                </a>
            `;
        } else if (browser === "edge") {
            buttonHTML = `
                <a href="${collegeHelpAjax.edge_link}" class="college-help-install-btn edge-style" target="_blank" onclick="collegeHelpPopup.trackAction('install', 'edge')">
                    <svg class="college-help-browser-logo" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21.86 8.882a10.328 10.328 0 0 0-.745-2.746 10.856 10.856 0 0 0-1.829-2.746 11.486 11.486 0 0 0-2.829-2.134A12.15 12.15 0 0 0 12.8.582a11.616 11.616 0 0 0-1.912-.04 10.6 10.6 0 0 0-4.635 1.355 9.764 9.764 0 0 0-3.383 3.164 8.92 8.92 0 0 0-1.308 4.411c-.04.913.04 1.829.242 2.702a6.942 6.942 0 0 0 .946 2.377c.424.673.946 1.266 1.59 1.748.643.484 1.387.866 2.173 1.145a9.164 9.164 0 0 0 2.617.362c.946 0 1.912-.161 2.857-.443.705-.202 1.387-.484 2.012-.866.625-.382 1.186-.866 1.67-1.427.322-.362.604-.766.825-1.206.221-.443.363-.928.424-1.427.04-.282.04-.564.04-.866 0-.564-.08-1.125-.242-1.67-.161-.542-.403-1.044-.725-1.504z"/>
                    </svg>
                    Get for Edge
                    <i class="fas fa-download"></i>
                </a>
            `;
        } else {
            buttonHTML = `
                <a href="${collegeHelpAjax.github_link}" class="college-help-install-btn github-style" target="_blank" onclick="collegeHelpPopup.trackAction('install', 'github')">
                    <i class="fab fa-github"></i>
                    Get from GitHub
                    <i class="fas fa-external-link-alt"></i>
                </a>
            `;
        }
        
        container.innerHTML = buttonHTML;
    },
    
    // Function to show popup
    showPopup: function() {
        const popup = document.getElementById("extensionPopup");
        popup.classList.add("show");
        this.trackAction("view", this.detectBrowser());
    },
    
    // Function to close popup
    closePopup: function() {
        const popup = document.getElementById("extensionPopup");
        popup.classList.remove("show");
        
        // Set localStorage with current timestamp for 7-day delay
        const now = new Date().getTime();
        localStorage.setItem("collegeHelpPopupClosed", now.toString());
        this.trackAction("close", this.detectBrowser());
    },
    
    // Function to check if popup should be shown
    shouldShowPopup: function() {
        const lastClosed = localStorage.getItem("collegeHelpPopupClosed");
        
        if (!lastClosed) {
            return true; // Never shown before
        }
        
        const lastClosedTime = parseInt(lastClosed);
        const now = new Date().getTime();
        const sevenDaysInMs = 7 * 24 * 60 * 60 * 1000; // 7 days in milliseconds
        
        return (now - lastClosedTime) > sevenDaysInMs;
    },
    
    // Function to track actions
    trackAction: function(actionType, browser) {
        jQuery.ajax({
            url: collegeHelpAjax.ajaxurl,
            type: "POST",
            data: {
                action: "extension_popup_track",
                action_type: actionType,
                browser: browser,
                nonce: collegeHelpAjax.nonce
            }
        });
    },
    
    // Initialize popup
    init: function() {
        this.createInstallButton();
        
        if (this.shouldShowPopup()) {
            // Show popup after 2 seconds delay
            setTimeout(() => {
                this.showPopup();
            }, 2000);
        }
        
        // Close popup when clicking outside
        document.addEventListener("click", (e) => {
            const popup = document.getElementById("extensionPopup");
            const container = document.querySelector(".college-help-popup-container");
            
            if (e.target === popup && !container.contains(e.target)) {
                this.closePopup();
            }
        });
        
        // Close popup with Escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                this.closePopup();
            }
        });
    }
};

// Initialize when page loads
jQuery(document).ready(function() {
    collegeHelpPopup.init();
});