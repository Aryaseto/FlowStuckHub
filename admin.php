<?php
// Halaman demo frontend. Semua angka di bawah ini statis dan tidak terhubung
// ke database, jadi dashboard bisa dibuka langsung tanpa login.

function e(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8');
}

$ringkasan = [
    [
        'label' => 'Total Barang',
        'nilai' => '487',
        'catatan' => 'dari 6 jenis barang',
        'tone' => 'bg-brand/10 text-brand',
        'ikon' => '<path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z"></path><path d="M4 8l8 4.5L20 8"></path><path d="M12 12.5v8"></path>',
    ],
    [
        'label' => 'Barang Masuk Hari Ini',
        'nilai' => '320',
        'catatan' => '12 surat jalan',
        'tone' => 'bg-emerald-50 text-emerald-700',
        'ikon' => '<path d="M12 4v10"></path><path d="m8 10 4 4 4-4"></path><path d="M5 20h14"></path>',
    ],
    [
        'label' => 'Barang Keluar Hari Ini',
        'nilai' => '214',
        'catatan' => '9 surat jalan',
        'tone' => 'bg-blue-50 text-blue-600',
        'ikon' => '<path d="M12 20V10"></path><path d="m8 14 4-4 4 4"></path><path d="M5 4h14"></path>',
    ],
    [
        'label' => 'Barang Return',
        'nilai' => '18',
        'catatan' => '3 menunggu pemeriksaan',
        'tone' => 'bg-amber-50 text-amber-600',
        'ikon' => '<path d="M9 14 4 9l5-5"></path><path d="M4 9h9a6 6 0 0 1 0 12h-2"></path>',
    ],
];

$tren = [
    'label' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    'masuk' => [120, 180, 150, 220, 260, 140, 90],
    'keluar' => [90, 130, 110, 180, 200, 120, 70],
];

$retur = [
    ['label' => 'Kemasan rusak', 'nilai' => 9, 'warna' => '#FACC15'],
    ['label' => 'Salah kirim', 'nilai' => 6, 'warna' => '#F59E0B'],
    ['label' => 'Kadaluarsa', 'nilai' => 3, 'warna' => '#DC2626'],
];

$totalRetur = array_sum(array_column($retur, 'nilai'));

$transaksi = [
    ['id' => 'TRX-2481', 'barang' => 'Beras premium 25 kg', 'tipe' => 'Masuk', 'tanggal' => '13 Nov 2026', 'status' => 'Selesai'],
    ['id' => 'TRX-2480', 'barang' => 'Air mineral 600 ml', 'tipe' => 'Keluar', 'tanggal' => '13 Nov 2026', 'status' => 'Selesai'],
    ['id' => 'TRX-2479', 'barang' => 'Gula pasir 50 kg', 'tipe' => 'Return', 'tanggal' => '12 Nov 2026', 'status' => 'Menunggu'],
    ['id' => 'TRX-2478', 'barang' => 'Kopi sachet', 'tipe' => 'Keluar', 'tanggal' => '12 Nov 2026', 'status' => 'Selesai'],
    ['id' => 'TRX-2477', 'barang' => 'Minyak goreng 2 L', 'tipe' => 'Masuk', 'tanggal' => '11 Nov 2026', 'status' => 'Selesai'],
];

$tipeClass = [
    'Masuk' => 'text-brand',
    'Keluar' => 'text-blue-600',
    'Return' => 'text-amber-600',
];

