<?php
require_once '../../config/session_check.php';
require_once '../../config/database.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['peran'] != 'admin') {
    header("Location: ../" . $_SESSION['peran'] . "/index.php");
    exit();
}

$pesan = '';
$tipe_pesan = '';

// Menentukan tab aktif untuk filter tampilan tabel data (default: admin)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'admin';

// --- PROSES AKSI: TAMBAH USER ---
if (isset($_POST['tambah_user'])) {
    $peran = $_POST['peran'];
    
    if ($peran === 'mahasiswa') {
        $nim = trim($_POST['username']); // Di tab mahasiswa, input username difungsikan sebagai NIM
        $nama = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $prodi = trim($_POST['prodi']);
        $kontak = trim($_POST['kontak']);

        try {
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE nim = ? OR email = ?");
            $stmt_cek->execute([$nim, $email]);
            if ($stmt_cek->fetchColumn() > 0) {
                $pesan = "NIM atau Email mahasiswa sudah terdaftar di sistem.";
                $tipe_pesan = "danger";
            } else {
                $stmt_ins = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, email, password, prodi, kontak, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
                $stmt_ins->execute([$nim, $nama, $email, $password, $prodi, $kontak]);
                $pesan = "Mahasiswa bernama <strong>" . htmlspecialchars($nama) . "</strong> berhasil ditambahkan.";
                $tipe_pesan = "success";
                $tab = 'mahasiswa';
            }
        } catch (PDOException $e) {
            $pesan = "Gagal menambah mahasiswa: " . $e->getMessage();
            $tipe_pesan = "danger";
        }
    } else {
        // Tambah ke tabel administrator
        $username = trim($_POST['username']);
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $password = SHA2(trim($_POST['password']), 256); // Menggunakan SHA2 sesuai skema tabel admin kamu
        $no_hp = trim($_POST['kontak']);
        $id_akses = ($peran === 'admin') ? 1 : 2; // Contoh pembagian id_akses peran

        try {
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM administrator WHERE username = ?");
            $stmt_cek->execute([$username]);
            if ($stmt_cek->fetchColumn() > 0) {
                $pesan = "Username administrator sudah terdaftar.";
                $tipe_pesan = "danger";
            } else {
                // Di sini diasumsikan status_verifikasi langsung 'terverifikasi'
                $stmt_ins = $pdo->prepare("INSERT INTO administrator (username, nama_lengkap, password, no_hp, id_akses, status_verifikasi) VALUES (?, ?, SHA2(?, 256), ?, ?, 'terverifikasi')");
                $stmt_ins->execute([$username, $nama_lengkap, $_POST['password'], $no_hp, $id_akses]);
                $pesan = "Admin baru bernama <strong>" . htmlspecialchars($username) . "</strong> berhasil ditambahkan.";
                $tipe_pesan = "success";
                $tab = 'admin';
            }
        } catch (PDOException $e) {
            $pesan = "Gagal menambah administrator: " . $e->getMessage();
            $tipe_pesan = "danger";
        }
    }
}

// --- PROSES AKSI: HAPUS USER ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_target = intval($_GET['id']);
    $type = isset($_GET['type']) ? $_GET['type'] : 'admin';

    try {
        if ($type === 'mahasiswa') {
            $stmt_del = $pdo->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
            $stmt_del->execute([$id_target]);
            $pesan = "Data mahasiswa berhasil dihapus permanen.";
            $tipe_pesan = "success";
            $tab = 'mahasiswa';
        } else {
            // Proteksi agar tidak menghapus akun yang sedang dipakai login
            if ($id_target == $_SESSION['id_admin'] || $id_target == 1) { // Ganti ke session id_admin kamu
                $pesan = "Anda tidak dapat menghapus akun administrator utama atau akun sendiri.";
                $tipe_pesan = "danger";
            } else {
                $stmt_del = $pdo->prepare("DELETE FROM administrator WHERE id_admin = ?");
                $stmt_del->execute([$id_target]);
                $pesan = "Data administrator berhasil dihapus.";
                $tipe_pesan = "success";
            }
            $tab = 'admin';
        }
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: Data terikat dengan relasi tabel lain.";
        $tipe_pesan = "danger";
    }
}

