-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 02:47 AM
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

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`id_admin`, `username`, `nama_lengkap`, `password`, `no_hp`, `id_akses`, `status_verifikasi`, `reset_token`, `reset_expires`) VALUES
(1, 'admin_kampus', 'Administrator', '$2y$10$qu8S96J2sRC6WfI3Nb2VsupZ1.X.h05ORFfiBOed8zwNYRAGuDz6.', NULL, NULL, 'Belum', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int NOT NULL,
  `kode_aspirasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isi_aspirasi` text COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_anonim` tinyint(1) DEFAULT '0',
  `id_mahasiswa` int DEFAULT NULL,
  `id_organisasi` int DEFAULT NULL,
  `id_organisasi_tujuan` int DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'proses',
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggapan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `kode_aspirasi`, `judul`, `isi_aspirasi`, `kategori`, `is_anonim`, `id_mahasiswa`, `id_organisasi`, `id_organisasi_tujuan`, `status`, `tanggal`, `tanggapan`, `created_at`) VALUES
(1, 'ASP-260621080311-52', 'perbakan', 'tes', 'Kritik', 1, NULL, 1, NULL, 'proses', '2026-06-21 06:03:11', NULL, '2026-06-21 06:03:11'),
(2, 'ASP-260622020306-50', 'tes', 'testes', 'Kritik', 0, 3, 6, NULL, 'proses', '2026-06-22 00:03:06', NULL, '2026-06-22 00:03:06'),
(3, 'ASP-260622020910-36', 'contoh', 'tesktitik', 'Kritik', 1, NULL, 1, NULL, 'proses', '2026-06-22 00:09:10', NULL, '2026-06-22 00:09:10');

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

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id_komentar`, `isi_komentar`, `id_mahasiswa`, `id_konten`, `id_komentar_parent`, `created_at`) VALUES
(1, 'Wah, acaranya sangat menarik min! Nggak sabar buat ikutan.', 3, 2, NULL, '2026-06-16 00:23:37');

-- --------------------------------------------------------

--
-- Table structure for table `komentar_aspirasi`
--

CREATE TABLE `komentar_aspirasi` (
  `id_komentar_aspirasi` int NOT NULL,
  `id_aspirasi` int NOT NULL,
  `level_user` enum('admin','pengurus','mahasiswa') DEFAULT 'mahasiswa',
  `isi_komentar` text NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `komentar_aspirasi`
--

INSERT INTO `komentar_aspirasi` (`id_komentar_aspirasi`, `id_aspirasi`, `level_user`, `isi_komentar`, `tanggal`) VALUES
(1, 2, 'mahasiswa', 'ya', '2026-06-22 08:06:57'),
(2, 2, 'mahasiswa', 'ya', '2026-06-22 08:07:13'),
(3, 2, 'mahasiswa', 'ya', '2026-06-22 08:07:40'),
(4, 2, 'mahasiswa', 'wat', '2026-06-22 08:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `konten_kegiatan`
--

CREATE TABLE `konten_kegiatan` (
  `id_konten` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
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

INSERT INTO `konten_kegiatan` (`id_konten`, `judul`, `deskripsi`, `tanggal_kegiatan`, `kategori`, `lampiran`, `status_publikasi`, `id_organisasi`, `id_pembuat`, `created_at`) VALUES
(1, 'Dies Natalis ITH ke-4', 'Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.', '2026-06-25', 'Acara Kampus', 'dies.jfif', 'publish', 1, 2, '2026-06-18 12:37:50'),
(2, 'Sosialisasi Beasiswa 2026', 'Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.', '2026-07-02', 'Pendidikan', NULL, 'publish', 1, 2, '2026-06-18 12:37:50'),
(6, 'Bootcamp Web Development', 'Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.', '2026-07-15', 'Workshop', NULL, 'publish', 6, 6, '2026-06-18 12:37:50');

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

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id_like`, `id_mahasiswa`, `id_konten`, `created_at`) VALUES
(12, 3, 2, '2026-06-16 00:41:02');

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
  `kuota_maks` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_mahasiswa`, `id_konten`, `tanggal_daftar`, `status_pendaftaran`, `kuota_maks`) VALUES
(1, 3, 1, '2026-06-21 00:56:21', 'menunggu', 50),
(2, 3, 2, '2026-06-21 03:56:57', 'ditolak', 0),
(3, 3, 6, '2026-06-21 05:41:50', 'diterima', 50),
(4, 45, 2, '2026-06-22 00:02:07', 'menunggu', 50);

-- --------------------------------------------------------

--
-- Table structure for table `pengurus_organisasi`
--

CREATE TABLE `pengurus_organisasi` (
  `id_pengurus` int NOT NULL,
  `id_organisasi` int DEFAULT NULL,
  `nama_pengurus` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Anggota Inti',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_akses` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Belum',
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengurus_organisasi`
--

INSERT INTO `pengurus_organisasi` (`id_pengurus`, `id_organisasi`, `nama_pengurus`, `jabatan`, `password`, `no_hp`, `id_akses`, `status_verifikasi`, `reset_token`, `reset_expires`) VALUES
(2, 1, 'Budi Santoso', 'Ketua', '$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG', NULL, NULL, 'Belum', NULL, NULL),
(6, 6, 'Fajar Coding', 'Inti', '$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG', NULL, NULL, 'Belum', NULL, NULL);

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
  `angkatan` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kontak` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` enum('0','1') COLLATE utf8mb4_general_ci DEFAULT '0',
  `verification_token` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbmahasiswa`
--

INSERT INTO `tbmahasiswa` (`id_mahasiswa`, `nim`, `nama`, `email`, `password`, `prodi`, `angkatan`, `kontak`, `is_verified`, `verification_token`, `created_at`, `reset_token`, `reset_expires`) VALUES
(3, '241011001', 'Andi Prasetyo', 'andi@mahasiswa.ith.ac.id', '$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G', 'Ilmu Komputer', '2024', NULL, '1', NULL, '2026-06-18 12:37:50', NULL, NULL),
(45, '241011002', 'Rina Melati', 'rina.melati@ith.ac.id', '$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G', 'Sistem Informasi', '2024', NULL, '1', NULL, '2026-06-18 12:37:50', NULL, NULL);

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
  ADD PRIMARY KEY (`id_komentar_aspirasi`),
  ADD KEY `id_aspirasi` (`id_aspirasi`);

--
-- Indexes for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD PRIMARY KEY (`id_konten`),
  ADD KEY `id_organisasi` (`id_organisasi`);

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
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id_komentar_aspirasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  MODIFY `id_pengurus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
