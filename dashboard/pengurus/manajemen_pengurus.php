<?php
// dashboard/pengurus/manajemen_pengurus.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$level = $_SESSION['level'] ?? 'biasa';
$is_inti = ($level === 'inti' || $level === 'Pengurus Inti');
if (!$is_inti) {
    $_SESSION['error'] = 'Hanya pengurus inti yang dapat mengakses halaman ini.';
    header('Location: index.php');
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil id_organisasi
$stmt = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmt->execute([$id_user]);
$id_organisasi = $stmt->fetchColumn();

// ============================================================
// AMBIL DAFTAR PENGURUS
// ============================================================
$stmt = $pdo->prepare("
    SELECT id_pengurus, nama_pengurus, no_hp, level
    FROM pengurus_organisasi
    WHERE id_organisasi = ?
    ORDER BY level DESC, nama_pengurus ASC
");
$stmt->execute([$id_organisasi]);
$pengurus = $stmt->fetchAll();

// Ambil error dari session
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);

$page_context = 'manajemen_pengurus';
include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.manajemen-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}
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
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}
.btn-tambah {
    background: #FFA007;
    color: #071C34;
    padding: 0.6rem 2rem;
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
    min-width: 600px;
}
.table-card thead {
    background: #f8fafc;
    border-bottom: 2px solid #e9ecef;
}
.table-card th {
    padding: 0.9rem 1.2rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.table-card td {
    padding: 0.9rem 1.2rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.table-card tbody tr:hover {
    background: #f8fafc;
}
.text-center {
    text-align: center;
    color: #94a3b8;
    padding: 2.5rem 0;
}
.text-center i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    display: block;
    color: #cbd5e0;
}
.badge-level {
    display: inline-block;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
}
.badge-level.inti {
    background: #071C34;
    color: #fff;
}
.badge-level.biasa {
    background: #f1f5f9;
    color: #64748b;
}

/* ===== AKSI - SEJAJAR DENGAN TOMBOL BESAR ===== */
.col-aksi {
    text-align: center;
    min-width: 220px;
}
.action-inline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    flex-wrap: nowrap;
}
.mini-btn {
    padding: 0.45rem 1.5rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: inherit;
    white-space: nowrap;
    min-width: 85px;
    border-width: 2px;
    border-style: solid;
    text-decoration: none;
    transition: all 0.25s ease;
    height: 40px;
}
.mini-btn.edit {
    background: transparent;
    color: #071C34;
    border-color: #071C34;
}
.mini-btn.edit:hover {
    background: #071C34;
    color: #ffffff;
}
.mini-btn.hapus {
    background: transparent;
    color: #dc2626;
    border-color: #dc2626;
}
.mini-btn.hapus:hover {
    background: #dc2626;
    color: #ffffff;
}
.mini-btn.hapus:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .manajemen-container { padding: 0 0.5rem; }
    .manajemen-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .manajemen-header .title-group h2 { font-size: 1.4rem; }
    .btn-tambah { justify-content: center; }
    .table-card { overflow-x: auto; }
    .table-card table { font-size: 0.85rem; min-width: 550px; }
    .table-card th, .table-card td { padding: 0.5rem 0.7rem; }
    .col-aksi { min-width: 180px; }
    .action-inline { gap: 0.4rem; }
    .mini-btn { padding: 0.3rem 1rem; font-size: 0.75rem; min-width: 70px; height: 34px; }
}
</style>

<div class="manajemen-container">

    <div class="manajemen-header">
        <div class="title-group">
            <h2><i class="fas fa-users-cog"></i> Manajemen Pengurus</h2>
            <p class="subtitle">Kelola pengurus organisasi Anda</p>
        </div>
        <a href="tambah_pengurus.php" class="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Pengurus
        </a>
    </div>

    <!-- NOTIFIKASI HAPUS SUKSES -->
    <?php if (isset($_GET['hapus']) && $_GET['hapus'] === 'sukses'): ?>
        <script>
            alert('Pengurus berhasil dihapus.');
            if (window.history && window.history.replaceState) {
                let url = window.location.href;
                url = url.replace(/[?&]hapus=sukses/, '').replace(/[?&]$/, '');
                window.history.replaceState(null, null, url);
            }
        </script>
    <?php endif; ?>

    <!-- NOTIFIKASI ERROR -->
    <?php if (!empty($error)): ?>
        <script>alert('<?= addslashes($error) ?>');</script>
    <?php endif; ?>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width:8%; text-align:center;">No</th>
                    <th style="width:28%;">Nama</th>
                    <th style="width:20%;">No. HP</th>
                    <th style="width:18%;">Level</th>
                    <th style="width:26%; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pengurus)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="text-center">
                                <i class="fa-regular fa-users-slash"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada pengurus</p>
                                <p style="margin:0; font-size:0.8rem; color:#cbd5e0;">Tambahkan pengurus pertama Anda</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $no = 1; foreach ($pengurus as $p): ?>
                    <tr>
                        <td style="text-align:center; font-weight:600; color:#071C34;"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($p['nama_pengurus']) ?></strong></td>
                        <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
                        <td>
                            <span class="badge-level <?= $p['level'] == 'inti' ? 'inti' : 'biasa' ?>">
                                <?= $p['level'] == 'inti' ? 'Inti' : 'Biasa' ?>
                            </span>
                        </td>
                        <td class="col-aksi">
                            <div class="action-inline">
                                <!-- Edit -->
                                <a href="edit_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="mini-btn edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <!-- Hapus -->
                                <?php if ($p['id_pengurus'] != $id_user): ?>
                                    <a href="hapus_pengurus.php?id=<?= $p['id_pengurus'] ?>" class="mini-btn hapus" 
                                       onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($p['nama_pengurus']) ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                <?php else: ?>
                                    <button class="mini-btn hapus" disabled title="Tidak dapat menghapus diri sendiri">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../include/footer.php'; ?>