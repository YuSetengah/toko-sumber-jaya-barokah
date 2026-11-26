<?php
include 'bio_init.php';
if (isset($_SESSION['bio_user'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $server->prepare("SELECT `id`, `password` FROM `akun` WHERE `email`=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['bio_user'] = (int)$user['id'];
        header("Location: dashboard.php");
        exit;
    }
    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - <?php echo e($title_name); ?></title>
    <link rel="icon" href="../assets/icons/<?php echo e($logo); ?>" type="image/svg">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="log_reg">
        <div class="box_log_reg">
            <img class="logo" src="../assets/icons/<?php echo e($logo); ?>" alt="">
            <h1>Masuk</h1>
            <?php if ($error) { ?><div class="alert error"><?php echo e($error); ?></div><?php } ?>
            <?php if (isset($_GET['daftar'])) { ?><div class="alert ok">Akun dibuat. Silakan masuk.</div><?php } ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input" required>
                <button class="button" type="submit">Masuk sekarang</button>
            </form>
            <p class="alt">Belum punya akun? <a href="register.php">Daftar</a></p>
        </div>
    </div>
</body>
</html>
