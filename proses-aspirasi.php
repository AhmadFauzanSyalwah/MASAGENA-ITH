<?php
include 'connection.php';
include 'components.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: aspirasi.php');
    exit;
}

if (!aspirasi_schema_ready($conn)) {
    die('Database belum siap. Jalankan aspirasi-update.sql terlebih dahulu.');
}

$mahasiswa = active_mahasiswa($conn);
if (!$mahasiswa) {
    die('Data mahasiswa belum tersedia. Isi tabel tbmahasiswa atau sambungkan dengan login mahasiswa.');
}

$idOrganisasi = !empty($_POST['id_organisasi']) ? (int) $_POST['id_organisasi'] : null;
$kategori = trim($_POST['kategori'] ?? '');
$judul = trim($_POST['judul'] ?? '');
$isi = trim($_POST['isi_aspirasi'] ?? '');
$isAnonim = isset($_POST['is_anonim']) ? 1 : 0;
$kode = generate_kode_aspirasi();
$idMahasiswa = $isAnonim ? null : (int) $mahasiswa['id_mahasiswa'];

$kategoriValid = ['Kritik', 'Saran', 'Keluhan', 'Apresiasi', 'Lainnya'];
if (!in_array($kategori, $kategoriValid, true)) {
    die('Kategori tidak valid.');
}

if ($judul === '' || $isi === '') {
    die('Judul dan isi aspirasi wajib diisi.');
}

$stmt = mysqli_prepare($conn, "
    INSERT INTO aspirasi
    (kode_aspirasi, id_mahasiswa, id_organisasi, judul, isi_aspirasi, kategori, is_anonim, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'proses')
");

mysqli_stmt_bind_param($stmt, 'siisssi', $kode, $idMahasiswa, $idOrganisasi, $judul, $isi, $kategori, $isAnonim);
$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    header('Location: cek-status-aspirasi.php?kode=' . urlencode($kode) . '&success=1');
    exit;
}

die('Gagal menyimpan aspirasi: ' . h($error));
?>
