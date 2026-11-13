<?php
declare(strict_types=1);

// Halaman demo frontend. Data barang di bawah ini statis dan tidak menyentuh
// database, jadi katalog bisa dibuka langsung tanpa login.

function e(?string $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function rupiah(int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function foto(array $barang): ?string
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file(__DIR__ . '/img/products/' . $barang['slug'] . '.' . $ext)) {
            return 'img/products/' . $barang['slug'] . '.' . $ext;
        }
    }

    return null;
}

function ilustrasi(string $slug): string
{
    $gambar = [
        'air-mineral' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="120" cy="191" rx="66" ry="7" fill="#1B5E20" opacity=".09"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <rect x="70" y="102" width="30" height="82" rx="9" fill="#F5F9F1"/>
    <rect x="105" y="92" width="30" height="92" rx="9" fill="#F5F9F1"/>
    <rect x="140" y="102" width="30" height="82" rx="9" fill="#F5F9F1"/>
    <rect x="81" y="88" width="8" height="14" rx="3" fill="#2E7D32"/>
    <rect x="116" y="78" width="8" height="14" rx="3" fill="#2E7D32"/>
    <rect x="151" y="88" width="8" height="14" rx="3" fill="#2E7D32"/>
    <path d="M70 118h30M105 108h30M140 118h30" opacity=".35"/>
  </g>
  <g fill="#CDDC39" opacity=".5">
    <rect x="74" y="132" width="22" height="46" rx="6"/>
    <rect x="109" y="126" width="22" height="54" rx="6"/>
    <rect x="144" y="132" width="22" height="46" rx="6"/>
  </g>
  <rect x="62" y="124" width="116" height="62" rx="7" fill="#FFFFFF" opacity=".18" stroke="#2E7D32" stroke-width="1.5"/>
  <path d="M62 132h116M62 178h116" stroke="#2E7D32" stroke-width="1.5" opacity=".3"/>
</svg>
SVG,
        'beras' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="120" cy="191" rx="62" ry="7" fill="#1B5E20" opacity=".09"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <path d="M84 182c-8 0-13-6-13-15l9-58 10-7h60l10 7 9 58c0 9-5 15-13 15z" fill="#F7FAF2"/>
    <path d="M90 102l-10 7 5 9 12-11z" fill="#E3EDDC"/>
    <path d="M150 102l10 7-5 9-12-11z" fill="#E3EDDC"/>
    <path d="M90 96h60l-7-16H97z" fill="#EAF1E3"/>
    <rect x="96" y="122" width="48" height="42" rx="6" fill="#FFFFFF"/>
    <path d="M107 136h26M107 146h18" opacity=".45"/>
    <path d="M120 154v10" opacity=".3"/>
  </g>
  <g fill="#CDDC39" stroke="#2E7D32" stroke-width="1.25">
    <ellipse cx="64" cy="182" rx="6" ry="3" transform="rotate(-18 64 182)"/>
    <ellipse cx="76" cy="187" rx="6" ry="3" transform="rotate(14 76 187)"/>
    <ellipse cx="57" cy="189" rx="5" ry="2.5" transform="rotate(8 57 189)"/>
    <ellipse cx="86" cy="180" rx="5" ry="2.5" transform="rotate(-24 86 180)"/>
  </g>
</svg>
SVG,
        'gula' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="118" cy="191" rx="58" ry="7" fill="#1B5E20" opacity=".09"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <path d="M94 180c-7 0-11-5-11-13l7-62 9-6h44l9 6 7 62c0 8-4 13-11 13z" fill="#F8FAF4"/>
    <path d="M99 100l-9 6 4 8 10-10z" fill="#E3EDDC"/>
    <path d="M143 100l9 6-4 8-10-10z" fill="#E3EDDC"/>
    <path d="M99 94h44l-6-14h-32z" fill="#EAF1E3"/>
    <circle cx="121" cy="132" r="18" fill="#FFFFFF"/>
    <path d="M113 128h16M113 134h16M113 140h10" opacity=".4"/>
  </g>
  <g fill="#FBFCF7" stroke="#2E7D32" stroke-width="1.5" stroke-linejoin="round">
    <rect x="150" y="168" width="12" height="12" rx="2" transform="rotate(-12 156 174)"/>
    <rect x="162" y="174" width="12" height="12" rx="2" transform="rotate(8 168 180)"/>
    <rect x="154" y="180" width="12" height="12" rx="2" transform="rotate(-6 160 186)"/>
    <rect x="168" y="164" width="10" height="10" rx="2" transform="rotate(18 173 169)"/>
  </g>
  <g fill="#FFFFFF" stroke="#2E7D32" stroke-width="1.25">
    <circle cx="145" cy="186" r="2.5"/>
    <circle cx="179" cy="181" r="2"/>
    <circle cx="186" cy="187" r="2.5"/>
  </g>
</svg>
SVG,
        'kopi' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="120" cy="191" rx="58" ry="7" fill="#1B5E20" opacity=".09"/>
  <path d="M76 104 94 84h82l-18 20z" fill="#E6EFDF" stroke="#2E7D32" stroke-width="2" stroke-linejoin="round"/>
  <path d="M158 104l18-20v72l-18 18z" fill="#D9E6D1" stroke="#2E7D32" stroke-width="2" stroke-linejoin="round"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <g transform="rotate(-9 97 118)">
      <rect x="86" y="86" width="22" height="60" rx="3" fill="#FFFFFF"/>
      <path d="M86 96h22" fill="none"/>
      <rect x="86" y="86" width="22" height="10" rx="3" fill="#CDDC39"/>
    </g>
    <g transform="rotate(3 122 112)">
      <rect x="111" y="82" width="22" height="62" rx="3" fill="#FFFFFF"/>
      <rect x="111" y="82" width="22" height="10" rx="3" fill="#CDDC39"/>
    </g>
    <g transform="rotate(12 147 118)">
      <rect x="136" y="86" width="22" height="60" rx="3" fill="#FFFFFF"/>
      <rect x="136" y="86" width="22" height="10" rx="3" fill="#CDDC39"/>
    </g>
  </g>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <rect x="78" y="106" width="82" height="72" rx="4" fill="#F8FAF4"/>
    <rect x="94" y="126" width="34" height="24" rx="4" fill="#FFFFFF"/>
    <path d="M101 134h20M101 142h13" opacity=".45"/>
  </g>
</svg>
SVG,
        'mie-instan' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="120" cy="191" rx="58" ry="7" fill="#1B5E20" opacity=".09"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <g transform="rotate(-12 105 108)">
      <rect x="92" y="72" width="26" height="58" rx="3" fill="#FFFFFF"/>
      <rect x="92" y="72" width="26" height="11" rx="3" fill="#CDDC39"/>
    </g>
    <g transform="rotate(9 137 106)">
      <rect x="126" y="68" width="26" height="60" rx="3" fill="#FFFFFF"/>
      <rect x="126" y="68" width="26" height="11" rx="3" fill="#CDDC39"/>
    </g>
    <path d="M72 106h96l-5 66c-1 6-4 8-10 8H87c-6 0-9-2-10-8z" fill="#F8FAF4"/>
    <path d="M72 106c0-5 4-8 9-8h78c5 0 9 3 9 8s-4 8-9 8H81c-5 0-9-3-9-8z" fill="#E6EFDF"/>
    <rect x="96" y="126" width="48" height="30" rx="4" fill="#FFFFFF"/>
    <path d="M104 136c4-4 8 4 12 0s8 4 12 0" fill="none" stroke-width="1.75" opacity=".55"/>
    <path d="M104 145c4-4 8 4 12 0s8 4 12 0" fill="none" stroke-width="1.75" opacity=".55"/>
  </g>
</svg>
SVG,
        'minyak' => <<<'SVG'
<svg viewBox="30 50 180 170" aria-hidden="true" class="h-full w-full">
  <ellipse cx="120" cy="191" rx="58" ry="7" fill="#1B5E20" opacity=".09"/>
  <g stroke="#2E7D32" stroke-width="2" stroke-linejoin="round">
    <path d="M82 96 100 82h40l18 14 8 70c-14 6-30 9-46 9s-32-3-46-9z" fill="#FBF8E8"/>
    <path d="M90 136h60l6 30c-12 5-24 7-36 7s-24-2-36-7z" fill="#EFE9BF" opacity=".85"/>
    <path d="M148 74h16v12h-16z" fill="#2E7D32"/>
    <path d="M152 86h8v10h-8z" fill="#2E7D32"/>
    <rect x="102" y="106" width="36" height="26" rx="4" fill="#FFFFFF"/>
    <path d="M110 116h20M110 124h13" opacity=".45"/>
  </g>
</svg>
SVG,
    ];

    return $gambar[$slug] ?? '';
}

