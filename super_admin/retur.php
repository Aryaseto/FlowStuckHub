<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['super_admin']);

// Handle aksi POST (ubah status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int) ($_POST['id'] ?? 0);
    $aksi   = $_POST['aksi'] ?? '';

    if ($id && in_array($aksi, ['diproses', 'selesai'], true)) {
        if ($aksi === 'selesai') {
            $st = $pdo->prepare("UPDATE retur SET status='selesai', tanggal_selesai=NOW() WHERE id=?");
        } else {
            $st = $pdo->prepare("UPDATE retur SET status='diproses' WHERE id=?");
        }
        $st->execute([$id]);
    }
    header('Location: ' . BASE . 'super_admin/retur.php' . (!empty($_POST['filter']) ? '?status=' . urlencode($_POST['filter']) : ''));
    exit;
}

// Filter
$filter  = $_GET['status'] ?? '';
$allowed = ['', 'baru', 'diproses', 'selesai'];
if (!in_array($filter, $allowed, true)) $filter = '';

$sql = '
    SELECT r.*, u.nama_lengkap, b.nama AS nama_barang
    FROM retur r
    JOIN users u ON u.id = r.user_id
    JOIN barang b ON b.id = r.barang_id
';
if ($filter !== '') {
    $sql .= ' WHERE r.status = ' . $pdo->quote($filter);
}
$sql .= ' ORDER BY r.id DESC';

$returs = $pdo->query($sql)->fetchAll();

