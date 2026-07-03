const CACHE_NAME = 'uas-kte-cache-v4';
const urlsToCache = [
    '/UAS_KTE/assets/style.css'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Jangan cache untuk proses login, callback, atau logout. Biarkan langsung ke server.
    if (url.pathname.includes('logout.php') || url.pathname.includes('callback.php') || url.pathname.includes('register')) {
        return; 
    }

    // Strategi: Network First, Fallback to Cache (Bagus untuk website dinamis berbasis PHP)
    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // Jika sukses mengambil dari jaringan, perbarui cache!
                if (networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Jika offline (jaringan gagal), coba cari di cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Jika di cache juga tidak ada, tampilkan halaman fallback
                    return new Response(
                        '<div style="font-family:sans-serif; text-align:center; padding:50px;">' +
                        '<h2>Koneksi Terputus</h2>' +
                        '<p>Anda sedang offline dan halaman ini belum tersimpan di memori perangkat Anda.</p>' +
                        '<a href="/UAS_KTE/index.php">Kembali ke Beranda</a>' +
                        '</div>',
                        {
                            status: 200,
                            headers: { 'Content-Type': 'text/html' }
                        }
                    );
                });
            })
    );
});
