<?php
require_once __DIR__ . '/auth.php';
$u = user();

// Definisi Navigasi per Role
$nav = [];
if ($u) {
    if ($u['role'] === 'user') {
        $nav = [
            [
                'href'  => 'user/katalog.php',
                'label' => 'Katalog Barang',
                'icon'  => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"></rect>'
            ],
            [
                'href'  => 'user/status-pengiriman.php',
                'label' => 'Status Pengiriman',
                'icon'  => '<rect x="1" y="3" width="15" height="13" rx="2"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>'
            ],
            [
                'href'  => 'user/retur.php',
                'label' => 'Retur Barang',
                'icon'  => '<path d="M9 14 4 9l5-5"></path><path d="M4 9h9a6 6 0 0 1 0 12h-2"></path>'
            ],
            [
                'href'  => 'profile.php',
                'label' => 'Edit Profile',
                'icon'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>'
            ],
        ];
    } elseif ($u['role'] === 'admin') {
        $nav = [
            [
                'href'  => 'dashboard.php',
                'label' => 'Dashboard',
                'icon'  => '<path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path>'
            ],
            [
                'href'  => 'admin/update-barang.php',
                'label' => 'Barang Masuk & Keluar',
                'icon'  => '<path d="M12 3v18"></path><path d="m8 7 4-4 4 4"></path><path d="m8 17 4 4 4-4"></path>'
            ],
            [
                'href'  => 'admin/surat-jalan.php',
                'label' => 'Surat Jalan',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>'
            ],
            [
                'href'  => 'profile.php',
                'label' => 'Edit Profile',
                'icon'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>'
            ],
        ];
    } else { // super_admin
        $nav = [
            [
                'href'  => 'dashboard.php',
                'label' => 'Dashboard',
                'icon'  => '<path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path>'
            ],
            [
                'href'  => 'super_admin/barang.php',
                'label' => 'Kelola Barang (CRUD)',
                'icon'  => '<path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z"></path><path d="M4 8l8 4.5L20 8"></path><path d="M12 12.5v8"></path>'
            ],
            [
                'href'  => 'admin/update-barang.php',
                'label' => 'Barang Masuk & Keluar',
                'icon'  => '<path d="M12 3v18"></path><path d="m8 7 4-4 4 4"></path><path d="m8 17 4 4 4-4"></path>'
            ],
            [
                'href'  => 'admin/surat-jalan.php',
                'label' => 'Surat Jalan (Filter)',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>'
            ],
            [
                'href'  => 'super_admin/retur.php',
                'label' => 'Keluhan Retur',
                'icon'  => '<path d="M9 14 4 9l5-5"></path><path d="M4 9h9a6 6 0 0 1 0 12h-2"></path>'
            ],
            [
                'href'  => 'super_admin/laporan.php',
                'label' => 'Laporan Logistik',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M10 12h4"></path><path d="M10 16h4"></path>'
            ],
            [
                'href'  => 'profile.php',
                'label' => 'Edit Profile',
                'icon'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>'
            ],
        ];
    }
}

$roleBadge = [
    'user'        => 'Pelanggan Grosir',
    'admin'       => 'Admin Gudang / Kurir',
    'super_admin' => 'Super Admin Purchasing'
];

$here = basename($_SERVER['PHP_SELF']);
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$home = $u ? home_for($u['role']) : BASE . 'login.php';
?>
<!doctype html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E7D32">
<title><?= e($title ?? 'FlowStuckHub') ?> · FlowStuckHub Logistik</title>
<link rel="icon" type="image/jpeg" href="<?= BASE ?>img/logo-160.jpg">
<link rel="apple-touch-icon" href="<?= BASE ?>img/logo-160.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: { DEFAULT: '#2E7D32', light: '#4CAF50', dark: '#1B5E20' },
          accent: '#CDDC39'
        },
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif']
        }
      }
    }
  };