// --- AMBIL DATA USER BERDASARKAN TAB YANG AKTIF ---
$semua_user = [];
try {
    if ($tab === 'mahasiswa') {
        $stmt_user = $pdo->query("SELECT id_mahasiswa AS id, nim AS pengenal, nama, email, prodi AS ekstra, is_verified AS status FROM mahasiswa ORDER BY id_mahasiswa DESC");
        $semua_user = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt_user = $pdo->query("SELECT id_admin AS id, username AS pengenal, nama_lengkap AS nama, no_hp AS email, status_verifikasi AS ekstra, id_akses AS status FROM administrator ORDER BY id_admin DESC");
        $semua_user = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Gagal mengambil data user: " . $e->getMessage());
}

include '../../include/header.php';
?>

<style>
    .user-management-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        align-items: start;
        margin-top: 1.5rem; 
    }
    .user-management-grid .profil-form {
        max-width: 100%; 
        margin: 0; 
        box-shadow: var(--shadow-sm); 
        background: #fff;
        padding: 1.5rem;
        border-radius: 8px;
    }
    .table-responsive-container {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px; 
        box-shadow: var(--shadow-sm); 
        background: #fff;
        padding: 1rem;
    }
    .role-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 30px;
        text-transform: uppercase;
    }
    .role-admin { background-color: #fee2e2; color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.15); }
    .role-mahasiswa { background-color: #dcfce7; color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.15); }
    .btn-delete { background-color: #dc3545; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem;}
    .btn-delete:hover { background-color: #b91c1c; }
    
    .tab-nav { display: flex; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 2px solid #dee2e6; }
    .tab-link { padding: 0.6rem 1.2rem; text-decoration: none; font-weight: bold; border-radius: 6px 6px 0 0; color: #495057; background: #f8f9fa; }
    .tab-active { background: #007bff; color: white !important; }

    input, select { width: 100%; padding: 0.5rem; margin-bottom: 0.8rem; border: 1px solid #ccc; border-radius: 4px; }
</style>

<div class="main-container">
    
    <h2 style="margin-bottom: 0.25rem;">Kelola Pengguna Sistem</h2>
    <p style="color: #6c757d; font-size: 0.85rem; margin-bottom: 1.5rem;">
        Manajemen autentikasi registrasi, konfigurasi akun, dan pemetaan hak akses peran terpisah.
    </p>

    <?php if (!empty($pesan)): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; background-color: <?= $tipe_pesan === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $tipe_pesan === 'success' ? '#155724' : '#721c24' ?>;">
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="tab-nav">
        <a href="kelola_user.php?tab=admin" class="tab-link <?= $tab === 'admin' ? 'tab-active' : '' ?>">🛡️ Data Admin</a>
        <a href="kelola_user.php?tab=mahasiswa" class="tab-link <?= $tab === 'mahasiswa' ? 'tab-active' : '' ?>">🎓 Data Mahasiswa</a>
    </div>

    <div class="user-management-grid">
        
        <div class="profil-form"> 
            <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem;">Form Input Data</h3>
            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 1rem;">
            
            <form action="kelola_user.php?tab=<?= $tab ?>" method="POST">
                <label for="peran">Pilih Target Tabel Peran</label> 
                <select id="peran" name="peran" onchange="sesuaikanForm(this.value)" required> 
                    <option value="admin" <?= $tab === 'admin' ? 'selected' : '' ?>>Administrator Sistem</option>
                    <option value="mahasiswa" <?= $tab === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                </select>

                <label id="label_pengenal" for="username">Username / NIM</label> 
                <input type="text" id="username" name="username" required> 

                <label for="nama_lengkap">Nama Lengkap</label> 
                <input type="text" id="nama_lengkap" name="nama_lengkap" required> 
                
                <label for="email">Alamat Email</label> 
                <input type="email" id="email" name="email" required> 
                
                <label for="password">Kata Sandi</label> 
                <input type="password" id="password" name="password" required> 

                <div id="field_mahasiswa" style="display: <?= $tab === 'mahasiswa' ? 'block' : 'none' ?>;">
                    <label for="prodi">Program Studi</label> 
                    <input type="text" id="prodi" name="prodi"> 
                </div>

                <label for="kontak">No. HP / Kontak</label> 
                <input type="text" id="kontak" name="kontak" required> 
                
                <button type="submit" name="tambah_user" style="background: #007bff; color:white; border:none; padding:0.7rem; width:100%; border-radius:5px; cursor:pointer; font-weight:bold;"> 
                    Simpan Data Akun
                </button>
            </form>
        </div>

        <div class="table-responsive-container">
            <table style="width: 100%; border-collapse: collapse; text-align: left;"> 
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding:0.5rem;">No</th>
                        <th style="padding:0.5rem;"><?= $tab === 'mahasiswa' ? 'NIM / Nama' : 'Username / Nama' ?></th>
                        <th style="padding:0.5rem;"><?= $tab === 'mahasiswa' ? 'Email' : 'No. HP' ?></th>
                        <th style="padding:0.5rem;"><?= $tab === 'mahasiswa' ? 'Prodi' : 'Status' ?></th>
                        <th style="padding:0.5rem; text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($semua_user) > 0): ?>
                        <?php $no = 1; foreach ($semua_user as $user): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding:0.7rem 0.5rem;"><?= $no++ ?></td>
                                <td style="padding:0.7rem 0.5rem;">
                                    <strong><?= htmlspecialchars($user['pengenal']) ?></strong><br>
                                    <small style="color:#6c757d;"><?= htmlspecialchars($user['nama']) ?></small>
                                </td>
                                <td style="padding:0.7rem 0.5rem;"><?= htmlspecialchars($user['email'] ?: '-') ?></td>
                                <td style="padding:0.7rem 0.5rem;">
                                    <span class="role-badge <?= $tab === 'mahasiswa' ? 'role-mahasiswa' : 'role-admin' ?>">
                                        <?= htmlspecialchars($user['ekstra'] ?: 'Aktif') ?>
                                    </span>
                                </td>
                                <td style="text-align: center; padding:0.7rem 0.5rem;">
                                    <a href="kelola_user.php?id=<?= $user['id'] ?>&action=delete&type=<?= $tab ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');"> 
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6c757d; padding: 3rem 0;">
                                📭 Tidak ditemukan data di dalam kategori ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function sesuaikanForm(val) {
    var fieldMhs = document.getElementById('field_mahasiswa');
    var labelPengenal = document.getElementById('label_pengenal');
    if(val === 'mahasiswa') {
        fieldMhs.style.display = 'block';
        labelPengenal.innerText = 'NIM (Nomor Induk Mahasiswa)';
    } else {
        fieldMhs.style.display = 'none';
        labelPengenal.innerText = 'Username Administrator';
    }
}
// Jalankan fungsi saat halaman pertama kali dimuat untuk sinkronisasi awal
sesuaikanForm(document.getElementById('peran').value);
</script>

<?php include '../../include/footer.php'; ?>