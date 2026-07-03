<?php
// =============================================================
// ===== FILE: register_fingerprint.php ========================
// ===== FUNGSI: Registrasi biometrik (WebAuthn) untuk Admin ===
// =============================================================

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Fingerprint</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>📌 Registrasi Fingerprint (WebAuthn)</h2>
    <p>Admin: <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
    <p>Klik tombol di bawah, lalu scan sidik jari / Face ID Anda.</p>
    <button onclick="registerFingerprint()" class="btn-primary">🔑 Daftarkan Sidik Jari</button>
    <pre id="output"></pre>
    <br><a href="dashboard.php">Kembali</a>
</div>

<script>
async function registerFingerprint() {
    try {
        const email = "<?= $_SESSION['email'] ?>";
        const challenge = new Uint8Array(32);
        window.crypto.getRandomValues(challenge);
        const userId = new Uint8Array(16);
        window.crypto.getRandomValues(userId);

        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: challenge,
                rp: { name: "UAS KTE", id: window.location.hostname },
                user: {
                    id: userId,
                    name: email,
                    displayName: email
                },
                pubKeyCredParams: [
                    { type: "public-key", alg: -7 },
                    { type: "public-key", alg: -257 }
                ],
                authenticatorSelection: {
                    authenticatorAttachment: "platform",
                    userVerification: "required",
                    residentKey: "required",
                    requireResidentKey: true
                },
                timeout: 60000,
                attestation: "none"
            }
        });

        const credData = {
            id: credential.id,
            rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
            type: credential.type,
            response: {
                clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)))
            }
        };

        const res = await fetch("store_credential.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: email, credential: credData })
        });

        const result = await res.text();
        document.getElementById("output").textContent = result;
        alert(result);
    } catch (err) {
        console.error(err);
        document.getElementById("output").textContent = "Error: " + err.message;
        alert("Gagal registrasi. Pastikan perangkat mendukung biometrik dan koneksi HTTPS.");
    }
}
</script>
</body>
</html>