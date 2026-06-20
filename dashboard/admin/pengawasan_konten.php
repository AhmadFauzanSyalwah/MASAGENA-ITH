<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$pesan = '';
$tipe_pesan = '';

// --- PROSES AKSI (Ubah Status / Hapus) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_konten = intval($_GET['id']);
    $action = $_GET['action'];

    try {
        if ($action === 'toggle_status') {
            $stmt = $pdo->prepare("SELECT status_publikasi FROM konten_kegiatan WHERE id_konten = ?");
            $stmt->execute([$id_konten]);
            $konten = $stmt->fetch();

            if ($konten) {
                $status_baru = ($konten['status_publikasi'] === 'publish') ? 'unpublish' : 'publish';
                
                $update_stmt = $pdo->prepare("UPDATE konten_kegiatan SET status_publikasi = ? WHERE id_konten = ?");
                $update_stmt->execute([$status_baru, $id_konten]);
                
                $pesan = "Status konten berhasil diubah menjadi <strong>" . ucfirst($status_baru) . "</strong>.";
                $tipe_pesan = "success";
            }
        } elseif ($action === 'delete') {
            $delete_stmt = $pdo->prepare("DELETE FROM konten_kegiatan WHERE id_konten = ?");
            $delete_stmt->execute([$id_konten]);
            
            $pesan = "Konten berhasil dihapus secara permanen dari sistem.";
            $tipe_pesan = "success";
        }
    } catch (PDOException $e) {
        $pesan = "Gagal memproses aksi: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

// --- AMBIL DATA SEMUA KONTEN ---
try {
    $query_konten = "
        SELECT k.*, o.nama_organisasi 
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        ORDER BY k.created_at DESC
    ";
    $stmt_konten = $pdo->query($query_konten);
    $semua_konten = $stmt_konten->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data konten: " . $e->getMessage());
}

include '../../include/header.php';
?>

<style>
    /* Menggunakan struktur internal halaman admin yang sinkron dengan stylesheet utama */
    .admin-title-section {
        margin-bottom: 1.5rem;
    }
    
    .admin-title-section h2 {
        font-size: 1.3rem;
        border-left: 4px solid var(--accent);
        padding-left: 0.75rem;
        margin-bottom: 1rem;
        color: var(--primary);
    }
    
    .admin-title-section p {
        color: var(--text-muted);
        font-size: 0.85rem;
        padding-left: 1rem;
    }

    /* Penyesuaian Pembungkus Tabel Responsif Berbasis Card Utama */
    .table-responsive-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        width: 100%;
        overflow-x: auto;
    }

    /* Modifikasi Kelas Khusus Tabel Admin */
    .custom-admin-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--white);
    }

    .custom-admin-table th {
        background-color: var(--primary);
        color: var(--white);
        padding: 0.75rem;
        text-align: left;
        font-size: 0.85rem;
    }

    .custom-admin-table td {
        padding: 0.75rem;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
        font-size: 0.85rem;
        color: var(--text-dark);
    }

    .custom-admin-table tbody tr:hover {
        background-color: var(--bg-body);
    }

    /* Pewarnaan Badge Status Terbuka / Sembunyi */
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 30px;
        text-align: center;
    }
    
    .status-active {
        background-color: #dcfce7;
        color: var(--success);
        border-left: 4px solid var(--success);
    }
    
    .status-hidden {
        background-color: #fee2e2;
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }

    /* Grup Tombol Kontrol */
    .action-group {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Pemetaan Tombol Sembunyikan Menggunakan Variabel Sistem Primary-Light */
    .btn-action-hide {
        background-color: var(--primary-light);
        color: var(--white);
    }
    .btn-action-hide:hover {
        background-color: var(--primary);
        color: var(--accent);
    }

    /* Pemetaan Tombol Tampilkan Menggunakan Skema Dasar btn-sm */
    .btn-action-show {
        background-color: var(--accent);
        color: var(--primary);
    }
    .btn-action-show:hover {
        background-color: var(--accent-dark);
    }

    /* Pemetaan Tombol Hapus Menggunakan Variabel Danger */
    .btn-action-delete {
        background-color: #ef4444;
        color: var(--white);
    }
    .btn-action-delete:hover {
        background-color: var(--danger);
    }

    /* Pembatasan Multiline Deskripsi */
    .text-truncate-multiline {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    /* Kelas Khusus untuk Penanda Sorot Baris Data */
    .row-highlight {
        background-color: #fffbeb !important;
        border-left: 4px solid var(--accent);
    }

    .kategori-tag {
        background: var(--bg-body); 
        padding: 2px 6px; 
        border-radius: var(--radius-sm); 
        color: var(--text-dark); 
        font-size: 0.7rem; 
        font-weight: 500;
        display: inline-block;
        margin-top: 0.25rem;
    }
</style>

<div class="main-container">
    
    <div class="admin-title-section">
        <h2>Pengawasan Konten Kegiatan</h2>
        <p>Halaman kontrol penuh untuk memoderasi, menyembunyikan, atau menghapus konten informasi dari organisasi mahasiswa.</p>
    </div>

    <?php if (!empty($pesan) && $tipe_pesan === 'success'): ?>
        <div class="alert">
            <?= $pesan ?>
        </div>
    <?php elseif (!empty($pesan) && $tipe_pesan === 'danger'): ?>
        <div class="error">
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive-card">
        <table class="custom-admin-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Judul & Informasi Kegiatan</th>
                    <th style="width: 20%;">Penyelenggara</th>
                    <th style="width: 13%;">Pelaksanaan</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 15%; text-align: center;">Aksi Kontrol</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($semua_konten) > 0): ?>
                    <?php $no = 1; foreach ($semua_konten as $row): ?>
                        <tr class="<?= (isset($_GET['edit']) && $_GET['edit'] == $row['id_konten']) ? 'row-highlight' : '' ?>">
                            <td><?= $no++ ?></td>
                            <td>
                                <strong style="color: var(--primary); font-size: 0.95rem; display: block;"><?= htmlspecialchars($row['judul']) ?></strong>
                                <span class="kategori-tag">
                                    📁 <?= htmlspecialchars($row['kategori'] ?? 'Umum') ?>
                                </span>
                                <div class="text-truncate-multiline" title="<?= htmlspecialchars($row['deskripsi']) ?>">
                                    <?= htmlspecialchars($row['deskripsi']) ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 500;">
                                    <?= htmlspecialchars($row['nama_organisasi']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; white-space: nowrap;">
                                    📅 <?= $row['tanggal_kegiatan'] ? date('d M Y', strtotime($row['tanggal_kegiatan'])) : '-' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status_publikasi'] === 'publish'): ?>
                                    <span class="status-badge status-active">Terbuka</span>
                                <?php else: ?>
                                    <span class="status-badge status-hidden">Disembunyikan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <?php if ($row['status_publikasi'] === 'publish'): ?>
                                        <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=toggle_status" 
                                           class="btn-sm btn-action-hide">
                                             Sembunyi
                                        </a>
                                    <?php else: ?>
                                        <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=toggle_status" 
                                           class="btn-sm btn-action-show">
                                             Tampilkan
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="pengawasan_konten.php?id=<?= $row['id_konten'] ?>&action=delete" 
                                       class="btn-sm btn-action-delete" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus konten ini secara permanen? Seluruh data pendaftaran, likes, dan komentar pada konten ini akan ikut terhapus.');">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
                            📭 Belum ada rekaman data konten kegiatan saat ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>