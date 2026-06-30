<?php
// index.php - Landing page one-page MASAGENA-ITH
require_once 'config/database.php';

// Statistik dinamis
$statOrganisasi = 10;
$statKegiatan = 50;
$statMahasiswa = 200;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM organisasi");
    $statOrganisasi = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM konten_kegiatan WHERE status_publikasi = 'publik'");
    $statKegiatan = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbmahasiswa");
    $statMahasiswa = $stmt->fetchColumn();
} catch (PDOException $e) {
    // gunakan default
}

include 'include/header_public.php';
?>

<!-- ============================================================
     CSS TAMBAHAN - LANDING PAGE (TERPISAH)
     ============================================================ -->
<link rel="stylesheet" href="/masagena-ith/assets/css/landing-page.css">

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="landing-hero" id="beranda">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="hero-container">
        <div class="hero-left">
            <div class="hero-badge">PLATFORM RESMI ITH</div>
            <h1>
                <span class="highlight">Kelola</span>
                <span class="line-break">Agenda Kampus</span>
                <span class="line-break">Lebih Mudah</span>
            </h1>
            <p class="hero-desc">
                MASAGENA-ITH adalah platform digital terpadu untuk mengakses agenda, 
                kegiatan, dan pengumuman kampus secara real-time. 
                Bergabunglah dengan ribuan mahasiswa ITH.
            </p>
            <div class="hero-buttons">
                <!-- Tombol "Pelajari Lebih Lanjut" mengarah ke section #tentang -->
                <a href="#tentang" class="btn-hero btn-primary">
                    <i class="fas fa-arrow-right"></i> Pelajari Lebih Lanjut
                </a>
                <!-- Tombol "Login" mengarah ke login.php -->
                <a href="/MASAGENA-ITH/auth/login.php" class="btn-hero btn-secondary">
                    Login <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="hero-stats-wrapper">
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?= $statOrganisasi ?>">0</span>
                        <span class="stat-label">Organisasi</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?= $statKegiatan ?>">0</span>
                        <span class="stat-label">Kegiatan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?= $statMahasiswa ?>">0</span>
                        <span class="stat-label">Mahasiswa</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="hero-visual">
                <div class="floating-card"><i class="fas fa-calendar-check"></i> Agenda</div>
                <div class="floating-card"><i class="fas fa-upload"></i> Publikasi</div>
                <div class="floating-card"><i class="fas fa-users"></i> Kolaborasi</div>
                <div class="floating-card"><i class="fas fa-info-circle"></i> Informasi</div>
                <div class="visual-ring visual-ring-1"></div>
                <div class="visual-ring visual-ring-2"></div>
                <div class="visual-center">
                    <img src="/masagena-ith/assets/img/logo.png" alt="MASAGENA-ITH Logo">
                </div>
            </div>
        </div>
    </div>

    <div class="hero-wave">
        <svg viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,100 L0,100 Z" fill="#f8fafc"/>
        </svg>
    </div>
</section>

<!-- ============================================================
     WELCOME / TENTANG SECTION
     ============================================================ -->
<section class="welcome-section" id="tentang">
    <div class="container">
        <span class="section-label">Mengapa MASAGENA-ITH</span>
        <h2>Solusi Digital untuk <span>Kegiatan Kampus</span></h2>
        <p class="section-desc">
            Kami menyediakan platform yang memudahkan mahasiswa dan organisasi 
            dalam mengelola serta mengakses informasi kegiatan kampus secara efisien.
        </p>
        <div class="welcome-grid">
            <div class="welcome-card">
                <div class="icon-wrapper"><i class="fas fa-calendar-alt"></i></div>
                <h3>Agenda Terintegrasi</h3>
                <p>Semua kegiatan kampus dalam satu kalender digital yang dapat diakses kapan saja dan di mana saja.</p>
            </div>
            <div class="welcome-card">
                <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                <h3>Kolaborasi Organisasi</h3>
                <p>Setiap organisasi memiliki ruang sendiri untuk mempublikasikan kegiatan dan mengelola anggotanya.</p>
            </div>
            <div class="welcome-card">
                <div class="icon-wrapper"><i class="fas fa-upload"></i></div>
                <h3>Publikasi Mudah</h3>
                <p>Publikasikan kegiatan kampus dengan cepat dan mudah, menjangkau seluruh mahasiswa ITH.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     LAYANAN SECTION
     ============================================================ -->
