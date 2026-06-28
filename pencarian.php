<?php
/**
 * pencarian.php - Halaman pencarian dengan filter di header
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// ============================================================
// FUNGSI HELPER
// ============================================================
if (!function_exists('h')) {
    function h($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('short_text')) {
    function short_text($text, $limit = 120) {
        $text = trim(strip_tags((string) $text));
        if (strlen($text) <= $limit) return $text;
        return substr($text, 0, $limit) . '...';
    }
}
if (!function_exists('rupiah_date')) {
    function rupiah_date($date) {
        return date('d M Y', strtotime($date));
    }
}
if (!function_exists('highlightText')) {
    function highlightText($text, $keyword) {
        if (empty($keyword)) return $text;
        return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span class="highlight">$1</span>', $text);
    }
}

// ============================================================
// PROSES PENCARIAN (sama seperti sebelumnya)
// ============================================================
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_jenis = isset($_GET['filter_jenis']) ? trim($_GET['filter_jenis']) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? trim($_GET['filter_kategori']) : '';
$filter_organisasi = isset($_GET['filter_organisasi']) ? (int)$_GET['filter_organisasi'] : 0;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'relevansi';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$results = ['kegiatan' => [], 'organisasi' => [], 'pengurus' => []];

$where_kegiatan = "k.status_publikasi = 'publik'";
$where_organisasi = "1=1";
$where_pengurus = "1=1";

if (!empty($keyword)) {
    $search = '%' . $keyword . '%';
    $where_kegiatan .= " AND (k.judul LIKE :keyword OR k.deskripsi LIKE :keyword OR k.kategori LIKE :keyword OR o.nama_organisasi LIKE :keyword)";
    $where_organisasi .= " AND (nama_organisasi LIKE :keyword OR deskripsi LIKE :keyword OR jenis LIKE :keyword)";
    $where_pengurus .= " AND (p.nama_pengurus LIKE :keyword OR p.no_hp LIKE :keyword)";
}
if (!empty($filter_jenis)) {
    $where_organisasi .= " AND jenis = :filter_jenis";
    $where_kegiatan .= " AND o.jenis = :filter_jenis";
}
if (!empty($filter_kategori)) {
    $where_kegiatan .= " AND k.kategori = :filter_kategori";
}
if ($filter_organisasi > 0) {
    $where_kegiatan .= " AND k.id_organisasi = :filter_organisasi";
    $where_pengurus .= " AND p.id_organisasi = :filter_organisasi";
}

switch ($sort_by) {
    case 'terbaru':  $order_kegiatan = "k.tanggal_kegiatan DESC"; $order_organisasi = "nama_organisasi ASC"; $order_pengurus = "nama_pengurus ASC"; break;
    case 'terlama':  $order_kegiatan = "k.tanggal_kegiatan ASC";  $order_organisasi = "nama_organisasi ASC"; $order_pengurus = "nama_pengurus ASC"; break;
    case 'nama_asc': $order_kegiatan = "k.judul ASC";            $order_organisasi = "nama_organisasi ASC"; $order_pengurus = "nama_pengurus ASC"; break;
    case 'nama_desc':$order_kegiatan = "k.judul DESC";           $order_organisasi = "nama_organisasi DESC";$order_pengurus = "nama_pengurus DESC"; break;
    default:         $order_kegiatan = "k.tanggal_kegiatan DESC";$order_organisasi = "nama_organisasi ASC"; $order_pengurus = "nama_pengurus ASC";
}

if (!empty($keyword)) {
    try {
        $sqlKegiatan = "SELECT k.*, o.nama_organisasi, o.jenis FROM konten_kegiatan k JOIN organisasi o ON k.id_organisasi = o.id_organisasi WHERE $where_kegiatan ORDER BY $order_kegiatan";
        $stmt = $pdo->prepare($sqlKegiatan);
        $params = [':keyword' => $search];
        if (!empty($filter_jenis)) $params[':filter_jenis'] = $filter_jenis;
        if (!empty($filter_kategori)) $params[':filter_kategori'] = $filter_kategori;
        if ($filter_organisasi > 0) $params[':filter_organisasi'] = $filter_organisasi;
        $stmt->execute($params);
        $results['kegiatan'] = $stmt->fetchAll();

        $sqlOrganisasi = "SELECT * FROM organisasi WHERE $where_organisasi ORDER BY $order_organisasi";
        $stmt = $pdo->prepare($sqlOrganisasi);
        $params = [':keyword' => $search];
        if (!empty($filter_jenis)) $params[':filter_jenis'] = $filter_jenis;
        $stmt->execute($params);
        $results['organisasi'] = $stmt->fetchAll();

        $sqlPengurus = "SELECT p.*, o.nama_organisasi, o.jenis FROM pengurus_organisasi p JOIN organisasi o ON p.id_organisasi = o.id_organisasi WHERE $where_pengurus ORDER BY $order_pengurus";
        $stmt = $pdo->prepare($sqlPengurus);
        $params = [':keyword' => $search];
        if ($filter_organisasi > 0) $params[':filter_organisasi'] = $filter_organisasi;
        $stmt->execute($params);
        $results['pengurus'] = $stmt->fetchAll();
    } catch (PDOException $e) { error_log("Pencarian error: " . $e->getMessage()); }
}

$filtered_results = [];
if ($type == 'all' || $type == 'kegiatan')   $filtered_results['kegiatan'] = $results['kegiatan'];
if ($type == 'all' || $type == 'organisasi') $filtered_results['organisasi'] = $results['organisasi'];
if ($type == 'all' || $type == 'pengurus')   $filtered_results['pengurus'] = $results['pengurus'];
$totalResults = count($filtered_results['kegiatan'] ?? []) + count($filtered_results['organisasi'] ?? []) + count($filtered_results['pengurus'] ?? []);

// ============================================================
// GANTI HEADER DENGAN HEADER KHUSUS PENCARIAN
// ============================================================
include 'include/header_pencarian.php';
?>

<style>
/* ===== KONTEN PENCARIAN ===== */
.pencarian-page {
    background: #ffffff;
    padding: 0 1rem;
}
.pencarian-page .container-fluid {
    max-width: 1400px;
}
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
    margin: 1.5rem 0 2rem 0;
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
.section-header {
    display: inline-flex;
    align-items: stretch;
    margin: 2.5rem 0 1.5rem 0;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.section-header .header-left {
    background: #071C34;
    color: #fff;
    padding: 0.5rem 2rem 0.5rem 1.8rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.section-header .header-left i {
    color: #FFA007;
    font-size: 1.2rem;
}
.section-header .header-right {
    background: #FFA007;
    color: #071C34;
    padding: 0.5rem 1.8rem 0.5rem 1.5rem;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
}
.section-header .header-right .count {
    background: #071C34;
    color: #fff;
    border-radius: 50%;
    padding: 0.1rem 0.5rem;
    font-size: 0.65rem;
    font-weight: 700;
}
.card-item {
    border-radius: 16px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    transition: all 0.3s ease;
    height: 100%;
    padding: 1.2rem 1.2rem 1rem 1.2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
}
.card-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(7,28,52,0.08);
    border-color: #FFA007;
}
.card-item .card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
}
.card-item .card-text {
    color: #475569;
    font-size: 0.85rem;
    line-height: 1.5;
    flex: 1;
}
.card-item .card-meta {
    color: #94a3b8;
    font-size: 0.75rem;
    margin: 0.4rem 0;
}
.card-item .card-meta i { color: #FFA007; margin-right: 0.3rem; }
.card-item .badge-org {
    background: #071C34;
    color: #fff;
    border-radius: 50px;
    padding: 0.15rem 0.7rem;
    font-size: 0.65rem;
    font-weight: 500;
    display: inline-block;
    margin-right: 0.3rem;
}
.card-item .badge-kategori {
    background: #FFA007;
    color: #071C34;
    border-radius: 50px;
    padding: 0.15rem 0.7rem;
    font-size: 0.65rem;
    font-weight: 600;
    display: inline-block;
}
.card-item .badge-status {
    background: #e2e8f0;
    color: #475569;
    border-radius: 50px;
    padding: 0.1rem 0.6rem;
    font-size: 0.6rem;
    font-weight: 500;
}
.card-item .btn-detail {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.2rem 1.2rem;
    font-size: 0.75rem;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    align-self: flex-start;
    margin-top: 0.5rem;
}
.card-item .btn-detail:hover {
    background: #071C34;
    color: #fff;
}
.highlight {
    background: #FFA007;
    color: #071C34;
    font-weight: 700;
    padding: 0 4px;
    border-radius: 4px;
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}
.empty-state i { font-size: 3.5rem; color: #cbd5e0; margin-bottom: 0.5rem; }
@media (max-width: 768px) {
    .section-header .header-left { font-size: 0.95rem; padding: 0.3rem 1.2rem; }
    .section-header .header-right { font-size: 0.7rem; padding: 0.3rem 1.2rem; }
    .search-stats { flex-direction: column; text-align: center; }
}
</style>

<div class="container-fluid pencarian-page">
    <!-- HASIL PENCARIAN -->
    <?php if(empty($keyword)): ?>
        <div class="empty-state">
            <i class="fa-regular fa-keyboard"></i>
            <h4>Masukkan kata kunci di form pencarian</h4>
            <p>Cari berdasarkan judul kegiatan, nama organisasi, atau nama pengurus.</p>
        </div>
    <?php else: ?>
        <div class="search-stats">
            <span class="stats-left"><i class="fas fa-search"></i> Menampilkan <strong><?= $totalResults ?></strong> hasil untuk "<strong><?= h($keyword) ?></strong>"</span>
            <a href="<?= BASE_URL ?>/pencarian.php" class="btn-clear"><i class="fas fa-times"></i> Hapus filter</a>
        </div>

        <!-- KEGIATAN -->
        <?php if($type=='all' || $type=='kegiatan'): ?>
        <div class="section-header">
            <span class="header-left"><i class="fa-regular fa-calendar"></i> KEGIATAN DAN AGENDA</span>
            <span class="header-right"><span class="count"><?= count($results['kegiatan']) ?></span> DITEMUKAN</span>
        </div>
        <div class="row">
            <?php if(count($results['kegiatan'])>0): ?>
                <?php foreach($results['kegiatan'] as $row): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card-item">
                            <h5 class="card-title"><?= highlightText(h($row['judul']), $keyword) ?></h5>
                            <div class="mb-2">
                                <span class="badge-org"><?= h($row['nama_organisasi']) ?></span>
                                <?php if($row['kategori']): ?><span class="badge-kategori"><?= h($row['kategori']) ?></span><?php endif; ?>
                            </div>
                            <p class="card-text"><?= highlightText(short_text($row['deskripsi'], 120), $keyword) ?></p>
                            <div class="card-meta"><i class="fa-regular fa-clock"></i> <?= rupiah_date($row['tanggal_kegiatan']) ?></div>
                            <a href="<?= BASE_URL ?>/detail_kegiatan.php?id=<?= $row['id_konten'] ?>" class="btn-detail">Lihat Detail <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted"><i class="fa-regular fa-circle-xmark"></i> Tidak ada kegiatan yang cocok.</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ORGANISASI -->
        <?php if($type=='all' || $type=='organisasi'): ?>
        <div class="section-header">
            <span class="header-left"><i class="fa-regular fa-building"></i> ORGANISASI</span>
            <span class="header-right"><span class="count"><?= count($results['organisasi']) ?></span> DITEMUKAN</span>
        </div>
        <div class="row">
            <?php if(count($results['organisasi'])>0): ?>
                <?php foreach($results['organisasi'] as $row): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card-item">
                            <h5 class="card-title"><?= highlightText(h($row['nama_organisasi']), $keyword) ?></h5>
                            <p><span class="badge bg-secondary"><?= h($row['jenis']) ?></span></p>
                            <p class="card-text"><?= highlightText(short_text($row['deskripsi'], 100), $keyword) ?></p>
                            <a href="<?= BASE_URL ?>/organisasi_detail.php?id=<?= $row['id_organisasi'] ?>" class="btn-detail">Lihat Profil <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted"><i class="fa-regular fa-circle-xmark"></i> Tidak ada organisasi yang cocok.</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- PENGURUS -->
        <?php if($type=='all' || $type=='pengurus'): ?>
        <div class="section-header">
            <span class="header-left"><i class="fa-regular fa-user"></i> PENGURUS ORGANISASI</span>
            <span class="header-right"><span class="count"><?= count($results['pengurus']) ?></span> DITEMUKAN</span>
        </div>
        <div class="row">
            <?php if(count($results['pengurus'])>0): ?>
                <?php foreach($results['pengurus'] as $row): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card-item">
                            <h5 class="card-title"><i class="fa-regular fa-user-circle"></i> <?= highlightText(h($row['nama_pengurus']), $keyword) ?></h5>
                            <p><span class="badge-org"><?= h($row['nama_organisasi']) ?></span></p>
                            <p class="card-text"><i class="fa-solid fa-phone"></i> <?= h($row['no_hp']) ?></p>
                            <p class="card-text"><span class="badge-status"><?= $row['status_verifikasi'] ?? 'Belum Verifikasi' ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted"><i class="fa-regular fa-circle-xmark"></i> Tidak ada pengurus yang cocok.</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>