function status_stok(array $barang): array
{
    if ($barang['stok'] < 1) {
        return ['label' => 'Habis', 'badge' => 'bg-red-50 text-red-700'];
    }
    if ($barang['stok'] <= 25) {
        return ['label' => 'Menipis', 'badge' => 'bg-amber-50 text-amber-700'];
    }

    return ['label' => 'Tersedia', 'badge' => 'bg-green-50 text-green-700'];
}

$barang = [
    [
        'sku' => 'FSH-AIR-600',
        'slug' => 'air-mineral',
        'nama' => 'Air mineral 600 ml',
        'deskripsi' => 'Dus isi 24 botol 600 ml. Cocok untuk konsumsi harian kantor dan mess.',
        'alt' => 'Dus air mineral 600 ml isi 24 botol',
        'kategori' => 'Minuman',
        'stok' => 18,
        'satuan' => 'dus',
        'harga' => 42000,
    ],
    [
        'sku' => 'FSH-BRS-025',
        'slug' => 'beras',
        'nama' => 'Beras premium 25 kg',
        'deskripsi' => 'Karung 25 kg, beras putih pulen. Stok rutin gudang pusat.',
        'alt' => 'Karung beras premium 25 kg',
        'kategori' => 'Bahan Pokok',
        'stok' => 120,
        'satuan' => 'karung',
        'harga' => 355000,
    ],
    [
        'sku' => 'FSH-GLA-050',
        'slug' => 'gula',
        'nama' => 'Gula pasir 50 kg',
        'deskripsi' => 'Karung 50 kg, gula kristal putih untuk dapur dan produksi.',
        'alt' => 'Karung gula pasir 50 kg',
        'kategori' => 'Bahan Pokok',
        'stok' => 0,
        'satuan' => 'karung',
        'harga' => 735000,
    ],
    [
        'sku' => 'FSH-KOP-010',
        'slug' => 'kopi',
        'nama' => 'Kopi sachet',
        'deskripsi' => 'Renceng isi 10 sachet kopi bubuk. Praktis untuk pantry kantor.',
        'alt' => 'Karton kopi sachet isi 10 renceng',
        'kategori' => 'Minuman',
        'stok' => 75,
        'satuan' => 'karton',
        'harga' => 132000,
    ],
    [
        'sku' => 'FSH-MIE-040',
        'slug' => 'mie-instan',
        'nama' => 'Mie instan goreng',
        'deskripsi' => 'Dus isi 40 bungkus mie instan goreng. Stok cepat berputar.',
        'alt' => 'Dus mie instan goreng isi 40 bungkus',
        'kategori' => 'Makanan Instan',
        'stok' => 210,
        'satuan' => 'dus',
        'harga' => 108000,
    ],
    [
        'sku' => 'FSH-MNY-2L',
        'slug' => 'minyak',
        'nama' => 'Minyak goreng 2 L',
        'deskripsi' => 'Dus isi 6 pouch 2 liter. Minyak goreng sawit kemasan pouch.',
        'alt' => 'Dus minyak goreng 2 liter isi 6 pouch',
        'kategori' => 'Minyak & Bumbu',
        'stok' => 64,
        'satuan' => 'dus',
        'harga' => 216000,
    ],
];

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) && is_string($_GET['kategori']) ? $_GET['kategori'] : 'Semua';
$urut = isset($_GET['urut']) && is_string($_GET['urut']) ? $_GET['urut'] : 'nama';

