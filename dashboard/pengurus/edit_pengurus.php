<?php
// dashboard/pengurus/edit_pengurus.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

// ============================================================
// CEK APAKAH PENGURUS INTI
// ============================================================
$level = $_SESSION['level'] ?? '';
$is_inti = ($level === 'Pengurus Inti' || $level === 'inti');

if (!$is_inti) {
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengelola pengurus.';
    header('Location: manajemen_pengurus.php');
    exit;
}

$id_organisasi = $_SESSION['id_organisasi'] ?? 0;

if (!$id_organisasi) {
    $_SESSION['error'] = 'Organisasi tidak ditemukan.';
    header('Location: manajemen_pengurus.php');
    exit;
}

// ============================================================
// AMBIL ID PENGURUS DARI GET
// ============================================================
$id_pengurus = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pengurus <= 0) {
    $_SESSION['error'] = 'ID pengurus tidak valid.';
    header('Location: manajemen_pengurus.php');
    exit;
}

// ============================================================
// AMBIL DATA PENGURUS
// ============================================================
$stmt = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE id_pengurus = ? AND id_organisasi = ?");
$stmt->execute([$id_pengurus, $id_organisasi]);
$pengurus = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengurus) {
    $_SESSION['error'] = 'Data pengurus tidak ditemukan.';
    header('Location: manajemen_pengurus.php');
    exit;
}

// ============================================================
// PROSES UPDATE (TANPA STATUS VERIFIKASI)
// ============================================================
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama = trim($_POST['nama_pengurus']);
    $jabatan = trim($_POST['jabatan']);
    $level = trim($_POST['level']);
    $no_hp = trim($_POST['no_hp']);

    if (empty($nama) || empty($jabatan) || empty($level)) {
        $error = 'Nama, jabatan, dan level wajib diisi.';
    } else {
        $update = $pdo->prepare("UPDATE pengurus_organisasi SET 
            nama_pengurus = :nama,
            jabatan = :jabatan,
            level = :level,
            no_hp = :no_hp
            WHERE id_pengurus = :id AND id_organisasi = :org");
        
        if ($update->execute([
            ':nama' => $nama,
            ':jabatan' => $jabatan,
            ':level' => $level,
            ':no_hp' => $no_hp,
            ':id' => $id_pengurus,
            ':org' => $id_organisasi
        ])) {
            $_SESSION['success'] = 'Data pengurus berhasil diperbarui!';
            header('Location: manajemen_pengurus.php');
            exit;
        } else {
            $error = 'Gagal memperbarui data pengurus.';
        }
    }
}

// ============================================================
// SET KONTEKS UNTUK HEADER
// ============================================================
$page_context = 'manajemen_pengurus';

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* ============================================
   EDIT PENGURUS - KONSISTEN DENGAN MANAJEMEN PENGURUS
   ============================================ */
.edit-pengurus-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Header */
.edit-pengurus-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.edit-pengurus-header .title-group h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #071C34;
    margin: 0;
}
.edit-pengurus-header .title-group .subtitle {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
}

/* ===== TOMBOL KEMBALI ===== */
.btn-back {
    background: transparent;
    color: #071C34;
    padding: 0.5rem 1.8rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 2px solid #071C34;
    transition: all 0.25s ease;
}
.btn-back:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* ===== CARD FORM ===== */
.edit-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.edit-card .form-group {
    margin-bottom: 1.2rem;
}
.edit-card .form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #071C34;
    margin-bottom: 0.3rem;
}
.edit-card .form-group label i {
    color: #FFA007;
    margin-right: 0.3rem;
}
.edit-card .form-group .form-control {
    width: 100%;
    padding: 0.6rem 0.9rem;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    background: #ffffff;
    transition: 0.3s;
    font-family: inherit;
}
.edit-card .form-group .form-control:focus {
    border-color: #FFA007;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,160,7,0.15);
}
.edit-card .form-group .form-control[readonly] {
    background: #f8fafc;
    cursor: not-allowed;
}
.edit-card .form-group select.form-control {
    appearance: auto;
}

