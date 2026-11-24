# FlowStuckHub

Sistem katalog dan manajemen gudang berbasis PHP Native dan MySQL, dengan antarmuka hijau forest, tiga level pengguna, serta alur pesanan dan retur barang.

> Status: proyek demo dan pembelajaran. Data contoh disediakan, bukan data produksi.

## Daftar isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Struktur folder](#struktur-folder)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Akun demo](#akun-demo)
- [Hak akses halaman](#hak-akses-halaman)
- [Halaman demo statis](#halaman-demo-statis)
- [Catatan keamanan](#catatan-keamanan)
- [Kustomisasi](#kustomisasi)
- [Troubleshooting](#troubleshooting)

## Fitur

### Autentikasi dan akun

- Login dengan `password_verify()` dan session
- Registrasi pelanggan baru
- Lupa password dengan token sekali pakai (berlaku 1 jam)
- Reset password melalui tautan token
- Edit profil, ganti nama, alamat, nomor HP, dan foto profil
- Logout dan proteksi halaman berdasarkan role

### Pelanggan (`user`)

- Katalog barang dari database dengan pencarian dan keranjang
- Pengajuan pesanan
- Status pengiriman dan riwayat pesanan
- Pengajuan retur barang rusak dengan unggah foto
- Halaman profil

### Admin gudang (`admin`)

- Dashboard ringkasan stok dan mutasi
- Pencatatan barang masuk dan keluar
- Pengelolaan surat jalan
- Pemantauan retur

### Super admin (`super_admin`)

- Dashboard dan ringkasan seluruh gudang
- Kelola data barang (tambah dan ubah)
- Update stok
- Laporan logistik dan rincian mutasi
- Kelola retur
- Akses penuh ke modul admin

### Halaman demo statis

- `katalog.php`: storefront contoh dengan grid produk, filter kategori, pencarian, urutan, keranjang `sessionStorage`, dan ilustrasi packshot vektor
- `admin.php`: dashboard contoh dengan 4 kartu ringkasan, grafik Chart.js, dan tabel transaksi

## Teknologi

- PHP 8 Native tanpa framework
- MySQL atau MariaDB melalui PDO prepared statement
- Tailwind CSS melalui CDN dengan konfigurasi kustom
- Vanilla JavaScript
- Chart.js 4.4.1 melalui CDN
- Font Plus Jakarta Sans melalui Google Fonts
- Ikon SVG inline

## Struktur folder

```
flowstuckhub/
├─ admin/                     Modul admin gudang
│  ├─ index.php               Dashboard admin
│  ├─ surat-jalan.php         Pengelolaan surat jalan
│  └─ update-barang.php       Catat mutasi stok masuk dan keluar
├─ super_admin/               Modul super admin
│  ├─ index.php               Dashboard super admin
│  ├─ barang.php              Kelola barang
│  ├─ update-stok.php         Update stok
│  ├─ laporan.php             Laporan logistik dan mutasi
│  └─ retur.php               Kelola retur
├─ user/                      Modul pelanggan
│  ├─ katalog.php             Katalog dari database
│  ├─ profile.php             Edit profil
│  ├─ retur.php               Daftar dan status retur
│  ├─ retur-form.php          Formulir pengajuan retur
│  └─ status-pengiriman.php   Status pengiriman pesanan
├─ assets/
│  ├─ style.css               Gaya halaman lama
│  └─ demo.css
├─ config/
│  └─ database.php            Koneksi PDO dan konstanta BASE
├─ database/
│  ├─ flowstuckhub.sql        Skema dan data contoh
│  └─ seed_users.php          Seed akun contoh (hapus setelah dipakai)
├─ img/
│  ├─ Logo 1.jpeg             Logo sumber 1024x1024
│  ├─ logo-160.jpg            Logo 160x160 untuk UI dan favicon
│  └─ products/               Foto produk opsional
├─ includes/
│  ├─ auth.php                Session, CSRF, role, helper
│  ├─ header.php              Header dan sidebar
│  └─ footer.php              Footer
├─ uploads/                   Unggahan foto retur dan barang
├─ admin.php                  Demo dashboard statis
├─ katalog.php                Demo storefront statis
├─ login.php                  Halaman masuk
├─ register.php               Halaman daftar
├─ forgot-password.php        Lupa password
├─ reset-password.php         Reset password
├─ profile.php                Profil pengguna
├─ dashboard.php              Dashboard admin berbasis database
├─ index.php                  Redirect sesuai status login
└─ logout.php                 Hapus session
```

## Persyaratan

- PHP 8.0 atau lebih baru (disarankan 8.3)
- MySQL 5.7+ atau MariaDB 10.4+
- Apache (XAMPP, Laragon, atau server lain)
- Browser modern
- Koneksi internet untuk CDN Tailwind, Chart.js, dan Google Fonts

## Instalasi

1. Salin folder proyek ke direktori web server, misalnya `htdocs/flowstuckhub`.
2. Nyalakan Apache dan MySQL.
3. Buka `http://localhost/phpmyadmin`, buat database bernama `flowstuckhub`.
4. Impor `database/flowstuckhub.sql`. File ini sudah berisi tabel, data barang, dan akun contoh.
5. Sesuaikan `BASE` dan kredensial database di `config/database.php` bila perlu.
6. Buka `http://localhost/flowstuckhub/login.php`.
7. Opsional: jalankan `http://localhost/flowstuckhub/database/seed_users.php` untuk menambah akun contoh alternatif, lalu hapus file tersebut.

Jika database sudah pernah dibuat sebelumnya, jalankan bagian `ALTER TABLE` di akhir file SQL untuk menambah kolom baru tanpa menghapus data.

## Akun demo

Akun dari `database/flowstuckhub.sql` (password semuanya `password`):

| Username | Password | Role |
| --- | --- | --- |
| `superadmin` | `password` | super_admin |
| `admin` | `password` | admin |
| `driver_andi` | `password` | user |
| `budi` | `password` | user |
| `citra` | `password` | user |

Akun tambahan dari `database/seed_users.php`:

| Username | Password | Role |
| --- | --- | --- |
| `purchasing` | `super123` | super_admin |
| `gudang` | `gudang123` | admin |
| `budi` | `budi123` | user |

Ganti password akun demo sebelum dipakai di lingkungan nyata.

## Hak akses halaman

| Halaman | user | admin | super_admin |
| --- | --- | --- | --- |
| `user/katalog.php` | Ya | Tidak | Tidak |
| `user/status-pengiriman.php` | Ya | Tidak | Tidak |
| `user/retur.php` | Ya | Tidak | Tidak |
| `profile.php` | Ya | Ya | Ya |
| `admin/index.php` | Tidak | Ya | Ya |
| `admin/update-barang.php` | Tidak | Ya | Ya |
| `admin/surat-jalan.php` | Tidak | Ya | Ya |
| `super_admin/index.php` | Tidak | Tidak | Ya |
| `super_admin/barang.php` | Tidak | Tidak | Ya |
| `super_admin/laporan.php` | Tidak | Tidak | Ya |
| `super_admin/retur.php` | Tidak | Tidak | Ya |

Setelah login, pengguna diarahkan otomatis: `user` ke `user/katalog.php`, `admin` dan `super_admin` ke `dashboard.php`.

## Halaman demo statis

`katalog.php` dan `admin.php` adalah halaman demo mandiri yang memakai data contoh di dalam file, bukan database. Keduanya berguna untuk presentasi tampilan.

- `katalog.php` punya filter kategori, urutan harga dan stok, keranjang dengan `sessionStorage` (`fsh-cart-v1`), dan checkout demo.
- `admin.php` punya grafik Chart.js dan tabel transaksi contoh.

Halaman demo ini tidak terhubung ke autentikasi. Lindungi atau hapus sebelum dipakai di produksi.

## Catatan keamanan

- Password disimpan dengan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Form menggunakan token CSRF melalui `csrf_field()` dan `csrf_check()`.
- Query database memakai PDO prepared statement.
- `database/seed_users.php` hanya untuk pengembangan. Hapus setelah dijalankan.
- `forgot-password.php` menampilkan token reset di halaman saat mode lokal untuk memudahkan pengujian tanpa email. Hapus tampilan tersebut dan sambungkan ke pengiriman email sebelum produksi.
- Kredensial database bawaan (`root` tanpa password) hanya cocok untuk XAMPP lokal.

## Kustomisasi

### Logo

Ganti `img/Logo 1.jpeg` dengan logo baru, lalu buat versi 160x160:

```bash
sips -z 160 160 -s format jpeg -s formatOptions 80 "img/Logo 1.jpeg" --out img/logo-160.jpg
```

### Foto produk

Taruh foto di `img/products/` dengan nama sesuai slug:

```
air-mineral.jpg
beras.jpg
gula.jpg
kopi.jpg
mie-instan.jpg
minyak.jpg
```

Format yang didukung: jpg, jpeg, png, webp. Jika foto belum ada, halaman katalog demo memakai ilustrasi vektor bawaan.

### Warna dan font

Warna utama didefinisikan di konfigurasi Tailwind pada setiap halaman demo:

- `brand`: `#2E7D32`
- `brand.light`: `#4CAF50`
- `brand.dark`: `#1B5E20`
- `accent`: `#CDDC39`

### Menu navigasi

Menu sidebar diatur di `includes/header.php`. Tambahkan entri setelah halaman barunya siap.

## Troubleshooting

**Koneksi database gagal**

Pastikan MySQL menyala, database `flowstuckhub` sudah dibuat, dan kredensial di `config/database.php` sesuai.

**Halaman menampilkan 403**

Akun yang dipakai tidak memiliki role untuk halaman tersebut. Login dengan role yang sesuai.

**CSS atau grafik tidak muncul**

Tailwind dan Chart.js dimuat dari CDN. Pastikan koneksi internet aktif. Halaman demo `admin.php` memiliki fallback saat Chart.js gagal dimuat.

**Token reset password tidak valid**

Token berlaku 1 jam dan hanya bisa dipakai sekali. Ulangi proses lupa password.

**Folder uploads tidak bisa menulis**

Beri izin tulis ke `uploads/barang` dan `uploads/retur` sesuai user web server.

## Lisensi

Belum ditentukan. Tambahkan file `LICENSE` sebelum publikasi jika diperlukan.
