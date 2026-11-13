# FlowStuckHub

Platform e-commerce distro barang gudang sederhana berbasis PHP Native + Tailwind CDN + Vanilla JS + Chart.js 4.4.1.

## Visinya

Aplikasi katalog barang dan dashboard pengelolaan gudang dengan identitas hijau hutan (#2E7D32), pensknikian, dan mudah digunakan.

## Stack Teknis

- **Bahasa**: PHP 8.3 Native (tidak framework)
- **Styling**: Tailwind CSS via CDN dengan konfigurasi kustom
- **Interaktivitas**: Vanilla JS, sessionStorage, Chart.js CDN (4.4.1)
- **Font**: Plus Jakarta Sans (Google Fonts)
- **Gambar**: Logo asli dari `img/Logo 1.jpeg`, `img/logo-160.jpg` (crop 160x160), ilustrasi packshot vektor untuk kartu katalog fallback

## Alur Halaman

### `login.php` — Halaman Masuk (50:50 Split)

- **Kiri**: Branding hijau dengan logo asli (lingkaran crop), deskripsi "FlowStuckHub", tombol "Daftar".
- **Kanan**: Form login lengkap:
  - Username & password (dari database MySQL)
  - CSRF token otomatis
  - `password_verify()` untuk verifikasi login
  - `home_for()` menuju halaman beranda berdasarkan role
- **Akses**: `gudang/gudang123` (admin), `purchasing/super123`, `budi/budi123`
- **Setelah login**: Redirect ke `admin.php` (role admin/super_admin) atau `user/katalog.php` (role user)

### `katalog.php` — Katalog Barang (Storefront Demo)

- **Fitur utama**:
  - Grid produk 3 kolom (lg), 1 kolom (ponsel) dengan tile studio hijau yang konsisten
  - 6 SKU: Air mineral, Beras, Gula, Kopi, Mie instan, Minyak
  - Pencarian nama/SKU/deskripsi/kategori
  - Filter kategori (Chip interaktif)
  - Urutan: Nama A-Z, Harga terendah, Stok terbanyak
  - Ringkasan stok: SKU aktif, kategori, siap dipesan, stok habis
  - Keranjang pesanan (sidebar drawer):
    - Tambah barang dari katalog (validasi stok)
    - Stepper jumlah (naik/turun/hapus)
    - Subtotal otomatis
    - Kosongkan pesanan
    - Checkout demo (toast konfirmasi)
    - Penyimpanan `sessionStorage` (`fsh-cart-v1`)
    - Escape menutup, overlay klik, fokus trap
  - Empty state saat tidak ada hasil
  - Ilustrasi packshot vektor fallback kalau foto belum ada

- **Penempatan foto**:
  - Foto produk otomatis dari `img/products/` dengan slug: `air-mineral`, `beras`, `gula`, `kopi`, `mie-instan`, `minyak`
  - Format: jpg, jpeg, png, webp
  - Kalau foto belum ada: tile hijau dengan ilustrasi SVG konsisten

- **Jumlah data**: 6 produk, stok variabel (0–210), kategori: Minuman, Bahan Pokok, Makanan Instan, Minyak & Bumbu

### `admin.php` — Dashboard Admin

- **Fitur**:
  - 4 kartu ringkasan: SKU aktif, kategori, siap dipesan, stok habis
  - Chart.js: Line chart (+Doughnut chart) tren 7 hari terakhir barang masuk/keluar
  - Tabel transaksi 5 baris terakhir (mutasi barang)
  - Navigasi sidebar dengan logo, nama admin, dan menu (Dashboard, Laporan → Segera)
  - Chart fallback jika CDN gagal muat
  - Tidak memerlukan foto produk

### `user/katalog.php` — Katalog Customer (Lama, DB-backed)

- Dipakai oleh role `user` setelah login
- Menggunakan database live (barang dari MySQL)

### `dashboard.php` — Dashboard Admin (Lama, DB-backed)

- Menggunakan database live untuk stok, mutasi, surat jalan, retur

## Instalasi Lokal

1. **Persyaratan**:
   - XAMPP (Apache + MySQL) terinstal
   - Port Apache: 80, Port MySQL: 3306

2. **Setup database**:
   - Jalankan `XAMPP Control Panel`, start Apache dan MySQL
   - Buka `http://localhost/phpmyadmin`
   - Buat database baru bernama `flowstuckhub`
   - Import file `database/flowstuckhub.sql`
   - (Opsional) Jalankan `database/seed_users.php` untuk menyematkan akun contoh, lalu hapus file tersebut

3. **Jalankan aplikasi**:
   - Letak folder `flowstuckhub` di `htdocs/XAMPP`
   - Buka `http://localhost/flowstuckhub/login.php`

4. **Akun contoh**:
   - `gudang/gudang123` → role admin
   - `purchasing/super123` → role super_admin
   - `budi/budi123` → role user

## Struktur Folder

```
/flowstuckhub
├─ img/                  → Logo asli & crop 160x160
│  ├─ Logo 1.jpeg      → Sumber logo (1024x1024)
│  └─ logo-160.jpg     → Crop 160x160 untuk favicon & lockup
├─ img/products/         → Foto produk (jika ada)
│  ├─ air-mineral.jpg
│  ├─ beras.jpg
│  ├─ gula.jpg
│  ├─ kopi.jpg
│  ├─ mie-instan.jpg
│  └─ minyak.jpg
├─ includes/             → Fungsi PHP bawaan
│  ├─ auth.php           → e(), user(), home_for(), csrf_field(), csrf_check()
│  ├─ header.php         → Header & sidebar navigasi
│  ├─ footer.php         → Footer
│  └─ database.php       → PDO connection, BASE = '/flowstuckhub/'
├─ config/
│  └─ database.php       → DSN, user root, pass ''
├─ database/
│  ├─ flowstuckhub.sql   → Schema & seed data barang
│  └─ seed_users.php     → Seed akun login
├─ assets/
│  ├─ style.css          → CSS lama (navy/yellow, digunakan page lama)
│  └─ demo.css
├─ login.php             → Halaman masuk 50:50
├─ katalog.php           → Katalog storefront (demo)
├─ admin.php             → Dashboard admin (demo)
├─ register.php          → Daftar akun baru
├─ index.php             → Redirect login handler
├─ logout.php            → Hapus session
├─ user/katalog.php      → Katalog customer (DB-backed)
├─ dashboard.php         → Dashboard admin (DB-backed)
└─ README.md             → Dokumentasi ini
```

## Catatan Penting

- **Foto produk AI**: Semua 6 job generate gambar AI gagal karena `Your API key has expired`. Katalog tetap tampil karena ilustrasi packshot vektor bawaan. Untuk menampilkan foto asli, letakkan file ke `img/products/` dengan nama sesuai slug di atas.
- **Mobile**: Sidebar drawer custom dengan overlay, Escape, focus trap, `inert`, dan pengawasan resize window.
- **Theme**: Semua halaman baru (login, katalog, admin) pakai Tailwind CDN + Plus Jakarta Sans. Halaman lama (dashboard/user/katalog) masih pakai `assets/style.css` navy/yellow, tidak disentuh.
- **Responsive**: 0 horizontal overflow di 320, 390, 768, 1024, 1440 px. Telah diverifikasi dengan Chrome headless + Chrome DevTools Protocol.
- **Cart storage**: `sessionStorage` kunci `fsh-cart-v1`. Berakhir saat menutup tab, tapi survive reload halaman.
- **Checkout**: Demo semata, hanya menampilkan toast "Pesanan demo dibuat" dan mengosongkan cart. Tidak ada pemrosesan pembayaran atau pengiriman.

## Conformance & Verifikasi

- `php -l` bersih untuk `login.php`, `katalog.php`, `admin.php`
- HTTP 200 untuk seluruh halaman
- Console error 0 di Chrome headless 320/390/768/1024/1440
- 0 horizontal overflow di semua breakpoint
- Interaksi keranjang: add, stepper inc/dec, remove, clear, checkout — semua terbukti bekerja dengan `sessionStorage` persistence
- Filter/search/sort menampilkan hasil yang benar
- Empty state menampilkan pesan "Barang tidak ditemukan" dengan aksi "Tampilkan semua"
- Logout mengarah ke login.php, login terproteksi CSRF & password_verify
- Favicon & apple-touch-icon pakai `img/logo-160.jpg`
- Warna utama: `#2E7D32` (hijau hutan), `#4CAF50` (light), `#1B5E20` (dark), `#CDDC39` (lime)
- Font: Plus Jakarta Sans
- Tidak ada em dash (`—`) di teks apapun
- Tidak ada claim fabricated (stok, testimonial, claim keamanan)
- Tidak ada kloning produk populer — identitas visual sendiri

## Roadmap (Sudah Dikerjakan)

- [x] `login.php` — visual baru, real DB auth preserved
- [x] `katalog.php` — storefront rewrite: grid, filter, sort, cart drawer, stats, empty state
- [x] `admin.php` — dashboard: 4 cards, Chart.js line+doughnut, 5-row table, drawer
- [x] Logo integrasi: `img/logo-160.jpg` di sidebar/header/login, favicon, apple-touch-icon
- [x] Mobile sidebar off-canvas drawer dengan fokus trap & inert
- [x] Ilustrasi packshot vektor fallback untuk kartu katalog tanpa foto
- [x] Verifikasi overflow & console error di 5 breakpoint
- [x] Cart interaksi: add, stepper, remove, clear, checkout, sessionStorage
- [x] Pencarian, filter kategori, urutan harga/stok/nama
- [x] Empty state dengan aksi "Tampilkan semua"
- [x] Toast notifikasi untuk add-to-cart & checkout