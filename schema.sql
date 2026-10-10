-- Buat database jika belum ada
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'db_timbangan')
BEGIN
    CREATE DATABASE db_timbangan;
END
GO

USE db_timbangan;
GO

-- Buat tabel transaksi timbangan
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'tbl_timbangan')
BEGIN
    CREATE TABLE tbl_timbangan (
        id INT IDENTITY(1,1) PRIMARY KEY,
        no_tiket VARCHAR(30) NOT NULL UNIQUE,
        no_polisi VARCHAR(20) NOT NULL,
        nama_sopir VARCHAR(100) NOT NULL,
        komoditas VARCHAR(100) NOT NULL,
        bruto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        tara DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        netto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(20) NOT NULL DEFAULT 'MASUK',
        waktu_masuk DATETIME2 NOT NULL DEFAULT GETDATE(),
        waktu_keluar DATETIME2 NULL
    );
END
GO