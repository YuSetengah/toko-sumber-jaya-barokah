<?php
// =====================================================================
// Halaman diagnosa login admin (SEMENTARA - hapus setelah selesai)
// Lokasi file: admin/cek.php   ->  buka: http://localhost/toko_sumber_jaya_barokah/admin/cek.php
// =====================================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include __DIR__ . '/../config.php';
$peringatan = ob_get_clean();   // tangkap peringatan PHP dari config.php

header('Content-Type: text/plain; charset=utf-8');

echo "== DIAGNOSA LOGIN ADMIN ==\n\n";

$ada = isset($_COOKIE['login_admin']);
echo "1. Cookie login_admin ada     : " . ($ada ? 'YA' : 'TIDAK (login belum membuat cookie)') . "\n";

if ($ada) {
    $dek = openssl_decrypt($_COOKIE['login_admin'], 'AES-128-CTR', 'admin_ecommerce', 0, '1234567891011121');
    echo "2. Hasil dekripsi cookie      : " . ($dek === false ? 'GAGAL' : 'berhasil') . "\n";
    $id = $dek === false ? '' : explode('hcCTZvFLD7XIchiaMqEka0TLzGgdpsXB', $dek)[0];
    echo "3. ID akun dari cookie        : " . ($id === '' ? '(kosong)' : $id) . "\n";

    $st = $server->prepare('SELECT `id`, `email`, `tipe_akun` FROM `akun` WHERE `id` = ? LIMIT 1');
    $st->bind_param('s', $id);
    $st->execute();
    $akun = $st->get_result()->fetch_assoc();
    if ($akun) {
        echo "4. Akun ditemukan             : id=" . $akun['id'] . ", email=" . $akun['email'] . "\n";
        echo "5. Isi kolom tipe_akun        : [" . $akun['tipe_akun'] . "]  (harus persis: Admin)\n";
    } else {
        echo "4. Akun ditemukan             : TIDAK ADA baris dengan id itu\n";
    }
}

echo "6. Nilai \$akun_adm di config  : " . (isset($akun_adm) ? $akun_adm : '(tidak terdefinisi)') . "\n";
echo "\n";
if ($peringatan !== '') {
    echo "== PERINGATAN/ERROR PHP DARI config.php ==\n" . strip_tags($peringatan) . "\n";
}
echo "\nKirim seluruh teks ini ke Claude. Hapus file cek.php setelah selesai.\n";