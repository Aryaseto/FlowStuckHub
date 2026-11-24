<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['admin', 'super_admin']);

$u    = user();
$isSuper = $u['role'] === 'super_admin';

/* ── Handle POST: update status ───────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $id     = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0 && in_array($action, ['kirim', 'terima'], true)) {
        $newStatus = $action === 'kirim' ? 'dikirim' : 'diterima';
        $oldStatus = $action === 'kirim' ? 'disiapkan' : 'dikirim';

        $stmt = $pdo->prepare('UPDATE surat_jalan SET status = ? WHERE id = ? AND status = ?');
        $stmt->execute([$newStatus, $id, $oldStatus]);

        $_SESSION['flash'] = $stmt->rowCount()
            ? ['type' => 'success', 'msg' => 'Status surat jalan berhasil diperbarui.']
            : ['type' => 'error',   'msg' => 'Gagal memperbarui status atau status tidak sesuai.'];
    }

    $qs = http_build_query(array_filter([
        'status' => $_POST['filter_status'] ?? '',
        'q'      => $_POST['filter_q']      ?? '',
    ]));
    header('Location: surat-jalan.php' . ($qs ? '?' . $qs : ''));
    exit;
}

/* ── Flash message ────────────────────────────────────────── */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ── Filters ──────────────────────────────────────────────── */
$filterStatus = $_GET['status'] ?? '';
$filterQ      = trim($_GET['q'] ?? '');

$validStatuses = ['disiapkan', 'dikirim', 'diterima'];

$sql = <<<SQL
    SELECT sj.id, sj.nomor, sj.status, sj.created_at,
           p.alamat_tujuan,
           u.nama_lengkap AS nama_pembeli
    FROM   surat_jalan sj
    JOIN   pesanan p ON p.id = sj.pesanan_id
    JOIN   users   u ON u.id = p.user_id
    WHERE  1=1
SQL;

$params = [];

if ($filterStatus && in_array($filterStatus, $validStatuses, true)) {
    $sql     .= ' AND sj.status = ?';
    $params[] = $filterStatus;
}

if ($filterQ !== '') {
    $sql     .= ' AND (sj.nomor LIKE ? OR u.nama_lengkap LIKE ?)';
    $like     = '%' . $filterQ . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= ' ORDER BY sj.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$statusLabel = [
    'disiapkan' => ['label' => 'Disiapkan', 'cls' => 'bg-yellow-100 text-yellow-800'],
    'dikirim'   => ['label' => 'Dikirim',   'cls' => 'bg-blue-100 text-blue-800'],
    'diterima'  => ['label' => 'Diterima',  'cls' => 'bg-green-100 text-green-800'],
];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Surat Jalan · FlowStuckHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    [x-cloak]{display:none}
    body{font-family:Inter,sans-serif}
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- ── SIDEBAR ──────────────────────────────────────────── -->
<aside class="w-64 min-h-screen flex flex-col" style="background:#2E7D32">
  <div class="px-6 py-5 border-b border-green-700">
    <a href="<?= BASE ?>dashboard.php" class="flex items-center gap-2 text-white font-bold text-lg no-underline">
      <span class="w-8 h-8 rounded flex items-center justify-center font-black text-green-900" style="background:#CDDC39">F</span>
      FlowStuckHub
    </a>
  </div>

  <nav class="flex-1 px-4 py-4 space-y-1">
    <a href="<?= BASE ?>dashboard.php"
       class="flex items-center gap-2 px-3 py-2 rounded text-green-100 hover:bg-green-700 transition text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      Dashboard
    </a>

    <a href="surat-jalan.php"
       class="flex items-center gap-2 px-3 py-2 rounded text-white font-semibold text-sm"
       style="background:rgba(255,255,255,0.15)" aria-current="page">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Surat Jalan
    </a>

    <a href="update-barang.php"
       class="flex items-center gap-2 px-3 py-2 rounded text-green-100 hover:bg-green-700 transition text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      Update Barang
    </a>

    <a href="<?= BASE ?>profile.php"
       class="flex items-center gap-2 px-3 py-2 rounded text-green-100 hover:bg-green-700 transition text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Edit Profile
    </a>

    <?php if ($isSuper): ?>
    <div class="border-t border-green-700 mt-3 pt-3">
      <p class="text-green-300 text-xs px-3 mb-1 font-semibold uppercase tracking-wide">Menu Super Admin</p>
      <a href="<?= BASE ?>super_admin/barang.php"
         class="flex items-center gap-2 px-3 py-1.5 rounded text-green-100 hover:bg-green-700 transition text-sm">
        Kelola Barang (CRUD)
      </a>
      <a href="<?= BASE ?>super_admin/retur.php"
         class="flex items-center gap-2 px-3 py-1.5 rounded text-green-100 hover:bg-green-700 transition text-sm">
        Keluhan Retur
      </a>
      <a href="<?= BASE ?>super_admin/laporan.php"
         class="flex items-center gap-2 px-3 py-1.5 rounded text-green-100 hover:bg-green-700 transition text-sm">
        Laporan Logistik
      </a>
    </div>
    <?php endif; ?>
  </nav>

  <!-- Profile footer -->
  <div class="px-4 py-4 border-t border-green-700">
    <div class="flex items-center gap-3 mb-3">
      <span class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm"
            style="background:#CDDC39;color:#1B5E20">
        <?php
          $initials = '';
          foreach (array_slice(preg_split('/\s+/', trim($u['nama'] ?? $u['nama_lengkap'] ?? '')), 0, 2) as $k)
              $initials .= mb_strtoupper(mb_substr($k, 0, 1));
          echo e($initials);
        ?>
      </span>
      <div class="text-sm">
        <div class="text-white font-semibold"><?= e($u['nama'] ?? $u['nama_lengkap'] ?? '') ?></div>
        <div class="text-green-300 text-xs"><?= $isSuper ? 'Super Admin' : 'Admin Gudang' ?></div>
      </div>
    </div>
    <a href="<?= BASE ?>logout.php"
       class="block text-center text-sm text-green-100 border border-green-500 rounded py-1.5 hover:bg-green-700 transition">
      Keluar
    </a>
  </div>
