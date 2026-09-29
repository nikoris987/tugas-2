-- ============================================================
-- MIGRASI: menambah peran Prodi & login Mahasiswa
-- Aman dijalankan pada database yang SUDAH berisi data
-- (tidak menghapus fakultas, program_studi, mahasiswa, admin).
-- Jalankan lewat phpMyAdmin > tab SQL/Import setelah memilih database.
--
-- Hasil:
--   Prodi     : prodi_<kode> / prodi123   (satu akun per program studi)
--   Mahasiswa : password contoh lama diganti jadi mahasiswa123
--               (hanya baris yang masih berisi password contoh "dummyhash")
-- GANTI password ini setelah selesai.
-- ============================================================

CREATE TABLE IF NOT EXISTS akun_prodi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    kode_prodi  VARCHAR(10) NOT NULL,
    FOREIGN KEY (kode_prodi) REFERENCES program_studi(kode_prodi) ON DELETE CASCADE
);

-- Buat akun untuk setiap program studi yang belum punya akun
-- (jalankan lagi baris ini kapan saja setelah menambah program studi baru)
INSERT IGNORE INTO akun_prodi (username, password, kode_prodi)
SELECT CONCAT('prodi_', LOWER(kode_prodi)), '$2y$10$V4J6EPcH257APQ0d62Wsr.BZaEs5JO1MhnJPnrNtrlOpTbYilI.cq', kode_prodi FROM program_studi;

-- Perbaiki password contoh mahasiswa agar bisa dipakai login
UPDATE mahasiswa SET password = '$2y$10$SLNsFyhfeKn05TOgAlypJeuD/NqXW8p8Gxn8EjDyUYleAcZqPbfSe' WHERE password LIKE '$2y$10$dummyhash%';
