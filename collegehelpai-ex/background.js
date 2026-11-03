// Background script for Chrome extension
// Import storage safety utilities
importScripts('lib/storage-safety.js');

// Constants and Configuration
const isProduction = true;
const baseUrl = isProduction ? 'https://collegehelpai.com/' : 'http://college.localhost/';
const CONFIG = {
    API_BASE_URL: `${baseUrl}wp-json/college-help-ai/v1`, // WordPress REST API base URL
    WP_LOGIN_URL: `${baseUrl}login`,// WordPress url for login
    WP_REGISTER_URL: `${baseUrl}registration`, // WordPress url for registration
    WP_PROFILE_URL: `${baseUrl}my-account`, // WordPress url for user profile
    ENDPOINTS: {
        FETCH_ANSWER: '/fetch-answer', // token auth required
        AUTH: {
            USER_DATA: '/user-data', // token auth required
            VALIDATE: 'wp-admin/admin-ajax.php', // use credentials: 'include'
            LOGOUT: '/auth/logout' // use X-WP-Nonce header
        }
    },
    CODEAPP: 'CHAI25_',

};
class User {
    static USER_SESSION = 'ch_user_session';

    constructor() {
        this.name = "";
        this.email = "";
        this.credits = 0;
        this.consumed = 0;         // Daily consumption
        this.lastHour = 0;         // Hourly consumption
        this.totalConsumed = 0;    // Total lifetime consumption
        this.isSubscribed = false;
        this.planType = "";
        this.token = "";
        this.nonce = "";
        this.expiresAt = "";
        this.userId = "";
    }
    // Add this method to your User class
    async loadUser() {
        // First try to load from storage
        const loaded = await this.loadFromStorage();
        
        // If we have data in storage and token is still valid, use it
        if (loaded && this.isLoggedIn()) {
            return this;
        }
        
        // If not in storage or token expired, validate with server
        const api = new APIService(this);
        const isValid = await api.checkValidLogin();

        if (isValid) {
            // Token validated and user data updated via checkValidLogin
            return this;
        }
        // Clear any invalid user data
        await this.clearUserData();
        return null;

    }
    // Load user data from Chrome storage
    // User class
    async loadFromStorage() {
        return new Promise((resolve) => {
            chrome.storage.local.get([User.USER_SESSION], (result) => {
                if (chrome.runtime.lastError) {
                    console.error('❌ [USER-LOAD] Error loading from storage:', chrome.runtime.lastError);
                    resolve(false);
                    return;
                }

                if (result && result[User.USER_SESSION]) {
                    try {
                        let stored = result[User.USER_SESSION];
                   

                        // If storage returns a string (serialized), parse it.
                        // Some older code paths or other tools may have stored an object directly.
                        let userData;
                        if (typeof stored === 'string') {
                            userData = JSON.parse(stored);
                          
                        } else if (typeof stored === 'object') {
                            userData = stored;
                           
                        } else {
                            throw new Error('Unexpected stored user session type: ' + typeof stored);
                        }

                        this.setUserData(userData);
                   
                        resolve(true);
                    } catch (error) {
                        console.error('❌ [USER-LOAD] Error parsing user data from storage:', error);
                        // Attempt a best-effort fallback: if the stored value is an object-like string representation
                        try {
                            const raw = String(result[User.USER_SESSION]);
                            // If it looks like "[object Object]", clear it to avoid blocking login flow
                            if (raw === '[object Object]') {
                                console.warn('⚠️ [USER-LOAD] Found invalid string "[object Object]" in storage, clearing it.');
                                chrome.storage.local.remove([User.USER_SESSION], () => {});
                            }
                        } catch (e) {
                            // ignore
                        }

                        resolve(false);
                    }
                } else {
                    resolve(false);
                }
            });
        });
    }

    // Save user data to Chrome storage
    async saveToStorage() {
        return new Promise(async (resolve) => {
            const userData = this.getUserData();
            
            // Use safe storage to prevent bloat
            if (typeof StorageSafety !== 'undefined') {
                await StorageSafety.safeStorageSet(User.USER_SESSION, userData);
            } else {
                // Fallback to regular storage
                chrome.storage.local.set({ [User.USER_SESSION]: JSON.stringify(userData) }, () => {});
            }
            resolve();
        });
    }

    // Clear user data from Chrome storage
    async clearStorage() {
        return new Promise((resolve) => {
            chrome.storage.local.remove([User.USER_SESSION], () => {
                // Don't call clearUserData here to avoid infinite recursion
                resolve();
            });
        });
    }

