<?php
// dashboard/mahasiswa/pendaftaran.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// KONTEKS UNTUK HEADER (AGAR MENU KEGIATAN AKTIF)
// ============================================================
$page_context = 'kegiatan';

// Ambil parameter
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$user_id = $_SESSION['user_id'];

// ============================================================
// BANGUN QUERY UNTUK RIWAYAT PENDAFTARAN
// ============================================================
$where = "p.id_mahasiswa = :user_id";
$params = [':user_id' => $user_id];

if ($filter_status != 'semua') {
    $where .= " AND p.status_pendaftaran = :status";
    $params[':status'] = $filter_status;
}

if (!empty($q)) {
    $search = '%' . $q . '%';
    $where .= " AND (k.judul LIKE :q OR o.nama_organisasi LIKE :q)";
    $params[':q'] = $search;
}

switch ($sort_by) {
    case 'terbaru':  $order = "p.tanggal_daftar DESC"; break;
    case 'terlama':  $order = "p.tanggal_daftar ASC"; break;
    case 'nama_asc': $order = "k.judul ASC"; break;
    case 'nama_desc':$order = "k.judul DESC"; break;
    default:         $order = "p.tanggal_daftar DESC";
}

// ============================================================
// HITUNG TOTAL
// ============================================================
$countSql = "SELECT COUNT(*) 
             FROM pendaftaran p
             JOIN konten_kegiatan k ON p.id_konten = k.id_konten
             JOIN organisasi o ON k.id_organisasi = o.id_organisasi
             WHERE $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalRiwayat = $countStmt->fetchColumn();
$totalPages = ceil($totalRiwayat / $limit);

// ============================================================
// QUERY DATA RIWAYAT
// ============================================================
$sql = "SELECT p.*, k.judul, k.deskripsi, k.tanggal_kegiatan, k.kuota_maks AS kuota,
               o.nama_organisasi
        FROM pendaftaran p
        JOIN konten_kegiatan k ON p.id_konten = k.id_konten
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE $where
        ORDER BY $order
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    // bindValue with type detection
    if (is_int($val)) {
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
}
// Bind limit and offset as integers
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$riwayat = $stmt->fetchAll();

// ============================================================
// INCLUDE HEADER
// ============================================================
include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   PENDAFTARAN (RIWAYAT) - STYLE TABEL
   SAMA DENGAN MANAJEMEN PENGURUS
   ============================================ */
.pendaftaran-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.pendaftaran-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.pendaftaran-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.pendaftaran-header .title-group .subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}

/* Filter bar - sama dengan manajemen */
.filter-bar {
    background: #f8fafc;
    border-radius: 16px;
    padding: 0.8rem 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.8rem 1.2rem;
}
.filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    flex: 0 1 130px;
    min-width: 100px;
}
.filter-bar .filter-group label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #071C34;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.1rem;
}
.filter-bar .filter-group select {
    border-radius: 50px;
    border: 1.5px solid #e2e8f0;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    background: #ffffff;
    height: 34px;
}
.filter-bar .filter-group select:focus {
    border-color: #FFA007;
    outline: none;
}
.filter-bar .btn-reset {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.25rem 1.8rem;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    height: 34px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-left: auto;
    transition: 0.3s;
}
.filter-bar .btn-reset:hover {
    background: #0a2a4a;
    color: #ffffff;
}

