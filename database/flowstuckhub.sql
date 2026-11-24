-- ============================================================
--  FlowStuckHub - Warehouse Logistics Database
--  Generated: 2026-11-18
--  Charset  : utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS flowstuckhub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE flowstuckhub;

-- ============================================================
--  TABLE: users
-- ============================================================
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap  VARCHAR(100)  NOT NULL,
  email         VARCHAR(100)  NOT NULL UNIQUE,
  username      VARCHAR(50)   NOT NULL UNIQUE,
  password      VARCHAR(255)  NOT NULL,
  role          ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
  alamat        VARCHAR(255)  NULL,
  no_hp         VARCHAR(20)   NULL,
  reset_token   VARCHAR(64)   NULL,
  reset_expires DATETIME      NULL,
  foto_profil   VARCHAR(255)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE: barang
-- ============================================================
CREATE TABLE barang (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(120) NOT NULL,
  deskripsi   TEXT         NULL,
  satuan      VARCHAR(30)  NOT NULL DEFAULT 'dus',
  stok        INT          NOT NULL DEFAULT 0,
  gambar      VARCHAR(255) NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE: mutasi
-- ============================================================
CREATE TABLE mutasi (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  barang_id  INT          NOT NULL,
  user_id    INT          NOT NULL,
  jenis      ENUM('masuk','keluar') NOT NULL,
  jumlah     INT          NOT NULL,
  catatan    VARCHAR(255) NULL,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)   REFERENCES users(id)
);

