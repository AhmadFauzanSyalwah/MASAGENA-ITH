-- =========================================================
-- UPDATE DATABASE UNTUK MODUL ASPIRASI DAN KRITIK MASAGENA-ITH
-- Jalankan file ini di phpMyAdmin pada database `masagena-ith`.
-- =========================================================

ALTER TABLE aspirasi
ADD COLUMN kode_aspirasi VARCHAR(30) NULL AFTER id_aspirasi;

ALTER TABLE aspirasi
MODIFY id_mahasiswa INT NULL;

ALTER TABLE aspirasi
ADD COLUMN id_organisasi INT NULL AFTER id_mahasiswa;

ALTER TABLE aspirasi
ADD COLUMN is_anonim TINYINT(1) NOT NULL DEFAULT 0 AFTER kategori;

UPDATE aspirasi
SET kode_aspirasi = CONCAT('ASP-', LPAD(id_aspirasi, 5, '0'))
WHERE kode_aspirasi IS NULL OR kode_aspirasi = '';

ALTER TABLE aspirasi
MODIFY kode_aspirasi VARCHAR(30) NOT NULL;

ALTER TABLE aspirasi
ADD UNIQUE KEY unique_kode_aspirasi (kode_aspirasi);

-- Data contoh organisasi agar dropdown tujuan aspirasi tidak kosong.
-- Jika organisasi sudah ada, bagian ini tidak menambah data dobel untuk nama yang sama.
INSERT INTO organisasi (nama_organisasi, deskripsi, logo)
SELECT 'BEM ITH', 'Badan Eksekutif Mahasiswa Institut Teknologi Bacharuddin Jusuf Habibie.', NULL
WHERE NOT EXISTS (SELECT 1 FROM organisasi WHERE nama_organisasi = 'BEM ITH');

INSERT INTO organisasi (nama_organisasi, deskripsi, logo)
SELECT 'UKM Kampus', 'Unit Kegiatan Mahasiswa tingkat kampus.', NULL
WHERE NOT EXISTS (SELECT 1 FROM organisasi WHERE nama_organisasi = 'UKM Kampus');

INSERT INTO organisasi (nama_organisasi, deskripsi, logo)
SELECT 'Himpunan Mahasiswa Ilmu Komputer', 'Organisasi mahasiswa Program Studi Ilmu Komputer.', NULL
WHERE NOT EXISTS (SELECT 1 FROM organisasi WHERE nama_organisasi = 'Himpunan Mahasiswa Ilmu Komputer');
