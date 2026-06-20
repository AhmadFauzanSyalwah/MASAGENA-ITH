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

// --- PROSES AKSI: TAMBAH USER ---
if (isset($_POST['tambah_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $peran = $_POST['peran'];

    try {
        $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt_cek->execute([$username, $email]);
        if ($stmt_cek->fetchColumn() > 0) {
            $pesan = "Username atau Email sudah terdaftar di sistem.";
            $tipe_pesan = "danger";
        } else {
            $stmt_ins = $pdo->prepare("INSERT INTO users (username, email, password, peran, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt_ins->execute([$username, $email, $password, $peran]);
            $pesan = "User baru bernama <strong>" . htmlspecialchars($username) . "</strong> berhasil ditambahkan.";
            $tipe_pesan = "success";
        }
    } catch (PDOException $e) {
        $pesan = "Gagal menambah user: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

// --- PROSES AKSI: HAPUS USER ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_user = intval($_GET['id']);
    
    if ($id_user == $_SESSION['id_user']) {
        $pesan = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.";
        $tipe_pesan = "danger";
    } else {
        try {
            $stmt_del = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt_del->execute([$id_user]);
            $pesan = "Data pengguna berhasil dihapus permanen.";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menghapus data: Akun terikat dengan data organisasi/kegiatan.";
            $tipe_pesan = "danger";
        }
    }
}

// --- AMBIL DATA SEMUA USER ---
try {
    $stmt_user = $pdo->query("SELECT id_user, username, email, peran, created_at FROM users ORDER BY created_at DESC");
    $semua_user = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data user: " . $e->getMessage());
}

include '../../include/header.php';
?>

<style>
    /* Layout Grid 2 Kolom khusus halaman manajemen user */
    .user-management-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        align-items: start;
        margin-top: 1.5rem; 
    }

    /* Memaksimalkan Form agar melebar penuh di dalam kolom grid */
    .user-management-grid .profil-form {
        max-width: 100%; 
        margin: 0; 
        box-shadow: var(--shadow-sm); 
    }

    /* Pembungkus Tabel untuk menangani luapan responsif */
    .table-responsive-container {
        width: 100%;
        overflow-x: auto;
        border-radius: var(--radius); 
        box-shadow: var(--shadow-sm); 
    }

    /* Badge Peran Akses Menggunakan Token Warna Master */
    .role-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 30px;
        text-transform: uppercase;
    }
    .role-admin {
        background-color: #fee2e2;
        color: var(--danger); 
        border: 1px solid rgba(220, 38, 38, 0.15);
    }
    .role-organisasi {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid rgba(3, 105, 161, 0.15);
    }
    .role-mahasiswa {
        background-color: #dcfce7;
        color: var(--success); 
        border: 1px solid rgba(22, 163, 74, 0.15);
    }

    /* Gaya Variasi Tombol Hapus berbasis warna dasar sistem */
    .btn-delete {
        background-color: var(--danger); 
        color: var(--white);
    }
    .btn-delete:hover {
        background-color: #b91c1c; /* merah lebih gelap saat hover */
    }

    /* Responsivitas Grid jika dibuka dari Handphone / Tablet */
    @media (max-width: 1024px) {
        .user-management-grid {
            grid-template-columns: 1fr; 
        }
    }
</style>

<div class="main-container">
    
    <h2 style="margin-bottom: 0.25rem;">Kelola Pengguna Sistem</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem; padding-left: 0.75rem; margin-bottom: 1.5rem;">
        Manajemen autentikasi registrasi, konfigurasi akun, dan pemetaan hak akses peran (Role) pengguna.
    </p>

    <?php if (!empty($pesan) && $tipe_pesan === 'success'): ?>
        <div class="alert"> 
            <?= $pesan ?>
        </div>
    <?php elseif (!empty($pesan) && $tipe_pesan === 'danger'): ?>
        <div class="error"> 
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="user-management-grid">
        
        <div class="profil-form"> 
            <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.1rem;">Tambah User Baru</h3>
            <hr style="border: 0; border-top: 1px solid var(--border); margin-bottom: 1rem;">
            
            <form action="kelola_user.php" method="POST">
                <label for="username">Username</label> 
                <input type="text" id="username" name="username" placeholder="Masukkan username..." required> 
                
                <label for="email">Alamat Email</label> 
                <input type="email" id="email" name="email" placeholder="contoh@domain.com" required> 
                
                <label for="password">Kata Sandi</label> 
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter..." required> 
                
                <label for="peran">Hak Akses Peran</label> 
                <select id="peran" name="peran" required> 
                    <option value="mahasiswa">Mahasiswa (User Biasa)</option>
                    <option value="organisasi">Pengurus Organisasi</option>
                    <option value="admin">Administrator Sistem</option>
                </select>
                
                <button type="submit" name="tambah_user" class="btn" style="width: 100%; margin-top: 1.5rem;"> 
                    Simpan Data Akun
                </button>
            </form>
        </div>

        <div class="table-responsive-container">
            <table class="agenda-table"> 
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th>Identitas User</th>
                        <th>Email</th>
                        <th style="width: 25%;">Peran Akses</th>
                        <th style="width: 15%; text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($semua_user) > 0): ?>
                        <?php $no = 1; foreach ($semua_user as $user): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong style="color: var(--primary);"><?= htmlspecialchars($user['username']) ?></strong>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">
                                        ⏱️ <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php 
                                    $role_class = 'role-mahasiswa';
                                    if ($user['peran'] === 'admin') $role_class = 'role-admin';
                                    if ($user['peran'] === 'organisasi') $role_class = 'role-organisasi';
                                    ?>
                                    <span class="role-badge <?= $role_class ?>">
                                        <?= htmlspecialchars($user['peran']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($user['id_user'] != $_SESSION['id_user']): ?>
                                        <a href="kelola_user.php?id=<?= $user['id_user'] ?>&action=delete" 
                                           class="btn-sm btn-delete" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');"> 
                                            Hapus
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">Aktif (Anda)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
                                📭 Tidak ditemukan data pengguna di dalam database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include '../../include/footer.php'; ?>