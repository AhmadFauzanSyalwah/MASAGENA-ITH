-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260523.4225f36c1b
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 29, 2026 at 09:23 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

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
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_akses` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Belum',
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`id_admin`, `username`, `nama_lengkap`, `password`, `no_hp`, `id_akses`, `status_verifikasi`, `reset_token`, `reset_expires`) VALUES
(2, 'admin', 'Super Administrator', '$2y$10$Q7Jkz2R9tY6xWqTb3uE4uO5pQ6sT7uV8wX9yZ0aB1cD2eF3gH4iJ5kL6', '081234567890', 'full', 'Sudah', NULL, NULL),
(3, 'Fauzan', 'Ahmad Fauzan Syalwah', '$2y$10$pwKB93rRy6TkYGGY8.VE5OD3ny0i.5ObxWs7FiwTSdvXlxKBTkO4S', '081527068552', 'ADM-2026-2247', 'Terverifikasi', NULL, NULL),
(4, 'tes123', 'Ahmad Fauzan Syalwah', '$2y$10$fJU5KXGxXEwe/2.k7AvayOYlQfgMbdrwFG5H9S8IG34/cMbA12RXy', '081527058552', 'ADM-2026-8133', 'Terverifikasi', NULL, NULL),
(5, 'Ahmad Fauzan Syalwah', 'Ahmad Fauzan Syalwah', '$2y$10$MCurKYXWvw9jM03q6s8tIOy.FhuXGZXjyQG1TNJbcrcsJw4UXyfyy', '081527068552', 'ADM-2026-8553', 'Terverifikasi', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int NOT NULL,
  `kode_aspirasi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isi_aspirasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_anonim` tinyint(1) DEFAULT '0',
  `id_mahasiswa` int NOT NULL,
  `id_organisasi_tujuan` int DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'proses',
  `tanggapan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `kode_aspirasi`, `judul`, `isi_aspirasi`, `kategori`, `is_anonim`, `id_mahasiswa`, `id_organisasi_tujuan`, `status`, `tanggapan`, `created_at`) VALUES
(2, 'ASP-260629084114-18', 'tesQTES', 'TES', 'Kritik', 0, 62, 24, 'selesai', NULL, '2026-06-29 08:41:14');

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id_komentar` int NOT NULL,
  `isi_komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_konten` int NOT NULL,
  `id_komentar_parent` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id_komentar`, `isi_komentar`, `id_mahasiswa`, `id_konten`, `id_komentar_parent`, `created_at`) VALUES