    setUserData(data) {
        this.name = data.name || "";
        this.email = data.email || "";
        this.credits = data.credits || 0;
        this.consumed = data.consumed || 0;           // Daily consumption
        this.lastHour = data.lastHour || 0;           // Hourly consumption  
        this.totalConsumed = data.totalConsumed || 0; // Total lifetime consumption
        this.planType = data.planType || "";
        this.token = data.token || "";
        this.nonce = data.nonce || "";
        this.expiresAt = data.expires_at || "";
        this.isSubscribed = data.isSubscribed || false;
        this.userId = data.user_id || "";

        // Only save to storage if data actually changed
        // Remove automatic save to prevent infinite loops
    }

    getUserData() {
        return {
            name: this.name,
            email: this.email,
            credits: this.credits,
            consumed: this.consumed,           // Daily consumption
            lastHour: this.lastHour,           // Hourly consumption
            totalConsumed: this.totalConsumed, // Total lifetime consumption
            planType: this.planType,
            token: this.token,
            nonce: this.nonce,
            expiresAt: this.expiresAt,
            isSubscribed: this.isSubscribed,
            userId: this.userId
        };
    }

    clearUserData() {
        this.name = "";
        this.email = "";
        this.credits = 0;
        this.consumed = 0;         // Reset daily consumption
        this.lastHour = 0;         // Reset hourly consumption
        this.totalConsumed = 0;    // Reset total lifetime consumption
        this.planType = "";
        this.token = "";
        this.nonce = "";
        this.expiresAt = "";
        this.isSubscribed = false;
        this.userId = "";

        // Clear from storage - but don't await to avoid blocking
        // This will clear the storage without creating recursion
        chrome.storage.local.remove([User.USER_SESSION], () => {});
    }

    updateField(field, value) {
        if (this.hasOwnProperty(field)) {
            this[field] = value;
            // Only save when explicitly updating a field
            this.saveToStorage();
            return true;
        }
        return false;
    }

    isLoggedIn() {
        return !!this.token && this.isTokenValid();
    }

    isTokenValid() {
        return checkTokenValidity(this);
    }

    // Get token for API requests
    getAuthHeader() {
        if (!this.isTokenValid()) return null;
        return {
            'Authorization': `Bearer ${this.token}`,
            'X-WP-Nonce': this.nonce
        };
    }
    async refreshToken() {
        if (this.token && this.expiresAt) {
            const expiresDate = new Date(this.expiresAt);
            const now = new Date();
            const timeRemaining = expiresDate.getTime() - now.getTime();

            // If token expires in less than 5 minutes, refresh it
            if (timeRemaining > 0 && timeRemaining < 5 * 60 * 1000) {
                try {
                    const api = new APIService(this);
                    await api.getUserData(true); // Pass true to trigger token refresh
                    return true;
                } catch (error) {
                    console.error('Failed to refresh token:', error);
                    return false;
                }
            }
        }
        return false;
    }
    async handleCheckSession(sendResponse) {
        // First try to check from storage
        const loaded = await this.loadFromStorage();
        
        // If we have data in storage and token is valid, use it
        if (loaded && this.isLoggedIn()) {
            sendResponse({
                success: true,
                isLoggedIn: true,
                userData: this.getUserData()
            });
            return;
        }

        // Otherwise, validate with server
        const api = new APIService(this);
        const isValid = await api.checkValidLogin();
        if (isValid) {
            sendResponse({
                success: true,
                isLoggedIn: true,
                userData: this.getUserData()
            });
        } else {
            sendResponse({
                success: true,
                isLoggedIn: false,
                reason: 'token_expired'
            });
        }
    }

}
class Settings {
    static USER_SETTINGS = 'ch_user_settings';
    static SYNC_POSITION = 'ch_answer_position';

    constructor() {
        this.language = "en"; // Default language
        this.camouflageMode = false;
        this.position = { right: '20px', top: '50%', transform: 'translateY(-50%)' };
        this.displayQuestion = false;
        this.screenshotButtonEnabled = true; // Screenshot button active by default
    }

    // Load settings from Chrome storage
    async loadFromStorage() {
        return new Promise((resolve) => {
                chrome.storage.sync.get([Settings.USER_SETTINGS], (result) => {
                    try {
                        if (result[Settings.USER_SETTINGS]) {
                            let stored = result[Settings.USER_SETTINGS];
                            let settingsObj = null;
                            if (typeof stored === 'string') {
                                settingsObj = JSON.parse(stored);
                            } else if (typeof stored === 'object') {
                                settingsObj = stored;
                            }

                            if (settingsObj) {
                                this.setSettings(settingsObj);
                                resolve(true);
                                return;
                            }
                        }
                        // Use defaults if no settings found
                        resolve(false);
                    } catch (e) {
                        console.error('Error parsing settings from storage:', e);
                        resolve(false);
                    }
                });
        });
    }

