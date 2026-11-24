<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['admin', 'super_admin']);

$u = user();
$isSuper = $u['role'] === 'super_admin';
$error = '';
$success = '';

// Handle POST: Update barang masuk / keluar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $barang_id = (int)($_POST['barang_id'] ?? 0);
    $jenis     = $_POST['jenis'] ?? '';
    $jumlah    = (int)($_POST['jumlah'] ?? 0);
    $catatan   = trim($_POST['catatan'] ?? '');

    if ($barang_id <= 0) {
        $error = 'Pilih barang terlebih dahulu.';
    } elseif (!in_array($jenis, ['masuk', 'keluar'], true)) {
        $error = 'Pilih jenis mutasi yang valid (masuk atau keluar).';
    } elseif ($jumlah <= 0) {
        $error = 'Jumlah barang harus lebih dari 0.';
    } else {
        $stBarang = $pdo->prepare('SELECT id, nama, stok, satuan FROM barang WHERE id = ?');
        $stBarang->execute([$barang_id]);
        $barang = $stBarang->fetch();

        if (!$barang) {
            $error = 'Barang tidak ditemukan.';
        } elseif ($jenis === 'keluar' && $jumlah > (int)$barang['stok']) {
            $error = 'Stok tidak mencukupi! Stok saat ini: ' . (int)$barang['stok'] . ' ' . $barang['satuan'] . ', permintaan keluar: ' . $jumlah . ' ' . $barang['satuan'] . '.';
        } else {
            // Update stok barang
            if ($jenis === 'masuk') {
                $upd = $pdo->prepare('UPDATE barang SET stok = stok + ? WHERE id = ?');
            } else {
                $upd = $pdo->prepare('UPDATE barang SET stok = stok - ? WHERE id = ?');
            }
            $upd->execute([$jumlah, $barang_id]);

            // Catat mutasi
            $insMutasi = $pdo->prepare('INSERT INTO mutasi (barang_id, user_id, jenis, jumlah, catatan) VALUES (?, ?, ?, ?, ?)');
            $insMutasi->execute([$barang_id, $u['id'], $jenis, $jumlah, $catatan ?: ($jenis === 'masuk' ? 'Penerimaan barang masuk' : 'Pengeluaran barang')]);

            $success = 'Berhasil memperbarui barang ' . ($jenis === 'masuk' ? 'masuk' : 'keluar') . ' (' . $jumlah . ' ' . $barang['satuan'] . ' ' . $barang['nama'] . ').';
        }
    }
}

// Ambil semua barang untuk dropdown
$listBarang = $pdo->query('SELECT id, nama, stok, satuan FROM barang ORDER BY nama')->fetchAll();

