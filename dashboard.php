<?php
require_once __DIR__ . '/includes/auth.php';
require_login(['admin', 'super_admin']);
$isSuper = user()['role'] === 'super_admin';
$tot = $pdo->query('SELECT COUNT(*) jenis, COALESCE(SUM(stok),0) stok FROM barang')->fetch();
$sj  = $pdo->query("SELECT COUNT(*) FROM surat_jalan WHERE status <> 'diterima'")->fetchColumn();
$ret = $isSuper ? $pdo->query("SELECT COUNT(*) FROM retur WHERE status <> 'selesai'")->fetchColumn() : null;
$low = $pdo->query('SELECT nama, stok, satuan FROM barang WHERE stok <= 20 ORDER BY stok LIMIT 10')->fetchAll();
$mut = $pdo->query('SELECT m.*, b.nama, u.nama_lengkap FROM mutasi m JOIN barang b ON b.id=m.barang_id JOIN users u ON u.id=m.user_id ORDER BY m.id DESC LIMIT 6')->fetchAll();
$kv = [['Jenis barang', $tot['jenis']], ['Total stok', $tot['stok']], ['Surat jalan berjalan', $sj]];
if ($isSuper) $kv[] = ['Retur belum selesai', $ret];
$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="main__inner">
  <header class="page-head">
    <div>
      <h1 class="page-title">Dashboard</h1>
      <p class="page-note">Ringkasan stok dan pergerakan barang.</p>
    </div>
  </header>

  <div class="stats">
    <?php foreach ($kv as [$label, $n]): ?>
      <div class="stat">
        <span class="stat__label"><?= e($label) ?></span>
        <strong class="stat__value"><?= (int) $n ?></strong>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Stok menipis <span>20 atau kurang</span></h2>
  <div class="panel">
    <?php if ($low): ?>
      <div class="table-wrap">
        <table class="data data--compact">
          <caption class="sr-only">Barang dengan stok 20 atau kurang</caption>
          <thead><tr><th scope="col">Barang</th><th scope="col" class="is-num">Sisa</th></tr></thead>
          <tbody>
          <?php foreach ($low as $b): ?>
            <tr>
              <td><?= e($b['nama']) ?></td>
              <td class="num is-num<?= $b['stok'] ? '' : ' is-empty' ?>"><?= (int) $b['stok'] . ' ' . e($b['satuan']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="panel__empty">Semua stok aman.</p>
    <?php endif; ?>
  </div>

  <h2 class="section-title">Mutasi terakhir</h2>
  <div class="panel">
    <?php if ($mut): ?>
      <div class="table-wrap">
        <table class="data data--wide">
          <caption class="sr-only">Enam mutasi stok terakhir</caption>
          <thead>
            <tr>
              <?php foreach (['Tanggal', 'Barang', 'Jenis', 'Jumlah', 'Oleh'] as $h): ?>
                <th scope="col"<?= $h === 'Jumlah' ? ' class="is-num"' : '' ?>><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($mut as $m): ?>
            <tr>
              <td class="num"><?= e(date('d/m/Y', strtotime($m['created_at']))) ?></td>
              <td><?= e($m['nama']) ?></td>
              <td><?= $m['jenis'] === 'masuk' ? 'Masuk' : 'Keluar' ?></td>
              <td class="num is-num"><?= (int) $m['jumlah'] ?></td>
              <td><?= e($m['nama_lengkap']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="panel__empty">Belum ada mutasi.</p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>