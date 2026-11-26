<?php
include 'bio_init.php';
if (!isset($_SESSION['bio_user'])) { header("Location: login.php"); exit; }
$uid = (int)$_SESSION['bio_user'];

$q = $server->prepare("SELECT * FROM `akun` WHERE `id`=?");
$q->bind_param("i", $uid);
$q->execute();
$akun = $q->get_result()->fetch_assoc();
if (!$akun) { header("Location: logout.php"); exit; }

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $del = $server->prepare("DELETE FROM `biography` WHERE `id_user`=?");
        $del->bind_param("i", $uid);
        $del->execute();
        $pesan = 'Biografi dihapus.';
    } else {
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $tempat = trim($_POST['tempat_lahir'] ?? '');
        $tgl = $_POST['tanggal_lahir'] ?? '';
        $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) ? $tgl : null;
        $alamat = trim($_POST['alamat'] ?? '');
        $kerja = trim($_POST['pekerjaan'] ?? '');
        $tentang = trim($_POST['tentang'] ?? '');

        if ($nama === '') {
            $pesan = 'Nama lengkap wajib diisi.';
        } else {
            // nama lengkap disimpan di tabel akun
            $up = $server->prepare("UPDATE `akun` SET `nama_lengkap`=? WHERE `id`=?");
            $up->bind_param("si", $nama, $uid);
            $up->execute();
            $akun['nama_lengkap'] = $nama;

            $sql = "INSERT INTO `biography` (`id_user`,`tempat_lahir`,`tanggal_lahir`,`alamat`,`pekerjaan`,`tentang`)
                    VALUES (?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE `tempat_lahir`=VALUES(`tempat_lahir`), `tanggal_lahir`=VALUES(`tanggal_lahir`),
                    `alamat`=VALUES(`alamat`), `pekerjaan`=VALUES(`pekerjaan`), `tentang`=VALUES(`tentang`)";
            $st = $server->prepare($sql);
            $st->bind_param("isssss", $uid, $tempat, $tgl, $alamat, $kerja, $tentang);
            $st->execute();
            $pesan = 'Biografi disimpan.';
        }
    }
}

$b = $server->prepare("SELECT * FROM `biography` WHERE `id_user`=?");
$b->bind_param("i", $uid);
$b->execute();
$bio = $b->get_result()->fetch_assoc();
$v = function ($k) use ($bio) { return e($bio[$k] ?? ($_POST[$k] ?? '')); };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biografi Saya - <?php echo e($title_name); ?></title>
    <link rel="icon" href="../assets/icons/<?php echo e($logo); ?>" type="image/svg">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <span><?php echo e($akun['email']); ?></span>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="wrap">
        <h1>Biografi saya</h1>
        <?php if ($pesan) { ?><div class="alert ok"><?php echo e($pesan); ?></div><?php } ?>

        <form method="post" class="card">
            <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>">
            <label for="nama_lengkap">Nama lengkap</label>
            <input class="input" id="nama_lengkap" name="nama_lengkap" required value="<?php echo e($akun['nama_lengkap']); ?>">

            <div class="row">
                <div>
                    <label for="tempat_lahir">Tempat lahir</label>
                    <input class="input" id="tempat_lahir" name="tempat_lahir" value="<?php echo $v('tempat_lahir'); ?>">
                </div>
                <div>
                    <label for="tanggal_lahir">Tanggal lahir</label>
                    <input class="input" type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $v('tanggal_lahir'); ?>">
                </div>
            </div>

            <label for="alamat">Alamat</label>
            <input class="input" id="alamat" name="alamat" value="<?php echo $v('alamat'); ?>">

            <label for="pekerjaan">Pekerjaan</label>
            <input class="input" id="pekerjaan" name="pekerjaan" value="<?php echo $v('pekerjaan'); ?>">

            <label for="tentang">Tentang saya</label>
            <textarea class="input" id="tentang" name="tentang" rows="5"><?php echo $v('tentang'); ?></textarea>

            <button class="button" type="submit">Simpan biografi</button>
        </form>

        <?php if ($bio) { ?>
            <form method="post" onsubmit="return confirm('Hapus biografi ini? Tindakan ini tidak bisa dibatalkan.')">
                <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>">
                <input type="hidden" name="aksi" value="hapus">
                <button class="button danger" type="submit">Hapus biografi</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