(1, 'keren cuy', 62, 54, NULL, '2026-06-26 23:13:06'),
(2, 'gg', 62, 54, 1, '2026-06-26 23:13:28'),
(3, 'tes hehe boy', 62, 54, NULL, '2026-06-26 23:16:06'),
(5, 'halo', 62, 7, NULL, '2026-06-26 23:18:45'),
(6, 'iye ndi', 62, 7, 5, '2026-06-26 23:18:54'),
(7, 'hayuk', 62, 54, NULL, '2026-06-26 23:33:25'),
(8, 'whatsap', 62, 54, NULL, '2026-06-26 23:33:33'),
(9, 'tes123 nih boskuh', 62, 6, NULL, '2026-06-26 23:34:16'),
(10, 'wah keren', 62, 6, NULL, '2026-06-26 23:34:22'),
(11, 'gelo', 62, 6, 10, '2026-06-26 23:34:29'),
(12, 'TES', 62, 7, NULL, '2026-06-27 01:07:18'),
(13, 'HAYUK', 62, 7, 12, '2026-06-27 01:07:26'),
(14, 'Tes 123 jere pulang', 62, 48, NULL, '2026-06-28 04:54:01'),
(15, 'Tes 123 kapten jere pulang kampung', 62, 54, NULL, '2026-06-29 02:40:17'),
(16, 'Hayo;oh', 62, 54, 15, '2026-06-29 02:40:25'),
(17, 'Keren', 62, 9, NULL, '2026-06-29 06:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `komentar_aspirasi`
--

CREATE TABLE `komentar_aspirasi` (
  `id_komentar` int NOT NULL,
  `id_aspirasi` int NOT NULL,
  `level_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `isi_komentar` text COLLATE utf8mb4_general_ci NOT NULL,
  `id_admin` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konten_kegiatan`
--

CREATE TABLE `konten_kegiatan` (
  `id_konten` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lampiran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_publikasi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'publish',
  `id_organisasi` int NOT NULL,
  `id_pembuat` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `kuota_maks` int DEFAULT '50',
  `id_user_pembuat` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten_kegiatan`
--

INSERT INTO `konten_kegiatan` (`id_konten`, `judul`, `deskripsi`, `tanggal_kegiatan`, `kategori`, `lampiran`, `status_publikasi`, `id_organisasi`, `id_pembuat`, `created_at`, `kuota_maks`, `id_user_pembuat`) VALUES
(1, 'Lomba Robotik Nasional 2026', 'Kompetisi robotik antar perguruan tinggi se-Indonesia. Kategori: Line Follower, Sumo, dan Drone Race.', '2026-08-15', 'Kompetisi', 'robotika.jpg', 'publik', 1, 1, '2026-06-26 21:35:03', 50, 1),
(2, 'Workshop Robotika Dasar', 'Pelatihan dasar merakit dan memprogram robot menggunakan Arduino dan sensor.', '2026-07-10', 'Workshop', 'robotika.jpg', 'publik', 1, 1, '2026-06-26 21:35:03', 40, 1),
(3, 'Showcase Robotika ITH', 'Pameran hasil karya robotika mahasiswa ITH di hadapan industri dan akademisi.', '2026-09-05', 'Pameran', 'robotika.jpg', 'publik', 1, 1, '2026-06-26 21:35:03', 200, 1),
(4, 'Competitive Programming 101', 'Pelatihan algoritma dan struktur data untuk persiapan kompetisi programming nasional.', '2026-07-12', 'Pelatihan', 'coding.jpg', 'publik', 2, 1, '2026-06-26 21:35:03', 50, 1),
(5, 'Hackathon ITH 2026', 'Kompetisi coding 24 jam dengan tema solusi teknologi untuk masalah sosial.', '2026-08-20', 'Kompetisi', 'coding.jpg', 'publik', 2, 1, '2026-06-26 21:35:03', 80, 1),
(6, 'Web Development Bootcamp', 'Bootcamp full-stack web development selama 2 hari untuk pemula.', '2026-09-12', 'Workshop', 'coding.jpg', 'publik', 2, 1, '2026-06-26 21:35:03', 60, 1),
(7, 'Workshop Startup & Business Plan', 'Pelatihan membuat business plan dan strategi startup bagi mahasiswa pengusaha.', '2026-07-20', 'Workshop', 'wirausaha.jpg', 'publik', 6, 1, '2026-06-26 21:35:03', 60, 1),
(8, 'Pameran Produk UMKM Mahasiswa', 'Pameran produk usaha mahasiswa ITH untuk memperkenalkan produk ke masyarakat.', '2026-08-15', 'Pameran', 'wirausaha.jpg', 'publik', 6, 1, '2026-06-26 21:35:03', 100, 1),
(9, 'Webinar Digital Marketing', 'Webinar strategi digital marketing untuk meningkatkan penjualan UMKM.', '2026-09-10', 'Webinar', 'wirausaha.jpg', 'publik', 6, 1, '2026-06-26 21:35:03', 200, 1),
(10, 'Liga Futsal ITH 2026', 'Kompetisi futsal antar program studi se-ITH dengan sistem grup & knockout.', '2026-07-10', 'Kompetisi', 'futsal.jpg', 'publik', 7, 1, '2026-06-26 21:35:03', 120, 1),
(11, 'Pelatihan Futsal Dasar', 'Pelatihan teknik dasar futsal: passing, dribbling, dan shooting.', '2026-08-01', 'Pelatihan', 'futsal.jpg', 'publik', 7, 1, '2026-06-26 21:35:03', 40, 1),
(12, 'Futsal Friendly Match', 'Pertandingan persahabatan antara UKM Futsal ITH dengan tim dari kampus lain.', '2026-08-22', 'Sosial', 'futsal.jpg', 'publik', 7, 1, '2026-06-26 21:35:03', 50, 1),
(13, 'Kajian Islami Rutin', 'Kajian mingguan dengan tema akhlak dan kehidupan mahasiswa muslim.', '2026-07-05', 'Kajian', 'dakwah.jpg', 'publik', 3, 1, '2026-06-26 21:35:03', 50, 1),
(14, 'Buka Puasa Bersama & Santunan', 'Acara buka puasa bersama dan santunan untuk anak yatim dan dhuafa.', '2026-06-30', 'Sosial', 'dakwah.jpg', 'publik', 3, 1, '2026-06-26 21:35:03', 100, 1),
(15, 'Pelatihan Public Speaking Dakwah', 'Pelatihan teknik berbicara di depan umum dengan pendekatan dakwah islami.', '2026-08-20', 'Pelatihan', 'dakwah.jpg', 'publik', 3, 1, '2026-06-26 21:35:03', 30, 1),
(16, 'Seminar Matematika Terapan', 'Seminar tentang aplikasi matematika di bidang kecerdasan buatan dan keuangan.', '2026-07-16', 'Seminar', 'matematika.jpg', 'publik', 8, 1, '2026-06-26 21:35:03', 80, 1),
(17, 'Olimpiade Matematika Mahasiswa', 'Kompetisi matematika antar mahasiswa se-ITH.', '2026-08-12', 'Kompetisi', 'matematika.jpg', 'publik', 8, 1, '2026-06-26 21:35:03', 60, 1),
(18, 'Workshop Statistika dengan R', 'Pelatihan analisis data menggunakan bahasa pemrograman R.', '2026-09-05', 'Workshop', 'matematika.jpg', 'publik', 8, 1, '2026-06-26 21:35:03', 40, 1),
(19, 'Seminar Sistem Informasi', 'Seminar tentang tren sistem informasi dan transformasi digital.', '2026-07-20', 'Seminar', 'sistem_informasi.jpg', 'publik', 9, 1, '2026-06-26 21:35:03', 100, 1),
(20, 'Workshop Database Design', 'Pelatihan perancangan database relasional dan non-relasional.', '2026-08-10', 'Workshop', 'sistem_informasi.jpg', 'publik', 9, 1, '2026-06-26 21:35:03', 50, 1),
(21, 'Kompetisi UI/UX Design', 'Kompetisi desain antarmuka dan pengalaman pengguna untuk aplikasi mobile.', '2026-09-02', 'Kompetisi', 'sistem_informasi.jpg', 'publik', 9, 1, '2026-06-26 21:35:03', 40, 1),
(22, 'Workshop Data Science', 'Pelatihan dasar data science: Python, Pandas, dan visualisasi data.', '2026-07-18', 'Workshop', 'data_science.jpg', 'publik', 10, 1, '2026-06-26 21:35:03', 60, 1),
(23, 'Kompetisi Data Hackathon', 'Kompetisi mengolah data dan menghasilkan insight untuk masalah bisnis.', '2026-08-15', 'Kompetisi', 'data_science.jpg', 'publik', 10, 1, '2026-06-26 21:35:03', 50, 1),
(24, 'Seminar Big Data', 'Seminar tentang big data dan implementasinya di industri 4.0.', '2026-09-07', 'Seminar', 'data_science.jpg', 'publik', 10, 1, '2026-06-26 21:35:03', 120, 1),
(25, 'Seminar Aktuaria dan Asuransi', 'Seminar tentang profesi aktuaria dan peluang karir di bidang asuransi.', '2026-07-14', 'Seminar', 'aktuaria.jpg', 'publik', 11, 1, '2026-06-26 21:35:03', 60, 1),
(26, 'Workshop Analisis Risiko', 'Pelatihan analisis risiko menggunakan model statistik.', '2026-08-08', 'Workshop', 'aktuaria.jpg', 'publik', 11, 1, '2026-06-26 21:35:03', 40, 1),
(27, 'Kompetisi Aktuaria', 'Kompetisi kasus aktuaria untuk mahasiswa se-Indonesia.', '2026-09-10', 'Kompetisi', 'aktuaria.jpg', 'publik', 11, 1, '2026-06-26 21:35:03', 30, 1),
(28, 'Seminar Bioteknologi', 'Seminar tentang aplikasi bioteknologi di bidang kesehatan dan pertanian.', '2026-07-21', 'Seminar', 'bioteknologi.jpg', 'publik', 12, 1, '2026-06-26 21:35:03', 80, 1),
(29, 'Workshop Mikrobiologi', 'Pelatihan praktikum mikrobiologi dasar untuk mahasiswa.', '2026-08-12', 'Workshop', 'bioteknologi.jpg', 'publik', 12, 1, '2026-06-26 21:35:03', 30, 1),
(30, 'Kunjungan Laboratorium', 'Kunjungan ke laboratorium riset bioteknologi di institusi mitra.', '2026-09-01', 'Kunjungan', 'bioteknologi.jpg', 'publik', 12, 1, '2026-06-26 21:35:03', 20, 1),
(31, 'Seminar Algoritma AI', 'Seminar tentang algoritma kecerdasan buatan dan machine learning.', '2026-07-15', 'Seminar', 'ilkom.jpg', 'publik', 13, 1, '2026-06-26 21:35:03', 120, 1),
(32, 'Hackathon Ilmu Komputer', 'Kompetisi pemrograman 24 jam untuk mahasiswa ilmu komputer.', '2026-08-20', 'Kompetisi', 'ilkom.jpg', 'publik', 13, 1, '2026-06-26 21:35:03', 60, 1),
(33, 'Workshop Cyber Security', 'Pelatihan dasar keamanan siber dan ethical hacking.', '2026-09-05', 'Workshop', 'ilkom.jpg', 'publik', 13, 1, '2026-06-26 21:35:03', 40, 1),
(34, 'Seminar Teknologi Pangan', 'Seminar tentang inovasi pengolahan pangan dan keamanan pangan.', '2026-07-17', 'Seminar', 'pangan.jpg', 'publik', 14, 1, '2026-06-26 21:35:03', 80, 1),
(35, 'Workshop Pengolahan Pangan', 'Pelatihan pengolahan pangan menjadi produk siap jual.', '2026-08-10', 'Workshop', 'pangan.jpg', 'publik', 14, 1, '2026-06-26 21:35:03', 40, 1),
(36, 'Kunjungan Industri Pangan', 'Kunjungan ke pabrik pengolahan pangan untuk melihat proses produksi.', '2026-09-02', 'Kunjungan', 'pangan.jpg', 'publik', 14, 1, '2026-06-26 21:35:03', 30, 1),
(37, 'Seminar Metalurgi Ekstraktif', 'Seminar tentang proses ekstraksi logam dan teknologi pengolahan mineral.', '2026-07-22', 'Seminar', 'metalurgi.jpg', 'publik', 15, 1, '2026-06-26 21:35:03', 60, 1),
(38, 'Workshop Material Characterization', 'Pelatihan karakterisasi material menggunakan mikroskop dan spektroskopi.', '2026-08-15', 'Workshop', 'metalurgi.jpg', 'publik', 15, 1, '2026-06-26 21:35:03', 30, 1),
(39, 'Kunjungan Tambang', 'Kunjungan ke lokasi pertambangan untuk melihat proses penambangan dan pengolahan.', '2026-09-05', 'Kunjungan', 'metalurgi.jpg', 'publik', 15, 1, '2026-06-26 21:35:03', 20, 1),
(40, 'Seminar Energi Terbarukan', 'Seminar tentang energi terbarukan dan efisiensi energi.', '2026-07-20', 'Seminar', 'energi.jpg', 'publik', 16, 1, '2026-06-26 21:35:03', 80, 1),
(41, 'Workshop Sistem Tenaga', 'Pelatihan sistem tenaga listrik dan distribusi energi.', '2026-08-12', 'Workshop', 'energi.jpg', 'publik', 16, 1, '2026-06-26 21:35:03', 40, 1),
(42, 'Kunjungan PLTS', 'Kunjungan ke pembangkit listrik tenaga surya untuk studi lapangan.', '2026-09-07', 'Kunjungan', 'energi.jpg', 'publik', 16, 1, '2026-06-26 21:35:03', 25, 1),
(43, 'Seminar Konstruksi Berkelanjutan', 'Seminar tentang konstruksi ramah lingkungan dan material bangunan inovatif.', '2026-07-25', 'Seminar', 'sipil.jpg', 'publik', 17, 1, '2026-06-26 21:35:03', 100, 1),
(44, 'Workshop AutoCAD & BIM', 'Pelatihan perancangan struktur menggunakan AutoCAD dan BIM.', '2026-08-18', 'Workshop', 'sipil.jpg', 'publik', 17, 1, '2026-06-26 21:35:03', 50, 1),
(45, 'Kunjungan Proyek Konstruksi', 'Kunjungan ke proyek konstruksi besar di wilayah setempat.', '2026-09-10', 'Kunjungan', 'sipil.jpg', 'publik', 17, 1, '2026-06-26 21:35:03', 30, 1),
(46, 'Seminar Desain Arsitektur', 'Seminar tentang desain arsitektur modern dan konservasi bangunan.', '2026-07-28', 'Seminar', 'arsitektur.jpg', 'publik', 18, 1, '2026-06-26 21:35:03', 80, 1),
(47, 'Workshop 3D Modeling', 'Pelatihan pemodelan 3D menggunakan SketchUp dan Blender.', '2026-08-20', 'Workshop', 'arsitektur.jpg', 'publik', 18, 1, '2026-06-26 21:35:03', 40, 1),
(48, 'Kompetisi Desain Fasad', 'Kompetisi merancang fasad bangunan dengan tema kearifan lokal.', '2026-09-12', 'Kompetisi', 'arsitektur.jpg', 'publik', 18, 1, '2026-06-26 21:35:03', 30, 1),
(49, 'Seminar Teknologi Perkapalan', 'Seminar tentang desain kapal dan teknologi maritim.', '2026-07-22', 'Seminar', 'perkapalan.jpg', 'publik', 19, 1, '2026-06-26 21:35:03', 60, 1),
(50, 'Workshop Desain Kapal', 'Pelatihan desain kapal menggunakan software khusus.', '2026-08-15', 'Workshop', 'perkapalan.jpg', 'publik', 19, 1, '2026-06-26 21:35:03', 30, 1),
(51, 'Kunjungan Galangan Kapal', 'Kunjungan ke galangan kapal untuk melihat proses pembuatan kapal.', '2026-09-03', 'Kunjungan', 'perkapalan.jpg', 'publik', 19, 1, '2026-06-26 21:35:03', 20, 1),
(52, 'Seminar AI untuk Masa Depan', 'Seminar tentang kecerdasan buatan dan implementasinya di berbagai bidang.', '2026-07-18', 'Seminar', 'ai.jpg', 'publik', 20, 1, '2026-06-26 21:35:03', 120, 1),
(53, 'Workshop Machine Learning', 'Pelatihan machine learning menggunakan Python dan TensorFlow.', '2026-08-10', 'Workshop', 'ai.jpg', 'publik', 20, 1, '2026-06-26 21:35:03', 50, 1),
(54, 'Robot Competition AI', 'Kompetisi robot dengan kecerdasan buatan untuk menyelesaikan berbagai misi.', '2026-09-15', 'Kompetisi', 'ai.jpg', 'publik', 20, 1, '2026-06-26 21:35:03', 60, 1),
(55, 'Seminar Lean Manufacturing', 'Seminar tentang lean manufacturing dan efisiensi industri.', '2026-07-24', 'Seminar', 'industri.jpg', 'publik', 21, 1, '2026-06-26 21:35:03', 80, 1),
(56, 'Workshop Supply Chain', 'Pelatihan manajemen rantai pasok dan logistik.', '2026-08-18', 'Workshop', 'industri.jpg', 'publik', 21, 1, '2026-06-26 21:35:03', 40, 1),
(57, 'Kunjungan Pabrik Manufaktur', 'Kunjungan ke pabrik manufaktur untuk melihat proses produksi.', '2026-09-08', 'Kunjungan', 'industri.jpg', 'publik', 21, 1, '2026-06-26 21:35:03', 25, 1),
(58, 'Seminar Pengelolaan Limbah', 'Seminar tentang pengelolaan limbah dan teknologi ramah lingkungan.', '2026-07-26', 'Seminar', 'lingkungan.jpg', 'publik', 22, 1, '2026-06-26 21:35:03', 70, 1),
(59, 'Workshop Analisis Lingkungan', 'Pelatihan analisis kualitas udara, air, dan tanah.', '2026-08-20', 'Workshop', 'lingkungan.jpg', 'publik', 22, 1, '2026-06-26 21:35:03', 30, 1),
(60, 'Kunjungan Pengolahan Sampah', 'Kunjungan ke tempat pengolahan sampah terpadu.', '2026-09-05', 'Kunjungan', 'lingkungan.jpg', 'publik', 22, 1, '2026-06-26 21:35:03', 20, 1),
(61, 'Seminar Teknik Mesin Modern', 'Seminar tentang teknologi manufaktur dan otomotif terkini.', '2026-07-23', 'Seminar', 'mesin.jpg', 'publik', 23, 1, '2026-06-26 21:35:03', 80, 1),
(62, 'Workshop CAD/CAM', 'Pelatihan desain dan manufaktur berbantuan komputer.', '2026-08-15', 'Workshop', 'mesin.jpg', 'publik', 23, 1, '2026-06-26 21:35:03', 40, 1),
(63, 'Kunjungan Bengkel Otomotif', 'Kunjungan ke bengkel otomotif untuk melihat proses perbaikan dan perawatan.', '2026-09-06', 'Kunjungan', 'mesin.jpg', 'publik', 23, 1, '2026-06-26 21:35:03', 25, 1),
(68, 'Dies Natalis ITH ke-4', 'Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.', '2026-06-25', 'Acara Kampus', NULL, 'draft', 1, 2, '2026-06-18 12:37:50', 50, 1),
(69, 'Sosialisasi Beasiswa 2026', 'Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.', '2026-07-02', 'Pendidikan', NULL, 'publik', 1, 2, '2026-06-18 12:37:50', 50, 1),
(70, 'Bootcamp Web Development', 'Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.', '2026-07-15', 'Workshop', NULL, 'publish', 6, 6, '2026-06-18 12:37:50', 50, 1);

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
(15, 62, 54, '2026-06-26 23:12:52'),
(16, 62, 18, '2026-06-26 23:18:38'),
(18, 62, 7, '2026-06-26 23:19:06'),
(19, 62, 3, '2026-06-26 23:21:06'),
(20, 62, 10, '2026-06-26 23:50:50'),
(21, 62, 27, '2026-06-27 00:25:33'),
(22, 62, 9, '2026-06-27 00:25:36'),
(23, 62, 6, '2026-06-27 00:25:41'),
(24, 62, 48, '2026-06-27 00:25:43'),
(25, 62, 13, '2026-06-27 01:39:19'),
(26, 62, 63, '2026-06-27 03:10:57'),
(27, 62, 36, '2026-06-27 04:55:00'),
(28, 62, 45, '2026-06-28 04:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id_organisasi` int NOT NULL,
  `nama_organisasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis` enum('BEM','UKM','SC','Himpunan') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `visi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `misi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `pembina` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id_organisasi`, `nama_organisasi`, `jenis`, `deskripsi`, `visi`, `misi`, `pembina`, `logo`, `created_at`) VALUES
(1, 'UKM Robotika HERO', 'UKM', 'Unit Kegiatan Mahasiswa Robotika dan Kecerdasan Buatan - Mengembangkan inovasi di bidang robotika dan otomasi.', 'Menjadi pusat pengembangan robotika dan kecerdasan buatan terkemuka di tingkat nasional yang menghasilkan inovasi dan karya unggul.', '1. Mengembangkan minat dan bakat mahasiswa di bidang robotika dan kecerdasan buatan.\r\n2. Menyelenggarakan pelatihan, workshop, dan kompetisi robotika secara rutin.\r\n3. Menjalin kerjasama dengan institusi dan industri di bidang robotika.\r\n4. Menghasilkan karya inovatif yang bermanfaat bagi masyarakat.', 'PAK SYAFAAT', '', '2026-06-26 21:18:48'),
(2, 'Habibie Coding Club', 'SC', 'Study Club pemrograman dan pengembangan perangkat lunak - Wadah belajar coding, algoritma, dan teknologi terkini.', 'Menjadi wadah pengembangan kompetensi pemrograman dan teknologi digital bagi mahasiswa ITH.', '1. Menyelenggarakan pelatihan dan workshop pemrograman secara berkala.\n2. Mendorong partisipasi dalam kompetisi coding dan hackathon.\n3. Mengembangkan proyek-proyek teknologi yang bermanfaat.', 'Mardhiyyah Rafrin, S.Kom., M.T.', NULL, '2026-06-26 21:18:48'),
(3, 'UKM LDK Al-Jazari', 'UKM', 'Lembaga Dakwah Kampus Al-Jazari - Pengembangan dakwah dan kajian Islam di lingkungan ITH.', 'Menjadi lembaga dakwah kampus yang moderat, inspiratif, dan berkontribusi bagi pembentukan karakter mahasiswa.', '1. Menyelenggarakan kajian dan kegiatan dakwah yang inklusif.\n2. Mengembangkan potensi mahasiswa dalam bidang keislaman.\n3. Menjalin kerukunan antarumat beragama di kampus.', 'Hasan Basri, S.Ag., M.Pd.I.', NULL, '2026-06-26 21:18:48'),
(4, 'UKM Chess Club', 'SC', 'Unit Kegiatan Mahasiswa Catur - Mengembangkan bakat dan prestasi di bidang catur.', 'Menjadi pusat pengembangan bakat catur dan olahraga otak terbaik di ITH.', '1. Mengadakan latihan dan turnamen catur rutin.\n2. Meningkatkan prestasi catur mahasiswa ITH di tingkat regional dan nasional.', 'Abdullah Bora, S.E., M.Si.', NULL, '2026-06-26 21:18:48'),
(5, 'UKM English Club', 'SC', 'Unit Kegiatan Mahasiswa Bahasa Inggris - Meningkatkan kemampuan berbahasa Inggris dan debat.', 'Menjadi pusat pengembangan kemampuan berbahasa Inggris mahasiswa ITH untuk bersaing di tingkat global.', '1. Menyelenggarakan kursus dan pelatihan bahasa Inggris.\n2. Mengadakan English debate, speech, dan competition.\n3. Meningkatkan kepercayaan diri mahasiswa dalam berkomunikasi dalam bahasa Inggris.', 'Anugrayani Bustamin, S.Kom., M.T.', NULL, '2026-06-26 21:18:48'),
(6, 'UKM Kewirausahaan', 'UKM', 'Unit Kegiatan Mahasiswa Kewirausahaan - Membangun jiwa wirausaha daengembangkan usaha mahasiswa.', 'Menjadi pusat pengembangan jiwa kewirausahaan dan inovasi bisnis mahasiswa ITH.', '1. Menyelenggarakan pelatihan bisnis dan kewirausahaan.\r\n2. Mendorong mahasiswa untuk memulai usaha mandiri.\r\n3. Menjalin kerjasama dengan pelaku usaha dan UMKM.', 'Ir. Muhammad Syafaat, S.Kom., M.Kom.', '', '2026-06-26 21:18:48'),
(7, 'UKM Futsal', 'UKM', 'Unit Kegiatan Mahasiswa Futsal - Wadah pengembangan bakat olahraga futsal dan kompetisi.', 'Mewujudkan UKM Futsal sebagai wadah pengembangan bakat olahraga dan prestasi futsal di ITH.', '1. Mengadakan latihan dan turnamen futsal rutin.\n2. Membentuk tim futsal yang berprestasi.\n3. Menanamkan nilai sportivitas dan kerja sama tim.', 'Putri Mutia Monica, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(8, 'Himpunan Mahasiswa Matematika', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Matematika.', 'Menjadikan Himpunan Mahasiswa Matematika sebagai wadah pengembangan ilmu matematika dan aplikasinya.', '1. Mengembangkan minat dan bakat mahasiswa di bidang matematika.\n2. Menyelenggarakan seminar dan workshop matematika.\n3. Mempersiapkan mahasiswa untuk kompetisi matematika.', 'Zaitun, S.Si., M.Stat.', NULL, '2026-06-26 21:18:48'),
(9, 'Himpunan Mahasiswa Sistem Informasi', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Sistem Informasi.', 'Menjadikan Himpunan Mahasiswa Sistem Informasi sebagai pusat pengembangan sistem informasi dan teknologi.', '1. Mengembangkan minat dan bakat mahasiswa di bidang sistem informasi.\n2. Menyelenggarakan pelatihan dan workshop sistem informasi.\n3. Mengembangkan proyek sistem informasi untuk kebutuhan kampus dan masyarakat.', 'Muhammad Ikhwan Burhan, S.Kom., M.Kom.', NULL, '2026-06-26 21:18:48'),
(10, 'Himpunan Mahasiswa Sains Data', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Sains Data.', 'Menjadikan Himpunan Mahasiswa Sains Data sebagai pusat pengembangan data science dan analitik.', '1. Mengembangkan minat dan bakat mahasiswa di bidang sains data.\n2. Menyelenggarakan pelatihan data science dan analitik.\n3. Mengembangkan proyek data science untuk solusi nyata.', 'Hartina Husain, S.Si., M.Stat.', NULL, '2026-06-26 21:18:48'),
(11, 'Himpunan Mahasiswa Sains Aktuaria', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Sains Aktuaria.', 'Menjadikan Himpunan Mahasiswa Sains Aktuaria sebagai pusat pengembangan ilmu aktuaria dan keuangan.', '1. Mengembangkan minat dan bakat mahasiswa di bidang aktuaria.\n2. Menyelenggarakan seminar dan workshop aktuaria dan keuangan.\n3. Mempersiapkan mahasiswa untuk ujian profesi aktuaria.', 'Zaitun, S.Si., M.Stat.', NULL, '2026-06-26 21:18:48'),
(12, 'Himpunan Mahasiswa Bioteknologi', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Bioteknologi.', 'Menjadikan Himpunan Mahasiswa Bioteknologi sebagai pusat pengembangan bioteknologi dan inovasi hayati.', '1. Mengembangkan minat dan bakat mahasiswa di bidang bioteknologi.\n2. Menyelenggarakan penelitian dan pengembangan bioteknologi.\n3. Mengaplikasikan bioteknologi untuk pertanian dan kesehatan.', 'Ardi Manggala Putra, S.TP., M.Si.', NULL, '2026-06-26 21:18:48'),
(13, 'Himpunan Mahasiswa Ilmu Komputer', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Ilmu Komputer.', 'Menjadikan Himpunan Mahasiswa Ilmu Komputer sebagai pusat pengembangan ilmu komputer dan teknologi informasi.', '1. Mengembangkan minat dan bakat mahasiswa di bidang ilmu komputer.\n2. Menyelenggarakan pelatihan dan workshop teknologi informasi.\n3. Mengembangkan solusi teknologi untuk masyarakat.', 'Ir. Muhammad Syafaat, S.Kom., M.Kom.', NULL, '2026-06-26 21:18:48'),
(14, 'Himpunan Mahasiswa Teknologi Pangan', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknologi Pangan.', 'Menjadikan Himpunan Mahasiswa Teknologi Pangan sebagai pusat pengembangan teknologi pangan dan agribisnis.', '1. Mengembangkan minat dan bakat mahasiswa di bidang teknologi pangan.\n2. Menyelenggarakan penelitian dan pengembangan pangan.\n3. Mengaplikasikan teknologi pangan untuk ketahanan pangan.', 'Husnul Hatimah, S.TP., M.T.P.', NULL, '2026-06-26 21:18:48'),
(15, 'Himpunan Mahasiswa Teknik Metalurgi', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Metalurgi.', 'Menjadikan Himpunan Mahasiswa Teknik Metalurgi sebagai pusat pengembangan ilmu metalurgi dan material.', '1. Mengembangkan minat dan bakat mahasiswa di bidang metalurgi.\n2. Menyelenggarakan penelitian dan pengembangan material.\n3. Mengaplikasikan ilmu metalurgi untuk industri.', 'Khaera Tunnisa, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(16, 'Himpunan Mahasiswa Teknik Sistem Energi', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Sistem Energi.', 'Menjadikan Himpunan Mahasiswa Teknik Sistem Energi sebagai pusat pengembangan energi terbarukan dan sistem energi.', '1. Mengembangkan minat dan bakat mahasiswa di bidang energi.\n2. Menyelenggarakan penelitian dan pengembangan energi terbarukan.\n3. Mengaplikasikan sistem energi untuk keberlanjutan.', 'Maratuttahirah, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(17, 'Himpunan Mahasiswa Teknik Sipil', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Sipil.', 'Menjadikan Himpunan Mahasiswa Teknik Sipil sebagai pusat pengembangan teknik sipil dan infrastruktur.', '1. Mengembangkan minat dan bakat mahasiswa di bidang teknik sipil.\n2. Menyelenggarakan pelatihan dan workshop teknik sipil.\n3. Mengaplikasikan ilmu teknik sipil untuk infrastruktur.', 'Putri Mutia Monica, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(18, 'Himpunan Mahasiswa Teknik Arsitektur', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Arsitektur.', 'Menjadikan Himpunan Mahasiswa Teknik Arsitektur sebagai pusat pengembangan arsitektur dan desain.', '1. Mengembangkan minat dan bakat mahasiswa di bidang arsitektur.\n2. Menyelenggarakan pelatihan dan workshop arsitektur.\n3. Mengaplikasikan ilmu arsitektur untuk desain berkelanjutan.', 'Amirayanti, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(19, 'Himpunan Mahasiswa Teknik Perkapalan', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Perkapalan.', 'Menjadikan Himpunan Mahasiswa Teknik Perkapalan sebagai pusat pengembangan teknik perkapalan dan kelautan.', '1. Mengembangkan minat dan bakat mahasiswa di bidang perkapalan.\n2. Menyelenggarakan penelitian dan pengembangan perkapalan.\n3. Mengaplikasikan ilmu perkapalan untuk kemaritiman.', 'Putri Mutia Monica, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(20, 'Himpunan Mahasiswa Robotika dan Kecerdasan Buatan', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Robotika dan Kecerdasan Buatan.', 'Menjadikan Himpunan Mahasiswa Robotika dan Kecerdasan Buatan sebagai pusat pengembangan robotika dan AI.', '1. Mengembangkan minat dan bakat mahasiswa di bidang robotika dan AI.\n2. Menyelenggarakan pelatihan dan kompetisi robotika.\n3. Mengembangkan inovasi di bidang robotika dan AI.', 'Khaera Tunnisa, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(21, 'Himpunan Mahasiswa Teknik Industri', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Industri.', 'Menjadikan Himpunan Mahasiswa Teknik Industri sebagai pusat pengembangan teknik industri dan manajemen.', '1. Mengembangkan minat dan bakat mahasiswa di bidang teknik industri.\n2. Menyelenggarakan pelatihan dan workshop teknik industri.\n3. Mengaplikasikan ilmu teknik industri untuk efisiensi dan produktivitas.', 'Rosmiati, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(22, 'Himpunan Mahasiswa Teknik Lingkungan', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Lingkungan.', 'Menjadikan Himpunan Mahasiswa Teknik Lingkungan sebagai pusat pengembangan teknik lingkungan dan keberlanjutan.', '1. Mengembangkan minat dan bakat mahasiswa di bidang teknik lingkungan.\n2. Menyelenggarakan penelitian dan pengembangan lingkungan.\n3. Mengaplikasikan teknik lingkungan untuk keberlanjutan.', 'Maratuttahirah, S.T., M.T.', NULL, '2026-06-26 21:18:48'),
(23, 'Himpunan Mahasiswa Teknik Mesin', 'Himpunan', 'Wadah aspirasi dan pengembangan mahasiswa program studi Teknik Mesin.', 'Menjadikan Himpunan Mahasiswa Teknik Mesin sebagai pusat pengembangan teknik mesin dan manufaktur.', '1. Mengembangkan minat dan bakat mahasiswa di bidang teknik mesin.\n2. Menyelenggarakan penelitian dan pengembangan mesin.\n3. Mengaplikasikan ilmu teknik mesin untuk industri.', 'Ir. Muhammad Syafaat, S.Kom., M.Kom.', NULL, '2026-06-26 21:18:48'),
(24, 'BEM ITH', 'BEM', 'Badan Eksekutif Mahasiswa Institut Teknologi Bacharuddin Jusuf Habibie - Wadah aspirasi dan penggerak kegiatan kemahasiswaan tingkat institut.', 'Menjadikan BEM ITH sebagai lembaga eksekutif yang aspiratif, inovatif, dan berintegritas.', '1. Menampung dan menyalurkan aspirasi mahasiswa.\r\n2. Menyelenggarakan program kerja yang bermanfaat bagi mahasiswa.\r\n3. Menjalin komunikasi yang baik dengan seluruh elemen kampus.', 'Rektor ITH', '', '2026-06-26 21:19:12'),
(25, 'UKM ARATTA', 'UKM', 'Unit Kegiatan Mahasiswa Seni dan Budaya - Mengembangkan bakat seni tari, musik, teater, dan seni rupa.', 'Menjadikan UKM ARATTA sebagai pusat pengembangan seni dan budaya mahasiswa ITH.', '1. Mengembangkan minat dan bakat mahasiswa di bidang seni dan budaya.\n2. Menyelenggarakan pentas seni dan kegiatan budaya.\n3. Melestarikan dan mengembangkan budaya lokal.', 'Anugrayani Bustamin, S.Kom., M.T.', NULL, '2026-06-26 21:19:12'),
(26, 'SC PKM Center', 'SC', 'Study Club Pusat Pengembangan Karya dan Inovasi - Membimbing mahasiswa dalam riset, kompetisi ilmiah, dan pengembangan karya.', NULL, NULL, NULL, NULL, '2026-06-26 21:19:12'),
(27, 'Himpunan Mahasiswa Sains', 'Himpunan', 'Wadah pengembangan dan aspirasi mahasiswa program studi Sains (Fisika, Kimia, Biologi).', NULL, NULL, NULL, NULL, '2026-06-26 21:19:12'),
(28, 'Himpunan Mahasiswa TPI', 'Himpunan', 'Wadah pengembangan dan aspirasi mahasiswa program studi Teknologi Produksi Industri.', NULL, NULL, NULL, NULL, '2026-06-26 21:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_konten` int NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pendaftaran` enum('menunggu','diterima','ditolak','batal') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `kuota_maks` int DEFAULT '50'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_mahasiswa`, `id_konten`, `tanggal_daftar`, `status_pendaftaran`, `kuota_maks`) VALUES
(4, 62, 1, '2026-06-28 20:58:35', 'diterima', 50),
(13, 62, 9, '2026-06-29 02:27:41', 'diterima', 50),
(16, 62, 54, '2026-06-29 06:49:54', 'menunggu', 50),
(17, 62, 42, '2026-06-29 06:52:36', 'menunggu', 50);

-- --------------------------------------------------------

--
-- Table structure for table `pengurus_organisasi`
--

CREATE TABLE `pengurus_organisasi` (
  `id_pengurus` int NOT NULL,
  `id_organisasi` int DEFAULT NULL,
  `nama_pengurus` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Staff',
  `periode_mulai` date DEFAULT NULL,
  `periode_selesai` date DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pengurus Departemen',
  `id_akses` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Belum',
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengurus_organisasi`
--

INSERT INTO `pengurus_organisasi` (`id_pengurus`, `id_organisasi`, `nama_pengurus`, `jabatan`, `periode_mulai`, `periode_selesai`, `password`, `no_hp`, `level`, `id_akses`, `status_verifikasi`, `reset_token`, `reset_expires`) VALUES
(1, 1, 'Andi Pratama', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567801', 'Pengurus Inti', 'HERO-001', 'Terverifikasi', NULL, NULL),
(2, 1, 'Siti Aisyah', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567802', 'Pengurus Inti', 'HERO-002', 'Terverifikasi', NULL, NULL),
(3, 1, 'Budi Santoso', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567803', 'Pengurus Inti', 'HERO-003', 'Terverifikasi', NULL, NULL),
(4, 1, 'Rina Marlina', 'Kepala Divisi Riset', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567804', 'Pengurus Departemen', 'HERO-004', 'Terverifikasi', NULL, NULL),
(8, 2, 'Fauzan Ramadhan', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567805', 'Pengurus Inti', 'HCC-001', 'Terverifikasi', NULL, NULL),
(9, 2, 'Ririn Andini', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567806', 'Pengurus Inti', 'HCC-002', 'Terverifikasi', NULL, NULL),
(10, 2, 'Deni Saputra', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567807', 'Pengurus Inti', 'HCC-003', 'Terverifikasi', NULL, NULL),
(11, 2, 'Maya Sari', 'Kepala Divisi Web', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567808', 'Pengurus Departemen', 'HCC-004', 'Terverifikasi', NULL, NULL),
(15, 3, 'Abdul Rahman', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567809', 'Pengurus Inti', 'LDK-001', 'Terverifikasi', NULL, NULL),
(16, 3, 'Fatimah Azzahra', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567810', 'Pengurus Inti', 'LDK-002', 'Terverifikasi', NULL, NULL),
(17, 3, 'Hasan Basri', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567811', 'Pengurus Inti', 'LDK-003', 'Terverifikasi', NULL, NULL),
(18, 3, 'Nur Aini', 'Kepala Divisi Kajian', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567812', 'Pengurus Departemen', 'LDK-004', 'Terverifikasi', NULL, NULL),
(22, 6, 'Irfan Hidayat', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567819', 'Pengurus Inti', 'WIRA-001', 'Terverifikasi', NULL, NULL),
(23, 6, 'Yuniarti', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567820', 'Pengurus Inti', 'WIRA-002', 'Terverifikasi', NULL, NULL),
(24, 6, 'Rahmat Hidayat', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567821', 'Pengurus Inti', 'WIRA-003', 'Terverifikasi', NULL, NULL),
(25, 7, 'Rudi Hartono', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567822', 'Pengurus Inti', 'FUTSAL-001', 'Terverifikasi', NULL, NULL),
(26, 7, 'Bambang Setiawan', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567823', 'Pengurus Inti', 'FUTSAL-002', 'Terverifikasi', NULL, NULL),
(27, 7, 'Hendra Gunawan', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567824', 'Pengurus Inti', 'FUTSAL-003', 'Terverifikasi', NULL, NULL),
(28, 8, 'Dedy Kurniawan', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567825', 'Pengurus Inti', 'MATH-001', 'Terverifikasi', NULL, NULL),
(29, 8, 'Nur Hasanah', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567826', 'Pengurus Inti', 'MATH-002', 'Terverifikasi', NULL, NULL),
(30, 8, 'Eko Wibowo', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567827', 'Pengurus Inti', 'MATH-003', 'Terverifikasi', NULL, NULL),
(31, 9, 'Aditya Wijaya', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567828', 'Pengurus Inti', 'SI-001', 'Terverifikasi', NULL, NULL),
(32, 9, 'Sari Wulandari', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567829', 'Pengurus Inti', 'SI-002', 'Terverifikasi', NULL, NULL),
(33, 9, 'Rudi Hartanto', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567830', 'Pengurus Inti', 'SI-003', 'Terverifikasi', NULL, NULL),
(34, 10, 'Fajar Nugroho', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567831', 'Pengurus Inti', 'DATA-001', 'Terverifikasi', NULL, NULL),
(35, 10, 'Dewi Anggraini', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567832', 'Pengurus Inti', 'DATA-002', 'Terverifikasi', NULL, NULL),
(36, 10, 'Putra Perdana', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567833', 'Pengurus Inti', 'DATA-003', 'Terverifikasi', NULL, NULL),
(37, 11, 'Agus Salim', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567834', 'Pengurus Inti', 'AKT-001', 'Terverifikasi', NULL, NULL),
(38, 11, 'Erna Wahyuni', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567835', 'Pengurus Inti', 'AKT-002', 'Terverifikasi', NULL, NULL),
(39, 11, 'Muhammad Ikhsan', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567836', 'Pengurus Inti', 'AKT-003', 'Terverifikasi', NULL, NULL),
(40, 12, 'Farid Maulana', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567837', 'Pengurus Inti', 'BIO-001', 'Terverifikasi', NULL, NULL),
(41, 12, 'Lina Sari', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567838', 'Pengurus Inti', 'BIO-002', 'Terverifikasi', NULL, NULL),
(42, 12, 'Taufik Hidayat', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567839', 'Pengurus Inti', 'BIO-003', 'Terverifikasi', NULL, NULL),
(43, 13, 'Rizal Firmansyah', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567840', 'Pengurus Inti', 'ILKOM-001', 'Terverifikasi', NULL, NULL),
(44, 13, 'Cahaya Rizki', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567841', 'Pengurus Inti', 'ILKOM-002', 'Terverifikasi', NULL, NULL),
(45, 13, 'Yuda Pratama', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567842', 'Pengurus Inti', 'ILKOM-003', 'Terverifikasi', NULL, NULL),
(46, 14, 'Rina Kurniawati', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567843', 'Pengurus Inti', 'TP-001', 'Terverifikasi', NULL, NULL),
(47, 14, 'Doni Irawan', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567844', 'Pengurus Inti', 'TP-002', 'Terverifikasi', NULL, NULL),
(48, 14, 'Eka Yulianto', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567845', 'Pengurus Inti', 'TP-003', 'Terverifikasi', NULL, NULL),
(49, 15, 'Arief Setiawan', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567846', 'Pengurus Inti', 'MET-001', 'Terverifikasi', NULL, NULL),
(50, 15, 'Tuti Handayani', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567847', 'Pengurus Inti', 'MET-002', 'Terverifikasi', NULL, NULL),
(51, 15, 'Guntur Purnama', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567848', 'Pengurus Inti', 'MET-003', 'Terverifikasi', NULL, NULL),
(52, 16, 'Ridho Fathoni', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567849', 'Pengurus Inti', 'ENERGI-001', 'Terverifikasi', NULL, NULL),
(53, 16, 'Yuli Astuti', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567850', 'Pengurus Inti', 'ENERGI-002', 'Terverifikasi', NULL, NULL),
(54, 16, 'Darma Wijaya', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567851', 'Pengurus Inti', 'ENERGI-003', 'Terverifikasi', NULL, NULL),
(55, 17, 'Bima Sakti', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567852', 'Pengurus Inti', 'SIPIL-001', 'Terverifikasi', NULL, NULL),
(56, 17, 'Winda Kirana', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567853', 'Pengurus Inti', 'SIPIL-002', 'Terverifikasi', NULL, NULL),
(57, 17, 'Haris Taufik', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567854', 'Pengurus Inti', 'SIPIL-003', 'Terverifikasi', NULL, NULL),
(58, 18, 'Gilang Ramadhan', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567855', 'Pengurus Inti', 'ARS-001', 'Terverifikasi', NULL, NULL),
(59, 18, 'Puspita Sari', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567856', 'Pengurus Inti', 'ARS-002', 'Terverifikasi', NULL, NULL),
(60, 18, 'Ivan Salim', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567857', 'Pengurus Inti', 'ARS-003', 'Terverifikasi', NULL, NULL),
(61, 19, 'Dwi Prasetyo', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567858', 'Pengurus Inti', 'KAPAL-001', 'Terverifikasi', NULL, NULL),
(62, 19, 'Nita Andriani', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567859', 'Pengurus Inti', 'KAPAL-002', 'Terverifikasi', NULL, NULL),
(63, 19, 'Krisna Murti', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567860', 'Pengurus Inti', 'KAPAL-003', 'Terverifikasi', NULL, NULL),
(64, 20, 'Abdullah Rahman', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567861', 'Pengurus Inti', 'RKB-001', 'Terverifikasi', NULL, NULL),
(65, 20, 'Nadia Putri', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567862', 'Pengurus Inti', 'RKB-002', 'Terverifikasi', NULL, NULL),
(66, 20, 'Rio Hermawan', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567863', 'Pengurus Inti', 'RKB-003', 'Terverifikasi', NULL, NULL),
(67, 21, 'Surya Adi', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567864', 'Pengurus Inti', 'INDUSTRI-001', 'Terverifikasi', NULL, NULL),
(68, 21, 'Rina Rosalina', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567865', 'Pengurus Inti', 'INDUSTRI-002', 'Terverifikasi', NULL, NULL),
(69, 21, 'Toni Kurniawan', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567866', 'Pengurus Inti', 'INDUSTRI-003', 'Terverifikasi', NULL, NULL),
(70, 22, 'Fajar Akbar', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567867', 'Pengurus Inti', 'LING-001', 'Terverifikasi', NULL, NULL),
(71, 22, 'Sri Wahyuni', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567868', 'Pengurus Inti', 'LING-002', 'Terverifikasi', NULL, NULL),
(72, 22, 'Hadi Suryanto', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567869', 'Pengurus Inti', 'LING-003', 'Terverifikasi', NULL, NULL),
(73, 23, 'Hendra Saputra', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567870', 'Pengurus Inti', 'MESIN-001', 'Terverifikasi', NULL, NULL),
(74, 23, 'Lestari Handayani', 'Sekretaris', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567871', 'Pengurus Inti', 'MESIN-002', 'Terverifikasi', NULL, NULL),
(75, 23, 'Imam Santoso', 'Bendahara', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567872', 'Pengurus Inti', 'MESIN-003', 'Terverifikasi', NULL, NULL),
(76, 1, 'Ahmad Fauzan', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081527068552', 'Pengurus Inti', 'ROBOT-001', 'Terverifikasi', NULL, NULL),
(77, 24, 'Fikri Nasution', 'Ketua', '2025-01-01', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Pengurus Inti', 'BEM-001', 'Terverifikasi', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbmahasiswa`
--

CREATE TABLE `tbmahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `nim` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prodi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kontak` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `verification_token` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbmahasiswa`
--

INSERT INTO `tbmahasiswa` (`id_mahasiswa`, `nim`, `nama`, `email`, `password`, `prodi`, `kontak`, `foto_profil`, `is_verified`, `verification_token`, `created_at`, `reset_token`, `reset_expires`) VALUES
(55, '241011087', 'Arya Ahmad', 'afriandata34@gmail.com', '$2y$10$/d1unAbDzGLXrUcCS5KyieivlxaW68WH6RxyC.RKY/yfLiqmzOEX2', 'Ilmu Komputer', '087862394435', NULL, '1', NULL, '2026-06-19 05:37:48', '802914', '2026-06-21 13:29:33'),
(57, '241011088', 'Dewi Lestari', 'dewi@ith.ac.id', '$2y$10$/d1unAbDzGLXrUcCS5KyieivlxaW68WH6RxyC.RKY/yfLiqmzOEX2', 'Sistem Informasi', '087812345678', NULL, '0', '654321', '2026-06-20 09:04:49', NULL, NULL),
(58, '241011089', 'Rina Fitriani', 'rina@ith.ac.id', '$2y$10$B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7', 'Teknik Informatika', '085612345678', NULL, '1', NULL, '2026-06-20 09:04:49', NULL, NULL),
(59, '241011099', 'Mahasiswa Test', 'mahasiswa@test.com', '$2y$10$/d1unAbDzGLXrUcCS5KyieivlxaW68WH6RxyC.RKY/yfLiqmzOEX2', 'Ilmu Komputer', '081234567890', NULL, '0', '683974', '2026-06-21 16:59:40', NULL, NULL),
(61, '241011057', 'Fauzan', 'ahmadfauzansyalwah209gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ilmu Komputer', '081234567890', NULL, '0', NULL, '2026-06-21 22:30:35', NULL, NULL),
(62, '24101057', 'Ahmad Fauzan Syalwah', 'ahmadfauzansyalwah209@gmail.com', '$2y$10$nAlLFSxhIeOOzrB9q2O0zeZEKZPmFmMBy7e9BcLZQyzNf9AXvWY/u', 'Ilmu Komputer', '081527068552', NULL, '1', NULL, '2026-06-23 05:05:13', NULL, NULL);

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
  ADD UNIQUE KEY `idx_kode_aspirasi` (`kode_aspirasi`),
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
  ADD KEY `fk_komentar_aspirasi` (`id_aspirasi`),
  ADD KEY `fk_komentar_aspirasi_admin` (`id_admin`);

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
  MODIFY `id_aspirasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id_komentar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `komentar_aspirasi`
--
ALTER TABLE `komentar_aspirasi`
  MODIFY `id_komentar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  MODIFY `id_pengurus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

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
  ADD CONSTRAINT `fk_komentar_aspirasi` FOREIGN KEY (`id_aspirasi`) REFERENCES `aspirasi` (`id_aspirasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_komentar_aspirasi_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrator` (`id_admin`) ON DELETE SET NULL;

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
