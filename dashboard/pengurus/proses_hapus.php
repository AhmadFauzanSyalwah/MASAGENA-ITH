<?php
// dashboard/pengurus/proses_hapus.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_konten'])) {
    $id_konten = (int)$_POST['id_konten'];
} elseif (isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] === 'kegiatan') {
    $id_konten = (int)$_GET['id'];
} else {
    $_SESSION['error'] = 'Parameter tidak valid.';
    header('Location: kelola_konten.php');
    exit;
}

if ($id_konten <= 0) {
    $_SESSION['error'] = 'ID kegiatan tidak valid.';
    header('Location: kelola_konten.php');
    exit;
}

$is_admin = ($_SESSION['peran'] === 'admin');

$stmt = $pdo->prepare("SELECT id_organisasi, lampiran FROM konten_kegiatan WHERE id_konten = ?");
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    $_SESSION['error'] = 'Kegiatan tidak ditemukan.';
    header('Location: kelola_konten.php');
    exit;
}

if (!$is_admin) {
    $id_pengurus = $_SESSION['user_id'];
    $stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmtOrg->execute([$id_pengurus]);
    $org_pengurus = $stmtOrg->fetchColumn();

    if ($org_pengurus != $kegiatan['id_organisasi']) {
        $_SESSION['error'] = 'Anda tidak memiliki akses untuk menghapus kegiatan ini.';
        header('Location: kelola_konten.php');
        exit;
    }
}

try {
    $pdo->beginTransaction();

    if (!empty($kegiatan['lampiran'])) {
        $file_path = '../../uploads/kegiatan/' . $kegiatan['lampiran'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM pendaftaran WHERE id_konten = ?");
    $stmt->execute([$id_konten]);

    $stmt = $pdo->prepare("DELETE FROM likes WHERE id_konten = ?");
    $stmt->execute([$id_konten]);

    $stmt = $pdo->prepare("DELETE FROM komentar WHERE id_konten = ?");
    $stmt->execute([$id_konten]);

    $stmt = $pdo->prepare("DELETE FROM konten_kegiatan WHERE id_konten = ?");
    $stmt->execute([$id_konten]);

    $pdo->commit();

    // ✅ Redirect dengan parameter sukses (tanpa session success)
    header('Location: kelola_konten.php?hapus=sukses');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Gagal menghapus kegiatan: ' . $e->getMessage();
    header('Location: kelola_konten.php');
    exit;
}
?>