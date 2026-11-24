<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user']);

$uid = user()['id'];

// Ambil riwayat pesanan dan surat jalan milik user
$st = $pdo->prepare('
    SELECT p.id AS pesanan_id, p.alamat_tujuan, p.status AS status_pesanan, p.created_at AS tgl_pesan, p.catatan,
           sj.nomor AS nomor_sj, sj.status AS status_sj, sj.created_at AS tgl_sj,
           GROUP_CONCAT(CONCAT(b.nama, " (", pi.jumlah, " ", b.satuan, ")") SEPARATOR "<br>") AS daftar_barang
    FROM pesanan p
    LEFT JOIN surat_jalan sj ON sj.pesanan_id = p.id
    LEFT JOIN pesanan_item pi ON pi.pesanan_id = p.id
    LEFT JOIN barang b ON b.id = pi.barang_id
    WHERE p.user_id = ?
    GROUP BY p.id, sj.id
    ORDER BY p.id DESC
');
$st->execute([$uid]);
$pengiriman = $st->fetchAll();

$title = 'Status Pengiriman Pesanan';
require __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Status Pengiriman</h1>
    <p class="text-sm text-gray-500 mt-1">Lacak dan pantau proses distribusi pesanan grosir Anda.</p>
  </div>
  <a href="<?= BASE ?>user/katalog.php" class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Pesan Barang Baru
  </a>
</div>

<!-- Tabel Pengiriman -->
<div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-sm font-bold text-gray-900">Riwayat Pesanan & Distribusi</h2>
    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600"><?= count($pengiriman) ?> pesanan</span>
  </div>

  <?php if ($pengiriman): ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
          <tr>
            <th class="py-3 px-5">ID Pesanan</th>
            <th class="py-3 px-5">No. Surat Jalan</th>
            <th class="py-3 px-5">Rincian Barang</th>
            <th class="py-3 px-5">Alamat Tujuan</th>
            <th class="py-3 px-5">Waktu Pemesanan</th>
            <th class="py-3 px-5 text-center">Status Distribusi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($pengiriman as $p): ?>
            <tr class="hover:bg-gray-50/75 transition">
              <td class="py-4 px-5 font-mono font-bold text-gray-700 whitespace-nowrap">#<?= (int)$p['pesanan_id'] ?></td>
              <td class="py-4 px-5 whitespace-nowrap">
                <?php if (!empty($p['nomor_sj'])): ?>
                  <span class="inline-flex items-center gap-1 font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">
                    <?= e($p['nomor_sj']) ?>
                  </span>
                <?php else: ?>
                  <span class="text-gray-400 italic">Menunggu Surat Jalan</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-5 font-medium text-gray-800 leading-relaxed">
                <?= $p['daftar_barang'] ?: '<span class="text-gray-400">Pesanan via WhatsApp</span>' ?>
              </td>
              <td class="py-4 px-5 text-gray-600 max-w-xs">
                <?= e($p['alamat_tujuan'] ?: 'Alamat Terdaftar') ?>
              </td>
              <td class="py-4 px-5 text-gray-500 whitespace-nowrap">
                <?= date('d/m/Y H:i', strtotime($p['tgl_pesan'])) ?>
              </td>
              <td class="py-4 px-5 text-center whitespace-nowrap">
                <?php
                $statusSJ = $p['status_sj'] ?? '';
                if ($statusSJ === 'diterima') {
                    echo '<span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800"><span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>Diterima</span>';
                } elseif ($statusSJ === 'dikirim') {
                    echo '<span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800"><span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>Dalam Pengiriman</span>';
                } elseif ($statusSJ === 'disiapkan') {
                    echo '<span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800"><span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>Disiapkan Gudang</span>';
                } else {
                    echo '<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Menunggu Konfirmasi</span>';
                }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="p-12 text-center">
      <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400 mb-3">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      </div>
      <h3 class="text-sm font-bold text-gray-900">Belum Ada Pengiriman</h3>
      <p class="text-xs text-gray-500 mt-1">Anda belum memiliki riwayat pesanan barang yang sedang dikirim.</p>
      <a href="<?= BASE ?>user/katalog.php" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-xs font-bold text-white hover:bg-brand-dark transition">
        Lihat Katalog Barang
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Catatan Layanan Retur -->
<div class="mt-6 rounded-xl border border-green-200 bg-green-50/70 p-4 flex items-start gap-3">
  <svg class="h-5 w-5 text-brand shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
  </svg>
  <div class="text-xs text-gray-700 leading-relaxed">
    <strong class="font-bold text-gray-900">Pemberitahuan Klaim Kerusakan Barang:</strong>
    Jika barang yang Anda terima mengalami kerusakan kemasan, cacat, atau tidak sesuai pesanan, silakan gunakan fitur
    <a href="<?= BASE ?>user/retur.php" class="font-bold text-brand underline hover:text-brand-dark">Retur Barang</a>
    untuk mengajukan klaim penukaran dengan mengunggah foto bukti fisik.
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
