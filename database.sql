-- ============================================================================
-- SKEMA DATABASE: SISTEM LABORATORIUM RUMAH SAKIT (MAGANG RS)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `magang_rs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `magang_rs`;

-- Hapus tabel lama jika ada (urutan child ke parent)
DROP TABLE IF EXISTS `hasil_tes`;
DROP TABLE IF EXISTS `pemeriksaan_lab`;
DROP TABLE IF EXISTS `aturan_lab`;
DROP TABLE IF EXISTS `pemeriksaan`;
DROP TABLE IF EXISTS `pasien`;

-- ----------------------------------------------------------------------------
-- 1. TABEL: pasien
-- Menyimpan identitas pasien dan tanggal lahir untuk penentuan kategori usia
-- ----------------------------------------------------------------------------
CREATE TABLE `pasien` (
    `id_pasien` VARCHAR(10) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `tgl_lahir` DATETIME NOT NULL,
    PRIMARY KEY (`id_pasien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. TABEL: pemeriksaan
-- Menyimpan master katalog tes/pemeriksaan laboratorium
-- ----------------------------------------------------------------------------
CREATE TABLE `pemeriksaan` (
    `id_pemeriksaan` VARCHAR(10) NOT NULL,
    `nama_tes` VARCHAR(100) NOT NULL,
    `satuan` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id_pemeriksaan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. TABEL: aturan_lab
-- Menyimpan nilai rujukan dan ambang batas kritis per kategori usia pasien
-- ----------------------------------------------------------------------------
CREATE TABLE `aturan_lab` (
    `id_aturan` INT AUTO_INCREMENT NOT NULL,
    `id_pemeriksaan` VARCHAR(10) NOT NULL,
    `kategori` ENUM('bayi lahir', 'bayi', 'anak dan dewasa') NOT NULL,
    `batas_bawah` DECIMAL(10,2) NULL,
    `batas_atas` DECIMAL(10,2) NULL,
    PRIMARY KEY (`id_aturan`),
    CONSTRAINT `fk_aturan_pemeriksaan`
        FOREIGN KEY (`id_pemeriksaan`) REFERENCES `pemeriksaan` (`id_pemeriksaan`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. TABEL: pemeriksaan_lab (Header Transaksi Kunjungan/Pemeriksaan)
-- Menyimpan sesi pemeriksaan pasien beserta waktu dilakukannya tes
-- ----------------------------------------------------------------------------
CREATE TABLE `pemeriksaan_lab` (
    `id_transaksi` INT AUTO_INCREMENT NOT NULL,
    `id_pasien` VARCHAR(10) NOT NULL,
    `waktu_pemeriksaan` DATETIME NOT NULL,
    PRIMARY KEY (`id_transaksi`),
    CONSTRAINT `fk_transaksi_pasien`
        FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. TABEL: hasil_tes (Detail Item Hasil Pemeriksaan)
-- Menyimpan nilai riil dari setiap tes yang dilakukan dalam transaksi
-- ----------------------------------------------------------------------------
CREATE TABLE `hasil_tes` (
    `id_hasil` INT AUTO_INCREMENT NOT NULL,
    `id_transaksi` INT NOT NULL,
    `id_pemeriksaan` VARCHAR(10) NOT NULL,
    `nilai` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id_hasil`),
    CONSTRAINT `fk_hasil_transaksi`
        FOREIGN KEY (`id_transaksi`) REFERENCES `pemeriksaan_lab` (`id_transaksi`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_hasil_pemeriksaan`
        FOREIGN KEY (`id_pemeriksaan`) REFERENCES `pemeriksaan` (`id_pemeriksaan`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DATA AWAL (SEED DATA)
-- ============================================================================

-- 1. Data Pasien
INSERT INTO `pasien` (`id_pasien`, `nama`, `tgl_lahir`) VALUES
('P001', 'By. Ny. Siti', '2026-09-29 10:00:00'),
('P002', 'Adik Rizky',   '2026-04-10 08:00:00'),
('P003', 'Bapak Budi',   '1995-02-14 06:30:00');

-- 2. Master Pemeriksaan
INSERT INTO `pemeriksaan` (`id_pemeriksaan`, `nama_tes`, `satuan`) VALUES
('PEM001', 'Gula Darah',      'mg/dL'),
('PEM002', 'Bilirubin Total', 'mg/dL'),
('PEM003', 'Trombosit',       'ribu/uL');

-- 3. Aturan Batas Kritis Lab
INSERT INTO `aturan_lab` (`id_pemeriksaan`, `kategori`, `batas_bawah`, `batas_atas`) VALUES
-- Gula Darah
('PEM001', 'bayi lahir',       30.00, 300.00),
('PEM001', 'bayi',             40.00, 300.00),
('PEM001', 'anak dan dewasa',  45.00, 450.00),
-- Bilirubin Total (Batas Bawah NULL -> Kritis jika > batas atas)
('PEM002', 'bayi lahir',       NULL,  15.00),
('PEM002', 'bayi',             NULL,  12.00),
('PEM002', 'anak dan dewasa',  NULL,  15.00),
-- Trombosit (Batas Atas NULL -> Kritis jika < batas bawah)
('PEM003', 'bayi lahir',       30.00, NULL),
('PEM003', 'bayi',             20.00, NULL),
('PEM003', 'anak dan dewasa',  20.00, NULL);

-- 4. Transaksi Pemeriksaan Lab Pasien
INSERT INTO `pemeriksaan_lab` (`id_transaksi`, `id_pasien`, `waktu_pemeriksaan`) VALUES
(1, 'P001', '2026-09-30 15:00:00'),
(2, 'P002', '2026-09-30 15:00:00'),
(3, 'P003', '2026-09-30 15:00:00');

-- 5. Detail Hasil Tes Pasien
INSERT INTO `hasil_tes` (`id_transaksi`, `id_pemeriksaan`, `nilai`) VALUES
-- Pasien P001 (Transaksi 1)
(1, 'PEM001', 25.00),  -- Gula Darah (< 30 => Kritis)
(1, 'PEM002', 16.50), -- Bilirubin Total (> 15 => Kritis)
(1, 'PEM003', 150.00), -- Trombosit (> 30 => Aman)

-- Pasien P002 (Transaksi 2)
(2, 'PEM001', 85.00),  -- Gula Darah (40 - 300 => Aman)
(2, 'PEM003', 15.00),  -- Trombosit (< 20 => Kritis)

-- Pasien P003 (Transaksi 3)
(3, 'PEM001', 490.00), -- Gula Darah (> 450 => Kritis)
(3, 'PEM002', 1.20),   -- Bilirubin Total (< 15 => Aman)
(3, 'PEM003', 250.00); -- Trombosit (> 20 => Aman)
