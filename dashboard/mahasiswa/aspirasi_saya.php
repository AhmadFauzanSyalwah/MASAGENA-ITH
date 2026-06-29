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
            LEFT JOIN organisasi o ON a.id_organisasi_tujuan = o.id_organisasi
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

<!-- Tambahan CDN FontAwesome untuk ikon aksi -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    /* Style khusus halaman aspirasi saya */
    .dashboard-welcome {
        display: flex; 
        flex-direction: column; 
        gap: 1.5rem;
        background-color: var(--primary, #0f172a);
        padding: 2rem;
        border-radius: var(--radius, 12px);
    }

    .dashboard-header-row {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 1rem;
    }

    .dashboard-welcome h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--white, #ffffff);
    }

    .dashboard-welcome p {
        margin: 0.35rem 0 0 0;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.75);
    }

    .inner-alert-mahasiswa {
        background-color: rgba(255, 255, 255, 0.08); 
        color: #f1f5f9; 
        border-left: 4px solid var(--accent, #ffaa07); 
        padding: 1rem 1.25rem;
        border-radius: 6px;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .inner-alert-mahasiswa strong {
        color: var(--accent, #ffaa07);
        font-weight: 600;
    }

    .code-chip {
        display: inline-block;
        padding: 0.2rem 0.7rem;
        border-radius: 30px;
        background-color: rgba(255, 160, 7, 0.12);
        color: var(--accent-dark, #b45309);
        font-weight: 700;
        font-size: 0.75rem;
        white-space: nowrap;
    }

    /* ===== TABLE CARD STYLE ===== */
    .table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e9ecef;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-top: 1.5rem;
    }
    .table-card table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        /* Lebar minimal diperbesar sedikit agar pembagian kolom statis tetap rapi */
        min-width: 1050px; 
        table-layout: fixed;
    }
    .table-card thead {
        background: #f8fafc;
        border-bottom: 2px solid #e9ecef;
    }
    .table-card th {
        padding: 0.7rem 0.8rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .table-card td {
        padding: 0.7rem 0.8rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-card tbody tr:hover {
        background: #f8fafc;
    }

    /* === PENYESUAIAN LEBAR KOLOM (Diperbaiki agar proporsional) === */
    .table-card .col-kode { width: 210px; }
    .table-card .col-judul { width: 150px; } /* Diubah dari 'auto' menjadi lebar tetap agar tidak terlalu lebar */
    .table-card .col-kategori { width: 110px; }
    .table-card .col-tujuan { width: 150px; }
    .table-card .col-tanggal { width: 140px; }
    .table-card .col-status { width: 110px; text-align: center; }
    .table-card .col-aksi { width: 170px; text-align: center; }

    /* Agar teks judul yang terlalu panjang turun ke bawah dengan rapi */
    .table-card .col-judul strong {
        display: block;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.4;
    }

    /* ===== TOMBOL AKSI INLINE ===== */
    .action-inline {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        flex-wrap: nowrap;
    }
    .action-inline form {
        display: inline;
    }

    .mini-btn {
        padding: 0.25rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-family: inherit;
        white-space: nowrap;
        justify-content: center;
        border-width: 2px;
        border-style: solid;
        text-decoration: none;
    }

    /* Detail (Kuning/Oranye) */
    .mini-btn.detail {
        background: transparent;
        color: #FFA007;
        border-color: #FFA007;
    }
    .mini-btn.detail:hover {
        background: #FFA007;
        color: #ffffff;
    }

    /* Hapus (Merah) */
    .mini-btn.hapus {
        background: transparent;
        color: #dc2626;
        border-color: #dc2626;
    }
    .mini-btn.hapus:hover {
        background: #dc2626;
        color: #ffffff;
    }

    .text-center-empty {
        text-align: center;
        color: #94a3b8;
        padding: 2.5rem 0;
        font-style: italic;
    }
</style>

<div class="dashboard-welcome">
    <!-- Baris Judul & Tombol -->
    <div class="dashboard-header-row">
        <div>
            <h1>Aspirasi Saya</h1>
            <p>Daftar aspirasi non-anonim milik mahasiswa aktif.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="aspirasi.php" class="btn" style="background-color: rgba(255, 255, 255, 0.15); color: var(--white); box-shadow: none; font-weight: 500;">Kirim Aspirasi Baru</a>
            <a href="cek_status_aspirasi.php" class="btn" style="font-weight: 700;">Cek Status</a>
        </div>
    </div>

    <!-- Mode Mahasiswa -->
    <?php if ($mahasiswa) { ?>
        <div class="inner-alert-mahasiswa">
            Mode mahasiswa: <strong><?= h($mahasiswa['nama']); ?></strong> &mdash; <?= h($mahasiswa['nim']); ?>.<br>
            <span style="opacity: 0.85;">Aspirasi anonim tidak muncul di halaman ini. Cek aspirasi anonim memakai kode aspirasi.</span>
        </div>
    <?php } else { ?>
        <div class="inner-alert-mahasiswa" style="border-left-color: var(--danger, #ef4444); background-color: rgba(239, 68, 68, 0.1);">
            Data mahasiswa tidak ditemukan. Pastikan data mahasiswa tersedia di tabel <strong>tbmahasiswa</strong>.
        </div>
    <?php } ?>
</div>

<div class="main-content">
    <?php if (!$schemaReady) { ?>
        <?php schema_warning(); ?>
    <?php } ?>

    <!-- Tampilan Tabel Menggunakan Format .table-card -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th class="col-kode">Kode</th>
                    <th class="col-judul">Judul</th>
                    <th class="col-kategori">Kategori</th>
                    <th class="col-tujuan">Tujuan</th>
                    <th class="col-tanggal">Tanggal</th>
                    <th class="col-status">Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($aspirasiList)) { ?>
                    <?php foreach ($aspirasiList as $row) { ?>
                        <tr>
                            <td class="col-kode">
                                <span class="code-chip">
                                    <?= h($row['kode_aspirasi'] ?? '-'); ?>
                                </span>
                            </td>
                            <td class="col-judul">
                                <strong style="color: #071C34;"><?= h($row['judul'] ?? '-'); ?></strong>
                            </td>
                            <td class="col-kategori">
                                <span style="background: #f1f5f9; padding: 0.15rem 0.6rem; border-radius: 50px; font-size: 0.7rem; color: #071C34;">
                                    <?= h($row['kategori'] ?? '-'); ?>
                                </span>
                            </td>
                            <td class="col-tujuan" style="color: #64748b; font-weight: 500;">
                                <?= h($row['nama_organisasi'] ?: 'Umum'); ?>
                            </td>
                            <td class="col-tanggal" style="font-size: 0.85rem; color: #64748b;">
                                <i class="fa-regular fa-calendar"></i> <?= h(tanggal_indo($row['created_at'] ?? '')); ?>
                            </td>
                            <td class="col-status">
                                <?= status_aspirasi_badge($row['status'] ?? 'proses'); ?>
                            </td>
                            <td class="col-aksi">
                                <div class="action-inline">
                                    <!-- Tombol Detail -->
                                    <a class="mini-btn detail" href="detail_aspirasi.php?id=<?= (int) $row['id_aspirasi']; ?>" title="Lihat detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    
                                    <!-- Tombol Hapus Form POST -->
                                    <form action="proses_hapus_aspirasi.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus aspirasi [<?= htmlspecialchars($row['judul'] ?? '') ?>] secara permanen?')">
                                        <input type="hidden" name="id_aspirasi" value="<?= (int) $row['id_aspirasi']; ?>">
                                        <button type="submit" class="mini-btn hapus" title="Hapus aspirasi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="text-center-empty">
                            <i class="fa-regular fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e0;"></i>
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