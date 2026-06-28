<?php
// ajax/komentar.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login.']);
    exit;
}

$id_konten = isset($_POST['id_konten']) ? (int)$_POST['id_konten'] : 0;
$isi = isset($_POST['isi']) ? trim($_POST['isi']) : '';
$parent = isset($_POST['parent']) ? (int)$_POST['parent'] : null;

if ($id_konten <= 0 || empty($isi)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek kolom user di tabel komentar
$komentarUserColumn = 'id_user'; // default
try {
    $columns = $pdo->query("SHOW COLUMNS FROM komentar")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('id_mahasiswa', $columns)) {
        $komentarUserColumn = 'id_mahasiswa';
    } elseif (in_array('id_user', $columns)) {
        $komentarUserColumn = 'id_user';
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Tabel komentar tidak ditemukan.']);
    exit;
}

try {
    // Insert komentar
    if ($parent) {
        $insert = $pdo->prepare("INSERT INTO komentar ($komentarUserColumn, id_konten, isi_komentar, id_komentar_parent, created_at) 
                                 VALUES (?, ?, ?, ?, NOW())");
        $insert->execute([$user_id, $id_konten, $isi, $parent]);
    } else {
        $insert = $pdo->prepare("INSERT INTO komentar ($komentarUserColumn, id_konten, isi_komentar, created_at) 
                                 VALUES (?, ?, ?, NOW())");
        $insert->execute([$user_id, $id_konten, $isi]);
    }
    
    // Ambil nama user
    $nama = 'User';
    try {
        $namaQuery = $pdo->prepare("SELECT nama FROM tbmahasiswa WHERE id_mahasiswa = ?");
        $namaQuery->execute([$user_id]);
        $nama = $namaQuery->fetchColumn();
        if (!$nama) {
            // Coba di tabel user
            $namaQuery2 = $pdo->prepare("SELECT nama FROM user WHERE id_user = ?");
            $namaQuery2->execute([$user_id]);
            $nama = $namaQuery2->fetchColumn();
        }
        if (!$nama) $nama = 'Pengguna';
    } catch (PDOException $e) {
        $nama = 'Pengguna';
    }

    // Hitung total komentar
    $count = $pdo->prepare("SELECT COUNT(*) FROM komentar WHERE id_konten = ?");
    $count->execute([$id_konten]);
    $totalKomentar = (int)$count->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'message' => 'Komentar berhasil ditambahkan.',
        'total_komentar' => $totalKomentar,
        'nama' => $nama,
        'isi' => nl2br(htmlspecialchars($isi)),
        'waktu' => date('d M Y H:i')
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}