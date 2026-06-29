<?php
// dashboard/mahasiswa/form_pendaftaran_kegiatan.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_context = 'kegiatan';

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$user_id = $_SESSION['user_id'];
$id_konten = isset($_GET['id_konten']) ? (int)$_GET['id_konten'] : 0;

if (!$id_konten) {
    header('Location: kegiatan.php');
    exit;
}

// ------------------------------------------------------------
// Ambil data kegiatan
// ------------------------------------------------------------
$stmt = $pdo->prepare("SELECT k.*, o.nama_organisasi, o.jenis 
                       FROM konten_kegiatan k 
                       JOIN organisasi o ON k.id_organisasi = o.id_organisasi 
                       WHERE k.id_konten = ? AND k.status_publikasi = 'publik'");
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    $_SESSION['error'] = 'Kegiatan tidak ditemukan atau tidak tersedia.';
    header('Location: kegiatan.php');
    exit;
}

// ------------------------------------------------------------
// Kuota & peserta
// ------------------------------------------------------------
$kuota = (int)($kegiatan['kuota'] ?? 50);
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_konten = ? AND status_pendaftaran != 'ditolak'");
$stmtCount->execute([$id_konten]);
$jumlah_peserta = $stmtCount->fetchColumn();
$penuh = $kuota > 0 && $jumlah_peserta >= $kuota;

// ------------------------------------------------------------
// Cek status pendaftaran mahasiswa
// ------------------------------------------------------------
$stmtCek = $pdo->prepare("SELECT * FROM pendaftaran WHERE id_mahasiswa = ? AND id_konten = ?");
$stmtCek->execute([$user_id, $id_konten]);
$sudah_daftar = $stmtCek->fetch(PDO::FETCH_ASSOC);

// ------------------------------------------------------------
// Proses pendaftaran
// ------------------------------------------------------------
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    if ($penuh) {
        $error = 'Maaf, kuota kegiatan sudah penuh.';
    } elseif ($sudah_daftar) {
        $error = 'Anda sudah mendaftar kegiatan ini.';
    } else {
        $insert = $pdo->prepare("INSERT INTO pendaftaran (id_mahasiswa, id_konten, status_pendaftaran, tanggal_daftar) VALUES (?, ?, 'menunggu', NOW())");
        if ($insert->execute([$user_id, $id_konten])) {
            // Set flag bahwa baru saja mendaftar, agar blok "sudah daftar" tidak muncul
            $_SESSION['just_registered'] = true;
            $_SESSION['success'] = 'Pendaftaran berhasil! Menunggu verifikasi dari pengurus.';
            header('Location: form_pendaftaran_kegiatan.php?id_konten=' . $id_konten);
            exit;
        } else {
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}

// ------------------------------------------------------------
// Ambil pesan dari session
// ------------------------------------------------------------
$just_registered = isset($_SESSION['just_registered']) && $_SESSION['just_registered'] === true;
if ($just_registered) {
    unset($_SESSION['just_registered']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profil.css?v=<?php echo time(); ?>">

<style>
.pendaftaran-form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}

.detail-kegiatan-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #FFA007;
}
.detail-kegiatan-card .judul {
    font-size: 1.2rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
}
.detail-kegiatan-card .meta {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.3rem;
}
.detail-kegiatan-card .meta i {
    color: #FFA007;
    width: 20px;
}
.detail-kegiatan-card .deskripsi {
    color: #1e293b;
    line-height: 1.6;
    margin-top: 0.5rem;
}
.detail-kegiatan-card .kuota-info {
    margin-top: 0.8rem;
    padding-top: 0.8rem;
    border-top: 1px solid #e9ecef;
    font-weight: 600;
    color: #071C34;
}
.detail-kegiatan-card .kuota-info .penuh {
    color: #dc2626;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
}
.form-actions .btn-batal {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.4rem 1.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}
.form-actions .btn-batal:hover {
    background: #071C34;
    color: #fff;
}
.form-actions .btn-daftar {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.4rem 1.8rem;
    font-weight: 700;
    transition: 0.3s;
    cursor: pointer;
}
.form-actions .btn-daftar:hover {
    background: #071C34;
    color: #fff;
}
.form-actions .btn-daftar.disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
    pointer-events: none;
}

.status-info {
    background: #fef3c7;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #f59e0b;
    color: #92400e;
}
.status-info.success {
    background: #dcfce7;
    border-color: #22c55e;
    color: #166534;
}
.status-info.error {
    background: #fee2e2;
    border-color: #dc2626;
    color: #991b1b;
}
.status-info i {
    margin-right: 0.5rem;
}
</style>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <h4 class="profil-title">Form Pendaftaran Kegiatan</h4>

            <div class="profil-outer-card">
                <div class="profil-inner-card">

                    <!-- Pesan sukses/error -->
                    <?php if (!empty($success)): ?>
                        <div class="status-info success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="status-info error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Detail Kegiatan -->
                    <div class="detail-kegiatan-card">
                        <div class="judul"><?= htmlspecialchars($kegiatan['judul']) ?></div>
                        <div class="meta">
                            <i class="fa-regular fa-building"></i> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?>
                            <span style="margin-left:1rem;"><i class="fa-regular fa-tag"></i> <?= htmlspecialchars($kegiatan['jenis'] ?? '') ?></span>
                        </div>
                        <div class="meta">
                            <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
                            <?php if (!empty($kegiatan['lokasi'])): ?>
                                <span style="margin-left:1rem;"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($kegiatan['lokasi']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="deskripsi"><?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?></div>
                        <div class="kuota-info">
                            <?php if ($penuh): ?>
                                <span class="penuh"><i class="fas fa-exclamation-circle"></i> Kuota Penuh (<?= $jumlah_peserta ?>/<?= $kuota ?>)</span>
                            <?php else: ?>
                                <span><i class="fas fa-users"></i> Kuota tersedia: <?= $kuota - $jumlah_peserta ?> (<?= $jumlah_peserta ?>/<?= $kuota ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status Pendaftaran (hanya tampil jika sudah daftar dan BUKAN baru saja mendaftar) -->
                    <?php if ($sudah_daftar && !$just_registered): ?>
                        <div class="status-info">
                            <i class="fas fa-info-circle"></i> Anda sudah mendaftar kegiatan ini dengan status 
                            <strong><?= ucfirst($sudah_daftar['status_pendaftaran'] ?? 'Menunggu') ?></strong>.
                            <?php if (($sudah_daftar['status_pendaftaran'] ?? '') == 'menunggu'): ?>
                                <br>Silakan tunggu verifikasi dari pengurus.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="post">
                        <div class="form-actions">
                            <a href="kegiatan.php" class="btn-batal"><i class="fas fa-arrow-left"></i> Kembali</a>
                            <?php if ($sudah_daftar): ?>
                                <button type="button" class="btn-daftar disabled" disabled>Sudah Terdaftar</button>
                            <?php elseif ($penuh): ?>
                                <button type="button" class="btn-daftar disabled" disabled>Kuota Penuh</button>
                            <?php else: ?>
                                <button type="submit" name="daftar" class="btn-daftar" onclick="return confirm('Apakah Anda yakin ingin mendaftar kegiatan ini?')">
                                    <i class="fas fa-check"></i> Daftar Sekarang
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                </div><!-- end inner -->
            </div><!-- end outer -->

        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>