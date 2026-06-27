<?php
// dashboard/mahasiswa/form_pendaftaran_kegiatan.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}
if ($_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../dashboard/' . $_SESSION['peran'] . '/index.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$id_konten = isset($_GET['id_konten']) ? (int)$_GET['id_konten'] : 0;
if ($id_konten <= 0) {
    header('Location: kegiatan.php');
    exit;
}

// ============================================================
// QUERY KEGIATAN + KUOTA DARI KONTEN_KEGIATAN
// ============================================================
$sql = "SELECT k.*, o.nama_organisasi,
               COALESCE(k.kuota, 50) AS kuota,
               (SELECT COUNT(*) FROM pendaftaran WHERE id_konten = k.id_konten AND status_pendaftaran != 'ditolak') AS jumlah_peserta
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE k.id_konten = :id AND k.status_publikasi = 'publik'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    header('Location: kegiatan.php');
    exit;
}

$kuota = (int)$kegiatan['kuota'];
$jumlah = (int)$kegiatan['jumlah_peserta'];
$penuh = $kuota > 0 && $jumlah >= $kuota;

// Cek apakah user sudah mendaftar
$sudahDaftar = false;
$statusPendaftaran = '';
$check = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_mahasiswa = ? AND id_konten = ?");
$check->execute([$_SESSION['user_id'], $id_konten]);
$row = $check->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $sudahDaftar = true;
    $statusPendaftaran = $row['status_pendaftaran'];
}

$message = '';
$error = '';

// Proses pendaftaran (hanya jika belum terdaftar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    if ($sudahDaftar) {
        $error = 'Anda sudah mendaftar untuk kegiatan ini. Status: <strong>' . ucfirst($statusPendaftaran) . '</strong>';
    } elseif ($penuh) {
        $error = 'Maaf, kuota kegiatan ini sudah penuh.';
    } else {
        $insert = $pdo->prepare("INSERT INTO pendaftaran (id_mahasiswa, id_konten, status_pendaftaran, kuota_maks) VALUES (?, ?, 'menunggu', ?)");
        if ($insert->execute([$_SESSION['user_id'], $id_konten, $kuota])) {
            $message = 'Pendaftaran berhasil! Status: <strong>Menunggu Konfirmasi</strong>.';
            $sudahDaftar = true;
            $statusPendaftaran = 'menunggu';
        } else {
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.daftar-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 0 1rem;
}
.daftar-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}
.daftar-card h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.5rem;
}
.daftar-card .meta {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}
.daftar-card .meta i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.daftar-card .deskripsi {
    font-size: 1rem;
    line-height: 1.6;
    margin: 1rem 0;
    color: #1e293b;
}
.daftar-card .info {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 12px;
    margin: 1rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.daftar-card .info .label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
}
.daftar-card .info .value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #071C34;
}
.daftar-card .info .value.penuh {
    color: #dc2626;
}
.daftar-card .info .value.terdaftar {
    color: #16a34a;
}
.btn-daftar {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.6rem 2rem;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
}
.btn-daftar:hover {
    background: #071C34;
    color: #fff;
}
.btn-daftar:disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
}
.btn-kembali {
    display: inline-block;
    background: #f1f5f9;
    color: #071C34;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 0.4rem 1.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
    margin-top: 0.5rem;
}
.btn-kembali:hover {
    background: #e2e8f0;
}
.alert {
    padding: 0.8rem 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}
.alert-success {
    background: #dcfce7;
    color: #16a34a;
    border-color: #bbf7d0;
}
.alert-danger {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fecaca;
}
.alert-warning {
    background: #fef9c3;
    color: #ca8a04;
    border-color: #fde68a;
}
.status-badge {
    display: inline-block;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-badge.menunggu {
    background: #fef9c3;
    color: #ca8a04;
}
.status-badge.diterima {
    background: #dcfce7;
    color: #16a34a;
}
.status-badge.ditolak {
    background: #fee2e2;
    color: #dc2626;
}
</style>

<div class="daftar-container">
    <div class="daftar-card">
        <h1><?= htmlspecialchars($kegiatan['judul']) ?></h1>
        <div class="meta">
            <i class="fa-regular fa-building"></i> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
            <?php if (!empty($kegiatan['kategori'])): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <span class="badge" style="background:#FFA007; color:#071C34; padding:0.2rem 0.6rem; border-radius:50px; font-size:0.7rem;"><?= htmlspecialchars($kegiatan['kategori']) ?></span>
            <?php endif; ?>
        </div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>

        <div class="info">
            <span class="label">Kuota Peserta</span>
            <span class="value <?= $penuh ? 'penuh' : '' ?>">
                <?php if ($penuh): ?>
                    <i class="fas fa-exclamation-circle"></i> Penuh (<?= $jumlah ?>/<?= $kuota ?>)
                <?php else: ?>
                    <?= $jumlah ?>/<?= $kuota ?> tersisa
                <?php endif; ?>
            </span>
        </div>

        <?php if ($sudahDaftar): ?>
            <div class="info" style="background:#e8f0fe;">
                <span class="label">Status Pendaftaran</span>
                <span class="value terdaftar">
                    <span class="status-badge <?= $statusPendaftaran ?>"><?= ucfirst($statusPendaftaran) ?></span>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!$sudahDaftar): ?>
            <form method="post">
                <?php if ($penuh): ?>
                    <button class="btn-daftar" disabled>Kuota Penuh</button>
                <?php else: ?>
                    <button type="submit" name="daftar" class="btn-daftar">Daftar Sekarang</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding:0.5rem 0;">
                <p style="color:#16a34a; font-weight:600;">
                    <i class="fas fa-check-circle"></i> Anda sudah terdaftar
                </p>
            </div>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1rem;">
            <a href="kegiatan.php" class="btn-kembali"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Kegiatan</a>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>