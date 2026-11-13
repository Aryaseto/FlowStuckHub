<?php
require_once __DIR__ . '/includes/auth.php';
if (user()) { header('Location: ' . home_for(user()['role'])); exit; }
$error = ''; $v = ['nama' => '', 'email' => '', 'username' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = ['nama' => trim($_POST['nama'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'username' => trim($_POST['username'] ?? '')];
    $pw = $_POST['password'] ?? '';
    if (in_array('', $v, true) || $pw === '') $error = 'Lengkapi semua kolom.';
    elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $error = 'Format email belum benar.';
    elseif (strlen($pw) < 6) $error = 'Password minimal 6 karakter.';
    elseif ($pw !== ($_POST['konfirmasi'] ?? '')) $error = 'Konfirmasi password tidak sama.';
    else {
        $st = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
        $st->execute([$v['username'], $v['email']]);
        if ($st->fetch()) $error = 'Username atau email sudah terdaftar.';
        else {
            $pdo->prepare('INSERT INTO users (nama_lengkap,email,username,password,role) VALUES (?,?,?,?,?)')
                ->execute([$v['nama'], $v['email'], $v['username'], password_hash($pw, PASSWORD_DEFAULT), 'user']);
            header('Location: ' . BASE . 'login.php'); exit;
        }
    }
}
$title = 'Daftar';
require __DIR__ . '/includes/header.php';
?>
<main class="auth" id="main">
  <section class="auth__brand" aria-label="FlowStuckHub">
    <div class="auth__stamp">
      <?= brand_mark('brand__mark--lg') ?>
      <span class="auth__stamp-label">Gudang grosir</span>
    </div>
    <div class="auth__pitch">
      <h1 class="auth__wordmark">FlowStuck<span>Hub</span></h1>
      <p class="auth__tagline">Gudang grosir dengan stok yang tercatat dan surat jalan yang bisa dilacak.</p>
    </div>
  </section>

  <section class="auth__panel">
    <div class="auth__form">
      <h2 class="auth__title">Buat akun</h2>
      <p class="auth__lede">Daftar sebagai pelanggan untuk mulai memesan grosir.</p>

      <?php if ($error): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>

      <form method="post" class="form">
        <?= csrf_field() ?>
        <div class="field">
          <label for="nama">Nama lengkap</label>
          <input class="input" id="nama" name="nama" value="<?= e($v['nama']) ?>" required autocomplete="name">
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input class="input" id="email" name="email" type="email" value="<?= e($v['email']) ?>" required autocomplete="email">
        </div>
        <div class="field">
          <label for="username">Username</label>
          <input class="input" id="username" name="username" value="<?= e($v['username']) ?>" required autocomplete="username">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input class="input" id="password" name="password" type="password" required autocomplete="new-password" aria-describedby="password-hint">
          <span id="password-hint" class="sr-only">Minimal 6 karakter.</span>
        </div>
        <div class="field">
          <label for="konfirmasi">Konfirmasi password</label>
          <input class="input" id="konfirmasi" name="konfirmasi" type="password" required autocomplete="new-password">
        </div>
        <button class="btn btn--primary btn--block" type="submit">Buat akun</button>
      </form>

      <p class="auth__switch">Sudah punya akun? <a href="<?= BASE ?>login.php">Masuk</a></p>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>