// Storage Safety Utilities for Chrome Extension
// Add this to your background.js to prevent storage bloat

class StorageSafety {
    static MAX_STORAGE_SIZE = 5 * 1024 * 1024; // 5MB limit
    static CHECK_INTERVAL = 5 * 60 * 1000; // Check every 5 minutes (instead of 1 minute)
    static LOG_ROTATION_SIZE = 1024 * 1024; // 1MB before rotation
    static isMonitoring = false; // Prevent multiple monitoring instances

    static async monitorStorageUsage() {
        try {
            // Check chrome.storage.local usage
            const usage = await chrome.storage.local.getBytesInUse();
            
            // Reduce verbose logging - only show in development
            // if (usage > 100 * 1024) { // Only log if > 100KB
            //     console.log(`Storage usage: ${(usage / 1024).toFixed(2)} KB`);
            // }

            if (usage > this.MAX_STORAGE_SIZE) {
                console.warn('Storage usage exceeding limit, cleaning up...');
                await this.cleanupStorage();
            }

            // Check extension's local database files
            await this.checkLocalDatabase();

        } catch (error) {
            console.error('Storage monitoring error:', error);
        }
    }

    static async cleanupStorage() {
        try {
            // Keep only essential data
            const essential = await chrome.storage.local.get(['ch_user_session']);
            await chrome.storage.local.clear();
            
            if (essential.ch_user_session) {
                await chrome.storage.local.set(essential);
            }
            
            // Reduce verbose logging
            // console.log('Storage cleaned up');
        } catch (error) {
            console.error('Storage cleanup error:', error);
        }
    }

    static async checkLocalDatabase() {
        // This would require additional permissions to access file system
        // For now, we'll just monitor chrome.storage usage
        // Reduce verbose logging
        // console.log('Database check completed');
    }

    static startMonitoring() {
        // Prevent multiple monitoring instances
        if (this.isMonitoring) {
            // Reduce verbose logging
            // console.log('Storage monitoring already running');
            return;
        }
        
        this.isMonitoring = true;
        
        // Initial check
        this.monitorStorageUsage();
        
        // Periodic checks - reduced frequency
        setInterval(() => {
            this.monitorStorageUsage();
        }, this.CHECK_INTERVAL);
        
        // Reduce verbose logging
        // console.log('Storage monitoring started - checking every 5 minutes');
    }

    // Safe storage operations
    static async safeStorageSet(key, value) {
        try {
            const serialized = JSON.stringify(value);
            
            // Check size before storing
            if (serialized.length > 500000) { // 500KB limit per item
                console.warn(`Large storage item detected: ${key} (${serialized.length} bytes)`);
                
                // Compress or truncate if needed
                if (typeof value === 'object' && value.logs) {
                    // Remove old logs if present
                    delete value.logs;
                }
            }
            
            // Store serialized string to ensure consistent format across codebase
            await chrome.storage.local.set({ [key]: serialized });
            // Reduce verbose logging
            // console.log(`Safely stored: ${key}`);
            
        } catch (error) {
            console.error(`Storage error for ${key}:`, error);
            
            // Fallback: try to store minimal data
                if (typeof value === 'object') {
                    const minimal = {
                        name: value.name,
                        email: value.email,
                        credits: value.credits,
                        isSubscribed: value.isSubscribed
                    };
                    // store as serialized string as fallback
                    await chrome.storage.local.set({ [key]: JSON.stringify(minimal) });
                }
        }
    }
}

// Initialize storage monitoring when extension starts
if (typeof chrome !== 'undefined' && chrome.runtime) {
    chrome.runtime.onStartup.addListener(() => {
        StorageSafety.startMonitoring();
    });
    
    chrome.runtime.onInstalled.addListener(() => {
        StorageSafety.startMonitoring();
    });
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StorageSafety;
}
