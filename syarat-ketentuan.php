<?php
// syarat-dan-ketentuan.php - Halaman Syarat & Ketentuan
require_once 'config/database.php';
include 'include/header_public.php';
?>

<!-- ============================================================
     CSS TAMBAHAN - HALAMAN SYARAT & KETENTUAN
     ============================================================ -->
<style>
    .legal-page {
        padding: 4rem 2rem;
        max-width: 1000px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 70vh;
    }
    .legal-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .legal-page .page-header h1 {
        font-size: 2.5rem;
        color: #071C34;
        font-weight: 700;
    }
    .legal-page .page-header h1 span {
        color: #FFA007;
    }
    .legal-page .page-header p {
        color: #555;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0.5rem auto 0;
    }
    .legal-page .last-updated {
        text-align: right;
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.5rem;
    }
    .legal-content {
        background: #fff;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .legal-content h2 {
        font-size: 1.6rem;
        color: #071C34;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid #FFA007;
        padding-left: 1rem;
    }
    .legal-content h2:first-of-type {
        margin-top: 0;
    }
    .legal-content p {
        color: #333;
        line-height: 1.8;
        margin-bottom: 1rem;
    }
    .legal-content ul {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .legal-content ul li {
        color: #333;
        line-height: 1.8;
        margin-bottom: 0.5rem;
        list-style-type: disc;
    }
    .legal-content .highlight-box {
        background: #fef9e7;
        border-left: 4px solid #FFA007;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        margin: 1.5rem 0;
    }
    .legal-content .highlight-box p {
        margin-bottom: 0;
        color: #333;
    }
    .legal-content a {
        color: #FFA007;
        font-weight: 600;
        text-decoration: none;
    }
    .legal-content a:hover {
        text-decoration: underline;
    }
    @media (max-width: 768px) {
        .legal-page {
            padding: 2rem 1rem;
        }
        .legal-page .page-header h1 {
            font-size: 2rem;
        }
        .legal-content {
            padding: 1.5rem;
        }
        .legal-content h2 {
            font-size: 1.3rem;
        }
    }
</style>

<section class="legal-page">
    <div class="page-header">
        <h1>Syarat &amp; <span>Ketentuan</span></h1>
        <p>Aturan penggunaan platform MASAGENA-ITH yang wajib Anda pahami dan patuhi.</p>
    </div>

    <div class="last-updated">
        <i class="fas fa-clock"></i> Terakhir diperbarui: 27 Juni 2026
    </div>

    <div class="legal-content">
        <!-- 1. Penerimaan Syarat -->
        <h2>1. Penerimaan Syarat</h2>
        <p>
            Dengan mengakses dan menggunakan platform <strong>MASAGENA-ITH</strong>, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang tercantum dalam dokumen ini. Jika Anda tidak menyetujui salah satu bagian dari syarat ini, Anda tidak diperkenankan menggunakan layanan kami.
        </p>

        <!-- 2. Akun Pengguna -->
        <h2>2. Akun Pengguna</h2>
        <p>
            Untuk menggunakan layanan penuh MASAGENA-ITH, Anda diwajibkan untuk mendaftar dan membuat akun. Anda bertanggung jawab penuh atas keamanan akun Anda, termasuk menjaga kerahasiaan kata sandi.
        </p>
        <ul>
            <li><strong>Keaslian Data:</strong> Anda wajib memberikan data diri yang akurat dan terkini saat mendaftar.</li>
            <li><strong>Tanggung Jawab Akun:</strong> Setiap aktivitas yang dilakukan melalui akun Anda menjadi tanggung jawab Anda sepenuhnya.</li>
            <li><strong>Penggunaan Akun:</strong> Akun hanya dapat digunakan oleh pemiliknya. Anda tidak diperkenankan meminjamkan atau mentransfer akun kepada pihak lain.</li>
        </ul>

        <!-- 3. Konten dan Pengguna -->
        <h2>3. Konten dan Pengguna</h2>
        <p>
            Pengguna dapat mengunggah, mempublikasikan, dan berbagi konten seputar kegiatan kemahasiswaan. Namun, konten yang diunggah harus memenuhi ketentuan berikut:
        </p>
        <ul>
            <li>Tidak mengandung unsur <strong>SARA, pornografi, kekerasan, atau ujaran kebencian</strong>.</li>
            <li>Tidak melanggar hak cipta atau hak kekayaan intelektual pihak lain.</li>
            <li>Relevan dengan tujuan platform sebagai media informasi kegiatan mahasiswa.</li>
            <li>Konten yang tidak sesuai dapat dihapus tanpa pemberitahuan sebelumnya.</li>
        </ul>
        <div class="highlight-box">
            <p><i class="fas fa-info-circle"></i> MASAGENA-ITH berhak untuk menghapus atau menonaktifkan akun yang terbukti melanggar ketentuan konten.</p>
        </div>

        <!-- 4. Hak dan Kewajiban -->
        <h2>4. Hak dan Kewajiban</h2>
        <p>
            <strong>Pengguna</strong> memiliki hak untuk mengakses informasi, berpartisipasi dalam kegiatan, dan menyampaikan aspirasi secara bertanggung jawab.
        </p>
        <p>
            <strong>Pengurus Organisasi</strong> memiliki hak untuk mengelola konten kegiatan masing-masing, serta wajib menyajikan informasi yang akurat dan terkini.
        </p>
        <p>
            <strong>Administrator</strong> berhak untuk mengelola sistem dan melakukan pengawasan terhadap konten dan aktivitas pengguna.
        </p>

        <!-- 5. Privasi dan Data -->
        <h2>5. Privasi dan Data Pribadi</h2>
        <p>
            MASAGENA-ITH menghormati privasi Anda. Data pribadi yang kami kumpulkan (seperti nama, email, NIM) digunakan semata-mata untuk keperluan layanan dan tidak akan dibagikan kepada pihak ketiga tanpa izin Anda, kecuali diwajibkan oleh hukum.
        </p>
        <ul>
            <li>Data Anda disimpan dengan aman dan hanya diakses oleh pihak yang berwenang.</li>
            <li>Anda dapat mengakses, mengubah, atau menghapus data pribadi Anda melalui dashboard.</li>
            <li>Kami tidak menjual atau menyewakan data Anda kepada pihak manapun.</li>
        </ul>
        <div class="highlight-box">
            <p><i class="fas fa-lock"></i> Informasi lebih lanjut mengenai pengelolaan data dapat Anda baca di halaman <a href="kebijakan-privasi.php">Kebijakan Privasi</a>.</p>
        </div>

        <!-- 6. Perubahan Syarat -->
        <h2>6. Perubahan Syarat</h2>
        <p>
            MASAGENA-ITH dapat memperbarui syarat dan ketentuan ini dari waktu ke waktu. Perubahan akan diinformasikan melalui platform dan berlaku efektif setelah dipublikasikan. Pengguna disarankan untuk secara berkala meninjau halaman ini.
        </p>

        <!-- 7. Sanksi Pelanggaran -->
        <h2>7. Sanksi Pelanggaran</h2>
        <p>Pelanggaran terhadap syarat dan ketentuan ini dapat mengakibatkan:</p>
        <ul>
            <li>Peringatan atau teguran tertulis.</li>
            <li>Pembatasan akses atau fitur tertentu.</li>
            <li>Penonaktifan atau penghapusan akun.</li>
            <li>Langkah hukum jika diperlukan.</li>
        </ul>

        <!-- 8. Hubungi Kami -->
        <h2>8. Hubungi Kami</h2>
        <p>
            Jika Anda memiliki pertanyaan atau keluhan terkait syarat dan ketentuan ini, silakan hubungi kami melalui:
        </p>
        <ul>
            <li><strong>Email:</strong> <a href="mailto:info@masagena.ith.ac.id">info@masagena.ith.ac.id</a></li>
            <li><strong>Telepon:</strong> (0421) 123456</li>
            <li><strong>Alamat:</strong> Kampus ITH, Parepare, Sulawesi Selatan</li>
        </ul>

        <div class="highlight-box">
            <p><i class="fas fa-gavel" style="color:#FFA007;"></i> Dengan menggunakan MASAGENA-ITH, Anda telah menyetujui seluruh syarat dan ketentuan yang berlaku.</p>
        </div>
    </div>
</section>

<?php include 'include/footer.php'; ?>