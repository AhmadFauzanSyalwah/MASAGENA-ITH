-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 05:55 AM
-- Server version: 10.4.32-MariaDB
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
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `id_akses` varchar(20) DEFAULT NULL,
  `status_verifikasi` enum('Belum','Terverifikasi') DEFAULT 'Belum',
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int(11) NOT NULL,
  `kode_aspirasi` varchar(30) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `id_organisasi` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `isi_aspirasi` text NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `is_anonim` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('proses','selesai','ditolak') NOT NULL DEFAULT 'proses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id_komentar` int(11) NOT NULL,
  `id_aspirasi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `level_user` enum('admin','mahasiswa') NOT NULL,
  `isi_komentar` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konten_kegiatan`
--

CREATE TABLE `konten_kegiatan` (
  `id_konten` int(11) NOT NULL,
  `id_organisasi` int(11) NOT NULL,
  `judul_kegiatan` varchar(255) NOT NULL,
  `isi_kegiatan` text NOT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
  `kuota` int(11) NOT NULL DEFAULT 50,
  `lokasi` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten_kegiatan`
--

INSERT INTO `konten_kegiatan` (`id_konten`, `id_organisasi`, `judul_kegiatan`, `isi_kegiatan`, `tanggal_kegiatan`, `kuota`, `lokasi`, `foto`, `tanggal_upload`) VALUES
(2, 0, 'Seminar AI Modern', 'Implementasi teknologi Generative AI untuk produktivitas mahasiswa.', '2026-05-12', 50, 'Aula Kampus ITH', NULL, '2026-06-04 11:34:35'),
(3, 0, 'Lomba Coding Nasional', 'Tunjukkan kemampuan problem-solving dalam tantangan algoritma.', '2026-05-12', 30, 'Laboratorium Komputer ITH', NULL, '2026-06-04 11:34:35'),
(4, 0, 'Workshop UI / UX', 'Sesi intensif perancangan desain aplikasi yang user-centric.', '2026-05-12', 20, 'Ruang Seminar ITH', NULL, '2026-06-04 11:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id_organisasi` int(11) NOT NULL,
  `nama_organisasi` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id_organisasi`, `nama_organisasi`, `deskripsi`, `logo`) VALUES
(7, 'BEM ITH', 'a', NULL),
(8, 'UKM Kampus', 'Unit Kegiatan Mahasiswa tingkat kampus.', NULL),
(9, 'Himpunan Mahasiswa Ilmu Komputer', 'Organisasi mahasiswa Program Studi Ilmu Komputer.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_organisasi` int(11) NOT NULL,
  `id_konten` int(11) NOT NULL,
  `tanggal_daftar` datetime NOT NULL DEFAULT current_timestamp(),
  `status_pendaftaran` enum('pending','diterima','ditolak') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_kegiatan`
--

CREATE TABLE `pendaftaran_kegiatan` (
  `id_pendaftaran_kegiatan` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `id_konten` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `catatan_tambahan` text DEFAULT NULL,
  `tanggal_daftar` datetime NOT NULL DEFAULT current_timestamp(),
  `status_pendaftaran` enum('pending','diterima','ditolak') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengurus_organisasi`
--

CREATE TABLE `pengurus_organisasi` (
  `id_pengurus` int(11) NOT NULL,
  `id_organisasi` int(11) NOT NULL,
  `nama_pengurus` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `id_akses` varchar(20) DEFAULT NULL,
  `status_verifikasi` enum('Belum','Terverifikasi') DEFAULT 'Belum',
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblike`
--

CREATE TABLE `tblike` (
  `id_like` int(11) NOT NULL,
  `id_aspirasi` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbmahasiswa`
--

CREATE TABLE `tbmahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` enum('0','1') NOT NULL DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD UNIQUE KEY `unique_kode_aspirasi` (`kode_aspirasi`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id_komentar`);

--
-- Indexes for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD PRIMARY KEY (`id_konten`);

--
-- Indexes for table `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id_organisasi`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`);

--
-- Indexes for table `pendaftaran_kegiatan`
--
ALTER TABLE `pendaftaran_kegiatan`
  ADD PRIMARY KEY (`id_pendaftaran_kegiatan`),
  ADD UNIQUE KEY `unique_nim_kegiatan` (`nim`,`id_konten`),
  ADD KEY `idx_id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `idx_id_konten` (`id_konten`),
  ADD KEY `idx_status_pendaftaran` (`status_pendaftaran`);

--
-- Indexes for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  ADD PRIMARY KEY (`id_pengurus`);

--
-- Indexes for table `tblike`
--
ALTER TABLE `tblike`
  ADD PRIMARY KEY (`id_like`);

--
-- Indexes for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id_komentar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pendaftaran_kegiatan`
--
ALTER TABLE `pendaftaran_kegiatan`
  MODIFY `id_pendaftaran_kegiatan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  MODIFY `id_pengurus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tblike`
--
ALTER TABLE `tblike`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
