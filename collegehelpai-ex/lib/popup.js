// popup.js
const isProduction = true; // Set to true for development mode, false for production
const baseUrl = !isProduction ? 'http://college.localhost/' : 'https://collegehelpai.com/';
const CONFIG = {
    websiteUrl: `${baseUrl}`,
    loginUrl: `${baseUrl}login?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button`,
    registerUrl: `${baseUrl}registration?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button`,
    profileUrl: (userId) => `${baseUrl}my-account?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button`,
    pricingUrl: `${baseUrl}plans/monthly-plan/?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button`,
    screenshotCommand: 'initiateScreenshot',
    codeApp: 'CHAI25_',
};

class PopupManager {
    constructor() {
        this.initializeElements();
        this.attachEventListeners();
        this.testBackgroundConnection();
    }

    initializeElements() {
        this.elements = {
            connectedView: document.getElementById('connectedView'),
            anonymousView: document.getElementById('anonymousView'),
            settingsBtn: document.getElementById('settingsBtn'),
            websiteBtn: document.getElementById('websiteBtn'),
            pricingBtn: document.getElementById('pricingBtn'),
            screenshotBtn: document.getElementById('screenshotBtn'),
            loginBtn: document.getElementById('loginBtn'),
            registerBtn: document.getElementById('registerBtn'),
            username: document.getElementById('username'),
            planType: document.getElementById('planType'),
            profileLink: null, // Removed profile link
            settingsPopup: document.getElementById('settingsPopup'),
            languageSelect: document.getElementById('languageSelect'),
            camouflageMode: document.getElementById('camouflageMode'),
            screenshotButtonToggle: document.getElementById('screenshotButtonToggle'),
            version: document.getElementById('version'),
            closeSettingsBtn: document.getElementById('closeSettingsBtn'),
            helpLink: document.getElementById('helpLink'),
            loadingView: document.getElementById('loadingView')
        };
        this.elements.version.textContent = `Version ${chrome.runtime.getManifest().version}`;
    }

