<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once '../../config/session_check.php';

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: aspirasi.php');
    exit;
}

// Gunakan $pdo sebagai objek koneksi
if (!aspirasi_schema_ready($pdo)) {
    die('Database belum siap. Jalankan aspirasi-update.sql terlebih dahulu.');
}

$mahasiswa = active_mahasiswa($pdo);

if (!$mahasiswa) {
    die('Data mahasiswa belum tersedia. Isi tabel tbmahasiswa atau sambungkan dengan login mahasiswa.');
}

// Tangkap name="id_organisasi_tujuan" yang dikirimkan oleh form aspirasi.php
$idOrganisasi = !empty($_POST['id_organisasi_tujuan']) ? (int) $_POST['id_organisasi_tujuan'] : null;
$kategori = trim($_POST['kategori'] ?? '');
$judul = trim($_POST['judul'] ?? '');
$isi = trim($_POST['isi_aspirasi'] ?? '');
$isAnonim = isset($_POST['is_anonim']) ? 1 : 0;
$kode = generate_kode_aspirasi(); 

$idMahasiswa = (int) $mahasiswa['id_mahasiswa'];

$kategoriValid = ['Kritik', 'Saran', 'Keluhan', 'Apresiasi', 'Lainnya'];
if (!in_array($kategori, $kategoriValid, true)) {
    die('Kategori tidak valid.');
}

if ($judul === '' || $isi === '') {
    die('Judul dan isi aspirasi wajib diisi.');
}

if (!$idOrganisasi) {
    die('Organisasi tujuan wajib dipilih.');
}

try {
    // PERBAIKAN: Mengubah 'id_organisasi' menjadi 'id_organisasi_tujuan' sesuai struktur database
    $sql = "INSERT INTO aspirasi (
                kode_aspirasi, id_mahasiswa, id_organisasi_tujuan, judul, 
                isi_aspirasi, kategori, is_anonim, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'proses')";

    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        $kode,
        $idMahasiswa,
        $idOrganisasi, 
        $judul,
        $isi,
        $kategori,
        $isAnonim
    ]);

    if ($ok) {
        header('Location: cek_status_aspirasi.php?kode=' . urlencode($kode) . '&success=1');
        exit;
    }
} catch (PDOException $e) {
    die('Gagal menyimpan aspirasi: ' . h($e->getMessage()));
}
?>