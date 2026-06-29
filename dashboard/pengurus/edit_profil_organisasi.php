<?php
// dashboard/pengurus/edit_profil_organisasi.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$id_user = $_SESSION['user_id'];
$level = $_SESSION['level'] ?? 'biasa';
$is_inti = ($level === 'Pengurus Inti' || $level === 'inti');

if (!$is_inti) {
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengedit profil organisasi.';
    header('Location: profil_organisasi.php');
    exit;
}

$stmtOrg = $pdo->prepare("SELECT id_organisasi FROM pengurus_organisasi WHERE id_pengurus = ?");
$stmtOrg->execute([$id_user]);
$pengurus = $stmtOrg->fetch(PDO::FETCH_ASSOC);

if (!$pengurus) {
    $_SESSION['error'] = 'Anda belum terdaftar di organisasi manapun.';
    header('Location: index.php');
    exit;
}

$id_organisasi = $pengurus['id_organisasi'];

$stmt = $pdo->prepare("SELECT * FROM organisasi WHERE id_organisasi = ?");
$stmt->execute([$id_organisasi]);
$organisasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$organisasi) {
    $_SESSION['error'] = 'Organisasi tidak ditemukan.';
    header('Location: index.php');
    exit;
}

$stmtPengurus = $pdo->prepare("SELECT * FROM pengurus_organisasi WHERE id_organisasi = ? ORDER BY 
    CASE 
        WHEN jabatan LIKE '%Ketua%' THEN 1
        WHEN jabatan LIKE '%Sekretaris%' THEN 2
        WHEN jabatan LIKE '%Bendahara%' THEN 3
        WHEN level = 'Pengurus Inti' THEN 4
        ELSE 5
    END, nama_pengurus ASC");
$stmtPengurus->execute([$id_organisasi]);
$semua_pengurus = $stmtPengurus->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $deskripsi = trim($_POST['deskripsi']);
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $pembina = trim($_POST['pembina'] ?? '');

    if (empty($deskripsi)) {
        $error = 'Deskripsi wajib diisi.';
    } else {
        $logo_path = $organisasi['logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (in_array($file['type'], $allowed)) {
                $target_dir = '../../uploads/logo/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'org_' . $id_organisasi . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
                    if (!empty($organisasi['logo']) && file_exists($target_dir . $organisasi['logo'])) {
                        unlink($target_dir . $organisasi['logo']);
                    }
                    $logo_path = $filename;
                } else {
                    $error = 'Gagal mengupload logo.';
                }
            } else {
                $error = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
            }
        }

        if (isset($_POST['hapus_logo']) && $_POST['hapus_logo'] == '1') {
            if (!empty($organisasi['logo']) && file_exists('../../uploads/logo/' . $organisasi['logo'])) {
                unlink('../../uploads/logo/' . $organisasi['logo']);
            }
            $logo_path = '';
        }

        if (empty($error)) {
            $update = $pdo->prepare("UPDATE organisasi SET 
                deskripsi = :deskripsi,
                visi = :visi,
                misi = :misi,
                pembina = :pembina,
                logo = :logo
                WHERE id_organisasi = :id");
            
            if ($update->execute([
                ':deskripsi' => $deskripsi,
                ':visi' => $visi,
                ':misi' => $misi,
                ':pembina' => $pembina,
                ':logo' => $logo_path,
                ':id' => $id_organisasi
            ])) {
                $_SESSION['success'] = 'Profil organisasi berhasil diperbarui!';
                header('Location: profil_organisasi.php');
                exit;
            } else {
                $error = 'Gagal memperbarui profil organisasi.';
            }
        }
    }
}

$page_context = 'profil_organisasi';

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil_organisasi.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php if ($error): ?>
    <div class="alert alert-danger" style="max-width:1200px; margin:0 auto 1rem auto; padding:0.8rem 1.5rem; border-radius:12px; background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<style>
/* ============================================
   EDIT PROFIL ORGANISASI - MINIMALIS IKON
   ============================================ */

