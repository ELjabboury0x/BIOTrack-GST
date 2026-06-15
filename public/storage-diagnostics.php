<!-- Storage Diagnostics Helper -->
<script>
// Add to window for console access
window.StorageDiags = {
    showStatus: async function() {
        const estimate = await navigator.storage.estimate();
        const usage = estimate.usage / 1024 / 1024;
        const quota = estimate.quota / 1024 / 1024;
        const percent = ((estimate.usage / estimate.quota) * 100).toFixed(1);
        
        console.log('=== STORAGE STATUS ===');
        console.log('Used: ' + usage.toFixed(2) + ' MB');
        console.log('Quota: ' + quota.toFixed(2) + ' MB');
        console.log('Usage: ' + percent + '%');
        console.log('Status: ' + (percent > 90 ? '🔴 CRITICAL' : percent > 80 ? '🟠 HIGH' : '🟢 NORMAL'));
        console.log('');
        console.log('To cleanup: window.StorageCleanup.fullCleanup()');
        console.log('To clear everything: localStorage.clear(); sessionStorage.clear()');
        
        return { usage: usage.toFixed(2), quota: quota.toFixed(2), percent: percent };
    },
    
    listCaches: async function() {
        const cacheNames = await caches.keys();
        console.log('=== CACHES ===');
        for (const name of cacheNames) {
            const cache = await caches.open(name);
            const keys = await cache.keys();
            console.log(name + ': ' + keys.length + ' entries');
        }
    },
    
    listIndexedDB: async function() {
        console.log('=== INDEXED DB ===');
        try {
            const databases = await indexedDB.databases();
            for (const db of databases) {
                console.log(db.name + ' (v' + db.version + ')');
            }
        } catch(e) {
            console.log('Could not list IndexedDB:', e);
        }
    },
    
    fullDiagnostics: async function() {
        await this.showStatus();
        await this.listCaches();
        await this.listIndexedDB();
    }
};

// Auto-run on console
console.log('%cStorage Diagnostics Available', 'background: #0066cc; color: white; padding: 5px;');
console.log('Run: window.StorageDiags.fullDiagnostics()');
</script>
