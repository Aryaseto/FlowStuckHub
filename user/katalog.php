<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user']);

const ADMIN_WA = '6281234567890'; // Nomor WhatsApp Admin Purchasing/Gudang

// Handle AJAX checkout pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    header('Content-Type: application/json');
    csrf_check();
    $rawItems = json_decode($_POST['items'] ?? '[]', true);
    if (empty($rawItems) || !is_array($rawItems)) {
        echo json_encode(['success' => false, 'error' => 'Pesanan kosong.']);
        exit;
    }

    $u = user();
    $stUser = $pdo->prepare('SELECT alamat, no_hp FROM users WHERE id = ?');
    $stUser->execute([$u['id']]);
    $userData = $stUser->fetch();
    $alamat = $userData['alamat'] ?? 'Alamat belum diatur';

    $stPesanan = $pdo->prepare('INSERT INTO pesanan (user_id, alamat_tujuan, status, catatan) VALUES (?, ?, "pending", "Dipesan via Web - Konfirmasi via WhatsApp")');
    $stPesanan->execute([$u['id'], $alamat]);
    $pesananId = $pdo->lastInsertId();

    $stItem = $pdo->prepare('INSERT INTO pesanan_item (pesanan_id, barang_id, jumlah) VALUES (?, ?, ?)');
    foreach ($rawItems as $it) {
        $bId = (int)($it['id'] ?? 0);
        $qty = (int)($it['qty'] ?? 1);
        if ($bId > 0 && $qty > 0) {
            $stItem->execute([$pesananId, $bId, $qty]);
        }
    }

    echo json_encode(['success' => true, 'pesanan_id' => $pesananId]);
    exit;
}

$q = trim($_GET['q'] ?? '');

// Berdasarkan arahan foto:
// "di beranda jika barang tidak tersedia maka barang tidak di tampilkan pada beranda tersebut. hanya bisa di lihat jika di cari saja"
if ($q === '') {
    // Beranda: HANYA menampilkan barang yang avail / tersedia (stok > 0)
    $st = $pdo->prepare('SELECT * FROM barang WHERE stok > 0 ORDER BY nama');
    $st->execute();
} else {
    // Pencarian: menampilkan semua barang termasuk yang stok = 0
    $st = $pdo->prepare('SELECT * FROM barang WHERE nama LIKE ? ORDER BY nama');
    $st->execute(['%' . $q . '%']);
}
$items = $st->fetchAll();

// Inisial dua huruf untuk ikon cadangan
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

$title = 'Katalog Barang Grosir';
require __DIR__ . '/../includes/header.php';
?>

<!-- Header Katalog -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Katalog Barang Grosir</h1>
    <p class="text-sm text-gray-500 mt-1">
      <?php if ($q !== ''): ?>
        Menampilkan hasil pencarian &ldquo;<strong><?= e($q) ?></strong>&rdquo; (<?= count($items) ?> barang ditemukan).
        <a class="text-brand font-semibold underline ml-1 hover:text-brand-dark" href="<?= BASE ?>user/katalog.php">Reset pencarian</a>
      <?php else: ?>
        Daftar barang grosir tersedia siap pesan dan kirim langsung dari gudang.
      <?php endif; ?>
    </p>
  </div>

  <!-- Search Input -->
  <form method="get" action="<?= BASE ?>user/katalog.php" class="relative w-full md:w-80">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400">
      <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input
      type="search"
      name="q"
      value="<?= e($q) ?>"
      placeholder="Cari nama barang grosir..."
      autocomplete="off"
      class="h-11 w-full rounded-xl border border-gray-300 bg-white pl-10 pr-20 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
    >
    <button type="submit" class="absolute right-1.5 top-1.5 h-8 rounded-lg bg-brand px-3 text-xs font-bold text-white hover:bg-brand-dark transition">
      Cari
    </button>
  </form>
</div>

