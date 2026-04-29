import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Composable for offline sync using IndexedDB.
 * Queues progress log submissions when offline and syncs when connection is restored.
 */
export function useOfflineSync() {
    const isOnline = ref(navigator.onLine);
    const pendingCount = ref(0);
    const isSyncing = ref(false);

    const DB_NAME = 'kebunku-offline';
    const STORE_NAME = 'pending-logs';
    const DB_VERSION = 1;

    function openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'offline_uuid' });
                }
            };
        });
    }

    async function queueLog(logData) {
        const uuid = crypto.randomUUID();
        const entry = { ...logData, offline_uuid: uuid, created_at: new Date().toISOString() };

        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        tx.objectStore(STORE_NAME).add(entry);
        await new Promise((resolve, reject) => {
            tx.oncomplete = resolve;
            tx.onerror = reject;
        });

        pendingCount.value++;

        // Try to sync immediately if online
        if (isOnline.value) {
            syncPendingLogs();
        }

        return uuid;
    }

    async function getPendingLogs() {
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const request = store.getAll();
        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function removePendingLog(uuid) {
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        tx.objectStore(STORE_NAME).delete(uuid);
        await new Promise((resolve, reject) => {
            tx.oncomplete = resolve;
            tx.onerror = reject;
        });
        pendingCount.value = Math.max(0, pendingCount.value - 1);
    }

    async function syncPendingLogs() {
        if (isSyncing.value || !isOnline.value) return;

        isSyncing.value = true;
        try {
            const logs = await getPendingLogs();
            if (logs.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch('/api/sync/progress-logs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ logs }),
            });

            if (response.ok) {
                const data = await response.json();
                for (const result of data.results) {
                    if (result.status === 'created' || result.status === 'duplicate') {
                        await removePendingLog(result.offline_uuid);
                    }
                }
            }
        } catch (err) {
            console.warn('Sync failed, will retry later:', err);
        } finally {
            isSyncing.value = false;
        }
    }

    async function updatePendingCount() {
        try {
            const logs = await getPendingLogs();
            pendingCount.value = logs.length;
        } catch { pendingCount.value = 0; }
    }

    function handleOnline() { isOnline.value = true; syncPendingLogs(); }
    function handleOffline() { isOnline.value = false; }

    onMounted(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        updatePendingCount();
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    });

    return { isOnline, pendingCount, isSyncing, queueLog, syncPendingLogs, getPendingLogs };
}
