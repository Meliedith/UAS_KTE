<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['otp_verified'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION;
$isAdmin = ($user['role'] === 'admin');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="manifest" href="/UAS_KTE/pwa/manifest.json">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/UAS_KTE/sw.js');
        }
    </script>
</head>
<body>
<div class="container">
    <h1>👋 Selamat datang, <?= htmlspecialchars($user['name']) ?>!</h1>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    
    <?php if ($isAdmin): ?>
        <hr>
        <h3>🔐 Menu Admin (Memerlukan Biometrik)</h3>
        <ul>
            <li><a href="register_fingerprint.php">📌 Daftarkan Sidik Jari / Face ID</a></li>
            <li><a href="admin/verify_biometric.php">👥 Kelola User (CRUD)</a></li>
        </ul>
    <?php endif; ?>
    
    <br>
    <a href="logout.php" class="btn-danger">Logout</a>
</div>
</body>
</html>