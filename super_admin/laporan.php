<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['super_admin']);

$u = user();

// Filter Tanggal
$tgl_mulai   = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
$jenis       = $_GET['jenis'] ?? 'semua'; // semua, masuk, keluar

// Validasi tanggal
$startDate = $tgl_mulai . ' 00:00:00';
$endDate   = $tgl_selesai . ' 23:59:59';

// Query Ringkasan Mutasi Periode Ini
$stSummary = $pdo->prepare('
    SELECT 
        COALESCE(SUM(CASE WHEN jenis = "masuk" THEN jumlah ELSE 0 END), 0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis = "keluar" THEN jumlah ELSE 0 END), 0) AS total_keluar,
        COUNT(*) AS total_transaksi
    FROM mutasi
    WHERE created_at BETWEEN ? AND ?
');
$stSummary->execute([$startDate, $endDate]);
$summary = $stSummary->fetch();

// Ringkasan Surat Jalan
$stSJ = $pdo->prepare('
    SELECT 
        COUNT(*) AS total_sj,
        COALESCE(SUM(CASE WHEN status = "diterima" THEN 1 ELSE 0 END), 0) AS sj_diterima,
        COALESCE(SUM(CASE WHEN status = "dikirim" THEN 1 ELSE 0 END), 0) AS sj_dikirim
    FROM surat_jalan
    WHERE created_at BETWEEN ? AND ?
');
$stSJ->execute([$startDate, $endDate]);
$summarySJ = $stSJ->fetch();

// Ringkasan Retur
$stRetur = $pdo->prepare('
    SELECT 
        COUNT(*) AS total_retur,
        COALESCE(SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END), 0) AS retur_selesai,
        COALESCE(SUM(CASE WHEN status = "baru" THEN 1 ELSE 0 END), 0) AS retur_baru
    FROM retur
    WHERE created_at BETWEEN ? AND ?
');
$stRetur->execute([$startDate, $endDate]);
$summaryRetur = $stRetur->fetch();

// Query Rincian Mutasi
$sqlMutasi = '
    SELECT m.*, b.nama AS nama_barang, b.satuan, u.nama_lengkap AS nama_petugas
    FROM mutasi m
    JOIN barang b ON b.id = m.barang_id
    JOIN users u ON u.id = m.user_id
    WHERE m.created_at BETWEEN ? AND ?
';
$params = [$startDate, $endDate];

if ($jenis === 'masuk') {
    $sqlMutasi .= ' AND m.jenis = "masuk"';
} elseif ($jenis === 'keluar') {
    $sqlMutasi .= ' AND m.jenis = "keluar"';
}
$sqlMutasi .= ' ORDER BY m.created_at DESC';

$stList = $pdo->prepare($sqlMutasi);
$stList->execute($params);
$rows = $stList->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Logistik & Mutasi Gudang · FlowStuckHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
    @media print {
      aside, form, .no-print { display: none !important; }
      body, main { background: white !important; padding: 0 !important; }
      .print-header { display: block !important; }
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- ── SIDEBAR ──────────────────────────────────────────── -->
<aside class="w-64 min-h-screen flex flex-col flex-shrink-0 no-print" style="background:#2E7D32">
  <div class="px-6 py-5 border-b border-green-700">
    <a href="<?= BASE ?>dashboard.php" class="flex items-center gap-2 text-white font-bold text-lg no-underline">
      <span class="w-8 h-8 rounded flex items-center justify-center font-black text-green-900" style="background:#CDDC39">F</span>
      FlowStuckHub
    </a>
    <p class="text-green-200 text-xs mt-1">Super Admin Purchasing</p>
  </div>

  <nav class="flex-1 px-4 py-4 flex flex-col gap-1">
    <a href="<?= BASE ?>dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Dashboard
    </a>
    <a href="<?= BASE ?>super_admin/barang.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Kelola Barang (CRUD)
    </a>
    <a href="<?= BASE ?>admin/update-barang.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Barang Masuk & Keluar
    </a>
    <a href="<?= BASE ?>admin/surat-jalan.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Surat Jalan (Filter)
    </a>
    <a href="<?= BASE ?>super_admin/retur.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Keluhan Retur
    </a>
    <a href="<?= BASE ?>super_admin/laporan.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold bg-white text-[#2E7D32] transition">
      Laporan Logistik
    </a>
    <a href="<?= BASE ?>profile.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Edit Profile
    </a>
  </nav>

  <div class="px-4 py-4 border-t border-green-700">
    <div class="flex items-center gap-3 mb-3">
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-600 text-white font-semibold text-sm">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($u['nama'] ?? 'S', 0, 1))) ?>
      </span>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($u['nama']) ?></p>
        <p class="text-xs text-green-200">Super Admin</p>
      </div>
    </div>
    <a href="<?= BASE ?>logout.php" class="block w-full text-center py-1.5 px-3 rounded text-xs text-green-100 border border-green-600 hover:bg-green-700 transition">
      Keluar
    </a>
  </div>
</aside>

<!-- ── CONTENT ──────────────────────────────────────────── -->
<main class="flex-1 p-8 overflow-y-auto">
  <div class="max-w-6xl mx-auto">

    <!-- Header Cetak -->
    <div class="hidden print-header mb-6 pb-4 border-b border-gray-300">
      <h1 class="text-2xl font-bold text-gray-900">FLOWSTUCKHUB LOGISTICS</h1>
      <p class="text-sm text-gray-600">Laporan Manajemen Logistik dan Monitoring Gudang Grosir</p>
      <p class="text-xs text-gray-500 mt-1">Periode: <?= htmlspecialchars($tgl_mulai) ?> s/d <?= htmlspecialchars($tgl_selesai) ?> | Dicetak oleh: <?= htmlspecialchars($u['nama']) ?> pada <?= date('d/m/Y H:i') ?></p>
    </div>

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 no-print">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Logistik & Monitoring Gudang</h1>
        <p class="text-sm text-gray-500 mt-1">Rekapitulasi pergerakan stok barang masuk, barang keluar, dan status distribusi.</p>
      </div>
      <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Cetak Laporan
      </button>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 no-print">
      <form method="get" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
          <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai) ?>" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-600 focus:outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
          <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai) ?>" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-600 focus:outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Mutasi</label>
          <select name="jenis" class="w-full text-sm border border-gray-300 rounded-lg p-2 bg-white focus:ring-2 focus:ring-green-600 focus:outline-none">
            <option value="semua" <?= $jenis === 'semua' ? 'selected' : '' ?>>Semua Mutasi</option>
            <option value="masuk" <?= $jenis === 'masuk' ? 'selected' : '' ?>>Barang Masuk Saja</option>
            <option value="keluar" <?= $jenis === 'keluar' ? 'selected' : '' ?>>Barang Keluar Saja</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="w-full py-2 px-4 bg-[#2E7D32] text-white text-sm font-semibold rounded-lg hover:bg-green-800 transition">
            Filter Data
          </button>
          <a href="<?= BASE ?>super_admin/laporan.php" class="py-2 px-3 text-center bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase">Total Barang Masuk</p>
        <p class="text-2xl font-bold text-green-700 mt-2 font-mono">+<?= number_format((float)$summary['total_masuk'], 0, ',', '.') ?> <span class="text-xs font-sans text-gray-500 font-normal">item</span></p>
        <p class="text-xs text-gray-400 mt-1">Periode terpilih</p>
      </div>

      <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase">Total Barang Keluar</p>
        <p class="text-2xl font-bold text-blue-700 mt-2 font-mono">-<?= number_format((float)$summary['total_keluar'], 0, ',', '.') ?> <span class="text-xs font-sans text-gray-500 font-normal">item</span></p>
        <p class="text-xs text-gray-400 mt-1">Periode terpilih</p>
      </div>

      <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase">Surat Jalan Distribusi</p>
        <p class="text-2xl font-bold text-gray-800 mt-2 font-mono"><?= (int)$summarySJ['total_sj'] ?> <span class="text-xs font-sans text-gray-500 font-normal">total</span></p>
        <p class="text-xs text-green-600 mt-1 font-medium"><?= (int)$summarySJ['sj_diterima'] ?> diterima, <?= (int)$summarySJ['sj_dikirim'] ?> jalan</p>
      </div>

      <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase">Keluhan Retur</p>
        <p class="text-2xl font-bold text-amber-700 mt-2 font-mono"><?= (int)$summaryRetur['total_retur'] ?> <span class="text-xs font-sans text-gray-500 font-normal">laporan</span></p>
        <p class="text-xs text-gray-500 mt-1"><?= (int)$summaryRetur['retur_selesai'] ?> selesai diproses</p>
      </div>
    </div>

    <!-- Tabel Rincian Mutasi Logistik -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900">Rincian Mutasi Barang Logistik</h2>
        <span class="text-xs text-gray-500"><?= count($rows) ?> data ditemukan</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
            <tr>
              <th class="py-3 px-4">No</th>
              <th class="py-3 px-4">Waktu Transaksi</th>
              <th class="py-3 px-4">Nama Barang</th>
              <th class="py-3 px-4 text-center">Tipe Mutasi</th>
              <th class="py-3 px-4 text-right">Volume / Jumlah</th>
              <th class="py-3 px-4">Petugas Gudang</th>
              <th class="py-3 px-4">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-xs">
            <?php if ($rows): ?>
              <?php foreach ($rows as $idx => $r): ?>
                <tr class="hover:bg-gray-50">
                  <td class="py-3 px-4 text-gray-400 font-mono"><?= $idx + 1 ?></td>
                  <td class="py-3 px-4 whitespace-nowrap text-gray-600">
                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['created_at']))) ?>
                  </td>
                  <td class="py-3 px-4 font-semibold text-gray-900">
                    <?= htmlspecialchars($r['nama_barang']) ?>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <?php if ($r['jenis'] === 'masuk'): ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-green-100 text-green-800">
                        Barang Masuk
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800">
                        Barang Keluar
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-right font-mono font-bold <?= $r['jenis'] === 'masuk' ? 'text-green-700' : 'text-blue-700' ?>">
                    <?= ($r['jenis'] === 'masuk' ? '+' : '-') . number_format($r['jumlah']) . ' ' . htmlspecialchars($r['satuan']) ?>
                  </td>
                  <td class="py-3 px-4 text-gray-700">
                    <?= htmlspecialchars($r['nama_petugas']) ?>
                  </td>
                  <td class="py-3 px-4 text-gray-500">
                    <?= htmlspecialchars($r['catatan'] ?: '-') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="py-8 text-center text-gray-400">Tidak ada riwayat mutasi pada periode ini.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

</body>
</html>