// Ambil riwayat mutasi terkini
$listMutasi = $pdo->query('
    SELECT m.*, b.nama AS nama_barang, b.satuan, u.nama_lengkap AS nama_petugas
    FROM mutasi m
    JOIN barang b ON b.id = m.barang_id
    JOIN users u ON u.id = m.user_id
    ORDER BY m.id DESC
    LIMIT 15
')->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Update Barang Masuk & Keluar · FlowStuckHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- ── SIDEBAR ──────────────────────────────────────────── -->
<aside class="w-64 min-h-screen flex flex-col flex-shrink-0" style="background:#2E7D32">
  <div class="px-6 py-5 border-b border-green-700">
    <a href="<?= BASE ?>dashboard.php" class="flex items-center gap-2 text-white font-bold text-lg no-underline">
      <span class="w-8 h-8 rounded flex items-center justify-center font-black text-green-900" style="background:#CDDC39">F</span>
      FlowStuckHub
    </a>
    <p class="text-green-200 text-xs mt-1"><?= $isSuper ? 'Super Admin Purchasing' : 'Admin Gudang / Kurir' ?></p>
  </div>

  <nav class="flex-1 px-4 py-4 flex flex-col gap-1">
    <a href="<?= BASE ?>dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Dashboard
    </a>
    <?php if ($isSuper): ?>
      <a href="<?= BASE ?>super_admin/barang.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
        Kelola Barang (CRUD)
      </a>
    <?php endif; ?>
    <a href="<?= BASE ?>admin/update-barang.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-white text-[#2E7D32] font-semibold transition">
      Update Barang Masuk/Keluar
    </a>
    <a href="<?= BASE ?>admin/surat-jalan.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Surat Jalan
    </a>
    <?php if ($isSuper): ?>
      <a href="<?= BASE ?>super_admin/retur.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
        Keluhan Retur
      </a>
      <a href="<?= BASE ?>super_admin/laporan.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
        Laporan
      </a>
    <?php endif; ?>
    <a href="<?= BASE ?>profile.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-green-100 hover:bg-green-700 transition">
      Edit Profile
    </a>
  </nav>

  <div class="px-4 py-4 border-t border-green-700">
    <div class="flex items-center gap-3 mb-3">
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-600 text-white font-semibold text-sm">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($u['nama'] ?? 'A', 0, 1))) ?>
      </span>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($u['nama']) ?></p>
        <p class="text-xs text-green-200"><?= $isSuper ? 'Super Admin' : 'Admin Gudang' ?></p>
      </div>
    </div>
    <a href="<?= BASE ?>logout.php" class="block w-full text-center py-1.5 px-3 rounded text-xs text-green-100 border border-green-600 hover:bg-green-700 transition">
      Keluar
    </a>
  </div>
</aside>

<!-- ── CONTENT ──────────────────────────────────────────── -->
<main class="flex-1 p-8 overflow-y-auto">
  <div class="max-w-5xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Update Barang Masuk & Keluar</h1>
      <p class="text-sm text-gray-500 mt-1">Perbarui mutasi logistik barang fisik masuk dan barang keluar dari gudang.</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Formulir Input Mutasi -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 lg:col-span-1">
        <h2 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-100">Catat Mutasi Stok</h2>

        <form method="post" action="<?= BASE ?>admin/update-barang.php" class="space-y-4">
          <?= csrf_field() ?>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1" for="barang_id">Pilih Barang</label>
            <select name="barang_id" id="barang_id" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-green-600 focus:outline-none" onchange="updateInfoStok(this)">
              <option value="">-- Pilih Barang --</option>
              <?php foreach ($listBarang as $b): ?>
                <option value="<?= $b['id'] ?>" data-stok="<?= $b['stok'] ?>" data-satuan="<?= htmlspecialchars($b['satuan']) ?>">
                  <?= htmlspecialchars($b['nama']) ?> (Stok: <?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <p id="stok-info" class="text-xs text-gray-500 mt-1.5 font-medium"></p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Transaksi</label>
            <div class="grid grid-cols-2 gap-3 mt-1">
              <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                <input type="radio" name="jenis" value="masuk" checked class="text-green-600 focus:ring-green-600">
                <span class="text-xs font-semibold text-green-800">Barang Masuk (+)</span>
              </label>
              <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="jenis" value="keluar" class="text-blue-600 focus:ring-blue-600">
                <span class="text-xs font-semibold text-blue-800">Barang Keluar (-)</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1" for="jumlah">Jumlah Barang</label>
            <input type="number" name="jumlah" id="jumlah" min="1" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none" placeholder="Masukkan jumlah">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1" for="catatan">Catatan / Keterangan</label>
            <input type="text" name="catatan" id="catatan" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none" placeholder="Misal: Penerimaan suplier, kirim pesanan...">
          </div>

          <button type="submit" class="w-full py-2.5 px-4 bg-[#2E7D32] text-white text-sm font-semibold rounded-lg hover:bg-green-800 transition active:scale-[0.98]">
            Simpan Mutasi Stok
          </button>
        </form>
      </div>

      <!-- Riwayat Mutasi Terkini -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-900">Riwayat Mutasi Terakhir</h2>
          <span class="text-xs text-gray-500">15 transaksi terbaru</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
              <tr>
                <th class="py-3 px-3">Waktu</th>
                <th class="py-3 px-3">Nama Barang</th>
                <th class="py-3 px-3 text-center">Jenis</th>
                <th class="py-3 px-3 text-right">Jumlah</th>
                <th class="py-3 px-3">Petugas</th>
                <th class="py-3 px-3">Catatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if ($listMutasi): ?>
                <?php foreach ($listMutasi as $m): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="py-3 px-3 text-gray-500 whitespace-nowrap">
                      <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))) ?>
                    </td>
                    <td class="py-3 px-3 font-semibold text-gray-900">
                      <?= htmlspecialchars($m['nama_barang']) ?>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <?php if ($m['jenis'] === 'masuk'): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-green-100 text-green-800">
                          Masuk
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800">
                          Keluar
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 text-right font-mono font-semibold text-gray-900">
                      <?= ($m['jenis'] === 'masuk' ? '+' : '-') . (int)$m['jumlah'] . ' ' . htmlspecialchars($m['satuan']) ?>
                    </td>
                    <td class="py-3 px-3 text-gray-600">
                      <?= htmlspecialchars($m['nama_petugas']) ?>
                    </td>
                    <td class="py-3 px-3 text-gray-500">
                      <?= htmlspecialchars($m['catatan'] ?: '-') ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="py-6 text-center text-gray-400">Belum ada riwayat mutasi barang.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
function updateInfoStok(sel) {
  const opt = sel.options[sel.selectedIndex];
  const info = document.getElementById('stok-info');
  if (opt && opt.value) {
    info.textContent = 'Stok saat ini di gudang: ' + opt.dataset.stok + ' ' + opt.dataset.satuan;
  } else {
    info.textContent = '';
  }
}
</script>
</body>
</html>