/* ===== TABLE CARD - SAMA DENGAN MANAJEMEN PENGURUS ===== */
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
    min-width: 700px;
}
/* === PERBAIKAN HEADER TABEL === */
.table-card thead {
    background: #f8fafc !important;
    border-bottom: 2px solid #e9ecef;
}
.table-card th {
    background: #f8fafc !important;
    padding: 0.7rem 1rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #071C34 !important; /* teks gelap agar kontras */
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.table-card td {
    padding: 0.7rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
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
   STATUS BADGE - SAMA DENGAN MANAJEMEN PENGURUS
   ============================================================ */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.2rem 1rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.status-badge.menunggu {
    background: #f59e0b;
}
.status-badge.diterima {
    background: #22c55e;
}
.status-badge.ditolak {
    background: #dc2626;
}
.status-badge.batal {
    background: #94a3b8;
}
.status-badge i {
    font-size: 0.65rem;
}

/* ============================================================
   TOMBOL AKSI - KONSISTEN DENGAN MANAJEMEN PENGURUS
   ============================================================ */
.action-inline {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: nowrap;
}
.action-inline a {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.15rem 0.7rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 700;
    transition: all 0.25s ease;
    border: 2px solid;
    background: transparent;
}
.btn-detail {
    color: #3b82f6;
    border-color: #3b82f6;
}
.btn-detail:hover {
    background: #3b82f6;
    color: #fff;
}
.btn-batal {
    color: #dc2626;
    border-color: #dc2626;
}
.btn-batal:hover {
    background: #dc2626;
    color: #fff;
}
.btn-batal:disabled,
.btn-batal.disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* ===== PAGINATION ===== */
.pagination-wrapper {
    margin: 2rem 0 1rem 0;
    display: flex;
    justify-content: center;
}
.pagination {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}
.pagination a, .pagination span {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #071C34;
    text-decoration: none;
    transition: 0.2s;
    min-width: 40px;
    text-align: center;
}
.pagination a:hover {
    background: #FFA007;
    color: #071C34;
    border-color: #FFA007;
}
.pagination .active {
    background: #071C34;
    color: #fff;
    border-color: #071C34;
    pointer-events: none;
}
.pagination .disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* ============================================================
   RESPONSIVE - SAMA DENGAN MANAJEMEN PENGURUS
   ============================================================ */
@media (max-width: 768px) {
    .pendaftaran-container {
        padding: 0 0.5rem;
    }
    .pendaftaran-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .pendaftaran-header .title-group h2 {
        font-size: 1.4rem;
    }
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-bar .filter-group {
        flex: 1;
    }
    .filter-bar .btn-reset {
        width: 100%;
        justify-content: center;
        margin-left: 0;
    }
    .table-card table {
        font-size: 0.8rem;
        min-width: 700px;
    }
    .table-card th, .table-card td {
        padding: 0.4rem 0.6rem;
    }
    .action-inline {
        flex-direction: column;
        gap: 0.2rem;
    }
    .action-inline a {
        justify-content: center;
        padding: 0.2rem 0.5rem;
        min-width: 60px;
    }
}
</style>

<div class="pendaftaran-container">

    <!-- HEADER -->
    <div class="pendaftaran-header">
        <div class="title-group">
            <h2>Riwayat Kegiatan</h2>
            <p class="subtitle">
                <i class="fas fa-history"></i> Daftar kegiatan yang telah Anda daftar
            </p>
        </div>

    </div>

    <!-- FILTER -->
    <div class="filter-bar">
        <form id="filterForm" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>" style="display:contents;">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

            <div class="filter-group">
                <label>Status</label>
                <select name="filter_status" onchange="this.form.submit()">
                    <option value="semua" <?= $filter_status=='semua'?'selected':'' ?>>Semua</option>
                    <option value="menunggu" <?= $filter_status=='menunggu'?'selected':'' ?>>Menunggu</option>
                    <option value="diterima" <?= $filter_status=='diterima'?'selected':'' ?>>Diterima</option>
                    <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                    <option value="batal" <?= $filter_status=='batal'?'selected':'' ?>>Dibatalkan</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Urutkan</label>
                <select name="sort_by" onchange="this.form.submit()">
                    <option value="terbaru" <?= $sort_by=='terbaru'?'selected':'' ?>>Terbaru</option>
                    <option value="terlama" <?= $sort_by=='terlama'?'selected':'' ?>>Terlama</option>
                    <option value="nama_asc" <?= $sort_by=='nama_asc'?'selected':'' ?>>Nama A-Z</option>
                    <option value="nama_desc" <?= $sort_by=='nama_desc'?'selected':'' ?>>Nama Z-A</option>
                </select>
            </div>

            <button type="button" class="btn-reset" onclick="resetFilter()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kegiatan</th>
                    <th>Organisasi</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($riwayat) > 0): ?>
                    <?php $no = (($page - 1) * $limit) + 1; foreach ($riwayat as $row): ?>
                        <tr>
                            <td style="font-weight:600; color:#071C34;"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight:600; color:#071C34;"><?= htmlspecialchars($row['judul']) ?></div>
                                <div style="font-size:0.7rem; color:#94a3b8;"><?= htmlspecialchars(substr($row['deskripsi'], 0, 60)) ?>...</div>
                            </td>
                            <td><?= htmlspecialchars($row['nama_organisasi']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_kegiatan'])) ?></td>
                            <td><?= date('d M Y H:i', strtotime($row['tanggal_daftar'])) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($row['status_pendaftaran']) ?>">
                                    <i class="fas <?= $row['status_pendaftaran'] == 'diterima' ? 'fa-check-circle' : ($row['status_pendaftaran'] == 'ditolak' ? 'fa-times-circle' : ($row['status_pendaftaran'] == 'menunggu' ? 'fa-clock' : 'fa-ban')) ?>"></i>
                                    <?= ucfirst($row['status_pendaftaran']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-inline">
                                    <a href="detail_kegiatan.php?id=<?= $row['id_konten'] ?>&back=<?= urlencode('/MASAGENA-ITH/dashboard/mahasiswa/pendaftaran.php') ?>" class="btn-detail" title="Lihat Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if (strtolower($row['status_pendaftaran']) == 'menunggu'): ?>
                                        <a href="batal_pendaftaran.php?id=<?= $row['id_pendaftaran'] ?>" class="btn-batal" onclick="return confirm('Yakin ingin membatalkan pendaftaran ini?')" title="Batalkan">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-batal disabled" style="opacity:0.4; pointer-events:none; color:#94a3b8; border-color:#94a3b8; padding:0.15rem 0.7rem; border-radius:50px; font-size:0.65rem; font-weight:700; border:2px solid; background:transparent; display:inline-flex; align-items:center; gap:0.2rem;">
                                            <i class="fas fa-times"></i> Batal
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="text-center">
                                <i class="fa-regular fa-calendar-circle-plus"></i>
                                <p style="margin:0; color:#94a3b8; font-weight:500;">Belum ada riwayat pendaftaran</p>
                                <p style="margin:0; font-size:0.8rem; color:#cbd5e0;">Anda belum mendaftar kegiatan apapun.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">‹</a>
                <?php else: ?>
                    <span class="disabled">‹</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">›</a>
                <?php else: ?>
                    <span class="disabled">›</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
function resetFilter() {
    var form = document.getElementById('filterForm');
    form.querySelectorAll('select').forEach(function(sel) {
        sel.selectedIndex = 0;
    });
    window.location.href = '<?= $_SERVER['PHP_SELF'] ?>';
}
</script>

<?php include '../../include/footer.php'; ?>