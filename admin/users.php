<?php
// =============================================================
// ===== FILE: admin/users.php =================================
// ===== FUNGSI: Halaman CRUD User (Hanya Admin) ==============
// =============================================================

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
if (!isset($_SESSION['biometric_verified']) || $_SESSION['biometric_verified'] !== true) {
    header('Location: verify_biometric.php');
    exit;
}

$stmt = $pdo->query("SELECT id, email, name, phone, role, created_at, updated_at FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
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
    <h2>👥 Kelola User (CRUD)</h2>
    <p><a href="add_user.php" class="btn-primary">➕ Tambah User Baru</a></p>
    <p><a href="../dashboard.php">⬅️ Kembali ke Dashboard</a></p>
    
    <div class="table-responsive">
        <table border="1" cellpadding="8" width="100%">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Role</th>
                <th>Dibuat</th>
                <th>Terakhir Diubah</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['phone']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= $user['created_at'] ?></td>
                <td><?= $user['updated_at'] ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn-edit">✏️ Edit</a>
                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn-danger" onclick="return confirm('Yakin hapus?')">🗑️ Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>