    // Save settings to Chrome storage
    async saveToStorage() {
        return new Promise((resolve) => {
            const settings = this.getSettings();
            chrome.storage.sync.set({ [Settings.USER_SETTINGS]: JSON.stringify(settings) }, () => {
                resolve();
            });
        });
    }

    // Reset settings to defaults and clear from storage
    async resetToDefaults() {
        return new Promise((resolve) => {
            this.clearSettings();
            chrome.storage.sync.remove([Settings.USER_SETTINGS], () => {
                resolve();
            });
        });
    }

    setSettings(data) {
        this.language = data.language || "en";
        this.camouflageMode = data.camouflageMode !== undefined ? data.camouflageMode : false;
        this.position = data.position || { right: '20px', top: '50%', transform: 'translateY(-50%)' };
        this.screenshotButtonEnabled = data.screenshotButtonEnabled !== undefined ? data.screenshotButtonEnabled : true;
        // Remove automatic save to prevent excessive writes
    }

    getSettings() {
        return {
            language: this.language,
            camouflageMode: this.camouflageMode,
            position: this.position,
            screenshotButtonEnabled: this.screenshotButtonEnabled
        };
    }

    clearSettings() {
        this.language = "en";
        this.camouflageMode = false;
        this.position = { right: '20px', top: '50%', transform: 'translateY(-50%)' };
    }

    updateField(field, value) {
        if (this.hasOwnProperty(field)) {
            this[field] = value;
            // Remove automatic save to prevent excessive writes
            return true;
        }
        return false;
    }

    updatePosition(data) {
        if (data) {
            this.position.right = data.right || this.position.right;
            this.position.top = data.top || this.position.top;
            this.position.transform = data.transform || this.position.transform;

            // Optionally sync position separately if it changes frequently
            this.syncPosition();

            // Remove automatic save - only save when explicitly needed
        }
    }

    // Special method to sync just the position (useful if position changes frequently)
    async syncPosition() {
        return new Promise((resolve) => {
            chrome.storage.sync.set({ [Settings.SYNC_POSITION]: JSON.stringify(this.position) }, () => {
                // Reduce logging verbosity  
                // console.log('Position synced to storage:', this.position);
                resolve();
            });
        });
    }

    // Load just the position setting
    async loadPosition() {
        return new Promise((resolve) => {
                chrome.storage.sync.get([Settings.SYNC_POSITION], (result) => {
                    try {
                        if (result[Settings.SYNC_POSITION]) {
                            const stored = result[Settings.SYNC_POSITION];
                            if (typeof stored === 'string') {
                                this.position = JSON.parse(stored);
                            } else if (typeof stored === 'object') {
                                this.position = stored;
                            }
                        }
                        resolve(true);
                    } catch (e) {
                        console.error('Error parsing sync position from storage:', e);
                        resolve(false);
                    }
                });
        });
    }
}
class CroppedImage {
    constructor(data = null) {
        if (data) {
            this.x = data.x || 0;
            this.y = data.y || 0;
            this.width = data.width || 0;
            this.height = data.height || 0;
            this.devicePixelRatio = data.devicePixelRatio || 1;
        }
    }
}
class RequestAnswer {
    constructor(data = null) {
        if (data) {
            this.id = data.id || '';
            this.question = data.question || '';
            this.tool = data.tool || 'select'; // value in  ['select', 'image', 'base64']
            this.type = data.type || 'global'; // value in ['global', 'math', explain', 'describe_image', 'translate',extract_text']
            this.image = data.image || '';
            this.language = data.language || chrome.i18n.getUILanguage();
            this.base64 = data.base64 || '';
            this.user_id = data.user_id;
            this.isImage = data.isImage || false;
            this.fromUrl = data.fromUrl || false;
            // Cropped image data if tool is 'image' and isImage is true
            this.croppedImage = new CroppedImage(data.croppedImage);
        } else {
            this.question = '';
            this.tool = ''; // value in  ['select', 'image', 'base64']
            this.type = ''; // value in ['global', 'math', explain', 'describe_image', 'translate',extract_text']
            this.image = '';
            this.language = '';
            this.user_id = '';
            this.base64 = '';
            this.isImage = false;
            this.fromUrl = false;
            this.croppedImage = new CroppedImage();
        }

        this.version = chrome.runtime.getManifest().version;

    }
    getZoomLevel() {
        return window.devicePixelRatio || 1;
    }
}
class ResponseAnswer {
    constructor(data = null) {
        if (data) {
            this.id = data.id || '';
            this.question = data.question || '';
            this.image = data.image || '';
            this.answer = data.answer || '';
            this.error = data.error || '';
            this.success = data.success || false;
            this.user_id = data.user_id || '';

            if (data.success && (!data.answer || data.answer.length == 0)) {
                this.success = false;
                this.error = chrome.i18n.getMessage('noAnswerFound');
            }
        } else {
            this.id = '';
            this.question = '';
            this.image = '';
            this.answer = '';
            this.error = '';
            this.success = false;
            this.user_id = '';
        }
    }
}
// API Service Class
class APIService {
    constructor(user) {
        this.baseURL = CONFIG.API_BASE_URL;
        this.user = user || new User();
    }

