<?php
<<<<<<< HEAD
// dashboard/mahasiswa/form_pendaftaran_kegiatan.php
session_start();

// ===== CEK LOGIN & ROLE =====
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}
if ($_SESSION['peran'] != 'mahasiswa') {
    header('Location: ../../dashboard/' . $_SESSION['peran'] . '/index.php');
    exit;
=======
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../include/components.php';
require_once __DIR__ . '/../../include/pendaftaran-helper.php';
require_once '../../config/session_check.php';

$id_konten = isset($_GET['id_konten']) ? (int) $_GET['id_konten'] : 0;
// Pastikan fungsi ini menerima $pdo jika menggunakan koneksi database di dalamnya
$mahasiswa = pendaftaran_current_mahasiswa($pdo);

if (!$mahasiswa) {
    try {
        $stmtMhs = $pdo->query("
            SELECT id_mahasiswa, nim, nama, prodi, kontak, email 
            FROM tbmahasiswa 
            ORDER BY id_mahasiswa ASC 
            LIMIT 1
        ");
        $mahasiswa = $stmtMhs->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Query mahasiswa gagal: ' . $e->getMessage());
    }
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
}

require_once '../../config/database.php';
require_once '../../include/pendaftaran-helper.php';

<<<<<<< HEAD
$id_konten = isset($_GET['id_konten']) ? (int)$_GET['id_konten'] : 0;
if ($id_konten <= 0) {
    header('Location: kegiatan.php');
    exit;
}

// ============================================================
// QUERY KEGIATAN + KUOTA DARI KONTEN_KEGIATAN
// ============================================================
$sql = "SELECT k.*, o.nama_organisasi,
               COALESCE(k.kuota, 50) AS kuota,
               (SELECT COUNT(*) FROM pendaftaran WHERE id_konten = k.id_konten AND status_pendaftaran != 'ditolak') AS jumlah_peserta
        FROM konten_kegiatan k
        JOIN organisasi o ON k.id_organisasi = o.id_organisasi
        WHERE k.id_konten = :id AND k.status_publikasi = 'publik'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    header('Location: kegiatan.php');
    exit;
}

$kuota = (int)$kegiatan['kuota'];
$jumlah = (int)$kegiatan['jumlah_peserta'];
$penuh = $kuota > 0 && $jumlah >= $kuota;

// Cek apakah user sudah mendaftar
$sudahDaftar = false;
$statusPendaftaran = '';
$check = $pdo->prepare("SELECT status_pendaftaran FROM pendaftaran WHERE id_mahasiswa = ? AND id_konten = ?");
$check->execute([$_SESSION['user_id'], $id_konten]);
$row = $check->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $sudahDaftar = true;
    $statusPendaftaran = $row['status_pendaftaran'];
}

$message = '';
$error = '';

// Proses pendaftaran (hanya jika belum terdaftar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    if ($sudahDaftar) {
        $error = 'Anda sudah mendaftar untuk kegiatan ini. Status: <strong>' . ucfirst($statusPendaftaran) . '</strong>';
    } elseif ($penuh) {
        $error = 'Maaf, kuota kegiatan ini sudah penuh.';
    } else {
        $insert = $pdo->prepare("INSERT INTO pendaftaran (id_mahasiswa, id_konten, status_pendaftaran, kuota_maks) VALUES (?, ?, 'menunggu', ?)");
        if ($insert->execute([$_SESSION['user_id'], $id_konten, $kuota])) {
            $message = 'Pendaftaran berhasil! Status: <strong>Menunggu Konfirmasi</strong>.';
            $sudahDaftar = true;
            $statusPendaftaran = 'menunggu';
        } else {
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.daftar-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 0 1rem;
}
.daftar-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}
.daftar-card h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.5rem;
}
.daftar-card .meta {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}
.daftar-card .meta i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.daftar-card .deskripsi {
    font-size: 1rem;
    line-height: 1.6;
    margin: 1rem 0;
    color: #1e293b;
}
.daftar-card .info {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 12px;
    margin: 1rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.daftar-card .info .label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
}
.daftar-card .info .value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #071C34;
}
.daftar-card .info .value.penuh {
    color: #dc2626;
}
.daftar-card .info .value.terdaftar {
    color: #16a34a;
}
.btn-daftar {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.6rem 2rem;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
}
.btn-daftar:hover {
    background: #071C34;
    color: #fff;
}
.btn-daftar:disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
}
.btn-kembali {
    display: inline-block;
    background: #f1f5f9;
    color: #071C34;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 0.4rem 1.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
    margin-top: 0.5rem;
}
.btn-kembali:hover {
    background: #e2e8f0;
}
.alert {
    padding: 0.8rem 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}