/* ===== TOMBOL AKSI ===== */
.edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
}
.edit-actions .btn-save {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.5rem 2.5rem;
    font-weight: 700;
    font-size: 1rem;
    transition: 0.3s;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.edit-actions .btn-save:hover {
    background: #071C34;
    color: #fff;
}
.edit-actions .btn-cancel {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.5rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.edit-actions .btn-cancel:hover {
    background: #071C34;
    color: #fff;
}

/* ===== INFO STATUS ===== */
.info-status {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.8rem 1rem;
    border: 1px solid #e9ecef;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
}
.info-status .label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #071C34;
}
.info-status .value {
    font-size: 0.9rem;
    color: #64748b;
}
.info-status .badge {
    display: inline-block;
    padding: 0.15rem 0.8rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}
.info-status .badge.aktif {
    background: #dcfce7;
    color: #16a34a;
}
.info-status .badge.belum {
    background: #fee2e2;
    color: #dc2626;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .edit-pengurus-container {
        padding: 0 0.5rem;
    }
    .edit-pengurus-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .edit-pengurus-header .title-group h2 {
        font-size: 1.4rem;
    }
    .btn-back {
        justify-content: center;
    }
    .edit-card {
        padding: 1rem;
    }
    .edit-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .edit-actions .btn-save,
    .edit-actions .btn-cancel {
        justify-content: center;
        width: 100%;
    }
    .info-status {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
}
</style>

<div class="edit-pengurus-container">

    <!-- HEADER -->
    <div class="edit-pengurus-header">
        <div class="title-group">
            <h2><i class="fas fa-user-edit" style="color:#FFA007; margin-right:0.5rem;"></i> Edit Pengurus</h2>
            <p class="subtitle">Perbarui data pengurus organisasi Anda</p>
        </div>
        <a href="manajemen_pengurus.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="max-width:100%; margin:0 0 1rem 0; padding:0.8rem 1.5rem; border-radius:12px; background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="edit-card">
        <!-- Info Status Verifikasi (hanya tampilan, tidak bisa diedit) -->
        <div class="info-status">
            <span class="label"><i class="fas fa-check-circle" style="color:#FFA007;"></i> Status Verifikasi:</span>
            <span class="value">
                <span class="badge <?= $pengurus['status_verifikasi'] == 'Terverifikasi' ? 'aktif' : 'belum' ?>">
                    <?= $pengurus['status_verifikasi'] == 'Terverifikasi' ? 'Terverifikasi' : 'Belum Verifikasi' ?>
                </span>
            </span>
            <span style="font-size:0.75rem; color:#94a3b8; margin-left:auto;">
                <i class="fas fa-info-circle"></i> Hanya admin yang dapat mengubah status
            </span>
        </div>

        <form method="post">
            <div class="form-group">
                <label for="nama_pengurus"><i class="fas fa-user"></i> Nama Pengurus</label>
                <input type="text" name="nama_pengurus" id="nama_pengurus" class="form-control" 
                       value="<?= htmlspecialchars($pengurus['nama_pengurus']) ?>" required>
            </div>

            <div class="form-group">
                <label for="jabatan"><i class="fas fa-briefcase"></i> Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control" 
                       value="<?= htmlspecialchars($pengurus['jabatan']) ?>" required>
            </div>

            <div class="form-group">
                <label for="level"><i class="fas fa-layer-group"></i> Level</label>
                <select name="level" id="level" class="form-control" required>
                    <option value="Pengurus Inti" <?= $pengurus['level'] == 'Pengurus Inti' || $pengurus['level'] == 'inti' ? 'selected' : '' ?>>Pengurus Inti</option>
                    <option value="Departemen" <?= $pengurus['level'] == 'Departemen' ? 'selected' : '' ?>>Departemen</option>
                </select>
            </div>

            <div class="form-group">
                <label for="no_hp"><i class="fas fa-phone"></i> No HP</label>
                <input type="text" name="no_hp" id="no_hp" class="form-control" 
                       value="<?= htmlspecialchars($pengurus['no_hp']) ?>" placeholder="Contoh: 08123456789">
            </div>

            <div class="edit-actions">
                <a href="manajemen_pengurus.php" class="btn-cancel"><i class="fas fa-times"></i> Batal</a>
                <button type="submit" name="update" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>

</div>

<?php include '../../include/footer.php'; ?>