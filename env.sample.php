<?php
// Salin file ini menjadi env.php, lalu isi sesuai akun hosting Anda.
// env.php berisi kata sandi database: jangan dibagikan dan jangan diunggah ke GitHub.
return [
    'url'     => 'https://alamat-situs-anda.com/',    // alamat situs, wajib diakhiri tanda /
    'db_host' => 'ISI_DARI_PANEL_HOSTING',            // contoh: sql123.namahosting.com
    'db_user' => 'ISI_DARI_PANEL_HOSTING',
    'db_pass' => 'ISI_DARI_PANEL_HOSTING',
    'db_name' => 'ISI_DARI_PANEL_HOSTING',
    'debug'   => true,    // true = tampilkan error (untuk uji pertama). Ubah ke false setelah situs berjalan.
];