    // Set user instance for authentication
    setUser(user) {
        this.user = user;
    }

    // Helper method to get auth headers
    getAuthHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };

        if (this.user && this.user.isLoggedIn()) {
            const authData = this.user.getAuthHeader();
            if (authData) {
                headers['Authorization'] = authData.Authorization;
                if (authData['X-WP-Nonce']) {
                    headers['X-WP-Nonce'] = authData['X-WP-Nonce'];
                }
            }
        }

        return headers;
    }

    // Check if user is authenticated before making requests
    ensureAuthenticated() {
        if (!this.user || !this.user.isLoggedIn()) {
            throw new Error(chrome.i18n.getMessage('loginRequiredMessage'));
        }
    }

    async fetchAnswer(requestData) {
        try {
            // Ensure user is authenticated
            this.ensureAuthenticated();

            // Create a proper request based on the requestAnswer instance
            // todo  fix type requestData always be RequestAnswer not object
            if (!(requestData instanceof RequestAnswer)) {
                requestData = new RequestAnswer(requestData);
            }

            // Add user_id from authenticated user
            requestData.user_id = this.user.userId;

            const response = await fetch(`${this.baseURL}${CONFIG.ENDPOINTS.FETCH_ANSWER}`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                body: JSON.stringify(requestData)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `API Error: ${response.status}`);
            }

            const data = await response.json();

            // Create response using your updated constructor
            return new ResponseAnswer(data);

        } catch (error) {
            console.error('Error fetching answer:', error);

            // Return error in responseAnswer format
            return new ResponseAnswer({
                question: requestData?.question || '',
                error: error.message,
                success: false
            });
        }
    }

    async getUserData(refreshToken = false) {
        try {
            // Ensure user is authenticated
            this.ensureAuthenticated();

            const response = await fetch(`${this.baseURL}${CONFIG.ENDPOINTS.AUTH.USER_DATA}`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                body: JSON.stringify({
                    user_id: this.user.userId,
                    refreshToken: refreshToken
                })
            });

            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }

            const userData = await response.json();

            // Update user instance with fresh data
            if (userData && userData.success) {
                this.user.setUserData(userData.data);
                // Explicitly save after API update
                await this.user.saveToStorage();
            }

            return userData;
        } catch (error) {
            console.error('Error fetching user data:', error);
            throw error;
        }
    }

    async checkValidLogin() {
        try {
             
            const response = await fetch(`${baseUrl}${CONFIG.ENDPOINTS.AUTH.VALIDATE}`, {
                method: 'POST',
                credentials: 'include',  // Include cookies for authentication
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'validate_my_session'
                })
            }); 

            if (!response.ok) {
                // Clear any existing user data if validation fails
                if (this.user) {
                    this.user.clearUserData();
                }
                return false;
            }

            const result = await response.json();

            // If login is valid and we get user data, update the user instance
            if (result && result.success && result.data) {
                this.user.setUserData(result.data);
                // Explicitly save after login validation
                await this.user.saveToStorage();
                return true;
            }

            // Clear user data if validation succeeded but no data returned
            if (this.user) {
                this.user.clearUserData();
            }

            return false;
        } catch (error) {
            console.error('❌ [AUTH-CHECK] Authentication check failed:', error);
            if (this.user) {
                this.user.clearUserData();
            }
            return false;
        }
    }

    async logout() {
        try {
            // Only attempt logout if we have a token
            if (!this.user || !this.user.isLoggedIn()) {
                if (this.user) {
                    this.user.clearUserData();
                }
                return true;
            }

            // Call WordPress logout endpoint
            const response = await fetch(`${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.AUTH.LOGOUT}`, {
                method: 'POST',
                credentials: 'include',
                headers: this.getAuthHeaders()
            });

            // Clear user data regardless of server response
            this.user.clearUserData();

            return response.ok;
        } catch (error) {
            console.error('WordPress logout error:', error);
            // Still clear local data even if server logout fails
            if (this.user) {
                this.user.clearUserData();
            }
            return false;
        }
    }
}

