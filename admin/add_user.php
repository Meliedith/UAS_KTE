<?php
// =============================================================
// FILE: admin/add_user.php
// FUNGSI: Admin menambah user baru (role otomatis 'user')
// =============================================================

session_start();
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin' || !isset($_SESSION['biometric_verified'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $role = 'user'; // Role otomatis user
    $email_hash = hash('sha512', $email);
    
    try {
        $check = $pdo->prepare("SELECT id FROM users WHERE email_hash = ?");
        $check->execute([$email_hash]);
        if ($check->fetch()) {
            $error = "Email sudah terdaftar!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (email, email_hash, name, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $email_hash, $name, $phone, $role]);
            $new_id = $pdo->lastInsertId();
            
            $log = $pdo->prepare("INSERT INTO user_logs (user_id, action, changed_by) VALUES (?, 'CREATE', ?)");
            $log->execute([$new_id, $_SESSION['email']]);
            
            $success = "✅ User berhasil ditambahkan! (Email: $email)";
        }
    } catch (PDOException $e) {
        $error = "❌ Gagal: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link rel="manifest" href="/UAS_KTE/pwa/manifest.json">
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/UAS_KTE/sw.js');
        }
    </script>
</head>
<body>
<div class="container">
    <h2>➕ Tambah User Baru</h2>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email user" required><br>
        <input type="text" name="name" placeholder="Nama Lengkap" required><br>
        <input type="text" name="phone" placeholder="Nomor WhatsApp (62812...)" required><br>
        <p><small>Role akan otomatis sebagai 'user'.</small></p>
        <button type="submit" class="btn-primary">Tambah User</button>
    </form>
    <p><a href="users.php">Kembali ke Daftar User</a></p>
</div>
</body>
</html>