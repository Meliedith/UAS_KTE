<?php
// =============================================================
// ===== FILE: admin/verify_biometric.php ======================
// ===== FUNGSI: Gate verifikasi biometrik sebelum CRUD ========
// =============================================================

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if (isset($_SESSION['biometric_verified']) && $_SESSION['biometric_verified'] === true) {
    header('Location: users.php');
    exit;
}

$raw_id_db = $_SESSION['raw_id'] ?? '';

if (empty($raw_id_db)) {
    die("⚠️ Anda belum mendaftarkan fingerprint. <a href='../register_fingerprint.php'>Daftar di sini</a>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Biometrik</title>
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
    <h2>🔐 Verifikasi Biometrik Wajib</h2>
    <p>Scan sidik jari / Face ID untuk mengakses halaman kelola user.</p>
    <button onclick="verifyFingerprint()" class="btn-primary">🖐️ Scan Sekarang</button>
    <pre id="output"></pre>
</div>

<script>
    const savedRawIdBase64Url = "<?= $raw_id_db ?>";
    
    function base64ToBuffer(base64) {
        const binaryString = atob(base64);
        const bytes = new Uint8Array(binaryString.length);
        for (let i = 0; i < binaryString.length; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes;
    }

    async function verifyFingerprint() {
        try {
            const challenge = crypto.getRandomValues(new Uint8Array(32));
            const options = {
                publicKey: {
                    challenge: challenge,
                    timeout: 60000,
                    userVerification: "required",
                    allowCredentials: [{
                        id: base64ToBuffer(savedRawIdBase64Url),
                        type: "public-key",
                        transports: ["internal"]
                    }]
                }
            };

            const credential = await navigator.credentials.get(options);
            const rawId = btoa(String.fromCharCode(...new Uint8Array(credential.rawId)));
            document.getElementById("output").textContent = "✅ Verifikasi berhasil! Redirecting...";
            
            const res = await fetch("verify_biometric.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: "verified", rawId: rawId })
            });
            
            if (res.ok) {
                window.location.href = "users.php";
            }
        } catch (err) {
            document.getElementById("output").textContent = "❌ Gagal: " + err.message;
            alert("Verifikasi gagal. Pastikan perangkat mendukung biometrik.");
        }
    }

    verifyFingerprint();
</script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['status']) && $data['status'] === 'verified') {
        $_SESSION['biometric_verified'] = true;
        echo json_encode(["status" => "success"]);
        exit;
    }
    http_response_code(400);
    echo json_encode(["status" => "failed"]);
}
?>