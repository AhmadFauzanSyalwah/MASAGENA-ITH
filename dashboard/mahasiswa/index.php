<?php
// dashboard/mahasiswa/index.php
session_start();
require_once '../../config/session_check.php';
require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

// Ambil parameter filter
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_jenis = isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : '';
$filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';
$filter_organisasi = isset($_GET['filter_organisasi']) ? (int)$_GET['filter_organisasi'] : 0;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'relevansi';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9; // 9 kegiatan per halaman (3x3)
$offset = ($page - 1) * $limit;

// Ambil daftar organisasi dan kategori untuk dropdown filter
$allOrganisasi = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi")->fetchAll();
$allKategori = $pdo->query("SELECT DISTINCT kategori FROM konten_kegiatan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

// Bangun query untuk kegiatan (dengan filter)
$where = "k.status_publikasi = 'publik'";
$params = [];

if (!empty($q)) {
    $search = '%' . $q . '%';
    $where .= " AND (k.judul LIKE :q OR k.deskripsi LIKE :q OR k.kategori LIKE :q OR o.nama_organisasi LIKE :q)";
    $params[':q'] = $search;
}
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

// Sorting
switch ($sort_by) {
    case 'terbaru':  $order = "k.tanggal_kegiatan DESC"; break;
    case 'terlama':  $order = "k.tanggal_kegiatan ASC"; break;
    case 'nama_asc': $order = "k.judul ASC"; break;
    case 'nama_desc':$order = "k.judul DESC"; break;
    default:         $order = "k.tanggal_kegiatan DESC";
}

// Hitung total kegiatan (untuk pagination)
$countSql = "SELECT COUNT(*) FROM konten_kegiatan k JOIN organisasi o ON k.id_organisasi = o.id_organisasi WHERE $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalKegiatan = $countStmt->fetchColumn();
$totalPages = ceil($totalKegiatan / $limit);

// Query kegiatan dengan limit & offset
$sql = "SELECT k.*, o.nama_organisasi,
               (SELECT COUNT(*) FROM likes WHERE id_konten = k.id_konten) as total_likes,
               (SELECT COUNT(*) FROM komentar WHERE id_konten = k.id_konten) as total_komentar
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

// Cek kolom user di tabel likes (untuk pengecekan like)
$likeUserColumn = 'id_user'; // default
try {
    $columns = $pdo->query("SHOW COLUMNS FROM likes")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('id_mahasiswa', $columns)) {
        $likeUserColumn = 'id_mahasiswa';
    } elseif (in_array('id_user', $columns)) {
        $likeUserColumn = 'id_user';
    } elseif (in_array('id_pengguna', $columns)) {
        $likeUserColumn = 'id_pengguna';
    }
} catch (PDOException $e) {
    // Jika tabel likes tidak ada, abaikan
    $likeUserColumn = null;
}

// Ambil pengumuman (untuk di bawah)
$stmtPengumuman = $pdo->query("SELECT * FROM konten_kegiatan WHERE kategori = 'pengumuman' AND status_publikasi = 'publik' ORDER BY created_at DESC LIMIT 3");
$pengumuman = $stmtPengumuman->fetchAll();

$isFilterActive = (!empty($q) || !empty($filter_jenis) || !empty($filter_kategori) || $filter_organisasi > 0);

include '../../include/header.php';
?>

