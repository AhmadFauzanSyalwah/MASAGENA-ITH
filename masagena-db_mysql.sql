-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jun 2026 pada 19.12
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Prosedur
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
-- Struktur dari tabel `administrator`
--

CREATE TABLE `administrator` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `id_akses` varchar(20) DEFAULT NULL,
  `status_verifikasi` varchar(50) DEFAULT 'Belum',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int(11) NOT NULL,
  `isi_aspirasi` text NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_organisasi_tujuan` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'proses',
  `tanggapan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `komentar`
--

CREATE TABLE `komentar` (
  `id_komentar` int(11) NOT NULL,
  `isi_komentar` text NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_konten` int(11) NOT NULL,
  `id_komentar_parent` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `konten_kegiatan`
--

CREATE TABLE `konten_kegiatan` (
  `id_konten` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `status_publikasi` varchar(20) DEFAULT 'publish',
  `id_organisasi` int(11) NOT NULL,
  `id_pembuat` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `konten_kegiatan`
--

INSERT INTO `konten_kegiatan` (`id_konten`, `judul`, `deskripsi`, `tanggal_kegiatan`, `kategori`, `lampiran`, `status_publikasi`, `id_organisasi`, `id_pembuat`, `created_at`) VALUES
(1, 'Dies Natalis ITH ke-4', 'Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.', '2026-06-25', 'Acara Kampus', NULL, 'publish', 1, 2, '2026-06-18 12:37:50'),
(2, 'Sosialisasi Beasiswa 2026', 'Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.', '2026-07-02', 'Pendidikan', NULL, 'publish', 1, 2, '2026-06-18 12:37:50'),
(6, 'Bootcamp Web Development', 'Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.', '2026-07-15', 'Workshop', NULL, 'publish', 6, 6, '2026-06-18 12:37:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `likes`
--

CREATE TABLE `likes` (
  `id_like` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_konten` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `organisasi`
--

CREATE TABLE `organisasi` (
  `id_organisasi` int(11) NOT NULL,
  `nama_organisasi` varchar(100) NOT NULL,
  `jenis` enum('BEM','UKM','SC','Himpunan') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `organisasi`
--

INSERT INTO `organisasi` (`id_organisasi`, `nama_organisasi`, `jenis`, `deskripsi`, `logo`, `created_at`) VALUES
(1, 'BEM ITH', 'BEM', 'Badan Eksekutif Mahasiswa Institut Teknologi Habibie', NULL, '2026-06-15 06:47:10'),
(2, 'UKM Robotik', 'UKM', 'Unit Kegiatan Mahasiswa Robotika', NULL, '2026-06-15 06:47:10'),
(6, 'Habibie Coding Club', 'SC', 'Klub pemrograman dan pengembangan perangkat lunak', NULL, '2026-06-15 23:34:36'),
(11, 'English Club ITH', 'UKM', 'Klub Bahasa Inggris Mahasiswa', NULL, '2026-06-15 23:34:36'),
(44, 'Himpunan Mahasiswa Ilmu Komputer', 'Himpunan', 'Himpunan Mahasiswa Program Studi S1 Ilmu Komputer / Informatika', NULL, '2026-06-15 23:34:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_konten` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_pendaftaran` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengurus_organisasi`
--

CREATE TABLE `pengurus_organisasi` (
  `id_pengurus` int(11) NOT NULL,
  `id_organisasi` int(11) DEFAULT NULL,
  `nama_pengurus` varchar(150) NOT NULL,
  `jabatan` varchar(100) DEFAULT 'Staff',
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `level` varchar(30) DEFAULT 'Pengurus Departemen',
  `id_akses` varchar(20) DEFAULT NULL,
  `status_verifikasi` varchar(50) DEFAULT 'Belum',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbmahasiswa`
--

CREATE TABLE `tbmahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `is_verified` enum('0','1') DEFAULT '0',
  `verification_token` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_organisasi_tujuan` (`id_organisasi_tujuan`);

--
-- Indeks untuk tabel `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id_komentar`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_konten` (`id_konten`),
  ADD KEY `id_komentar_parent` (`id_komentar_parent`);

--
-- Indeks untuk tabel `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD PRIMARY KEY (`id_konten`),
  ADD KEY `id_organisasi` (`id_organisasi`);
ALTER TABLE `konten_kegiatan` ADD FULLTEXT KEY `ft_kegiatan` (`judul`,`deskripsi`,`kategori`);

--
-- Indeks untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `id_mahasiswa` (`id_mahasiswa`,`id_konten`),
  ADD KEY `id_konten` (`id_konten`);

--
-- Indeks untuk tabel `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id_organisasi`);
ALTER TABLE `organisasi` ADD FULLTEXT KEY `ft_organisasi` (`nama_organisasi`,`deskripsi`);

--
-- Indeks untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD UNIQUE KEY `id_mahasiswa` (`id_mahasiswa`,`id_konten`),
  ADD KEY `id_konten` (`id_konten`);

--
-- Indeks untuk tabel `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  ADD PRIMARY KEY (`id_pengurus`),
  ADD KEY `id_organisasi` (`id_organisasi`);
ALTER TABLE `pengurus_organisasi` ADD FULLTEXT KEY `ft_pengurus` (`nama_pengurus`);

--
-- Indeks untuk tabel `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `administrator`
--
ALTER TABLE `administrator`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id_komentar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  MODIFY `id_konten` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  MODIFY `id_pengurus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `tbmahasiswa`
--
ALTER TABLE `tbmahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD CONSTRAINT `aspirasi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `aspirasi_ibfk_2` FOREIGN KEY (`id_organisasi_tujuan`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_3` FOREIGN KEY (`id_komentar_parent`) REFERENCES `komentar` (`id_komentar`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `konten_kegiatan`
--
ALTER TABLE `konten_kegiatan`
  ADD CONSTRAINT `konten_kegiatan_ibfk_1` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbmahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_konten`) REFERENCES `konten_kegiatan` (`id_konten`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengurus_organisasi`
--
ALTER TABLE `pengurus_organisasi`
  ADD CONSTRAINT `pengurus_organisasi_ibfk_1` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
