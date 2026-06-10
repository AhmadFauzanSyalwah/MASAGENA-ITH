<?php
session_start();
require_once __DIR__ . '/../config/database.php';
$id_org = $_GET['id'] ?? 0;

// ========== Cek Akses ==========
$allowed = false;
if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'admin') {
    $allowed = true;
} elseif (isset($_SESSION['id_pengurus'], $_SESSION['id_organisasi']) && $_SESSION['id_organisasi'] == $id_org) {
    // Cek jabatan – hanya ketua yang boleh
    $stmt = $conn->prepare("SELECT jabatan FROM pengurus_organisasi WHERE id_pengurus = :id");
    $stmt->execute([':id' => $_SESSION['id_pengurus']]);
    $jabatan = $stmt->fetchColumn();
    if ($jabatan && stripos($jabatan, 'Ketua') !== false) {
        $allowed = true;
    }
}
if (!$allowed) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

// ========== Ambil Data Organisasi ==========
$stmt = $conn->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
$stmt->execute([':id' => $id_org]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$org) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

// ========== Ambil Daftar Pengurus ==========
$stmt = $conn->prepare("SELECT * FROM pengurus_organisasi WHERE id_organisasi = :id ORDER BY id_pengurus");
$stmt->execute([':id' => $id_org]);
$pengurus_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../include/header.php';
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;">Kelola Pengurus: <?= htmlspecialchars($org['nama_organisasi']) ?></h2>

        <!-- Tombol Tambah Pengurus -->
        <a href="tambah_pengurus.php?id=<?= $id_org ?>" class="btn btn-success mb-3">+ Tambah Pengurus</a>

        <!-- Daftar Pengurus -->
        <h4>Daftar Pengurus</h4>
        <?php if (count($pengurus_list) > 0): ?>
            <div class="list-group">
                <?php foreach ($pengurus_list as $p): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($p['nama_pengurus']) ?></strong>
                            <span class="text-muted"> — <?= htmlspecialchars($p['jabatan']) ?></span>
                        </div>
                        <div class="btn-group">
                            <a href="edit_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="hapus_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengurus ini?')">Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Belum ada pengurus terdaftar di organisasi ini.</p>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>