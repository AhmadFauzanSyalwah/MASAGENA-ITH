<?php
session_start();

// 1. Deklarasi USE WAJIB di paling atas setelah tag php / session
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Load file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 3. Panggil koneksi database
require 'koneksi.php'; 

$error = "";

// Proses ketika tombol login ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_mahasiswa'])) {
    $identifier = $_POST['identifier']; 
    $password = $_POST['password'];

    if (!empty($identifier) && !empty($password)) {
        // Query HANYA untuk mengambil data berdasarkan email atau NIM (jangan cek password di SQL)
        $sql = "SELECT * FROM tbmahasiswa WHERE email = :identifier OR nim = :identifier";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':identifier' => $identifier
        ]);

        $user = $stmt->fetch();

        // Verifikasi password menggunakan password_verify()
        if ($user && password_verify($password, $user['password'])) {
            // -- JIKA PASSWORD COCOK, JALANKAN LOGIKA OTP --
            
            // 1. Buat 6 Digit OTP Acak
            $otp = rand(100000, 999999);

            // 2. Update OTP ke database mahasiswa tersebut
            $updateSql = "UPDATE tbmahasiswa SET verification_token = :otp WHERE id_mahasiswa = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':otp' => $otp,
                ':id' => $user['id_mahasiswa']
            ]);

            // 3. Proses Kirim Email OTP
            $mail = new PHPMailer(true);

            try {
                // Setting Server SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'adminmasagena@gmail.com'; // WAJIB GANTI DENGAN EMAIL ANDA
                $mail->Password   = 'dwhh atlo qerk bccu';  // WAJIB GANTI DENGAN 16 DIGIT SANDI APLIKASI
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                $mail->Port       = 465; 

                // Pengirim & Penerima
                $mail->setFrom('adminmasagena@gmail.com', 'Admin MASAGENA ITH'); // Ganti juga dengan email Anda
                $mail->addAddress($user['email'], $user['nama']);

                // Konten Email
                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Verifikasi Login Masagena ITH';
                
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                        <div style='background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>
                            <h2 style='color: #1F3D68; text-align: center;'>Kode OTP MASAGENA ITH</h2>
                            <p>Halo <b>{$user['nama']}</b>,</p>
                            <p>Seseorang mencoba masuk ke akun Anda. Jika itu adalah Anda, silakan gunakan kode OTP berikut untuk melanjutkan login:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #F59E0B; background: #fdf2f8; padding: 10px 20px; border-radius: 5px; border: 1px dashed #F59E0B;'>
                                    {$otp}
                                </span>
                            </div>
                            <p>Kode ini bersifat rahasia. Jangan berikan kode ini kepada siapapun.</p>
                        </div>
                    </div>
                ";

                // Eksekusi kirim email
                $mail->send();
                
                // 4. Setelah email terkirim, arahkan ke verifikasi.php
                $_SESSION['id_belum_verifikasi'] = $user['id_mahasiswa'];
                header("Location: verifikasi.php");
                exit;

            } catch (Exception $e) {
                $error = "Gagal mengirim OTP ke email. Silakan lapor ke Admin. Error: {$mail->ErrorInfo}";
            }

        } else {
            // Error ini akan muncul jika user tidak ditemukan ATAU password salah (hasil password_verify gagal)
            $error = "Email/Username atau Password salah!";
        }
    } else {
        $error = "Harap isi semua kolom!";
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
        /* Font Family (Opsional agar lebih rapi) */
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
                <div class="text-center mb-8">
                    <h2 class="text-[28px] font-bold text-[#1F3D68]">Selamat Datang</h2>
                    <p class="text-gray-500 text-sm mt-1 font-semibold">Silahkan login untuk melanjutkan</p>
                </div>

                <?php if($error != ""): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-center text-sm font-semibold">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-gray-500"></i>
                        </div>
                        <input type="text" name="identifier" required placeholder="Email atau Username" class="w-full pl-11 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1F3D68] text-sm font-semibold text-gray-700 placeholder-gray-500 transition">
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
                        <label class="flex items-center text-gray-600 cursor-pointer">
                            <input type="checkbox" class="mr-2 w-4 h-4 rounded border-gray-300 text-[#1F3D68] focus:ring-[#1F3D68]">
                            Ingat saya
                        </label>
                        <a href="#" class="text-[#205187] hover:underline">Lupa password?</a>
                    </div>

                    <button type="submit" name="login_mahasiswa" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3 px-4 rounded-lg focus:outline-none text-xs transition duration-200">
                        LOGIN
                    </button>

                    <button type="button" class="w-full bg-[#F59E0B] hover:bg-[#d98b09] text-white font-bold py-3 px-4 rounded-lg focus:outline-none text-xs transition duration-200">
                        LOGIN ADMIN & PENGURUS ORGANISASI
                    </button>
                </form>

                <div class="text-center mt-6">
                    <a href="#" class="text-[13px] text-[#2b7bed] font-bold hover:underline">Belum punya akun? Hubungi administrator</a>
                </div>
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
    </script>
</body>
</html>