.alert-success {
    background: #dcfce7;
    color: #16a34a;
    border-color: #bbf7d0;
}
.alert-danger {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fecaca;
}
.alert-warning {
    background: #fef9c3;
    color: #ca8a04;
    border-color: #fde68a;
}
.status-badge {
    display: inline-block;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-badge.menunggu {
    background: #fef9c3;
    color: #ca8a04;
}
.status-badge.diterima {
    background: #dcfce7;
    color: #16a34a;
}
.status-badge.ditolak {
    background: #fee2e2;
    color: #dc2626;
}
</style>

<div class="daftar-container">
    <div class="daftar-card">
        <h1><?= htmlspecialchars($kegiatan['judul']) ?></h1>
        <div class="meta">
            <i class="fa-regular fa-building"></i> <?= htmlspecialchars($kegiatan['nama_organisasi']) ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($kegiatan['tanggal_kegiatan'])) ?>
            <?php if (!empty($kegiatan['kategori'])): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <span class="badge" style="background:#FFA007; color:#071C34; padding:0.2rem 0.6rem; border-radius:50px; font-size:0.7rem;"><?= htmlspecialchars($kegiatan['kategori']) ?></span>
            <?php endif; ?>
        </div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?>
        </div>

        <div class="info">
            <span class="label">Kuota Peserta</span>
            <span class="value <?= $penuh ? 'penuh' : '' ?>">
                <?php if ($penuh): ?>
                    <i class="fas fa-exclamation-circle"></i> Penuh (<?= $jumlah ?>/<?= $kuota ?>)
                <?php else: ?>
                    <?= $jumlah ?>/<?= $kuota ?> tersisa
                <?php endif; ?>
            </span>
        </div>

        <?php if ($sudahDaftar): ?>
            <div class="info" style="background:#e8f0fe;">
                <span class="label">Status Pendaftaran</span>
                <span class="value terdaftar">
                    <span class="status-badge <?= $statusPendaftaran ?>"><?= ucfirst($statusPendaftaran) ?></span>
                </span>
=======
if ($id_konten > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT id_konten, judul, deskripsi, tanggal_kegiatan, kategori, lampiran
            FROM konten_kegiatan
            WHERE id_konten = ?
            AND status_publikasi = 'publish'
            LIMIT 1
        ");
        
        $stmt->execute([$id_konten]);
        $selected = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Prepare detail kegiatan gagal: ' . $e->getMessage());
    }
}

try {
    $kegiatanQuery = $pdo->query("
        SELECT
            k.id_konten,
            k.judul,
            k.tanggal_kegiatan,
            COALESCE(
                (
                    SELECT MAX(NULLIF(p2.kuota_maks, 0))
                    FROM pendaftaran p2
                    WHERE p2.id_konten = k.id_konten
                ),
                $defaultKuota
            ) AS kuota,
            (
                SELECT COUNT(*)
                FROM pendaftaran p
                WHERE p.id_konten = k.id_konten
                AND p.status_pendaftaran != 'ditolak'
            ) AS jumlah_peserta
        FROM konten_kegiatan k
        WHERE k.status_publikasi = 'publish'
        ORDER BY k.created_at DESC
    ");
} catch (PDOException $e) {
    die('Query kegiatan gagal: ' . $e->getMessage());
}

require_once __DIR__ . '/../../include/header.php';
?>

<!-- HEADER / WELCOME AREA -->
<div class="dashboard-welcome" style="margin-bottom: 2rem;">
    <h1>Pendaftaran Kegiatan</h1>
    <p>Silakan pilih kegiatan dan lengkapi formulir pendaftaran di bawah ini.</p>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; border: 1px solid var(--border); background-color: #ffffff;">
        
        <!-- Revisi Warna: Diubah ke tema terang (light) agar senada dengan detail kegiatan -->
        <aside style="flex: 1 1 350px; background-color: var(--bg-body, #f8f9fa); border-right: 1px solid var(--border); padding: 3rem 2.5rem; display: flex; flex-direction: column; justify-content: center;">
            <h2 style="font-size: 2.2rem; margin-bottom: 1rem; color: var(--primary);">Ayo Bergabung!</h2>
            
            <div style="margin: 1.5rem 0; width: 100%; height: 250px; border-radius: 12px; background: #e9ecef; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img src="/masagena-ith/assets/img/form_pendaftaran.png" alt="Form Pendaftaran" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display: none; font-weight: bold; color: #adb5bd; letter-spacing: 2px;">MASAGENA-ITH</span>
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
            </div>
        <?php endif; ?>

<<<<<<< HEAD
        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!$sudahDaftar): ?>
            <form method="post">
                <?php if ($penuh): ?>
                    <button class="btn-daftar" disabled>Kuota Penuh</button>
                <?php else: ?>
                    <button type="submit" name="daftar" class="btn-daftar">Daftar Sekarang</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding:0.5rem 0;">
                <p style="color:#16a34a; font-weight:600;">
                    <i class="fas fa-check-circle"></i> Anda sudah terdaftar
                </p>
            </div>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1rem;">
            <a href="kegiatan.php" class="btn-kembali"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Kegiatan</a>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>
