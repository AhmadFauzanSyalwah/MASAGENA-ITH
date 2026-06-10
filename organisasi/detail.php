<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM organisasi WHERE id_organisasi = :id");
$stmt->execute([':id' => $id]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$org) {
    header('Location: ' . BASE_URL . '/organisasi/index.php');
    exit;
}

// Ambil pengurus organisasi ini
$stmt = $conn->prepare("SELECT * FROM pengurus_organisasi WHERE id_organisasi = :id");
$stmt->execute([':id' => $id]);
$pengurus = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../include/header.php';
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;"><?= htmlspecialchars($org['nama_organisasi']) ?></h2>
        <p><?= htmlspecialchars($org['deskripsi'] ?? 'Tidak ada deskripsi') ?></p>

        <h4 class="mt-4">Daftar Pengurus</h4>
        <?php if (isset($_SESSION['peran']) && ($_SESSION['peran'] === 'admin' ||
                (isset($_SESSION['id_organisasi']) && $_SESSION['id_organisasi'] == $id))): ?>
            <a href="kelola_pengurus.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm mb-2">Kelola Pengurus</a>
        <?php endif; ?>

        <ul class="list-group">
            <?php foreach ($pengurus as $p): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($p['nama_pengurus']) ?> — <?= htmlspecialchars($p['jabatan']) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'admin'): ?>
            <div class="mt-3">
                <a href="edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus organisasi ini?')">Hapus</a>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>