$statusClass = [
    'Selesai' => 'bg-green-50 text-green-700',
    'Menunggu' => 'bg-amber-50 text-amber-700',
    'Diproses' => 'bg-blue-50 text-blue-700',
];
?>
<!doctype html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E7D32">
<title>Overview Dashboard · FlowStuckHub</title>
<link rel="icon" type="image/jpeg" href="img/logo-160.jpg">
<link rel="apple-touch-icon" href="img/logo-160.jpg">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  ::selection { background: #CDDC39; color: #1B5E20; }
</style>
</head>
<body class="min-h-dvh bg-gray-50 font-sans text-gray-900 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-gray-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten</a>

<div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-gray-900/50 lg:hidden"></div>

<div class="min-h-dvh lg:grid lg:h-dvh lg:grid-cols-[268px_minmax(0,1fr)] lg:overflow-hidden">

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[268px] -translate-x-full transition-transform duration-200 ease-out lg:static lg:translate-x-0">
    <div class="flex h-full flex-col bg-brand px-4 py-5 text-white">

      <div class="flex items-center justify-between px-1">
        <a href="admin.php" class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 shrink-0 overflow-hidden rounded-full ring-1 ring-white/30">
            <img src="img/logo-160.jpg" alt="" width="40" height="40" class="h-full w-full scale-150 object-cover">
          </span>
          <span class="text-[15px] font-bold tracking-tight">FlowStuckHub</span>
        </a>
        <button id="nav-close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-green-50 transition hover:bg-brand-dark focus:outline-none focus-visible:ring-4 focus-visible:ring-accent/40 lg:hidden" aria-label="Tutup menu">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="h-5 w-5"><path d="M6 6l12 12M18 6 6 18"></path></svg>
        </button>
      </div>

      <nav class="mt-8 space-y-1" aria-label="Navigasi utama">
        <a href="admin.php" aria-current="page" class="relative flex items-center gap-3 rounded-lg bg-brand-dark px-3 py-2.5 text-sm font-semibold text-white">
          <span aria-hidden="true" class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-accent"></span>
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]">
            <path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path>
          </svg>
          Dashboard
        </a>

        <?php
        $menu = [
            ['label' => 'Kelola Barang', 'ikon' => '<path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z"></path><path d="M4 8l8 4.5L20 8"></path>'],
            ['label' => 'Barang Masuk', 'ikon' => '<path d="M12 4v10"></path><path d="m8 10 4 4 4-4"></path><path d="M5 20h14"></path>'],
            ['label' => 'Barang Keluar', 'ikon' => '<path d="M12 20V10"></path><path d="m8 14 4-4 4 4"></path><path d="M5 4h14"></path>'],
            ['label' => 'Laporan', 'ikon' => '<path d="M7 3.5h7l4 4V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"></path><path d="M9.5 12h5"></path><path d="M9.5 16h3"></path>'],
        ];
        ?>
        <?php foreach ($menu as $m): ?>
          <span class="flex cursor-default items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-green-50" aria-disabled="true">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]"><?= $m['ikon'] ?></svg>
            <span class="flex-1"><?= e($m['label']) ?></span>
            <span class="text-[10px] font-semibold uppercase tracking-wide text-green-50">Segera</span>
          </span>
        <?php endforeach; ?>
      </nav>

      <div class="mt-auto border-t border-white/15 pt-4">
        <div class="flex items-center gap-3 px-2">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-accent text-[11px] font-bold text-brand-dark">FA</span>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-white">Fadlilah Aryaseto</p>
            <p class="text-xs text-green-50">Admin Gudang</p>
          </div>
        </div>
        <a href="login.php" class="mt-3 flex h-10 w-full items-center justify-center rounded-lg border border-white/25 text-sm font-semibold text-white transition hover:bg-white/10">Keluar</a>
      </div>
    </div>
  </aside>

  <!-- Konten -->
  <div id="main-wrap" class="flex min-w-0 flex-col lg:h-dvh lg:overflow-y-auto">

    <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
      <button id="nav-open" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="h-5 w-5"><path d="M4 7h16M4 12h16M4 17h10"></path></svg>
      </button>
      <a href="admin.php" class="flex items-center gap-2">
        <span class="inline-flex h-8 w-8 shrink-0 overflow-hidden rounded-full ring-1 ring-brand/15">
          <img src="img/logo-160.jpg" alt="" width="32" height="32" class="h-full w-full scale-150 object-cover">
        </span>
        <span class="text-sm font-bold tracking-tight text-gray-900">FlowStuckHub</span>
      </a>
      <span class="grid h-8 w-8 place-items-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600" aria-hidden="true">FA</span>
    </header>

    <main id="main" class="flex-1 px-4 py-7 sm:px-6 lg:px-10 lg:py-9">
      <div class="mx-auto w-full max-w-6xl">

        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-[28px]">Overview Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Good morning, Admin. Berikut ringkasan pergerakan gudang hari ini.</p>
          </div>
          <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-500 shadow-sm">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400">
              <rect x="4" y="5.5" width="16" height="15" rx="2"></rect><path d="M8 3.5v4"></path><path d="M16 3.5v4"></path><path d="M4 10h16"></path>
            </svg>
            13 Nov 2026
          </div>
        </div>

        <!-- Kartu ringkasan -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <?php foreach ($ringkasan as $r): ?>
            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
              <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-medium text-gray-500"><?= e($r['label']) ?></p>
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg <?= $r['tone'] ?>">
                  <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]"><?= $r['ikon'] ?></svg>
                </span>
              </div>
              <p class="mt-3 text-2xl font-bold tracking-tight tabular-nums text-gray-900"><?= e($r['nilai']) ?></p>
              <p class="mt-1 text-xs text-gray-500"><?= e($r['catatan']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Grafik -->
        <div class="mt-6 grid gap-4 lg:grid-cols-2">

          <section class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="tren-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h2 id="tren-title" class="text-sm font-semibold text-gray-900">Tren Barang Masuk &amp; Keluar</h2>
                <p class="mt-0.5 text-xs text-gray-500">7 hari terakhir</p>
              </div>
              <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                  <span class="h-2 w-2 rounded-full bg-brand" aria-hidden="true"></span>Masuk
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                  <span class="h-2 w-2 rounded-full bg-blue-600" aria-hidden="true"></span>Keluar
                </span>
              </div>
            </div>
            <div class="relative mt-5 h-[260px]">
              <canvas id="chartFlow" role="img" aria-label="Grafik garis tren barang masuk dan keluar tujuh hari terakhir"></canvas>
              <p class="chart-fallback absolute inset-0 z-10 hidden place-content-center rounded-lg border border-dashed border-gray-200 bg-gray-50 p-4 text-center text-xs text-gray-500"></p>
            </div>
          </section>

          <section class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="return-title">
            <div>
              <h2 id="return-title" class="text-sm font-semibold text-gray-900">Persentase Barang Return</h2>
              <p class="mt-0.5 text-xs text-gray-500">Berdasarkan alasan pengembalian</p>
            </div>

            <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row sm:flex-wrap sm:justify-around">
              <div class="relative h-[168px] w-[168px] shrink-0">
                <canvas id="chartReturn" role="img" aria-label="Grafik donat persentase barang return: kemasan rusak 50 persen, salah kirim 33 persen, kadaluarsa 17 persen"></canvas>
                <div class="pointer-events-none absolute inset-0 grid place-items-center text-center" aria-hidden="true">
                  <div>
                    <p class="text-2xl font-bold tracking-tight tabular-nums text-gray-900"><?= $totalRetur ?></p>
                    <p class="text-[11px] font-medium text-gray-500">total return</p>
                  </div>
                </div>
                <p class="chart-fallback absolute inset-0 z-10 hidden place-content-center rounded-lg border border-dashed border-gray-200 bg-gray-50 p-4 text-center text-xs text-gray-500"></p>
              </div>

              <ul class="w-full space-y-3 sm:max-w-[190px]">
                <?php foreach ($retur as $r): $persen = (int) round($r['nilai'] / $totalRetur * 100); ?>
                  <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex items-center gap-2 text-gray-600">
                      <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: <?= e($r['warna']) ?>" aria-hidden="true"></span>
                      <?= e($r['label']) ?>
                    </span>
                    <span class="font-semibold tabular-nums text-gray-900"><?= $persen ?>%</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </section>
        </div>

        <!-- Tabel transaksi -->
        <section class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="transaksi-title">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4">
            <div>
              <h2 id="transaksi-title" class="text-sm font-semibold text-gray-900">Transaksi Terbaru</h2>
              <p class="mt-0.5 text-xs text-gray-500">5 transaksi terakhir</p>
            </div>
            <span class="text-xs text-gray-500">Data contoh</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
              <thead class="bg-gray-50">
                <tr class="text-[11px] uppercase tracking-wide text-gray-500">
                  <th scope="col" class="px-5 py-3 font-semibold">ID</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Nama Barang</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Tipe</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Tanggal</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php foreach ($transaksi as $t): ?>
                  <tr class="transition-colors hover:bg-gray-50/70">
                    <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs text-gray-500"><?= e($t['id']) ?></td>
                    <td class="px-5 py-3.5 font-semibold text-gray-900"><?= e($t['barang']) ?></td>
                    <td class="whitespace-nowrap px-5 py-3.5">
                      <span class="text-xs font-semibold <?= e($tipeClass[$t['tipe']] ?? 'text-gray-500') ?>"><?= e($t['tipe']) ?></span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-500"><?= e($t['tanggal']) ?></td>
                    <td class="whitespace-nowrap px-5 py-3.5">
                      <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold <?= e($statusClass[$t['status']] ?? 'bg-gray-100 text-gray-600') ?>"><?= e($t['status']) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <p class="mt-6 text-xs text-gray-500">Dashboard demo. Angka pada halaman ini adalah data contoh.</p>
      </div>
    </main>
  </div>
</div>

<script>
(function () {
  // Drawer navigasi (mobile)
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('nav-overlay');
  const openBtn = document.getElementById('nav-open');
  const closeBtn = document.getElementById('nav-close');
  const mainWrap = document.getElementById('main-wrap');
  const desktop = () => window.matchMedia('(min-width: 1024px)').matches;
  const isOpen = () => !sidebar.classList.contains('-translate-x-full');

  function setOpen(open) {
    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('overflow-hidden', open);
    mainWrap.inert = open;
    if (open) {
      closeBtn.focus();
    } else if (!desktop()) {
      mainWrap.inert = false;
      openBtn.focus();
    }
  }

  openBtn.addEventListener('click', () => setOpen(true));
  closeBtn.addEventListener('click', () => setOpen(false));
  overlay.addEventListener('click', () => setOpen(false));

  window.addEventListener('resize', () => {
    if (desktop() && isOpen()) setOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (!isOpen() || desktop()) return;

    if (event.key === 'Escape') {
      setOpen(false);
      return;
    }

    if (event.key === 'Tab') {
      const fokus = sidebar.querySelectorAll('a[href], button:not([disabled])');
      if (!fokus.length) return;
      const pertama = fokus[0];
      const terakhir = fokus[fokus.length - 1];
      if (event.shiftKey && document.activeElement === pertama) {
        event.preventDefault();
        terakhir.focus();
      } else if (!event.shiftKey && document.activeElement === terakhir) {
        event.preventDefault();
        pertama.focus();
      }
    }
  });

  // Grafik
  const tren = <?= json_encode($tren, JSON_UNESCAPED_SLASHES) ?>;
  const retur = <?= json_encode($retur, JSON_UNESCAPED_SLASHES) ?>;
  const totalRetur = <?= (int) $totalRetur ?>;
  const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fontTick = { family: 'Plus Jakarta Sans', size: 11 };

  function tampilkanFallback() {
    document.querySelectorAll('.chart-fallback').forEach((el) => {
      el.classList.remove('hidden');
      el.classList.add('grid');
      el.textContent = 'Grafik tidak dapat dimuat. Periksa koneksi internet lalu muat ulang halaman.';
    });
  }

  if (typeof Chart === 'undefined') {
    tampilkanFallback();
  } else {
    new Chart(document.getElementById('chartFlow'), {
      type: 'line',
      data: {
        labels: tren.label,
        datasets: [
          {
            label: 'Masuk',
            data: tren.masuk,
            borderColor: '#2E7D32',
            backgroundColor: 'rgba(46, 125, 50, 0.07)',
            fill: true,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 4,
            pointHoverBackgroundColor: '#FFFFFF',
            pointHoverBorderColor: '#2E7D32',
            pointHoverBorderWidth: 2
          },
          {
            label: 'Keluar',
            data: tren.keluar,
            borderColor: '#2563EB',
            backgroundColor: 'transparent',
            fill: false,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 4,
            pointHoverBackgroundColor: '#FFFFFF',
            pointHoverBorderColor: '#2563EB',
            pointHoverBorderWidth: 2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 120,
        animation: kurangiGerak ? false : { duration: 650, easing: 'easeOutQuart' },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#111827',
            displayColors: true,
            padding: 10,
            cornerRadius: 8,
            titleFont: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
            callbacks: {
              label: (ctx) => ctx.dataset.label + ': ' + ctx.parsed.y + ' dus'
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false },
            ticks: { color: '#9CA3AF', font: fontTick, padding: 6 }
          },
          y: {
            beginAtZero: true,
            grid: { color: '#F3F4F6' },
            border: { display: false },
            ticks: { color: '#9CA3AF', font: fontTick, padding: 8, maxTicksLimit: 4, precision: 0 }
          }
        }
      }
    });

    new Chart(document.getElementById('chartReturn'), {
      type: 'doughnut',
      data: {
        labels: retur.map((r) => r.label),
        datasets: [{
          data: retur.map((r) => r.nilai),
          backgroundColor: retur.map((r) => r.warna),
          borderWidth: 0,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: kurangiGerak ? false : { duration: 650, easing: 'easeOutQuart' },
        cutout: '72%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#111827',
            displayColors: false,
            padding: 10,
            cornerRadius: 8,
            titleFont: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
            callbacks: {
              label: (ctx) => ctx.label + ': ' + Math.round((ctx.parsed / totalRetur) * 100) + '%'
            }
          }
        }
      }
    });
  }
})();
</script>
</body>
</html>