-- ============================================================
--  TABLE: pesanan
-- ============================================================
CREATE TABLE pesanan (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT          NOT NULL,
  alamat_tujuan VARCHAR(255) NOT NULL,
  status        ENUM('pending','dikonfirmasi','dikirim','diterima','dibatalkan')
                             NOT NULL DEFAULT 'pending',
  catatan       TEXT         NULL,
  no_wa         VARCHAR(20)  NULL,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ============================================================
--  TABLE: pesanan_item
-- ============================================================
CREATE TABLE pesanan_item (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT NOT NULL,
  barang_id   INT NOT NULL,
  jumlah      INT NOT NULL,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  FOREIGN KEY (barang_id)  REFERENCES barang(id)
);

-- ============================================================
--  TABLE: surat_jalan
-- ============================================================
CREATE TABLE surat_jalan (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT          NOT NULL,
  nomor       VARCHAR(20)  NOT NULL UNIQUE,
  status      ENUM('disiapkan','dikirim','diterima') NOT NULL DEFAULT 'disiapkan',
  driver_id   INT          NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  FOREIGN KEY (driver_id)  REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
--  TABLE: retur
-- ============================================================
CREATE TABLE retur (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT          NOT NULL,
  barang_id       INT          NOT NULL,
  foto            VARCHAR(255) NOT NULL,
  deskripsi       TEXT         NOT NULL,
  status          ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  tanggal_submit  DATETIME     NULL,
  tanggal_selesai DATETIME     NULL,
  created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)   REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
);

-- ============================================================
--  SAMPLE DATA
-- ============================================================

-- Users (password = bcrypt of 'password123')
INSERT INTO users (nama_lengkap, email, username, password, role, alamat, no_hp) VALUES
('Super Admin',  'superadmin@flowstuckhub.com', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'Jl. Pusat No. 1, Jakarta',   '08100000001'),
('Admin Gudang', 'admin@flowstuckhub.com',      'admin',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',       'Jl. Gudang No. 5, Bekasi',   '08100000002'),
('Driver Andi',  'andi@flowstuckhub.com',        'driver_andi','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',        'Jl. Sopir No. 3, Depok',     '08111222333'),
('Budi Santoso', 'budi@example.com',             'budi',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',        'Jl. Merdeka No. 10, Bogor',  '08222333444'),
('Citra Dewi',   'citra@example.com',            'citra',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',        'Jl. Melati No. 7, Tangerang','08333444555');

-- Barang
INSERT INTO barang (nama, deskripsi, satuan, stok) VALUES
('Beras premium 25 kg',  'Karung 25 kg, panen terbaru',   'karung', 120),
('Minyak goreng 2 L',    'Dus isi 6 pouch',                'dus',     64),
('Gula pasir 50 kg',     'Karung 50 kg',                   'karung',   0),
('Mie instan goreng',    'Dus isi 40 bungkus',             'dus',    210),
('Air mineral 600 ml',   'Dus isi 24 botol',               'dus',     18),
('Kopi sachet',          'Karton isi 10 renceng',          'karton',  75);

-- Pesanan
INSERT INTO pesanan (user_id, alamat_tujuan, status, catatan, no_wa) VALUES
(4, 'Jl. Merdeka No. 10, Bogor',   'dikonfirmasi', 'Tolong dikirim pagi hari', '08222333444'),
(5, 'Jl. Melati No. 7, Tangerang', 'pending',       NULL,                       '08333444555');

-- Pesanan Item
INSERT INTO pesanan_item (pesanan_id, barang_id, jumlah) VALUES
(1, 1, 5),
(1, 4, 2),
(2, 2, 3),
(2, 6, 1);

-- Mutasi
INSERT INTO mutasi (barang_id, user_id, jenis, jumlah, catatan) VALUES
(1, 2, 'masuk',  150, 'Penerimaan stok awal'),
(1, 2, 'keluar',  30, 'Pengiriman pesanan #1'),
(2, 2, 'masuk',   80, 'Penerimaan stok awal'),
(2, 2, 'keluar',  16, 'Pengiriman pesanan #2'),
(4, 2, 'masuk',  250, 'Penerimaan stok awal'),
(4, 2, 'keluar',  40, 'Pengiriman pesanan #1');

-- Surat Jalan
INSERT INTO surat_jalan (pesanan_id, nomor, status, driver_id) VALUES
(1, 'SJ-2026-001', 'dikirim', 3);

-- Retur
INSERT INTO retur (user_id, barang_id, foto, deskripsi, status, tanggal_submit) VALUES
(4, 4, 'retur/foto_001.jpg', 'Produk rusak saat diterima, kemasan penyok.', 'diproses', '2026-11-15 10:30:00');


-- ============================================================
--  ALTER TABLE (jalankan bagian ini jika database sudah ada
--  dan hanya perlu ditambahkan kolom-kolom baru)
-- ============================================================

-- ALTER users
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS no_hp         VARCHAR(20)  NULL AFTER alamat,
  ADD COLUMN IF NOT EXISTS reset_token   VARCHAR(64)  NULL AFTER no_hp,
  ADD COLUMN IF NOT EXISTS reset_expires DATETIME     NULL AFTER reset_token,
  ADD COLUMN IF NOT EXISTS foto_profil   VARCHAR(255) NULL AFTER reset_expires;

-- ALTER pesanan
ALTER TABLE pesanan
  ADD COLUMN IF NOT EXISTS status  ENUM('pending','dikonfirmasi','dikirim','diterima','dibatalkan') NOT NULL DEFAULT 'pending' AFTER alamat_tujuan,
  ADD COLUMN IF NOT EXISTS catatan TEXT        NULL AFTER status,
  ADD COLUMN IF NOT EXISTS no_wa   VARCHAR(20) NULL AFTER catatan;

-- ALTER surat_jalan
ALTER TABLE surat_jalan
  ADD COLUMN IF NOT EXISTS driver_id INT NULL AFTER status;

ALTER TABLE surat_jalan
  ADD CONSTRAINT fk_surat_jalan_driver
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL;

-- ALTER retur
ALTER TABLE retur
  ADD COLUMN IF NOT EXISTS tanggal_submit  DATETIME NULL AFTER status,
  ADD COLUMN IF NOT EXISTS tanggal_selesai DATETIME NULL AFTER tanggal_submit;
