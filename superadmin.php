<?php
session_start();
require 'koneksi.php';

// ==========================================
// 1. PENGATURAN AKUN SUPER ADMIN (UBAH DISINI)
// ==========================================
$superadmin_username = "owner";
$superadmin_password = "password123"; // Ganti dengan password yang rumit

// ==========================================
// 2. PROSES LOGOUT SUPER ADMIN
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['is_superadmin']);
    header("Location: superadmin.php");
    exit;
}

// ==========================================
// 3. PROSES LOGIN SUPER ADMIN
// ==========================================
$login_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_superadmin'])) {
    if ($_POST['username'] === $superadmin_username && $_POST['password'] === $superadmin_password) {
        $_SESSION['is_superadmin'] = true;
        header("Location: superadmin.php");
        exit;
    } else {
        $login_error = "Username atau Password Super Admin salah!";
    }
}

// ==========================================
// 4. PROSES TAMBAH DATA (JIKA SUDAH LOGIN)
// ==========================================
$pesan = "";
$tipe_pesan = "";

if (isset($_SESSION['is_superadmin']) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_user'])) {
    $role = $_POST['role'];
    $password_raw = $_POST['password'];
    
    // HASH PASSWORD DISINI SEBELUM MASUK DATABASE
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT); 

    try {
        if ($role === 'mahasiswa') {
            $sql = "INSERT INTO tbmahasiswa (nim, nama, email, password, is_verified) VALUES (:nim, :nama, :email, :password, '1')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nim' => $_POST['nim'],
                ':nama' => $_POST['nama'],
                ':email' => $_POST['email'],
                ':password' => $password_hash
            ]);
            $pesan = "Data Mahasiswa berhasil ditambahkan!";
            $tipe_pesan = "success";

        } elseif ($role === 'admin') {
            $sql = "INSERT INTO administrator (username, nama_lengkap, password) VALUES (:username, :nama_lengkap, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $_POST['username_admin'],
                ':nama_lengkap' => $_POST['nama_admin'],
                ':password' => $password_hash
            ]);
            $pesan = "Data Administrator berhasil ditambahkan!";
            $tipe_pesan = "success";

        } elseif ($role === 'pengurus') {
            $sql = "INSERT INTO pengurus_organisasi (id_organisasi, nama_pengurus, jabatan, password) VALUES (:id_org, :nama, :jabatan, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_org' => $_POST['id_organisasi'],
                ':nama' => $_POST['nama_pengurus'],
                ':jabatan' => $_POST['jabatan'],
                ':password' => $password_hash
            ]);
            $pesan = "Data Pengurus Organisasi berhasil ditambahkan!";
            $tipe_pesan = "success";
        }
    } catch (PDOException $e) {
        $pesan = "Gagal menambahkan data. Error: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

// Ambil data organisasi untuk dropdown pilihan di form pengurus
$organisasi_list = [];
if (isset($_SESSION['is_superadmin'])) {
    $stmtOrg = $pdo->query("SELECT * FROM organisasi");
    $organisasi_list = $stmtOrg->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - MASAGENA ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .hidden { display: none !important; }
    </style>
</head>
<body class="bg-[#ededed] min-h-screen">

    <?php if (!isset($_SESSION['is_superadmin'])): ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full border-t-4 border-red-600">
            <h2 class="text-2xl font-bold text-center text-red-600 mb-2">SUPER ADMIN ONLY</h2>
            <p class="text-center text-gray-500 text-sm mb-6">Akses khusus untuk pemilik program</p>
            
            <?php if($login_error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm font-semibold text-center"><?= $login_error ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <input type="text" name="username" required placeholder="Username Super Admin" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-600">
                <input type="password" name="password" required placeholder="Password Super Admin" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-600">
                <button type="submit" name="login_superadmin" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition">LOGIN OTORISASI</button>
            </form>
        </div>
    </div>

    <?php else: ?>
    <div class="max-w-4xl mx-auto p-6 mt-10">
        <div class="bg-[#1F3D68] p-6 rounded-t-xl flex justify-between items-center text-white">
            <div>
                <h1 class="text-2xl font-bold">Portal Super Admin</h1>
                <p class="text-sm text-gray-300">Tambahkan data Mahasiswa, Admin, atau Pengurus ke database.</p>
            </div>
            <a href="?action=logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg font-bold text-sm transition">LOGOUT</a>
        </div>

        <div class="bg-white p-8 rounded-b-xl shadow-lg border-2 border-t-0 border-gray-200">
            <?php if($pesan): ?>
                <div class="<?= $tipe_pesan == 'success' ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400' ?> border p-4 rounded-lg mb-6 font-semibold text-center">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Role Akun Baru</label>
                    <select name="role" id="roleSelector" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] bg-gray-50" onchange="toggleForm()">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="admin">Administrator</option>
                        <option value="pengurus">Pengurus Organisasi</option>
                    </select>
                </div>

                <hr>

                <div id="formMahasiswa" class="space-y-4">
                    <h3 class="font-bold text-[#F59E0B]">Data Mahasiswa</h3>
                    <input type="text" name="nim" placeholder="NIM Mahasiswa" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                    <input type="text" name="nama" placeholder="Nama Lengkap Mahasiswa" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                    <input type="email" name="email" placeholder="Email (Contoh: xxx@mahasiswa.ith.ac.id)" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                </div>

                <div id="formAdmin" class="space-y-4 hidden">
                    <h3 class="font-bold text-[#F59E0B]">Data Administrator</h3>
                    <input type="text" name="username_admin" placeholder="Username Login Admin" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                    <input type="text" name="nama_admin" placeholder="Nama Lengkap Admin" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                </div>

                <div id="formPengurus" class="space-y-4 hidden">
                    <h3 class="font-bold text-[#F59E0B]">Data Pengurus Organisasi</h3>
                    <select name="id_organisasi" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                        <option value="">-- Pilih Organisasi --</option>
                        <?php foreach($organisasi_list as $org): ?>
                            <option value="<?= $org['id_organisasi'] ?>"><?= $org['nama_organisasi'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="nama_pengurus" placeholder="Nama Lengkap Pengurus" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                    <input type="text" name="jabatan" placeholder="Jabatan (Contoh: Ketua, Sekretaris)" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#F59E0B]">
                </div>

                <div class="pt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password Akun (Akan di-hash otomatis)</label>
                    <input type="password" name="password" required placeholder="Masukkan Password Akun" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68]">
                </div>

                <button type="submit" name="tambah_user" class="w-full bg-[#1F3D68] hover:bg-[#152e52] text-white font-bold py-4 rounded-lg transition duration-200 text-lg shadow-md">
                    TAMBAHKAN PENGGUNA KE DATABASE
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            let role = document.getElementById('roleSelector').value;
            
            // Sembunyikan semua form terlebih dahulu
            document.getElementById('formMahasiswa').classList.add('hidden');
            document.getElementById('formAdmin').classList.add('hidden');
            document.getElementById('formPengurus').classList.add('hidden');

            // Hapus atribut required agar form yang tersembunyi tidak memblokir submit
            document.querySelectorAll('#formMahasiswa input, #formAdmin input, #formPengurus input, #formPengurus select').forEach(el => el.removeAttribute('required'));

            // Tampilkan form yang dipilih dan berikan atribut required
            if (role === 'mahasiswa') {
                document.getElementById('formMahasiswa').classList.remove('hidden');
                document.querySelector('input[name="nim"]').setAttribute('required', 'true');
                document.querySelector('input[name="nama"]').setAttribute('required', 'true');
                document.querySelector('input[name="email"]').setAttribute('required', 'true');
            } else if (role === 'admin') {
                document.getElementById('formAdmin').classList.remove('hidden');
                document.querySelector('input[name="username_admin"]').setAttribute('required', 'true');
                document.querySelector('input[name="nama_admin"]').setAttribute('required', 'true');
            } else if (role === 'pengurus') {
                document.getElementById('formPengurus').classList.remove('hidden');
                document.querySelector('select[name="id_organisasi"]').setAttribute('required', 'true');
                document.querySelector('input[name="nama_pengurus"]').setAttribute('required', 'true');
                document.querySelector('input[name="jabatan"]').setAttribute('required', 'true');
            }
        }
        
        // Panggil saat halaman pertama dimuat
        toggleForm();
    </script>
    <?php endif; ?>

</body>
</html>