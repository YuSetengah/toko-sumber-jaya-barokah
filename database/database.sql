-- =====================================================================
-- DATABASE TOKO ONLINE (17 tabel) - Toko Sumber Jaya Barokah
-- Cara pakai: di phpMyAdmin, PILIH DATABASE Anda terlebih dahulu, lalu
-- klik tab Impor dan pilih file ini. File ini tidak membuat database
-- (di hosting gratis, database dibuat lewat panel hosting).
-- Aman diimpor ulang: tabel yang sudah ada tidak diubah.
-- =====================================================================

-- ---------- AKUN & ALAMAT ----------
CREATE TABLE IF NOT EXISTS `akun` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `foto`         TEXT         NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email`        VARCHAR(200) NOT NULL,
  `no_whatsapp`  VARCHAR(14)  NOT NULL,
  `password`     TEXT         NOT NULL,           -- hasil password_hash(); akun Google dikosongkan
  `waktu`        VARCHAR(100) NOT NULL,           -- Y-m-d H:i:s
  `tipe_daftar`  VARCHAR(50)  NOT NULL,           -- kosong = daftar lewat website, 'Google'
  `tipe_akun`    VARCHAR(100) NOT NULL,           -- kosong = pembeli, 'Admin' = admin
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_akun_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `lokasi_user` (
  `idlokasi`       INT(11)     NOT NULL AUTO_INCREMENT,
  `id_user`        INT(11)     NOT NULL,
  `provinsi`       TEXT        NOT NULL,
  `id_provinsi`    INT(11)     NOT NULL,
  `kota`           TEXT        NOT NULL,
  `id_kota`        INT(11)     NOT NULL,
  `kecamatan`      TEXT        NOT NULL,
  `kelurahan`      TEXT        NOT NULL,
  `alamat_lengkap` TEXT        NOT NULL,
  `norek`          VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`idlokasi`),
  KEY `idx_lokasi_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------- KATALOG ----------
