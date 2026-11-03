// i18n.js - Handles loading translations for the popup
document.addEventListener('DOMContentLoaded', function() {
    // Apply translations based on data-i18n attributes
    function applyTranslations() {
        // Handle elements with data-i18n attribute for text content
        const i18nElements = document.querySelectorAll('[data-i18n]');
        i18nElements.forEach(element => {
            const messageKey = element.getAttribute('data-i18n');
            const message = chrome.i18n.getMessage(messageKey);
            if (message) {
                element.textContent = message;
            }
        });

        // Handle elements with data-i18n-title attribute for title/tooltip
        const i18nTitleElements = document.querySelectorAll('[data-i18n-title]');
        i18nTitleElements.forEach(element => {
            const messageKey = element.getAttribute('data-i18n-title');
            const message = chrome.i18n.getMessage(messageKey);
            if (message) {
                element.title = message;
            }
        });

        // Handle elements with data-i18n-alt attribute for alt text
        const i18nAltElements = document.querySelectorAll('[data-i18n-alt]');
        i18nAltElements.forEach(element => {
            const messageKey = element.getAttribute('data-i18n-alt');
            const message = chrome.i18n.getMessage(messageKey);
            if (message) {
                element.alt = message;
            }
        });

        // Text elements that need translation (for existing IDs)
        const textElements = {
            // Buttons
            takeScreenshotText: "takeScreenshot",
            loginButtonText: "loginButton",
            registerButtonText: "registerButton",
            
            // Settings
            settingsTitleText: "settingsTitle",
            languageLabelText: "languageLabel",
            camouflageModeText: "camouflageModeLabel",
            
            // Language options
            languageEnText: "languageEN",
            languageEsText: "languageES",
            languageFrText: "languageFR",
            languageArText: "languageAR",
            languageItText: "languageIT",
            languageRuText: "languageRU"
        };
        
        // Apply translations to all elements by ID
        for (const [elementId, messageName] of Object.entries(textElements)) {
            const element = document.getElementById(elementId);
            if (element) {
                const message = chrome.i18n.getMessage(messageName);
                if (message) {
                    element.textContent = message;
                }
            }
        }

        // Update version label with current version
        const versionElement = document.getElementById('version');
        if (versionElement) {
            const manifest = chrome.runtime.getManifest();
            const versionMessage = chrome.i18n.getMessage('versionLabel', [manifest.version]);
            if (versionMessage) {
                versionElement.textContent = versionMessage;
            }
        }
    }

    // Apply translations immediately
    applyTranslations();
    
    // Re-apply translations when language changes
    window.applyTranslations = applyTranslations;
});