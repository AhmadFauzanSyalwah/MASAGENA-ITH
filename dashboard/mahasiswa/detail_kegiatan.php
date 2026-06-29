<?php
// dashboard/mahasiswa/detail_kegiatan.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_konten <= 0) {
    header('Location: kegiatan.php');
    exit;
}

$page_context = 'kegiatan';

// Ambil data kegiatan
$sql = "SELECT k.*, o.nama_organisasi, o.jenis
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE k.id_konten = ? AND k.status_publikasi = 'publik'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kegiatan) {
    header('Location: kegiatan.php');
    exit;
}

// Kuota & peserta
$kuota = (int)($kegiatan['kuota'] ?? 50);
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_konten = ? AND status_pendaftaran != 'ditolak'");
$stmtCount->execute([$id_konten]);
$jumlah_peserta = $stmtCount->fetchColumn();
$penuh = $kuota > 0 && $jumlah_peserta >= $kuota;

// Status pendaftaran mahasiswa
$sudahDaftar = false;
$statusPendaftaran = '';
$check = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_mahasiswa = ? AND id_konten = ?");
$check->execute([$_SESSION['user_id'], $id_konten]);
$row = $check->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $sudahDaftar = true;
    $statusPendaftaran = $row['status_pendaftaran'];
}

// Likes
$likeCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
$likeCount->execute([$id_konten]);
$totalLikes = $likeCount->fetchColumn();

$isLiked = false;
$likeCheck = $pdo->prepare("SELECT 1 FROM likes WHERE id_mahasiswa = ? AND id_konten = ?");
$likeCheck->execute([$_SESSION['user_id'], $id_konten]);
$isLiked = $likeCheck->fetchColumn() > 0;

// Komentar
$sqlKomentar = "SELECT k.*, u.nama 
                FROM komentar k
                LEFT JOIN tbmahasiswa u ON k.id_mahasiswa = u.id_mahasiswa
                WHERE k.id_konten = ? AND k.id_komentar_parent IS NULL
                ORDER BY k.created_at DESC";
$stmt = $pdo->prepare($sqlKomentar);
$stmt->execute([$id_konten]);
$komentar = $stmt->fetchAll();

$sqlBalasan = "SELECT k.*, u.nama 
               FROM komentar k
               LEFT JOIN tbmahasiswa u ON k.id_mahasiswa = u.id_mahasiswa
               WHERE k.id_konten = ? AND k.id_komentar_parent IS NOT NULL
               ORDER BY k.created_at ASC";
$stmtBalasan = $pdo->prepare($sqlBalasan);
$stmtBalasan->execute([$id_konten]);
$balasan = $stmtBalasan->fetchAll();

$balasanGroup = [];
foreach ($balasan as $b) {
    $balasanGroup[$b['id_komentar_parent']][] = $b;
}