CREATE TABLE IF NOT EXISTS `kategori` (
  `id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(200) NOT NULL,
  `icon` VARCHAR(100) NOT NULL,                   -- nama file di assets/icons/category/
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `iklan` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `id_kategori` INT(11)      NOT NULL,
  `gambar`      LONGTEXT     NOT NULL,            -- nama file dipisah koma: a.jpg,b.jpg
  `judul`       VARCHAR(200) NOT NULL,
  `harga`       INT(10)      NOT NULL,
  `deskripsi`   LONGTEXT     NOT NULL,
  `berat`       INT(11)      NOT NULL,            -- gram
  `warna`       TEXT         NOT NULL,            -- Hitam,Putih
  `ukuran`      TEXT         NOT NULL,            -- S===75000,M===80000
  `stok`        INT(5)       NOT NULL,
  `terjual`     INT(5)       NOT NULL,
  `diskon`      INT(3)       NOT NULL,            -- persen
  `tipe_iklan`  VARCHAR(15)  NOT NULL,            -- kosong = biasa, 'flash sale'
  `waktu`       TEXT         NOT NULL,
  `status`      VARCHAR(50)  NOT NULL,            -- kosong = aktif
  PRIMARY KEY (`id`),
  KEY `idx_iklan_kategori` (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `banner_promo` (
  `idbanner` INT(11) NOT NULL AUTO_INCREMENT,
  `image`    TEXT    NOT NULL,                    -- nama file di assets/image/banner/
  `status`   TEXT    NOT NULL,                    -- kosong = aktif
  PRIMARY KEY (`idbanner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `flashsale` (
  `id_fs`          INT(11) NOT NULL AUTO_INCREMENT,
  `waktu_berakhir` TEXT    NOT NULL,              -- timestamp (detik), bukan teks tanggal
  PRIMARY KEY (`id_fs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------- BELANJA ----------
CREATE TABLE IF NOT EXISTS `keranjang` (
  `id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `id_iklan`  INT(11)      NOT NULL,
  `id_user`   INT(11)      NOT NULL,
  `jumlah`    INT(4)       NOT NULL,
  `harga_k`   INT(15)      NOT NULL,
  `diskon_k`  INT(11)      NOT NULL,
  `warna_k`   TEXT         NOT NULL,
  `ukuran_k`  TEXT         NOT NULL,
  `waktu`     VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_keranjang_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `invoice` (
  `idinvoice`       INT(11)      NOT NULL AUTO_INCREMENT,
  `id_user`         INT(11)      NOT NULL,
  `id_iklan`        INT(11)      NOT NULL,
  `jumlah`          INT(5)       NOT NULL DEFAULT 1,
  `harga_i`         INT(15)      NOT NULL DEFAULT 0,       -- harga saat dibeli
  `diskon_i`        INT(3)       NOT NULL DEFAULT 0,       -- diskon (%) saat dibeli
  `harga_ongkir`    INT(15)      NOT NULL DEFAULT 0,
  `kurir`           VARCHAR(50)  NOT NULL DEFAULT '',
  `layanan_kurir`   VARCHAR(100) NOT NULL DEFAULT '',
  `etd`             VARCHAR(20)  NOT NULL DEFAULT '',      -- perkiraan lama kirim (hari)
  `provinsi`        VARCHAR(100) NOT NULL DEFAULT '',      -- format: id,nama
  `kota`            VARCHAR(100) NOT NULL DEFAULT '',      -- format: id,nama (kosong = alamat belum diisi)
  `alamat_lengkap`  VARCHAR(500) NOT NULL DEFAULT '',
  `type`            VARCHAR(50)  NOT NULL DEFAULT '',      -- metode pembayaran
  `waktu`           VARCHAR(100) NOT NULL DEFAULT '',      -- waktu pembayaran
  `transaction`     VARCHAR(50)  NOT NULL DEFAULT '',      -- status pembayaran, mis. settlement
  `waktu_transaksi` VARCHAR(100) NOT NULL DEFAULT '',      -- Y-m-d H:i:s
  `bukti_transfer`  VARCHAR(255) NOT NULL DEFAULT '',      -- nama file bukti transfer (pembayaran manual)
  PRIMARY KEY (`idinvoice`),
  KEY `idx_invoice_user` (`id_user`),
  KEY `idx_invoice_iklan` (`id_iklan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `rating` (
  `idrating`       INT(11) NOT NULL AUTO_INCREMENT,
  `id_invoice_rat` INT(11) NOT NULL,
  `star_rat`       INT(1)  NOT NULL,
  `deskripsi_rat`  TEXT    NOT NULL,
  `waktu_rat`      TEXT    NOT NULL,
  PRIMARY KEY (`idrating`),
  KEY `idx_rating_invoice` (`id_invoice_rat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `notification` (
  `id_notif`        INT(11) NOT NULL AUTO_INCREMENT,
  `id_user`         INT(11) NOT NULL,
  `id_invoice`      INT(11) NOT NULL,
  `nama_notif`      TEXT    NOT NULL,
  `deskripsi_notif` TEXT    NOT NULL,
  `waktu_notif`     TEXT    NOT NULL,
  `status_notif`    TEXT    NOT NULL,             -- kosong = belum dibaca
  PRIMARY KEY (`id_notif`),
  KEY `idx_notif_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `nomor_rekening` (
  `idnorek`   INT(11) NOT NULL AUTO_INCREMENT,
  `nama_bank` TEXT    NOT NULL,
  `norek`     TEXT    NOT NULL,
  `an`        TEXT    NOT NULL,                   -- atas nama
  PRIMARY KEY (`idnorek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------- PENGATURAN SITUS ----------
CREATE TABLE IF NOT EXISTS `setting_header` (
  `id_hs`              INT(11) NOT NULL AUTO_INCREMENT,
  `logo`               TEXT    NOT NULL,
  `title_name`         TEXT    NOT NULL,
  `placeholder_search` TEXT    NOT NULL,
  PRIMARY KEY (`id_hs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `setting_footer` (
  `id_fo`         INT(11) NOT NULL AUTO_INCREMENT,
  `name_social`   TEXT    NOT NULL,
  `icon_social`   TEXT    NOT NULL,               -- tag ikon HTML, mis. <i class="ri-instagram-line"></i>
  `link_social`   TEXT    NOT NULL,
  `status_social` TEXT    NOT NULL,               -- 'active' / 'nonactive'
  PRIMARY KEY (`id_fo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `setting_apikey` (
  `id_apikey`            INT(11) NOT NULL AUTO_INCREMENT,
  `google_client_id`     TEXT    NOT NULL,
  `google_client_secret` TEXT    NOT NULL,
  `midtrans_client_key`  TEXT    NOT NULL,
  `midtrans_server_key`  TEXT    NOT NULL,
  `rajaongkir_key`       TEXT    NOT NULL,
  PRIMARY KEY (`id_apikey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `setting_email` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `email_notif`   TEXT         NOT NULL,
  `host_smtp`     VARCHAR(100) NOT NULL,
  `port_smtp`     INT(11)      NOT NULL,
  `username_smtp` VARCHAR(100) NOT NULL,
  `password_smtp` VARCHAR(100) NOT NULL,
  `setfrom_smtp`  VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `setting_lokasi` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `provinsi`    TEXT    NOT NULL,
  `kota`        TEXT    NOT NULL,
  `provinsi_id` INT(11) NOT NULL,
  `kota_id`     INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `setting_pembayaran` (
  `id`     INT(11) NOT NULL AUTO_INCREMENT,
  `tipe`   TEXT    NOT NULL,                      -- 'Manual' atau 'Midtrans'
  `status` TEXT    NOT NULL,                      -- 'active' = metode yang dipakai
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
-- DATA AWAL (wajib ada: kode memanggil baris dengan id = 1)
-- Ganti isinya lewat halaman admin Pengaturan setelah situs berjalan.
-- =====================================================================
INSERT IGNORE INTO `setting_header` (`id_hs`, `logo`, `title_name`, `placeholder_search`)
VALUES (1, 'logo.png', 'Toko Sumber Jaya Barokah', 'Cari produk di sini...');

INSERT IGNORE INTO `setting_apikey` (`id_apikey`, `google_client_id`, `google_client_secret`, `midtrans_client_key`, `midtrans_server_key`, `rajaongkir_key`)
VALUES (1, '', '', '', '', '');

INSERT IGNORE INTO `setting_email` (`id`, `email_notif`, `host_smtp`, `port_smtp`, `username_smtp`, `password_smtp`, `setfrom_smtp`)
VALUES (1, '', '', 587, '', '', 'Toko Sumber Jaya Barokah');

INSERT IGNORE INTO `setting_lokasi` (`id`, `provinsi`, `kota`, `provinsi_id`, `kota_id`)
VALUES (1, 'Sumatera Selatan', 'Palembang', 16, 1671);

INSERT IGNORE INTO `setting_pembayaran` (`id`, `tipe`, `status`) VALUES
(1, 'Manual', 'active'),
(2, 'Midtrans', 'nonactive');

-- GANTI dengan rekening toko yang sebenarnya
INSERT IGNORE INTO `nomor_rekening` (`idnorek`, `nama_bank`, `norek`, `an`)
VALUES (1, 'Bank BCA', '0000000000', 'Toko Sumber Jaya Barokah');

-- flash sale mulai dalam keadaan tidak aktif (waktu berakhir = sekarang)
INSERT IGNORE INTO `flashsale` (`id_fs`, `waktu_berakhir`) VALUES (1, UNIX_TIMESTAMP());

INSERT IGNORE INTO `kategori` (`id`, `nama`, `icon`) VALUES
(1, 'Fashion Pria',   'fashion-pria.png'),
(2, 'Fashion Wanita', 'fashion-wanita.png'),
(3, 'Elektronik',     'elektronik.png');

-- GANTI tautan dengan akun media sosial toko yang sebenarnya
INSERT IGNORE INTO `setting_footer` (`id_fo`, `name_social`, `icon_social`, `link_social`, `status_social`) VALUES
(1, 'Instagram', '<i class="ri-instagram-line"></i>', 'https://instagram.com/', 'active'),
(2, 'WhatsApp',  '<i class="ri-whatsapp-line"></i>',  'https://wa.me/6280000000000', 'active'),
(3, 'Facebook',  '<i class="ri-facebook-circle-line"></i>', 'https://facebook.com/', 'active');
