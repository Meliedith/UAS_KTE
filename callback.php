<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'config/db.php';

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost/adit/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $oauth = new Google_Service_Oauth2($client);
        $userInfo = $oauth->userinfo->get();
        
        $email = $userInfo->email;
        $name = $userInfo->name;
        $email_hash = hash('sha512', $email);
        $state = $_GET['state'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email_hash = ?");
        $stmt->execute([$email_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $error = urlencode("Email tidak terdaftar di sistem.");
            header("Location: index.php?error=$error");
            exit;
        }
        if ($state === 'admin' && $user['role'] !== 'admin') {
            $error = urlencode("Akun ini bukan admin.");
            header("Location: index.php?error=$error");
            exit;
        }
        if ($state === 'user' && $user['role'] === 'admin') {
            $error = urlencode("Admin tidak dapat login sebagai user.");
            header("Location: index.php?error=$error");
            exit;
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['raw_id'] = $user['raw_id'];
        header('Location: send_otp.php');
        exit;
    } else {
        echo "Login gagal: " . $token['error_description'];
    }
} else {
    echo "Kode otorisasi tidak ditemukan.";
    header('Location: index.php');
}
?>