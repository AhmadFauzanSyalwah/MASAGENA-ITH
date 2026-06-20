<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, redirect ke dashboard sesuai peran
    $peran = $_SESSION['peran'];
    // Pastikan path ini sesuai dengan struktur folder Anda (pakai ../ atau tidak)
    header("Location: ../dashboard/$peran/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MASAGENA-ITH</title>
    <link rel="stylesheet" href="/MASAGENA-ITH/assets/css/style.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="logo-login">
        <img src="/MASAGENA-ITH/assets/img/logo.png" alt="MASAGENA-ITH" onerror="this.style.display='none'">
        <h3 style="color: #071C34;">MASAGENA-ITH</h3>
    </div>
    <h2>Login</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error" style="color: red; margin-bottom: 15px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    
    <form action="proses_login.php" method="POST">
        <div class="form-group">
            <label>Username / Email / NIM</label>
            <input type="text" name="email" placeholder="Masukkan Email, NIM, atau Username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit">Masuk</button>
    </form>
    <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar sebagai mahasiswa</a>
    </div>
</div>
</body>
</html>     