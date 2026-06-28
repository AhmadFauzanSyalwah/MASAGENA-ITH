<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kegiatan.php');
    exit;
}

function back_with_error($message, $id_konten = 0) {
    $target = 'form_pendaftaran_kegiatan.php';

    $params = [
        'error' => $message
    ];

    if ($id_konten > 0) {
        $params['id_konten'] = (int) $id_konten;
    }

    header('Location: ' . $target . '?' . http_build_query($params));
    exit;
}

$id_konten = isset($_POST['id_konten']) ? (int) $_POST['id_konten'] : 0;
$nim = trim($_POST['nim'] ?? '');
$email = trim($_POST['email'] ?? '');
$defaultKuota = (int) pendaftaran_default_kuota();

if ($id_konten <= 0) {
    back_with_error('Kegiatan wajib dipilih.', $id_konten);
}

if ($nim === '' || $email === '') {
    back_with_error('NIM dan email wajib diisi.', $id_konten);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back_with_error('Format email tidak valid.', $id_konten);
}

// Memulai transaksi menggunakan PDO
$pdo->beginTransaction();

try {
    // 1. Ambil data mahasiswa berdasarkan NIM dan Email
    $stmtMhs = $pdo->prepare("
        SELECT id_mahasiswa, nim, nama, email
        FROM tbmahasiswa
        WHERE nim = ?
        AND email = ?
        LIMIT 1
    ");
    $stmtMhs->execute([$nim, $email]);
    $mahasiswa = $stmtMhs->fetch(PDO::FETCH_ASSOC);

    if (!$mahasiswa) {
        throw new Exception('NIM dan email tidak ditemukan di data mahasiswa.');
    }

    $id_mahasiswa = (int) $mahasiswa['id_mahasiswa'];

    // 2. Kunci baris kegiatan agar kuota tidak crash saat diakses banyak orang (Race Condition)
    $stmtKegiatan = $pdo->prepare("
        SELECT id_konten, judul
        FROM konten_kegiatan
        WHERE id_konten = ?
        AND status_publikasi = 'publish'
        LIMIT 1
        FOR UPDATE
    ");
    $stmtKegiatan->execute([$id_konten]);
    $kegiatan = $stmtKegiatan->fetch(PDO::FETCH_ASSOC);

    if (!$kegiatan) {
        throw new Exception('Kegiatan tidak ditemukan atau belum publish.');
    }

    // 3. Cek apakah mahasiswa bersangkutan sudah terdaftar di kegiatan ini
    $stmtCek = $pdo->prepare("
        SELECT id_pendaftaran
        FROM pendaftaran
        WHERE id_mahasiswa = ?
        AND id_konten = ?
        LIMIT 1
    ");
    $stmtCek->execute([$id_mahasiswa, $id_konten]);
    $sudahDaftar = $stmtCek->fetch(PDO::FETCH_ASSOC);

    if ($sudahDaftar) {
        throw new Exception('Kamu sudah mendaftar kegiatan ini.');
    }

    // 4. Hitung jumlah pendaftar aktif dan dapatkan kuota maksimal kegiatan
    $stmtJumlah = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(MAX(NULLIF(kuota_maks, 0)), ?) AS kuota
        FROM pendaftaran
        WHERE id_konten = ?
        AND status_pendaftaran != 'ditolak'
    ");
    $stmtJumlah->execute([$defaultKuota, $id_konten]);
    $jumlahData = $stmtJumlah->fetch(PDO::FETCH_ASSOC);

    $kuota = (int) ($jumlahData['kuota'] ?? $defaultKuota);
    $totalPeserta = (int) ($jumlahData['total'] ?? 0);

    if ($kuota > 0 && $totalPeserta >= $kuota) {
        throw new Exception('Kuota kegiatan sudah penuh.');
    }

    // 5. Masukkan data pendaftaran baru ke database
    $stmtInsert = $pdo->prepare("
        INSERT INTO pendaftaran
        (
            id_mahasiswa,
            id_konten,
            status_pendaftaran,
            kuota_maks
        )
        VALUES
        (
            ?,
            ?,
            'menunggu',
            ?
        )
    ");
    
    $stmtInsert->execute([$id_mahasiswa, $id_konten, $kuota]);

    // Jika semua proses aman, simpan transaksi secara permanen
    $pdo->commit();

    header(
        'Location: cek_status_pendaftaran.php?nim=' .
        urlencode($nim) .
        '&email=' .
        urlencode($email) .
        '&success=1'
    );
    exit;

} catch (Exception $e) {
    // Batalkan seluruh perubahan jika terjadi error di tengah jalan
    $pdo->rollBack();
    back_with_error($e->getMessage(), $id_konten);
}