<section class="services-section" id="layanan">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">LAYANAN KAMI</span>
            <h2>Solusi Lengkap <span>Informasi Kampus</span></h2>
            <p class="section-desc">
                MASAGENA-ITH menyediakan berbagai layanan untuk memudahkan mahasiswa 
                mengakses informasi kegiatan dan organisasi kemahasiswaan.
            </p>
        </div>
        <div class="services-grid">
            <!-- Layanan 1 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-calendar-check"></i></div>
                <h3>Agenda &amp; Kegiatan</h3>
                <p>Lihat jadwal kegiatan dari seluruh organisasi kemahasiswaan dalam tampilan kalender interaktif.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> Kalender interaktif</li>
                    <li><i class="fas fa-check-circle"></i> Filter berdasarkan kategori</li>
                    <li><i class="fas fa-check-circle"></i> Notifikasi kegiatan</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Layanan 2 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-building"></i></div>
                <h3>Informasi Organisasi</h3>
                <p>Profil lengkap BEM, UKM, Study Club, dan Himpunan Mahasiswa beserta struktur kepengurusan.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> Profil organisasi</li>
                    <li><i class="fas fa-check-circle"></i> Daftar pengurus</li>
                    <li><i class="fas fa-check-circle"></i> Program kerja</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Layanan 3 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-hand-holding-heart"></i></div>
                <h3>Pendaftaran Kegiatan</h3>
                <p>Daftar ke kegiatan langsung dari sistem dan pantau status pendaftaran Anda secara real-time.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> Daftar online</li>
                    <li><i class="fas fa-check-circle"></i> Status real-time</li>
                    <li><i class="fas fa-check-circle"></i> Riwayat pendaftaran</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Layanan 4 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-pen-fancy"></i></div>
                <h3>Aspirasi &amp; Kritik</h3>
                <p>Sampaikan aspirasi, saran, dan kritik secara anonim atau dengan identitas.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> Anonim atau identitas</li>
                    <li><i class="fas fa-check-circle"></i> Respon organisasi</li>
                    <li><i class="fas fa-check-circle"></i> Arsip aspirasi</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Layanan 5 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-search"></i></div>
                <h3>Pencarian Cepat</h3>
                <p>Cari kegiatan, organisasi, atau pengurus dengan cepat dan mudah.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> Cari kegiatan</li>
                    <li><i class="fas fa-check-circle"></i> Cari organisasi</li>
                    <li><i class="fas fa-check-circle"></i> Cari pengurus</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Layanan 6 -->
            <div class="service-card">
                <div class="icon-wrapper"><i class="fas fa-print"></i></div>
                <h3>Cetak Kalender</h3>
                <p>Cetak kalender agenda dalam format PDF untuk perencanaan kegiatan Anda secara offline.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check-circle"></i> PDF siap cetak</li>
                    <li><i class="fas fa-check-circle"></i> Pilih bulan/tahun</li>
                    <li><i class="fas fa-check-circle"></i> Desain rapi</li>
                </ul>
                <a href="#fitur" class="service-link">Pelajari Selengkapnya <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="services-cta">
            <!-- Link "Daftar sekarang" diubah menjadi "Login sekarang" -->
            <p>Siap memulai? <a href="/MASAGENA-ITH/auth/login.php">Login sekarang</a> dan nikmati semua layanan kami!</p>
        </div>
    </div>
</section>

<!-- ============================================================
     FITUR UNGGULAN SECTION
     ============================================================ -->
