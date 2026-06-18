-- Hapus database lama jika ada dan buat baru agar fresh
DROP DATABASE IF EXISTS `masagena-ith`;
CREATE DATABASE `masagena-ith`;
USE `masagena-ith`;

-- =======================================================
-- 1. TABEL ORGANISASI
-- =======================================================
CREATE TABLE `organisasi` (
    `id_organisasi` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_organisasi` VARCHAR(100) NOT NULL,
    `jenis` ENUM('BEM','UKM','SC','Himpunan') NOT NULL,
    `deskripsi` TEXT,
    `logo` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- 2. TABEL PENGGUNA (DIPISAH SESUAI KODE PHP)
-- =======================================================
CREATE TABLE `tbmahasiswa` (
    `id_mahasiswa` INT AUTO_INCREMENT PRIMARY KEY,
    `nim` VARCHAR(20) UNIQUE,
    `nama` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `prodi` VARCHAR(50),
    `angkatan` VARCHAR(4),
    `kontak` VARCHAR(20),
    `is_verified` ENUM('0','1') DEFAULT '0',
    `verification_token` VARCHAR(6),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `administrator` (
    `id_admin` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `nama_lengkap` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `no_hp` VARCHAR(20),
    `id_akses` VARCHAR(20),
    `status_verifikasi` VARCHAR(50) DEFAULT 'Belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pengurus_organisasi` (
    `id_pengurus` INT AUTO_INCREMENT PRIMARY KEY,
    `id_organisasi` INT,
    `nama_pengurus` VARCHAR(150) NOT NULL,
    `jabatan` VARCHAR(100) DEFAULT 'Anggota Inti',
    `password` VARCHAR(255) NOT NULL,
    `no_hp` VARCHAR(20),
    `id_akses` VARCHAR(20),
    `status_verifikasi` VARCHAR(50) DEFAULT 'Belum',
    FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi`(`id_organisasi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- 3. TABEL KONTEN & FITUR
-- =======================================================
CREATE TABLE `konten_kegiatan` (
    `id_konten` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `deskripsi` TEXT NOT NULL,
    `tanggal_kegiatan` DATE,
    `kategori` VARCHAR(50),
    `lampiran` VARCHAR(255),
    `status_publikasi` VARCHAR(20) DEFAULT 'publish',
    `id_organisasi` INT NOT NULL,
    `id_pembuat` INT NOT NULL, -- Merujuk ke id_pengurus / id_admin (FK dilepas agar fleksibel)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi`(`id_organisasi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `komentar` (
    `id_komentar` INT AUTO_INCREMENT PRIMARY KEY,
    `isi_komentar` TEXT NOT NULL,
    `id_mahasiswa` INT NOT NULL, -- Disetel agar mahasiswa yang bisa komen
    `id_konten` INT NOT NULL,
    `id_komentar_parent` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan`(`id_konten`) ON DELETE CASCADE,
    FOREIGN KEY (`id_komentar_parent`) REFERENCES `komentar`(`id_komentar`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `likes` (
    `id_like` INT AUTO_INCREMENT PRIMARY KEY,
    `id_mahasiswa` INT NOT NULL,
    `id_konten` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (`id_mahasiswa`, `id_konten`),
    FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan`(`id_konten`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pendaftaran` (
    `id_pendaftaran` INT AUTO_INCREMENT PRIMARY KEY,
    `id_mahasiswa` INT NOT NULL,
    `id_konten` INT NOT NULL,
    `tanggal_daftar` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status_pendaftaran` ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
    `kuota_maks` INT DEFAULT 0,
    UNIQUE (`id_mahasiswa`, `id_konten`),
    FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan`(`id_konten`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `aspirasi` (
    `id_aspirasi` INT AUTO_INCREMENT PRIMARY KEY,
    `isi_aspirasi` TEXT NOT NULL,
    `kategori` VARCHAR(100),
    `id_mahasiswa` INT NOT NULL,
    `id_organisasi_tujuan` INT,
    `status` VARCHAR(50) DEFAULT 'proses',
    `tanggapan` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`id_organisasi_tujuan`) REFERENCES `organisasi`(`id_organisasi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =======================================================
-- 4. INSERT DATA DARI FILE DUMP ANDA
-- =======================================================

-- 4.1 Data Organisasi
INSERT INTO organisasi (id_organisasi, nama_organisasi, jenis, deskripsi, created_at) VALUES
(1,'BEM ITH','BEM','Badan Eksekutif Mahasiswa Institut Teknologi Habibie','2026-06-15 14:47:10'),
(2,'UKM Robotik','UKM','Unit Kegiatan Mahasiswa Robotika','2026-06-15 14:47:10'),
(6,'Habibie Coding Club','SC','Klub pemrograman dan pengembangan perangkat lunak','2026-06-16 07:34:36'),
(11,'English Club ITH','UKM','Klub Bahasa Inggris Mahasiswa','2026-06-16 07:34:36'),
(44,'Himpunan Mahasiswa Ilmu Komputer','Himpunan','Himpunan Mahasiswa Program Studi S1 Ilmu Komputer / Informatika','2026-06-16 07:34:36');

-- 4.2 Data Mahasiswa
INSERT INTO tbmahasiswa (id_mahasiswa, nama, email, password, nim, prodi, angkatan, is_verified) VALUES
(3,'Andi Prasetyo','andi@mahasiswa.ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','241011001','Ilmu Komputer','2024','1'),
(45,'Rina Melati','rina.melati@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','241011002','Sistem Informasi','2024','1');

-- 4.3 Data Administrator
INSERT INTO administrator (id_admin, username, nama_lengkap, password, status_verifikasi) VALUES
(1,'admin_kampus','Administrator','$2y$10$qu8S96J2sRC6WfI3Nb2VsupZ1.X.h05ORFfiBOed8zwNYRAGuDz6.','Belum');

-- 4.4 Data Pengurus Organisasi
INSERT INTO pengurus_organisasi (id_pengurus, id_organisasi, nama_pengurus, jabatan, password, status_verifikasi) VALUES
(2, 1, 'Budi Santoso', 'Ketua', '$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG', 'Belum'),
(6, 6, 'Fajar Coding', 'Inti', '$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG', 'Belum');

-- 4.5 Data Konten Kegiatan
INSERT INTO konten_kegiatan (id_konten, judul, deskripsi, tanggal_kegiatan, kategori, id_organisasi, id_pembuat) VALUES
(1,'Dies Natalis ITH ke-4','Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.','2026-06-25','Acara Kampus',1,2),
(2,'Sosialisasi Beasiswa 2026','Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.','2026-07-02','Pendidikan',1,2),
(6,'Bootcamp Web Development','Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.','2026-07-15','Workshop',6,6);

-- 4.6 Data Komentar & Likes
INSERT INTO komentar (id_komentar, isi_komentar, id_mahasiswa, id_konten, created_at) VALUES
(1,'Wah, acaranya sangat menarik min! Nggak sabar buat ikutan.', 3, 2, '2026-06-16 08:23:37');

INSERT INTO likes (id_like, id_mahasiswa, id_konten, created_at) VALUES
(12, 3, 2, '2026-06-16 08:41:02');