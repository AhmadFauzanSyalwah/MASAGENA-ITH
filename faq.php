<?php
// faq.php - Halaman FAQ
require_once 'config/database.php';
include 'include/header_public.php';
?>

<style>
    .faq-page {
        padding: 4rem 2rem;
        max-width: 1000px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 70vh;
    }
    .faq-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .faq-page .page-header h1 {
        font-size: 2.5rem;
        color: #071C34;
        font-weight: 700;
    }
    .faq-page .page-header h1 span {
        color: #FFA007;
    }
    .faq-page .page-header p {
        color: #555;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0.5rem auto 0;
    }
    .faq-list {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .faq-item {
        border-bottom: 1px solid #e2e8f0;
        padding: 1.2rem 0;
    }
    .faq-item:last-child {
        border-bottom: none;
    }
    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 1.1rem;
        color: #071C34;
        transition: color 0.2s;
        user-select: none;
    }
    .faq-question:hover {
        color: #FFA007;
    }
    .faq-question i {
        transition: transform 0.3s ease;
        color: #FFA007;
        font-size: 1.2rem;
    }
    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.3s ease;
        padding: 0 0.5rem;
        color: #333;
        line-height: 1.7;
    }
    .faq-item.active .faq-answer {
        max-height: 500px;
        padding: 1rem 0.5rem 0.5rem;
    }
    @media (max-width: 768px) {
        .faq-page {
            padding: 2rem 1rem;
        }
        .faq-page .page-header h1 {
            font-size: 2rem;
        }
        .faq-list {
            padding: 1rem;
        }
        .faq-question {
            font-size: 1rem;
        }
    }
</style>

<section class="faq-page">
    <div class="page-header">
        <h1>Frequently Asked <span>Questions</span></h1>
        <p>Temukan jawaban atas pertanyaan yang paling sering diajukan tentang MASAGENA-ITH.</p>
    </div>

    <div class="faq-list">
        <!-- FAQ 1 -->
        <div class="faq-item active">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Apa itu MASAGENA-ITH?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>MASAGENA-ITH adalah platform digital terpadu yang berfungsi sebagai media informasi seputar kegiatan akademik dan non-akademik di lingkungan Institut Teknologi Bacharuddin Jusuf Habibie (IT-BJ Habibie).</p>
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Siapa yang bisa mengakses MASAGENA-ITH?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Seluruh sivitas akademika ITH, terutama mahasiswa. Namun, untuk fitur pendaftaran kegiatan dan aspirasi, Anda perlu login terlebih dahulu.</p>
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara mendaftar kegiatan?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Login sebagai mahasiswa, pilih kegiatan yang diinginkan, lalu klik tombol "Daftar". Anda akan mendapatkan notifikasi status pendaftaran.</p>
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah saya bisa menyampaikan aspirasi secara anonim?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Ya, Anda dapat menyampaikan aspirasi, saran, atau kritik secara anonim atau dengan mencantumkan identitas Anda.</p>
            </div>
        </div>

        <!-- FAQ 5 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana jika saya lupa password?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Gunakan fitur "Lupa Password" di halaman login. Kami akan mengirimkan instruksi reset password ke email Anda.</p>
            </div>
        </div>

        <!-- FAQ 6 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah MASAGENA-ITH gratis?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Ya, MASAGENA-ITH sepenuhnya gratis untuk seluruh mahasiswa dan organisasi kemahasiswaan di ITH.</p>
            </div>
        </div>

        <!-- FAQ 7 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara menjadi pengurus organisasi di platform ini?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Hubungi admin atau pengurus BEM untuk mendaftarkan organisasi Anda. Setelah terdaftar, Anda akan diberikan akses sebagai pengurus organisasi.</p>
            </div>
        </div>

        <!-- FAQ 8 -->
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah data saya aman di MASAGENA-ITH?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Kami sangat memperhatikan keamanan data Anda. Kami menggunakan enkripsi dan protokol keamanan standar. Baca lebih lanjut di halaman <a href="kebijakan-privasi.php" style="color:#FFA007; font-weight:600;">Kebijakan Privasi</a>.</p>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleFaq(el) {
        const item = el.closest('.faq-item');
        const isActive = item.classList.contains('active');

        // Opsional: Tutup semua FAQ (biarkan hanya satu terbuka)
        // document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));

        if (isActive) {
            item.classList.remove('active');
        } else {
            item.classList.add('active');
        }
    }
</script>

<?php include 'include/footer.php'; ?>