<section class="section-features" id="fitur">
    <div class="features-container">
        <div class="features-header">
            <span class="section-badge">FITUR UNGGULAN</span>
            <h2>Nikmati Kemudahan <span>Berbagai Fitur</span></h2>
            <p class="features-desc">
                MASAGENA-ITH dilengkapi dengan berbagai fitur canggih untuk mendukung 
                aktivitas kemahasiswaan Anda secara optimal.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-heart"></i></div>
                <h3>Like &amp; Komentar</h3>
                <p>Berinteraksi dengan konten kegiatan melalui like dan komentar.</p>
                <div class="feature-tag">Interaktif</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3>Pengurus Organisasi</h3>
                <p>Lihat daftar pengurus dan struktur kepengurusan setiap organisasi.</p>
                <div class="feature-tag">Transparan</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-pen-fancy"></i></div>
                <h3>Aspirasi &amp; Kritik</h3>
                <p>Sampaikan aspirasi secara anonim atau dengan identitas.</p>
                <div class="feature-tag">Anonim</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-search"></i></div>
                <h3>Pencarian Cepat</h3>
                <p>Temukan kegiatan, organisasi, atau pengurus dengan cepat.</p>
                <div class="feature-tag">Cepat</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-share-alt"></i></div>
                <h3>Berbagi Informasi</h3>
                <p>Bagikan informasi kegiatan ke media sosial atau teman Anda.</p>
                <div class="feature-tag">Sosial</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-print"></i></div>
                <h3>Cetak Kalender</h3>
                <p>Cetak kalender agenda dalam format PDF untuk perencanaan offline.</p>
                <div class="feature-tag">PDF</div>
            </div>
        </div>

        <div class="features-cta">
            <p>
                <i class="fas fa-rocket" style="color:#FFA007;"></i>
                Tertarik menggunakan fitur-fitur kami?
                <!-- Link "Daftar Sekarang" diubah menjadi "Login Sekarang" -->
                <a href="/MASAGENA-ITH/auth/login.php">Login Sekarang</a>
                dan rasakan kemudahannya!
            </p>
        </div>
    </div>
</section>

<!-- ============================================================
     TEKNOLOGI SECTION
     ============================================================ -->
