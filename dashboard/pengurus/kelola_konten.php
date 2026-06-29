<?php
// dashboard/pengurus/kelola_konten.php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['pengurus', 'admin'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// AMBIL DATA ORGANISASI PENGURUS
// ============================================================
$id_user = $_SESSION['user_id'];
$level = $_SESSION['level'] ?? 'biasa';
$id_organisasi = null;

if ($_SESSION['peran'] === 'admin') {
    $stmt = $pdo->prepare("SELECT k.*, o.nama_organisasi 
                           FROM konten_kegiatan k 
                           JOIN organisasi o ON o.id_organisasi = k.id_organisasi 
                           ORDER BY k.created_at DESC");
    $stmt->execute();
    $semua_konten = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
    $stmtOrg->execute([$id_user]);
    $pengurusOrg = $stmtOrg->fetch(PDO::FETCH_ASSOC);
    
    if ($pengurusOrg) {
        $id_organisasi = $pengurusOrg['id_organisasi'];
        $stmt = $pdo->prepare("SELECT k.*, o.nama_organisasi 
                               FROM konten_kegiatan k 
                               JOIN organisasi o ON o.id_organisasi = k.id_organisasi 
                               WHERE k.id_organisasi = ? 
                               ORDER BY k.created_at DESC");
        $stmt->execute([$id_organisasi]);
        $semua_konten = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $semua_konten = [];
    }
}

// Pesan session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   KELOLA KONTEN - CSS DIPERBAIKI
   ============================================ */
.kelola-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.kelola-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.kelola-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.kelola-header .title-group .subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}

/* Alert */
.kelola-container .alert {
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
.kelola-container .alert-success {
    background: #dcfce7;
    border-left: 4px solid #22c55e;
    color: #166534;
}
.kelola-container .alert-danger {
    background: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #991b1b;
}

/* ===== TOMBOL TAMBAH ===== */
.btn-tambah {
    background: #FFA007;
    color: #071C34;
    padding: 0.4rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 2px solid #FFA007;
}
.btn-tambah:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* ===== TABLE CARD ===== */
.table-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.table-card table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    table-layout: fixed; /* ← agar lebar kolom bisa diatur */
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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table-card tbody tr:hover {
    background: #f8fafc;
}
.table-card .text-center {
    text-align: center;
    color: #94a3b8;
    padding: 2.5rem 0;
}
.table-card .text-center i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    display: block;
    color: #cbd5e0;
}

/* ============================================================
   LEBAR KOLOM - DISESUAIKAN AGAR AKSI LEGA
   ============================================================ */
.table-card .col-no {
    width: 8%;
    text-align: center;
}
.table-card .col-judul {
    width: 28%;
}
.table-card .col-kategori {
    width: 15%;  /* ← lebih lega */
    text-align: center;
}
.table-card .col-tanggal {
    width: 15%;  /* ← lebih lega */
    text-align: center;
}
.table-card .col-status {
    width: 14%;
    text-align: center;
}
.table-card .col-aksi {
    width: 30%;  /* ← ruang cukup untuk 3 tombol */
    text-align: center;
}

/* ============================================================
   STATUS BADGE
   ============================================================ */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.status-badge i {
    font-size: 0.7rem;
}
.status-badge.publik {
    background: #22c55e;
}
.status-badge.draft {
    background: #f59e0b;
}
.status-badge.arsip {
    background: #64748b;
}

/* ============================================================
   TOMBOL AKSI - KOMPAK TAPI LEGA
   ============================================================ */
.action-inline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.action-inline form {
    display: inline;
}

.mini-btn {
    padding: 0.25rem 1.2rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-family: inherit;
    white-space: nowrap;
    min-width: 70px;
    justify-content: center;
    border-width: 2px;
    border-style: solid;
    text-decoration: none;
    transition: 0.3s;
}

.mini-btn.edit {
    background: transparent;
    color: #071C34;
    border-color: #071C34;
}
.mini-btn.edit:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

.mini-btn.detail {
    background: transparent;
    color: #FFA007;
    border-color: #FFA007;
}
.mini-btn.detail:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}

.mini-btn.hapus {
    background: transparent;
    color: #dc2626;
    border-color: #dc2626;
}
.mini-btn.hapus:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 992px) {
    .table-card .col-no { width: 6%; }
    .table-card .col-judul { width: 22%; }
    .table-card .col-kategori { width: 14%; }
    .table-card .col-tanggal { width: 14%; }
    .table-card .col-status { width: 14%; }
    .table-card .col-aksi { width: 30%; }
    .mini-btn {
        padding: 0.2rem 0.8rem;
        font-size: 0.7rem;
        min-width: 60px;
    }
}

