<?php
// dashboard/pengurus/reset_pengurus.php (BACKEND)
session_start();

// Cek login & role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Hanya pengurus inti atau admin
if ($_SESSION['peran'] !== 'admin' && $_SESSION['level'] !== 'inti' && $_SESSION['level'] !== 'Pengurus Inti') {
    $_SESSION['error'] = 'Anda tidak memiliki izin.';
    header('Location: manajemen_pengurus.php');
    exit;
}

require_once '../../config/database.php';

$id_user = $_SESSION['user_id'];
$is_admin = ($_SESSION['peran'] === 'admin');

// Ambil ID pengurus dari POST
if (!isset($_POST['id_pengurus']) || empty($_POST['id_pengurus'])) {
    $_SESSION['error'] = 'ID pengurus tidak valid.';
    header('Location: manajemen_pengurus.php');
    exit;
}

$id_pengurus = (int)$_POST['id_pengurus'];

// Cegah reset diri sendiri
if ($id_pengurus == $id_user) {
    $_SESSION['error'] = 'Anda tidak dapat mereset password sendiri.';
    header('Location: manajemen_pengurus.php');
    exit;
}

// Jika bukan admin, cek organisasi
if (!$is_admin) {
    $stmt = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_user]);
    $org_login = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_pengurus]);
    $org_target = $stmt->fetchColumn();

    if ($org_login != $org_target) {
        $_SESSION['error'] = 'Anda tidak dapat mereset pengurus dari organisasi lain.';
        header('Location: manajemen_pengurus.php');
        exit;
    }
}

// Proses reset password
try {
    $default_password = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE pengurus_organisasi SET password = ? WHERE id_pengurus = ?");
    $stmt->execute([$default_password, $id_pengurus]);

    // Redirect dengan parameter sukses
    header('Location: manajemen_pengurus.php?reset=sukses');
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal mereset password: ' . $e->getMessage();
    header('Location: manajemen_pengurus.php');
    exit;
}
?>