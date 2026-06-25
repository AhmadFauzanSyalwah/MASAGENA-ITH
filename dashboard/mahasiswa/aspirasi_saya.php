<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once '../../config/session_check.php';

// Pastikan menggunakan $pdo (objek PDO)
$schemaReady = aspirasi_schema_ready($pdo);
$mahasiswa = active_mahasiswa($pdo);
$aspirasiList = [];

if ($schemaReady && $mahasiswa) {
    try {
        // Menggunakan Prepared Statement PDO
        $stmt = $pdo->prepare("
            SELECT 
                a.*, 
                o.nama_organisasi
            FROM aspirasi a
            LEFT JOIN organisasi o ON a.id_organisasi = o.id_organisasi
            WHERE a.id_mahasiswa = ?
            ORDER BY a.created_at DESC
        ");
        
        $stmt->execute([ (int) $mahasiswa['id_mahasiswa'] ]);
        $aspirasiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        die('Query aspirasi saya gagal: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<style>
    /* Tambahan style khusus halaman aspirasi saya (menyesuaikan style.css root) */
    .code-chip {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        background-color: rgba(255, 160, 7, 0.15);
        color: var(--accent-dark);
        font-weight: 700;
        font-size: 0.75rem;
        white-space: nowrap;
    }

    /* Status Badge (asumsi class ini dihasilkan dari fungsi status_aspirasi_badge) */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-process { color: #92400e; background: #fef3c7; }
    .badge-done { color: var(--success); background: #dcfce7; }
    .badge-rejected { color: var(--danger); background: #fee2e2; }

    .empty-cell {
        color: var(--text-muted);
        text-align: center;
        font-style: italic;
        padding: 2rem !important;
    }
    
    .table-responsive {
        overflow-x: auto;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
    }
</style>

<!-- Banner Welcome langsung di luar seperti organisasi.php -->
<div class="dashboard-welcome">
    <h1>Aspirasi Saya</h1>
    <p>Daftar aspirasi non-anonim milik mahasiswa aktif.</p>
</div>

<!-- Sisa konten dibungkus main-content -->
<div class="main-content">
    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <a href="aspirasi.php" class="btn">Kirim Aspirasi Baru</a>
        <a href="cek_status_aspirasi.php" class="btn" style="background-color: var(--success); color: var(--white);">Cek Kode Aspirasi</a>
    </div>

    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <?php if ($mahasiswa) { ?>
        <div class="alert" style="background-color: rgba(255, 160, 7, 0.15); color: var(--primary); border-left-color: var(--accent);">
            Mode mahasiswa:
            <strong><?= h($mahasiswa['nama']); ?></strong>
            — <?= h($mahasiswa['nim']); ?>.<br>
            Aspirasi anonim tidak muncul di halaman ini. Cek aspirasi anonim memakai kode aspirasi.
        </div>
    <?php } else { ?>
        <div class="error">
            Data mahasiswa tidak ditemukan. Pastikan data mahasiswa tersedia di tabel <strong>tbmahasiswa</strong>.
        </div>
    <?php } ?>

    <div class="table-responsive">
        <table class="aspirasi-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($aspirasiList)) { ?>
                    <?php foreach ($aspirasiList as $row) { ?>
                        <tr>
                            <td>
                                <span class="code-chip">
                                    <?= h($row['kode_aspirasi'] ?? '-'); ?>
                                </span>
                            </td>
                            <td style="font-weight: 500; color: var(--primary);"><?= h($row['judul'] ?? '-'); ?></td>
                            <td><?= h($row['kategori'] ?? '-'); ?></td>
                            <td><?= h($row['nama_organisasi'] ?: 'Umum'); ?></td>
                            <td style="white-space: nowrap;"><?= h(tanggal_indo($row['created_at'] ?? '')); ?></td>
                            <td><?= status_aspirasi_badge($row['status'] ?? 'proses'); ?></td>
                            <td>
                                <a class="btn-sm" href="detail_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="empty-cell">
                            Belum ada aspirasi non-anonim yang Anda kirimkan.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../include/footer.php';
?>