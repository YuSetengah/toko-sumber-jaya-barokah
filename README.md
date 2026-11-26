# Toko Sumber Jaya Barokah

Website toko online (e-commerce) berbasis **PHP + MySQL**, dengan halaman pembeli dan halaman admin.

> **Status: dalam pengembangan.** Tampilan halaman publik dan halaman admin sudah ada.
> Proses daftar/login, keranjang, dan checkout masih dikerjakan (lihat [Status pengerjaan](#status-pengerjaan)).

## Teknologi

- PHP 8.x (mysqli), MySQL / MariaDB
- HTML, CSS, JavaScript, [Remix Icon](https://remixicon.com)
- Opsional: Midtrans Snap (pembayaran), RajaOngkir (ongkir), Google Login

## Struktur folder

```
.
├── index.php            Beranda
├── config.php           Koneksi database + pembacaan cookie login
├── env.sample.php       Contoh pengaturan (salin menjadi env.php)
├── .htaccess            Aturan URL & pengamanan berkas
├── admin/               Halaman admin
│   ├── login/ product/ category/ flashsale/ promotion/
│   └── transaction/ users/ alamat/ settings/ partials/
├── category/            Daftar produk per kategori
├── cart/  checkout/     Keranjang dan checkout
├── login/               Login pembeli (email + Google)
├── help/                Tentang kami, kebijakan privasi, hubungi kami, bantuan
├── partials/            Header, footer, navigasi bawah
├── assets/              CSS, JS, ikon, gambar
└── database/            Skema database (database.sql)
```

## Menjalankan di komputer sendiri (XAMPP)

1. Salin folder proyek ke `C:\xampp\htdocs\toko_sumber_jaya_barokah\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Di phpMyAdmin, buat database kosong bernama `toko_sumber_jaya_barokah`
   (collation `utf8mb4_general_ci`), lalu **Impor** `database/database.sql`.
4. Buka `http://localhost/toko_sumber_jaya_barokah/`.

Tanpa file `env.php`, aplikasi memakai nilai bawaan XAMPP (user `root`, tanpa password).

## Memasang di hosting

1. Buat database di panel hosting, lalu impor `database/database.sql`.
2. Salin `env.sample.php` menjadi `env.php`, isi alamat situs dan data database.
   **`env.php` berisi kata sandi dan tidak boleh di-commit** (sudah ada di `.gitignore`).
3. Unggah isi proyek ke folder web hosting (`htdocs`), **kecuali** folder `database/`, `.git/`, dan `README.md`.
4. Setelah situs berjalan, ubah `'debug' => false` di `env.php`.

## Pustaka pihak ketiga (tidak disertakan)

Letakkan sendiri di `assets/composer/` jika fitur terkait dipakai:

- `midtrans-php-master/` — pembayaran Midtrans
- `google-api-client-php-7.4/` — login dengan Google

## Status pengerjaan

- [x] Beranda, daftar produk per kategori, halaman bantuan
- [x] Skema database (17 tabel) dan data awal
- [x] Halaman admin: dashboard, produk (`admin/product/tambah.php`), kategori, flash sale, banner, pengaturan, akun user, alamat user, transaksi, cetak nota
- [x] Halaman keranjang dan checkout (tampilan)
- [ ] Proses daftar dan login (pembeli & admin), logout
- [ ] Halaman daftar akun, detail produk, profil, notifikasi, pencarian
- [ ] File JavaScript dan endpoint `system/` untuk keranjang, checkout, dan aksi admin
- [ ] CSS halaman admin, keranjang, checkout, dan bantuan
- [ ] Daftar provinsi dan ongkos kirim (RajaOngkir)

## Keamanan

- Repositori ini sebaiknya **private**: kunci enkripsi cookie login masih tertulis di kode
  dan direncanakan dipindah ke `env.php`.
- Jangan unggah file pengujian apa pun yang memberi akses admin tanpa password.
- Gambar unggahan tidak disimpan di repositori (lihat `.gitignore`).
