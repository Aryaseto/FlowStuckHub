<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['super_admin']);

$u     = user();
$error = '';
$ok    = '';

/* ─── HANDLE POST ─── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    /* ── DELETE ── */
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        // also remove image file
        $row = $pdo->prepare('SELECT gambar FROM barang WHERE id = ?');
        $row->execute([$id]);
        $b = $row->fetch();
        if ($b && $b['gambar']) {
            $imgPath = __DIR__ . '/../' . $b['gambar'];
            if (file_exists($imgPath)) @unlink($imgPath);
        }
        $pdo->prepare('DELETE FROM barang WHERE id = ?')->execute([$id]);
        $ok = 'Barang berhasil dihapus.';

    /* ── CREATE / EDIT ── */
    } elseif ($action === 'save') {
        $id      = (int) ($_POST['id'] ?? 0);
        $nama    = trim($_POST['nama'] ?? '');
        $desc    = trim($_POST['deskripsi'] ?? '');
        $satuan  = trim($_POST['satuan'] ?? 'dus');
        $stok    = (int) ($_POST['stok'] ?? 0);
        $gambar  = $_POST['gambar_lama'] ?? null;

        if ($nama === '') { $error = 'Nama barang wajib diisi.'; goto render; }

        // Handle upload
        if (!empty($_FILES['gambar']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $error = 'Format gambar tidak didukung (jpg, jpeg, png, webp).';
                goto render;
            }
            if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 5 MB.';
                goto render;
            }
            $uploadDir = __DIR__ . '/../uploads/barang/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
            $fname  = uniqid('brg_', true) . '.' . $ext;
            $target = $uploadDir . $fname;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                $error = 'Gagal menyimpan gambar.';
                goto render;
            }
            // remove old image if editing
            if ($id && $gambar) {
                $old = __DIR__ . '/../' . $gambar;
                if (file_exists($old)) @unlink($old);
            }
            $gambar = 'uploads/barang/' . $fname;
        }

        if ($id) {
            // UPDATE
            $pdo->prepare('UPDATE barang SET nama=?, deskripsi=?, satuan=?, stok=?, gambar=? WHERE id=?')
                ->execute([$nama, $desc, $satuan, $stok, $gambar, $id]);
            $ok = 'Barang berhasil diperbarui.';
        } else {
            // INSERT
            $pdo->prepare('INSERT INTO barang (nama, deskripsi, satuan, stok, gambar) VALUES (?,?,?,?,?)')
                ->execute([$nama, $desc, $satuan, $stok, $gambar]);
            $ok = 'Barang berhasil ditambahkan.';
        }
    }
}

render:
/* ─── FETCH LIST ─── */
$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $stmt = $pdo->prepare('SELECT * FROM barang WHERE nama LIKE ? ORDER BY nama');
    $stmt->execute(['%' . $cari . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM barang ORDER BY nama');
}
$barangs = $stmt->fetchAll();

/* ─── EDIT TARGET ─── */
$editId = (int) ($_GET['edit'] ?? 0);
$editData = null;
if ($editId) {
    $s = $pdo->prepare('SELECT * FROM barang WHERE id = ?');
    $s->execute([$editId]);
    $editData = $s->fetch();
}
?>
<!doctype html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E7D32">
<title>Kelola Barang · FlowStuckHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: { DEFAULT: '#2E7D32', light: '#4CAF50', dark: '#1B5E20' },
          accent: '#CDDC39'
        },
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif']
        }
      }
    }
  };
