/**
 * Storage Cleanup Utility
 * Manages localStorage, sessionStorage, and IndexedDB to prevent quota exceeded errors
 */
(function() {
    'use strict';

    window.StorageCleanup = {
        /**
         * Get estimated storage usage
         */
        getEstimate: async function() {
            try {
                if (navigator.storage && navigator.storage.estimate) {
                    const estimate = await navigator.storage.estimate();
                    const percentUsed = Math.round((estimate.usage / estimate.quota) * 100);
                    return {
                        usage: estimate.usage,
                        quota: estimate.quota,
                        percentUsed: percentUsed
                    };
                }
                return null;
            } catch(e) {
                console.warn('Could not get storage estimate:', e);
                return null;
            }
        },

        /**
         * Clear old cache entries
         */
        clearOldCache: async function() {
            try {
                if (!window.caches) return;
                const names = await caches.keys();
                const now = Date.now();
                const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 days

                for (const name of names) {
                    const cache = await caches.open(name);
                    const keys = await cache.keys();
                    
                    for (const request of keys) {
                        const response = await cache.match(request);
                        if (response) {
                            const dateHeader = response.headers.get('date');
                            if (dateHeader) {
                                const responseTime = new Date(dateHeader).getTime();
                                if (now - responseTime > maxAge) {
                                    await cache.delete(request);
                                    console.log('Deleted old cache entry:', request.url);
                                }
                            }
                        }
                    }
                }
            } catch(e) {
                console.warn('Error clearing old cache:', e);
            }
        },

        /**
         * Clear all IndexedDB databases
         */
        clearIndexedDB: async function() {
            try {
                if (!window.indexedDB) return;

                const databases = await window.indexedDB.databases();
                for (const db of databases) {
                    window.indexedDB.deleteDatabase(db.name);
                    console.log('Deleted IndexedDB:', db.name);
                }
            } catch(e) {
                console.warn('Error clearing IndexedDB:', e);
            }
        },

        /**
         * Clear localStorage, keeping important keys
         */
        clearLocalStorage: function() {
            const importantKeys = ['gst-theme', 'gst-lang', 'auth_token', 'user_id'];
            const toDelete = [];

            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && !importantKeys.includes(key)) {
                    toDelete.push(key);
                }
            }

            toDelete.forEach(key => {
                try {
                    localStorage.removeItem(key);
                    console.log('Removed localStorage key:', key);
                } catch(e) {
                    console.warn('Could not remove localStorage key:', key, e);
                }
            });
        },

        /**
         * Clear sessionStorage
         */
        clearSessionStorage: function() {
            try {
                sessionStorage.clear();
                console.log('Cleared sessionStorage');
            } catch(e) {
                console.warn('Could not clear sessionStorage:', e);
            }
        },

        /**
         * Perform full cleanup
         */
        fullCleanup: async function() {
            console.log('Starting full storage cleanup...');
            const before = await this.getEstimate();
            if (before) {
                console.log('Before cleanup - Usage: ' + (before.usage / 1024 / 1024).toFixed(2) + 'MB / ' + 
                           (before.quota / 1024 / 1024).toFixed(2) + 'MB (' + before.percentUsed + '%)');
            }

            // Execute cleanup operations
            this.clearLocalStorage();
            this.clearSessionStorage();
            await this.clearOldCache();
            await this.clearIndexedDB();

            const after = await this.getEstimate();
            if (after) {
                console.log('After cleanup - Usage: ' + (after.usage / 1024 / 1024).toFixed(2) + 'MB / ' + 
                           (after.quota / 1024 / 1024).toFixed(2) + 'MB (' + after.percentUsed + '%)');
            }

            return {
                before: before,
                after: after,
                cleared: before && after ? (before.usage - after.usage) : 0
            };
        },

        /**
         * Monitor storage usage and auto-cleanup if needed
         */
        startMonitoring: async function() {
            const estimate = await this.getEstimate();
            if (estimate && estimate.percentUsed > 80) {
                console.warn('Storage usage is high (' + estimate.percentUsed + '%). Running cleanup...');
                await this.fullCleanup();
            }
        }
    };

    // Auto-monitor on page load
    document.addEventListener('DOMContentLoaded', function() {
        window.StorageCleanup.startMonitoring().catch(e => console.warn('Storage monitoring error:', e));
    });
})();
