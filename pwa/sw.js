const CACHE_NAME = 'uas-kte-cache-v1';
const urlsToCache = [
    '/UAS_KTE/index.php',
    '/UAS_KTE/dashboard.php',
    '/UAS_KTE/assets/style.css'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => response || fetch(event.request))
    );
});