<?php
// Sesuaikan dengan XAMPP: user root tanpa password adalah bawaan.
const BASE = '/flowstuckhub/';
try {
    $pdo = new PDO('mysql:host=localhost;dbname=flowstuckhub;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL di XAMPP menyala dan database flowstuckhub sudah diimpor.');
}
