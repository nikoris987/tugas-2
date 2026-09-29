-- ============================================================
-- SIMATIK - Universitas Semantik (hasil normalisasi 3NF)
-- Tabel: admin, akun_prodi, fakultas, program_studi, mahasiswa
--
-- PERHATIAN: file ini MENGHAPUS lalu membuat ulang semua tabel.
-- Kalau database sudah berisi data, pakai migrasi_role.sql saja.
-- Import setelah memilih database di phpMyAdmin (tanpa CREATE DATABASE).
--
-- Akun contoh:
--   Admin     : admin      / admin123
--   Prodi     : prodi_ti, prodi_sip, prodi_mjm / prodi123
--   Mahasiswa : NPM 2355201001 s/d 2355201005 / mahasiswa123
-- GANTI semua password ini sebelum dipakai sungguhan.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS akun_prodi;
DROP TABLE IF EXISTS program_studi;
DROP TABLE IF EXISTS fakultas;
DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50) UNIQUE NOT NULL,
    password  VARCHAR(255) NOT NULL
);

CREATE TABLE fakultas (
    kode_fakultas  VARCHAR(10) PRIMARY KEY,
    nama_fakultas  VARCHAR(100) NOT NULL
);

CREATE TABLE program_studi (
    kode_prodi     VARCHAR(10) PRIMARY KEY,
    nama_prodi     VARCHAR(100) NOT NULL,
    kode_fakultas  VARCHAR(10) NOT NULL,
    FOREIGN KEY (kode_fakultas) REFERENCES fakultas(kode_fakultas)
);

-- Akun login untuk peran Prodi (satu akun terkait satu program studi)
CREATE TABLE akun_prodi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    kode_prodi  VARCHAR(10) NOT NULL,
    FOREIGN KEY (kode_prodi) REFERENCES program_studi(kode_prodi) ON DELETE CASCADE
);

CREATE TABLE mahasiswa (
    npm             VARCHAR(15) PRIMARY KEY,
    nama_mahasiswa  VARCHAR(100) NOT NULL,
    jenis_kelamin   ENUM('L', 'P') NOT NULL,
    tempat_lahir    VARCHAR(100),
    tanggal_lahir   DATE,
    tanggal_masuk   DATE,
    alamat          TEXT,
    password        VARCHAR(255) NOT NULL,
    kode_prodi      VARCHAR(10),
    FOREIGN KEY (kode_prodi) REFERENCES program_studi(kode_prodi)
);

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Data contoh
-- ------------------------------------------------------------

INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$ID2AY7MI7NGM58MODSkUEuUWAnoySkeIuhd1utY58HSKwF9TBlDO.');

INSERT INTO fakultas (kode_fakultas, nama_fakultas) VALUES
('TEK', 'Teknik'),
('EKO', 'Ekonomi');

INSERT INTO program_studi (kode_prodi, nama_prodi, kode_fakultas) VALUES
('TI',  'Teknik Informatika', 'TEK'),
('SIP', 'Teknik Sipil',       'TEK'),
('MJM', 'Manajemen',          'EKO');

-- Satu akun untuk setiap program studi: prodi_<kode>, password prodi123
INSERT INTO akun_prodi (username, password, kode_prodi)
SELECT CONCAT('prodi_', LOWER(kode_prodi)), '$2y$10$V4J6EPcH257APQ0d62Wsr.BZaEs5JO1MhnJPnrNtrlOpTbYilI.cq', kode_prodi FROM program_studi;

INSERT INTO mahasiswa (npm, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat, password, kode_prodi) VALUES
('2355201001', 'Ahmad Fauzi',    'L', 'Bengkulu',  '2005-03-12', '2023-08-01', 'Jl. Merdeka No. 1, Bengkulu', '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe', 'TI'),
('2355201002', 'Siti Nurhaliza', 'P', 'Curup',     '2005-06-25', '2023-08-01', 'Jl. Sudirman No. 5, Curup',   '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe', 'TI'),
('2355201003', 'Budi Santoso',   'L', 'Manna',     '2004-11-02', '2023-08-01', 'Jl. Pahlawan No. 9, Manna',   '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe', 'SIP'),
('2355201004', 'Dewi Lestari',   'P', 'Bengkulu',  '2005-01-18', '2023-08-01', 'Jl. Kartini No. 3, Bengkulu', '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe', 'SIP'),
('2355201005', 'Rizky Ramadhan', 'L', 'Kepahiang', '2005-09-09', '2023-08-01', 'Jl. Melati No. 7, Kepahiang', '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe', 'MJM');
