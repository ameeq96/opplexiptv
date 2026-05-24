self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil((async function () {
        if ('caches' in self) {
            const keys = await caches.keys();
            await Promise.all(keys.map(function (key) {
                return caches.delete(key);
            }));
        }

        if (self.clients && self.clients.claim) {
            await self.clients.claim();
        }

        if (self.registration && self.registration.unregister) {
            await self.registration.unregister();
        }
    })());
});
