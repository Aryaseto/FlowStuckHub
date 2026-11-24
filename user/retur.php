<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user']);

$uid = user()['id'];

// Flash message
$success = '';
if (!empty($_SESSION['retur_success'])) {
    $success = $_SESSION['retur_success'];
    unset($_SESSION['retur_success']);
}

// Ambil daftar retur milik user ini
$st = $pdo->prepare('
    SELECT r.*, b.nama AS nama_barang, b.satuan
    FROM retur r
    JOIN barang b ON b.id = r.barang_id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
');
$st->execute([$uid]);
$returs = $st->fetchAll();

$title = 'Klaim Retur Barang Rusak';
require __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Keluhan Retur Barang</h1>
    <p class="text-sm text-gray-500 mt-1">Daftar pengajuan pengembalian atau penukaran barang yang rusak / cacat kemasan.</p>
  </div>
  <a href="<?= BASE ?>user/retur-form.php" class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Ajukan Retur Baru
  </a>
</div>

<?php if ($success): ?>
  <div class="mt-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-xs flex items-center gap-3">
    <svg class="h-5 w-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span><?= e($success) ?></span>
  </div>
<?php endif; ?>

<!-- Tabel Daftar Retur -->
<div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-sm font-bold text-gray-900">Riwayat Pengajuan Retur</h2>
    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600"><?= count($returs) ?> pengajuan</span>
  </div>

  <?php if ($returs): ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
          <tr>
            <th class="py-3 px-5">No</th>
            <th class="py-3 px-5">Foto Bukti</th>
            <th class="py-3 px-5">Nama Barang</th>
            <th class="py-3 px-5">Deskripsi Kerusakan</th>
            <th class="py-3 px-5 text-center">Status Retur</th>
            <th class="py-3 px-5">Tanggal Submit</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($returs as $i => $r): ?>
            <tr class="hover:bg-gray-50/75 transition">
              <td class="py-4 px-5 text-gray-400 font-mono"><?= $i + 1 ?></td>
              <td class="py-4 px-5 whitespace-nowrap">
                <?php if (!empty($r['foto'])): ?>
                  <a href="<?= BASE . e($r['foto']) ?>" target="_blank" class="inline-block relative group">
                    <img src="<?= BASE . e($r['foto']) ?>" alt="Bukti" class="h-12 w-12 rounded-lg object-cover border border-gray-200 shadow-xs group-hover:opacity-90">
                    <span class="text-[10px] text-brand block mt-0.5 underline">Lihat Foto</span>
                  </a>
                <?php else: ?>
                  <span class="text-gray-300">-</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-5 font-bold text-gray-900 whitespace-nowrap">
                <?= e($r['nama_barang']) ?>
              </td>
              <td class="py-4 px-5 text-gray-600 max-w-sm leading-relaxed">
                <?= e($r['deskripsi']) ?>
              </td>
              <td class="py-4 px-5 text-center whitespace-nowrap">
                <?php if ($r['status'] === 'selesai'): ?>
                  <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>Selesai / Diganti
                  </span>
                <?php elseif ($r['status'] === 'diproses'): ?>
                  <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>Sedang Diproses
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>Menunggu Review
                  </span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-5 font-mono text-gray-600 whitespace-nowrap">
                <?php if ($r['tanggal_submit']): ?>
                  <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($r['tanggal_submit'])) ?></span>
                  <span class="text-gray-400 text-[10px] block"><?= date('H:i', strtotime($r['tanggal_submit'])) ?> WIB</span>
                <?php else: ?>
                  <span class="text-gray-400 italic">Tanggal belum muncul</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="p-12 text-center">
      <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400 mb-3">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h3 class="text-sm font-bold text-gray-900">Belum Ada Pengajuan Retur</h3>
      <p class="text-xs text-gray-500 mt-1">Semua barang yang Anda terima dalam kondisi baik.</p>
      <a href="<?= BASE ?>user/retur-form.php" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-xs font-bold text-white hover:bg-brand-dark transition">
        Ajukan Keluhan Barang
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Catatan Sesuai Arahan Foto -->
<p class="mt-4 text-xs text-gray-500 italic">
  * Catatan: Tanggal akan otomatis muncul pada tabel setelah keluhan retur berhasil disubmit. Keluhan Anda akan ditinjau langsung oleh Super Admin Purchasing.
</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
