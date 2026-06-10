<?php
session_start();
// Jika sudah login, arahkan ke dashboard masing-masing
if (isset($_SESSION['id_mahasiswa'])) {
    header('Location: /dashboard/mahasiswa.php');
    exit;
} elseif (isset($_SESSION['id_pengurus'])) {
    header('Location: /dashboard/pengurus.php');
    exit;
} elseif (isset($_SESSION['id_admin'])) {
    header('Location: /dashboard/admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - MASAGENA-ITH</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 400px;">
    <div class="card p-4">
        <h4 class="text-center mb-3">Masuk</h4>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="proses_login.php" method="POST">
            <div class="mb-3">
                <label for="credential" class="form-label">Email / NIM / Nama Pengurus</label>
                <input type="text" class="form-control" id="credential" name="credential" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
        <p class="text-center mt-3 mb-0">
            Belum punya akun? <a href="register.php">Daftar</a>
        </p>
    </div>
</div>
</body>
</html>