.edit-form .form-control {
    width: 100%;
    padding: 0.6rem 0.9rem;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #ffffff;
    transition: 0.3s;
    font-family: inherit;
}
.edit-form .form-control:focus {
    border-color: #FFA007;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,160,7,0.15);
}
.edit-form .form-control[readonly] {
    background: #f8fafc;
    cursor: not-allowed;
}
.edit-form textarea.form-control {
    resize: vertical;
    min-height: 80px;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.edit-form textarea.form-control::-webkit-scrollbar {
    display: none;
}

.btn-remove-logo {
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 0.2rem 1rem;
    font-size: 0.65rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.btn-remove-logo:hover {
    background: #991b1b;
}

/* TOMBOL AKSI - TANPA IKON */
.profil-org-actions-edit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
    flex-wrap: wrap;
}

/* Tombol Batal - KUNING, HOVER BIRU */
.profil-org-actions-edit .btn-cancel {
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
}
.profil-org-actions-edit .btn-cancel:hover {
    background: #071C34;
    color: #ffffff;
    border-color: #071C34;
}

/* Tombol Simpan - BIRU, HOVER KUNING */
.profil-org-actions-edit .btn-save {
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
}
.profil-org-actions-edit .btn-save:hover {
    background: #FFA007;
    color: #ffffff;
    border-color: #FFA007;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 576px) {
    .profil-org-actions-edit {
        flex-direction: column;
        align-items: stretch;
    }
    .profil-org-actions-edit .btn-cancel,
    .profil-org-actions-edit .btn-save {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="profil-org-container">

<!-- HEADER -->
<div class="profil-org-header">
    <div class="title-group">
        <h2>
            <i class="fas fa-pen"></i> Edit Profil Organisasi
        </h2>
        <p class="subtitle">Perbarui informasi organisasi Anda</p>
    </div>
    <div class="header-badge">
        <span class="badge-mode edit"><i class="fas fa-edit"></i> Mode Edit</span>
    </div>
</div>

    <div class="profil-org-outer-card">
        <div class="profil-org-inner-card">

            <form method="post" class="edit-form" enctype="multipart/form-data">

                <!-- ===== TOP: FOTO + NAMA + DESKRIPSI ===== -->
                <div class="profil-org-top">

                    <!-- Avatar -->
                    <div class="profil-org-avatar">
                        <div class="avatar-wrapper">
                            <?php 
                            $logoPath = '';
                            if (!empty($organisasi['logo']) && file_exists('../../uploads/logo/' . $organisasi['logo'])) {
                                $logoPath = BASE_URL . '/uploads/logo/' . $organisasi['logo'];
                            }
                            ?>
                            <?php if ($logoPath): ?>
                                <img src="<?= $logoPath ?>" alt="Logo <?= htmlspecialchars($organisasi['nama_organisasi']) ?>" id="logoPreview">
                            <?php else: ?>
                                <span class="no-logo"><i class="fas fa-building"></i></span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">
                            <label for="logoInput" class="btn-upload" style="background:#071C34; color:#fff; border:none; border-radius:50px; padding:0.25rem 1.2rem; font-size:0.7rem; font-weight:600; cursor:pointer; transition:0.3s; display:inline-flex; align-items:center; gap:0.3rem;">
                                <i class="fas fa-camera"></i> Ganti Logo
                            </label>
                            <input type="file" name="logo" id="logoInput" accept="image/*" onchange="previewLogo(event)" style="display:none;">
                            <?php if ($logoPath): ?>
                                <button type="button" class="btn-remove-logo" onclick="hapusLogo()">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                                <input type="hidden" name="hapus_logo" id="hapus_logo" value="0">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Nama & Deskripsi (readonly untuk nama & jenis) -->
                    <div class="profil-org-info">
                        <div class="nama-org">
                            <?= htmlspecialchars($organisasi['nama_organisasi']) ?>
                            <span class="status-badge-org"><?= htmlspecialchars($organisasi['jenis']) ?></span>
                        </div>
                        <div class="deskripsi-org" style="margin-top:0.2rem;">
                            <label for="deskripsi" style="font-size:0.65rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:0.1rem;">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($organisasi['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- ===== INFO GRID (TANPA IKON) ===== -->
                <div class="profil-org-grid">
                    <div class="grid-item">
                        <label>ID Organisasi</label>
                        <div class="value"><?= $organisasi['id_organisasi'] ?></div>
                    </div>
                    <div class="grid-item">
                        <label>Tanggal Bergabung</label>
                        <div class="value"><?= date('d M Y', strtotime($organisasi['created_at'] ?? date('Y-m-d'))) ?></div>
                    </div>
                    <div class="grid-item">
                        <label>Dosen Pembina</label>
                        <input type="text" name="pembina" class="form-control" value="<?= htmlspecialchars($organisasi['pembina'] ?? '') ?>" placeholder="Nama Dosen Pembina">
                    </div>
                </div>

                <!-- ===== VISI & MISI (TANPA IKON) ===== -->
                <div class="profil-org-vm">
                    <div class="vm-item">
                        <label>Visi</label>
                        <textarea name="visi" class="form-control" rows="3"><?= htmlspecialchars($organisasi['visi'] ?? '') ?></textarea>
                    </div>
                    <div class="vm-item">
                        <label>Misi</label>
                        <textarea name="misi" class="form-control" rows="3"><?= htmlspecialchars($organisasi['misi'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- ===== STRUKTUR KEPENGURUSAN (TANPA IKON) ===== -->
                <div class="profil-org-struktur">
                    <div class="section-title"><i class="fas fa-users"></i> Struktur Kepengurusan</div>
                    <div class="sub-title">Daftar pengurus organisasi <?= htmlspecialchars($organisasi['nama_organisasi']) ?></div>

                    <?php if (count($semua_pengurus) > 0): ?>
                        <div class="pengurus-grid">
                            <?php foreach ($semua_pengurus as $p): ?>
                                <div class="pengurus-card">
                                    <div class="p-nama"><?= htmlspecialchars($p['nama_pengurus']) ?></div>
                                    <div class="p-jabatan"><?= htmlspecialchars($p['jabatan']) ?></div>
                                    <div style="margin-top:0.2rem; display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                        <span class="p-level <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'inti' : 'departemen' ?>">
                                            <?= ($p['level'] === 'Pengurus Inti' || $p['level'] === 'inti') ? 'Inti' : 'Departemen' ?>
                                        </span>
                                        <?php if (!empty($p['no_hp'])): ?>
                                            <span class="p-phone"><?= htmlspecialchars($p['no_hp']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color:#94a3b8; padding:0.5rem 0;">Belum ada pengurus terdaftar.</p>
                    <?php endif; ?>
                </div>

                <!-- ===== TOMBOL AKSI (TANPA IKON) ===== -->
                <div class="profil-org-actions-edit">
                    <a href="profil_organisasi.php" class="btn-cancel">Batal</a>
                    <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
                </div>

            </form>

        </div><!-- end inner -->
    </div><!-- end outer -->

</div>

<script>
function previewLogo(event) {
    const input = event.target;
    const reader = new FileReader();
    
    reader.onload = function() {
        const preview = document.getElementById('logoPreview');
        if (preview) {
            preview.src = reader.result;
        } else {
            const wrapper = document.querySelector('.avatar-wrapper');
            wrapper.innerHTML = `<img src="${reader.result}" alt="Logo Preview" id="logoPreview">`;
        }
    };
    
    if (input.files && input.files[0]) {
        reader.readAsDataURL(input.files[0]);
    }
}

function hapusLogo() {
    if (confirm('Apakah Anda yakin ingin menghapus logo ini?')) {
        document.getElementById('hapus_logo').value = '1';
        const preview = document.getElementById('logoPreview');
        if (preview) {
            const wrapper = preview.parentElement;
            wrapper.innerHTML = `<span class="no-logo"><i class="fas fa-building"></i></span>`;
        }
        const btnHapus = document.querySelector('.btn-remove-logo');
        if (btnHapus) btnHapus.style.display = 'none';
    }
}
</script>

<?php include '../../include/footer.php'; ?>