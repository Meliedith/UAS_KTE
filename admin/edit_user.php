<?php
// =============================================================
// ===== FILE: admin/edit_user.php =============================
// ===== FUNGSI: Form edit data user ===========================
// =============================================================

session_start();
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin' || !isset($_SESSION['biometric_verified'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);
$user = $user->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User tidak ditemukan.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, role=? WHERE id=?");
    $stmt->execute([$name, $phone, $role, $id]);
    
    $log = $pdo->prepare("INSERT INTO user_logs (user_id, action, changed_by) VALUES (?, 'UPDATE', ?)");
    $log->execute([$id, $_SESSION['email']]);
    
    $success = "✅ User berhasil diupdate!";
    
    $user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user->execute([$id]);
    $user = $user->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit User</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="container">
    <h2>✏️ Edit User</h2>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required><br>
        <select name="role">
            <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select><br>
        <button type="submit" class="btn-primary">Update</button>
    </form>
    <p><a href="users.php">Kembali</a></p>
</div>
</body>
</html>