// Post komentar
$komentar_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['komentar'])) {
    $isi = trim($_POST['isi']);
    $parent = isset($_POST['parent']) ? (int)$_POST['parent'] : null;
    if (empty($isi)) {
        $komentar_message = '<div class="alert alert-danger">Isi komentar tidak boleh kosong.</div>';
    } else {
        $insert = $pdo->prepare("INSERT INTO komentar (id_konten, id_mahasiswa, isi_komentar, id_komentar_parent, created_at) 
                                 VALUES (?, ?, ?, ?, NOW())");
        if ($insert->execute([$id_konten, $_SESSION['user_id'], $isi, $parent])) {
            header("Location: detail_kegiatan.php?id=$id_konten");
            exit;
        } else {
            $komentar_message = '<div class="alert alert-danger">Gagal mengirim komentar.</div>';
        }
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   DETAIL KEGIATAN - LAYOUT 2 KOLOM + KOMENTAR
   ============================================ */
.detail-container {
    max-width: 1200px;
    margin: 1.5rem auto;
    padding: 0 1rem;
}

/* GAMBAR FULL WIDTH */
.detail-gambar {
    width: 100%;
    max-height: 380px;
    object-fit: cover;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    background: #f1f5f9;
}
.detail-gambar .no-image {
    width: 100%;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #94a3b8;
    font-size: 3rem;
    border-radius: 16px;
}

/* ===== TWO COLUMN LAYOUT (Judul + Status) ===== */
.detail-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

/* KOLOM KIRI - INFO KEGIATAN */
.detail-left {
    display: flex;
    flex-direction: column;
}
.detail-left .judul {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
}
.detail-left .meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 1rem;
}
.detail-left .meta i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.detail-left .meta .badge-cat {
    background: #FFA007;
    color: #071C34;
    padding: 0.1rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}
.detail-left .meta .badge-jenis {
    background: #071C34;
    color: #fff;
    padding: 0.1rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}
.detail-left .deskripsi {
    font-size: 1rem;
    line-height: 1.7;
    color: #1e293b;
    margin-top: 0.5rem;
}

/* KOLOM KANAN - STATUS PENDAFTARAN */
.detail-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-card {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1.2rem 1.5rem;
    border: 1px solid #e9ecef;
}
.status-card .status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.status-card .status-header .label {
    font-weight: 600;
    color: #071C34;
    font-size: 0.9rem;
}
.status-card .status-header .label i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.status-card .status-header .badge-status {
    padding: 0.2rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.8rem;
}
.badge-status.menunggu {
    background: #fef9c3;
    color: #ca8a04;
}
.badge-status.diterima {
    background: #dcfce7;
    color: #16a34a;
}
.badge-status.ditolak {
    background: #fee2e2;
    color: #dc2626;
}
.badge-status.belum {
    background: #e2e8f0;
    color: #475569;
}

.status-card .kuota-info {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.8rem;
}
.status-card .kuota-info .penuh {
    color: #dc2626;
    font-weight: 600;
}

.status-card .btn-daftar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.35rem 1.5rem;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: 0.3s;
    width: 100%;
}
.status-card .btn-daftar:hover {
    background: #071C34;
    color: #fff;
}
.status-card .btn-daftar.disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
    pointer-events: none;
}
.status-card .btn-daftar.menunggu {
    background: #f59e0b;
    color: #fff;
}
.status-card .btn-daftar.diterima {
    background: #22c55e;
    color: #fff;
}
.status-card .btn-daftar.ditolak {
    background: #dc2626;
    color: #fff;
}

/* Interaksi (Like & Share) di kanan bawah */
.interaksi-card {
    display: flex;
    gap: 1rem;
    padding: 0.5rem 0;
}
.interaksi-card .btn-interaksi {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 0.3rem 1rem;
    font-size: 0.85rem;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.interaksi-card .btn-interaksi:hover {
    background: #f1f5f9;
}
.interaksi-card .btn-like.liked {
    color: #ff4757;
    border-color: #ff4757;
}
.interaksi-card .btn-like.liked i {
    font-weight: 900;
}

/* ===== KOMENTAR (Full Width) ===== */
.komentar-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #f1f5f9;
}
.komentar-section .komentar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #FFA007;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}
.komentar-section .komentar-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.komentar-section .komentar-header .count {
    font-size: 0.8rem;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 0.1rem 0.6rem;
    border-radius: 50px;
}

/* Form komentar */
.form-komentar {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.form-komentar textarea {
    flex: 1;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    padding: 0.6rem 0.9rem;
    font-family: inherit;
    resize: vertical;
    min-height: 50px;
    font-size: 0.9rem;
}
.form-komentar textarea:focus {
    border-color: #FFA007;
    outline: none;
}
.form-komentar .btn-kirim {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0 1.5rem;
    font-weight: 700;
    font-size: 0.85rem;
    transition: 0.3s;
    cursor: pointer;
    white-space: nowrap;
}
.form-komentar .btn-kirim:hover {
    background: #071C34;
    color: #fff;
}

/* Daftar komentar */
.komentar-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 0.3rem;
}
.komentar-list::-webkit-scrollbar {
    width: 4px;
}
.komentar-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.komentar-list::-webkit-scrollbar-thumb {
    background: #FFA007;
    border-radius: 10px;
}

.komentar-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.8rem 1rem;
    margin-bottom: 0.8rem;
}
.komentar-item .nama {
    font-weight: 700;
    color: #071C34;
    font-size: 0.85rem;
}
.komentar-item .waktu {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-left: 0.5rem;
}
.komentar-item .isi {
    margin: 0.2rem 0 0 0;
    font-size: 0.9rem;
    color: #1e293b;
    word-wrap: break-word;
}
.komentar-item .balas-btn {
    background: none;
    border: none;
    color: #FFA007;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.1rem 0.4rem;
    border-radius: 20px;
    transition: 0.2s;
}
.komentar-item .balas-btn:hover {
    background: rgba(255,160,7,0.1);
}

