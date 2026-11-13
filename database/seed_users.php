<?php
// Jalankan sekali: http://localhost/flowstuckhub/database/seed_users.php lalu hapus file ini.
require_once __DIR__ . '/../config/database.php';
$akun = [
  ['Siti Marlina', 'siti@flowstuckhub.id', 'purchasing', 'super123', 'super_admin'],
  ['Rahmat Hidayat', 'rahmat@flowstuckhub.id', 'gudang', 'gudang123', 'admin'],
  ['Budi Santoso', 'budi@toko.id', 'budi', 'budi123', 'user'],
];
$ins = $pdo->prepare('INSERT IGNORE INTO users (nama_lengkap,email,username,password,role) VALUES (?,?,?,?,?)');
foreach ($akun as [$n, $e, $u, $p, $r]) $ins->execute([$n, $e, $u, password_hash($p, PASSWORD_DEFAULT), $r]);
echo 'Selesai. Akun contoh: purchasing/super123, gudang/gudang123, budi/budi123. Hapus file ini sekarang.';
