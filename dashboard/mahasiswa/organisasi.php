<?php
// organisasi.php
// Pastikan letak file database sesuai dengan struktur folder Anda
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Mengambil data organisasi dari database
try {
    $stmt = $pdo->query("SELECT * FROM organisasi ORDER BY nama_organisasi ASC");
    $organisasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $organisasi = [];
    $error_msg = $e->getMessage();
}

// Sertakan header halaman
include '../../include/header.php';
?>

<style>
    /* Tambahan style khusus halaman organisasi (menyesuaikan style.css root) */
    .org-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .org-card h3 {
        font-size: 1.2rem;
    }
    
    .org-card p {
        font-size: 0.85rem;
        color: var(--text-dark);
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .org-card .jenis {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        align-self: flex-start;
        margin-bottom: 0.6rem;
        font-weight: 700;
    }

    .org-card .meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .org-card .card-actions {
        margin-top: auto;
        padding-top: 0.5rem;
    }
</style>

<div class="dashboard-welcome">
    <h1>Direktori Organisasi Mahasiswa</h1>
    <p>Kenali lebih dekat lembaga kemahasiswaan, himpunan, dan unit kegiatan yang ada di Institut Teknologi Bacharuddin Jusuf Habibie.</p>
</div>

<div class="main-content">
    <h2>Daftar Organisasi</h2>

    <?php if (!empty($organisasi)): ?>
        <div class="organisasi-grid">
            <?php foreach ($organisasi as $org): ?>
                <div class="card org-card">
                    <span class="jenis"><?= htmlspecialchars($org['kategori'] ?? 'Ormawa') ?></span>
                    
                    <h3>
                        <a href="detail_organisasi.php?id=<?= $org['id_organisasi'] ?>">
                            <?= htmlspecialchars($org['nama_organisasi']) ?>
                        </a>
                    </h3>
                    
                    <p class="meta">
                        <i class="fas fa-bullseye"></i> Singkatan: <strong><?= htmlspecialchars($org['singkatan'] ?? '-') ?></strong>
                    </p>
                    
                    <p><?= nl2br(htmlspecialchars(substr($org['deskripsi'] ?? '', 0, 120))) ?><?= strlen($org['deskripsi'] ?? '') > 120 ? '...' : '' ?></p>
                    
                    <div class="card-actions">
                        <a href="detail_organisasi.php?id=<?= $org['id_organisasi'] ?>" class="btn-sm">Lihat Profil</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert" style="margin-top: 1.5rem;">
            Belum ada data organisasi yang terdaftar saat ini.
        </div>
    <?php endif; ?>

</div>

<?php include '../../include/footer.php'; ?>    