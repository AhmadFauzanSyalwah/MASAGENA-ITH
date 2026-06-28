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
                <a href="/MASAGENA-ITH/auth/login.php" class="btn-hero btn-primary">
                    <i class="fas fa-arrow-right"></i> Mulai
                </a>
                <a href="/MASAGENA-ITH/auth/register.php" class="btn-hero btn-secondary">
                    Daftar <i class="fas fa-arrow-right"></i>
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
            <p>Siap memulai? <a href="/MASAGENA-ITH/auth/register.php">Daftar sekarang</a> dan nikmati semua layanan kami!</p>
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
                <a href="/MASAGENA-ITH/auth/register.php">Daftar Sekarang</a>
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

        <div class="team-subsection">
            <h3 class="team-subtitle"><i class="fas fa-users" style="color:#FFA007;"></i> Tim Pengembang</h3>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="team-info">
                        <h4>Ahmad Fauzan Syalwah</h4>
                        <p class="nim">241011057</p>
                        <p class="role">Fullstack Developer</p>
                        <p class="bio">Bertanggung jawab atas pengembangan backend dan integrasi sistem.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="team-info">
                        <h4>Muhammad Bintang</h4>
                        <p class="nim">241011083</p>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">Mengembangkan antarmuka pengguna yang responsif dan interaktif.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="team-info">
                        <h4>Arya Ahmad</h4>
                        <p class="nim">241011087</p>
                        <p class="role">UI/UX Designer</p>
                        <p class="bio">Merancang pengalaman pengguna dan antarmuka yang intuitif.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="team-info">
                        <h4>Muhammad Aqsha Ridwan</h4>
                        <p class="nim">241011089</p>
                        <p class="role">Database Engineer</p>
                        <p class="bio">Mengelola struktur basis data dan memastikan integritas data.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="team-info">
                        <h4>Jeremia Anderson Sipayung</h4>
                        <p class="nim">241011096</p>
                        <p class="role">Quality Assurance</p>
                        <p class="bio">Memastikan kualitas dan stabilitas sistem melalui pengujian menyeluruh.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
     SYARAT & KETENTUAN SECTION
     ============================================================ -->
