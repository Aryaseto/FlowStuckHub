<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user']);

$uid    = user()['id'];
$errors = [];
$old    = [];

// Ambil semua barang untuk dropdown
$barangs = $pdo->query('SELECT id, nama, satuan FROM barang ORDER BY nama')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $barang_id = (int) ($_POST['barang_id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $old       = ['barang_id' => $barang_id, 'deskripsi' => $deskripsi];

    // Validasi
    if (!$barang_id) {
        $errors[] = 'Pilih barang terlebih dahulu.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM barang WHERE id = ?');
        $cek->execute([$barang_id]);
        if (!$cek->fetch()) $errors[] = 'Barang tidak valid.';
    }
    if ($deskripsi === '') $errors[] = 'Deskripsi kerusakan wajib diisi.';

    // Validasi file
    $foto_path = '';
    if (empty($_FILES['foto']['name'])) {
        $errors[] = 'Foto kerusakan barang wajib diunggah.';
    } else {
        $file   = $_FILES['foto'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo  = finfo_open(FILEINFO_MIME_TYPE);
        $mime   = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload foto gagal (kode ' . $file['error'] . ').';
        } elseif (!in_array($mime, $allowed, true)) {
            $errors[] = 'File harus berupa gambar (JPG, PNG, GIF, atau WebP).';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran file foto maksimal 5 MB.';
        } else {
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $nama_file  = 'retur_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/retur/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file)) {
                $errors[] = 'Gagal menyimpan file ke server. Coba lagi.';
            } else {
                $foto_path = 'uploads/retur/' . $nama_file;
            }
        }
    }

    if (empty($errors)) {
        // NOTE: Tanggal muncul jika keluhan sudah disubmit
        $ins = $pdo->prepare('
            INSERT INTO retur (user_id, barang_id, foto, deskripsi, status, tanggal_submit)
            VALUES (?, ?, ?, ?, "baru", NOW())
        ');
        $ins->execute([$uid, $barang_id, $foto_path, $deskripsi]);

        $_SESSION['retur_success'] = 'Keluhan retur barang berhasil diajukan pada ' . date('d/m/Y H:i') . '. Keluhan Anda akan segera diproses oleh Super Admin Purchasing.';
        header('Location: ' . BASE . 'user/retur.php');
        exit;
    }
}

$title = 'Formulir Pengajuan Retur';
require __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Ajukan Retur Barang Rusak</h1>
    <p class="text-sm text-gray-500 mt-1">Lengkapi informasi barang dan unggah bukti fisik kerusakan barang yang diterima.</p>
  </div>
  <a href="<?= BASE ?>user/retur.php" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition">
    &larr; Kembali ke Daftar
  </a>
</div>

<?php if ($errors): ?>
  <div class="mt-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">
    <strong class="font-bold block mb-1">Periksa kembali formulir Anda:</strong>
    <ul class="list-disc pl-5 space-y-0.5">
      <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<!-- Form Box -->
<div class="mt-6 max-w-2xl bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
  <form method="post" enctype="multipart/form-data" class="space-y-6">
    <?= csrf_field() ?>

    <div>
      <label for="barang_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Pilih Barang Rusak <span class="text-red-500">*</span></label>
      <select id="barang_id" name="barang_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-white px-3.5 text-sm text-gray-900 shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        <option value="">-- Pilih Barang --</option>
        <?php foreach ($barangs as $b): ?>
          <option value="<?= $b['id'] ?>" <?= ($old['barang_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
            <?= e($b['nama']) ?> (Satuan: <?= e($b['satuan']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="foto" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Unggah Foto Bukti Kerusakan <span class="text-red-500">*</span></label>
      <input type="file" id="foto" name="foto" accept="image/*" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-green-50 file:text-brand hover:file:bg-green-100 cursor-pointer border border-gray-300 rounded-xl p-2 bg-gray-50">
      <p class="text-xs text-gray-400 mt-1.5">Format file: JPG, PNG, atau WebP. Ukuran maks 5 MB.</p>
    </div>

    <div>
      <label for="deskripsi" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Deskripsi Kerusakan Barang <span class="text-red-500">*</span></label>
      <textarea id="deskripsi" name="deskripsi" rows="4" required placeholder="Jelaskan secara detail kondisi kerusakan barang (misal: kemasan sobek, bocor saat pengiriman, kadaluarsa, dsb)..." class="w-full rounded-xl border border-gray-300 p-3.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"><?= e($old['deskripsi'] ?? '') ?></textarea>
    </div>

    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
      <a href="<?= BASE ?>user/retur.php" class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
        Batal
      </a>
      <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]">
        Kirim Keluhan Retur
      </button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
