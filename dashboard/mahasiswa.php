<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_mahasiswa']) || $_SESSION['peran'] !== 'mahasiswa') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id");
$stmt->execute([':id' => $_SESSION['id_mahasiswa']]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mahasiswa || $mahasiswa['is_verified'] != 1) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

require_once __DIR__ . '/../include/header.php';
?>

    <!-- ==================== HERO ==================== -->
    <div class="container-fluid text-white py-5" style="background: linear-gradient(135deg, #1B4C85, #2a6cb6); border-radius: 0 0 2rem 2rem;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="display-5 fw-bold mb-3">Selamat Datang, <?= htmlspecialchars($mahasiswa['nama']) ?>!</h1>
                    <p class="lead">NIM: <?= htmlspecialchars($mahasiswa['nim']) ?></p>
                    <p>Akses informasi terkini seputar kegiatan dan berita kampus.</p>
                    <a href="#berita" class="btn btn-lg rounded-pill" style="background:#FFA007; color:#1B4C85; font-weight:600; border:none;">Lihat Berita</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== STATISTIK ==================== -->
    <div class="container mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4">
                    <h3 style="color:#1B4C85;">6</h3>
                    <p class="text-muted mb-0">Organisasi Aktif</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4">
                    <h3 style="color:#1B4C85;">24</h3>
                    <p class="text-muted mb-0">Mahasiswa</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4">
                    <h3 style="color:#1B4C85;">12</h3>
                    <p class="text-muted mb-0">Berita Terkini</p>
                </div>
            </div>
        </div>

        <!-- ==================== BERITA TERKINI ==================== -->
        <div class="d-flex justify-content-between align-items-center mb-3" id="berita">
            <h2 style="color:#1B4C85;">Berita Terkini</h2>
            <a href="<?= BASE_URL ?>/berita/index.php" class="btn btn-outline-primary rounded-pill">Lihat Semua</a>
        </div>
        <div class="row g-4">
            <?php
            // Data dummy berita – nanti diambil dari database
            $berita = [
                    ['judul' => 'Seminar Nasional Inovasi Digital', 'ringkasan' => 'Himpunan Teknik mengadakan seminar nasional dengan pembicara dari industri.', 'tanggal' => '2026-06-15'],
                    ['judul' => 'Pengumuman Libur Akademik', 'ringkasan' => 'Rektorat mengumumkan jadwal libur semester genap tahun 2026.', 'tanggal' => '2026-06-10'],
                    ['judul' => 'Workshop Kewirausahaan Mahasiswa', 'ringkasan' => 'Study Club Ekonomi menghadirkan workshop untuk memulai bisnis.', 'tanggal' => '2026-06-05']
            ];
            foreach ($berita as $b): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:16px; transition: transform 0.2s;">
                        <div class="card-body">
                            <h5 class="card-title" style="color:#1B4C85;"><?= $b['judul'] ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?= $b['ringkasan'] ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= date('d M Y', strtotime($b['tanggal'])) ?></small>
                                <a href="#" class="btn btn-sm btn-outline-warning rounded-pill" style="border-color:#FFA007; color:#FFA007;">Baca</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>