<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Wajib login
if (!isset($_SESSION['peran'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$peran = $_SESSION['peran'];
$data = null;

// Ambil data sesuai peran
if ($peran === 'mahasiswa' && isset($_SESSION['id_mahasiswa'])) {
    $stmt = $conn->prepare("SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id");
    $stmt->execute([':id' => $_SESSION['id_mahasiswa']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $dashboard_url = BASE_URL . '/dashboard/mahasiswa.php';
} elseif ($peran === 'pengurus' && isset($_SESSION['id_pengurus'])) {
    $stmt = $conn->prepare("
        SELECT po.*, o.nama_organisasi
        FROM pengurus_organisasi po
        JOIN organisasi o ON po.id_organisasi = o.id_organisasi
        WHERE po.id_pengurus = :id
    ");
    $stmt->execute([':id' => $_SESSION['id_pengurus']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $dashboard_url = BASE_URL . '/dashboard/pengurus.php';
} elseif ($peran === 'admin' && isset($_SESSION['id_admin'])) {
    $stmt = $conn->prepare("SELECT * FROM administrator WHERE id_admin = :id");
    $stmt->execute([':id' => $_SESSION['id_admin']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $dashboard_url = BASE_URL . '/dashboard/admin.php';
}

// Jika data tidak ditemukan, kembali ke login
if (!$data) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

require_once __DIR__ . '/include/header.php';
?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4" style="color: #1B4C85;">Profil Saya</h2>

                        <?php if ($peran === 'mahasiswa'): ?>
                            <table class="table table-borderless">
                                <tr><td class="fw-bold" style="color: #1B4C85;">NIM</td><td>: <?= htmlspecialchars($data['nim']) ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Nama</td><td>: <?= htmlspecialchars($data['nama']) ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Email</td><td>: <?= htmlspecialchars($data['email'] ?? '-') ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Status</td><td>: <?= $data['is_verified'] == '1' ? '<span class="text-success">Terverifikasi</span>' : '<span class="text-danger">Belum Diverifikasi</span>' ?></td></tr>
                            </table>

                        <?php elseif ($peran === 'pengurus'): ?>
                            <table class="table table-borderless">
                                <tr><td class="fw-bold" style="color: #1B4C85;">Nama</td><td>: <?= htmlspecialchars($data['nama_pengurus']) ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Jabatan</td><td>: <?= htmlspecialchars($data['jabatan']) ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Organisasi</td><td>: <?= htmlspecialchars($data['nama_organisasi']) ?></td></tr>
                            </table>

                        <?php elseif ($peran === 'admin'): ?>
                            <table class="table table-borderless">
                                <tr><td class="fw-bold" style="color: #1B4C85;">Username</td><td>: <?= htmlspecialchars($data['username']) ?></td></tr>
                                <tr><td class="fw-bold" style="color: #1B4C85;">Nama</td><td>: <?= htmlspecialchars($data['nama_lengkap']) ?></td></tr>
                            </table>
                        <?php endif; ?>

                        <a href="<?= $dashboard_url ?>" class="btn btn-outline-primary rounded-pill mt-3">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/include/footer.php'; ?>