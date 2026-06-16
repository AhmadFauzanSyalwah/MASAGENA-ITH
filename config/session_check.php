<?php
// config/session_check.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/database.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}
// Perbarui session dengan data terbaru
$_SESSION['nama'] = $user['nama'];
$_SESSION['peran'] = $user['peran'];
$_SESSION['level'] = $user['level'] ?? 'biasa';
$_SESSION['id_organisasi'] = $user['id_organisasi'];
?>