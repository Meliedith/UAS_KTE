const CACHE_NAME = 'uas-kte-cache-v5';
const urlsToCache = [
    '/UAS_KTE/index.php',
    '/UAS_KTE/assets/style.css',
    '/UAS_KTE/assets/icon-192.png',
    '/UAS_KTE/assets/icon-512.png',
    '/UAS_KTE/pwa/manifest.json'
];

// === INSTALL: Pre-cache assets penting ===
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Pre-caching assets...');
                return cache.addAll(urlsToCache);
            })
            .catch((err) => console.warn('[SW] Pre-cache error:', err))
    );
});

// === ACTIVATE: Hapus cache lama ===
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[SW] Menghapus cache lama:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('[SW] Aktif dan mengklaim klien');
            return self.clients.claim();
        })
    );
});

// === FETCH: Strategi Network-First, Fallback ke Cache ===
self.addEventListener('fetch', (event) => {
    // Hanya tangani GET
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Jangan intercept: logout, callback OAuth, dan endpoint eksternal
    const skipPaths = ['logout.php', 'callback.php', 'accounts.google.com', 'oauth2', 'ngrok'];
    if (skipPaths.some(p => url.href.includes(p))) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // Simpan ke cache jika berhasil dan response valid
                if (
                    networkResponse.status === 200 &&
                    (networkResponse.type === 'basic' || networkResponse.type === 'cors')
                ) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Offline fallback: coba dari cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Halaman fallback offline yang lebih bagus
                    return new Response(
                        `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Offline - KTE Secure</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #0a1628, #1a3a6c);
      color: white;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 20px;
    }
    .card {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 16px;
      padding: 40px 30px;
      max-width: 360px;
      backdrop-filter: blur(10px);
    }
    .icon { font-size: 64px; margin-bottom: 16px; }
    h2 { font-size: 22px; margin-bottom: 10px; }
    p { opacity: 0.8; font-size: 14px; margin-bottom: 24px; line-height: 1.6; }
    a {
      display: inline-block;
      background: #4285f4;
      color: white;
      text-decoration: none;
      padding: 12px 24px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">📡</div>
    <h2>Koneksi Terputus</h2>
    <p>Anda sedang offline dan halaman ini belum tersimpan di cache perangkat Anda. Periksa koneksi internet Anda.</p>
    <a href="/UAS_KTE/index.php">🔄 Coba Lagi</a>
  </div>
</body>
</html>`,
                        {
                            status: 200,
                            headers: { 'Content-Type': 'text/html; charset=utf-8' }
                        }
                    );
                });
            })
    );
});
