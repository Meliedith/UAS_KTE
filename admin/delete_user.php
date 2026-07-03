<?php
// =============================================================
// ===== FILE: admin/delete_user.php ===========================
// ===== FUNGSI: Proses hapus user =============================
// =============================================================

session_start();
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin' || !isset($_SESSION['biometric_verified'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if ($id > 0) {
    $log = $pdo->prepare("INSERT INTO user_logs (user_id, action, changed_by) VALUES (?, 'DELETE', ?)");
    $log->execute([$id, $_SESSION['email']]);
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: users.php');
exit;
?>