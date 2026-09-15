-- ============================================================
-- DATABASE SCHEMA FINAL: E-Hafalan (Ustadz sebagai Super Admin)
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_monitoring_hafalan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_monitoring_hafalan`;

-- ------------------------------------------------------------
-- 1. TABEL UTAMA: USERS
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `setoran`;
DROP TABLE IF EXISTS `penilaian`;
DROP TABLE IF EXISTS `target_hafalan`;
DROP TABLE IF EXISTS `jadwal`;
DROP TABLE IF EXISTS `santri`;
DROP TABLE IF EXISTS `wali_santri`;
DROP TABLE IF EXISTS `pengasuh`;
DROP TABLE IF EXISTS `ustadz`;
DROP TABLE IF EXISTS `halaqah`;
DROP TABLE IF EXISTS `pengumuman`;
DROP TABLE IF EXISTS `pengaturan`;
DROP TABLE IF EXISTS `surah`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('ustad', 'pengasuh', 'santri', 'wali') NOT NULL,
  `foto` VARCHAR(255) DEFAULT 'default.png',
  `telepon` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. TABEL HALAQAH (KELOMPOK)
-- ------------------------------------------------------------
CREATE TABLE `halaqah` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_halaqah` VARCHAR(100) NOT NULL,
  `ustad_id` INT NULL,
  `ruangan` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ustad_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. TABEL PROFIL SPECIFIC
-- ------------------------------------------------------------

-- Profil Ustadz
CREATE TABLE `ustadz` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nip_nik` VARCHAR(50) DEFAULT NULL,
  `gelar` VARCHAR(50) DEFAULT NULL,
  `spesialisasi` VARCHAR(100) DEFAULT 'Tahfidz Al-Qur\'an',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Profil Pengasuh / Pimpinan
CREATE TABLE `pengasuh` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `jabatan` VARCHAR(100) DEFAULT 'Pengasuh Pesantren',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Profil Wali Santri
CREATE TABLE `wali_santri` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hubungan` ENUM('Ayah', 'Ibu', 'Wali') DEFAULT 'Ayah',
  `alamat` TEXT DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Profil Santri (Sudah ditambahkan halaqah_id & total_hafalan)
CREATE TABLE `santri` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nis` VARCHAR(30) NOT NULL UNIQUE,
  `halaqah_id` INT DEFAULT NULL,
  `kelas_kelompok` VARCHAR(50) DEFAULT 'Kelas Tahfidz 1',
  `ustadz_id` INT DEFAULT NULL,
  `wali_id` INT DEFAULT NULL,
  `target_juz` INT DEFAULT 30,
  `total_hafalan` INT DEFAULT 0,
  `total_hafalan_halaman` INT DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`halaqah_id`) REFERENCES `halaqah`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`ustadz_id`) REFERENCES `ustadz`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`wali_id`) REFERENCES `wali_santri`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. MASTER DATA AL-QUR'AN & JADWAL
-- ------------------------------------------------------------
CREATE TABLE `surah` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_surah` INT NOT NULL UNIQUE,
  `nama_surah` VARCHAR(100) NOT NULL,
  `nama_arab` VARCHAR(100) NOT NULL,
  `jumlah_ayat` INT NOT NULL,
  `tempat_turun` ENUM('Makkiyah', 'Madaniyah') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jadwal` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ustadz_id` INT NOT NULL,
  `nama_kelompok` VARCHAR(100) NOT NULL,
  `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `lokasi` VARCHAR(100) DEFAULT 'Masjid Utama',
  FOREIGN KEY (`ustadz_id`) REFERENCES `ustadz`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. PENGUMUMAN & PENGATURAN SISTEM
-- ------------------------------------------------------------
CREATE TABLE `pengumuman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `penulis_id` INT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `target_role` ENUM('semua', 'santri', 'wali', 'pengasuh') DEFAULT 'semua',
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`penulis_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pengaturan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_pesantren` VARCHAR(150) NOT NULL DEFAULT 'Pesantren Tahfidz Al-Qur\'an',
  `tautan_sistem` VARCHAR(255) DEFAULT NULL,
  `tahun_ajaran` VARCHAR(20) DEFAULT '2026/2027',
  `kepala_tahfidz` VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. TRANSAKSI SETORAN & PENILAIAN
-- ------------------------------------------------------------
CREATE TABLE `setoran` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `santri_id` INT NOT NULL,
  `ustadz_id` INT DEFAULT NULL,
  `jenis` ENUM('ziyadah', 'murajaah') DEFAULT 'ziyadah',
  `surah_id` INT DEFAULT NULL,
  `ayat_mulai` INT DEFAULT NULL,
  `ayat_selesai` INT DEFAULT NULL,
  `juz` INT NOT NULL,
  `halaman` INT DEFAULT NULL,
  `kelancaran` ENUM('Sangat Lancar', 'Lancar', 'Kurang Lancar', 'Mengulang') DEFAULT 'Lancar',
  `makhroj` ENUM('Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan') DEFAULT 'Baik',
  `tajwid` ENUM('Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan') DEFAULT 'Baik',
  `nilai_angka` DECIMAL(5,2) DEFAULT 80.00,
  `nilai_huruf` VARCHAR(5) DEFAULT 'A',
  `catatan` TEXT DEFAULT NULL,
  `tanggal_setor` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `target_hafalan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `santri_id` INT NOT NULL,
  `target_juz` INT NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_tenggat` DATE NOT NULL,
  `status` ENUM('Berjalan', 'Tercapai', 'Gagal') DEFAULT 'Berjalan',
  FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `penilaian` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `santri_id` INT NOT NULL,
  `jenis_ujian` VARCHAR(50) NOT NULL,
  `juz_diuji` INT NULL,
  `nilai` FLOAT NOT NULL,
  `keterangan` TEXT NULL,
  `tanggal` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. SEED DATA AWAL (USER ADMIN & HALAQAH)
-- ------------------------------------------------------------
INSERT INTO `users` (`nama`, `username`, `email`, `password`, `role`) VALUES
('Ustadz Super Admin', 'admin', 'admin@hafalan.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe11j4D1EGYLHG3eG6pWpI7.m3rM8n8G6', 'ustad'); -- Password Default: admin123 (jika memakai hash yang cocok) atau bisa kamu reset.

INSERT INTO `halaqah` (`nama_halaqah`, `ruangan`) VALUES
('Halaqah Abu Bakar (Tingkat Dasar)', 'Ruang 01'),
('Halaqah Umar bin Khattab (Tingkat Menengah)', 'Ruang 02'),
('Halaqah Utsman bin Affan (Tingkat Lanjut)', 'Ruang 03'),
('Halaqah Ali bin Abi Thalib (Takhassus 30 Juz)', 'Ruang Utama');


ALTER TABLE `halaqah` 
ADD COLUMN `hari` VARCHAR(20) NULL AFTER `ruangan`,
ADD COLUMN `jam_mulai` TIME NULL AFTER `hari`,
ADD COLUMN `jam_selesai` TIME NULL AFTER `jam_mulai`;