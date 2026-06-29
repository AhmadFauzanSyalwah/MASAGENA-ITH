<?php
// dashboard/pengurus/manajemen_pengurus.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

// ============================================================
// CEK APAKAH PENGURUS INTI
// ============================================================
$level = $_SESSION['level'] ?? '';
$is_inti = ($level === 'Pengurus Inti' || $level === 'inti');

if (!$is_inti) {
    header('Location: index.php');
    exit;
}

$id_organisasi = $_SESSION['id_organisasi'] ?? 0;

if (!$id_organisasi) {
    $_SESSION['error'] = 'Organisasi tidak ditemukan.';
    header('Location: index.php');
    exit;
}

// ============================================================
// AMBIL DATA PENGURUS
// ============================================================
$stmt = $pdo->prepare("
    SELECT id_pengurus, nama_pengurus, jabatan, level, no_hp, status_verifikasi 
    FROM pengurus_organisasi 
    WHERE id_organisasi = ? 
    ORDER BY 
        CASE 
            WHEN level = 'Pengurus Inti' THEN 1
            ELSE 2
        END,
        nama_pengurus ASC
");
$stmt->execute([$id_organisasi]);
$daftar_pengurus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// CEK PESAN SESSION
// ============================================================
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// ============================================================
// SET KONTEKS UNTUK HEADER
// ============================================================
$page_context = 'manajemen_pengurus';

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php if ($success): ?>
    <script>alert('<?= addslashes($success) ?>');</script>
<?php endif; ?>
<?php if ($error): ?>
    <script>alert('<?= addslashes($error) ?>');</script>
<?php endif; ?>

<style>
/* ============================================
   MANAJEMEN PENGURUS - UKURAN TEKS LEBIH BESAR
   ============================================ */
.manajemen-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.manajemen-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.manajemen-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.manajemen-header .title-group .subtitle {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
}

/* ===== TOMBOL TAMBAH ===== */
.btn-tambah {
    background: #FFA007;
    color: #071C34;
    padding: 0.5rem 1.8rem;
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 2px solid #FFA007;
    transition: all 0.25s ease;
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
    font-size: 1rem;
    min-width: 700px;
}
.table-card thead {
    background: #f8fafc;
    border-bottom: 2px solid #e9ecef;
}
.table-card th {
    padding: 0.8rem 1.2rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #071C34;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.table-card th.text-center-header {
    text-align: center;
}
.table-card td.text-center-cell {
    text-align: center;
    vertical-align: middle;
}
.table-card td {
    padding: 0.8rem 1.2rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.95rem;
    color: #1e293b;
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
.table-card .text-center p {
    font-size: 1rem;
}

/* ============================================================
   STATUS BADGE - LEBIH BESAR
   ============================================================ */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 1.2rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.status-badge i {
    font-size: 0.8rem;
}
.status-badge.aktif {
    background: #22c55e;
}
.status-badge.belum {
    background: #dc2626;
}

/* ===== LEVEL BADGE - LEBIH BESAR ===== */
.level-badge {
    display: inline-block;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
}
.level-badge.inti {
    background: #fef9c3;
    color: #ca8a04;
}
.level-badge.departemen {
    background: #dbeafe;
    color: #1e40af;
}

/* ============================================================
   TOMBOL AKSI - LEBIH BESAR
   ============================================================ */
.action-inline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: nowrap;
}
.action-inline a {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 1.2rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    transition: all 0.25s ease;
    border: 2px solid;
    background: transparent;
}

/* Edit - border biru */
.btn-edit {
    color: #3b82f6;
    border-color: #3b82f6;
}
.btn-edit:hover {
    background: #3b82f6;
    color: #fff;
}

/* Hapus - border merah */
.btn-hapus {
    color: #dc2626;
    border-color: #dc2626;
}
.btn-hapus:hover {
    background: #dc2626;
    color: #fff;
}

/* Reset - border kuning */
.btn-reset {
    color: #071C34;
    border-color: #FFA007;
}
.btn-reset:hover {
    background: #FFA007;
    color: #fff;
}

/* ============================================================
   NAMA PENGGUNA DI TABEL - LEBIH TEBAL & BESAR
   ============================================================ */
.table-card .nama-pengurus {
    font-weight: 700;
    color: #071C34;
    font-size: 1rem;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    .manajemen-container {
        padding: 0 0.5rem;
    }
    .manajemen-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .manajemen-header .title-group h2 {
        font-size: 1.4rem;
    }
    .btn-tambah {
        justify-content: center;
        font-size: 0.9rem;
        padding: 0.4rem 1.2rem;
    }
    .table-card {
        overflow-x: auto;
    }
    .table-card table {
        font-size: 0.85rem;
        min-width: 700px;
    }
    .table-card th, .table-card td {
        padding: 0.5rem 0.8rem;
        font-size: 0.85rem;
    }
    .table-card th {
        font-size: 0.75rem;
    }
    .action-inline {
        flex-direction: column;
        gap: 0.3rem;
    }
    .action-inline a {
        justify-content: center;
        padding: 0.2rem 0.8rem;
        font-size: 0.7rem;
        min-width: 60px;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.8rem;
    }
    .level-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.7rem;
    }
}
</style>

<div class="manajemen-container">

    <!-- HEADER -->
    <div class="manajemen-header">
        <div class="title-group">
            <h2>Manajemen Pengurus</h2>
            <p class="subtitle">
                <i class="fas fa-users-cog"></i> Kelola anggota kepengurusan organisasi Anda
            </p>
        </div>
        <a href="tambah_pengurus.php" class="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Pengurus
        </a>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengurus</th>
                    <th>Jabatan</th>
                    <th class="text-center-header">Level</th>
                    <th>No HP</th>
                    <th class="text-center-header">Status</th>
                    <th class="text-center-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($daftar_pengurus) > 0): ?>
                    <?php $no = 1; foreach ($daftar_pengurus as $p): ?>
                        <tr>
                            <td style="font-weight:700; color:#071C34; font-size:1rem;"><?= $no++ ?></td>
                            <td>
                                <div class="nama-pengurus"><?= htmlspecialchars($p['nama_pengurus']) ?></div>
                            </td>
                            <td style="font-size:0.95rem;"><?= htmlspecialchars($p['jabatan']) ?></td>
                            <td class="text-center-cell">
                                <span class="level-badge <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'inti' : 'departemen' ?>">
                                    <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'Inti' : 'Departemen' ?>
                                </span>
                            </td>
                            <td style="font-size:0.95rem;"><?= htmlspecialchars($p['no_hp'] ?: '-') ?></td>
                            <td class="text-center-cell">
                                <span class="status-badge <?= $p['status_verifikasi'] == 'Terverifikasi' ? 'aktif' : 'belum' ?>">
                                    <i class="fas <?= $p['status_verifikasi'] == 'Terverifikasi' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                    <?= $p['status_verifikasi'] == 'Terverifikasi' ? 'Aktif' : 'Belum Verifikasi' ?>
                                </span>
                            </td>
                            <td class="text-center-cell">
                                <div class="action-inline">
                                    <a href="edit_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="hapus_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus pengurus <?= htmlspecialchars($p['nama_pengurus']) ?>?')" title="Hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                    <a href="reset_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="btn-reset" title="Reset Password">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="text-center">
                                <i class="fa-regular fa-users"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada pengurus terdaftar</p>
                                <p style="margin:0; font-size:0.9rem; color:#cbd5e0;">Tambahkan pengurus untuk organisasi Anda</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>