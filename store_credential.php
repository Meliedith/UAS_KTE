<?php
// =============================================================
// ===== FILE: store_credential.php ============================
// ===== FUNGSI: Simpan raw_id dan public_key ke database =====
// =============================================================

session_start();
require_once 'config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$rawId = $data['credential']['rawId'];
$publicKey = $data['credential']['id'];

$stmt = $pdo->prepare("UPDATE users SET raw_id = ?, public_key = ? WHERE email = ?");
if ($stmt->execute([$rawId, $publicKey, $email])) {
    $_SESSION['raw_id'] = $rawId;
    echo "✅ Registrasi fingerprint berhasil!";
} else {
    echo "❌ Gagal menyimpan credential.";
}
?>