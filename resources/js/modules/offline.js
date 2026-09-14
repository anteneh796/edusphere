const WS_CACHE = 'edusphere-v1';

let queuedRecords = [];

function updateNetworkState() {
    document.body.classList.toggle('offline', !navigator.onLine);
    document.dispatchEvent(new CustomEvent('network-change', { detail: { online: navigator.onLine } }));
}

function openQueueDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('edusphere-attendance', 1);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('queue')) {
                db.createObjectStore('queue', { keyPath: 'clientId' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function readQueue() {
    const db = await openQueueDb();
    return new Promise((resolve) => {
        const tx = db.transaction('queue', 'readonly');
        const store = tx.objectStore('queue');
        const rows = [];
        const cursor = store.openCursor();
        cursor.onsuccess = (event) => {
            const c = event.target.result;
            if (c) {
                rows.push(c.value);
                c.continue();
            } else {
                resolve(rows);
            }
        };
        cursor.onerror = () => resolve(rows);
    });
}

async function clearQueue(ids) {
    const db = await openQueueDb();
    return new Promise((resolve) => {
        const tx = db.transaction('queue', 'readwrite');
        const store = tx.objectStore('queue');
        ids.forEach((id) => store.delete(id));
        tx.oncomplete = () => resolve();
    });
}

async function enqueue(record) {
    const db = await openQueueDb();
    return new Promise((resolve) => {
        const tx = db.transaction('queue', 'readwrite');
        const store = tx.objectStore('queue');
        store.put(record);
        tx.oncomplete = () => {
            queuedRecords.push(record);
            window.dispatchEvent(new CustomEvent('attendance-queued', { detail: record }));
            resolve();
        };
    });
}

async function syncQueue() {
    if (!navigator.onLine) {
        return 0;
    }

    const records = await readQueue();
    if (records.length === 0) {
        return 0;
    }

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const response = await fetch('/api/v1/attendance/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ records }),
        });

        if (response.ok) {
            await clearQueue(records.map((r) => r.clientId));
            queuedRecords = queuedRecords.filter((r) => !records.some((queued) => queued.clientId === r.clientId));
            window.dispatchEvent(new CustomEvent('attendance-synced', { detail: { count: records.length } }));
            return records.length;
        }

        return 0;
    } catch (error) {
        return 0;
    }
}

function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Service worker unavailable; app continues with graceful degradation.
        });
    });
}

/** Prime the current page into the app-shell cache so it works offline. */
function primeCurrentPageCache() {
    const marker = document.querySelector('[data-offline-cache]');

    if (!marker || !navigator.onLine || !('caches' in window)) {
        return;
    }

    fetch(window.location.href)
        .then((response) => {
            if (!response.ok) {
                return null;
            }

            return caches.open(WS_CACHE).then((cache) => cache.put(window.location.href, response.clone()));
        })
        .catch(() => {});
}

/** Store a single attendance record to be synced later. */
window.EduOffline = {
    queueAttendance(records) {
        return Promise.all(records.map((record) => enqueue(record))).then(() => syncQueue());
    },

    async pendingCount() {
        return (await readQueue()).length;
    },

    syncQueue,
};

document.addEventListener('DOMContentLoaded', () => {
    registerServiceWorker();
    updateNetworkState();
    primeCurrentPageCache();

    window.addEventListener('online', updateNetworkState);
    window.addEventListener('offline', updateNetworkState);

    window.addEventListener('online', syncQueue);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            syncQueue();
        }
    });
});