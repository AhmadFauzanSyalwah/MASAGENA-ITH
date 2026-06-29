<?php
// dashboard/mahasiswa/detail_organisasi.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// SET KONTEKS UNTUK HEADER (AGAR MENU ORGANISASI AKTIF)
// ============================================================
$page_context = 'organisasi';

$id_organisasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_organisasi <= 0) {
    header('Location: organisasi.php');
    exit;
}

// ============================================================
// AMBIL DATA ORGANISASI
// ============================================================
$sqlOrg = "SELECT * FROM organisasi WHERE id_organisasi = :id";
$stmtOrg = $pdo->prepare($sqlOrg);
$stmtOrg->execute([':id' => $id_organisasi]);
$organisasi = $stmtOrg->fetch(PDO::FETCH_ASSOC);

if (!$organisasi) {
    header('Location: organisasi.php');
    exit;
}

// ============================================================
// AMBIL DATA PENGURUS
// ============================================================
$sqlPengurus = "SELECT * FROM pengurus_organisasi 
                WHERE id_organisasi = :id_org 
                ORDER BY 
                    CASE 
                        WHEN jabatan IN ('Ketua', 'Wakil Ketua') THEN 1
                        WHEN jabatan IN ('Sekretaris', 'Bendahara') THEN 2
                        ELSE 3
                    END,
                    nama_pengurus ASC";
$stmtPengurus = $pdo->prepare($sqlPengurus);
$stmtPengurus->execute([':id_org' => $id_organisasi]);
$pengurus = $stmtPengurus->fetchAll();

// ============================================================
// AMBIL DATA KEGIATAN ORGANISASI
// ============================================================
$sqlKegiatan = "SELECT * FROM konten_kegiatan 
                WHERE id_organisasi = :id_org 
                AND status_publikasi = 'publik' 
                ORDER BY tanggal_kegiatan DESC 
                LIMIT 5";
$stmtKegiatan = $pdo->prepare($sqlKegiatan);
$stmtKegiatan->execute([':id_org' => $id_organisasi]);
$kegiatan = $stmtKegiatan->fetchAll();

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   DETAIL ORGANISASI - LAYOUT RAPI
   ============================================ */
.detail-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1.5rem;
    box-sizing: border-box;
}

/* Tombol Kembali */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.3rem 1.2rem;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: 0.3s;
    margin-bottom: 1rem;
}
.btn-back:hover {
    background: #071C34;
    color: #fff;
}

/* Card */
.profil-outer-card {
    border: none;
    border-radius: 20px;
    padding: 20px;
    background: #f8fafc;
    box-shadow: 0 4px 20px rgba(10, 42, 74, 0.08);
}
.profil-inner-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 30px 32px;
    border: 1px solid #e9ecef;
}

/* ===== HEADER: FOTO + INFO ===== */
.org-header {
    display: flex;
    gap: 2rem;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}
.org-header .org-logo {
    flex-shrink: 0;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    background: #f8fafc;
    border: 3px solid #FFA007;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(10, 42, 74, 0.12);
}
.org-header .org-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.org-header .org-logo .no-logo {
    font-size: 4rem;
    color: #94a3b8;
}
.org-header .org-info {
    flex: 1;
}
.org-header .org-info .nama {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.1rem;
}
.org-header .org-info .jenis-badge {
    display: inline-block;
    background: #FFA007;
    color: #071C34;
    padding: 0.1rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.org-header .org-info .deskripsi {
    color: #475569;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0.4rem;
}
.org-header .org-info .pembina {
    font-size: 0.85rem;
    color: #64748b;
}
.org-header .org-info .pembina i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.org-header .org-info .pembina strong {
    color: #071C34;
}

/* ===== VISI & MISI GRID ===== */
.visi-misi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}
.visi-misi-grid .vm-item label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: block;
}
.visi-misi-grid .vm-item p {
    font-size: 0.95rem;
    font-weight: 500;
    color: #071C34;
    margin: 0;
    line-height: 1.6;
}
.visi-misi-grid .vm-item .empty-text {
    color: #cbd5e0;
    font-style: italic;
}

/* ===== SECTION TITLE ===== */
.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #071C34;
    border-bottom: 2px solid #FFA007;
    padding-bottom: 0.4rem;
    margin-bottom: 1.2rem;
    margin-top: 1.5rem;
}
.section-title i {
    color: #FFA007;
    margin-right: 0.4rem;
}

/* ===== PENGURUS GRID ===== */
.pengurus-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.8rem;
}
.pengurus-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.8rem 1rem;
    border-left: 4px solid #FFA007;
    transition: 0.2s;
}
.pengurus-item:hover {
    background: #f1f5f9;
}
.pengurus-item .p-nama {
    font-weight: 700;
    color: #071C34;
}
.pengurus-item .p-jabatan {
    font-size: 0.75rem;
    color: #FFA007;
    font-weight: 600;
}
.pengurus-item .p-hp {
    font-size: 0.65rem;
    color: #94a3b8;
}

/* ===== KEGIATAN LIST ===== */
.kegiatan-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.kegiatan-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: #f8fafc;
    border-radius: 10px;
    transition: 0.2s;
    border-left: 3px solid #FFA007;
}
.kegiatan-item:hover {
    background: #f1f5f9;
}
.kegiatan-item .keg-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.kegiatan-item .keg-info .keg-judul {
    font-weight: 600;
    color: #071C34;
}
.kegiatan-item .keg-info .keg-judul a {
    color: #071C34;
    text-decoration: none;
}
.kegiatan-item .keg-info .keg-judul a:hover {
    color: #FFA007;
}
.kegiatan-item .keg-info .keg-meta {
    font-size: 0.75rem;
    color: #94a3b8;
}
.kegiatan-item .keg-info .keg-meta i {
    color: #FFA007;
    margin-right: 0.2rem;
}
.kegiatan-item .keg-info .keg-badge {
    background: #FFA007;
    color: #071C34;
    padding: 0.1rem 0.5rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
}
.kegiatan-item .btn-detail-keg {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.15rem 0.8rem;
    font-size: 0.65rem;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
}
.kegiatan-item .btn-detail-keg:hover {
    background: #071C34;
    color: #fff;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem 0;
    color: #94a3b8;
}
.empty-state i {
    font-size: 2.5rem;
    color: #cbd5e0;
    margin-bottom: 0.5rem;
    display: block;
}

