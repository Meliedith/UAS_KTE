<?php
// ======================================================
// ===== FILE: send_otp.php =============================
// ===== FUNGSI: Generate dan kirim OTP via WhatsApp ====
// ======================================================

session_start();
require_once 'config/db.php';
require_once 'utils/otp.php';
require_once 'utils/fonnte.php';

$mode = $_GET['mode'] ?? 'login';

if ($mode === 'register') {
    if (!isset($_SESSION['temp_user_id'])) {
        header('Location: register.php');
        exit;
    }
    $phone = $_SESSION['temp_phone'];
    $user_id = $_SESSION['temp_user_id'];
    $base_secret = $_ENV['OTP_SECRET_KEY'] ?? 'SECRET';
    $secret = $base_secret . '_REG_' . $user_id . '_' . date('Ymd');
    $otp = totp($secret, 6);
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['temp_otp_expiry'] = time() + 300;
    sendOTPWhatsApp($phone, $otp);
    header('Location: verify_otp.php?mode=register');
    exit;
    
} else {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    $phone = $_SESSION['phone'];
    $user_id = $_SESSION['user_id'];
    $base_secret = $_ENV['OTP_SECRET_KEY'] ?? 'SECRET';
    $secret = $base_secret . '_' . $user_id . '_' . date('Ymd');
    $otp = totp($secret, 6);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300;
    sendOTPWhatsApp($phone, $otp);
    header('Location: verify_otp.php');
    exit;
}
?>