<?php
// =============================================================
// ===== FILE: utils/otp.php ===================================
// ===== FUNGSI: Generate OTP berbasis TOTP/HOTP ==============
// =============================================================

function hotp($secret, $counter, $digits = 6) {
    $counter = pack('N*', 0) . pack('N*', $counter);
    $hash = hash_hmac('sha1', $counter, $secret, true);
    $offset = ord(substr($hash, -1)) & 0x0F;
    $binary = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
    return str_pad($binary % pow(10, $digits), $digits, '0', STR_PAD_LEFT);
}

function totp($secret, $digits = 6, $period = 30) {
    $time = floor(time() / $period);
    return hotp($secret, $time, $digits);
}

function generateRandomOTP() {
    return str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}
?>