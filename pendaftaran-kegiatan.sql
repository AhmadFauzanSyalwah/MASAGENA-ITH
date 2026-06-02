-- PATCH DATABASE MODUL PENDAFTARAN KEGIATAN MASAGENA-ITH
-- Jalankan file ini di phpMyAdmin pada database `masagena-ith`.

ALTER TABLE `konten_kegiatan`
ADD COLUMN IF NOT EXISTS `tanggal_kegiatan` DATE NULL AFTER `isi_kegiatan`;

ALTER TABLE `konten_kegiatan`
ADD COLUMN IF NOT EXISTS `kuota` INT NOT NULL DEFAULT 50 AFTER `tanggal_kegiatan`;

ALTER TABLE `konten_kegiatan`
ADD COLUMN IF NOT EXISTS `lokasi` VARCHAR(150) NULL AFTER `kuota`;

CREATE TABLE IF NOT EXISTS `pendaftaran_kegiatan` (
    `id_pendaftaran_kegiatan` INT AUTO_INCREMENT PRIMARY KEY,
    `id_mahasiswa` INT NULL,
    `id_konten` INT NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `nim` VARCHAR(20) NOT NULL,
    `program_studi` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `catatan_tambahan` TEXT NULL,
    `tanggal_daftar` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status_pendaftaran` ENUM('pending','diterima','ditolak') NOT NULL DEFAULT 'pending',
    UNIQUE KEY `unique_nim_kegiatan` (`nim`, `id_konten`),
    KEY `idx_id_mahasiswa` (`id_mahasiswa`),
    KEY `idx_id_konten` (`id_konten`),
    KEY `idx_status_pendaftaran` (`status_pendaftaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data contoh agar halaman kartu kegiatan langsung terlihat.
-- Aman dijalankan berkali-kali karena dicek berdasarkan judul kegiatan.

INSERT INTO `konten_kegiatan` (`judul_kegiatan`, `isi_kegiatan`, `tanggal_kegiatan`, `kuota`, `lokasi`, `foto`)
SELECT 'Seminar AI Modern', 'Implementasi teknologi Generative AI untuk produktivitas mahasiswa.', '2026-05-12', 50, 'Aula Kampus ITH', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `konten_kegiatan` WHERE `judul_kegiatan` = 'Seminar AI Modern'
);

INSERT INTO `konten_kegiatan` (`judul_kegiatan`, `isi_kegiatan`, `tanggal_kegiatan`, `kuota`, `lokasi`, `foto`)
SELECT 'Lomba Coding Nasional', 'Tunjukkan kemampuan problem-solving dalam tantangan algoritma.', '2026-05-12', 30, 'Laboratorium Komputer ITH', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `konten_kegiatan` WHERE `judul_kegiatan` = 'Lomba Coding Nasional'
);

INSERT INTO `konten_kegiatan` (`judul_kegiatan`, `isi_kegiatan`, `tanggal_kegiatan`, `kuota`, `lokasi`, `foto`)
SELECT 'Workshop UI / UX', 'Sesi intensif perancangan desain aplikasi yang user-centric.', '2026-05-12', 20, 'Ruang Seminar ITH', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `konten_kegiatan` WHERE `judul_kegiatan` = 'Workshop UI / UX'
);
