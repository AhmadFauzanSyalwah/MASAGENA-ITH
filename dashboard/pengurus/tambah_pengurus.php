<?php
// dashboard/pengurus/tambah_pengurus.php
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
    header('Location: index.php');
    exit;
}

$id_organisasi = $_SESSION['id_organisasi'] ?? 0;

if (!$id_organisasi) {
    $_SESSION['error'] = 'Organisasi tidak ditemukan.';
    header('Location: index.php');
    exit;
}

// ============================================================
// PROSES TAMBAH PENGURUS
// ============================================================
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_pengurus']);
    $jabatan = trim($_POST['jabatan']);
    $level_pengurus = trim($_POST['level']);
    $no_hp = trim($_POST['no_hp']);
    $password = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi_password']);

    // Validasi
    if (empty($nama) || empty($jabatan) || empty($level_pengurus) || empty($no_hp) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!in_array($level_pengurus, ['Pengurus Inti', 'Pengurus Departemen'])) {
        $error = 'Level pengurus tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sesuai.';
    } else {
        // Cek duplikat nama atau no hp
        $cek = $pdo->prepare("SELECT id_pengurus FROM pengurus_organisasi WHERE id_organisasi = ? AND (nama_pengurus = ? OR no_hp = ?)");
        $cek->execute([$id_organisasi, $nama, $no_hp]);
        if ($cek->fetch()) {
            $error = 'Nama atau No HP sudah terdaftar di organisasi ini.';
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $id_akses = strtoupper(substr($nama, 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            $insert = $pdo->prepare("INSERT INTO pengurus_organisasi 
                (id_organisasi, nama_pengurus, jabatan, level, no_hp, password, id_akses, status_verifikasi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Belum Verifikasi')");
            if ($insert->execute([$id_organisasi, $nama, $jabatan, $level_pengurus, $no_hp, $hashed, $id_akses])) {
                $success = 'Pengurus berhasil ditambahkan! ID Akses: ' . $id_akses;
            } else {
                $error = 'Gagal menambahkan pengurus.';
            }
        }
    }
}

include '../../include/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profil.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php if ($success): ?>
    <script>
        alert('<?= addslashes($success) ?>');
        window.location.href = 'manajemen_pengurus.php';
    </script>
<?php endif; ?>
<?php if ($error): ?>
    <script>
        alert('<?= addslashes($error) ?>');
    </script>
<?php endif; ?>

<style>
/* ============================================
   TAMBAH PENGURUS - KONSISTEN
   ============================================ */
.tambah-container {
    max-width: 100%;
    margin: 0;
    padding: 0 1rem;
    box-sizing: border-box;
}

.tambah-title {
    color: #071C34;
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    padding-left: 0.3rem;
}
.tambah-subtitle {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    padding-left: 0.3rem;
}

/* Card */
.tambah-outer-card {
    border: none;
    border-radius: 20px;
    padding: 20px;
    background: #f8fafc;
    box-shadow: 0 4px 20px rgba(10, 42, 74, 0.08);
}
.tambah-inner-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 30px 32px;
    border: 1px solid #e9ecef;
}

/* Form */
.form-group {
    margin-bottom: 1.2rem;
}
.form-group label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.2rem;
}
.form-group .form-control {
    width: 100%;
    padding: 0.5rem 0.8rem;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #ffffff;
    transition: 0.3s;
}
.form-group .form-control:focus {
    border-color: #FFA007;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,160,7,0.15);
}

/* Tombol */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
    flex-wrap: wrap;
}
.form-actions .btn-batal {
    background: transparent;
    color: #071C34;
    border: 2px solid #071C34;
    border-radius: 50px;
    padding: 0.4rem 1.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}
.form-actions .btn-batal:hover {
    background: #071C34;
    color: #fff;
}
.form-actions .btn-simpan {
    background: #FFA007;
    color: #071C34;
    border: none;
    border-radius: 50px;
    padding: 0.4rem 1.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}
.form-actions .btn-simpan:hover {
    background: #071C34;
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .tambah-container {
        padding: 0 0.5rem;
    }
    .tambah-inner-card {
        padding: 16px;
    }
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .form-actions .btn-batal,
    .form-actions .btn-simpan {
        text-align: center;
        padding: 0.5rem;
    }
}
</style>

<div class="tambah-container">

    <h4 class="tambah-title"><i class="fas fa-user-plus" style="color:#FFA007;"></i> Tambah Pengurus</h4>
    <p class="tambah-subtitle">Tambahkan anggota baru ke kepengurusan organisasi Anda</p>

    <div class="tambah-outer-card">
        <div class="tambah-inner-card">

            <form method="post">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_pengurus">Nama Lengkap</label>
                            <input type="text" name="nama_pengurus" id="nama_pengurus" class="form-control" 
                                   placeholder="Nama lengkap pengurus" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control" 
                                   placeholder="Contoh: Ketua, Sekretaris, Kepala Divisi" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select name="level" id="level" class="form-control" required>
                                <option value="">Pilih Level</option>
                                <option value="Pengurus Inti">Pengurus Inti</option>
                                <option value="Pengurus Departemen">Pengurus Departemen</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_hp">Nomor HP</label>
                            <input type="text" name="no_hp" id="no_hp" class="form-control" 
                                   placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="konfirmasi_password">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" 
                                   placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="manajemen_pengurus.php" class="btn-batal"><i class="fas fa-arrow-left"></i> Batal</a>
                    <button type="submit" name="tambah" class="btn-simpan"><i class="fas fa-save"></i> Simpan</button>
                </div>

            </form>

        </div>
    </div>

</div>

<?php include '../../include/footer.php'; ?>