$kategoriOpsi = array_merge(['Semua'], array_values(array_unique(array_column($barang, 'kategori'))));
sort($kategoriOpsi);
array_unshift($kategoriOpsi, array_shift($kategoriOpsi)); // "Semua" tetap di depan

if (!in_array($kategori, $kategoriOpsi, true)) {
    $kategori = 'Semua';
}

$opsiUrut = ['nama' => 'Nama A-Z', 'harga' => 'Harga terendah', 'stok' => 'Stok terbanyak'];
if (!isset($opsiUrut[$urut])) {
    $urut = 'nama';
}

$jumlahKategori = array_count_values(array_column($barang, 'kategori'));
$jumlahKategori = ['Semua' => count($barang)] + $jumlahKategori;

$hasil = array_values(array_filter($barang, static function (array $b) use ($q, $kategori): bool {
    $cocokKategori = $kategori === 'Semua' || $b['kategori'] === $kategori;
    if (!$cocokKategori) {
        return false;
    }
    if ($q === '') {
        return true;
    }

    return stripos($b['nama'], $q) !== false
        || stripos($b['sku'], $q) !== false
        || stripos($b['deskripsi'], $q) !== false
        || stripos($b['kategori'], $q) !== false;
}));

usort($hasil, static function (array $a, array $b) use ($urut): int {
    return match ($urut) {
        'harga' => $a['harga'] <=> $b['harga'],
        'stok' => $b['stok'] <=> $a['stok'],
        default => strcasecmp($a['nama'], $b['nama']),
    };
});

$siapDipesan = count(array_filter($barang, static fn(array $b): bool => $b['stok'] > 0));
$statistik = [
    ['label' => 'SKU aktif', 'nilai' => (string) count($barang)],
    ['label' => 'Kategori', 'nilai' => (string) (count($kategoriOpsi) - 1)],
    ['label' => 'Siap dipesan', 'nilai' => (string) $siapDipesan],
    ['label' => 'Stok habis', 'nilai' => (string) (count($barang) - $siapDipesan)],
];

$tautan = static function (array $ganti = []) use ($q, $kategori, $urut): string {
    $params = ['q' => $q, 'kategori' => $kategori, 'urut' => $urut];
    foreach ($ganti as $kunci => $nilai) {
        $params[$kunci] = $nilai;
    }
    $params = array_filter($params, static fn(string $nilai): bool => $nilai !== '' && $nilai !== 'Semua' && $nilai !== 'nama');

    return 'katalog.php' . ($params !== [] ? '?' . http_build_query($params) : '');
};

$produkJs = [];
foreach ($barang as $b) {
    $produkJs[$b['sku']] = [
        'sku' => $b['sku'],
        'nama' => $b['nama'],
        'harga' => $b['harga'],
        'satuan' => $b['satuan'],
        'stok' => $b['stok'],
        'foto' => foto($b),
        'gambar' => ilustrasi($b['slug']),
    ];
}

