<?php
<<<<<<< HEAD
// dashboard/mahasiswa/organisasi.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ===== AMBIL PARAMETER FILTER =====
$filter_jenis = isset($_GET['filter_jenis']) ? trim($_GET['filter_jenis']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// ===== AMBIL JENIS UNIK UNTUK FILTER =====
$allJenis = $pdo->query("SELECT DISTINCT jenis FROM organisasi WHERE jenis IS NOT NULL AND jenis != '' ORDER BY jenis")->fetchAll(PDO::FETCH_COLUMN);

// ============================================================
// BANGUN QUERY DENGAN FILTER
// ============================================================
$where = "1=1";
$params = [];

if (!empty($filter_jenis)) {
    $where .= " AND jenis = :jenis";
    $params[':jenis'] = $filter_jenis;
}

// ============================================================
// HITUNG TOTAL ORGANISASI (UNTUK PAGINATION)
// ============================================================
$countSql = "SELECT COUNT(*) FROM organisasi WHERE $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalOrganisasi = $countStmt->fetchColumn();
$totalPages = ceil($totalOrganisasi / $limit);

// ============================================================
// QUERY ORGANISASI DENGAN LIMIT & OFFSET
// ============================================================
$sql = "SELECT * FROM organisasi WHERE $where ORDER BY nama_organisasi ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$organisasi = $stmt->fetchAll();

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.organisasi-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Filter bar */
.organisasi-filter-bar {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.8rem 1.2rem;
}
.organisasi-filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    flex: 0 1 200px;
    min-width: 150px;
}
.organisasi-filter-bar .filter-group label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #071C34;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.1rem;
}
.organisasi-filter-bar .filter-group select {
    border-radius: 50px;
    border: 1.5px solid #e2e8f0;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    background: #ffffff;
    height: 34px;
}
.organisasi-filter-bar .filter-group select:focus {
    border-color: #FFA007;
    outline: none;
}
.organisasi-filter-bar .btn-reset {
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
.organisasi-filter-bar .btn-reset:hover {
    background: #0a2a4a;
    color: #ffffff;
}

/* Search stats */
.search-stats {
    background: linear-gradient(135deg, #071C34 0%, #0a2a4a 100%);
    border-radius: 16px;
    padding: 0.8rem 2rem;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-bottom: 2rem;
}
.search-stats .stats-left i {
    color: #FFA007;
    font-size: 1.2rem;
    margin-right: 0.5rem;
}
.search-stats .stats-left strong {
    color: #FFA007;
}
.search-stats .btn-clear {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 0.25rem 1.2rem;
    font-size: 0.8rem;
    transition: 0.3s;
    text-decoration: none;
}
.search-stats .btn-clear:hover {
    background: #FFA007;
    color: #071C34;
}

/* Grid */
.organisasi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}
.organisasi-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.organisasi-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(7,28,52,0.10);
    border-color: #FFA007;
}
.organisasi-card .card-logo {
    width: 100%;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    font-size: 4rem;
    color: #071C34;
}
.organisasi-card .card-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    padding: 1rem;
}
.organisasi-card .card-body {
    padding: 1.2rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.organisasi-card .card-body .nama {
    font-size: 1.1rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
}
.organisasi-card .card-body .jenis {
    display: inline-block;
    background: #FFA007;
    color: #071C34;
    padding: 0.15rem 0.8rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    align-self: flex-start;
    margin-bottom: 0.6rem;
}
.organisasi-card .card-body .deskripsi {
    font-size: 0.85rem;
    color: #475569;
    line-height: 1.5;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.organisasi-card .card-footer {
    padding: 0.6rem 1.2rem 1rem 1.2rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
}
.organisasi-card .card-footer .btn-profil {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.2rem 1.2rem;
    font-size: 0.75rem;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
}
.organisasi-card .card-footer .btn-profil:hover {
    background: #071C34;
    color: #fff;
}

/* Pagination */
.pagination-wrapper {
    margin: 2.5rem 0 1rem 0;
    display: flex;
    justify-content: center;
}
.pagination {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}
.pagination a,
.pagination span {
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
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}
.empty-state i {
    font-size: 3.5rem;
    color: #cbd5e0;
    margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 992px) {
    .organisasi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .organisasi-grid {
        grid-template-columns: 1fr;
    }
    .organisasi-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .organisasi-filter-bar .filter-group {
        flex: 1;
    }
    .organisasi-filter-bar .btn-reset {
        width: 100%;
        justify-content: center;
        margin-left: 0;
    }
    .search-stats {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<div class="organisasi-container">

    <!-- FILTER -->
    <div class="organisasi-filter-bar">
        <form id="filterForm" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>" style="display:contents;">
            <input type="hidden" name="page" value="1">

            <div class="filter-group">
                <label>Jenis Organisasi</label>
                <select name="filter_jenis" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach ($allJenis as $jenis): ?>
                        <option value="<?= htmlspecialchars($jenis) ?>" <?= ($filter_jenis == $jenis) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($jenis) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" class="btn-reset" onclick="resetFilter()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </form>
    </div>

    <!-- STATISTIK -->
    <?php if (!empty($filter_jenis)): ?>
        <div class="search-stats">
            <span class="stats-left"><i class="fas fa-search"></i> Menampilkan <strong><?= $totalOrganisasi ?></strong> organisasi jenis "<strong><?= htmlspecialchars($filter_jenis) ?></strong>"</span>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn-clear"><i class="fas fa-times"></i> Hapus filter</a>
        </div>
    <?php endif; ?>

    <!-- GRID ORGANISASI -->
    <?php if ($totalOrganisasi > 0): ?>
        <div class="organisasi-grid">
            <?php foreach ($organisasi as $row): ?>
                <div class="organisasi-card">
                    <div class="card-logo">
                        <?php if (!empty($row['logo']) && file_exists('../../uploads/logo/' . $row['logo'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/logo/<?= $row['logo'] ?>" alt="Logo <?= htmlspecialchars($row['nama_organisasi']) ?>">
                        <?php else: ?>
                            <i class="fas fa-building"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="nama"><?= htmlspecialchars($row['nama_organisasi']) ?></div>
                        <span class="jenis"><?= htmlspecialchars($row['jenis']) ?></span>
                        <div class="deskripsi"><?= htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 120)) ?>...</div>
                    </div>
                    <div class="card-footer">
                        <a href="detail_organisasi.php?id=<?= $row['id_organisasi'] ?>" class="btn-profil">Lihat Profil <i class="fas fa-arrow-right"></i></a>
=======
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
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
<<<<<<< HEAD

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">‹</a>
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
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">›</a>
                    <?php else: ?>
                        <span class="disabled">›</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-building"></i>
            <h4>Belum ada organisasi</h4>
            <p class="text-muted">Belum ada organisasi yang terdaftar atau sesuai filter.</p>
=======
    <?php else: ?>
        <div class="alert" style="margin-top: 1.5rem;">
            Belum ada data organisasi yang terdaftar saat ini.
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
        </div>
    <?php endif; ?>

</div>

<<<<<<< HEAD
<script>
function resetFilter() {
    var form = document.getElementById('filterForm');
    form.querySelectorAll('select').forEach(function(sel) {
        sel.selectedIndex = 0;
    });
    form.submit();
}
</script>

<?php include '../../include/footer.php'; ?>
=======
<?php include '../../include/footer.php'; ?>    
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
