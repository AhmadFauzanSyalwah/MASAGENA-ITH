CREATE DATABASE IF NOT EXISTS masagena_ith;
USE masagena_ith;

-- Tabel users
CREATE TABLE users (
                       id_user INT AUTO_INCREMENT PRIMARY KEY,
                       nama VARCHAR(100) NOT NULL,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       peran ENUM('mahasiswa','pengurus','admin') NOT NULL,
                       level ENUM('inti','biasa') DEFAULT 'biasa',
                       id_organisasi INT,
                       nim VARCHAR(20),
                       prodi VARCHAR(50),
                       angkatan VARCHAR(4),
                       status_verifikasi ENUM('pending','verified') DEFAULT 'pending',
                       verification_token VARCHAR(64),
                       remember_token VARCHAR(100),
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (id_organisasi) REFERENCES organisasi(id_organisasi) ON DELETE SET NULL
);

-- Tabel organisasi
CREATE TABLE organisasi (
                            id_organisasi INT AUTO_INCREMENT PRIMARY KEY,
                            nama_organisasi VARCHAR(100) NOT NULL,
                            jenis ENUM('BEM','UKM','SC','Himpunan') NOT NULL,
                            deskripsi TEXT,
                            logo VARCHAR(255),
                            id_user_ketua INT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            UNIQUE (nama_organisasi, jenis),
                            FOREIGN KEY (id_user_ketua) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Tabel konten_kegiatan
CREATE TABLE konten_kegiatan (
                                 id_konten INT AUTO_INCREMENT PRIMARY KEY,
                                 judul VARCHAR(255) NOT NULL,
                                 deskripsi TEXT NOT NULL,
                                 tanggal_kegiatan DATE,
                                 kategori VARCHAR(50),
                                 lampiran VARCHAR(255),
                                 status_publikasi VARCHAR(20) DEFAULT 'publish',
                                 id_organisasi INT NOT NULL,
                                 id_user_pembuat INT NOT NULL,
                                 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                 FOREIGN KEY (id_organisasi) REFERENCES organisasi(id_organisasi) ON DELETE CASCADE,
                                 FOREIGN KEY (id_user_pembuat) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Tabel komentar
CREATE TABLE komentar (
                          id_komentar INT AUTO_INCREMENT PRIMARY KEY,
                          isi_komentar TEXT NOT NULL,
                          id_user INT NOT NULL,
                          id_konten INT NOT NULL,
                          id_komentar_parent INT,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
                          FOREIGN KEY (id_konten) REFERENCES konten_kegiatan(id_konten) ON DELETE CASCADE,
                          FOREIGN KEY (id_komentar_parent) REFERENCES komentar(id_komentar) ON DELETE CASCADE
);

-- Tabel likes
CREATE TABLE likes (
                       id_like INT AUTO_INCREMENT PRIMARY KEY,
                       id_user INT NOT NULL,
                       id_konten INT NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       UNIQUE (id_user, id_konten),
                       FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
                       FOREIGN KEY (id_konten) REFERENCES konten_kegiatan(id_konten) ON DELETE CASCADE
);

-- Tabel pendaftaran
CREATE TABLE pendaftaran (
                             id_pendaftaran INT AUTO_INCREMENT PRIMARY KEY,
                             id_user INT NOT NULL,
                             id_konten INT NOT NULL,
                             tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                             status_pendaftaran ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
                             kuota_maks INT DEFAULT 0,
                             UNIQUE (id_user, id_konten),
                             FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
                             FOREIGN KEY (id_konten) REFERENCES konten_kegiatan(id_konten) ON DELETE CASCADE
);

-- Tabel aspirasi
CREATE TABLE aspirasi (
                          id_aspirasi INT AUTO_INCREMENT PRIMARY KEY,
                          isi_aspirasi TEXT NOT NULL,
                          id_user INT,
                          id_organisasi_tujuan INT NOT NULL,
                          status ENUM('terkirim','dibaca','direspons') DEFAULT 'terkirim',
                          tanggapan TEXT,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL,
                          FOREIGN KEY (id_organisasi_tujuan) REFERENCES organisasi(id_organisasi) ON DELETE CASCADE
);

-- Masukkan data dari file dump (sudah dikonversi ke INSERT)
-- Users
INSERT INTO users (id_user, nama, email, password, peran, level, id_organisasi, nim, prodi, angkatan, status_verifikasi) VALUES
                                                                                                                             (1,'Administrator','admin@ith.ac.id','$2y$10$qu8S96J2sRC6WfI3Nb2VsupZ1.X.h05ORFfiBOed8zwNYRAGuDz6.','admin','biasa',NULL,NULL,NULL,NULL,'verified'),
                                                                                                                             (2,'Budi Santoso','budi@bem.ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',1,NULL,NULL,NULL,'verified'),
                                                                                                                             (3,'Andi Prasetyo','andi@mahasiswa.ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011001','Ilmu Komputer','2024','verified'),
                                                                                                                             (4,'Dewi Robotik','dewi.robotik@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',2,NULL,NULL,NULL,'verified'),
                                                                                                                             (5,'Eko Robotika','eko.robotika@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',5,NULL,NULL,NULL,'verified'),
                                                                                                                             (6,'Fajar Coding','fajar.coding@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',6,NULL,NULL,NULL,'verified'),
                                                                                                                             (7,'Gita PKM','gita.pkm@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',7,NULL,NULL,NULL,'verified'),
                                                                                                                             (8,'Hana Seni','hana.seni@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',8,NULL,NULL,NULL,'verified'),
                                                                                                                             (9,'Irfan Futsal','irfan.futsal@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',9,NULL,NULL,NULL,'verified'),
                                                                                                                             (10,'Joko Catur','joko.catur@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',10,NULL,NULL,NULL,'verified'),
                                                                                                                             (11,'Kiki English','kiki.english@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',11,NULL,NULL,NULL,'verified'),
                                                                                                                             (12,'Lutfi Aljazari','lutfi.aljazari@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',12,NULL,NULL,NULL,'verified'),
                                                                                                                             (13,'Mira Basket','mira.basket@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',13,NULL,NULL,NULL,'verified'),
                                                                                                                             (14,'Nando Voli','nando.voli@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',14,NULL,NULL,NULL,'verified'),
                                                                                                                             (15,'Oki Bulutangkis','oki.bulutangkis@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',15,NULL,NULL,NULL,'verified'),
                                                                                                                             (16,'Putri Padus','putri.padus@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',16,NULL,NULL,NULL,'verified'),
                                                                                                                             (17,'Qori Teater','qori.teater@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',17,NULL,NULL,NULL,'verified'),
                                                                                                                             (18,'Rian Foto','rian.foto@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',18,NULL,NULL,NULL,'verified'),
                                                                                                                             (19,'Sari Jurnal','sari.jurnal@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',19,NULL,NULL,NULL,'verified'),
                                                                                                                             (20,'Tio Wirausaha','tio.wirausaha@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',20,NULL,NULL,NULL,'verified'),
                                                                                                                             (21,'Umar Pramuka','umar.pramuka@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',21,NULL,NULL,NULL,'verified'),
                                                                                                                             (22,'Vina Debat','vina.debat@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',22,NULL,NULL,NULL,'verified'),
                                                                                                                             (23,'Wahyu Islam','wahyu.islam@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',23,NULL,NULL,NULL,'verified'),
                                                                                                                             (24,'Ketua HMTI','ketua.hmti@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',24,NULL,NULL,NULL,'verified'),
                                                                                                                             (25,'Ketua HIMSAINS','ketua.himsains@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',25,NULL,NULL,NULL,'verified'),
                                                                                                                             (26,'Ketua Matematika','ketua.matematika@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',26,NULL,NULL,NULL,'verified'),
                                                                                                                             (27,'Ketua SI','ketua.si@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',27,NULL,NULL,NULL,'verified'),
                                                                                                                             (28,'Ketua Aktuaria','ketua.aktuaria@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',28,NULL,NULL,NULL,'verified'),
                                                                                                                             (29,'Ketua Sains Data','ketua.sainsdata@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',29,NULL,NULL,NULL,'verified'),
                                                                                                                             (30,'Ketua Bisnis Digital','ketua.bisnisdigital@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',30,NULL,NULL,NULL,'verified'),
                                                                                                                             (31,'Ketua Bioteknologi','ketua.biotek@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',31,NULL,NULL,NULL,'verified'),
                                                                                                                             (32,'Ketua Robotika AI','ketua.robotikaai@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',32,NULL,NULL,NULL,'verified'),
                                                                                                                             (33,'Ketua Tekpang','ketua.tekpang@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',33,NULL,NULL,NULL,'verified'),
                                                                                                                             (34,'Ketua Metalurgi','ketua.metalurgi@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',34,NULL,NULL,NULL,'verified'),
                                                                                                                             (35,'Ketua Energi','ketua.energi@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',35,NULL,NULL,NULL,'verified'),
                                                                                                                             (36,'Ketua Elektro','ketua.elektro@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',36,NULL,NULL,NULL,'verified'),
                                                                                                                             (37,'Ketua Industri','ketua.industri@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',37,NULL,NULL,NULL,'verified'),
                                                                                                                             (38,'Ketua Mesin','ketua.mesin@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',38,NULL,NULL,NULL,'verified'),
                                                                                                                             (39,'Ketua Sipil','ketua.sipil@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',39,NULL,NULL,NULL,'verified'),
                                                                                                                             (40,'Ketua Arsitektur','ketua.arsitektur@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',40,NULL,NULL,NULL,'verified'),
                                                                                                                             (41,'Ketua Perkapalan','ketua.perkapalan@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',41,NULL,NULL,NULL,'verified'),
                                                                                                                             (42,'Ketua Lingkungan','ketua.lingkungan@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',42,NULL,NULL,NULL,'verified'),
                                                                                                                             (43,'Ketua PWK','ketua.pwk@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',43,NULL,NULL,NULL,'verified'),
                                                                                                                             (44,'Ketua Ilkom','ketua.ilkom@ith.ac.id','$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG','pengurus','inti',44,NULL,NULL,NULL,'verified'),
                                                                                                                             (45,'Rina Melati','rina.melati@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011002','Sistem Informasi','2024','verified'),
                                                                                                                             (46,'Dimas Saputra','dimas.saputra@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011003','Bisnis Digital','2024','verified'),
                                                                                                                             (47,'Siska Aulia','siska.aulia@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011004','Sains Data','2024','verified'),
                                                                                                                             (48,'Arif Rahman','arif.rahman@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011005','Matematika','2024','verified'),
                                                                                                                             (49,'Dewi Lestari','dewi.lestari@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011006','Aktuaria','2024','verified'),
                                                                                                                             (50,'Bayu Prasetya','bayu.prasetya@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011007','Bioteknologi','2024','verified'),
                                                                                                                             (51,'Cindy Permata','cindy.permata@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011008','Teknik Robotika dan Kecerdasan Buatan','2024','verified'),
                                                                                                                             (52,'Eko Prasetyo','eko.prasetyo@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011009','Teknologi Pangan','2024','verified'),
                                                                                                                             (53,'Fitriani','fitriani@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011010','Teknik Metalurgi','2024','verified'),
                                                                                                                             (54,'Gilang Ramadhan','gilang.ramadhan@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011011','Teknik Sistem Energi','2024','verified'),
                                                                                                                             (55,'Hana Amalia','hana.amalia@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011012','Teknik Elektro','2024','verified'),
                                                                                                                             (56,'Irfan Maulana','irfan.maulana@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011013','Teknik Industri','2024','verified'),
                                                                                                                             (57,'Joko Widodo','joko.widodo@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011014','Teknik Mesin','2024','verified'),
                                                                                                                             (58,'Kartika Sari','kartika.sari@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011015','Teknik Sipil','2024','verified'),
                                                                                                                             (59,'Lutfi Hakim','lutfi.hakim@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011016','Arsitektur','2024','verified'),
                                                                                                                             (60,'Mega Utami','mega.utami@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011017','Teknik Perkapalan','2024','verified'),
                                                                                                                             (61,'Nanda Pratama','nanda.pratama@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011018','Teknik Lingkungan','2024','verified'),
                                                                                                                             (62,'Olivia Putri','olivia.putri@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011019','PWK','2024','verified'),
                                                                                                                             (63,'Pandu Winata','pandu.winata@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'241011020','Ilmu Komputer','2024','verified'),
                                                                                                                             (64,'Qonita Aulia','qonita.aulia@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011001','Sains Data','2025','verified'),
                                                                                                                             (65,'Rudi Hartono','rudi.hartono@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011002','Teknik Mesin','2025','verified'),
                                                                                                                             (66,'Santi Oktaviani','santi.oktaviani@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011003','Bioteknologi','2025','verified'),
                                                                                                                             (67,'Teguh Setiawan','teguh.setiawan@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011004','Teknik Elektro','2025','verified'),
                                                                                                                             (68,'Uswatun Hasanah','uswatun.hasanah@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011005','Sistem Informasi','2025','verified'),
                                                                                                                             (69,'Vera Andriani','vera.andriani@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011006','Matematika','2025','verified'),
                                                                                                                             (70,'Wahyu Setiawan','wahyu.setiawan@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011007','Teknik Industri','2025','verified'),
                                                                                                                             (71,'Xena Putri','xena.putri@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011008','Teknik Sipil','2025','verified'),
                                                                                                                             (72,'Yusuf Kurniawan','yusuf.kurniawan@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011009','Ilmu Komputer','2025','verified'),
                                                                                                                             (73,'Zahra Amalia','zahra.amalia@ith.ac.id','$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G','mahasiswa','biasa',NULL,'251011010','Arsitektur','2025','verified');

-- Organisasi
INSERT INTO organisasi (id_organisasi, nama_organisasi, jenis, deskripsi, logo, id_user_ketua, created_at) VALUES
                                                                                                               (1,'BEM ITH','BEM','Badan Eksekutif Mahasiswa Institut Teknologi Habibie',NULL,2,'2026-06-15 14:47:10'),
                                                                                                               (2,'UKM Robotik','UKM','Unit Kegiatan Mahasiswa Robotika',NULL,4,'2026-06-15 14:47:10'),
                                                                                                               (3,'Himpunan Ilmu Komputer','Himpunan','Himpunan Mahasiswa Program Studi Ilmu Komputer',NULL,NULL,'2026-06-15 14:47:10'),
                                                                                                               (5,'UKM Robotika','UKM','Unit Kegiatan Mahasiswa Robotika',NULL,5,'2026-06-16 07:34:36'),
                                                                                                               (6,'Habibie Coding Club','SC','Klub pemrograman dan pengembangan perangkat lunak',NULL,6,'2026-06-16 07:34:36'),
                                                                                                               (7,'PKM Center','UKM','Pusat Pengembangan Kreativitas Mahasiswa (PKM)',NULL,7,'2026-06-16 07:34:36'),
                                                                                                               (8,'ARATTA','UKM','Organisasi Kesenian Mahasiswa ITH',NULL,8,'2026-06-16 07:34:36'),
                                                                                                               (9,'Futsal ITH','UKM','Unit Kegiatan Mahasiswa Futsal',NULL,9,'2026-06-16 07:34:36'),
                                                                                                               (10,'Catur Club ITH','UKM','Klub Catur Institut Teknologi Habibie',NULL,10,'2026-06-16 07:34:36'),
                                                                                                               (11,'English Club ITH','UKM','Klub Bahasa Inggris Mahasiswa',NULL,11,'2026-06-16 07:34:36'),
                                                                                                               (12,'Aljazari','UKM','Klub Robotika dan Mekatronika Aljazari',NULL,12,'2026-06-16 07:34:36'),
                                                                                                               (13,'Basket ITH','UKM','Unit Kegiatan Mahasiswa Bola Basket',NULL,13,'2026-06-16 07:34:36'),
                                                                                                               (14,'Voli ITH','UKM','Unit Kegiatan Mahasiswa Bola Voli',NULL,14,'2026-06-16 07:34:36'),
                                                                                                               (15,'Bulu Tangkis ITH','UKM','Unit Kegiatan Mahasiswa Bulu Tangkis',NULL,15,'2026-06-16 07:34:36'),
                                                                                                               (16,'Paduan Suara Mahasiswa ITH','UKM','Unit Kegiatan Mahasiswa Paduan Suara',NULL,16,'2026-06-16 07:34:36'),
                                                                                                               (17,'Teater ITH','UKM','Unit Kegiatan Mahasiswa Teater',NULL,17,'2026-06-16 07:34:36'),
                                                                                                               (18,'Fotografi ITH','UKM','Klub Fotografi Mahasiswa',NULL,18,'2026-06-16 07:34:36'),
                                                                                                               (19,'Jurnalistik ITH','UKM','Klub Jurnalistik dan Media Kampus',NULL,19,'2026-06-16 07:34:36'),
                                                                                                               (20,'Kewirausahaan ITH','UKM','Unit Kegiatan Mahasiswa Kewirausahaan',NULL,20,'2026-06-16 07:34:36'),
                                                                                                               (21,'Pramuka ITH','UKM','Gugus Depan Pramuka Institut Teknologi Habibie',NULL,21,'2026-06-16 07:34:36'),
                                                                                                               (22,'Debat ITH','UKM','Klub Debat Bahasa Indonesia dan Inggris',NULL,22,'2026-06-16 07:34:36'),
                                                                                                               (23,'Kajian Islam ITH','UKM','Unit Kegiatan Mahasiswa Kerohanian Islam',NULL,23,'2026-06-16 07:34:36'),
                                                                                                               (24,'Himpunan Mahasiswa Teknologi Produksi dan Industri','Himpunan','Himpunan Mahasiswa Jurusan Teknologi Produksi dan Industri',NULL,24,'2026-06-16 07:34:36'),
                                                                                                               (25,'Himpunan Mahasiswa Sains','Himpunan','Himpunan Mahasiswa Jurusan Sains',NULL,25,'2026-06-16 07:34:36'),
                                                                                                               (26,'Himpunan Mahasiswa Matematika','Himpunan','Himpunan Mahasiswa Program Studi S1 Matematika / Sains Matematika',NULL,26,'2026-06-16 07:34:36'),
                                                                                                               (27,'Himpunan Mahasiswa Sistem Informasi','Himpunan','Himpunan Mahasiswa Program Studi S1 Sistem Informasi',NULL,27,'2026-06-16 07:34:36'),
                                                                                                               (28,'Himpunan Mahasiswa Aktuaria','Himpunan','Himpunan Mahasiswa Program Studi S1 Sains Aktuaria',NULL,28,'2026-06-16 07:34:36'),
                                                                                                               (29,'Himpunan Mahasiswa Sains Data','Himpunan','Himpunan Mahasiswa Program Studi S1 Sains Data',NULL,29,'2026-06-16 07:34:36'),
                                                                                                               (30,'Himpunan Mahasiswa Bisnis Digital','Himpunan','Himpunan Mahasiswa Program Studi S1 Bisnis Digital',NULL,30,'2026-06-16 07:34:36'),
                                                                                                               (31,'Himpunan Mahasiswa Bioteknologi','Himpunan','Himpunan Mahasiswa Program Studi S1 Bioteknologi',NULL,31,'2026-06-16 07:34:36'),
                                                                                                               (32,'Himpunan Mahasiswa Teknik Robotika dan AI','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Robotika dan Kecerdasan Buatan',NULL,32,'2026-06-16 07:34:36'),
                                                                                                               (33,'Himpunan Mahasiswa Teknologi Pangan','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknologi Pangan',NULL,33,'2026-06-16 07:34:36'),
                                                                                                               (34,'Himpunan Mahasiswa Teknik Metalurgi','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Metalurgi',NULL,34,'2026-06-16 07:34:36'),
                                                                                                               (35,'Himpunan Mahasiswa Teknik Sistem Energi','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Sistem Energi',NULL,35,'2026-06-16 07:34:36'),
                                                                                                               (36,'Himpunan Mahasiswa Teknik Elektro','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Elektro',NULL,36,'2026-06-16 07:34:36'),
                                                                                                               (37,'Himpunan Mahasiswa Teknik Industri','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Industri',NULL,37,'2026-06-16 07:34:36'),
                                                                                                               (38,'Himpunan Mahasiswa Teknik Mesin','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Mesin',NULL,38,'2026-06-16 07:34:36'),
                                                                                                               (39,'Himpunan Mahasiswa Teknik Sipil','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Sipil',NULL,39,'2026-06-16 07:34:36'),
                                                                                                               (40,'Himpunan Mahasiswa Arsitektur','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Arsitektur / Arsitektur',NULL,40,'2026-06-16 07:34:36'),
                                                                                                               (41,'Himpunan Mahasiswa Teknik Perkapalan','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Perkapalan',NULL,41,'2026-06-16 07:34:36'),
                                                                                                               (42,'Himpunan Mahasiswa Teknik Lingkungan','Himpunan','Himpunan Mahasiswa Program Studi S1 Teknik Lingkungan',NULL,42,'2026-06-16 07:34:36'),
                                                                                                               (43,'Himpunan Mahasiswa PWK','Himpunan','Himpunan Mahasiswa Program Studi S1 Perencanaan Wilayah dan Kota',NULL,43,'2026-06-16 07:34:36'),
                                                                                                               (44,'Himpunan Mahasiswa Ilmu Komputer','Himpunan','Himpunan Mahasiswa Program Studi S1 Ilmu Komputer / Informatika',NULL,44,'2026-06-16 07:34:36');

-- Konten kegiatan
INSERT INTO konten_kegiatan (id_konten, judul, deskripsi, tanggal_kegiatan, kategori, lampiran, status_publikasi, id_organisasi, id_user_pembuat, created_at) VALUES
                                                                                                                                                                  (1,'Dies Natalis ITH ke-4','Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.','2026-06-25','Acara Kampus','','publish',1,2,'2026-06-16 07:42:40'),
                                                                                                                                                                  (2,'Sosialisasi Beasiswa 2026','Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.','2026-07-02','Pendidikan','','publish',1,2,'2026-06-16 07:42:40'),
                                                                                                                                                                  (3,'Kongres Mahasiswa Tahunan','Pemilihan ketua BEM dan pembahasan program kerja tahunan.','2026-08-10','Acara Kampus','','publish',1,2,'2026-06-16 07:42:40'),
                                                                                                                                                                  (4,'Workshop Robotika Dasar','Belajar merakit dan memprogram robot sederhana menggunakan Arduino.','2026-07-10','Workshop','','publish',5,5,'2026-06-16 07:42:40'),
                                                                                                                                                                  (5,'Lomba Robot Line Follower','Kompetisi robot pengikut garis tingkat institut.','2026-08-20','Lomba','','publish',5,5,'2026-06-16 07:42:40'),
                                                                                                                                                                  (6,'Bootcamp Web Development','Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.','2026-07-15','Workshop','','publish',6,6,'2026-06-16 07:42:40'),
                                                                                                                                                                  (7,'Hackathon ITH 2026','Kompetisi coding 24 jam membangun aplikasi inovatif.','2026-08-25','Lomba','','publish',6,6,'2026-06-16 07:42:40'),
                                                                                                                                                                  (8,'English Debate Competition','Kompetisi debat bahasa Inggris antar mahasiswa ITH.','2026-08-15','Lomba','','publish',11,11,'2026-06-16 07:42:40'),
                                                                                                                                                                  (9,'TOEFL Preparation Class','Kelas persiapan TOEFL gratis untuk mahasiswa.','2026-07-20','Pendidikan','','publish',11,11,'2026-06-16 07:42:40'),
                                                                                                                                                                  (10,'Turnamen Basket 3x3','Turnamen bola basket tiga lawan tiga untuk seluruh mahasiswa.','2026-07-05','Olahraga','','publish',13,13,'2026-06-16 07:42:40'),
                                                                                                                                                                  (11,'Futsal Championship','Kejuaraan futsal antar program studi ITH.','2026-07-18','Olahraga','','publish',9,9,'2026-06-16 07:42:40'),
                                                                                                                                                                  (12,'Pentas Seni Tradisional','Pagelaran seni tari dan musik tradisional oleh anggota ARATTA.','2026-08-05','Seni','','publish',8,8,'2026-06-16 07:42:40'),
                                                                                                                                                                  (13,'Workshop Melukis Kanvas','Belajar teknik melukis di atas kanvas untuk pemula.','2026-07-12','Workshop','','publish',8,8,'2026-06-16 07:42:40'),
                                                                                                                                                                  (14,'Konser Amal "Suara Hati"','Konser paduan suara untuk penggalangan dana beasiswa.','2026-08-30','Seni','','publish',16,16,'2026-06-16 07:42:40'),
                                                                                                                                                                  (15,'Kemah Bakti Sosial','Kegiatan bakti sosial dan perkemahan di desa binaan.','2026-07-25','Sosial','','publish',21,21,'2026-06-16 07:42:40'),
                                                                                                                                                                  (16,'Seminar Start-Up Mahasiswa','Kiat sukses membangun start-up dari nol bersama founder ternama.','2026-08-12','Seminar','','publish',20,20,'2026-06-16 07:42:40'),
                                                                                                                                                                  (17,'Kajian Ramadhan & Buka Puasa Bersama','Kajian Islam mingguan selama Ramadhan dan buka puasa gratis.','2026-06-20','Keagamaan','','publish',23,23,'2026-06-16 07:42:40'),
                                                                                                                                                                  (18,'Pelatihan Public Speaking','Meningkatkan kemampuan berbicara di depan umum untuk mahasiswa.','2026-07-28','Workshop','','publish',22,22,'2026-06-16 07:42:40'),
                                                                                                                                                                  (19,'Seminar AI & Machine Learning','Pengenalan kecerdasan buatan dan implementasinya di dunia industri.','2026-08-22','Seminar','','publish',44,44,'2026-06-16 07:42:40');

-- Komentar
INSERT INTO komentar (id_komentar, isi_komentar, id_user, id_konten, id_komentar_parent, created_at) VALUES
    (1,'tes',3,2,NULL,'2026-06-16 08:23:37');

-- Likes
INSERT INTO likes (id_like, id_user, id_konten, created_at) VALUES
    (12,3,2,'2026-06-16 08:41:02');