// Context Menu Manager Class
class ContextMenuManager {
    static initialize() {
        // Remove existing context menu if it exists
        chrome.contextMenus.removeAll(() => {
            // Create the context menu
            chrome.contextMenus.create({
                id: 'askCollegeHelpAi',
                title: chrome.i18n.getMessage('contextMenuAsk'),
                contexts: ['selection']
            });
        });
    }

    static async handleContextClick(info, tab) {
        try {

            if (!tab || tab.id < 0) {
                const tabs = await chrome.tabs.query({ active: true, currentWindow: true });
                if (tabs.length > 0) {
                    tab = tabs[0];
                } else {
                    console.error("No active tab found");
                    return;
                }
            }

            // Ensure content script is injected first
            const injected = await CommandHandler.ensureContentScriptInjected(tab.id);
            if (!injected) {
                console.error('Could not inject content script into tab');
                return;
            }
    
            chrome.tabs.sendMessage(tab.id, { type: 'showLoading' });
            // First, get the user instance from storage
            const user = new User();
            await user.loadUser();

            // Check if user is logged in and token is valid
            if (!user.isLoggedIn()) {
                chrome.tabs.sendMessage(tab.id, { 
                    type: 'showToast',
                    text: chrome.i18n.getMessage('loginRequired'),
                    duration: 3000,
                    status: 'error'
                 });
                return;
            }

            // Create API service with the user
            const api = new APIService(user);

            if (info.selectionText) {
                const request = new RequestAnswer({
                    question: info.selectionText,
                    tool: 'select',
                    type: 'global',
                    user_id: user.userId,
                    language: await ContextMenuManager.getLanguageSetting()
                });

                const answer = await api.fetchAnswer(request);

                chrome.tabs.sendMessage(tab.id, {
                    type: 'displayAnswer',
                    data: answer
                });
            }
        } catch (error) {
           
            chrome.tabs.sendMessage(tab.id, {
                type: 'showToast',
                text: error.message || 'Failed to process request',
                duration: 3000,
                status: 'error'

            });
        }
    }

    // Helper method to get language setting
    static async getLanguageSetting() {
        return new Promise((resolve) => {
            const settings = new Settings();
            settings.loadFromStorage().then(() => {
                resolve(settings.language || chrome.i18n.getUILanguage());
            }).catch(() => {
                resolve(chrome.i18n.getUILanguage());
            });
        });
    }
}

// Command Handler Class
class CommandHandler {
    static async ensureContentScriptInjected(tabId) {
        try {
            // Reduce verbose logging
            // console.log(`Checking if content script is injected in tab ${tabId}`);
            // Try to ping the content script with a timeout
            const response = await Promise.race([
                chrome.tabs.sendMessage(tabId, { type: 'ping' }),
                new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 1000))
            ]);
            
