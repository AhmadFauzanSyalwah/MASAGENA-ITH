<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once '../../config/session_check.php';

// Pastikan menggunakan $pdo (objek PDO)
$schemaReady = aspirasi_schema_ready($pdo);
$kode = trim($_GET['kode'] ?? $_POST['kode_aspirasi'] ?? '');
$success = isset($_GET['success']);
$data = null;
$komentar = [];

if ($schemaReady && $kode !== '') {
    try {
        // Menggunakan Prepared Statement PDO
        $stmt = $pdo->prepare("
            SELECT
                a.*,
                o.nama_organisasi,
                m.nama AS nama_mahasiswa,
                m.nim,
                m.email
            FROM aspirasi a
            LEFT JOIN organisasi o ON a.id_organisasi_tujuan = o.id_organisasi
            LEFT JOIN tbmahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
            WHERE a.kode_aspirasi = ?
            LIMIT 1
        ");
        $stmt->execute([$kode]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $stmtKomentar = $pdo->prepare("
                SELECT *
                FROM komentar_aspirasi
                WHERE id_aspirasi = ?
                ORDER BY created_at ASC
            ");
            $stmtKomentar->execute([$data['id_aspirasi']]);
            $komentar = $stmtKomentar->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        die('Query cek status aspirasi gagal: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<style>
    /* Tambahan style khusus halaman cek status (menyesuaikan style.css root) */
    .status-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
        margin-top: 1rem;
    }

    .status-form-wrapper {
        position: sticky;
        top: 90px;
    }

    .code-chip {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        background: var(--bg-body);
        padding: 1rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }

    .meta-item span {
        display: block;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.3rem;
    }

    .meta-item strong {
        font-size: 0.95rem;
        color: var(--primary);
    }

    .content-box {
        background: var(--bg-body);
        padding: 1.5rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        line-height: 1.7;
        margin-bottom: 2rem;
        color: var(--text-dark);
    }

    /* Penyesuaian form profil untuk card kecil */
    .status-form-custom {
        padding: 0;
        max-width: 100%;
        background: transparent;
    }
    
    .status-form-custom label {
        margin-top: 0;
        margin-bottom: 0.5rem;
    }

    .status-form-custom button {
        width: 100%;
        margin-top: 1rem;
    }

    /* Badge Status */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-process { color: #92400e; background: #fef3c7; }
    .badge-done { color: var(--success); background: #dcfce7; }
    .badge-rejected { color: var(--danger); background: #fee2e2; }

    /* Komentar */
    .comment-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 1rem;
    }

    .comment-item {
        background: var(--bg-body);
        padding: 1.2rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }

    .comment-item.admin-comment {
        background-color: rgba(7, 28, 52, 0.05);
        border-color: rgba(7, 28, 52, 0.1);
    }

    .comment-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .comment-head strong { color: var(--primary); }
    .comment-head span { color: var(--text-muted); font-size: 0.8rem; }
    
    .empty-text {
        color: var(--text-muted);
        font-style: italic;
    }

    @media (max-width: 900px) {
        .status-layout { grid-template-columns: 1fr; }
        .status-form-wrapper { position: static; }
    }
</style>

<div class="dashboard-welcome">
    <h1>Cek Status Aspirasi</h1>
    <p>Masukkan kode aspirasi untuk melihat status tindak lanjut dari laporan atau aspirasi yang telah Anda kirimkan.</p>
</div>

<div class="main-content">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
        <a href="aspirasi.php" class="btn">Kirim Aspirasi Baru</a>
    </div>

    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <?php if ($success && $kode !== '') { ?>
        <div class="alert">
            Aspirasi berhasil dikirim.
            Simpan kode ini untuk mengecek status:
            <strong><?= h($kode); ?></strong>
        </div>
    <?php } ?>

    <section class="status-layout">
        <div class="status-form-wrapper">
            <div class="card">
                <h3 style="color: var(--primary); margin-bottom: 1.2rem; border-left: 4px solid var(--accent); padding-left: 0.5rem;">Cek Status</h3>
                <form class="profil-form status-form-custom" method="POST" action="cek_status_aspirasi.php">
                    <label>Kode Aspirasi</label>
                    <input type="text" name="kode_aspirasi" value="<?= h($kode); ?>" placeholder="Contoh: ASP-2606..." required>
                    <button type="submit" class="btn" <?= !$schemaReady ? 'disabled' : ''; ?>>
                        Cek Status
                    </button>
                </form>
            </div>
        </div>

        <div>
            <?php if ($schemaReady && $kode !== '' && !$data) { ?>
                <div class="error">Data aspirasi dengan kode tersebut tidak ditemukan.</div>
            <?php } ?>

            <?php if ($data) { ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span class="code-chip"><?= h($data['kode_aspirasi']); ?></span>
                            <h3 style="color: var(--primary); font-size: 1.5rem; margin-top: 0.5rem;"><?= h($data['judul']); ?></h3>
                        </div>
                        <?= status_aspirasi_badge($data['status']); ?>
                    </div>

                    <div class="meta-grid">
                        <div class="meta-item"><span>Kategori</span><strong><?= h($data['kategori']); ?></strong></div>
                        <div class="meta-item"><span>Tujuan</span><strong><?= h($data['nama_organisasi'] ?: 'Umum'); ?></strong></div>
                        <div class="meta-item"><span>Pengirim</span><strong><?= ((int) $data['is_anonim'] === 1) ? 'Anonim' : h($data['nama_mahasiswa'] ?: 'Mahasiswa'); ?></strong></div>
                        <div class="meta-item"><span>Tanggal</span><strong><?= h(tanggal_indo($data['created_at'])); ?></strong></div>
                    </div>

                    <div class="content-box">
                        <?= nl2br(h($data['isi_aspirasi'])); ?>
                    </div>

                    <h4 style="color: var(--primary); border-left: 4px solid var(--accent); padding-left: 0.5rem; margin-bottom: 1rem; font-size: 1.2rem;">Tanggapan</h4>
                    
                    <?php if (!empty($komentar)) { ?>
                        <div class="comment-list">
                            <?php foreach ($komentar as $row) { ?>
                                <div class="comment-item <?= ($row['level_user'] ?? '') === 'admin' ? 'admin-comment' : ''; ?>">
                                    <div class="comment-head">
                                        <strong><?= ($row['level_user'] ?? '') === 'admin' ? 'Pengurus/Admin' : 'Mahasiswa'; ?></strong>
                                        <span><?= h(tanggal_indo($row['created_at'])); ?></span>
                                    </div>
                                    <p style="margin-top: 0.5rem; color: var(--text-dark);"><?= nl2br(h($row['isi_komentar'])); ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p class="empty-text">Belum ada tanggapan terkait aspirasi ini.</p>
                    <?php } ?>

                    <div style="margin-top: 2rem;">
                        <a class="btn-sm" href="detail_aspirasi.php?id=<?= (int) $data['id_aspirasi']; ?>">Lihat Detail Lengkap</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../include/footer.php'; ?>