$adaFilter = $q !== '' || $kategori !== 'Semua';
?><!doctype html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E7D32">
<title>Katalog Barang · FlowStuckHub</title>
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
<style>
  ::selection { background: #CDDC39; color: #1B5E20; }
  .no-scrollbar { scrollbar-width: none; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  @media (prefers-reduced-motion: reduce) {
    *, ::before, ::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
</style>
</head>
<body class="min-h-dvh bg-gray-50 font-sans text-gray-900 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[90] focus:rounded-lg focus:bg-gray-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten</a>

<div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-gray-900/50 lg:hidden"></div>

<div class="min-h-dvh lg:grid lg:h-dvh lg:grid-cols-[268px_minmax(0,1fr)] lg:overflow-hidden">

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[268px] -translate-x-full transition-transform duration-200 ease-out lg:static lg:translate-x-0">
    <div class="flex h-full flex-col bg-brand px-4 py-5 text-white">

      <div class="flex items-center justify-between px-1">
        <a href="katalog.php" class="flex items-center gap-3">
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
        <a href="katalog.php" aria-current="page" class="relative flex items-center gap-3 rounded-lg bg-brand-dark px-3 py-2.5 text-sm font-semibold text-white">
          <span aria-hidden="true" class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-accent"></span>
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect>
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5"></rect>
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect>
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5"></rect>
          </svg>
          Katalog
        </a>

        <a href="admin.php" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-green-50 transition hover:bg-brand-dark/70 hover:text-white">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]">
            <path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path>
          </svg>
          Dashboard
        </a>

        <span class="flex cursor-default items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-green-50" aria-disabled="true">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]">
            <path d="M7 3.5h7l4 4V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"></path>
            <path d="M9.5 12h5"></path><path d="M9.5 16h3"></path>
          </svg>
          <span class="flex-1">Laporan</span>
          <span class="text-[10px] font-semibold uppercase tracking-wide text-green-50">Segera</span>
        </span>
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
      <a href="katalog.php" class="flex items-center gap-2">
        <span class="inline-flex h-8 w-8 shrink-0 overflow-hidden rounded-full ring-1 ring-brand/15">
          <img src="img/logo-160.jpg" alt="" width="32" height="32" class="h-full w-full scale-150 object-cover">
        </span>
        <span class="text-sm font-bold tracking-tight text-gray-900">FlowStuckHub</span>
      </a>
      <button type="button" data-cart-open class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15" aria-label="Buka pesanan">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
          <path d="M4.5 5.5h1.7l2.1 9.2a1.5 1.5 0 0 0 1.5 1.2h7.4a1.5 1.5 0 0 0 1.5-1.1l1.5-6.3H7.3"></path>
          <circle cx="9.5" cy="19" r="1.2"></circle><circle cx="17" cy="19" r="1.2"></circle>
        </svg>
        <span data-cart-count class="absolute -right-1 -top-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-accent px-1 text-[10px] font-bold text-brand-dark ring-2 ring-gray-50">0</span>
      </button>
    </header>

    <main id="main" class="flex-1 px-4 pb-14 pt-6 sm:px-6 lg:px-10 lg:py-9">
      <div class="mx-auto w-full max-w-7xl">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-[30px]">Katalog Barang</h1>
            <p class="mt-1 text-sm text-gray-500">Pilih barang, atur jumlah, lalu buat draft pesanan.</p>
          </div>

          <div class="flex w-full items-center gap-2.5 lg:w-auto">
            <form method="get" action="katalog.php" role="search" class="relative min-w-0 flex-1 lg:w-[360px] lg:flex-none">
              <?php if ($kategori !== 'Semua'): ?>
                <input type="hidden" name="kategori" value="<?= e($kategori) ?>">
              <?php endif; ?>
              <?php if ($urut !== 'nama'): ?>
                <input type="hidden" name="urut" value="<?= e($urut) ?>">
              <?php endif; ?>
              <label for="q" class="sr-only">Cari barang</label>
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-gray-400">
                <circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path>
              </svg>
              <input
                id="q"
                name="q"
                type="search"
                value="<?= e($q) ?>"
                placeholder="Cari nama atau SKU..."
                autocomplete="off"
                class="h-11 w-full rounded-full border border-gray-200 bg-white pl-11 pr-24 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-500 focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
              >
              <button type="submit" class="absolute right-1.5 top-1.5 h-8 rounded-full bg-brand px-4 text-xs font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/25">
                Cari
              </button>
            </form>

            <button type="button" data-cart-open class="hidden h-11 shrink-0 items-center gap-2 rounded-full bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark active:translate-y-px focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/25 lg:inline-flex" aria-label="Buka pesanan">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-[18px] w-[18px]">
                <path d="M4.5 5.5h1.7l2.1 9.2a1.5 1.5 0 0 0 1.5 1.2h7.4a1.5 1.5 0 0 0 1.5-1.1l1.5-6.3H7.3"></path>
                <circle cx="9.5" cy="19" r="1.2"></circle><circle cx="17" cy="19" r="1.2"></circle>
              </svg>
              Pesanan
              <span data-cart-count class="grid h-5 min-w-[20px] place-items-center rounded-full bg-accent px-1 text-[11px] font-bold text-brand-dark">0</span>
            </button>
          </div>
        </div>

        <!-- Ringkasan stok -->
        <div class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-gray-200 bg-gray-200 sm:grid-cols-4">
          <?php foreach ($statistik as $s): ?>
            <div class="bg-white px-4 py-3.5">
              <p class="text-[11px] font-medium text-gray-500"><?= e($s['label']) ?></p>
              <p class="mt-0.5 text-xl font-bold tracking-tight tabular-nums text-gray-900"><?= e($s['nilai']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Filter dan urutan -->
        <div class="mt-5 flex flex-col gap-3 border-b border-gray-200 pb-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="-mx-4 overflow-x-auto px-4 no-scrollbar lg:mx-0 lg:px-0">
            <div class="flex items-center gap-2">
              <?php foreach ($kategoriOpsi as $k): ?>
                <a href="<?= e($tautan(['kategori' => $k])) ?>"
                   <?= $k === $kategori ? 'aria-current="true"' : '' ?>
                   class="inline-flex h-9 shrink-0 items-center gap-2 rounded-full border px-3.5 text-xs font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15 <?= $k === $kategori ? 'border-brand bg-brand text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-brand/40 hover:text-brand' ?>">
                  <?= e($k) ?>
                  <span class="<?= $k === $kategori ? 'text-green-100' : 'text-gray-500' ?>"><?= (int) ($jumlahKategori[$k] ?? 0) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3 sm:justify-end">
            <p class="text-xs text-gray-500">
              <span class="font-semibold text-gray-900"><?= count($hasil) ?></span> barang
              <?php if ($adaFilter): ?>
                <a href="katalog.php" class="ml-1 font-semibold text-brand hover:underline">Reset</a>
              <?php endif; ?>
            </p>
            <form method="get" action="katalog.php">
              <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
              <?php if ($kategori !== 'Semua'): ?><input type="hidden" name="kategori" value="<?= e($kategori) ?>"><?php endif; ?>
              <label for="urut" class="sr-only">Urutkan barang</label>
              <select id="urut" name="urut" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-white pl-3 pr-8 text-xs font-semibold text-gray-700 focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10">
                <?php foreach ($opsiUrut as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>" <?= $urut === $nilai ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>

        <!-- Grid produk -->
        <?php if ($hasil): ?>
          <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($hasil as $b): ?>
              <?php
              $stok = status_stok($b);
              $src = foto($b);
              $habis = $b['stok'] < 1;
              ?>
              <article class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-[0_18px_34px_-20px_rgba(27,94,32,0.35)]">
                <div class="relative aspect-square overflow-hidden bg-[#F1F4EC]" style="background-image:radial-gradient(110% 85% at 50% 18%, #FCFDF9 0%, #F2F5ED 58%, #E7EDE0 100%)">
                  <?php if ($src): ?>
                    <img
                      src="<?= e($src) ?>"
                      alt="<?= e($b['alt']) ?>"
                      width="720"
                      height="720"
                      loading="lazy"
                      class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    >
                  <?php else: ?>
                    <div class="h-full w-full p-4 transition-transform duration-500 group-hover:scale-[1.04] sm:p-5">
                      <?= ilustrasi($b['slug']) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="flex flex-1 flex-col p-4 sm:p-5">
                  <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-medium text-gray-500"><?= e($b['kategori']) ?></span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold <?= e($stok['badge']) ?>"><?= e($stok['label']) ?></span>
                  </div>

                  <h2 class="mt-2 text-[15px] font-bold tracking-tight text-gray-900"><?= e($b['nama']) ?></h2>
                  <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-gray-500"><?= e($b['deskripsi']) ?></p>
                  <p class="mt-2 font-mono text-[10px] tracking-wide text-gray-500"><?= e($b['sku']) ?></p>

                  <div class="mt-auto pt-4">
                    <div class="flex items-end justify-between gap-3">
                      <div>
                        <p class="text-[11px] text-gray-500">Harga per <?= e($b['satuan']) ?></p>
                        <p class="text-[17px] font-bold tracking-tight tabular-nums text-gray-900"><?= rupiah($b['harga']) ?></p>
                      </div>
                      <div class="text-right">
                        <p class="text-[11px] text-gray-500">Stok</p>
                        <p class="text-sm font-semibold tabular-nums <?= $habis ? 'text-red-600' : 'text-gray-900' ?>"><?= (int) $b['stok'] ?> <?= e($b['satuan']) ?></p>
                      </div>
                    </div>

                    <?php if ($habis): ?>
                      <button type="button" disabled class="mt-4 h-10 w-full cursor-not-allowed rounded-lg bg-gray-100 text-sm font-semibold text-gray-500" aria-label="<?= e($b['nama']) ?> sedang habis">Stok habis</button>
                    <?php else: ?>
                      <button
                        type="button"
                        data-add="<?= e($b['sku']) ?>"
                        class="mt-4 inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-brand text-sm font-semibold text-white transition hover:bg-brand-dark active:translate-y-px focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/25"
                        aria-label="Tambah <?= e($b['nama']) ?> ke pesanan"
                      >
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" class="h-4 w-4"><path d="M12 5v14M5 12h14"></path></svg>
                        Tambah
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="h-5 w-5"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
            </span>
            <h2 class="mt-4 text-sm font-semibold text-gray-900">Barang tidak ditemukan</h2>
            <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">
              Tidak ada barang yang cocok<?= $q !== '' ? ' dengan &ldquo;' . e($q) . '&rdquo;' : '' ?><?= $kategori !== 'Semua' ? ' di kategori ' . e($kategori) : '' ?>. Coba kata kunci atau filter lain.
            </p>
            <a href="katalog.php" class="mt-5 inline-flex h-9 items-center rounded-lg bg-brand px-4 text-xs font-semibold text-white transition hover:bg-brand-dark">Tampilkan semua</a>
          </div>
        <?php endif; ?>

        <p class="mt-8 text-xs text-gray-500">Katalog demo. Harga dan stok adalah data contoh, pesanan tidak dikirim ke database.</p>
      </div>
    </main>
  </div>
</div>

<!-- Keranjang pesanan -->
<div id="cart-overlay" class="fixed inset-0 z-[60] hidden bg-gray-900/50 backdrop-blur-[2px]"></div>

<aside id="cart-panel" role="dialog" aria-modal="true" aria-labelledby="cart-title" aria-hidden="true" inert
       class="fixed inset-y-0 right-0 z-[70] flex w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300 ease-out">
  <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
    <div>
      <h2 id="cart-title" class="text-base font-bold tracking-tight text-gray-900">Pesanan</h2>
      <p id="cart-meta" class="mt-0.5 text-xs text-gray-500">0 item</p>
    </div>
    <button id="cart-close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15" aria-label="Tutup pesanan">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="h-5 w-5"><path d="M6 6l12 12M18 6 6 18"></path></svg>
    </button>
  </div>

  <div class="flex-1 overflow-y-auto px-5">
    <div id="cart-empty" class="flex h-full flex-col items-center justify-center py-14 text-center">
      <span class="grid h-12 w-12 place-items-center rounded-full bg-gray-100 text-gray-400">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
          <path d="M4.5 5.5h1.7l2.1 9.2a1.5 1.5 0 0 0 1.5 1.2h7.4a1.5 1.5 0 0 0 1.5-1.1l1.5-6.3H7.3"></path>
          <circle cx="9.5" cy="19" r="1.2"></circle><circle cx="17" cy="19" r="1.2"></circle>
        </svg>
      </span>
      <h3 class="mt-4 text-sm font-semibold text-gray-900">Pesanan masih kosong</h3>
      <p class="mt-1 max-w-[240px] text-sm leading-relaxed text-gray-500">Tambahkan barang dari katalog untuk mulai menyusun pesanan.</p>
      <button id="cart-empty-close" type="button" class="mt-5 h-9 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Kembali ke katalog</button>
    </div>

    <div id="cart-lines" class="hidden"></div>
  </div>

  <div class="border-t border-gray-100 bg-white px-5 py-4">
    <div class="flex items-center justify-between text-sm">
      <span class="text-gray-500">Subtotal</span>
      <span id="cart-subtotal" class="font-semibold tabular-nums text-gray-900">Rp 0</span>
    </div>
    <p class="mt-1 text-[11px] text-gray-500">Harga demo, belum termasuk pajak.</p>
    <button id="cart-checkout" type="button" disabled class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/25 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 disabled:hover:bg-gray-100">
      Buat pesanan
    </button>
    <button id="cart-clear" type="button" disabled class="mt-2 h-8 w-full rounded-lg text-xs font-semibold text-gray-500 transition hover:text-gray-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15 disabled:cursor-not-allowed disabled:opacity-50">
      Kosongkan pesanan
    </button>
  </div>
</aside>

<div id="toast" role="status" aria-live="polite" class="pointer-events-none fixed bottom-5 left-4 right-4 z-[80] flex translate-y-3 items-center gap-3 rounded-xl bg-gray-900 px-4 py-3 text-white opacity-0 shadow-xl transition duration-200 sm:left-6 sm:right-auto sm:max-w-sm lg:left-[292px]">
  <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-accent text-brand-dark">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
  </span>
  <span class="min-w-0 leading-tight">
    <span id="toast-text" class="block text-sm font-semibold"></span>
    <span id="toast-meta" class="mt-0.5 block text-xs text-gray-400"></span>
  </span>
</div>

<script>
(function () {
  const sidebar = document.getElementById('sidebar');
  const navOverlay = document.getElementById('nav-overlay');
  const openNavBtn = document.getElementById('nav-open');
  const closeNavBtn = document.getElementById('nav-close');
  const mainWrap = document.getElementById('main-wrap');
  const desktop = () => window.matchMedia('(min-width: 1024px)').matches;
  const navOpen = () => !sidebar.classList.contains('-translate-x-full');

  // Drawer navigasi (mobile)
  function setNav(open) {
    sidebar.classList.toggle('-translate-x-full', !open);
    navOverlay.classList.toggle('hidden', !open);
    openNavBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('overflow-hidden', open);
    mainWrap.inert = open;
    if (open) {
      closeNavBtn.focus();
    } else {
      mainWrap.inert = false;
      openNavBtn.focus();
    }
  }

  openNavBtn.addEventListener('click', () => setNav(true));
  closeNavBtn.addEventListener('click', () => setNav(false));
  navOverlay.addEventListener('click', () => setNav(false));

  window.addEventListener('resize', () => {
    if (desktop() && navOpen()) setNav(false);
  });

  // Pesanan
  const produk = <?= json_encode($produkJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const KUNCI = 'fsh-cart-v1';
  const cartPanel = document.getElementById('cart-panel');
  const cartOverlay = document.getElementById('cart-overlay');
  const cartClose = document.getElementById('cart-close');
  const cartLines = document.getElementById('cart-lines');
  const cartEmpty = document.getElementById('cart-empty');
  const cartMeta = document.getElementById('cart-meta');
  const cartSubtotal = document.getElementById('cart-subtotal');
  const cartCheckout = document.getElementById('cart-checkout');
  const cartClear = document.getElementById('cart-clear');
  const toast = document.getElementById('toast');
  const toastText = document.getElementById('toast-text');
  const toastMeta = document.getElementById('toast-meta');
  const cartTriggers = document.querySelectorAll('[data-cart-open]');
  const cartCounts = document.querySelectorAll('[data-cart-count]');
  const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

  let cart = bacaPesanan();
  let pemicuTerakhir = null;
  let timerToast;

  function bacaPesanan() {
    try {
      const mentah = JSON.parse(sessionStorage.getItem(KUNCI) || '{}');
      const bersih = {};
      Object.keys(mentah).forEach((sku) => {
        const p = produk[sku];
        const jumlah = Math.min(Math.max(parseInt(mentah[sku], 10) || 0, 0), p ? p.stok : 0);
        if (p && jumlah > 0) bersih[sku] = jumlah;
      });
      return bersih;
    } catch (error) {
      return {};
    }
  }

  function simpanPesanan() {
    try {
      sessionStorage.setItem(KUNCI, JSON.stringify(cart));
    } catch (error) {
      // Penyimpanan bisa diblokir; pesanan tetap jalan di memori.
    }
  }

  function totalItem() {
    return Object.values(cart).reduce((total, jumlah) => total + jumlah, 0);
  }

  function subtotalPesanan() {
    return Object.keys(cart).reduce((total, sku) => total + cart[sku] * produk[sku].harga, 0);
  }

  function tampilkanToast(judul, catatan) {
    toastText.textContent = judul;
    toastMeta.textContent = catatan || '';
    toast.classList.remove('opacity-0', 'translate-y-3');
    clearTimeout(timerToast);
    timerToast = setTimeout(() => toast.classList.add('opacity-0', 'translate-y-3'), 2600);
  }

  function el(tag, kelas, teks) {
    const node = document.createElement(tag);
    if (kelas) node.className = kelas;
    if (teks !== undefined) node.textContent = teks;
    return node;
  }

  function barisPesanan(sku) {
    const p = produk[sku];
    const jumlah = cart[sku];

    const baris = el('div', 'flex gap-3.5 border-b border-gray-100 py-4 last:border-b-0');

    if (p.foto) {
      const gambar = el('img', 'h-16 w-16 shrink-0 rounded-lg bg-gray-100 object-cover');
      gambar.src = p.foto;
      gambar.alt = '';
      gambar.width = 64;
      gambar.height = 64;
      baris.append(gambar);
    } else if (p.gambar) {
      const wadah = el('span', 'grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-lg bg-[#F1F4EC] p-1');
      wadah.innerHTML = p.gambar;
      baris.append(wadah);
    }

    const isi = el('div', 'min-w-0 flex-1');

    const atas = el('div', 'flex items-start justify-between gap-2');
    atas.append(el('p', 'truncate text-sm font-semibold text-gray-900', p.nama));

    const hapus = el('button', 'shrink-0 rounded text-[11px] font-semibold text-gray-500 transition hover:text-red-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15', 'Hapus');
    hapus.type = 'button';
    hapus.dataset.act = 'remove';
    hapus.dataset.sku = sku;
    hapus.setAttribute('aria-label', 'Hapus ' + p.nama + ' dari pesanan');
    atas.append(hapus);

    const harga = el('p', 'mt-0.5 text-xs text-gray-500', rupiah.format(p.harga) + ' / ' + p.satuan);

    const bawah = el('div', 'mt-2.5 flex items-center justify-between gap-2');
    const stepper = el('div', 'inline-flex items-center rounded-lg border border-gray-200');

    const kurang = el('button', 'grid h-8 w-8 place-items-center rounded-l-lg text-base leading-none text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30', '−');
    kurang.type = 'button';
    kurang.dataset.act = 'dec';
    kurang.dataset.sku = sku;
    kurang.disabled = jumlah <= 1;
    kurang.setAttribute('aria-label', 'Kurangi jumlah ' + p.nama);

    const jumlahEl = el('span', 'w-8 text-center text-sm font-semibold tabular-nums text-gray-900', String(jumlah));

    const tambah = el('button', 'grid h-8 w-8 place-items-center rounded-r-lg text-base leading-none text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30', '+');
    tambah.type = 'button';
    tambah.dataset.act = 'inc';
    tambah.dataset.sku = sku;
    tambah.disabled = jumlah >= p.stok;
    tambah.setAttribute('aria-label', 'Tambah jumlah ' + p.nama);

    stepper.append(kurang, jumlahEl, tambah);
    bawah.append(stepper, el('p', 'text-sm font-semibold tabular-nums text-gray-900', rupiah.format(p.harga * jumlah)));

    isi.append(atas, harga, bawah);
    baris.append(isi);

    return baris;
  }

  function renderPesanan(fokus) {
    const daftar = Object.keys(cart);
    const jumlah = totalItem();
    const subtotal = subtotalPesanan();

    cartCounts.forEach((node) => { node.textContent = String(jumlah); });
    cartTriggers.forEach((node) => node.setAttribute('aria-label', 'Buka pesanan, ' + jumlah + ' item'));
    cartMeta.textContent = jumlah + ' item';
    cartSubtotal.textContent = rupiah.format(subtotal);

    const kosong = daftar.length === 0;
    cartLines.classList.toggle('hidden', kosong);
    cartEmpty.classList.toggle('hidden', !kosong);
    cartCheckout.disabled = kosong;
    cartClear.disabled = kosong;

    if (kosong) {
      cartLines.replaceChildren();
      return;
    }

    cartLines.replaceChildren(...daftar.map(barisPesanan));

    if (fokus) {
      const berikut = cartLines.querySelector('button[data-act="' + fokus.act + '"][data-sku="' + fokus.sku + '"]');
      if (berikut && !berikut.disabled) {
        berikut.focus();
      } else {
        cartClose.focus();
      }
    }
  }

  function setPesanan(open) {
    cartPanel.classList.toggle('translate-x-full', !open);
    cartOverlay.classList.toggle('hidden', !open);
    cartPanel.inert = !open;
    cartPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.classList.toggle('overflow-hidden', open);

    if (open) {
      mainWrap.inert = true;
      sidebar.inert = true;
      if (mainWrap.style.overflowY !== 'hidden') mainWrap.dataset.overflowSebelum = mainWrap.style.overflowY || '';
      mainWrap.style.overflowY = 'hidden';
      cartClose.focus();
    } else {
      mainWrap.inert = false;
      sidebar.inert = false;
      mainWrap.style.overflowY = mainWrap.dataset.overflowSebelum || '';
      const tujuan = pemicuTerakhir && pemicuTerakhir.offsetParent !== null ? pemicuTerakhir : cartTriggers[0];
      if (tujuan) tujuan.focus();
    }
  }

  cartTriggers.forEach((node) => {
    node.addEventListener('click', () => {
      pemicuTerakhir = node;
      setPesanan(true);
    });
  });
  cartClose.addEventListener('click', () => setPesanan(false));
  cartOverlay.addEventListener('click', () => setPesanan(false));
  document.getElementById('cart-empty-close').addEventListener('click', () => setPesanan(false));

  cartLines.addEventListener('click', (event) => {
    const tombol = event.target.closest('button[data-act]');
    if (!tombol) return;

    const sku = tombol.dataset.sku;
    const aksi = tombol.dataset.act;
    if (!cart[sku]) return;

    if (aksi === 'inc' && cart[sku] < produk[sku].stok) {
      cart[sku] += 1;
    } else if (aksi === 'dec' && cart[sku] > 1) {
      cart[sku] -= 1;
    } else if (aksi === 'remove') {
      delete cart[sku];
    } else {
      return;
    }

    simpanPesanan();
    renderPesanan(aksi === 'remove' ? null : { act: aksi, sku: sku });
  });

  document.querySelectorAll('[data-add]').forEach((tombol) => {
    tombol.addEventListener('click', () => {
      const sku = tombol.dataset.add;
      const p = produk[sku];
      if (!p) return;

      const jumlah = cart[sku] || 0;
      if (jumlah >= p.stok) {
        tampilkanToast('Stok tidak cukup', p.nama + ' tersisa ' + p.stok + ' ' + p.satuan);
        return;
      }

      cart[sku] = jumlah + 1;
      simpanPesanan();
      renderPesanan();
      tampilkanToast(p.nama + ' ditambahkan', (jumlah + 1) + ' ' + p.satuan + ' di pesanan');
    });
  });

  cartCheckout.addEventListener('click', () => {
    const jumlah = totalItem();
    if (!jumlah) return;

    const subtotal = rupiah.format(subtotalPesanan());
    cart = {};
    simpanPesanan();
    renderPesanan();
    setPesanan(false);
    tampilkanToast('Pesanan demo dibuat', jumlah + ' item · ' + subtotal);
  });

  cartClear.addEventListener('click', () => {
    if (!totalItem()) return;
    cart = {};
    simpanPesanan();
    renderPesanan();
    cartClose.focus();
  });

  document.addEventListener('keydown', (event) => {
    if (cartPanel.inert) {
      if (!navOpen() || desktop()) return;

      if (event.key === 'Escape') {
        setNav(false);
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
      return;
    }

    if (event.key === 'Escape') {
      setPesanan(false);
      return;
    }

    if (event.key === 'Tab') {
      const fokus = cartPanel.querySelectorAll('a[href], button:not([disabled]), input, select, textarea');
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

  renderPesanan();
})();
</script>
</body>
</html>