$statusLabel = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
$statusColor = [
    'baru'     => 'background:#FFF3E0;color:#E65100;',
    'diproses' => 'background:#E3F2FD;color:#0D47A1;',
    'selesai'  => 'background:#E8F5E9;color:#1B5E20;',
];
$u = user();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Retur · FlowStuckHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { brand: '#2E7D32' } } } }
  </script>
  <style>
    [x-cloak]{display:none}
    body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-[#2E7D32] flex flex-col flex-shrink-0">
    <div class="px-6 py-5 border-b border-green-700">
      <span class="flex items-center gap-2 text-white font-bold text-lg">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded bg-[#CDDC39] text-[#2E7D32] font-black text-sm">F</span>
        FlowStuckHub
      </span>
      <p class="text-green-200 text-xs mt-1">Super Admin</p>
    </div>
    <nav class="flex-1 px-4 py-4 flex flex-col gap-1">
      <?php
      $navItems = [
          ['label' => 'Dashboard',     'href' => BASE . 'dashboard.php'],
          ['label' => 'Kelola Barang', 'href' => BASE . 'super_admin/barang.php'],
          ['label' => 'Barang Masuk/Keluar', 'href' => BASE . 'admin/update-barang.php'],
          ['label' => 'Surat Jalan (Filter)', 'href' => BASE . 'admin/surat-jalan.php'],
          ['label' => 'Keluhan Retur', 'href' => BASE . 'super_admin/retur.php'],
          ['label' => 'Laporan',       'href' => BASE . 'super_admin/laporan.php'],
          ['label' => 'Edit Profile',  'href' => BASE . 'profile.php'],
      ];
      $current = basename($_SERVER['PHP_SELF']);
      foreach ($navItems as $item):
          $isActive = basename($item['href']) === $current;
      ?>
        <a href="<?= htmlspecialchars($item['href']) ?>"
           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                  <?= $isActive ? 'bg-white text-[#2E7D32]' : 'text-green-100 hover:bg-green-700' ?>">
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="px-4 py-4 border-t border-green-700">
      <div class="flex items-center gap-3 mb-3">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-600 text-white font-semibold text-sm">
          <?= htmlspecialchars(mb_strtoupper(mb_substr($u['nama'] ?? 'S', 0, 1))) ?>
        </span>
        <div class="min-w-0">
          <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($u['nama'] ?? '') ?></p>
          <p class="text-green-300 text-xs">Super Admin</p>
        </div>
      </div>
      <a href="<?= BASE ?>logout.php"
         class="block w-full text-center px-3 py-2 rounded-lg text-sm font-medium text-green-100 border border-green-600 hover:bg-green-700 transition-colors">
        Keluar
      </a>
    </div>
  </aside>

  <!-- Main content -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white border-b border-gray-200 px-8 py-4">
      <h1 class="text-xl font-bold text-gray-800">Kelola Retur Barang</h1>
      <p class="text-sm text-gray-500 mt-0.5">Kelola semua pengajuan retur dari pelanggan.</p>
    </header>

    <main class="flex-1 overflow-auto px-8 py-6">

      <!-- Filter -->
      <div class="flex gap-2 mb-6 flex-wrap">
        <?php
        $filters = ['' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
        foreach ($filters as $val => $lbl):
            $isActive = $filter === $val;
        ?>
          <a href="<?= BASE ?>super_admin/retur.php<?= $val !== '' ? '?status=' . urlencode($val) : '' ?>"
             class="px-4 py-1.5 rounded-full text-sm font-medium border transition-colors
                    <?= $isActive ? 'bg-[#2E7D32] text-white border-[#2E7D32]' : 'text-gray-600 border-gray-300 hover:border-[#2E7D32] hover:text-[#2E7D32]' ?>">
            <?= htmlspecialchars($lbl) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if ($returs): ?>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <?php foreach (['ID', 'Nama User', 'Barang', 'Deskripsi', 'Status', 'Tanggal Submit', 'Foto', 'Aksi'] as $h): ?>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap"><?= $h ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
              <?php foreach ($returs as $r): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 font-mono text-gray-500">#<?= $r['id'] ?></td>
                  <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                  <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($r['nama_barang']) ?></td>
                  <td class="px-4 py-3 text-gray-600 max-w-xs">
                    <span title="<?= htmlspecialchars($r['deskripsi']) ?>">
                      <?= htmlspecialchars(mb_substr($r['deskripsi'], 0, 60)) ?><?= mb_strlen($r['deskripsi']) > 60 ? '…' : '' ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          style="<?= $statusColor[$r['status']] ?? '' ?>">
                      <?= htmlspecialchars($statusLabel[$r['status']] ?? $r['status']) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                    <?= $r['tanggal_submit'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($r['tanggal_submit']))) : '—' ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php if ($r['foto']): ?>
                      <a href="<?= BASE . htmlspecialchars($r['foto']) ?>" target="_blank" rel="noopener"
                         class="block w-14 h-14 rounded overflow-hidden border border-gray-200 hover:opacity-80 transition-opacity">
                        <img src="<?= BASE . htmlspecialchars($r['foto']) ?>" alt="Foto retur #<?= $r['id'] ?>"
                             class="w-full h-full object-cover" loading="lazy">
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex gap-1 flex-wrap">
                      <?php if ($r['status'] === 'baru'): ?>
                        <form method="post">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= $r['id'] ?>">
                          <input type="hidden" name="aksi" value="diproses">
                          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                          <button type="submit"
                                  class="px-3 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors">
                            Proses
                          </button>
                        </form>
                      <?php endif; ?>
                      <?php if ($r['status'] !== 'selesai'): ?>
                        <form method="post" onsubmit="return confirm('Tandai retur #<?= $r['id'] ?> sebagai selesai?')">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= $r['id'] ?>">
                          <input type="hidden" name="aksi" value="selesai">
                          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                          <button type="submit"
                                  class="px-3 py-1 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-colors">
                            Selesai
                          </button>
                        </form>
                      <?php endif; ?>
                      <?php if ($r['status'] === 'selesai'): ?>
                        <span class="text-xs text-gray-400 italic">Selesai</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="py-16 text-center text-gray-400">
            <svg class="mx-auto w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="font-medium">Belum ada retur<?= $filter ? ' dengan status <strong>' . htmlspecialchars($statusLabel[$filter] ?? $filter) . '</strong>' : '' ?>.</p>
          </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>

</body>
</html>
