<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['user', 'admin', 'super_admin']);

$u    = user();
$msgs = [];
$errs = [];

// Ambil data user terkini dari DB
$st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$u['id']]);
$profile = $st->fetch();

// ── POST handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'profile';

    // ── Update informasi profil ──────────────────────────────────────────────
    if ($action === 'profile') {
        $nama   = trim($_POST['nama_lengkap'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $no_hp  = trim($_POST['no_hp'] ?? '');

        if ($nama === '') $errs[] = 'Nama lengkap tidak boleh kosong.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Alamat email tidak valid.';

        if (empty($errs)) {
            // Cek duplikat email (selain diri sendiri)
            $ck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $ck->execute([$email, $u['id']]);
            if ($ck->fetch()) {
                $errs[] = 'Email sudah digunakan oleh akun lain.';
            } else {
                $upd = $pdo->prepare('UPDATE users SET nama_lengkap=?, email=?, alamat=?, no_hp=? WHERE id=?');
                $upd->execute([$nama, $email, $alamat, $no_hp, $u['id']]);
                // Perbarui nama di session
                $_SESSION['user']['nama'] = $nama;
                $msgs[] = 'Informasi profil berhasil diperbarui.';
                // Refresh data
                $profile = array_merge($profile, [
                    'nama_lengkap' => $nama,
                    'email'        => $email,
                    'alamat'       => $alamat,
                    'no_hp'        => $no_hp,
                ]);
            }
        }
    }

    // ── Ganti password ───────────────────────────────────────────────────────
    if ($action === 'password') {
        $old  = $_POST['password_lama']  ?? '';
        $new1 = $_POST['password_baru']  ?? '';
        $new2 = $_POST['konfirmasi_baru'] ?? '';

        if ($old === '')  $errs[] = 'Masukkan password lama.';
        if (strlen($new1) < 6) $errs[] = 'Password baru minimal 6 karakter.';
        if ($new1 !== $new2)   $errs[] = 'Konfirmasi password baru tidak cocok.';

        if (empty($errs)) {
            if (!password_verify($old, $profile['password'])) {
                $errs[] = 'Password lama yang Anda masukkan salah.';
            } else {
                $hash = password_hash($new1, PASSWORD_DEFAULT);
                $upd  = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
                $upd->execute([$hash, $u['id']]);
                $msgs[] = 'Password akun berhasil diubah.';
            }
        }
    }

    // ── Upload foto profil ───────────────────────────────────────────────────
    if ($action === 'foto' && isset($_FILES['foto_profil'])) {
        $file = $_FILES['foto_profil'];
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errs[] = 'Gagal mengunggah foto profil (kode: ' . $file['error'] . ').';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed, true)) {
                    $errs[] = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.';
                } elseif ($file['size'] > 3 * 1024 * 1024) {
                    $errs[] = 'Ukuran foto maksimal 3 MB.';
                } else {
                    $uploadDir = __DIR__ . '/../uploads/profil/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $fname = 'profil_' . $u['id'] . '_' . time() . '.' . $ext;
                    $dest  = $uploadDir . $fname;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        if (!empty($profile['foto_profil'])) {
                            $old_path = __DIR__ . '/../' . $profile['foto_profil'];
                            if (file_exists($old_path)) @unlink($old_path);
                        }
                        $rel = 'uploads/profil/' . $fname;
                        $upd = $pdo->prepare('UPDATE users SET foto_profil=? WHERE id=?');
                        $upd->execute([$rel, $u['id']]);
                        $profile['foto_profil'] = $rel;
                        $msgs[] = 'Foto profil berhasil diperbarui.';
                    } else {
                        $errs[] = 'Gagal menyimpan foto profil ke direktori server.';
                    }
                }
            }
        }
    }
}

$title = 'Edit Profile Pengguna';
require __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
  <div>
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl">Profil & Keamanan Akun</h1>
    <p class="text-sm text-gray-500 mt-1">Kelola data pribadi, kontak, foto identitas, dan kata sandi akun Anda.</p>
  </div>
</div>

