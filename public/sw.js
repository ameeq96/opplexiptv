self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil((async function () {
        if (self.clients && self.clients.claim) {
            await self.clients.claim();
        }

        if (self.caches && self.caches.keys) {
            var cacheNames = await self.caches.keys();
            await Promise.all(cacheNames.map(function (cacheName) {
                return self.caches.delete(cacheName);
            }));
        }

        if (self.registration && self.registration.unregister) {
            await self.registration.unregister();
        }
    })());
});