</aside>

<!-- ── MAIN ──────────────────────────────────────────────── -->
<main class="flex-1 p-6 overflow-auto">
  <div class="max-w-6xl mx-auto">

    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Surat Jalan</h1>
      <p class="text-gray-500 text-sm mt-1">Kelola dan perbarui status pengiriman surat jalan.</p>
    </div>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="mb-4 px-4 py-3 rounded text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
      <?= e($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
      <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
          <select name="status" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">Semua</option>
            <?php foreach ($validStatuses as $s): ?>
            <option value="<?= e($s) ?>"<?= $filterStatus === $s ? ' selected' : ''?>>
              <?= ucfirst($s) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex-1 min-w-48">
          <label class="block text-xs font-medium text-gray-600 mb-1">Cari (No. SJ / Nama Pembeli)</label>
          <input type="text" name="q" value="<?= e($filterQ) ?>" placeholder="Ketik untuk cari..."
                 class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <button type="submit"
                class="px-4 py-2 rounded text-white text-sm font-medium transition"
                style="background:#2E7D32" onmouseover="this.style.background='#1B5E20'" onmouseout="this.style.background='#2E7D32'">
          Filter
        </button>
        <a href="surat-jalan.php"
           class="px-4 py-2 rounded text-gray-600 text-sm border hover:bg-gray-50 transition">Reset</a>
      </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <?php if ($rows): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">No. SJ</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Tanggal</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Nama Pembeli</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Alamat Tujuan</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Status</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($rows as $row): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono font-semibold text-gray-800"><?= e($row['nomor']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e(date('d/m/Y', strtotime($row['created_at']))) ?></td>
              <td class="px-4 py-3 text-gray-800"><?= e($row['nama_pembeli']) ?></td>
              <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="<?= e($row['alamat_tujuan']) ?>"><?= e($row['alamat_tujuan']) ?></td>
              <td class="px-4 py-3">
                <?php $st = $statusLabel[$row['status']] ?? ['label' => $row['status'], 'cls' => 'bg-gray-100 text-gray-700']; ?>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $st['cls'] ?>">
                  <?= e($st['label']) ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <?php if ($row['status'] === 'disiapkan'): ?>
                <form method="POST" class="inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="kirim">
                  <input type="hidden" name="filter_status" value="<?= e($filterStatus) ?>">
                  <input type="hidden" name="filter_q" value="<?= e($filterQ) ?>">
                  <button type="submit"
                          class="px-3 py-1.5 rounded text-white text-xs font-semibold transition"
                          style="background:#1976D2"
                          onmouseover="this.style.background='#1565C0'" onmouseout="this.style.background='#1976D2'"
                          onclick="return confirm('Tandai surat jalan ini sebagai DIKIRIM?')">
                    Kirim
                  </button>
                </form>
                <?php elseif ($row['status'] === 'dikirim'): ?>
                <form method="POST" class="inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="terima">
                  <input type="hidden" name="filter_status" value="<?= e($filterStatus) ?>">
                  <input type="hidden" name="filter_q" value="<?= e($filterQ) ?>">
                  <button type="submit"
                          class="px-3 py-1.5 rounded text-white text-xs font-semibold transition"
                          style="background:#2E7D32"
                          onmouseover="this.style.background='#1B5E20'" onmouseout="this.style.background='#2E7D32'"
                          onclick="return confirm('Konfirmasi bahwa barang sudah DITERIMA?')">
                    Konfirmasi Terima
                  </button>
                </form>
                <?php else: ?>
                <span class="text-gray-400 text-xs">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">Tidak ada surat jalan ditemukan.</p>
      </div>
      <?php endif; ?>
    </div>

    <p class="text-xs text-gray-400 mt-3"><?= count($rows) ?> data ditampilkan.</p>

  </div>
</main>

</body>
</html>