            if (response && response.success) {
                // Reduce verbose logging
                // console.log(`Content script already injected in tab ${tabId}`);
                return true;
            }
        } catch (error) {
            // Reduce verbose logging
            // console.log(`Content script not available in tab ${tabId}, need to inject:`, error.message);
        }

        try {
            // Reduce verbose logging
            // console.log(`Injecting content script into tab ${tabId}`);
            
            // Check if we can inject into this tab
            const tab = await chrome.tabs.get(tabId);
            if (tab.url.startsWith('chrome://') || 
                tab.url.startsWith('chrome-extension://') || 
                tab.url.startsWith('edge://') ||
                tab.url.startsWith('moz-extension://')) {
                // Reduce verbose logging
                // console.log(`Cannot inject into system tab: ${tab.url}`);
                return false;
            }

            // Inject CSS first
            await chrome.scripting.insertCSS({
                target: { tabId: tabId },
                files: ['lib/content.css']
            });

            // Then inject JavaScript files
            await chrome.scripting.executeScript({
                target: { tabId: tabId },
                files: ['lib/tex-mml-chtml.js', 'lib/content.js']
            });

            // Wait a moment for initialization
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Verify injection worked
            try {
                const verifyResponse = await chrome.tabs.sendMessage(tabId, { type: 'ping' });
                if (verifyResponse && verifyResponse.success) {
                    // Reduce verbose logging
                    // console.log(`Content script successfully injected and verified in tab ${tabId}`);
                    return true;
                } else {
                    console.error(`Content script injection verification failed in tab ${tabId}`);
                    return false;
                }
            } catch (verifyError) {
                console.error(`Content script injection verification failed in tab ${tabId}:`, verifyError);
                return false;
            }
            
        } catch (error) {
            console.error(`Failed to inject content script into tab ${tabId}:`, error);
            return false;
        }
    }

    static async handleCommand(command, tab) {
        try {
            // Reduce verbose logging
            // console.log(`Handling command: ${command} for tab ${tab.id}`);
            
            // Ensure content script is injected first
            const injected = await CommandHandler.ensureContentScriptInjected(tab.id);
            if (!injected) {
                console.error('Could not inject content script into tab');
                // Try to show a notification or alert to the user
                try {
                    chrome.action.setBadgeText({text: "!"});
                    chrome.action.setBadgeBackgroundColor({color: "#ff0000"});
                    setTimeout(() => {
                        chrome.action.setBadgeText({text: ""});
                    }, 3000);
                } catch (badgeError) {
                    console.error('Could not set badge:', badgeError);
                }
                return;
            }

            // Load user data from storage
            const user = new User();
            await user.loadUser();

            // Check if user is logged in with valid token
            if (!user.isLoggedIn()) {
                chrome.tabs.sendMessage(tab.id, { 
                    type: 'showToast',
                    text: chrome.i18n.getMessage('loginRequired'),
                    duration: 3000,
                    status: 'error'
                });
                return;
            }

            // Load settings to pass along with commands
            const settings = new Settings();
            await settings.loadFromStorage();

            switch (command) {
                case 'ask_collegehelp':
                    chrome.scripting.executeScript({
                        target: { tabId: tab.id },
                        func: () => window.getSelection().toString()
                    }, (injectionResults) => {
                        if (chrome.runtime.lastError || !injectionResults || !injectionResults[0].result) {
                            chrome.tabs.sendMessage(tab.id, {
                                type: 'showToast',
                                text: 'No text selected',
                                duration: 3000,
                                status: 'error'
                            });
                            return;
                        }
                
                        ContextMenuManager.handleContextClick({ selectionText: injectionResults[0].result }, tab);
                    });

                    break;

                case 'take_screenshot':
                    chrome.tabs.sendMessage(tab.id, {
                        type: 'initiateScreenshot',
                        settings: settings.getSettings()
                    });
                    break;

                default:
                    console.warn(`Unknown command: ${command}`);
            }
        } catch (error) {
            // console.error('Error handling command:', error);
            chrome.tabs.sendMessage(tab.id, {
                type: 'showToast',
                text: error.message || 'Failed to process command',
                duration: 3000,
                status: 'error'
            });
        }
    }

    // Helper method to check if a tab is compatible with our extension
    static isCompatibleTab(tab) {
        // Skip chrome:// pages, extension pages, etc.
        return tab && tab.url &&
            !tab.url.startsWith('chrome://') &&
            !tab.url.startsWith('chrome-extension://') &&
            !tab.url.startsWith('about:');
    }
}

// Global variables for extension state
let user, settings, api;

