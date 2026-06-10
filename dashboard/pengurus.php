<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya pengurus yang bisa akses
if (!isset($_SESSION['id_pengurus']) || $_SESSION['peran'] !== 'pengurus') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Ambil data pengurus beserta organisasi
$stmt = $conn->prepare("
    SELECT po.*, o.nama_organisasi, o.deskripsi, o.logo
    FROM pengurus_organisasi po
    JOIN organisasi o ON po.id_organisasi = o.id_organisasi
    WHERE po.id_pengurus = :id
");
$stmt->execute([':id' => $_SESSION['id_pengurus']]);
$pengurus = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengurus) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$id_organisasi = $pengurus['id_organisasi'];

// Statistik
$totalPendaftar = $conn->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_organisasi = :id");
$totalPendaftar->execute([':id' => $id_organisasi]);
$totalPendaftar = $totalPendaftar->fetchColumn();

$totalKegiatan = $conn->query("SELECT COUNT(*) FROM konten_kegiatan")->fetchColumn();
$totalMahasiswa = $conn->query("SELECT COUNT(*) FROM tbmahasiswa")->fetchColumn();

require_once __DIR__ . '/../include/header.php';
?>

    <!-- ==================== HERO PENGURUS ==================== -->
    <div class="container-fluid text-white py-5" style="background: linear-gradient(135deg, #1B4C85, #0f3b5c); border-radius: 0 0 2rem 2rem;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-2">Dashboard Pengurus</h1>
                    <p class="lead mb-1">Selamat datang, <?= htmlspecialchars($pengurus['nama_pengurus']) ?>.</p>
                    <p class="mb-0">Anda mengelola organisasi: <strong><?= htmlspecialchars($pengurus['nama_organisasi']) ?></strong> (<?= htmlspecialchars($pengurus['jabatan']) ?>)</p>
                </div>
                <div class="col-md-4 text-center d-none d-md-block">
                    <div style="background: rgba(255,255,255,0.1); border-radius: 1rem; padding: 1rem;">
                        <span style="font-size: 1.2rem;">Organisasi</span>
                        <h5><?= htmlspecialchars($pengurus['nama_organisasi']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== STATISTIK ==================== -->
    <div class="container mt-4">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #FFA007;">
                    <div class="card-body">
                        <h3 class="card-title" style="color:#1B4C85;"><?= $totalPendaftar ?></h3>
                        <p class="card-text text-muted">Pendaftar Kegiatan Organisasi Anda</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1B4C85;">
                    <div class="card-body">
                        <h3 class="card-title" style="color:#1B4C85;"><?= $totalKegiatan ?></h3>
                        <p class="card-text text-muted">Total Kegiatan di Kampus</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754;">
                    <div class="card-body">
                        <h3 class="card-title" style="color:#1B4C85;"><?= $totalMahasiswa ?></h3>
                        <p class="card-text text-muted">Mahasiswa Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MENU CEPAT ==================== -->
        <h2 class="fw-bold mb-3" style="color:#1B4C85;">Manajemen Organisasi</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/kegiatan/tambah.php" class="card border-0 shadow-sm text-decoration-none h-100 p-4" style="transition: all 0.2s ease; border-radius: 12px;">
                    <h5 style="color:#1B4C85;">Tambah Kegiatan</h5>
                    <p class="text-muted mb-0">Publikasikan kegiatan baru untuk mahasiswa</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/kegiatan/index.php" class="card border-0 shadow-sm text-decoration-none h-100 p-4" style="transition: all 0.2s ease; border-radius: 12px;">
                    <h5 style="color:#1B4C85;">Kelola Kegiatan</h5>
                    <p class="text-muted mb-0">Edit atau hapus kegiatan yang sudah ada</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/pendaftaran/kelola.php" class="card border-0 shadow-sm text-decoration-none h-100 p-4" style="transition: all 0.2s ease; border-radius: 12px;">
                    <h5 style="color:#1B4C85;">Data Pendaftar</h5>
                    <p class="text-muted mb-0">Lihat mahasiswa yang mendaftar kegiatan Anda</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/aspirasi/index.php" class="card border-0 shadow-sm text-decoration-none h-100 p-4" style="transition: all 0.2s ease; border-radius: 12px;">
                    <h5 style="color:#1B4C85;">Aspirasi (jika tersedia)</h5>
                    <p class="text-muted mb-0">Baca aspirasi dari mahasiswa (segera hadir)</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/organisasi/detail.php" class="card border-0 shadow-sm text-decoration-none h-100 p-4" style="transition: all 0.2s ease; border-radius: 12px;">
                    <h5 style="color:#1B4C85;">Profil Organisasi</h5>
                    <p class="text-muted mb-0">Lihat atau ubah profil organisasi Anda</p>
                </a>
            </div>
        </div>
    </div>

    <style>
        a.card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15) !important;
        }
        a.card:hover h5 {
            color: #FFA007 !important;
        }
    </style>

<?php require_once __DIR__ . '/../include/footer.php'; ?>