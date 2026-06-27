<?php
// dashboard/mahasiswa/detail_kegiatan.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_konten <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data kegiatan
$sql = "SELECT k.*, o.nama_organisasi 
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE k.id_konten = ? AND k.status_publikasi = 'publik'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kegiatan) {
    header('Location: index.php');
    exit;
}

// ===== STATUS PENDAFTARAN MAHASISWA =====
$sudahDaftar = false;
$statusPendaftaran = '';
$check = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_mahasiswa = ? AND id_konten = ?");
$check->execute([$_SESSION['user_id'], $id_konten]);
$row = $check->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $sudahDaftar = true;
    $statusPendaftaran = $row['status_pendaftaran'];
}

// ===== LIKES =====
$likeCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_konten = ?");
$likeCount->execute([$id_konten]);
$totalLikes = $likeCount->fetchColumn();

$isLiked = false;
$likeCheck = $pdo->prepare("SELECT 1 FROM likes WHERE id_mahasiswa = ? AND id_konten = ?");
$likeCheck->execute([$_SESSION['user_id'], $id_konten]);
$isLiked = $likeCheck->fetchColumn() > 0;

// ===== KOMENTAR =====
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

// ===== POST KOMENTAR =====
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
            $komentar_message = '<div class="alert alert-success">Komentar berhasil dikirim.</div>';
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
.detail-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}
.detail-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.detail-card .judul {
    font-size: 2rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.5rem;
}
.detail-card .meta {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}
.detail-card .meta i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.detail-card .deskripsi {
    font-size: 1.05rem;
    line-height: 1.7;
    margin: 1.5rem 0;
    color: #1e293b;
}
.detail-card .gambar {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: 12px;
    margin: 1rem 0;
}
.detail-card .status-pendaftaran {
    background: #f8fafc;
    padding: 0.8rem 1.2rem;
    border-radius: 12px;
    margin: 1rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.detail-card .status-pendaftaran .label {
    font-weight: 600;
    color: #071C34;
}
.detail-card .status-pendaftaran .badge-status {
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
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
.detail-card .interaksi {
    display: flex;
    gap: 1.5rem;
    margin: 1.5rem 0;
    padding: 1rem 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
}
.detail-card .interaksi .btn-interaksi {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
    padding: 0.3rem 0.8rem;
    border-radius: 30px;
}
.detail-card .interaksi .btn-interaksi:hover {
    background: #f1f5f9;
}
.detail-card .interaksi .btn-like.liked {
    color: #ff4757;
}
.detail-card .interaksi .btn-like.liked i {
    font-weight: 900;
}
.komentar-section {
    margin-top: 2rem;
}
.komentar-section h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #071C34;
    border-bottom: 2px solid #FFA007;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}
.form-komentar textarea {
    width: 100%;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    padding: 0.8rem 1rem;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
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
    padding: 0.4rem 1.8rem;
    font-weight: 700;
    margin-top: 0.5rem;
    transition: 0.3s;
}
.form-komentar .btn-kirim:hover {
    background: #071C34;
    color: #fff;
}
.komentar-item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    margin-bottom: 1rem;
}
.komentar-item .nama {
    font-weight: 700;
    color: #071C34;
}
.komentar-item .waktu {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-left: 0.5rem;
}
.komentar-item .isi {
    margin: 0.4rem 0 0 0;
    color: #1e293b;
}
.komentar-item .balas {
    background: none;
    border: none;
    color: #FFA007;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    transition: 0.2s;
}
.komentar-item .balas:hover {
    background: rgba(255,160,7,0.1);
}
.balasan-item {
    margin-left: 2.5rem;
    padding-left: 1rem;
    border-left: 3px solid #FFA007;
}
.form-balasan {
    margin-top: 0.5rem;
    display: none;
}
.form-balasan textarea {
    width: 100%;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    padding: 0.5rem 0.8rem;
    font-size: 0.9rem;
    resize: vertical;
    min-height: 50px;
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
    padding: 0.2rem 1.2rem;
    font-weight: 700;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    transition: 0.3s;
}
.form-balasan .btn-kirim-balasan:hover {
    background: #071C34;
    color: #fff;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #071C34;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 1rem;
    padding: 0.3rem 1rem;
    border-radius: 50px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: 0.2s;
}
.btn-back:hover {
    background: #FFA007;
    color: #071C34;
    border-color: #FFA007;
}
</style>

<div class="detail-container">
    <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>

    <div class="detail-card">
        <!-- Gambar -->
        <?php 
        $imagePath = '';
        if (!empty($kegiatan['lampiran']) && file_exists('../../uploads/kegiatan/' . $kegiatan['lampiran'])) {
            $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $kegiatan['lampiran'];
        } else {
            $exts = ['jpg', 'jpeg', 'png', 'gif'];
            foreach ($exts as $ext) {
                $base = basename($kegiatan['lampiran'], '.' . pathinfo($kegiatan['lampiran'], PATHINFO_EXTENSION));
                if (file_exists('../../uploads/kegiatan/' . $base . '.' . $ext)) {
                    $imagePath = '/MASAGENA-ITH/uploads/kegiatan/' . $base . '.' . $ext;
                    break;
                }
            }
        }
        ?>
        <?php if (!empty($imagePath)): ?>
            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($kegiatan['judul']) ?>" class="gambar">
        <?php endif; ?>

        <h1 class="judul"><?= htmlspecialchars($kegiatan['judul']) ?></h1>
        <div class="meta">
            <i class="fa-regular fa-building"></i> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
            <?php if (!empty($kegiatan['kategori'])): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <span class="badge" style="background:#FFA007; color:#071C34; padding:0.2rem 0.6rem; border-radius:50px; font-size:0.7rem;"><?= htmlspecialchars($kegiatan['kategori']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Status Pendaftaran -->
        <div class="status-pendaftaran">
            <span class="label"><i class="fas fa-info-circle"></i> Status Pendaftaran Anda</span>
            <?php if ($sudahDaftar): ?>
                <span class="badge-status <?= $statusPendaftaran ?>">
                    <?= ucfirst($statusPendaftaran) ?>
                </span>
            <?php else: ?>
                <span class="badge-status belum">Belum Terdaftar</span>
            <?php endif; ?>
        </div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>

        <!-- Interaksi -->
        <div class="interaksi">
            <button class="btn-interaksi btn-like <?= $isLiked ? 'liked' : '' ?>" data-id="<?= $id_konten ?>">
                <i class="<?= $isLiked ? 'fas' : 'far' ?> fa-heart"></i>
                <span class="like-count"><?= $totalLikes ?></span>
            </button>
            <button class="btn-interaksi" onclick="document.getElementById('formKomentar').scrollIntoView({behavior:'smooth'});">
                <i class="far fa-comment"></i> Komentar
            </button>
            <button class="btn-interaksi btn-share" data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/MASAGENA-ITH/dashboard/mahasiswa/detail_kegiatan.php?id=' . $id_konten ?>">
                <i class="fas fa-share-alt"></i> Share
            </button>
        </div>

        <!-- Komentar -->
        <div class="komentar-section" id="komentar">
            <h3>Komentar (<?= count($komentar) ?>)</h3>

            <?= $komentar_message ?>

            <div class="form-komentar" id="formKomentar">
                <form method="post">
                    <textarea name="isi" placeholder="Tulis komentar..." required></textarea>
                    <button type="submit" name="komentar" class="btn-kirim">Kirim Komentar</button>
                </form>
            </div>

            <?php if (count($komentar) > 0): ?>
                <?php foreach ($komentar as $komen): ?>
                    <div class="komentar-item" id="kom-<?= $komen['id_komentar'] ?>">
                        <div>
                            <span class="nama"><?= htmlspecialchars($komen['nama'] ?? 'Anonim') ?></span>
                            <span class="waktu"><?= date('d M Y H:i', strtotime($komen['created_at'])) ?></span>
                        </div>
                        <div class="isi"><?= nl2br(htmlspecialchars($komen['isi_komentar'])) ?></div>
                        <button class="balas" onclick="toggleBalas(<?= $komen['id_komentar'] ?>)">Balas</button>

                        <div class="form-balasan" id="balas-<?= $komen['id_komentar'] ?>">
                            <form method="post">
                                <input type="hidden" name="parent" value="<?= $komen['id_komentar'] ?>">
                                <textarea name="isi" placeholder="Tulis balasan..." required></textarea>
                                <button type="submit" name="komentar" class="btn-kirim-balasan">Kirim Balasan</button>
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
                <p class="text-muted">Belum ada komentar. Jadilah yang pertama!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// LIKE AJAX
document.querySelector('.btn-like').addEventListener('click', async function(e) {
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
document.querySelector('.btn-share').addEventListener('click', function(e) {
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