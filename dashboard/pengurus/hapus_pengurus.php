<?php
// dashboard/pengurus/hapus_pengurus.php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Hanya pengurus inti atau admin yang boleh menghapus
if ($_SESSION['peran'] !== 'admin' && $_SESSION['level'] !== 'inti' && $_SESSION['level'] !== 'Pengurus Inti') {
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk menghapus pengurus.';
    header('Location: manajemen_pengurus.php');
    exit;
}

require_once '../../config/database.php';

// Ambil ID pengurus yang akan dihapus (bisa dari POST atau GET)
$id_hapus = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if (!$id_hapus) {
    $_SESSION['error'] = 'ID pengurus tidak valid.';
    header('Location: manajemen_pengurus.php');
    exit;
}

// Cegah penghapusan diri sendiri
if ($id_hapus == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Anda tidak dapat menghapus akun sendiri.';
    header('Location: manajemen_pengurus.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$is_admin = ($_SESSION['peran'] === 'admin');

// Jika bukan admin, pastikan pengurus yang dihapus satu organisasi
if (!$is_admin) {
    // Ambil id_organisasi dari pengurus yang login
    $stmt = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_user]);
    $org_login = $stmt->fetchColumn();
    if (!$org_login) {
        $_SESSION['error'] = 'Anda tidak terdaftar di organisasi mana pun.';
        header('Location: manajemen_pengurus.php');
        exit;
    }

    // Cek id_organisasi pengurus yang akan dihapus
    $stmt = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_hapus]);
    $org_target = $stmt->fetchColumn();
    if ($org_target != $org_login) {
        $_SESSION['error'] = 'Anda tidak dapat menghapus pengurus dari organisasi lain.';
        header('Location: manajemen_pengurus.php');
        exit;
    }
}

// Lakukan penghapusan
try {
    $stmt = $pdo->prepare("DELETE FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmt->execute([$id_hapus]);
    
    // Redirect dengan parameter sukses
    header('Location: manajemen_pengurus.php?hapus=sukses');
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menghapus pengurus: ' . $e->getMessage();
    header('Location: manajemen_pengurus.php');
    exit;
}
?>