=======
            <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.3; color: var(--text-dark);">
                <?= h($selected['judul'] ?? 'Pilih kegiatan yang ingin kamu ikuti'); ?>
            </h3>

            <p style="font-size: 1.05rem; line-height: 1.6; color: var(--text-muted); margin: 0;">
                <?= h($selected ? short_text($selected['deskripsi'], 130) : 'Pilih kegiatan pada formulir, lalu lengkapi dan kirim data pendaftaranmu.'); ?>
            </p>
        </aside>

        <section style="flex: 2 1 500px; padding: 3rem 2.5rem; background-color: #ffffff;">
            
            <?php if (isset($_GET['error'])) { ?>
                <div style="padding: 15px 20px; background-color: rgba(220, 53, 69, 0.1); color: var(--danger, #dc3545); border-radius: var(--radius); margin-bottom: 25px; font-weight: 700;">
                    <?= h($_GET['error']); ?>
                </div>
            <?php } ?>

            <form action="proses_pendaftaran_kegiatan.php" method="POST" style="width: 100%;">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Nama Mahasiswa</label>
                        <input type="text" value="<?= h($mahasiswa['nama'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">NIM</label>
                        <input type="text" name="nim" value="<?= h($mahasiswa['nim'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Program Studi</label>
                        <input type="text" value="<?= h($mahasiswa['prodi'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">No HP / Kontak</label>
                        <input type="text" value="<?= h($mahasiswa['kontak'] ?? '-'); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Email</label>
                        <input type="email" name="email" value="<?= h($mahasiswa['email'] ?? ''); ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-body); color: var(--text-dark); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Pilih Kegiatan</label>
                        <select name="id_konten" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--text-dark); font-family: inherit; font-size: 1rem; cursor: pointer;">
                            <option value="">-- Pilih Kegiatan --</option>
                            <?php while ($row = $kegiatanQuery->fetch(PDO::FETCH_ASSOC)) { ?>
                                <?php
                                    $kuota = (int) $row['kuota'];
                                    $jumlah = (int) $row['jumlah_peserta'];
                                    $penuh = $kuota > 0 && $jumlah >= $kuota;
                                    $isSelected = $id_konten === (int) $row['id_konten'];
                                ?>
                                <option value="<?= (int) $row['id_konten']; ?>" <?= $isSelected ? 'selected' : ''; ?> <?= $penuh ? 'disabled' : ''; ?>>
                                    <?= h($row['judul']); ?> — <?= $jumlah; ?>/<?= $kuota; ?> <?= $penuh ? '(Kuota Penuh)' : ''; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Catatan / Alasan Mengikuti</label>
                    <textarea name="catatan" rows="5" placeholder="Tuliskan alasan kamu mengikuti kegiatan ini..." required style="width: 100%; padding: 15px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--text-dark); font-family: inherit; font-size: 1rem; resize: vertical; min-height: 120px;"></textarea>
                </div>

                <!-- Tombol Kembali dibuat sama letak dan gayanya dengan yang di detail_kegiatan.php -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 1rem;">
                    <a href="javascript:history.back()" class="btn-cancel" style="padding: 12px 24px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                        &larr; Kembali
                    </a>
                    <button type="submit" class="btn" style="flex: 1; padding: 12px 24px; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 1px; border-radius: 8px;">
                        Submit Pendaftaran
                    </button>
                </div>

            </form>
        </section>

    </div>
</div>

<?php require_once __DIR__ . '/../../include/footer.php'; ?>
>>>>>>> 9e4b9b789696603edaa30fd5aeb277ddc8239c7c