<section class="section-tech" id="teknologi">
    <div class="tech-container">
        <div class="tech-header">
            <span class="section-badge">TEKNOLOGI</span>
            <h2>Dibangun dengan <span>Teknologi Terkini</span></h2>
            <p class="tech-desc">
                MASAGENA-ITH dibangun menggunakan teknologi modern yang memastikan 
                performa, keamanan, dan kemudahan pengembangan.
            </p>
        </div>

        <div class="tech-marquee-wrapper">
            <div class="tech-marquee">
                <div class="tech-track">
                    <span class="tech-item"><i class="fab fa-php"></i> PHP 8.2</span>
                    <span class="tech-item"><i class="fab fa-html5"></i> HTML5</span>
                    <span class="tech-item"><i class="fab fa-css3-alt"></i> CSS3</span>
                    <span class="tech-item"><i class="fab fa-js"></i> JavaScript</span>
                    <span class="tech-item"><i class="fas fa-database"></i> MySQL</span>
                    <span class="tech-item"><i class="fab fa-bootstrap"></i> Bootstrap 5</span>
                    <span class="tech-item"><i class="fas fa-code"></i> PDO</span>
                    <span class="tech-item"><i class="fas fa-shield-alt"></i> Bcrypt</span>
                    <span class="tech-item"><i class="fab fa-git-alt"></i> Git</span>
                    <span class="tech-item"><i class="fas fa-cloud"></i> Apache</span>
                    <span class="tech-item"><i class="fab fa-github"></i> GitHub</span>
                    <span class="tech-item"><i class="fas fa-server"></i> Laragon</span>
                    <span class="tech-item"><i class="fas fa-terminal"></i> PHPStorm</span>
                    <!-- Loop -->
                    <span class="tech-item"><i class="fab fa-php"></i> PHP 8.2</span>
                    <span class="tech-item"><i class="fab fa-html5"></i> HTML5</span>
                    <span class="tech-item"><i class="fab fa-css3-alt"></i> CSS3</span>
                    <span class="tech-item"><i class="fab fa-js"></i> JavaScript</span>
                    <span class="tech-item"><i class="fas fa-database"></i> MySQL</span>
                    <span class="tech-item"><i class="fab fa-bootstrap"></i> Bootstrap 5</span>
                    <span class="tech-item"><i class="fas fa-code"></i> PDO</span>
                    <span class="tech-item"><i class="fas fa-shield-alt"></i> Bcrypt</span>
                    <span class="tech-item"><i class="fab fa-git-alt"></i> Git</span>
                    <span class="tech-item"><i class="fas fa-cloud"></i> Apache</span>
                    <span class="tech-item"><i class="fab fa-github"></i> GitHub</span>
                    <span class="tech-item"><i class="fas fa-server"></i> Laragon</span>
                    <span class="tech-item"><i class="fas fa-terminal"></i> PHPStorm</span>
                </div>
            </div>
        </div>

        <div class="tech-grid">
            <div class="tech-grid-item"><i class="fab fa-php"></i> PHP 8.2</div>
            <div class="tech-grid-item"><i class="fab fa-html5"></i> HTML5</div>
            <div class="tech-grid-item"><i class="fab fa-css3-alt"></i> CSS3</div>
            <div class="tech-grid-item"><i class="fab fa-js"></i> JavaScript</div>
            <div class="tech-grid-item"><i class="fas fa-database"></i> MySQL</div>
            <div class="tech-grid-item"><i class="fab fa-bootstrap"></i> Bootstrap 5</div>
            <div class="tech-grid-item"><i class="fas fa-code"></i> PDO</div>
            <div class="tech-grid-item"><i class="fas fa-shield-alt"></i> Bcrypt</div>
            <div class="tech-grid-item"><i class="fab fa-git-alt"></i> Git</div>
            <div class="tech-grid-item"><i class="fas fa-cloud"></i> Apache</div>
            <div class="tech-grid-item"><i class="fab fa-github"></i> GitHub</div>
            <div class="tech-grid-item"><i class="fas fa-server"></i> Laragon</div>
            <div class="tech-grid-item"><i class="fas fa-terminal"></i> PHPStorm</div>
        </div>
    </div>
</section>

<!-- ============================================================
     TIM PENGEMBANG & DOSEN SECTION
     ============================================================ -->
<section class="section-team" id="tim">
    <div class="team-container">
        <div class="team-header">
            <span class="section-badge">TIM KAMI</span>
            <h2>Tim Pengembang &amp; <span>Dosen Pengampu</span></h2>
            <p class="team-desc">
                Dibangun oleh mahasiswa Ilmu Komputer ITH yang berdedikasi, 
                dengan bimbingan dosen-dosen berpengalaman di bidang Rekayasa Perangkat Lunak dan Pemrograman Web.
            </p>
        </div>

        <!-- TIM PENGEMBANG (dengan pembagian tugas baru & link GitHub) -->
        <!-- TIM PENGEMBANG (dengan foto) -->