/* ===== LIHAT SEMUA KEGIATAN ===== */
.btn-lihat-semua {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.3rem 1.2rem;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: 0.3s;
    margin-top: 1rem;
}
.btn-lihat-semua:hover {
    background: #071C34;
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .detail-container {
        padding: 0 0.5rem;
    }
    .profil-inner-card {
        padding: 16px;
    }
    .org-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .org-header .org-logo {
        width: 100px;
        height: 100px;
    }
    .org-header .org-logo .no-logo {
        font-size: 3rem;
    }
    .visi-misi-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .pengurus-grid {
        grid-template-columns: 1fr 1fr;
    }
    .kegiatan-item {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .kegiatan-item .keg-info {
        justify-content: center;
    }
    .btn-detail-keg {
        align-self: center;
    }
}
@media (max-width: 480px) {
    .pengurus-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="detail-container">

    <!-- ===== TOMBOL KEMBALI ===== -->
    <a href="organisasi.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Organisasi
    </a>

    <div class="profil-outer-card">
        <div class="profil-inner-card">

            <!-- ===== HEADER: FOTO + INFO ===== -->
            <div class="org-header">
                <div class="org-logo">
                    <?php if (!empty($organisasi['logo']) && file_exists('../../uploads/logo/' . $organisasi['logo'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/logo/<?= $organisasi['logo'] ?>" alt="Logo <?= htmlspecialchars($organisasi['nama_organisasi']) ?>">
                    <?php else: ?>
                        <span class="no-logo"><i class="fas fa-building"></i></span>
                    <?php endif; ?>
                </div>
                <div class="org-info">
                    <div class="nama"><?= htmlspecialchars($organisasi['nama_organisasi']) ?></div>
                    <span class="jenis-badge"><?= htmlspecialchars($organisasi['jenis']) ?></span>
                    <div class="deskripsi"><?= nl2br(htmlspecialchars($organisasi['deskripsi'] ?? '')) ?></div>
                    <?php if (!empty($organisasi['pembina'])): ?>
                        <div class="pembina">
                            <i class="fas fa-user-tie"></i> Dosen Pembina: <strong><?= htmlspecialchars($organisasi['pembina']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== VISI & MISI ===== -->
            <div class="visi-misi-grid">
                <div class="vm-item">
                    <label><i class="fas fa-bullseye" style="color:#FFA007;"></i> Visi</label>
                    <?php if (!empty($organisasi['visi'])): ?>
                        <p><?= htmlspecialchars($organisasi['visi']) ?></p>
                    <?php else: ?>
                        <p class="empty-text">Belum ada visi</p>
                    <?php endif; ?>
                </div>
                <div class="vm-item">
                    <label><i class="fas fa-list-check" style="color:#FFA007;"></i> Misi</label>
                    <?php if (!empty($organisasi['misi'])): ?>
                        <p><?= htmlspecialchars($organisasi['misi']) ?></p>
                    <?php else: ?>
                        <p class="empty-text">Belum ada misi</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== PENGURUS ===== -->
            <div class="section-title"><i class="fas fa-users"></i> Pengurus</div>
            <?php if (count($pengurus) > 0): ?>
                <div class="pengurus-grid">
                    <?php foreach ($pengurus as $p): ?>
                        <div class="pengurus-item">
                            <div class="p-nama"><?= htmlspecialchars($p['nama_pengurus']) ?></div>
                            <div class="p-jabatan"><?= htmlspecialchars($p['jabatan'] ?? 'Anggota') ?></div>
                            <?php if (!empty($p['no_hp'])): ?>
                                <div class="p-hp"><i class="fas fa-phone"></i> <?= htmlspecialchars($p['no_hp']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>Belum ada pengurus terdaftar.</p>
                </div>
            <?php endif; ?>

            <!-- ===== KEGIATAN ===== -->
            <div class="section-title"><i class="fas fa-calendar-alt"></i> Kegiatan</div>
            <?php if (count($kegiatan) > 0): ?>
                <div class="kegiatan-list">
                    <?php foreach ($kegiatan as $k): ?>
                        <div class="kegiatan-item">
                            <div class="keg-info">
                                <span class="keg-judul">
                                    <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>">
                                        <?= htmlspecialchars($k['judul']) ?>
                                    </a>
                                </span>
                                <span class="keg-meta">
                                    <i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($k['tanggal_kegiatan'])) ?>
                                </span>
                                <?php if (!empty($k['kategori'])): ?>
                                    <span class="keg-badge"><?= htmlspecialchars($k['kategori']) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>" class="btn-detail-keg">Detail</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($kegiatan) >= 5): ?>
                    <div style="text-align:center;">
                        <a href="kegiatan.php?filter_organisasi=<?= $id_organisasi ?>" class="btn-lihat-semua">
                            Lihat Semua Kegiatan <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <p>Belum ada kegiatan yang diselenggarakan.</p>
                </div>
            <?php endif; ?>

        </div><!-- end inner -->
    </div><!-- end outer -->

</div>

<?php include '../../include/footer.php'; ?>