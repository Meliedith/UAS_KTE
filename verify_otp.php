<?php
// ======================================================
// ===== FILE: verify_otp.php ===========================
// ===== FUNGSI: Verifikasi kode OTP user ==============
// ======================================================

session_start();
require_once 'config/db.php';

$mode = $_GET['mode'] ?? 'login';
$error = '';

if ($mode === 'register') {
    if (!isset($_SESSION['temp_user_id'])) {
        header('Location: register.php');
        exit;
    }
} else {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp']);
    $otp_valid = false;
    $expired = false;
    
    if ($mode === 'register') {
        if (time() > $_SESSION['temp_otp_expiry']) {
            $expired = true;
        } elseif ($input_otp == $_SESSION['temp_otp']) {
            $otp_valid = true;
        }
    } else {
        if (time() > $_SESSION['otp_expiry']) {
            $expired = true;
        } elseif ($input_otp == $_SESSION['otp']) {
            $otp_valid = true;
        }
    }
    
    if ($expired) {
        $error = "⏰ Kode OTP sudah kadaluarsa (5 menit). Silakan kirim ulang.";
    } elseif ($otp_valid) {
        if ($mode === 'register') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['temp_user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['raw_id'] = $user['raw_id'];
            $_SESSION['otp_verified'] = true;
            
            unset($_SESSION['temp_otp'], $_SESSION['temp_otp_expiry'], $_SESSION['temp_user_id']);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['otp_verified'] = true;
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = "❌ Kode OTP salah. Coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
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
    <h2>📱 Verifikasi OTP</h2>
    <?php if ($mode === 'register'): ?>
        <p>Kode OTP dikirim ke: <strong><?= htmlspecialchars($_SESSION['temp_phone'] ?? '') ?></strong></p>
    <?php else: ?>
        <p>Kode OTP dikirim ke: <strong><?= htmlspecialchars($_SESSION['phone']) ?></strong></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="otp" placeholder="Masukkan 6 digit kode" maxlength="6" required>
        <button type="submit" class="btn-primary">Verifikasi</button>
    </form>
    
    <?php if ($mode === 'register'): ?>
        <p><a href="send_otp.php?mode=register">📨 Kirim Ulang OTP</a></p>
    <?php else: ?>
        <p><a href="send_otp.php">📨 Kirim Ulang OTP</a></p>
    <?php endif; ?>
</div>
</body>
</html> 