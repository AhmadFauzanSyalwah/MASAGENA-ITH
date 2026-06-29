<?php
// dashboard/mahasiswa/kegiatan.php
session_start();

// ===== CEK LOGIN & ROLE MANUAL =====
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}
if ($_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../dashboard/' . $_SESSION['peran'] . '/index.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// ============================================================
// FUNGSI HIGHLIGHT
// ============================================================
if (!function_exists('highlightText')) {
    function highlightText($text, $keyword) {
        if (empty($keyword) || empty($text)) {
            return $text;
        }
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span class="highlight">$1</span>', $text);
    }
}

// ============================================================
// KONTEKS UNTUK HEADER
// ============================================================
$page_context = 'kegiatan';

// Ambil parameter pencarian dari header
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$context = isset($_GET['context']) ? $_GET['context'] : '';

// ===== AMBIL PARAMETER FILTER & PAGINATION =====
$filter_organisasi = isset($_GET['filter_organisasi']) ? (int)$_GET['filter_organisasi'] : 0;
$filter_jenis = isset($_GET['filter_jenis']) ? trim($_GET['filter_jenis']) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? trim($_GET['filter_kategori']) : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// ===== AMBIL DATA UNTUK DROPDOWN =====
$allOrganisasi = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi")->fetchAll();
$allKategori = $pdo->query("SELECT DISTINCT kategori FROM konten_kegiatan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
$allJenis = $pdo->query("SELECT DISTINCT jenis FROM organisasi WHERE jenis IS NOT NULL AND jenis != '' ORDER BY jenis")->fetchAll(PDO::FETCH_COLUMN);

// ============================================================
// BANGUN QUERY DENGAN FILTER DAN PENCARIAN
// ============================================================
$where = "k.status_publikasi = 'publik'";
$params = [];

// Pencarian (q)
if (!empty($q)) {
    $search = '%' . $q . '%';
    $where .= " AND (k.judul LIKE :q OR k.deskripsi LIKE :q OR k.kategori LIKE :q OR o.nama_organisasi LIKE :q)";
    $params[':q'] = $search;
}

// Filter lainnya
if (!empty($filter_jenis)) {
    $where .= " AND o.jenis = :jenis";
    $params[':jenis'] = $filter_jenis;
}
if (!empty($filter_kategori)) {
    $where .= " AND k.kategori = :kat";
    $params[':kat'] = $filter_kategori;
}
if ($filter_organisasi > 0) {
    $where .= " AND k.id_organisasi = :org";
    $params[':org'] = $filter_organisasi;
}
if ($filter_status == 'akan_datang') {
    $where .= " AND k.tanggal_kegiatan >= CURDATE()";
} elseif ($filter_status == 'lewat') {
    $where .= " AND k.tanggal_kegiatan < CURDATE()";
}

switch ($sort_by) {
    case 'terbaru':  $order = "k.tanggal_kegiatan DESC"; break;
    case 'terlama':  $order = "k.tanggal_kegiatan ASC"; break;
    case 'nama_asc': $order = "k.judul ASC"; break;
    case 'nama_desc':$order = "k.judul DESC"; break;
    default:         $order = "k.tanggal_kegiatan DESC";
}

// ============================================================
// HITUNG TOTAL KEGIATAN
// ============================================================
$countSql = "SELECT COUNT(*) FROM konten_kegiatan k JOIN organisasi o ON k.id_organisasi = o.id_organisasi WHERE $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalKegiatan = $countStmt->fetchColumn();
$totalPages = ceil($totalKegiatan / $limit);

// ============================================================
// QUERY KEGIATAN
// ============================================================
$sql = "SELECT k.*, o.nama_organisasi, o.jenis,
               COALESCE(k.kuota_maks, 50) AS kuota,
               (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_konten = k.id_konten AND p.status_pendaftaran != 'ditolak') AS jumlah_peserta
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE $where
        ORDER BY $order
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$kegiatan = $stmt->fetchAll();

// ============================================================
// INCLUDE HEADER
// ============================================================
include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.kegiatan-container { max-width: 1400px; margin: 0 auto; padding: 0 1rem; }

/* Filter bar */
.kegiatan-filter-bar {
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
.kegiatan-filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    flex: 0 1 130px;
    min-width: 100px;
}
.kegiatan-filter-bar .filter-group label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #071C34;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.1rem;
}
.kegiatan-filter-bar .filter-group select {
    border-radius: 50px;
    border: 1.5px solid #e2e8f0;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    background: #ffffff;
    height: 34px;
}
.kegiatan-filter-bar .filter-group select:focus {
    border-color: #FFA007;
    outline: none;
}
.kegiatan-filter-bar .btn-reset {
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
.kegiatan-filter-bar .btn-reset:hover {
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
.search-stats .stats-left i { color: #FFA007; font-size: 1.2rem; margin-right: 0.5rem; }
.search-stats .stats-left strong { color: #FFA007; }
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
.kegiatan-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}
.kegiatan-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.kegiatan-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(7,28,52,0.10);
    border-color: #FFA007;
}
.kegiatan-card .card-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f1f5f9;
    position: relative;
}
.kegiatan-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.kegiatan-card:hover .card-image img {
    transform: scale(1.03);
}
.kegiatan-card .card-image .no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #94a3b8;
    font-size: 3rem;
    background: #f1f5f9;
}
.kegiatan-card .card-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #FFA007;
    color: #071C34;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.kegiatan-card .card-body {
    padding: 1rem 1.2rem 0.8rem 1.2rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.kegiatan-card .card-body .org-name {
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.2rem;
}
.kegiatan-card .card-body .org-name i { color: #FFA007; }
.kegiatan-card .card-body .judul {
    font-size: 1.05rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.4rem;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.kegiatan-card .card-body .deskripsi {
    font-size: 0.85rem;
    color: #475569;
    line-height: 1.5;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.6rem;
}
.kegiatan-card .card-body .meta {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-bottom: 0.6rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kegiatan-card .card-body .meta .quota {
    font-weight: 600;
    color: #071C34;
}
.kegiatan-card .card-body .meta .quota.penuh { color: #dc2626; }
.kegiatan-card .card-footer {
    padding: 0.6rem 1.2rem 1rem 1.2rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.kegiatan-card .card-footer .btn-detail {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.2rem 1rem;
    font-size: 0.7rem;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
}
.kegiatan-card .card-footer .btn-detail:hover {
    background: #071C34;
    color: #fff;
}
.kegiatan-card .card-footer .btn-daftar {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.2rem 1rem;
    font-size: 0.7rem;
    font-weight: 700;
    transition: 0.3s;
    text-decoration: none;
}
.kegiatan-card .card-footer .btn-daftar:hover {
    background: #071C34;
    color: #fff;
}
.kegiatan-card .card-footer .btn-daftar.disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
}

/* ===== HIGHLIGHT KUNING ===== */
.highlight {
    background: #FFA007;
    color: #071C34;
    font-weight: 700;
    padding: 0 4px;
    border-radius: 4px;
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
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}
.empty-state i { font-size: 3.5rem; color: #cbd5e0; margin-bottom: 0.5rem; }

@media (max-width: 992px) {
    .kegiatan-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
    .kegiatan-grid { grid-template-columns: 1fr; }
    .kegiatan-filter-bar { flex-direction: column; align-items: stretch; }
    .kegiatan-filter-bar .filter-group { flex: 1; }
    .kegiatan-filter-bar .btn-reset { width: 100%; justify-content: center; margin-left: 0; }
    .search-stats { flex-direction: column; text-align: center; }
}
</style>

<div class="kegiatan-container">

    <!-- FILTER -->
    <div class="kegiatan-filter-bar">
        <form id="filterForm" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>" style="display:contents;">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
            <?php if (!empty($context)): ?>
                <input type="hidden" name="context" value="<?= htmlspecialchars($context) ?>">
            <?php endif; ?>

            <div class="filter-group">
                <label>Status</label>
                <select name="filter_status" onchange="this.form.submit()">
                    <option value="semua" <?= $filter_status=='semua'?'selected':'' ?>>Semua</option>
                    <option value="akan_datang" <?= $filter_status=='akan_datang'?'selected':'' ?>>Akan Datang</option>
                    <option value="lewat" <?= $filter_status=='lewat'?'selected':'' ?>>Sudah Lewat</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Jenis Org</label>
                <select name="filter_jenis" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach ($allJenis as $jenis): ?>
                        <option value="<?= htmlspecialchars($jenis) ?>" <?= ($filter_jenis == $jenis) ? 'selected' : '' ?>><?= htmlspecialchars($jenis) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Kategori</label>
                <select name="filter_kategori" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach($allKategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>" <?= $filter_kategori==$kat?'selected':'' ?>><?= htmlspecialchars($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Organisasi</label>
                <select name="filter_organisasi" onchange="this.form.submit()">
                    <option value="0">Semua</option>
                    <?php foreach($allOrganisasi as $org): ?>
                        <option value="<?= $org['id_organisasi'] ?>" <?= $filter_organisasi==$org['id_organisasi']?'selected':'' ?>><?= htmlspecialchars($org['nama_organisasi']) ?></option>
                    <?php endforeach; ?>
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

    <!-- STATISTIK -->
    <?php if (!empty($q) || $filter_jenis || $filter_kategori || $filter_organisasi > 0 || $filter_status != 'semua'): ?>
        <div class="search-stats">
            <span class="stats-left"><i class="fas fa-search"></i> Menampilkan <strong><?= $totalKegiatan ?></strong> hasil
                <?php if (!empty($q)): ?> untuk "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
            </span>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn-clear"><i class="fas fa-times"></i> Hapus filter</a>
        </div>
    <?php endif; ?>

    <!-- GRID -->
    <?php if ($totalKegiatan > 0): ?>
        <div class="kegiatan-grid">
            <?php foreach ($kegiatan as $row):
                $kuota = (int)$row['kuota'];
                $jumlah = (int)$row['jumlah_peserta'];
                $penuh = $kuota > 0 && $jumlah >= $kuota;
                $imagePath = '';
                if (!empty($row['lampiran']) && file_exists('../../uploads/kegiatan/' . $row['lampiran'])) {
                    $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $row['lampiran'];
                } else {
                    $exts = ['jpg', 'jpeg', 'png', 'gif'];
                    foreach ($exts as $ext) {
                        $base = basename($row['lampiran'], '.' . pathinfo($row['lampiran'], PATHINFO_EXTENSION));
                        if (file_exists('../../uploads/kegiatan/' . $base . '.' . $ext)) {
                            $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $base . '.' . $ext;
                            break;
                        }
                    }
                }

                // Terapkan highlight
                $highlightedJudul = highlightText($row['judul'], $q);
                $highlightedDeskripsi = highlightText(substr($row['deskripsi'], 0, 150), $q);
                $highlightedOrg = highlightText($row['nama_organisasi'], $q);
                $highlightedKategori = highlightText($row['kategori'], $q);
            ?>
                <div class="kegiatan-card" data-id="<?= $row['id_konten'] ?>">
                    <div class="card-image">
                        <?php if (!empty($imagePath)): ?>
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($row['judul']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i></div>
                        <?php endif; ?>
                        <?php if (!empty($row['kategori'])): ?>
                            <span class="card-badge"><?= $highlightedKategori ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="org-name"><i class="fa-regular fa-building"></i> <?= $highlightedOrg ?></div>
                        <div class="judul"><?= $highlightedJudul ?></div>
                        <div class="deskripsi"><?= $highlightedDeskripsi ?>...</div>
                        <div class="meta">
                            <span class="quota <?= $penuh ? 'penuh' : '' ?>">
                                <?php if ($penuh): ?>
                                    <i class="fas fa-exclamation-circle"></i> Kuota Penuh
                                <?php else: ?>
                                    <i class="fas fa-users"></i> <?= $jumlah ?>/<?= $kuota ?>
                                <?php endif; ?>
                            </span>
                            <span><i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($row['tanggal_kegiatan'])) ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="detail_kegiatan.php?id=<?= $row['id_konten'] ?>&back=<?= urlencode('/MASAGENA-ITH/dashboard/mahasiswa/kegiatan.php') ?>" class="btn-detail">Lihat Detail</a>
                        <?php if ($penuh): ?>
                            <span class="btn-daftar disabled">Daftar</span>
                        <?php else: ?>
                            <a href="form_pendaftaran_kegiatan.php?id_konten=<?= $row['id_konten'] ?>" class="btn-daftar">Daftar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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

    <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-circle-xmark"></i>
            <h4>Belum ada kegiatan</h4>
            <p class="text-muted">Saat ini belum ada kegiatan yang tersedia atau sesuai filter.</p>
        </div>
    <?php endif; ?>

</div>

<script>
function resetFilter() {
    var form = document.getElementById('filterForm');
    form.querySelectorAll('select').forEach(function(sel) {
        sel.selectedIndex = 0;
    });
    // Hapus q juga dengan redirect ke halaman tanpa q
    window.location.href = '<?= $_SERVER['PHP_SELF'] ?>';
}
</script>

<?php include '../../include/footer.php'; ?>