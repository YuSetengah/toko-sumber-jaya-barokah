<?php
// Memakai config.php toko (koneksi $server, $url, $logo, session_start)
include '../config.php';

// Buat tabel biography otomatis di database toko (aman diulang)
$server->query("CREATE TABLE IF NOT EXISTS `biography` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_user` INT NOT NULL,
    `tempat_lahir` VARCHAR(100) DEFAULT '',
    `tanggal_lahir` DATE NULL,
    `alamat` VARCHAR(255) DEFAULT '',
    `pekerjaan` VARCHAR(100) DEFAULT '',
    `tentang` TEXT,
    `diubah` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_user` (`id_user`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function cek_csrf() {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman.');
    }
}
