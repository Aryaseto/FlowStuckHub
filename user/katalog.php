<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user']);
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT * FROM barang WHERE nama LIKE ? ORDER BY nama');
$st->execute(['%' . $q . '%']);
$items = $st->fetchAll();
// Inisial dua huruf; beri pembeda bila dua barang berawalan sama.
$marks = [];
$terpakai = [];
foreach ($items as $b) {
    $m = mb_substr($b['nama'], 0, 2);
    if (isset($terpakai[$m])) {
        $kata = preg_split('/\s+/', trim($b['nama'])) ?: [$b['nama']];
        $m = mb_substr($kata[0], 0, 1) . mb_substr($kata[1] ?? $kata[0], 0, 1);
    }
    $terpakai[$m] = true;
    $marks[$b['id']] = $m;
}
$title = 'Katalog';
require __DIR__ . '/../includes/header.php';
?>
<div class="main__inner">
  <header class="page-head">
    <div>
      <h1 class="page-title">Katalog barang</h1>
      <p class="page-note">
        <?= count($items) ?> barang<?= $q !== '' ? ' cocok dengan &ldquo;' . e($q) . '&rdquo;' : ' di katalog' ?>.<?php if ($q !== ''): ?> <a class="text-link" href="<?= BASE ?>user/katalog.php">Hapus pencarian</a><?php endif; ?>
      </p>
    </div>
  </header>

  <form class="search" role="search" method="get" action="<?= BASE ?>user/katalog.php">
    <label class="sr-only" for="q">Cari nama barang</label>
    <input class="input" id="q" name="q" value="<?= e($q) ?>" placeholder="Cari nama barang" autocomplete="off">
    <button class="btn btn--primary" type="submit">Cari</button>
  </form>

  <?php if ($items): ?>
    <ul class="catalog">
      <?php foreach ($items as $b): $stok = (int) $b['stok']; ?>
        <li class="item<?= $stok < 1 ? ' item--empty' : '' ?>">
          <span class="item__mark" aria-hidden="true">
            <?php if (!empty($b['gambar'])): ?>
              <img src="<?= BASE . e($b['gambar']) ?>" alt="">
            <?php else: ?>
              <?= e($marks[$b['id']]) ?>
            <?php endif; ?>
          </span>

          <div class="item__body">
            <h2 class="item__name"><?= e($b['nama']) ?></h2>
            <p class="item__desc"><?= e($b['deskripsi']) ?></p>
          </div>

          <div class="item__actions">
            <?php if ($stok < 1): ?>
              <p class="stock stock--empty">Habis</p>
            <?php else: ?>
              <p class="stock">Tersedia <b><?= $stok ?> <?= e($b['satuan']) ?></b></p>
            <?php endif; ?>
            <button
              type="button"
              class="btn btn--primary btn--sm"
              data-add
              data-nama="<?= e($b['nama']) ?>"
              data-satuan="<?= e($b['satuan']) ?>"
              data-stok="<?= $stok ?>"
              aria-label="Tambah <?= e($b['nama']) ?> ke draft pesanan"
            >Tambah</button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="empty">
      <?php if ($q !== ''): ?>
        <h2>Tidak ada barang yang cocok</h2>
        <p>Pencarian &ldquo;<?= e($q) ?>&rdquo; tidak menemukan barang. Coba kata kunci lain atau tampilkan semua barang.</p>
        <a class="btn btn--ghost" href="<?= BASE ?>user/katalog.php">Tampilkan semua barang</a>
      <?php else: ?>
        <h2>Katalog masih kosong</h2>
        <p>Belum ada barang yang tercatat. Hubungi admin gudang untuk menambahkan stok.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<div class="draft" id="draft" hidden>
  <div class="draft__text" role="status" aria-live="polite">
    <strong id="draft-count">0 item</strong>
    <span>Draft pesanan, belum dikirim ke gudang</span>
  </div>
  <button class="btn btn--on-dark btn--sm" type="button" id="draft-clear">Kosongkan</button>
</div>

<dialog class="modal" id="stok-modal" aria-labelledby="stok-title" aria-describedby="stok-body">
  <div class="modal__body">
    <h2 id="stok-title">Stok habis</h2>
    <p id="stok-body"></p>
  </div>
  <form method="dialog" class="modal__foot">
    <button class="btn btn--ghost btn--sm" value="tutup">Tutup</button>
  </form>
</dialog>

<script>
// Draft pesanan hanya tersimpan di tab ini, belum dikirim ke server.
const draft = new Map();
const draftBar = document.getElementById('draft');
const draftCount = document.getElementById('draft-count');
const draftClear = document.getElementById('draft-clear');
const modal = document.getElementById('stok-modal');
const modalTitle = document.getElementById('stok-title');
const modalBody = document.getElementById('stok-body');

function draftTotal() {
  let total = 0;
  draft.forEach(jumlah => { total += jumlah; });
  return total;
}

function renderDraft() {
  const total = draftTotal();
  draftCount.textContent = total + ' item';
  draftBar.hidden = total === 0;
  document.body.classList.toggle('has-draft', total > 0);
}

function beriTahu(judul, pesan) {
  modalTitle.textContent = judul;
  modalBody.textContent = pesan;
  modal.showModal();
}

document.querySelectorAll('[data-add]').forEach(btn => {
  btn.addEventListener('click', () => {
    const nama = btn.dataset.nama;
    const satuan = btn.dataset.satuan;
    const stok = Number(btn.dataset.stok);
    const jumlah = draft.get(nama) || 0;

    if (stok < 1) {
      beriTahu('Stok habis', nama + ' sedang habis. Coba lagi setelah stok masuk.');
      return;
    }
    if (jumlah >= stok) {
      beriTahu('Stok tidak mencukupi', 'Sisa stok ' + nama + ' hanya ' + stok + ' ' + satuan + '.');
      return;
    }

    draft.set(nama, jumlah + 1);
    renderDraft();
  });
});

draftClear.addEventListener('click', () => {
  draft.clear();
  renderDraft();
});

renderDraft();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>