<div class="team-subsection">
    <h3 class="team-subtitle"><i class="fas fa-users" style="color:#FFA007;"></i> Tim Pengembang</h3>
    <div class="team-grid">
        <!-- 1. Arya Ahmad -->
        <div class="team-card">
            <div class="team-avatar">
                <img src="/masagena-ith/assets/img/tim/arya.png" alt="Arya Ahmad" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            </div>
            <div class="team-info">
                <h4>Arya Ahmad</h4>
                <p class="nim">241011087</p>
                <p class="role">Autentikasi &amp; Manajemen Pengguna</p>
                <p class="bio">Bertanggung jawab atas sistem login, registrasi, dan manajemen akun pengguna.</p>
                <div class="team-social">
                    <a href="https://github.com/Aryarec21" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>

        <!-- 2. Ahmad Fauzan Syalwah -->
        <div class="team-card">
            <div class="team-avatar">
                <img src="/masagena-ith/assets/img/tim/fauzan.png" alt="Ahmad Fauzan Syalwah" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            </div>
            <div class="team-info">
                <h4>Ahmad Fauzan Syalwah</h4>
                <p class="nim">241011057</p>
                <p class="role">Dashboard &amp; Informasi Organisasi</p>
                <p class="bio">Mengembangkan tampilan dashboard dan menyajikan informasi organisasi secara terstruktur.</p>
                <div class="team-social">
                    <a href="https://github.com/AhmadFauzanSyalwah" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>

        <!-- 3. Jeremia Anderson Sipayung -->
        <div class="team-card">
            <div class="team-avatar">
                <img src="/masagena-ith/assets/img/tim/jeremia.png" alt="Jeremia Anderson Sipayung" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            </div>
            <div class="team-info">
                <h4>Jeremia Anderson Sipayung</h4>
                <p class="nim">241011096</p>
                <p class="role">Manajemen Konten &amp; Agenda</p>
                <p class="bio">Mengelola konten kegiatan dan agenda kampus agar selalu terbaru dan terorganisir.</p>
                <div class="team-social">
                    <a href="https://github.com/Jeremia-spy" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>

        <!-- 4. Muhammad Aqsha Ridwan -->
        <div class="team-card">
            <div class="team-avatar">
                <img src="/masagena-ith/assets/img/tim/acca.png" alt="Muhammad Aqsha Ridwan" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            </div>
            <div class="team-info">
                <h4>Muhammad Aqsha Ridwan</h4>
                <p class="nim">241011089</p>
                <p class="role">Aktivitas Pengguna &amp; Pencarian</p>
                <p class="bio">Mengembangkan fitur pencarian dan mencatat aktivitas pengguna untuk analisis.</p>
                <div class="team-social">
                    <a href="https://github.com/muhammadaqsharidwan-spec" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>

        <!-- 5. Muhammad Bintang -->
        <div class="team-card">
            <div class="team-avatar">
                <img src="/masagena-ith/assets/img/tim/bintang.png" alt="Muhammad Bintang" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            </div>
            <div class="team-info">
                <h4>Muhammad Bintang</h4>
                <p class="nim">241011083</p>
                <p class="role">Pendaftaran &amp; Aspirasi/Kritik</p>
                <p class="bio">Mengelola sistem pendaftaran kegiatan serta fitur aspirasi dan kritik mahasiswa.</p>
                <div class="team-social">
                    <a href="https://github.com/bintz30" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- DOSEN PENGAMPU (tetap seperti semula) -->
        <div class="team-subsection">
            <h3 class="team-subtitle"><i class="fas fa-chalkboard-teacher" style="color:#FFA007;"></i> Dosen Pengampu</h3>
            <div class="dosen-grid">
                <div class="dosen-card">
                    <div class="dosen-avatar"><i class="fas fa-user-tie"></i></div>
                    <div class="dosen-info">
                        <h4>Putri Ayu Maharani, S.T., M.Sc</h4>
                        <p class="dosen-role">Dosen Rekayasa Perangkat Lunak (UTS)</p>
                        <p class="dosen-desc">Mengampu mata kuliah Rekayasa Perangkat Lunak pada periode UTS.</p>
                        <span class="dosen-badge">RPL UTS</span>
                    </div>
                </div>
                <div class="dosen-card">
                    <div class="dosen-avatar"><i class="fas fa-user-tie"></i></div>
                    <div class="dosen-info">
                        <h4>Andri Dwi Utomo, S.Kom., M.T.</h4>
                        <p class="dosen-role">Dosen Pemrograman Web (UTS)</p>
                        <p class="dosen-desc">Mengampu mata kuliah Pemrograman Web pada periode UTS.</p>
                        <span class="dosen-badge">Web UTS</span>
                    </div>
                </div>
                <div class="dosen-card">
                    <div class="dosen-avatar"><i class="fas fa-user-tie"></i></div>
                    <div class="dosen-info">
                        <h4>Muh. Ikhsan Amar, S.Kom., M.Kom.</h4>
                        <p class="dosen-role">Dosen Rekayasa Perangkat Lunak (UAS)</p>
                        <p class="dosen-desc">Mengampu mata kuliah Rekayasa Perangkat Lunak pada periode UAS.</p>
                        <span class="dosen-badge">RPL UAS</span>
                    </div>
                </div>
                <div class="dosen-card">
                    <div class="dosen-avatar"><i class="fas fa-user-tie"></i></div>
                    <div class="dosen-info">
                        <h4>Mardhiyyah Rafrin, S.T., M.Sc</h4>
                        <p class="dosen-role">Dosen Pemrograman Web (UAS)</p>
                        <p class="dosen-desc">Mengampu mata kuliah Pemrograman Web pada periode UAS.</p>
                        <span class="dosen-badge">Web UAS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     KONTAK SECTION
     ============================================================ -->