</script>
<style>
  ::selection { background: #CDDC39; color: #1B5E20; }
  @media print { body { display: none; } }
</style>
</head>
<body class="min-h-dvh bg-gray-50 font-sans text-gray-900 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-gray-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten</a>

<div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-gray-900/50 lg:hidden"></div>

<div class="min-h-dvh lg:grid lg:h-dvh lg:grid-cols-[268px_minmax(0,1fr)] lg:overflow-hidden">

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[268px] -translate-x-full transition-transform duration-200 ease-out lg:static lg:translate-x-0">
    <div class="flex h-full flex-col bg-brand px-4 py-5 text-white">
      <!-- Logo -->
      <div class="flex items-center gap-3 px-2 pb-5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-dark font-black text-lg leading-none">F</span>
        <span class="font-bold text-lg tracking-tight">FlowStuckHub</span>
      </div>
      <!-- Nav -->
      <nav class="flex-1 space-y-0.5" aria-label="Navigasi super admin">
        <?php
        $navItems = [
          ['../dashboard.php', 'Dashboard', '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
          ['barang.php',       'Kelola Barang', '<path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z"/><path d="M4 8l8 4.5L20 8"/><path d="M12 12.5v8"/>'],
          ['update-stok.php',  'Update Stok', '<path d="M12 4v16"/><path d="M5 12l7-7 7 7"/>'],
          ['../admin/surat-jalan.php', 'Surat Jalan (Filter)', '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
          ['retur.php',        'Keluhan Retur', '<path d="M9 14 4 9l5-5"/><path d="M4 9h9a6 6 0 0 1 0 12h-2"/>'],
          ['laporan.php',      'Laporan', '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
          ['../profile.php',   'Edit Profile', '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
        ];
        $currentFile = basename($_SERVER['PHP_SELF']);
        foreach ($navItems as [$href, $label, $svgPath]):
            $isActive = basename($href) === $currentFile;
        ?>
        <a href="<?= $href ?>"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                  <?= $isActive ? 'bg-white/20 text-white' : 'text-green-100 hover:bg-white/10 hover:text-white' ?>"
           <?= $isActive ? 'aria-current="page"' : '' ?>>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $svgPath ?></svg>
          <?= e($label) ?>
        </a>
        <?php endforeach; ?>
      </nav>
      <!-- User / Logout -->
      <div class="mt-auto border-t border-white/20 pt-4">
        <div class="flex items-center gap-3 px-2 pb-3">
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 text-sm font-bold">
            <?= e(mb_strtoupper(mb_substr($u['nama'] ?? 'S', 0, 2))) ?>
          </span>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold"><?= e($u['nama'] ?? '') ?></p>
            <p class="truncate text-xs text-green-200">Super Admin</p>
          </div>
        </div>
        <a href="<?= BASE ?>logout.php" class="flex w-full items-center justify-center rounded-lg border border-white/30 py-2 text-sm font-medium text-white transition hover:bg-white/10">Keluar</a>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <main id="main" class="flex flex-col lg:overflow-y-auto">
    <!-- Topbar mobile -->
    <div class="sticky top-0 z-30 flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
      <button id="btn-menu" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"
              class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="font-semibold text-gray-800">Kelola Barang</span>
    </div>

    <!-- Content -->
    <div class="flex-1 p-4 sm:p-6 lg:p-8">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Kelola Barang</h1>
          <p class="mt-0.5 text-sm text-gray-500">Tambah, edit, dan hapus data barang.</p>
        </div>
        <button type="button" onclick="openModal()"
                class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Barang
        </button>
      </div>

      <!-- Alerts -->
      <?php if ($ok): ?>
      <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= e($ok) ?>
      </div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <!-- Search -->
      <form method="GET" class="mb-5 flex gap-2">
        <input type="search" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama barang…"
               class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
        <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark transition-colors">Cari</button>
        <?php if ($cari): ?>
        <a href="barang.php" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Reset</a>
        <?php endif; ?>
      </form>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Gambar</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Satuan</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Stok</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Deskripsi</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if ($barangs): ?>
              <?php foreach ($barangs as $i => $b): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-500"><?= $i + 1 ?></td>
                <td class="px-4 py-3">
                  <?php if ($b['gambar'] && file_exists(__DIR__ . '/../' . $b['gambar'])): ?>
                  <img src="<?= BASE . e($b['gambar']) ?>" alt="<?= e($b['nama']) ?>"
                       class="h-12 w-12 rounded-lg object-cover ring-1 ring-gray-200">
                  <?php else: ?>
                  <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 font-medium text-gray-900"><?= e($b['nama']) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($b['satuan']) ?></td>
                <td class="px-4 py-3 text-right <?= (int)$b['stok'] === 0 ? 'font-semibold text-red-600' : 'text-gray-900' ?>">
                  <?= (int) $b['stok'] ?>
                </td>
                <td class="px-4 py-3 text-gray-500 max-w-xs">
                  <span class="line-clamp-2"><?= e(mb_strimwidth($b['deskripsi'] ?? '', 0, 80, '…')) ?></span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    <button type="button"
                            onclick="openEdit(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)"
                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      Edit
                    </button>
                    <form method="POST" onsubmit="return confirm('Hapus barang ini?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                      <button type="submit"
                              class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        Hapus
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-gray-400">
                  <?= $cari ? 'Barang tidak ditemukan.' : 'Belum ada barang. Tambah barang pertama Anda.' ?>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /content -->
  </main>
</div><!-- /grid -->

<!-- ═══════════ MODAL BARANG ═══════════ -->
<div id="modal-barang" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
  <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
      <h2 id="modal-title" class="text-lg font-semibold text-gray-900">Tambah Barang</h2>
      <button type="button" onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="form-id" value="">
      <input type="hidden" name="gambar_lama" id="form-gambar-lama" value="">

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="form-nama">Nama Barang <span class="text-red-500">*</span></label>
        <input type="text" id="form-nama" name="nama" required maxlength="120"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700" for="form-satuan">Satuan</label>
          <input type="text" id="form-satuan" name="satuan" value="dus" maxlength="30"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700" for="form-stok">Stok Awal</label>
          <input type="number" id="form-stok" name="stok" value="0" min="0"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="form-deskripsi">Deskripsi</label>
        <textarea id="form-deskripsi" name="deskripsi" rows="3"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand resize-none"></textarea>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="form-gambar">Gambar (jpg/jpeg/png/webp, maks 5 MB)</label>
        <div id="gambar-preview-wrap" class="mb-2 hidden">
          <img id="gambar-preview" src="" alt="Preview" class="h-24 w-24 rounded-lg object-cover ring-1 ring-gray-200">
        </div>
        <input type="file" id="form-gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"
               onchange="previewGambar(this)"
               class="w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand hover:file:bg-brand/20">
      </div>

      <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
        <button type="button" onclick="closeModal()"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Batal</button>
        <button type="submit"
                class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition-colors">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  /* Sidebar toggle */
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('nav-overlay');
  const btnMenu = document.getElementById('btn-menu');
  const isOpen  = () => !sidebar.classList.contains('-translate-x-full');
  const setOpen  = (v) => {
    sidebar.classList.toggle('-translate-x-full', !v);
    overlay.classList.toggle('hidden', !v);
    btnMenu && (btnMenu.setAttribute('aria-expanded', String(v)));
  };
  btnMenu  && btnMenu.addEventListener('click', () => setOpen(!isOpen()));
  overlay.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isOpen()) setOpen(false); });

  /* Modal */
  const modal = document.getElementById('modal-barang');
  window.openModal = function () {
    document.getElementById('modal-title').textContent   = 'Tambah Barang';
    document.getElementById('form-id').value             = '';
    document.getElementById('form-gambar-lama').value    = '';
    document.getElementById('form-nama').value           = '';
    document.getElementById('form-satuan').value         = 'dus';
    document.getElementById('form-stok').value           = '0';
    document.getElementById('form-deskripsi').value      = '';
    document.getElementById('gambar-preview-wrap').classList.add('hidden');
    document.getElementById('form-gambar').value         = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    document.getElementById('form-nama').focus();
  };
  window.openEdit = function (data) {
    document.getElementById('modal-title').textContent   = 'Edit Barang';
    document.getElementById('form-id').value             = data.id;
    document.getElementById('form-gambar-lama').value    = data.gambar || '';
    document.getElementById('form-nama').value           = data.nama || '';
    document.getElementById('form-satuan').value         = data.satuan || 'dus';
    document.getElementById('form-stok').value           = data.stok || 0;
    document.getElementById('form-deskripsi').value      = data.deskripsi || '';
    if (data.gambar) {
      document.getElementById('gambar-preview').src = '/flowstuckhub/' + data.gambar;
      document.getElementById('gambar-preview-wrap').classList.remove('hidden');
    } else {
      document.getElementById('gambar-preview-wrap').classList.add('hidden');
    }
    document.getElementById('form-gambar').value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    document.getElementById('form-nama').focus();
  };
  window.closeModal = function () {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  };
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  /* Image preview */
  window.previewGambar = function (input) {
    const wrap = document.getElementById('gambar-preview-wrap');
    const img  = document.getElementById('gambar-preview');
    if (input.files && input.files[0]) {
      img.src = URL.createObjectURL(input.files[0]);
      wrap.classList.remove('hidden');
    } else {
      wrap.classList.add('hidden');
    }
  };

  <?php if ($error): ?>
  openModal();
  <?php endif; ?>
})();
</script>
</body>
</html>