// Set up message listeners immediately (before async initialization)
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    // Reduce verbose logging
    // console.log('Background received message:', message.action);
    
    if (message.action === CONFIG.CODEAPP + "checkSession") {
        if (user) {
            user.handleCheckSession(sendResponse);
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "getUserData") {
        if (user) {
            // First try to load from storage
            user.loadFromStorage().then((loaded) => {
                // If user is logged in, return the data immediately
                if (loaded && user.isLoggedIn()) {
                    sendResponse({ success: true, data: user.getUserData() });
                } else {
                    // If not in storage or not logged in, validate with server
                    user.loadUser().then(() => {
                        sendResponse({ success: true, data: user.getUserData() });
                    }).catch((error) => {
                        sendResponse({ success: false, error: error.message });
                    });
                }
            }).catch((error) => {
                sendResponse({ success: false, error: error.message });
            });
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "captureScreenshot") {
        if (user) {
            // Handle screenshot with dynamic injection
            handleScreenshotWithInjection(message, user, sendResponse);
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "getSettings") {
        if (settings) {
            settings.loadFromStorage().then(() => {
                sendResponse({ success: true, data: settings.getSettings() });
            }).catch((error) => {
                sendResponse({ success: false, error: error.message });
            });
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "updateSettings") {
        if (settings) {
            settings.loadFromStorage().then(() => {
                settings.setSettings(message.settings);
                return settings.saveToStorage();
            }).then(() => {
                sendResponse({ success: true });
            }).catch((error) => {
                sendResponse({ success: false, error: error.message });
            });
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "logout") {
        if (user) {
            user.loadUser().then(() => {
                const api = new APIService(user);
                return api.logout();
            }).then(() => {
                sendResponse({ success: true });
            }).catch((error) => {
                sendResponse({ success: false, error: error.message });
            });
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }
    else if (message.action === CONFIG.CODEAPP + "getAnswer") {
        if (user) {
            user.loadUser().then(() => {
                const api = new APIService(user);
                const request = message.request instanceof RequestAnswer ? message.request : new RequestAnswer(message.request);
                return api.fetchAnswer(request);
            }).then((answer) => {
                sendResponse({ success: true, data: answer });
            }).catch((error) => {
                sendResponse({ success: false, error: error.message });
            });
        } else {
            sendResponse({ success: false, error: 'Extension not initialized yet' });
        }
        return true;
    }

    return false;
});

// Initialize Extension
async function initializeExtension() {
    try {
        // Reduce verbose logging
        // console.log('Initializing extension...');
        
        // Initialize user and settings objects
        user = new User();
        settings = new Settings();

        // Load saved data
        await Promise.all([
            user.loadUser(),
            settings.loadFromStorage()
        ]);

        // Create API service with user context
        api = new APIService(user);

        // Initialize context menus
        ContextMenuManager.initialize();

        // Set up event listeners
        chrome.contextMenus.onClicked.addListener(ContextMenuManager.handleContextClick);
        chrome.commands.onCommand.addListener(CommandHandler.handleCommand);
    } catch (error) {
        console.error('Failed to initialize extension:', error);
        // Still create basic objects so the extension doesn't completely fail
        user = new User();
        settings = new Settings();
        api = new APIService(user);
    }
}

// Handle installation/update
chrome.runtime.onInstalled.addListener(async ({ reason }) => {
    if (reason === 'install') {
        // Set default settings (initialize settings if needed)
        if (!settings) {
            settings = new Settings();
        }
        settings.setSettings({
            language: chrome.i18n.getUILanguage(),
            camouflageMode: false,
            position: { right: '20px', top: '50%', transform: 'translateY(-50%)' }
        });
        await settings.saveToStorage();

        // Inject content scripts into all existing tabs
        await injectContentScriptsIntoAllTabs();

        // Open onboarding page
        chrome.tabs.create({ url: 'onboarding.html' });
    } else if (reason === 'update') {
        // Check for version-specific upgrades
        const manifest = chrome.runtime.getManifest();

        // Inject content scripts into all existing tabs after update
        await injectContentScriptsIntoAllTabs();

        // Optional: update settings format if needed for version migrations
        // await migrateSettingsIfNeeded(settings);
    }
});

// Helper function to inject content scripts into all existing tabs
async function injectContentScriptsIntoAllTabs() {
    try {
        const tabs = await chrome.tabs.query({});
        
        for (const tab of tabs) {
            // Skip system pages and invalid URLs
            if (!tab.url || 
                tab.url.startsWith('chrome://') || 
                tab.url.startsWith('chrome-extension://') ||
                tab.url.startsWith('edge://') ||
                tab.url.startsWith('about:') ||
                tab.url.startsWith('moz-extension://')) {
                continue;
            }

            try {
                // Test if content script already exists
                await chrome.tabs.sendMessage(tab.id, { type: 'ping' });
                // If successful, content script is already there
            } catch (error) {
                // Content script not present, inject it
                try {
                    await chrome.scripting.insertCSS({
                        target: { tabId: tab.id },
                        files: ['lib/content.css']
                    });
                    
                    await chrome.scripting.executeScript({
                        target: { tabId: tab.id },
                        files: ['lib/tex-mml-chtml.js', 'lib/content.js']
                    });
                } catch (injectError) {
                    // Silent fail for tabs that can't be injected
                    // (e.g., Chrome Web Store pages, some restricted pages)
                }
            }
        }
    } catch (error) {
        console.error('Error injecting content scripts into tabs:', error);
    }
}

// Periodically validate session if user is logged in
// setInterval(async () => {
//     if (user.isLoggedIn()) {
//         await api.checkValidLogin();
//     }
// }, 30 * 60 * 1000); // Check every 30 minutes

async function handleScreenshotWithInjection(message, user, sendResponse) {
    try {
        // Get the current tab or use provided tabId
        let currentTab;
        if (message.tabId) {
            currentTab = await chrome.tabs.get(message.tabId);
        } else {
            const tabs = await chrome.tabs.query({ active: true, currentWindow: true });
            currentTab = tabs[0];
        }

        if (!currentTab) {
            sendResponse({ success: false, error: 'No active tab found' });
            return;
        }

        // Reduce verbose logging
        // console.log(`Handling screenshot for tab ${currentTab.id}: ${currentTab.url}`);

        // Ensure content script is injected first
        const injected = await CommandHandler.ensureContentScriptInjected(currentTab.id);
        if (!injected) {
            sendResponse({ success: false, error: 'Could not inject content script into tab' });
            return;
        }

        // Check if this is from popup (needs overlay) or content script (already selected)
        const isFromPopup = message.request && message.request.tool === 'screenshot';
        const hasImageData = message.request && message.request.isImage;

        if (isFromPopup && !hasImageData) {
            // This is from popup button - need to initiate screenshot overlay
            try {
                await chrome.tabs.sendMessage(currentTab.id, { 
                    type: 'initiateScreenshot',
                    settings: settings ? settings.getSettings() : {}
                });
                // Don't process screenshot yet - wait for content script to send back with image data
                sendResponse({ success: true, message: 'Screenshot overlay initiated' });
            } catch (messageError) {
                console.error('Failed to send screenshot message:', messageError);
                sendResponse({ success: false, error: 'Failed to communicate with content script' });
            }
        } else {
            // This is from content script with image data - process directly
            handleProcessScreenshot(message, user, sendResponse);
        }

    } catch (error) {
        console.error('Screenshot with injection failed:', error);
        sendResponse({ success: false, error: error.message });
    }
}

function handleProcessScreenshot(message, user, sendResponse) {
    try {

        // Get the current tab
        chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
            const currentTab = tabs[0];
            if (currentTab) {
                // Capture the visible area of the current tab
                chrome.tabs.captureVisibleTab(currentTab.windowId, { format: 'png' }, (dataUrl) => {
                    if (chrome.runtime.lastError) {
                        console.error('Capture error:', chrome.runtime.lastError);
                        sendResponse({ success: false, error: chrome.runtime.lastError.message });
                        return;
                    }
                    // Send the screenshot data URL back to the content script
                    // sendResponse({ success: true, dataUrl: dataUrl });
                    user.loadUser().then(() => {
                        const api = new APIService(user);
                        const request = new RequestAnswer(message.request);
                        request.base64 = dataUrl;
                        return api.fetchAnswer(request);
                    }).then((answer) => {
                        sendResponse(answer);
                    }).catch((error) => {
                        sendResponse({ success: false, error: error.message });
                    });
                });
            } else {
                console.error('No active tab found');
                sendResponse({ success: false, error: 'No active tab found' });
            }
        });
    } catch (error) {
        console.error('Error processing screenshot:', error);
        sendResponse({ success: false, error: error.message });
    }

    // Return true to indicate we want to send a response asynchronously
    // return true;
}
function handleCheckSession(sendResponse) {
    // Get user session from storage
    chrome.storage.local.get(['ch_user_session'], (result) => {
        if (chrome.runtime.lastError) {
            console.error('Storage error:', chrome.runtime.lastError);
            sendResponse({
                success: false,
                error: chrome.runtime.lastError.message,
                isLoggedIn: false
            });
            return;
        }

        let userSession = null;
        if (result.ch_user_session) {
            try {
                if (typeof result.ch_user_session === 'string') {
                    userSession = JSON.parse(result.ch_user_session);
                } else if (typeof result.ch_user_session === 'object') {
                    userSession = result.ch_user_session;
                } else {
                    userSession = null;
                }
            } catch (e) {
                console.error('Error parsing ch_user_session in handleCheckSession:', e);
                // Clean up invalid stored value to prevent repeated errors
                if (String(result.ch_user_session) === '[object Object]') {
                    chrome.storage.local.remove(['ch_user_session'], () => {});
                }
                userSession = null;
            }
        }
    

        if (!userSession || !userSession.token) {
            sendResponse({
                success: true,
                isLoggedIn: false
            });
            return;
        }

        // Check if token is valid (not expired)
        const isValid = checkTokenValidity(userSession);

        if (isValid) {
            sendResponse({
                success: true,
                isLoggedIn: true,
                userData: userSession
            });
        } else {
            sendResponse({
                success: true,
                isLoggedIn: false,
                reason: 'token_expired'
            });
        }
    });
}

// Helper function to check token validity
function checkTokenValidity(userSession) {
    if (!userSession || !userSession.expiresAt) {
        return false;
    }

    const expirationDate = new Date(userSession.expiresAt * 1000); // Convert Unix timestamp to milliseconds
    const now = new Date();

    // Return true if token hasn't expired yet
    return expirationDate > now;
}
// Helper function to handle settings migrations between versions
async function migrateSettingsIfNeeded(settings) {
    // Example: if we need to add new fields or change format
    const currentSettings = settings.getSettings();

    // Add any missing default values for new settings
    if (currentSettings.newFeatureFlag === undefined) {
        currentSettings.newFeatureFlag = false;
        settings.setSettings(currentSettings);
        await settings.saveToStorage();
    }
}

// Start the extension
initializeExtension().catch(error => {
    console.error('Failed to initialize extension:', error);
});

// Initialize storage monitoring
chrome.runtime.onStartup.addListener(() => {
    if (typeof StorageSafety !== 'undefined') {
        StorageSafety.startMonitoring();
    }
});

chrome.runtime.onInstalled.addListener(() => {
    if (typeof StorageSafety !== 'undefined') {
        StorageSafety.startMonitoring();
    }
});

