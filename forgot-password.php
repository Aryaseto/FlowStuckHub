<?php
require_once __DIR__ . '/includes/auth.php';
if (user()) { header('Location: ' . home_for(user()['role'])); exit; }

$error   = '';
$success = '';
$devToken = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } else {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        if ($row) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $upd = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $upd->execute([$token, $expires, $row['id']]);
            $devToken = $token; // untuk testing
        }
        // Pesan sama meski email tidak ditemukan (mencegah user enumeration)
        $success = 'Kode reset telah dikirim ke email Anda. Silakan cek inbox Anda.';
    }
}
?>
<!doctype html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E7D32">
<title>Lupa Password · FlowStuckHub</title>
<link rel="icon" type="image/jpeg" href="<?= BASE ?>img/logo-160.jpg">
<link rel="apple-touch-icon" href="<?= BASE ?>img/logo-160.jpg">
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
</head>
<body class="min-h-full bg-white font-sans text-gray-900 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-gray-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten</a>

<main id="main" class="grid min-h-dvh lg:grid-cols-2">

  <!-- Panel brand -->
  <section class="relative flex min-h-[320px] flex-col justify-between overflow-hidden bg-brand px-7 py-8 text-white sm:px-10 lg:min-h-dvh lg:px-14 lg:py-12">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
      <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full border border-accent/30"></div>
      <div class="absolute right-20 top-28 h-40 w-40 rounded-full border border-white/10"></div>
      <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-brand-dark/50"></div>

      <div class="absolute bottom-12 right-12 hidden h-40 w-40 sm:block lg:hidden min-[1400px]:block">
        <div class="absolute bottom-0 right-0 h-24 w-24 rounded-2xl bg-white/10"></div>
        <div class="absolute bottom-0 right-28 h-16 w-16 rounded-xl bg-accent/20"></div>
        <div class="absolute bottom-28 right-0 h-16 w-16 rounded-xl bg-white/[0.07]"></div>
        <div class="absolute bottom-24 right-24 h-10 w-10 rounded-lg bg-white/20"></div>
      </div>
    </div>

    <a href="login.php" class="relative flex items-center gap-3 self-start">
      <span class="inline-flex h-11 w-11 shrink-0 overflow-hidden rounded-full ring-1 ring-white/30">
        <img src="<?= BASE ?>img/logo-160.jpg" alt="" width="44" height="44" class="h-full w-full scale-150 object-cover">
      </span>
      <span class="text-lg font-bold tracking-tight">FlowStuckHub</span>
    </a>

    <div class="relative mt-14 max-w-md">
      <h1 class="text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-[3.4rem]">
        FlowStuck<span class="text-accent">Hub</span>
      </h1>
      <p class="mt-4 max-w-sm text-[15px] leading-relaxed text-green-50/90 sm:text-base">
        Sistem Manajemen Gudang Terintegrasi
      </p>
    </div>
  </section>

  <!-- Panel form -->
  <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-14">
    <div class="w-full max-w-sm">
      <div class="mb-8 flex items-center gap-3 lg:hidden">
        <span class="inline-flex h-9 w-9 shrink-0 overflow-hidden rounded-full ring-1 ring-brand/15">
          <img src="<?= BASE ?>img/logo-160.jpg" alt="" width="36" height="36" class="h-full w-full scale-150 object-cover">
        </span>
        <span class="text-base font-bold tracking-tight text-gray-900">FlowStuckHub</span>
      </div>

      <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-[28px]">Lupa Password</h2>
      <p class="mt-2 text-sm leading-relaxed text-gray-500">
        Masukkan email terdaftar dan kami akan memberikan kode reset.
      </p>

      <?php if ($error): ?>
        <div role="alert" class="mt-6 flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-700">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="mt-0.5 h-4 w-4 shrink-0">
            <circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5"></path><path d="M12 16.5h.01"></path>
          </svg>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div role="alert" class="mt-6 flex items-start gap-2.5 rounded-lg border border-green-200 bg-green-50 px-3.5 py-3 text-sm text-green-700">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="mt-0.5 h-4 w-4 shrink-0">
            <path d="M20 6 9 17l-5-5"></path>
          </svg>
          <div>
            <span><?= e($success) ?></span>
            <?php if ($devToken): ?>
              <div class="mt-3 rounded-md border border-green-300 bg-white px-3 py-2">
                <p class="text-xs font-semibold text-gray-600 mb-1">🔧 Token untuk testing:</p>
                <code class="break-all text-xs text-brand font-mono"><?= e($devToken) ?></code>
                <p class="mt-1 text-xs text-gray-500">
                  <a href="<?= BASE ?>reset-password.php?token=<?= urlencode($devToken) ?>" class="underline font-semibold text-brand">Klik di sini untuk reset password</a>
                </p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="post" class="mt-7 space-y-5" novalidate>
        <?= csrf_field() ?>

        <div>
          <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Alamat Email</label>
          <input
            id="email"
            name="email"
            type="email"
            value="<?= e($_POST['email'] ?? '') ?>"
            autocomplete="email"
            required
            placeholder="contoh@email.com"
            <?= $error ? 'aria-invalid="true"' : '' ?>
            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
          >
        </div>

        <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark active:translate-y-px focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/25">
          Kirim Kode Reset
        </button>
      </form>
      <?php endif; ?>

      <p class="mt-6 text-center text-sm text-gray-500">
        <a href="<?= BASE ?>login.php" class="font-semibold text-brand underline decoration-brand/30 underline-offset-2 transition hover:decoration-brand">← Kembali ke login</a>
      </p>
    </div>
  </section>
</main>

</body>
</html>
