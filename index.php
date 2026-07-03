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
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost/UAS_KTE/callback.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UAS KTE</title>
    <link rel="manifest" href="pwa/manifest.json">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('pwa/sw.js');
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
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <hr>
        <p>Belum ada admin? <a href="register_admin.php">Daftar sebagai Admin</a></p>
    </div>
</body>
</html>