.balasan-item {
    margin-left: 1.5rem;
    padding-left: 0.8rem;
    border-left: 2px solid #FFA007;
    margin-top: 0.4rem;
}
.balasan-item .nama {
    font-weight: 600;
    font-size: 0.8rem;
}
.balasan-item .isi {
    font-size: 0.85rem;
}

.form-balasan {
    margin-top: 0.3rem;
    display: none;
}
.form-balasan textarea {
    width: 100%;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    padding: 0.4rem 0.7rem;
    font-size: 0.85rem;
    resize: vertical;
    min-height: 45px;
}
.form-balasan textarea:focus {
    border-color: #FFA007;
    outline: none;
}
.form-balasan .btn-kirim-balasan {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.15rem 1.2rem;
    font-weight: 700;
    font-size: 0.75rem;
    margin-top: 0.2rem;
    cursor: pointer;
    transition: 0.3s;
}
.form-balasan .btn-kirim-balasan:hover {
    background: #071C34;
    color: #fff;
}

/* Alert */
.alert {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}
.alert-danger {
    background: #fee2e2;
    color: #991b1b;
}
.alert-success {
    background: #dcfce7;
    color: #166534;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .detail-two-col {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .status-card .btn-daftar {
        width: auto;
    }
}
@media (max-width: 576px) {
    .detail-left .judul {
        font-size: 1.4rem;
    }
    .form-komentar {
        flex-direction: column;
    }
    .form-komentar .btn-kirim {
        padding: 0.4rem;
        width: 100%;
    }
    .status-card {
        padding: 0.8rem 1rem;
    }
}
</style>

