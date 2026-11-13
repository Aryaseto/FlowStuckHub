<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function user() { return $_SESSION['user'] ?? null; }
function home_for($role) { return BASE . ($role === 'user' ? 'user/katalog.php' : 'dashboard.php'); }

function initials(string $nama): string {
    $kata = preg_split('/\s+/', trim($nama)) ?: [];
    $hasil = '';
    foreach (array_slice($kata, 0, 2) as $k) $hasil .= mb_substr($k, 0, 1);
    return mb_strtoupper($hasil);
}

// Logo kotak kuning yang dipakai di sidebar dan halaman masuk.
function brand_mark(string $class = ''): string {
    return '<span class="brand__mark' . ($class !== '' ? ' ' . $class : '') . '" aria-hidden="true">F</span>';
}

// Panggil di awal tiap halaman terlindungi. Contoh: require_login(['admin','super_admin']);
function require_login(array $roles = []) {
    $u = user();
    if (!$u) { header('Location: ' . BASE . 'login.php'); exit; }
    if ($roles && !in_array($u['role'], $roles, true)) { http_response_code(403); exit('Anda tidak punya akses ke halaman ini.'); }
}
function csrf_field() {
    $_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Sesi formulir kedaluwarsa. Muat ulang halaman.'); }
}