<!-- Grid Produk Modern -->
<?php if ($items): ?>
  <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($items as $b): $stok = (int)$b['stok']; ?>
      <div class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg">
        <div>
          <!-- Thumbnail / Badge -->
          <div class="relative aspect-video w-full rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden mb-4 border border-gray-100">
            <?php if (!empty($b['gambar']) && file_exists(__DIR__ . '/../' . $b['gambar'])): ?>
              <img src="<?= BASE . e($b['gambar']) ?>" alt="<?= e($b['nama']) ?>" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            <?php else: ?>
              <div class="flex flex-col items-center justify-center text-brand">
                <span class="grid h-12 w-12 place-items-center rounded-xl bg-brand/10 text-lg font-black tracking-wider text-brand">
                  <?= e($marks[$b['id']]) ?>
                </span>
              </div>
            <?php endif; ?>

            <!-- Badge Ketersediaan -->
            <div class="absolute top-2.5 right-2.5">
              <?php if ($stok < 1): ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 shadow-sm">
                  Stok Habis
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-800 shadow-sm">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                  Tersedia
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Deskripsi Produk -->
          <h2 class="text-base font-bold text-gray-900 group-hover:text-brand transition"><?= e($b['nama']) ?></h2>
          <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed"><?= e($b['deskripsi'] ?: 'Barang logistik grosir terdaftar di sistem FlowStuckHub.') ?></p>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Sisa Stok</p>
            <p class="text-sm font-extrabold font-mono <?= $stok < 1 ? 'text-red-600' : 'text-gray-900' ?>">
              <?= $stok ?> <span class="text-xs font-sans text-gray-500 font-normal"><?= e($b['satuan']) ?></span>
            </p>
          </div>

          <div>
            <?php if ($stok < 1): ?>
              <button
                type="button"
                data-empty
                data-nama="<?= e($b['nama']) ?>"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition"
              >
                Cek Ketersediaan
              </button>
            <?php else: ?>
              <button
                type="button"
                data-add
                data-id="<?= $b['id'] ?>"
                data-nama="<?= e($b['nama']) ?>"
                data-satuan="<?= e($b['satuan']) ?>"
                data-stok="<?= $stok ?>"
                class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="mt-12 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center max-w-lg mx-auto">
    <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
    </div>
    <h3 class="mt-4 text-base font-bold text-gray-900">Barang Tidak Ditemukan</h3>
    <p class="mt-1 text-xs text-gray-500">Tidak ada barang yang cocok dengan pencarian Anda. Silakan coba kata kunci lain.</p>
    <a href="<?= BASE ?>user/katalog.php" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-xs font-bold text-white hover:bg-brand-dark transition">
      Tampilkan Semua Barang
    </a>
  </div>
<?php endif; ?>

<!-- Bilah Floating Pesanan & WhatsApp Checkout -->
<div id="draft" hidden class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 w-[92%] max-w-2xl rounded-2xl bg-gray-900/95 backdrop-blur-md p-4 text-white shadow-2xl border border-gray-700/50 flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300">
  <div class="min-w-0 text-center sm:text-left">
    <div class="flex items-center justify-center sm:justify-start gap-2">
      <span class="inline-flex h-2 w-2 rounded-full bg-accent animate-pulse"></span>
      <strong id="draft-count" class="text-sm font-bold text-white">0 item dipilih</strong>
    </div>
    <p id="draft-summary" class="text-xs text-gray-300 truncate max-w-sm mt-0.5"></p>
  </div>
  <div class="flex items-center gap-2.5 shrink-0">
    <button type="button" id="draft-clear" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-xs font-semibold text-gray-300 hover:bg-gray-700 hover:text-white transition">
      Kosongkan
    </button>
    <button type="button" id="draft-buy" class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-4 py-2 text-xs font-extrabold text-white shadow-md hover:bg-emerald-600 transition active:scale-[0.98]">
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
      Beli Sekarang (WhatsApp) &rarr;
    </button>
  </div>
</div>

<!-- Modal Pop Up Stok Habis / Ketersediaan -->
<dialog id="stok-modal" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl backdrop:bg-gray-900/50 backdrop:backdrop-blur-sm max-w-sm w-full mx-auto my-auto">
  <div class="text-center">
    <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-red-100 text-red-600 mb-3">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
    </div>
    <h3 id="stok-title" class="text-base font-bold text-gray-900">Ketersediaan Barang</h3>
    <p id="stok-body" class="mt-2 text-xs text-gray-500 leading-relaxed"></p>
    <div class="mt-5">
      <form method="dialog">
        <button class="w-full rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-gray-800 transition">
          Mengerti
        </button>
      </form>
    </div>
  </div>
</dialog>

<form id="csrf-form" style="display:none;"><?= csrf_field() ?></form>

<script>
const ADMIN_WA = '<?= ADMIN_WA ?>';
const USER_NAMA = '<?= e(user()['nama']) ?>';

