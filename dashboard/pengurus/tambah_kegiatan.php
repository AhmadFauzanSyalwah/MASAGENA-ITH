<?php
// dashboard/pengurus/tambah_kegiatan.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

// ============================================================
// AMBIL ID ORGANISASI DARI SESSION
// ============================================================
$id_pengurus = $_SESSION['user_id'];
$stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmtOrg->execute([$id_pengurus]);
$id_organisasi = $stmtOrg->fetchColumn();

if (!$id_organisasi) {
    $_SESSION['error'] = 'Anda belum terdaftar di organisasi mana pun.';
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// AMBIL NAMA ORGANISASI
// ============================================================
$stmtNama = $pdo->prepare("SELECT nama_organisasi FROM organisasi WHERE id_organisasi = ?");
$stmtNama->execute([$id_organisasi]);
$nama_organisasi = $stmtNama->fetchColumn();

// ============================================================
// PROSES TAMBAH
// ============================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'] ?? '';
    $kategori = trim($_POST['kategori'] ?? '');
    $kuota = (int)($_POST['kuota'] ?? 0);
    $status_publikasi = $_POST['status_publikasi'] ?? 'draft';

    if (empty($judul) || empty($deskripsi) || empty($tanggal_kegiatan)) {
        $error = 'Judul, deskripsi, dan tanggal kegiatan wajib diisi.';
    } elseif ($kuota < 0) {
        $error = 'Kuota tidak boleh negatif.';
    } else {
        $lampiran = null;
        $upload_error = '';

        if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $upload_dir = '../../uploads/kegiatan/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $nama_file = $_FILES['lampiran']['name'];
            $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $upload_error = 'Format file tidak diizinkan. Gunakan JPG, PNG, GIF, atau PDF.';
            } else {
                $nama_unik = uniqid() . '_' . basename($nama_file);
                $target = $upload_dir . $nama_unik;
                if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $target)) {
                    $lampiran = $nama_unik;
                } else {
                    $upload_error = 'Gagal mengupload file.';
                }
            }
        }

        if (empty($upload_error)) {
            // ===== PERBAIKAN: Tambahkan id_pembuat = id_pengurus =====
            $sql = "INSERT INTO konten_kegiatan 
                    (id_organisasi, id_pembuat, judul, deskripsi, tanggal_kegiatan, kategori, kuota_maks, status_publikasi, lampiran, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            try {
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$id_organisasi, $id_pengurus, $judul, $deskripsi, $tanggal_kegiatan, $kategori, $kuota, $status_publikasi, $lampiran])) {
                    header('Location: kelola_konten.php?tambah=sukses');
                    exit;
                } else {
                    $error = 'Gagal menambahkan kegiatan.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = $upload_error;
        }
    }
}

$page_context = 'kelola_kegiatan';
include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.tambah-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}
.tambah-container .alert-danger {
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    background: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #991b1b;
}
.tambah-form .form-group {
    margin-bottom: 1.2rem;
}
.tambah-form .form-group label {
    font-weight: 600;
    display: block;
    margin-bottom: 0.3rem;
    color: #071C34;
    font-size: 0.9rem;
}
.tambah-form .form-group .form-control {
    width: 100%;
    padding: 0.6rem 0.9rem;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #ffffff;
    transition: 0.3s;
    font-family: inherit;
}
.tambah-form .form-group .form-control:focus {
    border-color: #FFA007;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,160,7,0.15);
}
.tambah-form .form-group textarea.form-control {
    resize: vertical;
    min-height: 120px;
}
.tambah-form .form-group .form-text {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.2rem;
}
.tambah-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
    flex-wrap: wrap;
}
.tambah-actions .btn-cancel {
    background: #FFA007;
    color: #ffffff;
    border: 2px solid #FFA007;
    border-radius: 50px;
    padding: 0.5rem 2rem;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.tambah-actions .btn-cancel:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}
.tambah-actions .btn-save {
    background: #071C34;
    color: #ffffff;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.5rem 2.5rem;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.tambah-actions .btn-save:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}
@media (max-width: 576px) {
    .tambah-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .tambah-actions .btn-cancel,
    .tambah-actions .btn-save {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="tambah-container">
    <div class="profil-outer-card">
        <div class="profil-inner-card">
            <h4 class="profil-title"><i class="fas fa-plus-circle"></i> Tambah Kegiatan</h4>
            <p class="subtitle" style="color:#64748b; margin-bottom:1.5rem;">
                Buat kegiatan baru untuk <strong><?= htmlspecialchars($nama_organisasi) ?></strong>
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="tambah-form" enctype="multipart/form-data" id="formTambah">

                <div class="form-group">
                    <label for="judul">Judul Kegiatan <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="judul" id="judul" class="form-control" required
                           value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span style="color:#dc2626;">*</span></label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggal_kegiatan">Tanggal Kegiatan <span style="color:#dc2626;">*</span></label>
                    <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" class="form-control" required
                           value="<?= htmlspecialchars($_POST['tanggal_kegiatan'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" name="kategori" id="kategori" class="form-control"
                           value="<?= htmlspecialchars($_POST['kategori'] ?? '') ?>">
                    <small class="form-text">Misal: Workshop, Seminar, Kompetisi, Pameran, dll.</small>
                </div>

                <div class="form-group">
                    <label for="kuota">Kuota Peserta</label>
                    <input type="number" name="kuota" id="kuota" class="form-control" min="0"
                           value="<?= htmlspecialchars($_POST['kuota'] ?? 50) ?>">
                    <small class="form-text">Jumlah maksimal peserta yang dapat mendaftar (0 = tidak terbatas).</small>
                </div>

                <div class="form-group">
                    <label for="status_publikasi">Status Publikasi</label>
                    <select name="status_publikasi" id="status_publikasi" class="form-control">
                        <option value="draft" <?= ($_POST['status_publikasi'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="publik" <?= ($_POST['status_publikasi'] ?? '') == 'publik' ? 'selected' : '' ?>>Publik</option>
                    </select>
                    <small class="form-text">Publik akan langsung terlihat oleh mahasiswa, Draft hanya tersimpan.</small>
                </div>

                <div class="form-group">
                    <label for="lampiran">Upload Lampiran (opsional)</label>
                    <input type="file" name="lampiran" id="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <small class="form-text">Format yang diizinkan: JPG, PNG, GIF, PDF. Maksimal 2MB.</small>
                </div>

                <div class="tambah-actions">
                    <a href="kelola_konten.php" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" name="tambah" class="btn-save">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambah');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitter = e.submitter;
            if (submitter && submitter.name === 'tambah') {
                if (!confirm('Apakah Anda yakin ingin menambahkan kegiatan ini?')) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>

<?php include '../../include/footer.php'; ?>