<?php
// ====================================================
// ===== FILE: utils/fonnte.php =======================
// ===== FUNGSI: Kirim OTP via WhatsApp (Fonnte) =====
// ====================================================

function sendOTPWhatsApp($phone, $otp) {
    // ===== GANTI DENGAN TOKEN ANDA DARI FONNTE.COM =====
    $token = "";
    
    $message = "🔐 *Kode OTP Anda*\n\nKode: *$otp*\n\nKode ini berlaku 5 menit dan hanya untuk sekali pakai. Jangan bagikan ke siapa pun.";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $phone,
            'message' => $message
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $token"
        ]
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
?>