<section class="section-contact" id="kontak">
    <div class="contact-container">
        <div class="contact-header">
            <span class="section-badge">HUBUNGI KAMI</span>
            <h2>Ada Pertanyaan? <span>Hubungi Kami</span></h2>
            <p class="contact-desc">
                Kami siap membantu Anda. Silakan hubungi kami melalui form di bawah 
                atau melalui kontak yang tersedia.
            </p>
        </div>

        <div class="contact-wrapper">
            <div class="contact-info">
                <h3>Informasi Kontak</h3>
                <p class="contact-sub">Tim MASAGENA-ITH siap merespon pertanyaan dan masukan Anda.</p>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div><h4>Email</h4><p><a href="mailto:info@masagena.ith.ac.id">info@masagena.ith.ac.id</a></p></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                    <div><h4>Telepon</h4><p><a href="tel:+62421123456">(0421) 123456</a></p></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div><h4>Alamat</h4><p>Kampus ITH, Parepare, Sulawesi Selatan</p></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                    <div><h4>Jam Operasional</h4><p>Senin - Jumat: 08.00 - 17.00 WITA</p></div>
                </div>

                <div class="contact-social">
                    <h4>Ikuti Kami</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3>Kirim Pesan</h3>
                <p class="contact-sub">Isi form di bawah untuk mengirim pesan kepada kami.</p>

                <form id="contactForm" method="POST" action="#">
                    <div class="form-group">
                        <label for="contactName">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="contactName" name="name" placeholder="Masukkan nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label for="contactEmail">Email <span class="required">*</span></label>
                        <input type="email" id="contactEmail" name="email" placeholder="Masukkan email Anda" required>
                    </div>
                    <div class="form-group">
                        <label for="contactSubject">Subjek <span class="required">*</span></label>
                        <input type="text" id="contactSubject" name="subject" placeholder="Masukkan subjek pesan" required>
                    </div>
                    <div class="form-group">
                        <label for="contactMessage">Pesan <span class="required">*</span></label>
                        <textarea id="contactMessage" name="message" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SCRIPT ANIMASI
     ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Counter Statistik
    const statNumbers = document.querySelectorAll('.stat-number');
    const observerOptions = { threshold: 0.3, rootMargin: '0px' };
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-count')) || 0;
                animateCounter(el, target);
                counterObserver.unobserve(el);
            }
        });
    }, observerOptions);
    statNumbers.forEach(el => counterObserver.observe(el));

    function animateCounter(el, target) {
        let current = 0;
        const duration = 1500;
        const stepTime = 30;
        const steps = duration / stepTime;
        const increment = target / steps;
        let count = 0;
        const timer = setInterval(() => {
            count++;
            current = Math.min(Math.round(count * increment), target);
            el.textContent = current + '+';
            if (count >= steps) {
                clearInterval(timer);
                el.textContent = target + '+';
            }
        }, stepTime);
    }

    // Scroll Reveal untuk Welcome Cards
    const cards = document.querySelectorAll('.welcome-card');
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 120);
                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        cardObserver.observe(card);
    });

    // Scroll Reveal untuk Service Cards
    const serviceCards = document.querySelectorAll('.service-card');
    const serviceObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 120);
                serviceObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    serviceCards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        serviceObserver.observe(card);
    });
});
</script>

<?php include 'include/footer.php'; ?>