<?php
// kebijakan-privasi.php - Halaman Kebijakan Privasi
require_once 'config/database.php';
include 'include/header_public.php';
?>

<!-- ============================================================
     CSS TAMBAHAN - HALAMAN KEBIJAKAN PRIVASI
     ============================================================ -->
<style>
    .privacy-page {
        padding: 4rem 2rem;
        max-width: 1000px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 70vh;
    }
    .privacy-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .privacy-page .page-header h1 {
        font-size: 2.5rem;
        color: #071C34;
        font-weight: 700;
    }
    .privacy-page .page-header h1 span {
        color: #FFA007;
    }
    .privacy-page .page-header p {
        color: #555;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0.5rem auto 0;
    }
    .privacy-page .last-updated {
        text-align: right;
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.5rem;
    }
    .privacy-content {
        background: #fff;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .privacy-content h2 {
        font-size: 1.6rem;
        color: #071C34;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid #FFA007;
        padding-left: 1rem;
    }
    .privacy-content h2:first-of-type {
        margin-top: 0;
    }
    .privacy-content p {
        color: #333;
        line-height: 1.8;
        margin-bottom: 1rem;
    }
    .privacy-content ul {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .privacy-content ul li {
        color: #333;
        line-height: 1.8;
        margin-bottom: 0.5rem;
        list-style-type: disc;
    }
    .privacy-content .highlight-box {
        background: #fef9e7;
        border-left: 4px solid #FFA007;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        margin: 1.5rem 0;
    }
    .privacy-content .highlight-box p {
        margin-bottom: 0;
        color: #333;
    }
    .privacy-content a {
        color: #FFA007;
        font-weight: 600;
        text-decoration: none;
    }
    .privacy-content a:hover {
        text-decoration: underline;
    }
    @media (max-width: 768px) {
        .privacy-page {
            padding: 2rem 1rem;
        }
        .privacy-page .page-header h1 {
            font-size: 2rem;
        }
        .privacy-content {
            padding: 1.5rem;
        }
        .privacy-content h2 {
            font-size: 1.3rem;
        }
    }
</style>

<section class="privacy-page">
    <div class="page-header">
        <h1>Kebijakan <span>Privasi</span></h1>
        <p>Bagaimana MASAGENA-ITH mengelola dan melindungi data pribadi Anda.</p>
    </div>

    <div class="last-updated">
        <i class="fas fa-clock"></i> Terakhir diperbarui: 27 Juni 2026
    </div>

    <div class="privacy-content">
        <h2>1. Pendahuluan</h2>
        <p>
            MASAGENA-ITH berkomitmen untuk melindungi privasi Anda. Kebijakan privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi yang Anda berikan saat menggunakan platform kami. Dengan menggunakan MASAGENA-ITH, Anda menyetujui praktik yang dijelaskan dalam kebijakan ini.
        </p>

        <h2>2. Informasi yang Kami Kumpulkan</h2>
        <p>Kami mengumpulkan beberapa jenis informasi untuk memberikan layanan yang lebih baik kepada Anda:</p>
        <ul>
            <li><strong>Informasi Akun:</strong> Nama, alamat email, NIM (Nomor Induk Mahasiswa), dan kata sandi yang di-hash saat Anda mendaftar.</li>
            <li><strong>Data Profil:</strong> Informasi tambahan yang Anda berikan, seperti foto profil, jurusan, dan afiliasi organisasi.</li>
            <li><strong>Data Aktivitas:</strong> Riwayat pendaftaran kegiatan, aspirasi yang Anda kirimkan, komentar, dan like pada konten.</li>
            <li><strong>Data Teknis:</strong> Alamat IP, jenis peramban, sistem operasi, dan cookie yang digunakan untuk meningkatkan pengalaman pengguna.</li>
        </ul>

        <h2>3. Bagaimana Kami Menggunakan Informasi Anda</h2>
        <p>Informasi yang kami kumpulkan digunakan untuk:</p>
        <ul>
            <li>Menyediakan, memelihara, dan meningkatkan layanan MASAGENA-ITH.</li>
            <li>Memproses pendaftaran kegiatan dan aspirasi Anda.</li>
            <li>Mengirimkan notifikasi penting terkait akun atau kegiatan yang Anda ikuti.</li>
            <li>Menganalisis penggunaan platform untuk pengembangan fitur.</li>
            <li>Menanggapi pertanyaan, keluhan, atau permintaan bantuan Anda.</li>
        </ul>

        <h2>4. Berbagi Informasi dengan Pihak Ketiga</h2>
        <p>
            Kami tidak menjual, menyewakan, atau membagikan data pribadi Anda kepada pihak ketiga untuk kepentingan komersial. Informasi Anda hanya akan dibagikan dalam situasi berikut:
        </p>
        <ul>
            <li>Kepada pengurus organisasi kemahasiswaan yang relevan untuk menindaklanjuti aspirasi atau pendaftaran kegiatan yang Anda kirimkan.</li>
            <li>Jika diwajibkan oleh hukum atau untuk melindungi hak, properti, atau keselamatan MASAGENA-ITH, pengguna, atau publik.</li>
            <li>Dengan persetujuan Anda secara eksplisit.</li>
        </ul>

        <h2>5. Keamanan Data</h2>
        <p>
            Kami menerapkan langkah-langkah keamanan yang wajar untuk melindungi data Anda dari akses tidak sah, perubahan, pengungkapan, atau penghancuran. Kami menggunakan enkripsi kata sandi (bcrypt) dan protokol HTTPS untuk melindungi transmisi data. Namun, perlu diingat bahwa tidak ada metode transmisi data melalui internet yang 100% aman, dan kami tidak dapat menjamin keamanan absolut.
        </p>

        <h2>6. Hak Anda atas Data Pribadi</h2>
        <p>Anda memiliki hak untuk:</p>
        <ul>
            <li><strong>Akses:</strong> Meminta salinan data pribadi yang kami simpan tentang Anda.</li>
            <li><strong>Perbaikan:</strong> Memperbaiki data yang tidak akurat atau melengkapi data yang tidak lengkap.</li>
            <li><strong>Penghapusan:</strong> Meminta penghapusan data pribadi Anda, kecuali jika kami wajib menyimpannya untuk kepatuhan hukum.</li>
            <li><strong>Batasan:</strong> Membatasi pemrosesan data dalam kondisi tertentu.</li>
            <li><strong>Keberatan:</strong> Menolak pemrosesan data untuk kepentingan pemasaran atau analisis.</li>
        </ul>
        <p>Untuk menggunakan hak-hak tersebut, silakan hubungi kami melalui kontak yang tersedia di halaman <a href="#kontak">Kontak</a>.</p>

        <h2>7. Cookie</h2>
        <p>
            Kami menggunakan cookie untuk meningkatkan pengalaman Anda, seperti menyimpan preferensi login dan menganalisis lalu lintas situs. Anda dapat mengatur browser Anda untuk menolak cookie, tetapi beberapa fitur mungkin tidak berfungsi dengan baik.
        </p>

        <h2>8. Tautan ke Situs Lain</h2>
        <p>
            Platform kami mungkin berisi tautan ke situs web pihak ketiga. Kami tidak bertanggung jawab atas konten atau kebijakan privasi situs tersebut. Kami menyarankan Anda untuk membaca kebijakan privasi setiap situs yang Anda kunjungi.
        </p>

        <h2>9. Perubahan Kebijakan Privasi</h2>
        <p>
            Kami dapat memperbarui kebijakan privasi ini dari waktu ke waktu. Setiap perubahan akan diumumkan melalui platform dan berlaku efektif setelah dipublikasikan. Kami mendorong Anda untuk meninjau halaman ini secara berkala.
        </p>

        <h2>10. Hubungi Kami</h2>
        <p>
            Jika Anda memiliki pertanyaan, kekhawatiran, atau permintaan terkait kebijakan privasi ini, silakan hubungi kami melalui:
        </p>
        <ul>
            <li><strong>Email:</strong> <a href="mailto:info@masagena.ith.ac.id">info@masagena.ith.ac.id</a></li>
            <li><strong>Telepon:</strong> (0421) 123456</li>
            <li><strong>Alamat:</strong> Kampus ITH, Parepare, Sulawesi Selatan</li>
        </ul>

        <div class="highlight-box">
            <p><i class="fas fa-shield-alt" style="color:#FFA007;"></i> MASAGENA-ITH berkomitmen untuk menjaga privasi Anda. Data Anda aman bersama kami.</p>
        </div>
    </div>
</section>

<?php include 'include/footer.php'; ?>