<?php
include 'bio_init.php';
if (isset($_SESSION['bio_user'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $wa = trim($_POST['no_whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $ulang = $_POST['ulang'] ?? '';

    if ($nama === '') {
        $error = 'Nama lengkap wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $ulang) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $cek = $server->prepare("SELECT `id` FROM `akun` WHERE `email`=?");
        $cek->bind_param("s", $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $foto = 'default.png';
            $waktu = date("Y-m-d H:i:s");
            $daftar = 'manual';
            $tipe = 'customer';
            $ins = $server->prepare("INSERT INTO `akun` (`foto`,`nama_lengkap`,`email`,`no_whatsapp`,`password`,`waktu`,`tipe_daftar`,`tipe_akun`) VALUES (?,?,?,?,?,?,?,?)");
            $ins->bind_param("ssssssss", $foto, $nama, $email, $wa, $hash, $waktu, $daftar, $tipe);
            $ins->execute();
            header("Location: login.php?daftar=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - <?php echo e($title_name); ?></title>
    <link rel="icon" href="../assets/icons/<?php echo e($logo); ?>" type="image/svg">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="log_reg">
        <div class="box_log_reg">
            <img class="logo" src="../assets/icons/<?php echo e($logo); ?>" alt="">
            <h1>Buat akun</h1>
            <?php if ($error) { ?><div class="alert error"><?php echo e($error); ?></div><?php } ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>">
                <label for="nama_lengkap">Nama lengkap</label>
                <input class="input" id="nama_lengkap" name="nama_lengkap" required value="<?php echo e($_POST['nama_lengkap'] ?? ''); ?>">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                <label for="no_whatsapp">Nomor WhatsApp</label>
                <input class="input" id="no_whatsapp" name="no_whatsapp" inputmode="tel" value="<?php echo e($_POST['no_whatsapp'] ?? ''); ?>">
                <label for="password">Password (min. 6 karakter)</label>
                <input type="password" id="password" name="password" class="input" required>
                <label for="ulang">Ulangi password</label>
                <input type="password" id="ulang" name="ulang" class="input" required>
                <button class="button" type="submit">Daftar</button>
            </form>
            <p class="alt">Sudah punya akun? <a href="login.php">Masuk</a></p>
        </div>
    </div>
</body>
</html>