<<<<<<< HEAD
<style>
/* ===== BERANDA GRID 3 KOLOM ===== */
.container-beranda {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}
.filter-area {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0 2rem 0;
    border: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.8rem 1.2rem;
}
.filter-area .filter-group {
    display: flex;
    flex-direction: column;
    flex: 0 1 130px;
    min-width: 100px;
}
.filter-area .filter-group label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #071C34;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.1rem;
}
.filter-area .filter-group select {
    border-radius: 50px;
    border: 1.5px solid #e2e8f0;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    background: #ffffff;
    height: 34px;
}
.filter-area .filter-group select:focus {
    border-color: #FFA007;
    outline: none;
}
.filter-area .btn-reset-filter {
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
.filter-area .btn-reset-filter:hover {
    background: #0a2a4a;
    color: #ffffff;
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
    margin: 0 0 1.5rem 0;
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
    box-shadow: 0 12px 30px rgba(7, 28, 52, 0.10);
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
.kegiatan-card .card-body .org-name i {
    color: #FFA007;
}
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
.kegiatan-card .card-body .tanggal {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-bottom: 0.6rem;
}
.kegiatan-card .card-body .tanggal i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.kegiatan-card .card-footer {
    padding: 0.6rem 1.2rem 1rem 1.2rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.kegiatan-card .card-footer .interaksi {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}
.kegiatan-card .card-footer .interaksi .btn-interaksi {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    color: #64748b;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0.2rem 0.4rem;
    border-radius: 30px;
}
.kegiatan-card .card-footer .interaksi .btn-interaksi:hover {
    color: #FFA007;
    background: rgba(255,160,7,0.08);
}
.kegiatan-card .card-footer .interaksi .btn-like.liked {
    color: #ff4757;
}
.kegiatan-card .card-footer .interaksi .btn-like.liked i {
    font-weight: 900;
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

/* Responsive */
@media (max-width: 992px) {
    .kegiatan-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
    .kegiatan-grid { grid-template-columns: 1fr; }
    .filter-area { flex-direction: column; align-items: stretch; }
    .filter-area .filter-group { flex: 1; }
    .filter-area .btn-reset-filter { width: 100%; justify-content: center; margin-left: 0; }
    .search-stats { flex-direction: column; text-align: center; }
}

@keyframes heartBeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
.heart-animate { animation: heartBeat 0.3s ease; }
</style>

<div class="container-beranda">

    <?php if ($isFilterActive): ?>
        <div class="search-stats">
            <span class="stats-left"><i class="fas fa-search"></i> Menampilkan <strong><?= $totalKegiatan ?></strong> hasil 
                <?php if (!empty($q)): ?> untuk "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
            </span>
            <a href="/MASAGENA-ITH/dashboard/mahasiswa/index.php" class="btn-clear"><i class="fas fa-times"></i> Hapus filter</a>
        </div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="filter-area">
        <form id="filterForm" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>" style="display:contents;">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

            <div class="filter-group">
                <label>Jenis Org</label>
                <select name="filter_jenis" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="BEM" <?= $filter_jenis=='BEM'?'selected':'' ?>>BEM</option>
                    <option value="UKM" <?= $filter_jenis=='UKM'?'selected':'' ?>>UKM</option>
                    <option value="SC" <?= $filter_jenis=='SC'?'selected':'' ?>>Study Club</option>
                    <option value="Himpunan" <?= $filter_jenis=='Himpunan'?'selected':'' ?>>Himpunan</option>
                </select>
            </div>

            <?php if(!empty($allKategori)): ?>
            <div class="filter-group">
                <label>Kategori</label>
                <select name="filter_kategori" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach($allKategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>" <?= $filter_kategori==$kat?'selected':'' ?>><?= htmlspecialchars($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

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
                    <option value="relevansi" <?= $sort_by=='relevansi'?'selected':'' ?>>Relevansi</option>
                    <option value="terbaru" <?= $sort_by=='terbaru'?'selected':'' ?>>Terbaru</option>
                    <option value="terlama" <?= $sort_by=='terlama'?'selected':'' ?>>Terlama</option>
                    <option value="nama_asc" <?= $sort_by=='nama_asc'?'selected':'' ?>>Nama A-Z</option>
                    <option value="nama_desc" <?= $sort_by=='nama_desc'?'selected':'' ?>>Nama Z-A</option>
                </select>
            </div>

            <button type="button" class="btn-reset-filter" onclick="resetFilter()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </form>
    </div>

    <?php if (!$isFilterActive): ?>
        <div class="dashboard-welcome">
            <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></h1>
            <p>Ini adalah portal informasi kegiatan kemahasiswaan ITH.</p>
        </div>
    <?php endif; ?>

    <!-- GRID KEGIATAN -->
    <?php if ($totalKegiatan > 0): ?>
        <div class="kegiatan-grid">
            <?php foreach ($kegiatan as $k): 
                // Cek gambar
                $imagePath = '';
                if (!empty($k['lampiran']) && file_exists('../../uploads/kegiatan/' . $k['lampiran'])) {
                    $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $k['lampiran'];
                } else {
                    // Coba cari file lain
                    $exts = ['jpg', 'jpeg', 'png', 'gif'];
                    foreach ($exts as $ext) {
                        $base = basename($k['lampiran'], '.' . pathinfo($k['lampiran'], PATHINFO_EXTENSION));
                        if (file_exists('../../uploads/kegiatan/' . $base . '.' . $ext)) {
                            $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $base . '.' . $ext;
                            break;
                        }
                    }
                }
                if (empty($imagePath)) $imagePath = '';

                // Cek like
                $isLiked = false;
                if (isset($_SESSION['user_id']) && $likeUserColumn) {
                    try {
                        $likeCheck = $pdo->prepare("SELECT 1 FROM likes WHERE $likeUserColumn = ? AND id_konten = ?");
                        $likeCheck->execute([$_SESSION['user_id'], $k['id_konten']]);
                        $isLiked = $likeCheck->fetchColumn() > 0;
                    } catch (PDOException $e) {
                        $isLiked = false;
                    }
                }
            ?>
                <div class="kegiatan-card" data-id="<?= $k['id_konten'] ?>">
                    <div class="card-image">
                        <?php if (!empty($imagePath)): ?>
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($k['judul']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i></div>
                        <?php endif; ?>
                        <?php if (!empty($k['kategori'])): ?>
                            <span class="card-badge"><?= htmlspecialchars($k['kategori']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="org-name"><i class="fa-regular fa-building"></i> <?= htmlspecialchars($k['nama_organisasi']) ?></div>
                        <div class="judul"><?= htmlspecialchars($k['judul']) ?></div>
                        <div class="deskripsi"><?= htmlspecialchars(substr($k['deskripsi'], 0, 150)) ?>...</div>
                        <div class="tanggal"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($k['tanggal_kegiatan'])) ?></div>
                    </div>
                    <div class="card-footer">
                        <div class="interaksi">
                            <button class="btn-interaksi btn-like <?= $isLiked ? 'liked' : '' ?>" data-id="<?= $k['id_konten'] ?>">
                                <i class="<?= $isLiked ? 'fas' : 'far' ?> fa-heart"></i>
                                <span class="like-count"><?= $k['total_likes'] ?></span>
                            </button>
                            <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>#komentar" class="btn-interaksi" style="text-decoration:none;">
                                <i class="far fa-comment"></i> <span><?= $k['total_komentar'] ?></span>
                            </a>
                            <button class="btn-interaksi btn-share" data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/MASAGENA-ITH/dashboard/mahasiswa/detail_kegiatan.php?id=' . $k['id_konten'] ?>">
                                <i class="fas fa-share-alt"></i>
                            </button>
=======
    <style>
        /* Animasi heart */
        .heart-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0;
        }
        .heart-icon {
            font-size: 1.2rem;
            transition: transform 0.2s ease;
        }
        .heart-btn:active .heart-icon {
            transform: scale(1.3);
        }
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .heart-animate {
            animation: heartBeat 0.3s ease;
        }
        .like-count, .komentar-count {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .interaction-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .interaction {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .komentar-link, .share-link {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .komentar-link:hover, .share-link:hover {
            color: var(--accent);
        }
        .btn-sm {
            padding: 0.2rem 0.8rem;
            font-size: 0.75rem;
        }
        /* Efek Bulatan Indikator di Foto (Khas Instagram Carousel) */
        .photo-wrapper {
            position: relative;
            display: inline-block;
        }
        .indicator-dot {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        /* Efek Modal/Pop-up Foto Full Screen saat Di-tab */
        .modal-foto {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            align-items: center;
            justify-content: center;
        }
        .modal-foto img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(255,255,255,0.1);
        }
    </style>

    <div class="dashboard-welcome">
        <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Mahasiswa') ?></h1>
        <p>Ini adalah portal informasi kegiatan kemahasiswaan ITH.</p>
    </div>

    <div class="dashboard-grid">
        <div class="main-content">
            <h2>Kegiatan Terbaru</h2>
            <?php if (count($kegiatan_terbaru) > 0): ?>
                <div class="kegiatan-list">
                    <?php foreach ($kegiatan_terbaru as $k): ?>
                        <div class="card" data-id="<?= $k['id_konten'] ?>">
                            <h3><a href="detail_kegiatan.php?id_konten=<?= $k['id_konten'] ?>"><?= htmlspecialchars($k['judul']) ?></a></h3>
                            <p class="meta">Organisasi: <?= htmlspecialchars($k['nama_organisasi']) ?> | 🗓️ <?= $k['tanggal_kegiatan'] ?></p>
                            <p><?= nl2br(htmlspecialchars(substr($k['deskripsi'], 0, 150))) ?>...</p>
                            
                            <div class="card-actions">
                                <div class="interaction-group">
                                    <div class="interaction">
                                        <button class="heart-btn" data-id="<?= $k['id_konten'] ?>">
                                            <i class="far fa-heart heart-icon"></i>
                                            <span class="like-count"><?= $k['total_likes'] ?></span>
                                        </button>
                                    </div>
                                    <div class="interaction">
                                        <a href="detail_kegiatan.php?id_konten=<?= $k['id_konten'] ?>#komentar" class="komentar-link">
                                            <i class="far fa-comment"></i>
                                            <span class="komentar-count"><?= $k['total_komentar'] ?></span>
                                        </a>
                                    </div>
                                    <div class="interaction">
                                        <button class="share-link" data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/MASAGENA-ITH/dashboard/mahasiswa/detail_kegiatan.php?id_konten=' . $k['id_konten'] ?>">
                                            <i class="fas fa-share-alt"></i> Share
                                        </button>
                                    </div>
                                </div>
                                <a href="detail_kegiatan.php?id_konten=<?= $k['id_konten'] ?>" class="btn-sm">Lihat Detail</a>
                            </div>
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
                        </div>
                        <a href="detail_kegiatan.php?id=<?= $k['id_konten'] ?>" class="btn-detail">Lihat Detail</a>
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
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">‹</a>
                <?php else: ?>
                    <span class="disabled">‹</span>
                <?php endif; ?>
=======
        <aside class="sidebar">
            <h2>Pengumuman</h2>
            <?php if (count($pengumuman) > 0): ?>
                <ul class="pengumuman-list">
                    <?php foreach ($pengumuman as $p): ?>
                        <li>
                            <a href="detail_kegiatan.php?id_konten=<?= $p['id_konten'] ?>"><?= htmlspecialchars($p['judul']) ?></a>
                            <span class="date"><?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Tidak ada pengumuman.</p>
            <?php endif; ?>
        </aside>
    </div>
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c

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
        <div class="text-center py-5">
            <i class="fa-regular fa-circle-xmark fa-3x text-muted"></i>
            <h4 class="mt-3">Belum ada kegiatan</h4>
            <p class="text-muted">Saat ini belum ada kegiatan yang tersedia.</p>
        </div>
    <?php endif; ?>

    <!-- PENGUMUMAN -->
    <?php if (!$isFilterActive && count($pengumuman) > 0): ?>
        <div class="mt-5">
            <h3 class="border-bottom pb-2" style="border-bottom-color: #FFA007 !important;">
                <i class="fa-regular fa-bullhorn me-2 text-warning"></i> Pengumuman
            </h3>
            <div class="row mt-3">
                <?php foreach ($pengumuman as $p): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($p['judul']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($p['deskripsi'], 0, 100)) ?>...</p>
                                <a href="detail_kegiatan.php?id=<?= $p['id_konten'] ?>" class="btn btn-sm btn-outline-primary">Baca</a>
                            </div>
                            <div class="card-footer text-muted small"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
// ===== LIKE AJAX =====
document.querySelectorAll('.btn-like').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        const icon = this.querySelector('i');
        const countSpan = this.querySelector('.like-count');
        const kegiatanId = this.dataset.id;
        icon.classList.add('heart-animate');
        setTimeout(() => icon.classList.remove('heart-animate'), 300);
        try {
            const response = await fetch('/MASAGENA-ITH/ajax/like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + kegiatanId
            });
<<<<<<< HEAD
            const data = await response.json();
            if (data.status === 'liked') {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#ff4757';
                this.classList.add('liked');
            } else if (data.status === 'unliked') {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
                this.classList.remove('liked');
            }
            countSpan.textContent = data.likes;
        } catch (err) {
            console.error(err);
        }
    });
});
=======
        });

        // Share button (copy link atau Web Share)
        document.querySelectorAll('.share-link').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;
                if (navigator.share) {
                    navigator.share({
                        title: '<?= addslashes($k['judul'] ?? 'Kegiatan') ?>',
                        url: url
                    }).catch(() => {});
                } else {
                    navigator.clipboard.writeText(url);
                    alert('Link kegiatan disalin ke clipboard!');
                }
            });
        });

        // Fitur Tab Foto agar Menampilkan Ukuran Penuh (Full Screen)
        document.querySelectorAll('.card img').forEach(foto => {
            foto.style.cursor = 'pointer'; // Ubah kursor jadi bentuk jari tangan
            foto.addEventListener('click', function() {
                const srcFoto = this.getAttribute('src');
                if(srcFoto) {
                    document.getElementById('imgFull').setAttribute('src', srcFoto);
                    document.getElementById('popupFoto').style.display = 'flex';
                }
            });
        });
    </script> 
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c

// ===== SHARE =====
document.querySelectorAll('.btn-share').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.dataset.url;
        if (navigator.share) {
            navigator.share({ title: 'Kegiatan', url: url }).catch(() => {});
        } else {
            navigator.clipboard.writeText(url);
            alert('Link kegiatan disalin ke clipboard!');
        }
    });
});

// ===== RESET FILTER =====
function resetFilter() {
    window.location.href = '/MASAGENA-ITH/dashboard/mahasiswa/index.php';
}
</script>

<?php include '../../include/footer.php'; ?>