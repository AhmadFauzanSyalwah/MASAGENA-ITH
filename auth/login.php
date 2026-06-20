<?php
session_start();
// 1. Sesuaikan path ke folder config
require_once '../config/database.php';

$error = "";
$step = isset($_GET['step']) ? $_GET['step'] : 'login';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

// ----------- A. JIKA TOMBOL "LOGIN" (MAHASISWA) DITEKAN -----------
    if (isset($_POST['login_mahasiswa'])) {
        $identifier = trim($_POST['identifier']); 
        $password = trim($_POST['password']);

        if (!empty($identifier) && !empty($password)) {
            $sql = "SELECT * FROM tbmahasiswa WHERE email = :email OR nim = :nim";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $identifier,':nim'   => $identifier]);
            $user = $stmt->fetch();

            // Jika User ditemukan dan password benar
            if ($user && password_verify($password, $user['password'])) {
                // Generate OTP 6 Digit
                $otp = rand(100000, 999999);
                
                // 2. Simpan OTP ke database (kolom verification_token)
                $update_otp = $pdo->prepare("UPDATE tbmahasiswa SET verification_token = :otp WHERE id_mahasiswa = :id");
                $update_otp->execute([':otp' => $otp, ':id' => $user['id_mahasiswa']]);
                
                // 3. Panggil PHPMailer & Kirim Email
                require_once 'PHPMailer/src/Exception.php';
                require_once 'PHPMailer/src/PHPMailer.php';
                require_once 'PHPMailer/src/SMTP.php';
                
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; 
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'adminmasagena@gmail.com'; 
                    $mail->Password   = 'dwhh atlo qerk bccu';    
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('email_anda@gmail.com', 'Admin Masagena ITH');
                    $mail->addAddress($user['email'], $user['nama']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Kode Keamanan Login OTP - Masagena ITH';
                    $mail->Body    = "Halo <b>{$user['nama']}</b>,<br><br>Seseorang baru saja mencoba login ke akun Anda. Berikut adalah kode OTP Anda untuk masuk ke sistem MASAGENA ITH:<br><h2 style='color:#F59E0B;'>{$otp}</h2><br>Kode ini bersifat rahasia. Jangan berikan kepada siapapun.";

                    $mail->send();
                    
                    // 4. Buat session khusus untuk tahap verifikasi & Alihkan ke verifikasi.php
                    $_SESSION['id_belum_verifikasi'] = $user['id_mahasiswa'];
                    header("Location: verifikasi.php");
                    exit;
                    
                } catch (Exception $e) {
                    $error = "Gagal mengirim email OTP. Error: {$mail->ErrorInfo}";
                }

            } else {
                $error = "NIM/Email atau Password salah!";
            }
        } else {
            $error = "Harap isi semua kolom!";
        }
    }

    // ----------- B. JIKA TOMBOL "LOGIN PENGURUS ORGANISASI" DITEKAN -----------
    if (isset($_POST['login_pengurus'])) {
        $identifier = trim($_POST['identifier']); 
        $password = trim($_POST['password']);

        if (!empty($identifier) && !empty($password)) {
            // PERBAIKAN: Menggunakan nama_pengurus atau no_hp karena kolom 'username' tidak ada di tabel pengurus
            $sql = "SELECT * FROM pengurus_organisasi WHERE nama_pengurus = :nama_pengurus OR no_hp = :no_hp";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama_pengurus' => $identifier,
                ':no_hp'         => $identifier
            ]);
            $pengurus = $stmt->fetch();

            if ($pengurus && password_verify($password, $pengurus['password'])) {
                
                // Cek apakah pengurus ini sudah diverifikasi dan punya ID Akses dari Superadmin
                if ($pengurus['status_verifikasi'] === 'Terverifikasi' && !empty($pengurus['id_akses'])) {
                    // Simpan sementara datanya ke session untuk verifikasi tahap 2
                    $_SESSION['pending_pengurus_data'] = $pengurus;
                    
                    // Alihkan ke tahap input ID Akses
                    header("Location: login.php?step=verifikasi");
                    exit;
                } else {
                    $error = "Akun Anda belum diverifikasi oleh Superadmin (Belum memiliki ID Akses).";
                }

            } else {
                $error = "Nama Pengurus/No HP atau Password Pengurus salah!";
            }
        } else {
            $error = "Harap isi semua kolom!";
        }
    }

    // ----------- C. PROSES VERIFIKASI ID AKSES PENGURUS (TAHAP 2) -----------
    if (isset($_POST['verifikasi_id_akses'])) {
        $input_id_akses = trim($_POST['id_akses']);

        if (!empty($input_id_akses)) {
            if (isset($_SESSION['pending_pengurus_data'])) {
                $pengurus_sukses = $_SESSION['pending_pengurus_data'];

                // Mencocokkan data session dengan input, mengabaikan besar/kecil huruf (strcasecmp)
                if (strcasecmp($pengurus_sukses['id_akses'], $input_id_akses) === 0) {
                    
                    // SET SESSION PENGURUS
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['peran']   = 'pengurus';                 
                    $_SESSION['user_id'] = $pengurus_sukses['id_pengurus'];   
                    $_SESSION['nama']    = $pengurus_sukses['nama_pengurus']; // PERBAIKAN: Menyesuaikan nama kolom database 
                    $_SESSION['id_organisasi'] = $pengurus_sukses['id_organisasi'];
                    
                    // Hapus session temporary
                    unset($_SESSION['pending_pengurus_data']);

                    // Arahkan ke dashboard pengurus
                    header("Location: ../dashboard/pengurus/index.php");
                    exit;
                } else {
                    $error = "ID Akses Verifikasi Pengurus Salah!";
                }
            } else {
                $error = "Sesi verifikasi kadaluwarsa, silakan login ulang dari awal.";
            }
        } else {
            $error = "ID Akses verifikasi wajib diisi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MASAGENA ITH</title>
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
                
                <?php if($error != ""): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-center text-sm font-semibold">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step == 'login'): ?>
                    <div class="text-center mb-8">
                        <h2 class="text-[28px] font-bold text-[#1F3D68]">Selamat Datang</h2>
                        <p class="text-gray-500 text-sm mt-1 font-semibold">Silahkan login untuk melanjutkan</p>
                    </div>

                    <form action="login.php?step=login" method="POST" class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-500"></i>
                            </div>
                            <input type="text" name="identifier" required placeholder="Nama Lengkap / No HP Pengurus" class="w-full pl-11 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-semibold text-gray-700 placeholder-gray-500 transition">
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

                        <button type="submit" name="login_mahasiswa" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3 px-4 rounded-lg focus:outline-none text-xs transition duration-200">
                            LOGIN MAHASISWA
                        </button>

                        <button type="submit" name="login_pengurus" class="w-full bg-[#1F3D68] hover:bg-[#152e52] text-white font-bold py-3 px-4 rounded-lg focus:outline-none text-xs transition duration-200">
                            LOGIN PENGURUS ORGANISASI
                        </button>

                        <div class="flex justify-between items-center mt-2 px-1">
                            <a href="lupa_password.php?role=mahasiswa" class="text-[11px] font-bold text-blue-500 hover:text-blue-700 hover:underline">Lupa Password Mahasiswa?</a>
                            <a href="lupa_password.php?role=pengurus" class="text-[11px] font-bold text-blue-500 hover:text-blue-700 hover:underline">Lupa Password Pengurus?</a>
                        </div>
                    </form>
                
                <?php elseif ($step == 'verifikasi'): ?>
                    
                    <div class="text-center mb-8">
                        <h2 class="text-[28px] font-bold text-[#1F3D68]">Verifikasi Keamanan</h2>
                        <p class="text-gray-500 text-xs mt-1 font-semibold leading-relaxed">
                            Masukkan kode <b>ID AKSES</b> Anda yang telah<br>
                            diberikan oleh Superadmin via WhatsApp.
                        </p>
                    </div>

                    <form action="login.php?step=verifikasi" method="POST" class="space-y-6">
                        <div class="relative w-4/5 mx-auto">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-key text-gray-500"></i>
                            </div>
                            <input type="text" name="id_akses" required placeholder="ID AKSES" autocomplete="off" class="w-full pl-11 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-bold text-gray-700 placeholder-gray-500 transition tracking-widest text-center uppercase">
                        </div>

                        <div class="w-4/5 mx-auto mt-6">
                            <button type="submit" name="verifikasi_id_akses" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3.5 px-4 rounded-lg focus:outline-none text-xs transition duration-200 uppercase tracking-wide">
                                VERIFIKASI & LOGIN
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-6">
                        <a href="login.php?step=login" class="text-[12px] text-gray-500 hover:text-red-500 hover:underline transition font-bold"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login Utama</a>
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