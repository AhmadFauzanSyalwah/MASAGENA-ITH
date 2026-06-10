<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['id_admin']) || $_SESSION['peran'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Ambil data admin yang login
$stmt = $conn->prepare("SELECT * FROM administrator WHERE id_admin = :id");
$stmt->execute([':id' => $_SESSION['id_admin']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Statistik sistem (perbaikan untuk enum)
$totalMahasiswa = $conn->query("SELECT COUNT(*) FROM tbmahasiswa")->fetchColumn();
$belumVerifikasi = $conn->query("SELECT COUNT(*) FROM tbmahasiswa WHERE is_verified = '0'")->fetchColumn();
$totalOrganisasi = $conn->query("SELECT COUNT(*) FROM organisasi")->fetchColumn();
$totalKegiatan = $conn->query("SELECT COUNT(*) FROM konten_kegiatan")->fetchColumn();

require_once __DIR__ . '/../include/header.php';
?>

    <!-- Hero Admin -->
    <div class="container-fluid text-white py-4" style="background: linear-gradient(135deg, #1B4C85, #0a2f4a); border-radius: 0 0 2rem 2rem;">
        <div class="container">
            <h1 class="fw-bold mb-2">Dashboard Administrator</h1>
            <p class="lead mb-0">Selamat datang, <?= htmlspecialchars($admin['nama_lengkap']) ?>. Kelola sistem dari sini.</p>
        </div>
    </div>

    <!-- Statistik -->
    <div class="container mt-4">
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #1B4C85; border-radius: 12px;">
                    <div class="card-body">
                        <h3 style="color:#1B4C85;"><?= $totalMahasiswa ?></h3>
                        <p class="text-muted mb-0">Mahasiswa Terdaftar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #dc3545; border-radius: 12px;">
                    <div class="card-body">
                        <h3 style="color:#dc3545;"><?= $belumVerifikasi ?></h3>
                        <p class="text-muted mb-0">Belum Diverifikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #FFA007; border-radius: 12px;">
                    <div class="card-body">
                        <h3 style="color:#1B4C85;"><?= $totalOrganisasi ?></h3>
                        <p class="text-muted mb-0">Organisasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #198754; border-radius: 12px;">
                    <div class="card-body">
                        <h3 style="color:#1B4C85;"><?= $totalKegiatan ?></h3>
                        <p class="text-muted mb-0">Kegiatan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Administrasi -->
        <h2 class="fw-bold mb-3" style="color:#1B4C85;">Menu Administrasi</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>/admin/pengguna.php" class="card border-0 shadow-sm text-decoration-none p-4 h-100" style="transition: all 0.2s;">
                    <h5 style="color:#1B4C85;">Verifikasi Pengguna</h5>
                    <p class="text-muted mb-0">Aktifkan akun mahasiswa yang baru mendaftar.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>/admin/organisasi_kelola.php" class="card border-0 shadow-sm text-decoration-none p-4 h-100" style="transition: all 0.2s;">
                    <h5 style="color:#1B4C85;">Kelola Organisasi</h5>
                    <p class="text-muted mb-0">Tambah, edit, atau hapus organisasi kemahasiswaan.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>/admin/konten_moderasi.php" class="card border-0 shadow-sm text-decoration-none p-4 h-100" style="transition: all 0.2s;">
                    <h5 style="color:#1B4C85;">Moderasi Konten</h5>
                    <p class="text-muted mb-0">Pantau komentar dan konten yang dilaporkan.</p>
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