const draft = new Map();
const draftBar = document.getElementById('draft');
const draftCount = document.getElementById('draft-count');
const draftSummary = document.getElementById('draft-summary');
const draftClear = document.getElementById('draft-clear');
const draftBuy = document.getElementById('draft-buy');

const modal = document.getElementById('stok-modal');
const modalTitle = document.getElementById('stok-title');
const modalBody = document.getElementById('stok-body');

function draftTotal() {
  let total = 0;
  draft.forEach(item => { total += item.qty; });
  return total;
}

function renderDraft() {
  const total = draftTotal();
  if (total > 0) {
    draftCount.textContent = total + ' item dipilih';
    const ringkasan = [];
    draft.forEach(item => {
      ringkasan.push(item.nama + ' (' + item.qty + ' ' + item.satuan + ')');
    });
    draftSummary.textContent = ringkasan.join(', ');
    draftBar.hidden = false;
  } else {
    draftBar.hidden = true;
  }
}

function beriTahu(judul, pesan) {
  modalTitle.textContent = judul;
  modalBody.textContent = pesan;
  modal.showModal();
}

// Tombol Tambah Barang
document.querySelectorAll('[data-add]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const nama = btn.dataset.nama;
    const satuan = btn.dataset.satuan;
    const stok = Number(btn.dataset.stok);
    const existing = draft.get(id);
    const currentQty = existing ? existing.qty : 0;

    if (stok < 1) {
      beriTahu('Stok Habis', 'Maaf, ' + nama + ' saat ini sedang tidak tersedia.');
      return;
    }
    if (currentQty >= stok) {
      beriTahu('Batas Stok Tercapai', 'Sisa stok ' + nama + ' hanya ' + stok + ' ' + satuan + '.');
      return;
    }

    draft.set(id, { id, nama, satuan, stok, qty: currentQty + 1 });
    renderDraft();
  });
});

// Pop-up Barang Tidak Tersedia
document.querySelectorAll('[data-empty]').forEach(btn => {
  btn.addEventListener('click', () => {
    const nama = btn.dataset.nama;
    beriTahu('Barang Tidak Tersedia', 'Barang "' + nama + '" saat ini stoknya kosong / tidak tersedia di gudang.');
  });
});

// Kosongkan draft
draftClear.addEventListener('click', () => {
  draft.clear();
  renderDraft();
});

// Beli Sekarang -> Simpan ke database & buka WhatsApp
draftBuy.addEventListener('click', async () => {
  if (draft.size === 0) return;

  const csrf = document.querySelector('#csrf-form input[name="csrf"]').value;
  const items = [];
  let pesanWa = 'Halo Admin FlowStuckHub, saya *' + USER_NAMA + '* ingin konfirmasi pemesanan dan pembayaran barang grosir:\n\n';

  let no = 1;
  draft.forEach(it => {
    items.push({ id: it.id, qty: it.qty, nama: it.nama, satuan: it.satuan });
    pesanWa += no + '. ' + it.nama + ' - ' + it.qty + ' ' + it.satuan + '\n';
    no++;
  });
  pesanWa += '\nMohon info total harga dan rekening pembayaran. Terima kasih!';

  try {
    draftBuy.disabled = true;
    draftBuy.textContent = 'Memproses...';

    const formData = new FormData();
    formData.append('csrf', csrf);
    formData.append('action', 'checkout');
    formData.append('items', JSON.stringify(items));

    const res = await fetch('<?= BASE ?>user/katalog.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (data.success) {
      draft.clear();
      renderDraft();
      const waUrl = 'https://wa.me/' + ADMIN_WA + '?text=' + encodeURIComponent(pesanWa);
      window.open(waUrl, '_blank');
      window.location.href = '<?= BASE ?>user/status-pengiriman.php';
    } else {
      alert(data.error || 'Terjadi kesalahan.');
      draftBuy.disabled = false;
      draftBuy.textContent = 'Beli Sekarang (WhatsApp) →';
    }
  } catch (err) {
    const waUrl = 'https://wa.me/' + ADMIN_WA + '?text=' + encodeURIComponent(pesanWa);
    window.open(waUrl, '_blank');
    draftBuy.disabled = false;
    draftBuy.textContent = 'Beli Sekarang (WhatsApp) →';
  }
});

renderDraft();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
