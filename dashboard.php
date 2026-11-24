<?php
require_once __DIR__ . '/includes/auth.php';
require_login(['admin', 'super_admin']);

$u       = user();
$isSuper = $u['role'] === 'super_admin';

// Statistik utama
$tot = $pdo->query('SELECT COUNT(*) jenis, COALESCE(SUM(stok),0) stok FROM barang')->fetch();
$sj  = $pdo->query("SELECT COUNT(*) FROM surat_jalan WHERE status <> 'diterima'")->fetchColumn();
$ret = $pdo->query("SELECT COUNT(*) FROM retur WHERE status <> 'selesai'")->fetchColumn();

// Mutasi hari ini
$today = date('Y-m-d');
$mutHariIni = $pdo->prepare('
    SELECT 
        COALESCE(SUM(CASE WHEN jenis="masuk" THEN jumlah ELSE 0 END), 0) AS masuk,
        COALESCE(SUM(CASE WHEN jenis="keluar" THEN jumlah ELSE 0 END), 0) AS keluar
    FROM mutasi 
    WHERE DATE(created_at) = ?
');
$mutHariIni->execute([$today]);
$statHariIni = $mutHariIni->fetch();

// Stok menipis (<= 20)
$low = $pdo->query('SELECT id, nama, stok, satuan FROM barang WHERE stok <= 20 ORDER BY stok ASC LIMIT 8')->fetchAll();

// 8 Mutasi stok terakhir
$mut = $pdo->query('
    SELECT m.*, b.nama, b.satuan, u.nama_lengkap 
    FROM mutasi m 
    JOIN barang b ON b.id = m.barang_id 
    JOIN users u ON u.id = m.user_id 
    ORDER BY m.id DESC 
    LIMIT 7
')->fetchAll();

$title = 'Dashboard Monitoring';
require __DIR__ . '/includes/header.php';
?>

<!-- Header Dashboard -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Dashboard Monitoring</h1>
    <p class="text-sm text-gray-500 mt-1">Selamat datang, <strong class="text-gray-800"><?= e($u['nama']) ?></strong> (<?= $isSuper ? 'Super Admin Purchasing' : 'Admin Gudang' ?>). Berikut ringkasan logistik hari ini.</p>
  </div>
  <div class="flex items-center gap-2">
    <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-600 shadow-sm">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-brand">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      <?= date('d M Y') ?>
    </div>
  </div>
</div>

<!-- Kartu Ringkasan KPI -->
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

  <!-- Total Barang -->
  <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Jenis Barang</p>
      <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-green-50 text-brand">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
          <path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z"></path><path d="M4 8l8 4.5L20 8"></path><path d="M12 12.5v8"></path>
        </svg>
      </span>
    </div>
    <p class="mt-3 text-3xl font-extrabold tracking-tight tabular-nums text-gray-900"><?= (int)$tot['jenis'] ?></p>
    <p class="mt-1 text-xs text-gray-500 font-medium">Total stok fisik: <strong class="text-brand"><?= number_format((float)$tot['stok']) ?></strong> unit</p>
  </article>

  <!-- Mutasi Hari Ini -->
  <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Mutasi Hari Ini</p>
      <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
          <path d="M12 3v18"></path><path d="m8 7 4-4 4 4"></path><path d="m8 17 4 4 4-4"></path>
        </svg>
      </span>
    </div>
    <div class="mt-3 flex items-baseline gap-3">
      <p class="text-2xl font-extrabold tracking-tight text-green-700">+<?= (int)$statHariIni['masuk'] ?></p>
      <p class="text-2xl font-extrabold tracking-tight text-blue-700">-<?= (int)$statHariIni['keluar'] ?></p>
    </div>
    <p class="mt-1 text-xs text-gray-500 font-medium">Barang masuk & keluar hari ini</p>
  </article>

  <!-- Surat Jalan Berjalan -->
  <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Surat Jalan Berjalan</p>
      <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
          <rect x="1" y="3" width="15" height="13" rx="2"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>
      </span>
    </div>
    <p class="mt-3 text-3xl font-extrabold tracking-tight tabular-nums text-gray-900"><?= (int)$sj ?></p>
    <p class="mt-1 text-xs text-gray-500 font-medium">Dalam proses penyiapan & kurir</p>
  </article>

  <!-- Retur Barang -->
  <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Keluhan Retur Aktif</p>
      <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-red-50 text-red-600">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
          <path d="M9 14 4 9l5-5"></path><path d="M4 9h9a6 6 0 0 1 0 12h-2"></path>
        </svg>
      </span>
    </div>
    <p class="mt-3 text-3xl font-extrabold tracking-tight tabular-nums text-gray-900"><?= (int)$ret ?></p>
    <p class="mt-1 text-xs text-gray-500 font-medium">Menunggu pemeriksaan / proses</p>
  </article>

</div>

<!-- Pintasan Aksi Cepat -->
<div class="mt-6 flex flex-wrap gap-2.5">
  <a href="<?= BASE ?>admin/update-barang.php" class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Catat Mutasi Masuk/Keluar
  </a>
  <a href="<?= BASE ?>admin/surat-jalan.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition active:scale-[0.98]">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Lihat Surat Jalan
  </a>
  <?php if ($isSuper): ?>
    <a href="<?= BASE ?>super_admin/barang.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition active:scale-[0.98]">
      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      Kelola Katalog Barang
    </a>
    <a href="<?= BASE ?>super_admin/laporan.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition active:scale-[0.98]">
      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Laporan Logistik
    </a>
  <?php endif; ?>
</div>

<!-- Dua Kolom: Stok Menipis & Mutasi Terakhir -->
<div class="mt-8 grid gap-6 lg:grid-cols-5">

  <!-- Stok Menipis (2 Kolom) -->
  <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
      <div>
        <h2 class="text-sm font-bold text-gray-900">Peringatan Stok Menipis</h2>
        <p class="text-xs text-gray-500">Stok 20 unit atau kurang</p>
      </div>
      <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-700"><?= count($low) ?> item</span>
    </div>

    <div class="mt-4 overflow-x-auto">
      <?php if ($low): ?>
        <table class="w-full text-left text-xs">
          <thead class="text-gray-400 uppercase font-semibold">
            <tr>
              <th class="pb-2">Barang</th>
              <th class="pb-2 text-right">Sisa Stok</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($low as $b): ?>
              <tr>
                <td class="py-2.5 font-semibold text-gray-800"><?= e($b['nama']) ?></td>
                <td class="py-2.5 text-right font-mono font-bold <?= (int)$b['stok'] === 0 ? 'text-red-600' : 'text-amber-600' ?>">
                  <?= (int)$b['stok'] ?> <?= e($b['satuan']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="py-8 text-center text-xs text-gray-400">Semua stok barang aman.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Mutasi Stok Terkini (3 Kolom) -->
  <section class="lg:col-span-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
      <div>
        <h2 class="text-sm font-bold text-gray-900">Pergerakan Mutasi Logistik Terakhir</h2>
        <p class="text-xs text-gray-500">Aktivitas barang masuk & keluar terkini</p>
      </div>
      <a href="<?= BASE ?>admin/update-barang.php" class="text-xs font-semibold text-brand hover:underline">Lihat Semua &rarr;</a>
    </div>

    <div class="mt-4 overflow-x-auto">
      <?php if ($mut): ?>
        <table class="w-full text-left text-xs">
          <thead class="text-gray-400 uppercase font-semibold">
            <tr>
              <th class="pb-2">Waktu</th>
              <th class="pb-2">Nama Barang</th>
              <th class="pb-2 text-center">Tipe</th>
              <th class="pb-2 text-right">Jumlah</th>
              <th class="pb-2">Petugas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($mut as $m): ?>
              <tr>
                <td class="py-2.5 text-gray-500 whitespace-nowrap"><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
                <td class="py-2.5 font-semibold text-gray-800"><?= e($m['nama']) ?></td>
                <td class="py-2.5 text-center">
                  <?php if ($m['jenis'] === 'masuk'): ?>
                    <span class="inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-700">Masuk</span>
                  <?php else: ?>
                    <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">Keluar</span>
                  <?php endif; ?>
                </td>
                <td class="py-2.5 text-right font-mono font-bold <?= $m['jenis'] === 'masuk' ? 'text-green-700' : 'text-blue-700' ?>">
                  <?= ($m['jenis'] === 'masuk' ? '+' : '-') . (int)$m['jumlah'] ?> <?= e($m['satuan']) ?>
                </td>
                <td class="py-2.5 text-gray-600 truncate max-w-[100px]"><?= e($m['nama_lengkap']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="py-8 text-center text-xs text-gray-400">Belum ada riwayat mutasi stok.</p>
      <?php endif; ?>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
