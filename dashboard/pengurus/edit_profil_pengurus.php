<?php
/**
 * dashboard/pengurus/edit_profil_pengurus.php
 * Halaman edit profil pengurus - Layout konsisten dengan edit_profil mahasiswa
 * Foto kiri, form kanan, tombol Batal & Simpan Perubahan
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/session_check.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'pengurus') {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// Ambil data pengurus
$query = "SELECT * FROM pengurus_organisasi WHERE id_pengurus = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Data pengurus tidak ditemukan.');
}

// Hapus session success
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Ambil error dari session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama = trim($_POST['nama_pengurus']);
    $no_hp = trim($_POST['no_hp'] ?? '');

    if (empty($nama)) {
        $error = 'Nama pengurus wajib diisi.';
    } else {
        // Update data tanpa email (karena kolom email mungkin tidak ada)
        $update = "UPDATE pengurus_organisasi SET nama_pengurus = :nama, no_hp = :no_hp WHERE id_pengurus = :id";
        $stmt = $pdo->prepare($update);
        if ($stmt->execute([':nama' => $nama, ':no_hp' => $no_hp, ':id' => $user_id])) {
            // Update session nama
            $_SESSION['nama'] = $nama;

            // Upload foto
            $upload_error = false;
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto_profil'];
                $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (in_array($file['type'], $allowed)) {
                    $target_dir = '../../uploads/profil/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'pengurus_' . $user_id . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
                        // Hapus file lama dengan ekstensi lain
                        foreach (['jpg', 'jpeg', 'png', 'gif'] as $old_ext) {
                            if ($old_ext !== $ext && file_exists($target_dir . 'pengurus_' . $user_id . '.' . $old_ext)) {
                                unlink($target_dir . 'pengurus_' . $user_id . '.' . $old_ext);
                            }
                        }
                    } else {
                        $upload_error = true;
                    }
                } else {
                    $upload_error = true;
                }
            }

            if ($upload_error) {
                $_SESSION['error'] = 'Data diri berhasil diperbarui, tetapi gagal mengunggah foto.';
                header('Location: edit_profil_pengurus.php');
                exit;
            } else {
                $_SESSION['success'] = 'Profil berhasil diperbarui!';
                header('Location: profil.php');
                exit;
            }
        } else {
            $error = 'Gagal memperbarui profil.';
        }
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profil.css?v=<?php echo time(); ?>">
<style>
:root {
    --primary: #0a2a4a;
    --accent: #FFA007;
    --border: #e9ecef;
    --bg-light: #f8fafc;
}

/* ===== STRUKTUR EDIT PROFIL ===== */
.profil-edit-header {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}

.edit-avatar {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.8rem;
}

.edit-avatar .foto-profil-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
    box-shadow: 0 4px 15px rgba(10, 42, 74, 0.12);
}

.edit-avatar .fas.fa-user-circle {
    font-size: 120px;
    color: var(--primary);
    opacity: 0.85;
}

/* ===== NORMALISASI TOMBOL ===== */
.btn-upload, .btn-batal, .btn-simpan {
    padding: 0.5rem 1.8rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.25s ease;
    text-decoration: none;
}

.btn-upload:hover, .btn-batal:hover, .btn-simpan:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.btn-upload { background: var(--primary); color: #ffffff; padding: 0.5rem 1rem; font-size: 0.75rem; }
.btn-upload:hover { background: var(--accent); color: var(--primary); }

.btn-batal { background: var(--accent); color: var(--primary); }
.btn-batal:hover { background: var(--primary); color: #ffffff; }

.btn-simpan { background: var(--primary); color: #ffffff; }
.btn-simpan:hover { background: var(--accent); color: var(--primary); }

/* ===== INPUT FORM ===== */
.profil-edit-form { flex: 1; min-width: 250px; }
.form-group { margin-bottom: 1rem; }
.form-group label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    margin-bottom: 0.3rem;
}
.form-control {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
}
.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 42, 74, 0.1);
}

.edit-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.2rem;
    border-top: 2px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 0.8rem;
}