</script>
<style>
  ::selection { background: #CDDC39; color: #1B5E20; }
  .no-scrollbar { scrollbar-width: none; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
</head>
<body class="min-h-dvh bg-gray-50 font-sans text-gray-900 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[90] focus:rounded-lg focus:bg-gray-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten</a>

<?php if ($u): ?>
<div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-gray-900/50 backdrop-blur-[2px] lg:hidden"></div>

<div class="min-h-dvh lg:grid lg:h-dvh lg:grid-cols-[268px_minmax(0,1fr)] lg:overflow-hidden">

  <!-- Unified Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[268px] -translate-x-full transition-transform duration-200 ease-out lg:static lg:translate-x-0">
    <div class="flex h-full flex-col bg-brand px-4 py-5 text-white">

      <div class="flex items-center justify-between px-1">
        <a href="<?= e($home) ?>" class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 shrink-0 overflow-hidden rounded-full ring-2 ring-white/30 shadow">
            <img src="<?= BASE ?>img/logo-160.jpg" alt="Logo" width="40" height="40" class="h-full w-full scale-125 object-cover">
          </span>
          <div>
            <span class="text-[16px] font-extrabold tracking-tight block leading-tight">FlowStuck<span class="text-accent">Hub</span></span>
            <span class="text-[10px] uppercase font-bold tracking-wider text-green-100 opacity-90 block">Logistik Gudang</span>
          </div>
        </a>
        <button id="nav-close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-green-50 transition hover:bg-brand-dark focus:outline-none lg:hidden" aria-label="Tutup menu">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-5 w-5"><path d="M6 6l12 12M18 6 6 18"></path></svg>
        </button>
      </div>

      <div class="mt-4 px-2 py-1.5 rounded-lg bg-brand-dark/40 border border-white/10 flex items-center justify-between">
        <span class="text-xs text-green-100 font-medium">Peran Akses:</span>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-accent text-brand-dark shadow-sm">
          <?= e($roleBadge[$u['role']] ?? 'User') ?>
        </span>
      </div>

      <nav class="mt-6 flex-1 space-y-1 overflow-y-auto pr-1" aria-label="Navigasi utama">
        <?php foreach ($nav as $item): 
          $isActive = (basename($item['href']) === $here) || (strpos($currentUri, $item['href']) !== false);
        ?>
          <a href="<?= BASE . $item['href'] ?>"
             class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition <?= $isActive ? 'bg-brand-dark text-white shadow-sm' : 'text-green-50 hover:bg-brand-dark/60 hover:text-white' ?>"
             <?= $isActive ? 'aria-current="page"' : '' ?>>
            <?php if ($isActive): ?>
              <span aria-hidden="true" class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-accent"></span>
            <?php endif; ?>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px] shrink-0 opacity-90">
              <?= $item['icon'] ?>
            </svg>
            <span class="truncate"><?= e($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="mt-auto border-t border-white/15 pt-4">
        <a href="<?= BASE ?>profile.php" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-brand-dark/60 transition group">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-accent text-[12px] font-bold text-brand-dark shadow">
            <?= e(initials($u['nama'])) ?>
          </span>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-white group-hover:text-accent transition"><?= e($u['nama']) ?></p>
            <p class="text-xs text-green-100/80 truncate"><?= e($roleBadge[$u['role']] ?? 'Pengguna') ?></p>
          </div>
        </a>
        <a href="<?= BASE ?>logout.php" class="mt-2.5 flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-white/25 text-xs font-semibold text-white transition hover:bg-white/10 active:scale-[0.98]">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Keluar Sistem
        </a>
      </div>
    </div>
  </aside>

  <!-- Konten Kanan -->
  <div id="main-wrap" class="flex min-w-0 flex-col lg:h-dvh lg:overflow-y-auto">

    <!-- Topbar Mobile -->
    <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:hidden shadow-sm">
      <button id="nav-open" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:bg-gray-50 focus:outline-none" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-5 w-5"><path d="M4 7h16M4 12h16M4 17h10"></path></svg>
      </button>
      <a href="<?= e($home) ?>" class="flex items-center gap-2">
        <span class="inline-flex h-8 w-8 shrink-0 overflow-hidden rounded-full ring-1 ring-brand/15">
          <img src="<?= BASE ?>img/logo-160.jpg" alt="" width="32" height="32" class="h-full w-full scale-125 object-cover">
        </span>
        <span class="text-sm font-bold tracking-tight text-gray-900">FlowStuckHub</span>
      </a>
      <a href="<?= BASE ?>profile.php" class="grid h-8 w-8 place-items-center rounded-full bg-accent text-[11px] font-bold text-brand-dark" aria-label="Profil"><?= e(initials($u['nama'])) ?></a>
    </header>

    <main id="main" class="flex-1 px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
      <div class="mx-auto w-full max-w-6xl">
<?php endif; ?>
