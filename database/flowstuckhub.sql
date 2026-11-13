CREATE DATABASE IF NOT EXISTS flowstuckhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flowstuckhub;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
  alamat VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  deskripsi TEXT NULL,
  satuan VARCHAR(30) NOT NULL DEFAULT 'dus',
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE mutasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  barang_id INT NOT NULL,
  user_id INT NOT NULL,
  jenis ENUM('masuk','keluar') NOT NULL,
  jumlah INT NOT NULL,
  catatan VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE pesanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  alamat_tujuan VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE pesanan_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT NOT NULL,
  barang_id INT NOT NULL,
  jumlah INT NOT NULL,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  FOREIGN KEY (barang_id) REFERENCES barang(id)
);
CREATE TABLE surat_jalan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT NOT NULL,
  nomor VARCHAR(20) NOT NULL UNIQUE,
  status ENUM('disiapkan','dikirim','diterima') NOT NULL DEFAULT 'disiapkan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE
);
CREATE TABLE retur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  barang_id INT NOT NULL,
  foto VARCHAR(255) NOT NULL,
  deskripsi TEXT NOT NULL,
  status ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
);

INSERT INTO barang (nama, deskripsi, satuan, stok) VALUES
('Beras premium 25 kg','Karung 25 kg, panen terbaru','karung',120),
('Minyak goreng 2 L','Dus isi 6 pouch','dus',64),
('Gula pasir 50 kg','Karung 50 kg','karung',0),
('Mie instan goreng','Dus isi 40 bungkus','dus',210),
('Air mineral 600 ml','Dus isi 24 botol','dus',18),
('Kopi sachet','Karton isi 10 renceng','karton',75);
