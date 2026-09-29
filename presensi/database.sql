-- =========================================================
-- DATABASE: PRESENSI KELAS (QR CODE)
-- Import file ini melalui phpMyAdmin / mysql client
-- =========================================================

CREATE DATABASE IF NOT EXISTS presensi_db;
USE presensi_db;

DROP TABLE IF EXISTS presensi;
DROP TABLE IF EXISTS sesi_presensi;
DROP TABLE IF EXISTS mahasiswa;

-- Tabel data mahasiswa
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Tabel sesi presensi (1 sesi = 1 kode QR yang aktif)
CREATE TABLE sesi_presensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_sesi VARCHAR(50) NOT NULL,
    mata_kuliah VARCHAR(100) NOT NULL DEFAULT 'Kelas Hari Ini',
    tanggal DATE NOT NULL,
    waktu_dibuat DATETIME NOT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB;

-- Tabel hasil presensi mahasiswa
CREATE TABLE presensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesi_id INT NOT NULL,
    nim VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    waktu_scan DATETIME NOT NULL,
    FOREIGN KEY (sesi_id) REFERENCES sesi_presensi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Contoh data mahasiswa (silakan sesuaikan/tambah sendiri)
INSERT INTO mahasiswa (nim, nama, kelas) VALUES
('2210001', 'Ahmad Fadillah', 'TI-A'),
('2210002', 'Siti Nurhaliza', 'TI-A'),
('2210003', 'Budi Santoso', 'TI-A'),
('2210004', 'Dewi Lestari', 'TI-B'),
('2210005', 'Rizky Ramadhan', 'TI-B');