    attachEventListeners() {
        this.elements.settingsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleSettings();
        });
        this.elements.websiteBtn.addEventListener('click', () => this.openWebsite());
        this.elements.pricingBtn.addEventListener('click', () => {
            chrome.tabs.create({ url: CONFIG.pricingUrl });
        });
        this.elements.screenshotBtn.addEventListener('click', () => this.initiateScreenshot());
        this.elements.loginBtn.addEventListener('click', () => this.openLogin());
        this.elements.registerBtn.addEventListener('click', () => this.openRegister());
        this.elements.languageSelect.addEventListener('change', (e) => this.updateLanguage(e.target.value));
        this.elements.camouflageMode.addEventListener('change', (e) => this.updateCamouflageMode(e.target.checked));
        this.elements.screenshotButtonToggle.addEventListener('change', (e) => this.updateScreenshotButton(e.target.checked));

        // Add shortcuts configuration button
        document.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'configureShortcutsBtn') {
                chrome.tabs.create({ url: 'chrome://extensions/shortcuts' });
            }
        });

        this.elements.helpLink.addEventListener('click', () => {
            chrome.tabs.create({ url: `${CONFIG.websiteUrl}faq/?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button` });
        });

        // Add event listener for the close settings button
        if (this.elements.closeSettingsBtn) {
            this.elements.closeSettingsBtn.addEventListener('click', () => {
                this.elements.settingsPopup.classList.add('hidden');
            });
        }

        document.addEventListener('click', (e) => {
            if (!this.elements.settingsPopup.contains(e.target) && !this.elements.settingsBtn.contains(e.target)) {
                this.elements.settingsPopup.classList.add('hidden');
            }
        });
    }

    init() {

        // First load settings
        this.loadSettings((settings) => {
            // Check for shortcuts and show notification if not set
            this.checkShortcuts();

            // console.log('Settings loaded:', settings);
        });
        // Then check login status
        this.checkLoginStatus((isLoggedIn, userData) => {

            if (isLoggedIn) {
                // If checkLoginStatus already returned userData, use it
                if (userData) {
                    this.updateUserInterface(userData);
                    this.showConnectedView();
                } else {
                    // Otherwise load user data separately
                    this.loadUserData((userData) => {
                        this.showConnectedView();
                    });
                }
            } else {
                this.showAnonymousView();
            }
        });
    }

    // Check if keyboard shortcuts are configured
    checkShortcuts() {
        chrome.commands.getAll((commands) => {
            // Filter out the built-in "_execute_action" command and only check our custom commands
            const customCommands = commands.filter(command =>
                command.name !== '_execute_action' &&
                (command.name === 'ask_collegehelp' || command.name === 'take_screenshot')
            );

            const unconfiguredCommands = customCommands.filter(command => !command.shortcut);

            // Show notification if our custom shortcuts are not configured
            if (unconfiguredCommands.length > 0) {
                this.showShortcutNotification();
            }

            // Update shortcuts display in settings
            this.updateShortcutsDisplay(commands);
        });
    }

    // Update shortcuts display in settings
    updateShortcutsDisplay(commands) {
        const shortcutsList = document.getElementById('shortcutsList');
        if (!shortcutsList) return;

        shortcutsList.innerHTML = '';

        // Add info text for Windows users
        const infoText = document.createElement('div');
        infoText.style.cssText = `
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
            padding: 6px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #ffc107;
        `;
        infoText.innerHTML = `
            <strong>Windows Note:</strong> Chrome doesn't automatically assign shortcuts. 
            Click "Configure Shortcuts" to set them up manually.
        `;
        shortcutsList.appendChild(infoText);

        commands.forEach(command => {
            // Skip the built-in "Activate the extension" command if you want to hide it
            if (command.name === '_execute_action') {
                return; // Skip this built-in command
            }

            const shortcutItem = document.createElement('div');
            shortcutItem.className = 'shortcut-item';

            const shortcutName = document.createElement('span');
            shortcutName.className = 'shortcut-name';

            // Use custom descriptions for better clarity
            if (command.name === 'ask_collegehelp') {
                shortcutName.textContent = 'Ask College Help AI (analyze selected text)';
            } else if (command.name === 'take_screenshot') {
                shortcutName.textContent = 'Take Screenshot (capture area for analysis)';
            } else {
                shortcutName.textContent = command.description || command.name;
            }

            const shortcutKey = document.createElement('span');
            shortcutKey.className = command.shortcut ? 'shortcut-key' : 'shortcut-key not-set';

            // Show suggested key if not set
            if (!command.shortcut) {
                const suggestedKey = this.getSuggestedKey(command.name);
                shortcutKey.innerHTML = `
                    <span style="text-decoration: line-through; opacity: 0.5;">${suggestedKey}</span>
                    <span style="margin-left: 4px; color: #dc3545;">Not set</span>
                `;
            } else {
                shortcutKey.textContent = command.shortcut;
            }

            shortcutItem.appendChild(shortcutName);
            shortcutItem.appendChild(shortcutKey);
            shortcutsList.appendChild(shortcutItem);
        });
    }

    // Get suggested key combination for a command
    getSuggestedKey(commandName) {
        const suggestions = {
            'ask_collegehelp': 'Ctrl+Shift+A',
            'take_screenshot': 'Ctrl+Shift+S'
        };
        return suggestions[commandName] || 'Not set';
    }

    // Show notification about unconfigured shortcuts
    showShortcutNotification() {
        // Check if notification already exists
        if (document.querySelector('.shortcut-notification')) return;

        // Create notification element inside the popup
        const notification = document.createElement('div');
        notification.className = 'shortcut-notification';
        notification.style.cssText = `
            position: absolute;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            margin: 8px 16px;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
            cursor: pointer;
            border: 1px solid rgba(255, 152, 0, 0.3);
            transition: all 0.2s ease;
            top: 50px;
            width: 82%;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 14px;">⚠️</span>
                <div>
                    <div style="font-weight: 600; margin-bottom: 2px;">Setup Required</div>
                    <div style="font-size: 10px; opacity: 0.8;">Keyboard shortcuts not configured. Click to setup.</div>
                </div>
            </div>
        `;

        // Add hover effect
        notification.addEventListener('mouseenter', () => {
            notification.style.background = 'linear-gradient(135deg, #ffca28 0%, #ffa726 100%)';
            notification.style.transform = 'translateY(-1px)';
        });

        notification.addEventListener('mouseleave', () => {
            notification.style.background = 'linear-gradient(135deg, #ffc107 0%, #ff9800 100%)';
            notification.style.transform = 'translateY(0)';
        });

        notification.addEventListener('click', () => {
            chrome.tabs.create({ url: 'chrome://extensions/shortcuts' });
            notification.remove();
        });

        // Insert notification at the top of popup content
        const popupContainer = document.getElementById('popup-container');
        const header = popupContainer.querySelector('.popup-header');
        if (header && header.nextSibling) {
            popupContainer.insertBefore(notification, header.nextSibling);
        } else {
            popupContainer.appendChild(notification);
        }

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => notification.remove(), 200);
            }
        }, 3000);
    }

    sendMessage(messageType, data, callback) {
        const message = {
            action: CONFIG.codeApp + messageType,
            ...data
        };

        try {
            chrome.runtime.sendMessage(message, (response) => {
                if (chrome.runtime.lastError) {
                    console.error('Message error:', chrome.runtime.lastError.message);
                    // If background script is not ready, retry after a short delay
                    if (chrome.runtime.lastError.message.includes('Receiving end does not exist')) {
                        // console.log('Background script not ready, retrying...');
                        setTimeout(() => {
                            this.sendMessage(messageType, data, callback);
                        }, 100);
                        return;
                    }
                    callback(null);
                    return;
                }

                callback(response);
            });
        } catch (error) {
            console.error('Send message failed:', error);
            callback(null);
        }
    }

    testBackgroundConnection() {
        // console.log('Testing background script connection...');
        this.sendMessage('getSettings', {}, (response) => {
            if (response) {
                // console.log('Background script connection successful');
            } else {
                console.error('Background script connection failed');
            }
        });
    }

    checkLoginStatus(callback) {
        this.sendMessage('checkSession', {}, (response) => {
            const isLoggedIn = !!(response?.success && response?.isLoggedIn);
            const userData = response?.userData || null;
            callback(isLoggedIn, userData);
        });
    }

    loadUserData(callback) {
        this.sendMessage('getUserData', {}, (response) => {
            if (response?.success && response?.data) {
                this.updateUserInterface(response.data);
                callback(response.data); 
            } else {
                callback(null);
            }
        });
    }

    loadSettings(callback) {
        this.sendMessage('getSettings', {}, (response) => {
            if (response?.success && response?.data) {
                this.updateSettingsUI(response.data);
                callback(response.data);
            } else {
                // Return default settings if none received
                const defaultSettings = {
                    language: chrome.i18n.getUILanguage(),
                    camouflageMode: false
                };
                callback(defaultSettings);
            }
        });
    }

    updateSettingsUI(settings) {
        if (settings) {
            // Update language dropdown
            if (settings.language && this.elements.languageSelect) {
                this.elements.languageSelect.value = settings.language;
            }

            // Update camouflage mode checkbox
            if (this.elements.camouflageMode) {
                this.elements.camouflageMode.checked = !!settings.camouflageMode;
            }

            // Update screenshot button toggle
            if (this.elements.screenshotButtonToggle) {
                this.elements.screenshotButtonToggle.checked = settings.screenshotButtonEnabled !== undefined ? settings.screenshotButtonEnabled : true;
            }
        }
    }

    updateUserInterface(userData) {
        if (!userData) return;

        // Update user profile information
        this.elements.username.textContent = userData.name || 'User';

        // Update email if available
        const userEmailElement = document.getElementById('userEmail');
        if (userEmailElement && userData.email) {
            userEmailElement.textContent = userData.email;
        }

        // Update plan information
        const isSubscribed = userData.isSubscribed === true;
        const planBadge = document.getElementById('planBadge');
        const upgradeBtn = document.getElementById('upgradeBtn');

        if (isSubscribed) {
            this.elements.planType.textContent = 'Premium';
            this.elements.planType.style.cursor = 'pointer';
            this.elements.planType.style.textDecoration = 'underline';
            this.elements.planType.onclick = () => {
                chrome.tabs.create({ url: 'https://collegehelpai.com/my-account/#subscription' });
            };
            if (planBadge) {
                planBadge.className = 'plan-badge premium';
            }
            if (upgradeBtn) {
                upgradeBtn.classList.add('hidden');
            }
        } else {
            this.elements.planType.textContent = 'Free';
            if (planBadge) {
                planBadge.className = 'plan-badge free';
            }
            if (upgradeBtn) {
                upgradeBtn.classList.remove('hidden');
                // Add click handler for upgrade button
                upgradeBtn.onclick = () => {
                    chrome.tabs.create({ url: 'https://collegehelpai.com/plans/monthly-plan/' });
                };
            }
        }

        // Update usage statistics for free users
        const usageStats = document.getElementById('usageStats');
        if (!isSubscribed && usageStats) {
            usageStats.classList.remove('hidden');
            
            // Calculate daily usage - consumed is used today, credits are remaining
            const consumedToday = userData.consumed || 0;
            const remainingCredits = userData.credits || 0;
            // Daily limit is fixed for free users (typically 10)
            const dailyLimit = 10; 
            
            // console.log('Usage Debug:', {
            //     consumedToday,
            //     remainingCredits,
            //     dailyLimit,
            //     userData
            // });

            // Update usage display - show consumed / fixed daily limit
            const usedTodayElement = document.getElementById('usedToday');
            const dailyLimitElement = document.getElementById('dailyLimit');
            const usageProgress = document.getElementById('usageProgress');

            if (usedTodayElement) usedTodayElement.textContent = consumedToday;
            if (dailyLimitElement) dailyLimitElement.textContent = dailyLimit;

            // Update progress bar based on consumed vs fixed daily limit
            if (usageProgress) {
                const progressPercentage = dailyLimit > 0 ? (consumedToday / dailyLimit) * 100 : 0;
                usageProgress.style.width = `${Math.min(progressPercentage, 100)}%`;

                // Change color based on usage
                if (progressPercentage >= 90) {
                    usageProgress.style.background = 'linear-gradient(90deg, #dc3545, #c82333)';
                } else if (progressPercentage >= 70) {
                    usageProgress.style.background = 'linear-gradient(90deg, #ffc107, #fd7e14)';
                } else {
                    usageProgress.style.background = 'linear-gradient(90deg, var(--primary-color), var(--secondary-color))';
                }
            }
        } else if (usageStats) {
            usageStats.classList.add('hidden');
        }

        // Profile link functionality removed as requested
    }

    showConnectedView() {
        this.elements.loadingView.classList.add('hidden');
        this.elements.loadingView.classList.remove('active');
        this.elements.connectedView.classList.remove('hidden');
        this.elements.anonymousView.classList.add('hidden');

        // Add active class for animation
        this.elements.connectedView.classList.add('active');
    }

    showAnonymousView() {
        this.elements.loadingView.classList.add('hidden');
        this.elements.loadingView.classList.remove('active');
        this.elements.connectedView.classList.add('hidden');
        this.elements.anonymousView.classList.remove('hidden');

        // Add active class for animation
        this.elements.anonymousView.classList.add('active');
    }

    toggleSettings() {
        this.elements.settingsPopup.classList.toggle('hidden');
    }

    openWebsite() {
        chrome.tabs.create({ url: CONFIG.websiteUrl });
    }

    initiateScreenshot() {
        chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
            if (tabs && tabs[0] && tabs[0].id) {
                // Send message to background script to handle screenshot with injection
                this.sendMessage('captureScreenshot', {
                    tabId: tabs[0].id,
                    request: {
                        question: 'Take a screenshot and analyze',
                        tool: 'screenshot',
                        type: 'global'
                    }
                }, (response) => {
                    if (response && response.success) {
                        // console.log('Screenshot initiated successfully');
                    } else {
                        console.error('Screenshot failed:', response?.error);
                    }
                });
                window.close();
            }
        });
    }

    openLogin() {
        chrome.tabs.create({ url: CONFIG.loginUrl });
    }

    openRegister() {
        chrome.tabs.create({ url: CONFIG.registerUrl });
    }

    updateLanguage(language) {
        this.sendMessage('updateSettings', {
            settings: { language }
        }, (response) => {
            // Re-apply translations after language change
            if (response && response.success && window.applyTranslations) {
                // Small delay to ensure settings are saved
                setTimeout(() => {
                    window.applyTranslations();
                }, 100);
            }
        });
    }

    updateCamouflageMode(enabled) {
        this.sendMessage('updateSettings', {
            settings: { camouflageMode: enabled }
        }, (response) => {
            // console.log('Camouflage mode update response:', response);
        });
    }

    updateScreenshotButton(enabled) {
        this.sendMessage('updateSettings', {
            settings: { screenshotButtonEnabled: enabled }
        }, (response) => {
            if (response && response.success) {
                // Broadcast to all tabs to update button visibility
                chrome.tabs.query({}, (tabs) => {
                    tabs.forEach(tab => {
                        chrome.tabs.sendMessage(tab.id, {
                            type: 'updateSettings',
                            settings: { screenshotButtonEnabled: enabled }
                        }).catch(() => {}); // Ignore errors for tabs without content script
                    });
                });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const popup = new PopupManager();
    popup.init();
});