#foto_profil { display: none; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .profil-edit-header { flex-direction: column; align-items: center; }
    .btn-upload, .btn-batal, .btn-simpan { width: 100%; }
    .edit-actions { flex-direction: column-reverse; }
}
</style>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <h4 class="profil-title">Edit Profile</h4>

            <div class="profil-outer-card">
                <div class="profil-inner-card">

                    <?php if (!empty($error)): ?>
                        <script>
                            alert('<?php echo addslashes($error); ?>');
                        </script>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" id="editForm">

                        <div class="profil-edit-header">

                            <!-- KIRI: Foto -->
                            <div class="edit-avatar">
                                <?php 
                                $fotoPath = '';
                                // Cek foto di beberapa lokasi
                                if (!empty($user['foto_profil']) && file_exists('../../' . $user['foto_profil'])) {
                                    $fotoPath = BASE_URL . '/' . $user['foto_profil'];
                                } elseif (file_exists('../../uploads/profil/pengurus_' . $user['id_pengurus'] . '.jpg')) {
                                    $fotoPath = BASE_URL . '/uploads/profil/pengurus_' . $user['id_pengurus'] . '.jpg';
                                } elseif (file_exists('../../uploads/profil/pengurus_' . $user['id_pengurus'] . '.png')) {
                                    $fotoPath = BASE_URL . '/uploads/profil/pengurus_' . $user['id_pengurus'] . '.png';
                                }
                                ?>
                                <?php if ($fotoPath): ?>
                                    <img src="<?php echo $fotoPath; ?>" alt="Foto Profil" class="foto-profil-img" id="fotoPreview">
                                <?php else: ?>
                                    <i class="fas fa-user-circle" id="fotoPreviewIcon"></i>
                                <?php endif; ?>

                                <div class="edit-upload-wrapper">
                                    <label for="foto_profil" class="btn-upload">
                                        <i class="fas fa-camera"></i> Ganti Foto
                                    </label>
                                    <input type="file" name="foto_profil" id="foto_profil" accept="image/*" style="display:none;" onchange="previewImage(event)">
                                </div>
                            </div>

                            <!-- KANAN: Form -->
                            <div class="profil-edit-form">
                                <div class="form-group">
                                    <label for="nama_pengurus">Nama Lengkap</label>
                                    <input type="text" name="nama_pengurus" id="nama_pengurus" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['nama_pengurus']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="no_hp">No. HP</label>
                                    <input type="text" name="no_hp" id="no_hp" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Level</label>
                                    <input type="text" class="form-control" value="<?php echo $user['level'] == 'inti' ? 'Pengurus Inti' : 'Pengurus Biasa'; ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Organisasi</label>
                                    <?php 
                                    // Ambil nama organisasi
                                    $stmtOrg = $pdo->prepare("SELECT nama_organisasi FROM organisasi WHERE id_organisasi = ?");
                                    $stmtOrg->execute([$user['id_organisasi']]);
                                    $orgName = $stmtOrg->fetchColumn();
                                    ?>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($orgName ?? '-'); ?>" disabled>
                                </div>
                            </div>

                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="edit-actions">
                            <a href="<?php echo BASE_URL; ?>/dashboard/pengurus/profil.php" class="btn-batal">
                                <i class="fas fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" name="update" class="btn-simpan">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div><!-- end inner -->
            </div><!-- end outer -->

        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const reader = new FileReader();
    
    reader.onload = function() {
        const preview = document.getElementById('fotoPreview');
        const icon = document.getElementById('fotoPreviewIcon');
        
        if (preview) {
            preview.src = reader.result;
        } else if (icon) {
            const avatarDiv = document.querySelector('.edit-avatar');
            const uploadWrapper = document.querySelector('.edit-upload-wrapper');
            avatarDiv.innerHTML = `
                <img src="${reader.result}" alt="Foto Profil" class="foto-profil-img" id="fotoPreview">
                <div class="edit-upload-wrapper">${uploadWrapper.outerHTML}</div>
            `;
            const newInput = document.querySelector('#foto_profil');
            if (newInput) {
                newInput.onchange = previewImage;
            }
        }
    };
    
    if (input.files && input.files[0]) {
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../include/footer.php'; ?>