<section class="section-terms" id="syarat">
    <div class="terms-container">
        <!-- Header -->
        <div class="terms-header">
            <span class="section-badge">SYARAT &amp; KETENTUAN</span>
            <h2>Aturan Penggunaan <span>MASAGENA-ITH</span></h2>
            <p class="terms-desc">
                Dengan menggunakan MASAGENA-ITH, Anda menyetujui seluruh syarat dan ketentuan yang berlaku. 
                Harap baca dengan saksama.
            </p>
        </div>

        <!-- Content -->
        <div class="terms-content">
            <div class="last-updated">
                <i class="fas fa-clock"></i> Terakhir diperbarui: 27 Juni 2026
            </div>

            <!-- 1. Penerimaan Syarat -->
            <div class="terms-item">
                <h3><i class="fas fa-check-circle"></i> 1. Penerimaan Syarat</h3>
                <p>Dengan mengakses dan menggunakan platform <strong>MASAGENA-ITH</strong>, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang tercantum dalam dokumen ini. Jika Anda tidak menyetujui salah satu bagian dari syarat ini, Anda tidak diperkenankan menggunakan layanan kami.</p>
            </div>

            <!-- 2. Akun Pengguna -->
            <div class="terms-item">
                <h3><i class="fas fa-user-circle"></i> 2. Akun Pengguna</h3>
                <p>Untuk menggunakan layanan penuh MASAGENA-ITH, Anda diwajibkan untuk mendaftar dan membuat akun. Anda bertanggung jawab penuh atas keamanan akun Anda, termasuk menjaga kerahasiaan kata sandi.</p>
                <ul>
                    <li><strong>Keaslian Data:</strong> Anda wajib memberikan data diri yang akurat dan terkini saat mendaftar.</li>
                    <li><strong>Tanggung Jawab Akun:</strong> Setiap aktivitas yang dilakukan melalui akun Anda menjadi tanggung jawab Anda sepenuhnya.</li>
                    <li><strong>Penggunaan Akun:</strong> Akun hanya dapat digunakan oleh pemiliknya. Anda tidak diperkenankan meminjamkan atau mentransfer akun kepada pihak lain.</li>
                </ul>
            </div>

            <!-- 3. Konten dan Pengguna -->
            <div class="terms-item">
                <h3><i class="fas fa-file-alt"></i> 3. Konten dan Pengguna</h3>
                <p>Pengguna dapat mengunggah, mempublikasikan, dan berbagi konten seputar kegiatan kemahasiswaan. Namun, konten yang diunggah harus memenuhi ketentuan berikut:</p>
                <ul>
                    <li>Tidak mengandung unsur <strong>SARA, pornografi, kekerasan, atau ujaran kebencian</strong>.</li>
                    <li>Tidak melanggar hak cipta atau hak kekayaan intelektual pihak lain.</li>
                    <li>Relevan dengan tujuan platform sebagai media informasi kegiatan mahasiswa.</li>
                    <li>Konten yang tidak sesuai dapat dihapus tanpa pemberitahuan sebelumnya.</li>
                </ul>
                <div class="highlight-box">
                    <p><i class="fas fa-info-circle"></i> MASAGENA-ITH berhak untuk menghapus atau menonaktifkan akun yang terbukti melanggar ketentuan konten.</p>
                </div>
            </div>

            <!-- 4. Hak dan Kewajiban -->
            <div class="terms-item">
                <h3><i class="fas fa-balance-scale"></i> 4. Hak dan Kewajiban</h3>
                <p><strong>Pengguna</strong> memiliki hak untuk mengakses informasi, berpartisipasi dalam kegiatan, dan menyampaikan aspirasi secara bertanggung jawab.</p>
                <p><strong>Pengurus Organisasi</strong> memiliki hak untuk mengelola konten kegiatan masing-masing, serta wajib menyajikan informasi yang akurat dan terkini.</p>
                <p><strong>Administrator</strong> berhak untuk mengelola sistem dan melakukan pengawasan terhadap konten dan aktivitas pengguna.</p>
            </div>

            <!-- 5. Privasi dan Data -->
            <div class="terms-item">
                <h3><i class="fas fa-shield-alt"></i> 5. Privasi dan Data Pribadi</h3>
                <p>MASAGENA-ITH menghormati privasi Anda. Data pribadi yang kami kumpulkan (seperti nama, email, NIM) digunakan semata-mata untuk keperluan layanan dan tidak akan dibagikan kepada pihak ketiga tanpa izin Anda, kecuali diwajibkan oleh hukum.</p>
                <ul>
                    <li>Data Anda disimpan dengan aman dan hanya diakses oleh pihak yang berwenang.</li>
                    <li>Anda dapat mengakses, mengubah, atau menghapus data pribadi Anda melalui dashboard.</li>
                    <li>Kami tidak menjual atau menyewakan data Anda kepada pihak manapun.</li>
                </ul>
                <div class="highlight-box">
                    <p><i class="fas fa-lock"></i> Informasi lebih lanjut mengenai pengelolaan data dapat Anda baca di halaman <a href="#kebijakan" style="color:#FFA007; font-weight:600;">Kebijakan Privasi</a>.</p>
                </div>
            </div>

            <!-- 6. Perubahan Syarat -->
            <div class="terms-item">
                <h3><i class="fas fa-sync-alt"></i> 6. Perubahan Syarat</h3>
                <p>MASAGENA-ITH dapat memperbarui syarat dan ketentuan ini dari waktu ke waktu. Perubahan akan diinformasikan melalui platform dan berlaku efektif setelah dipublikasikan. Pengguna disarankan untuk secara berkala meninjau halaman ini.</p>
            </div>

            <!-- 7. Sanksi Pelanggaran -->
            <div class="terms-item">
                <h3><i class="fas fa-gavel"></i> 7. Sanksi Pelanggaran</h3>
                <p>Pelanggaran terhadap syarat dan ketentuan ini dapat mengakibatkan:</p>
                <ul>
                    <li>Peringatan atau teguran tertulis.</li>
                    <li>Pembatasan akses atau fitur tertentu.</li>
                    <li>Penonaktifan atau penghapusan akun.</li>
                    <li>Langkah hukum jika diperlukan.</li>
                </ul>
            </div>

            <!-- 8. Hubungi Kami -->
            <div class="terms-item">
                <h3><i class="fas fa-envelope"></i> 8. Hubungi Kami</h3>
                <p>Jika Anda memiliki pertanyaan atau keluhan terkait syarat dan ketentuan ini, silakan hubungi kami melalui:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:info@masagena.ith.ac.id" style="color:#FFA007;">info@masagena.ith.ac.id</a></li>
                    <li><strong>Telepon:</strong> (0421) 123456</li>
                    <li><strong>Alamat:</strong> Kampus ITH, Parepare, Sulawesi Selatan</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FAQ SECTION
     ============================================================ -->
<section class="section-faq" id="faq">
    <div class="faq-container">
        <div class="faq-header">
            <span class="section-badge">FAQ</span>
            <h2>Frequently Asked <span>Questions</span></h2>
            <p class="faq-desc">
                Temukan jawaban atas pertanyaan yang paling sering diajukan tentang MASAGENA-ITH.
            </p>
        </div>

        <div class="faq-list" id="faqList">
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
// ===== FAQ TOGGLE =====
function toggleFaq(el) {
    const item = el.closest('.faq-item');
    const isActive = item.classList.contains('active');

    // Tutup semua FAQ (opsional: biarkan hanya satu terbuka)
    // document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));

    if (isActive) {
        item.classList.remove('active');
    } else {
        item.classList.add('active');
    }
}

// ===== SCROLL REVEAL (sudah ada) =====
// ... kode scroll reveal yang sudah ada ...
</script>

<?php include 'include/footer.php'; ?>