<?php if ($msgs): ?>
  <div class="mt-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-xs flex items-center gap-2">
    <svg class="h-4 w-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <div>
      <?php foreach ($msgs as $m): ?>
        <p><?= e($m) ?></p>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php if ($errs): ?>
  <div class="mt-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">
    <ul class="list-disc pl-5 space-y-0.5">
      <?php foreach ($errs as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="mt-8 grid gap-8 lg:grid-cols-3">

  <!-- Kolom Kiri: Foto Profil & Info Cepat -->
  <div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
      <div class="relative mx-auto w-24 h-24 mb-4">
        <?php if (!empty($profile['foto_profil']) && file_exists(__DIR__ . '/../' . $profile['foto_profil'])): ?>
          <img src="<?= BASE . e($profile['foto_profil']) ?>" alt="Foto profil" class="w-24 h-24 rounded-full object-cover ring-4 ring-brand/10 shadow-sm">
        <?php else: ?>
          <span class="grid w-24 h-24 place-items-center rounded-full bg-accent text-2xl font-black text-brand-dark ring-4 ring-brand/10 shadow-sm">
            <?= e(initials($profile['nama_lengkap'])) ?>
          </span>
        <?php endif; ?>
      </div>

      <h2 class="text-base font-bold text-gray-900"><?= e($profile['nama_lengkap']) ?></h2>
      <p class="text-xs text-gray-400 font-mono mt-0.5">@<?= e($profile['username']) ?></p>
      <span class="mt-2 inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-brand uppercase tracking-wider">
        <?= e($roleBadge[$profile['role']] ?? $profile['role']) ?>
      </span>

      <!-- Form Ganti Foto -->
      <form method="post" enctype="multipart/form-data" class="mt-6 pt-5 border-t border-gray-100 text-left">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="foto">
        <label for="foto_profil" class="block text-xs font-bold text-gray-700 mb-2">Unggah Foto Baru</label>
        <input type="file" id="foto_profil" name="foto_profil" accept="image/*" required class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-green-50 file:text-brand hover:file:bg-green-100 cursor-pointer border border-gray-300 rounded-lg p-1.5 bg-gray-50">
        <button type="submit" class="mt-3 w-full rounded-lg bg-gray-900 py-2 text-xs font-bold text-white hover:bg-gray-800 transition">
          Perbarui Foto
        </button>
      </form>
    </div>
  </div>

  <!-- Kolom Kanan: Data Profil & Password -->
  <div class="lg:col-span-2 space-y-6">

    <!-- Card Edit Data Profil -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-7">
      <h3 class="text-base font-bold text-gray-900 mb-1">Informasi Pengguna</h3>
      <p class="text-xs text-gray-500 mb-6">Perbarui nama lengkap, email, alamat pengiriman, dan kontak telepon.</p>

      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Username</label>
            <input type="text" value="<?= e($profile['username']) ?>" disabled class="h-10 w-full rounded-xl border border-gray-200 bg-gray-100 px-3.5 text-xs text-gray-500 cursor-not-allowed">
            <span class="text-[10px] text-gray-400 mt-1 block">Username tidak dapat diubah.</span>
          </div>

          <div>
            <label for="nama_lengkap" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= e($profile['nama_lengkap']) ?>" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Email Terdaftar <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" value="<?= e($profile['email']) ?>" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
          </div>

          <div>
            <label for="no_hp" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Nomor Handphone / WhatsApp</label>
            <input type="text" id="no_hp" name="no_hp" value="<?= e($profile['no_hp'] ?? '') ?>" placeholder="Misal: 081234567890" class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
          </div>
        </div>

        <div>
          <label for="alamat" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Alamat Pengiriman Lengkap</label>
          <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap gudang/toko Anda..." class="w-full rounded-xl border border-gray-300 p-3 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"><?= e($profile['alamat'] ?? '') ?></textarea>
        </div>

        <div class="pt-2 flex justify-end">
          <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-dark transition active:scale-[0.98]">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>

    <!-- Card Ganti Password -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-7">
      <h3 class="text-base font-bold text-gray-900 mb-1">Ganti Kata Sandi</h3>
      <p class="text-xs text-gray-500 mb-6">Pastikan menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak.</p>

      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">

        <div>
          <label for="password_lama" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
          <input type="password" id="password_lama" name="password_lama" required class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="password_baru" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Kata Sandi Baru <span class="text-red-500">*</span></label>
            <input type="password" id="password_baru" name="password_baru" required minlength="6" placeholder="Minimal 6 karakter" class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
          </div>

          <div>
            <label for="konfirmasi_baru" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
            <input type="password" id="konfirmasi_baru" name="konfirmasi_baru" required minlength="6" placeholder="Ulangi kata sandi baru" class="h-10 w-full rounded-xl border border-gray-300 px-3.5 text-xs text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-6 py-2.5 text-xs font-bold text-white hover:bg-gray-800 transition active:scale-[0.98]">
            Perbarui Kata Sandi
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