<div class="detail-container">

    <!-- ===== GAMBAR (AMAN DARI NULL) ===== -->
    <?php
    $imagePath = '';
    if (!empty($kegiatan['lampiran'])) {
        // Cek file langsung
        $filePath = '../../uploads/kegiatan/' . $kegiatan['lampiran'];
        if (file_exists($filePath)) {
            $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $kegiatan['lampiran'];
        } else {
            // Coba ekstensi lain
            $exts = ['jpg', 'jpeg', 'png', 'gif'];
            $base = pathinfo($kegiatan['lampiran'], PATHINFO_FILENAME);
            foreach ($exts as $ext) {
                $altPath = '../../uploads/kegiatan/' . $base . '.' . $ext;
                if (file_exists($altPath)) {
                    $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $base . '.' . $ext;
                    break;
                }
            }
        }
    }
    ?>
    <?php if (!empty($imagePath)): ?>
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($kegiatan['judul']) ?>" class="detail-gambar">
    <?php else: ?>
        <div class="detail-gambar no-image"><i class="fa-regular fa-image"></i></div>
    <?php endif; ?>

    <!-- TWO COLUMN -->
    <div class="detail-two-col">

        <!-- KOLOM KIRI -->
        <div class="detail-left">
            <h1 class="judul"><?= htmlspecialchars($kegiatan['judul']) ?></h1>
            <div class="meta">
                <span><i class="fa-regular fa-building"></i> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?></span>
                <span><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?></span>
                <?php if (!empty($kegiatan['kategori'])): ?>
                    <span class="badge-cat"><?= htmlspecialchars($kegiatan['kategori']) ?></span>
                <?php endif; ?>
                <?php if (!empty($kegiatan['jenis'])): ?>
                    <span class="badge-jenis"><?= htmlspecialchars($kegiatan['jenis']) ?></span>
                <?php endif; ?>
            </div>
            <div class="deskripsi">
                <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="detail-right">

            <div class="status-card">
                <div class="status-header">
                    <span class="label"><i class="fas fa-info-circle"></i> Status Pendaftaran</span>
                    <?php if ($sudahDaftar): ?>
                        <span class="badge-status <?= $statusPendaftaran ?>"><?= ucfirst($statusPendaftaran) ?></span>
                    <?php else: ?>
                        <span class="badge-status belum">Belum Terdaftar</span>
                    <?php endif; ?>
                </div>

                <div class="kuota-info">
                    <i class="fas fa-users"></i> <?= $jumlah_peserta ?> / <?= $kuota ?>
                    <?php if ($penuh): ?><span class="penuh">(Penuh)</span><?php endif; ?>
                </div>

                <?php if (!$sudahDaftar && !$penuh): ?>
                    <a href="/MASAGENA-ITH/dashboard/mahasiswa/form_pendaftaran_kegiatan.php?id_konten=<?= $id_konten ?>" class="btn-daftar">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </a>
                <?php elseif ($sudahDaftar && $statusPendaftaran == 'menunggu'): ?>
                    <span class="btn-daftar disabled menunggu"><i class="fas fa-clock"></i> Menunggu Verifikasi</span>
                <?php elseif ($sudahDaftar && $statusPendaftaran == 'diterima'): ?>
                    <span class="btn-daftar disabled diterima"><i class="fas fa-check-circle"></i> Terdaftar</span>
                <?php elseif ($sudahDaftar && $statusPendaftaran == 'ditolak'): ?>
                    <span class="btn-daftar disabled ditolak"><i class="fas fa-times-circle"></i> Ditolak</span>
                <?php elseif ($penuh): ?>
                    <span class="btn-daftar disabled">Kuota Penuh</span>
                <?php endif; ?>
            </div>

            <!-- Interaksi -->
            <div class="interaksi-card">
                <button class="btn-interaksi btn-like <?= $isLiked ? 'liked' : '' ?>" data-id="<?= $id_konten ?>">
                    <i class="<?= $isLiked ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="like-count"><?= $totalLikes ?></span>
                </button>
                <button class="btn-interaksi btn-share" data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/MASAGENA-ITH/dashboard/mahasiswa/detail_kegiatan.php?id=' . $id_konten ?>">
                    <i class="fas fa-share-alt"></i> Share
                </button>
            </div>

        </div>

    </div>

    <!-- KOMENTAR -->
    <div class="komentar-section">

        <div class="komentar-header">
            <h3><i class="fa-regular fa-comment" style="color:#FFA007; margin-right:0.3rem;"></i> Komentar</h3>
            <span class="count"><?= count($komentar) ?></span>
        </div>

        <?= $komentar_message ?>

        <div class="form-komentar" id="formKomentar">
            <form method="post" style="display:flex; gap:0.5rem; width:100%;">
                <textarea name="isi" placeholder="Tulis komentar..." required></textarea>
                <button type="submit" name="komentar" class="btn-kirim">Kirim</button>
            </form>
        </div>

        <div class="komentar-list">
            <?php if (count($komentar) > 0): ?>
                <?php foreach ($komentar as $komen): ?>
                    <div class="komentar-item" id="kom-<?= $komen['id_komentar'] ?>">
                        <div>
                            <span class="nama"><?= htmlspecialchars($komen['nama'] ?? 'Anonim') ?></span>
                            <span class="waktu"><?= date('d M Y H:i', strtotime($komen['created_at'])) ?></span>
                        </div>
                        <div class="isi"><?= nl2br(htmlspecialchars($komen['isi_komentar'])) ?></div>
                        <button class="balas-btn" onclick="toggleBalas(<?= $komen['id_komentar'] ?>)">Balas</button>

                        <div class="form-balasan" id="balas-<?= $komen['id_komentar'] ?>">
                            <form method="post">
                                <input type="hidden" name="parent" value="<?= $komen['id_komentar'] ?>">
                                <textarea name="isi" placeholder="Tulis balasan..." required></textarea>
                                <button type="submit" name="komentar" class="btn-kirim-balasan">Kirim</button>
                            </form>
                        </div>

                        <?php if (isset($balasanGroup[$komen['id_komentar']])): ?>
                            <?php foreach ($balasanGroup[$komen['id_komentar']] as $balas): ?>
                                <div class="balasan-item">
                                    <div>
                                        <span class="nama"><?= htmlspecialchars($balas['nama'] ?? 'Anonim') ?></span>
                                        <span class="waktu"><?= date('d M Y H:i', strtotime($balas['created_at'])) ?></span>
                                    </div>
                                    <div class="isi"><?= nl2br(htmlspecialchars($balas['isi_komentar'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#94a3b8; text-align:center; padding:1rem 0;">Belum ada komentar. Jadilah yang pertama!</p>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
// LIKE AJAX
document.querySelector('.btn-like')?.addEventListener('click', async function(e) {
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

// SHARE
document.querySelector('.btn-share')?.addEventListener('click', function(e) {
    e.preventDefault();
    const url = this.dataset.url;
    if (navigator.share) {
        navigator.share({ title: 'Kegiatan', url: url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url);
        alert('Link kegiatan disalin ke clipboard!');
    }
});

// TOGGLE BALAS
function toggleBalas(id) {
    const form = document.getElementById('balas-' + id);
    if (form.style.display === 'block') {
        form.style.display = 'none';
    } else {
        form.style.display = 'block';
        form.querySelector('textarea').focus();
    }
}
</script>

<?php include '../../include/footer.php'; ?>