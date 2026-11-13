<?php
$u = user();
$nav = !$u ? [] : ($u['role'] === 'user'
    ? [['user/katalog.php', 'Katalog']]
    : [['dashboard.php', 'Dashboard']]); // tambah menu baru di sini saat halamannya selesai
$roleName = ['user' => 'Pelanggan', 'admin' => 'Admin gudang', 'super_admin' => 'Super admin'];
$here = basename($_SERVER['PHP_SELF']);
$home = $u ? home_for($u['role']) : BASE . 'login.php';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#F8F9FA">
<title><?= e($title ?? 'FlowStuckHub') ?> · FlowStuckHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head>
<body>
<a class="skip-link" href="#main">Lewati ke konten</a>
<?php if ($u): ?>
<div class="shell">
  <aside class="sidebar">
    <div class="sidebar__head">
      <a class="brand" href="<?= e($home) ?>">
        <?= brand_mark() ?>
        <span class="brand__name">FlowStuckHub</span>
      </a>
      <nav class="nav" aria-label="Navigasi utama">
        <?php foreach ($nav as [$href, $label]): $on = basename($href) === $here; ?>
          <a class="nav__link<?= $on ? ' is-active' : '' ?>" href="<?= BASE . $href ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
    <div class="sidebar__foot">
      <div class="account">
        <span class="account__avatar" aria-hidden="true"><?= e(initials($u['nama'])) ?></span>
        <span class="account__text">
          <strong><?= e($u['nama']) ?></strong>
          <small><?= e($roleName[$u['role']] ?? 'Pengguna') ?></small>
        </span>
      </div>
      <a class="btn btn--ghost btn--sm btn--block" href="<?= BASE ?>logout.php">Keluar</a>
    </div>
  </aside>
  <main class="main" id="main">
<?php endif; ?>