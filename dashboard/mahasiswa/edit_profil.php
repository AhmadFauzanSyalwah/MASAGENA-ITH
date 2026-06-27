<?php
/**
 * dashboard/mahasiswa/edit_profil.php
 * Halaman edit profil mahasiswa - Layout seperti contoh
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/session_check.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'mahasiswa') {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Ambil data mahasiswa
$query = "SELECT * FROM tbmahasiswa WHERE id_mahasiswa = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Data mahasiswa tidak ditemukan.');
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama = trim($_POST['nama']);
    $nim = trim($_POST['nim']);
    $email = trim($_POST['email']);

    if (empty($nama) || empty($nim) || empty($email)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $update = "UPDATE tbmahasiswa SET nama = :nama, nim = :nim, email = :email WHERE id_mahasiswa = :id";
        $stmt = $pdo->prepare($update);
        if ($stmt->execute([':nama' => $nama, ':nim' => $nim, ':email' => $email, ':id' => $user_id])) {
            $_SESSION['nama'] = $nama;

            // Upload foto
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto_profil'];
                $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (in_array($file['type'], $allowed)) {
                    $target_dir = '../../uploads/profil/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = $user_id . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], $target_dir . $filename);
                    // Hapus file lama
                    foreach (['jpg', 'jpeg', 'png', 'gif'] as $old_ext) {
                        if ($old_ext !== $ext && file_exists($target_dir . $user_id . '.' . $old_ext)) {
                            unlink($target_dir . $user_id . '.' . $old_ext);
                        }
                    }
                    $_SESSION['success'] = 'Profil berhasil diperbarui!';
                } else {
                    $_SESSION['error'] = 'Format file tidak didukung. Data diri tetap tersimpan.';
                }
            } else {
                $_SESSION['success'] = 'Profil berhasil diperbarui!';
            }

            header('Location: edit_profil.php');
            exit;
        } else {
            $error = 'Gagal memperbarui profil.';
        }
    }
}

// Ambil pesan dari session
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profil.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/edit_profil.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <div class="profil-outer-card">
                <div class="profil-inner-card">
                    
                    <!-- Judul -->
                    <h4 class="edit-title">Profile</h4>

                    <!-- Tampilkan pesan error via JavaScript popup -->
                    <?php if (!empty($error)): ?>
                        <script>
                            alert('<?php echo addslashes($error); ?>');
                        </script>
                    <?php endif; ?>
                    <?php if (!empty($message)): ?>
                        <script>
                            alert('<?php echo addslashes($message); ?>');
                        </script>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="edit-form-layout">
                        
                        <!-- FORM KIRI (Data Diri) -->
                        <div class="edit-form-left">
                            <div class="form-group">
                                <label for="nama">Name</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="nim">NIM</label>
                                <input type="text" name="nim" id="nim" class="form-control" value="<?php echo htmlspecialchars($user['nim']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <!-- Tempat untuk Ganti Password (opsional, bisa ditambahkan nanti) -->
                            <!-- 
                            <div class="form-group">
                                <label>Old Password</label>
                                <input type="password" class="form-control" placeholder="Old Password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" class="form-control" placeholder="New Password">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" class="form-control" placeholder="Confirm Password">
                            </div>
                            -->
                        </div>

                        <!-- FORM KANAN (Foto & Tombol) -->
                        <div class="edit-form-right">
                            <div class="foto-section">
                                <label>Change Profile Photo</label>
                                <div class="current-foto">
                                    <?php 
                                    $foto_path = '../../uploads/profil/' . $user_id . '.jpg';
                                    if (file_exists($foto_path)): ?>
                                        <img src="<?php echo BASE_URL; ?>/uploads/profil/<?php echo $user_id; ?>.jpg" alt="Foto" class="edit-foto-preview">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size:80px; color:#0a2a4a;"></i>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="foto_profil" accept="image/*" class="form-control-file">
                            </div>

                            <div class="edit-actions">
                                <a href="profil.php" class="btn btn-batal">← Batal</a>
                                <button type="submit" name="update" class="btn btn-simpan">Update Profile</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>