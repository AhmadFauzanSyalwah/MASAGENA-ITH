<?php
// dashboard/pengurus/edit_konten.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_konten) {
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// AMBIL DATA KEGIATAN
// ============================================================
$stmt = $pdo->prepare("SELECT k.*, o.id_organisasi, o.nama_organisasi 
                       FROM konten_kegiatan k
                       JOIN organisasi o ON k.id_organisasi = o.id_organisasi
                       WHERE k.id_konten = ?");
$stmt->execute([$id_konten]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    $_SESSION['error'] = 'Kegiatan tidak ditemukan.';
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// CEK AKSES
// ============================================================
$id_pengurus = $_SESSION['user_id'];
$stmtCek = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmtCek->execute([$id_pengurus]);
$org_pengurus = $stmtCek->fetchColumn();

if ($org_pengurus != $kegiatan['id_organisasi']) {
    $_SESSION['error'] = 'Anda tidak memiliki akses untuk mengedit kegiatan ini.';
    header('Location: kelola_konten.php');
    exit;
}

// ============================================================
// PROSES UPDATE
// ============================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'] ?? '';
    $kategori = trim($_POST['kategori'] ?? '');
    $kuota = (int)($_POST['kuota'] ?? 0);
    $status_publikasi = $_POST['status_publikasi'] ?? 'publik';
    $hapus_lampiran = isset($_POST['hapus_lampiran']) ? true : false;

    if (empty($judul) || empty($deskripsi) || empty($tanggal_kegiatan)) {
        $error = 'Judul, deskripsi, dan tanggal kegiatan wajib diisi.';
    } elseif ($kuota < 0) {
        $error = 'Kuota tidak boleh negatif.';
    } else {
        $lampiran_baru = null;
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
                    $lampiran_baru = $nama_unik;
                } else {
                    $upload_error = 'Gagal mengupload file.';
                }
            }
        }

        if (empty($upload_error)) {
            $update_fields = [
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'tanggal_kegiatan' => $tanggal_kegiatan,
                'kategori' => $kategori,
                'kuota_maks' => $kuota,
                'status_publikasi' => $status_publikasi
            ];

            if ($hapus_lampiran && $kegiatan['lampiran']) {
                $old_file = '../../uploads/kegiatan/' . $kegiatan['lampiran'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
                $update_fields['lampiran'] = null;
            }

            if ($lampiran_baru) {
                if ($kegiatan['lampiran']) {
                    $old_file = '../../uploads/kegiatan/' . $kegiatan['lampiran'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $update_fields['lampiran'] = $lampiran_baru;
            }

            $set = [];
            $params = [];
            foreach ($update_fields as $key => $val) {
                $set[] = "$key = ?";
                $params[] = $val;
            }
            $params[] = $id_konten;

            $sql = "UPDATE konten_kegiatan SET " . implode(', ', $set) . " WHERE id_konten = ?";
            try {
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    // Redirect dengan parameter sukses (tanpa session)
                    header('Location: kelola_konten.php?update=sukses');
                    exit;
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = $upload_error;
        }
    }
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

$page_context = 'kelola_kegiatan';
include '../../include/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.edit-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}
.edit-container .alert-danger {
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    background: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #991b1b;
}
.edit-form .form-group {
    margin-bottom: 1.2rem;
}
.edit-form .form-group label {
    font-weight: 600;
    display: block;
    margin-bottom: 0.3rem;
    color: #071C34;
    font-size: 0.9rem;
}
.edit-form .form-group .form-control {
    width: 100%;
    padding: 0.6rem 0.9rem;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #ffffff;
    transition: 0.3s;
    font-family: inherit;
}
.edit-form .form-group .form-control:focus {
    border-color: #FFA007;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,160,7,0.15);
}
.edit-form .form-group textarea.form-control {
    resize: vertical;
    min-height: 120px;
}
.edit-form .form-group .form-text {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.2rem;
}
.current-file-wrapper {
    background: #f8fafc;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.current-file {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}
.current-file i {
    color: #3b82f6;
    font-size: 1.2rem;
}
.hapus-check {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.85rem;
    color: #dc2626;
    cursor: pointer;
}
.hapus-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
.no-lampiran {
    color: #94a3b8;
    font-style: italic;
}
.edit-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
    flex-wrap: wrap;
}
.edit-actions .btn-cancel {
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
.edit-actions .btn-cancel:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}
.edit-actions .btn-save {
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
.edit-actions .btn-save:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}

@media (max-width: 576px) {
    .edit-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .edit-actions .btn-cancel,
    .edit-actions .btn-save {
        width: 100%;
        justify-content: center;
    }
    .current-file-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="edit-container">
    <div class="profil-outer-card">
        <div class="profil-inner-card">
            <h4 class="profil-title"><i class="fas fa-edit"></i> Edit Kegiatan</h4>
            <p class="subtitle" style="color:#64748b; margin-bottom:1.5rem;">
                Perbarui informasi kegiatan yang dikelola oleh <strong><?= htmlspecialchars($kegiatan['nama_organisasi']) ?></strong>
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="edit-form" enctype="multipart/form-data" id="formEdit">

                <div class="form-group">
                    <label for="judul">Judul Kegiatan <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="judul" id="judul" class="form-control" required
                           value="<?= htmlspecialchars($kegiatan['judul']) ?>">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span style="color:#dc2626;">*</span></label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" required><?= htmlspecialchars($kegiatan['deskripsi']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggal_kegiatan">Tanggal Kegiatan <span style="color:#dc2626;">*</span></label>
                    <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" class="form-control" required
                           value="<?= htmlspecialchars($kegiatan['tanggal_kegiatan']) ?>">
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" name="kategori" id="kategori" class="form-control"
                           value="<?= htmlspecialchars($kegiatan['kategori']) ?>">
                    <small class="form-text">Misal: Workshop, Seminar, Kompetisi, Pameran, dll.</small>
                </div>

                <div class="form-group">
                    <label for="kuota">Kuota Peserta</label>
                    <input type="number" name="kuota" id="kuota" class="form-control" min="0"
                           value="<?= htmlspecialchars($kegiatan['kuota_maks'] ?? 50) ?>">
                    <small class="form-text">Jumlah maksimal peserta yang dapat mendaftar.</small>
                </div>

                <div class="form-group">
                    <label for="status_publikasi">Status Publikasi</label>
                    <select name="status_publikasi" id="status_publikasi" class="form-control">
                        <option value="publik" <?= $kegiatan['status_publikasi'] == 'publik' ? 'selected' : '' ?>>Publik</option>
                        <option value="draft" <?= $kegiatan['status_publikasi'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                    <small class="form-text">Publik akan terlihat oleh mahasiswa, Draft hanya tersimpan.</small>
                </div>

                <div class="form-group">
                    <label>Lampiran Saat Ini</label>
                    <div class="current-file-wrapper">
                        <?php if (!empty($kegiatan['lampiran'])): ?>
                            <span class="current-file">
                                <i class="fas fa-file"></i> <?= htmlspecialchars($kegiatan['lampiran']) ?>
                            </span>
                            <label class="hapus-check">
                                <input type="checkbox" name="hapus_lampiran" value="1">
                                <i class="fas fa-times-circle"></i> Hapus lampiran
                            </label>
                        <?php else: ?>
                            <span class="no-lampiran">Tidak ada lampiran</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lampiran">Ganti / Tambah Lampiran (opsional)</label>
                    <input type="file" name="lampiran" id="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <small class="form-text">Format yang diizinkan: JPG, PNG, GIF, PDF. Maksimal 2MB.</small>
                </div>

                <div class="edit-actions">
                    <a href="kelola_konten.php" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" name="update" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ===== SCRIPT KONFIRMASI SEBELUM SIMPAN ===== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEdit');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Cek apakah tombol yang ditekan adalah "Simpan Perubahan" (bukan tombol lain)
            const submitter = e.submitter;
            if (submitter && submitter.name === 'update') {
                if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>

<?php include '../../include/footer.php'; ?>