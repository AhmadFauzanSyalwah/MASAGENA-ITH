<?php
session_start();
require 'koneksi.php'; // Hubungkan ke koneksi database Anda

// Jika admin sudah login sebelumnya, langsung alihkan ke dashboard_admin.php
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: dashboard_admin.php?tab=dashboard");
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : 'login';
$error = "";

// =========================================================================
// PROSES LOGIKA FORM LOGIN & VERIFIKASI ID AKSES
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ----------- TAHAP 1: CEK USERNAME & PASSWORD -----------
    if (isset($_POST['login_admin'])) {
        $username = trim($_POST['username']); 
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            
            // Ambil data dari database administrator
            $sql = "SELECT * FROM administrator WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            // Verifikasi password
            if ($admin && password_verify($password, $admin['password'])) {
                
                // Cek apakah Admin sudah diverifikasi oleh Superadmin (punya ID Akses)
                if (empty($admin['id_akses']) || $admin['status_verifikasi'] !== 'Terverifikasi') {
                    $error = "Akun Anda belum diverifikasi oleh Superadmin. Silakan hubungi pusat!";
                } else {
                    // Lolos tahap password & akun sudah diverifikasi -> Lanjut input ID Akses
                    $_SESSION['temp_admin_id'] = $admin['id_admin'];
                    header("Location: ?step=verify");
                    exit;
                }

            } else {
                $error = "Username atau Password salah!";
            }
        } else {
            $error = "Harap isi semua kolom!";
        }
    } 
    
    // ----------- TAHAP 2: VALIDASI ID AKSES DARI DATABASE -----------
    elseif (isset($_POST['verify_admin'])) {
        $id_akses_input = trim($_POST['otp']); // Mengambil dari input bernama 'otp'
        
        if (isset($_SESSION['temp_admin_id'])) {
            $id_admin = $_SESSION['temp_admin_id'];
            
            // Ambil ulang data dari DB untuk mencocokkan ID Akses
            $sql = "SELECT * FROM administrator WHERE id_admin = :id_admin";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_admin' => $id_admin]);
            $admin = $stmt->fetch();
            
            // Cek kecocokan ID Akses dari inputan dengan yang ada di database
            if ($admin && $admin['id_akses'] === $id_akses_input) {
                
                // =============================================================
                // SET SESSION RESMI UNTUK MASUK KE DASHBOARD ADMIN
                // =============================================================
                $_SESSION['is_admin']   = true;
                $_SESSION['admin_id']   = $admin['id_admin'];
                $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                
                // Hapus sesi penampung sementara
                unset($_SESSION['temp_admin_id']);
                
                // Alihkan ke Dashboard Admin
                header("Location: dashboard_admin.php?tab=dashboard");
                exit;
                // =============================================================
                
            } else {
                $error = "ID Akses tidak valid! Periksa kembali kode Anda.";
            }
        } else {
            // Jika memaksa masuk ke step=verify tanpa login username/password
            header("Location: ?step=login");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MASAGENA ITH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#ededed] min-h-screen flex flex-col relative z-0">

    <div class="absolute top-0 left-0 w-[22%] h-[55%] bg-[#1F3D68] z-[-1]"></div>
    <div class="absolute top-[55%] left-0 w-[22%] h-[45%] bg-[#F59E0B] z-[-1]"></div>

    <div class="flex-grow flex items-center justify-center p-4 md:p-8">
        
        <div class="bg-white rounded-[24px] shadow-[15px_15px_0px_0px_#d4d4d4] flex flex-col md:flex-row max-w-[900px] w-full min-h-[500px] overflow-hidden">
            
            <div class="md:w-[42%] flex flex-col">
                <div class="flex-grow bg-white flex items-center justify-center p-6 md:p-8">
                    <img src="asset/logo-masagena.png" alt="Masagena ITH Logo" class="w-[90%] max-w-[280px] object-contain">
                </div>
                <div class="bg-[#1F3D68] h-[35%] flex items-center justify-center p-6">
                    <div class="text-white text-[13px] text-center font-semibold leading-relaxed space-y-1.5">
                        <p>Media Akses Seputar</p>
                        <p>Agenda dan Kegiatan</p>
                        <p>Institut Teknologi</p>
                        <p>Bacharuddin Jusuf Habibie</p>
                    </div>
                </div>
            </div>

            <div class="md:w-[58%] px-12 py-10 flex flex-col justify-center bg-white">
                
                <?php if ($step === 'login'): ?>
                    <div class="text-center mb-8">
                        <h2 class="text-[28px] font-bold text-[#1F3D68]">Selamat Datang</h2>
                        <p class="text-gray-500 text-sm mt-1 font-semibold">Silahkan login Admin untuk melanjutkan</p>
                    </div>

                    <?php if($error != ""): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-center text-sm font-semibold">
                            <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="?step=login" method="POST" class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-500"></i>
                            </div>
                            <input type="text" name="username" required placeholder="Username Admin" class="w-full pl-11 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-semibold text-gray-700 placeholder-gray-500 transition">
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-500"></i>
                            </div>
                            <input type="password" name="password" id="passwordField" required placeholder="Password" class="w-full pl-11 pr-11 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-semibold text-gray-700 placeholder-gray-500 transition">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword()">
                                <i class="fa-regular fa-eye-slash text-gray-500 hover:text-gray-700" id="eyeIcon"></i>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs font-semibold pt-1 pb-2">
                            <a href="#" class="text-[#205187] hover:underline">Lupa password?</a>
                        </div>

                        <button type="submit" name="login_admin" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3 px-4 rounded-lg focus:outline-none text-xs transition duration-200 uppercase tracking-wide">
                            LANJUTKAN LOGIN
                        </button>
                    </form>

                <?php elseif ($step === 'verify'): ?>
                    <div class="text-center mb-8">
                        <h2 class="text-[28px] font-bold text-[#1F3D68]">Verifikasi Keamanan</h2>
                        <p class="text-gray-500 text-xs mt-1 font-semibold leading-relaxed">
                            Masukkan kode <b>ID AKSES</b> Anda yang telah<br>
                            diberikan oleh Superadmin via WhatsApp.
                        </p>
                    </div>

                    <?php if($error != ""): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-center text-sm font-semibold">
                            <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="?step=verify" method="POST" class="space-y-6">
                        <div class="relative w-4/5 mx-auto">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-key text-gray-500"></i>
                            </div>
                            <input type="text" name="otp" required placeholder="ID AKSES" autocomplete="off" class="w-full pl-11 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-bold text-gray-700 placeholder-gray-500 transition tracking-widest text-center uppercase">
                        </div>

                        <div class="w-4/5 mx-auto mt-6">
                            <button type="submit" name="verify_admin" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3.5 px-4 rounded-lg focus:outline-none text-xs transition duration-200 uppercase tracking-wide">
                                VERIFIKASI & LOGIN
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-6">
                        <a href="?step=login" class="text-[12px] text-gray-500 hover:text-red-500 hover:underline transition font-bold"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login Utama</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="bg-[#1F3D68] text-white flex flex-col md:flex-row justify-between items-center px-8 py-4 w-full relative z-10 border-t-2 border-[#152e52]">
        <div class="text-xs font-semibold mb-2 md:mb-0">
            &copy; 2026 MASAGENA ITH. All rights reserved
        </div>
        <div class="font-bold text-lg tracking-widest flex items-center">
            MASAGENA &nbsp;<span class="text-[#F59E0B]">ITH</span>
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("passwordField");
            var icon = document.getElementById("eyeIcon");
            if (x && icon) {
                if (x.type === "password") {
                    x.type = "text";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                } else {
                    x.type = "password";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                }
            }
        }
    </script>
</body>
</html>