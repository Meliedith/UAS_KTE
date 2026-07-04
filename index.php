<?php
// ============================================================
// FILE: index.php
// FUNGSI: Halaman login dengan dua pilihan peran
// ============================================================

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'vendor/autoload.php';
require_once 'config/env.php';

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost/adit/callback.php');
$client->addScope("email");
$client->addScope("profile");

// Buat dua URL dengan state berbeda
$client->setState('admin');
$login_url_admin = $client->createAuthUrl();
$client->setState('user');
$login_url_user = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - Sistem Keamanan Transaksi</title>
    <meta name="description" content="Sistem Keamanan Transaksi berbasis OTP, Fingerprint, dan Google OAuth">
    <!-- PWA Manifest -->
    <link rel="manifest" href="pwa/manifest.json">
    <!-- Theme Color -->
    <meta name="theme-color" content="#4285f4">
    <meta name="msapplication-TileColor" content="#0a1628">
    <meta name="msapplication-TileImage" content="/adit/assets/icon-192.png">
    <!-- iOS PWA Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="KTE Secure">
    <link rel="apple-touch-icon" href="/adit/assets/icon-192.png">
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="192x192" href="/adit/assets/icon-192.png">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* === PWA Install Banner === */
        #pwa-install-banner {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(135deg, #0a1628 0%, #1a3a6c 100%);
            color: white;
            padding: 16px 20px;
            z-index: 9999;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }
        #pwa-install-banner.show { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        #pwa-install-banner .banner-icon { font-size: 32px; flex-shrink: 0; }
        #pwa-install-banner .banner-text { flex: 1; min-width: 0; }
        #pwa-install-banner .banner-text strong { display: block; font-size: 15px; }
        #pwa-install-banner .banner-text span { font-size: 12px; opacity: 0.8; }
        #pwa-install-banner .banner-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-install {
            background: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-install:hover { background: #2c6fdb; transform: scale(1.03); }
        .btn-dismiss {
            background: transparent;
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-dismiss:hover { background: rgba(255,255,255,0.1); color: white; }
        /* Floating install button (always visible if not installed) */
        #pwa-fab {
            display: none;
            position: fixed;
            bottom: 24px; right: 20px;
            background: linear-gradient(135deg, #4285f4, #0a1628);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(66,133,244,0.5);
            z-index: 9998;
            transition: transform 0.2s, box-shadow 0.2s;
            gap: 8px;
            align-items: center;
        }
        #pwa-fab.show { display: flex; }
        #pwa-fab:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(66,133,244,0.7); }
    </style>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/adit/sw.js')
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.warn('SW error:', err));
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>🔐 Sistem Keamanan Transaksi</h1>
        <h2>Pilih Peran Login</h2>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= htmlspecialchars($login_url_admin) ?>" class="btn-google" style="background: #dc3545;">
                🛡️ Login sebagai Admin
            </a>
            <a href="<?= htmlspecialchars($login_url_user) ?>" class="btn-google" style="background: #28a745;">
                👤 Login sebagai User
            </a>
        </div>
        <p class="info">* Hanya email yang terdaftar sesuai peran yang dapat login</p>
        <p class="info">⚠️ 1 email hanya dapat digunakan untuk 1 peran (Admin <strong>atau</strong> User, tidak bisa keduanya)</p>
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <hr>
        <p>Belum ada admin? <a href="register_admin.php">Daftar sebagai Admin</a></p>
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner">
        <div class="banner-icon">📲</div>
        <div class="banner-text">
            <strong>Pasang Aplikasi KTE Secure</strong>
            <span>Install di perangkat Anda untuk akses lebih cepat!</span>
        </div>
        <div class="banner-actions">
            <button class="btn-install" id="btn-install-banner">Pasang</button>
            <button class="btn-dismiss" id="btn-dismiss-banner">Nanti</button>
        </div>
    </div>

    <!-- Floating Install Button -->
    <button id="pwa-fab" title="Install Aplikasi">
        ⬇️ Install App
    </button>

    <script>
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-install-banner');
        const fab    = document.getElementById('pwa-fab');

        // Tangkap event install dari browser
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            // Tampilkan banner jika user belum dismiss
            if (!sessionStorage.getItem('pwa-dismissed')) {
                banner.classList.add('show');
            }
            // Selalu tampilkan FAB
            fab.classList.add('show');
        });

        // Tombol install di banner
        document.getElementById('btn-install-banner').addEventListener('click', () => {
            installPWA();
        });

        // Tombol FAB
        fab.addEventListener('click', () => {
            installPWA();
        });

        function installPWA() {
            if (!deferredPrompt) {
                alert('Untuk install: buka menu browser ⋮ → "Tambahkan ke layar utama" / "Install App"');
                return;
            }
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('PWA berhasil diinstall!');
                    banner.classList.remove('show');
                    fab.classList.remove('show');
                } else {
                    console.log('User menolak install PWA');
                }
                deferredPrompt = null;
            });
        }

        // Tombol dismiss
        document.getElementById('btn-dismiss-banner').addEventListener('click', () => {
            banner.classList.remove('show');
            sessionStorage.setItem('pwa-dismissed', '1');
        });

        // Sembunyikan FAB jika sudah terinstall
        window.addEventListener('appinstalled', () => {
            fab.classList.remove('show');
            banner.classList.remove('show');
            deferredPrompt = null;
            console.log('PWA sudah terinstall');
        });
    </script>
</body>
</html>