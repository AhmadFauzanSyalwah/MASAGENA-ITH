-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 05:38 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD PRIMARY KEY (`id_konten`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
