-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2026 at 05:02 AM
-- Server version: 8.0.46
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `masagena-ith`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `TampilkanSemuaData` ()   BEGIN
    SELECT * FROM administrator;
    SELECT * FROM aspirasi;
    SELECT * FROM komentar;
    SELECT * FROM konten_kegiatan;
    SELECT * FROM likes;
    SELECT * FROM organisasi;
    SELECT * FROM pendaftaran;
    SELECT * FROM pengurus_organisasi;
    SELECT * FROM tbmahasiswa;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `id_admin` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_akses` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Belum',
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int NOT NULL,
  `kode_aspirasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_organisasi` int DEFAULT NULL,
  `is_anonim` tinyint(1) DEFAULT '0',
  `isi_aspirasi` text COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_organisasi_tujuan` int DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'proses',
  `tanggapan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id_komentar` int NOT NULL,
  `isi_komentar` text COLLATE utf8mb4_general_ci NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_konten` int NOT NULL,
  `id_komentar_parent` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar_aspirasi`
--

CREATE TABLE `komentar_aspirasi` (
  `id_komentar` int NOT NULL,
  `id_aspirasi` int NOT NULL,
  `level_user` varchar(50) NOT NULL,
  `isi_komentar` text NOT NULL,
  `id_admin` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konten_kegiatan`
--

CREATE TABLE `konten_kegiatan` (
  `id_konten` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
  `kuota_maks` int DEFAULT '50',
  `kategori` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lampiran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_publikasi` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'publish',
  `id_organisasi` int NOT NULL,
  `id_pembuat` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten_kegiatan`
--

INSERT INTO `konten_kegiatan` (`id_konten`, `judul`, `deskripsi`, `tanggal_kegiatan`, `kuota_maks`, `kategori`, `lampiran`, `status_publikasi`, `id_organisasi`, `id_pembuat`, `created_at`) VALUES
(1, 'Dies Natalis ITH ke-4', 'Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.', '2026-06-25', 50, 'Acara Kampus', NULL, 'publish', 1, 2, '2026-06-18 12:37:50'),
(2, 'Sosialisasi Beasiswa 2026', 'Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.', '2026-07-02', 50, 'Pendidikan', NULL, 'publish', 1, 2, '2026-06-18 12:37:50'),
(6, 'Bootcamp Web Development', 'Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.', '2026-07-15', 50, 'Workshop', NULL, 'publish', 6, 6, '2026-06-18 12:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id_like` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_konten` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id_organisasi` int NOT NULL,
  `nama_organisasi` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis` enum('BEM','UKM','SC','Himpunan') COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id_organisasi`, `nama_organisasi`, `jenis`, `deskripsi`, `logo`, `created_at`) VALUES
(1, 'BEM ITH', 'BEM', 'Badan Eksekutif Mahasiswa Institut Teknologi Habibie', NULL, '2026-06-15 06:47:10'),
(2, 'UKM Robotik', 'UKM', 'Unit Kegiatan Mahasiswa Robotika', NULL, '2026-06-15 06:47:10'),
(6, 'Habibie Coding Club', 'SC', 'Klub pemrograman dan pengembangan perangkat lunak', NULL, '2026-06-15 23:34:36'),
(11, 'English Club ITH', 'UKM', 'Klub Bahasa Inggris Mahasiswa', NULL, '2026-06-15 23:34:36'),
(44, 'Himpunan Mahasiswa Ilmu Komputer', 'Himpunan', 'Himpunan Mahasiswa Program Studi S1 Ilmu Komputer / Informatika', NULL, '2026-06-15 23:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_konten` int NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pendaftaran` enum('menunggu','diterima','ditolak') COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `kuota_maks` int DEFAULT '50'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengurus_organisasi`
--

CREATE TABLE `pengurus_organisasi` (
  `id_pengurus` int NOT NULL,
  `id_organisasi` int DEFAULT NULL,
  `nama_pengurus` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Staff',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'Pengurus Departemen',
  `id_akses` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Belum',
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbmahasiswa`
--

CREATE TABLE `tbmahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `nim` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `prodi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kontak` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` enum('0','1') COLLATE utf8mb4_general_ci DEFAULT '0',
  `verification_token` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD UNIQUE KEY `kode_aspirasi` (`kode_aspirasi`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_organisasi_tujuan` (`id_organisasi_tujuan`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id_komentar`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_konten` (`id_konten`),
  ADD KEY `id_komentar_parent` (`id_komentar_parent`);

--
-- Indexes for table `komentar_aspirasi`
--
ALTER TABLE `komentar_aspirasi`
  ADD PRIMARY KEY (`id_komentar`),
  ADD KEY `fk_komentar_aspirasi` (`id_aspirasi`);

--
-- Indexes for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD PRIMARY KEY (`id_konten`),
  ADD KEY `id_organisasi` (`id_organisasi`);
ALTER TABLE `konten_kegiatan` ADD FULLTEXT KEY `ft_kegiatan` (`judul`,`deskripsi`,`kategori`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `id_mahasiswa` (`id_mahasiswa`,`id_konten`),
  ADD KEY `id_konten` (`id_konten`);

--
-- Indexes for table `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id_organisasi`);
ALTER TABLE `organisasi` ADD FULLTEXT KEY `ft_organisasi` (`nama_organisasi`,`deskripsi`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD UNIQUE KEY `id_mahasiswa` (`id_mahasiswa`,`id_konten`),
  ADD KEY `id_konten` (`id_konten`);

--
-- Indexes for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  ADD PRIMARY KEY (`id_pengurus`),
  ADD KEY `id_organisasi` (`id_organisasi`);
ALTER TABLE `pengurus_organisasi` ADD FULLTEXT KEY `ft_pengurus` (`nama_pengurus`);

--
-- Indexes for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id_komentar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `komentar_aspirasi`
--
ALTER TABLE `komentar_aspirasi`
  MODIFY `id_komentar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  MODIFY `id_pengurus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD CONSTRAINT `aspirasi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `aspirasi_ibfk_2` FOREIGN KEY (`id_organisasi_tujuan`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_3` FOREIGN KEY (`id_komentar_parent`) REFERENCES `komentar` (`id_komentar`) ON DELETE CASCADE;

--
-- Constraints for table `komentar_aspirasi`
--
ALTER TABLE `komentar_aspirasi`
  ADD CONSTRAINT `fk_komentar_aspirasi` FOREIGN KEY (`id_aspirasi`) REFERENCES `aspirasi` (`id_aspirasi`) ON DELETE CASCADE;

--
-- Constraints for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD CONSTRAINT `konten_kegiatan_ibfk_1` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE;

--
-- Constraints for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  ADD CONSTRAINT `pengurus_organisasi_ibfk_1` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
