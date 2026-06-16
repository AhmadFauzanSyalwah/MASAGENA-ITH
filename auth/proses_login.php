<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Cek status verifikasi (kecuali admin)
        if ($user['peran'] != 'admin' && $user['status_verifikasi'] != 'verified') {
            header("Location: login.php?error=Akun belum diverifikasi oleh admin");
            exit();
        }
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['peran'] = $user['peran'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['level'] = $user['level'] ?? 'biasa'; // tambahkan ini
        $_SESSION['id_organisasi'] = $user['id_organisasi'];

        // Redirect ke dashboard masing-masing
        $redirect = "../dashboard/" . $user['peran'] . "/index.php";
        header("Location: $redirect");
        exit();
    } else {
        header("Location: login.php?error=Email atau password salah");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>