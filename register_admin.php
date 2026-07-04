<?php
// =============================================================
// FILE: register_admin.php
// FUNGSI: Mendaftarkan admin baru (siapa pun bisa akses)
// =============================================================

session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    
    // Validasi sederhana
    if (empty($email) || empty($name) || empty($phone)) {
        $error = "Semua field wajib diisi!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Nomor WhatsApp harus angka, minimal 10 digit (contoh: 628123456789)";
    } else {
        $email_hash = hash('sha512', $email);
        
        // Cek duplikat email
        $check = $pdo->prepare("SELECT id FROM users WHERE email_hash = ?");
        $check->execute([$email_hash]);
        if ($check->fetch()) {
            $error = "Email sudah terdaftar.";
        } else {
            // Simpan sebagai admin
            $stmt = $pdo->prepare("INSERT INTO users (email, email_hash, name, phone, role) VALUES (?, ?, ?, ?, 'admin')");
            if ($stmt->execute([$email, $email_hash, $name, $phone])) {
                $new_id = $pdo->lastInsertId();
                // Catat log
                $log = $pdo->prepare("INSERT INTO user_logs (user_id, action, changed_by) VALUES (?, 'CREATE', ?)");
                $log->execute([$new_id, $email]);
                $success = "✅ Admin berhasil didaftarkan! Silakan login sebagai admin.";
            } else {
                $error = "❌ Gagal mendaftarkan admin.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin</title>
    <link rel="manifest" href="/adit/pwa/manifest.json">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/adit/sw.js');
        }
    </script>
</head>
<body>
<div class="container">
    <h2>🛡️ Registrasi Admin</h2>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
        <p><a href="index.php">Login sekarang</a></p>
    <?php else: ?>
        <form method="POST">
            <label>Email Admin:</label>
            <input type="email" name="email" placeholder="email@domain.com" required><br>
            <label>Nama Lengkap:</label>
            <input type="text" name="name" placeholder="Nama Admin" required><br>
            <label>Nomor WhatsApp:</label>
            <input type="text" name="phone" placeholder="628123456789" required><br>
            <button type="submit" class="btn-primary">Daftar sebagai Admin</button>
        </form>
        <p><a href="index.php">Kembali ke Login</a></p>
    <?php endif; ?>
</div>
</body>
</html>