@media (max-width: 768px) {
    .kelola-container {
        padding: 0 0.5rem;
    }
    .kelola-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .kelola-header .title-group h2 {
        font-size: 1.4rem;
    }
    .btn-tambah {
        justify-content: center;
    }
    .table-card {
        overflow-x: auto;
    }
    .table-card table {
        font-size: 0.8rem;
        min-width: 700px;
    }
    .table-card th, .table-card td {
        padding: 0.4rem 0.5rem;
    }
    .action-inline {
        flex-direction: column;
        gap: 0.3rem;
    }
    .action-inline form {
        display: block;
        width: 100%;
    }
    .mini-btn {
        width: 100%;
        justify-content: center;
        padding: 0.3rem 0.5rem;
        min-width: unset;
    }
    /* Lebar di mobile dibuat lebih fleksibel */
    .table-card .col-aksi { width: 100%; }
}
</style>

<div class="kelola-container">

    <!-- HEADER -->
    <div class="kelola-header">
        <div class="title-group">
            <h2>Kelola Konten Kegiatan</h2>
            <p class="subtitle">
                <i class="fas fa-building"></i> 
                <?php if ($_SESSION['peran'] === 'admin'): ?>
                    Admin - Semua Organisasi
                <?php else: ?>
                    <?= $id_organisasi ? 'Organisasi Anda' : 'Belum memiliki organisasi' ?>
                <?php endif; ?>
                &nbsp;|&nbsp; Tingkatan: <strong><?= ucfirst($level) ?></strong>
            </p>
        </div>
        <a href="tambah_kegiatan.php" class="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Kegiatan
        </a>
    </div>

    <!-- PESAN -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-judul">Judul</th>
                    <th class="col-kategori">Kategori</th>
                    <th class="col-tanggal">Tanggal</th>
                    <th class="col-status">Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($semua_konten)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="text-center">
                                <i class="fa-regular fa-calendar-circle-plus"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada konten kegiatan</p>
                                <p style="margin:0; font-size:0.8rem; color:#cbd5e0;">
                                    <?php if ($_SESSION['peran'] === 'admin'): ?>
                                        Belum ada kegiatan yang dibuat
                                    <?php else: ?>
                                        Belum ada kegiatan untuk organisasi Anda
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $no = 1; foreach ($semua_konten as $row): ?>
                    <tr>
                        <td class="col-no" style="font-weight:600; color:#071C34;"><?= $no++ ?></td>
                        <td class="col-judul">
                            <strong style="color:#071C34;"><?= htmlspecialchars($row['judul']) ?></strong>
                        </td>
                        <td class="col-kategori">
                            <span style="background:#f1f5f9; padding:0.15rem 0.8rem; border-radius:50px; font-size:0.7rem; color:#071C34; display:inline-block; white-space:nowrap;">
                                <?= htmlspecialchars($row['kategori'] ?? 'Umum') ?>
                            </span>
                        </td>
                        <td class="col-tanggal" style="font-size:0.85rem; color:#64748b;">
                            <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($row['tanggal_kegiatan'])) ?>
                        </td>
                        <td class="col-status">
                            <span class="status-badge <?= $row['status_publikasi'] ?? 'draft' ?>">
                                <i class="fas <?= ($row['status_publikasi'] ?? 'draft') == 'publik' ? 'fa-check-circle' : 'fa-pen' ?>"></i>
                                <?= ucfirst($row['status_publikasi'] ?? 'Draft') ?>
                            </span>
                        </td>
                        <td class="col-aksi">
                            <div class="action-inline">
                                <a href="edit_konten.php?id=<?= $row['id_konten'] ?>" class="mini-btn edit" title="Edit kegiatan">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="detail_kegiatan.php?id=<?= $row['id_konten'] ?>" class="mini-btn detail" title="Lihat detail" target="_self">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <form action="proses_hapus.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus kegiatan [<?= htmlspecialchars($row['judul']) ?>] secara permanen? Semua data pendaftaran juga akan terhapus.')">
                                    <input type="hidden" name="id_konten" value="<?= (int) $row['id_konten'] ?>">
                                    <button type="submit" class="mini-btn hapus" title="Hapus kegiatan">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>