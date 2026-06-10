<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../include/header.php';

// Ambil semua organisasi
$stmt = $conn->query("SELECT * FROM organisasi ORDER BY nama_organisasi");
$organisasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container mt-4">
        <h2 style="color: #1B4C85;">Daftar Organisasi</h2>
        <?php if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'admin'): ?>
            <a href="tambah.php" class="btn btn-primary mb-3">Tambah Organisasi</a>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($organisasi as $org): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title" style="color: #1B4C85;"><?= htmlspecialchars($org['nama_organisasi']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($org['deskripsi'] ?? '', 0, 100)) ?>...</p>
                            <a href="detail.php?id=<?= $org['id_organisasi'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>