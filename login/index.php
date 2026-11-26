<?php
include '../config.php';

// Library Google API (opsional). Jika belum dipasang, tombol "Masuk Dengan Google" disembunyikan
// dan login dengan email + password tetap bisa dipakai.
$file_google = __DIR__ . '/../assets/composer/google-api-client-php-7.4/config.php';
if (file_exists($file_google)) {
    include $file_google;   // mengisi $google_client
}
$google_tersedia = isset($google_client) && is_object($google_client);
$pesan_login = '';

// Membuat cookie login (format sama dengan yang dibaca config.php)
function buat_cookie_login($id_akun)
{
    $idakun = $id_akun . "hcCTZvFLD7XIchiaMqEka0TLzGgdpsXB";
    $ciphering = "AES-128-CTR";
    $options = 0;
    $encryption_iv = '1234567891011121';
    $encryption_key = "ecommerce";
    $encryption = openssl_encrypt($idakun, $ciphering, $encryption_key, $options, $encryption_iv);
    return setcookie("login", $encryption, time() + (86400 * 30), "/");
}

// CEK LOGIN GOOGLE
if ($google_tersedia && isset($_GET['code'])) {
    try {
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);
        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();
            // DATA LOGIN GOOGLE
            $email_google = $data['email'];
            $nama_depan_google = $data['given_name'];
            $nama_belakang_google = $data['family_name'];
            $nama_lengkap_google = trim("$nama_depan_google $nama_belakang_google");
            $time = date("Y-m-d H:i:s");
            $jenis_daftar = "Google";

            // CEK AKUN (prepared statement: nama dan email dari Google bisa memuat tanda kutip)
            $stmt = $server->prepare("SELECT `id` FROM `akun` WHERE `email` = ?");
            $stmt->bind_param('s', $email_google);
            $stmt->execute();
            $akun_user_data = $stmt->get_result()->fetch_assoc();

            // HASIL CEK AKUN
            if ($akun_user_data) {
                $id_login = $akun_user_data['id'];
            } else {
                // akun baru; kolom wajib lain diisi kosong (akun Google tidak punya password)
                $foto = 'user.png';
                $kosong = '';
                $stmt = $server->prepare(
                    "INSERT INTO `akun` (`foto`, `nama_lengkap`, `email`, `no_whatsapp`, `password`, `waktu`, `tipe_daftar`, `tipe_akun`)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('ssssssss', $foto, $nama_lengkap_google, $email_google, $kosong, $kosong, $time, $jenis_daftar, $kosong);
                $stmt->execute();
                $id_login = $server->insert_id;
            }

            if (buat_cookie_login($id_login)) {
                echo '<script>window.location.href="' . $url . '";</script>';
                exit;
            }
        } else {
            $pesan_login = 'Login dengan Google gagal. Silakan coba lagi.';
        }
    } catch (Exception $e) {
        $pesan_login = 'Login dengan Google gagal. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
    <title>Login</title>
    <link rel="icon" href="../assets/icons/<?php echo $logo; ?>" type="image/svg">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login-register/index.css">
</head>

<body>
    <!-- CONTENT -->
    <div class="log_reg">
        <center><a href="<?php echo $url; ?>"><img src="../assets/icons/<?php echo $logo; ?>" alt=""></a></center>
        <div class="box_log_reg">
            <h1>Masuk</h1>
            <h4>Belum punya akun? <a href="<?php echo $url; ?>register/">Daftar Sekarang</a></h4>
            <?php if ($google_tersedia) { ?>
                <div class="log_reg_social">
                    <a href="<?php echo $google_client->createAuthUrl(); ?>">
                        <div class="isi_log_reg_social">
                            <img src="../assets/icons/sosmed/google.svg" alt="">
                            <p>Masuk Dengan Google</p>
                        </div>
                    </a>
                </div>
                <div class="line_log_reg"></div>
                <center>
                    <p class="text_line_log_reg">Atau masuk dengan</p>
                </center>
            <?php } ?>
            <div class="box_form_log_reg">
                <div class="form_log_reg">
                    <p id="p_email"></p>
                    <input type="text" placeholder="Email" class="input" id="email">
                </div>
                <div class="form_log_reg">
                    <p id="p_password"></p>
                    <input type="password" placeholder="Password" class="input" id="password">
                </div>
            </div>
            <div id="masuk_button">
                <div class="button" id="masuk">
                    <p>Masuk Sekarang</p>
                </div>
            </div>
            <div id="masuk_loading">
                <div class="button">
                    <img src="../assets/icons/loading-w.svg" id="loading_masuk">
                </div>
            </div>
        </div>
    </div>
    <div class="res" id="res"><?php if ($pesan_login) { echo '<p>' . htmlspecialchars($pesan_login) . '</p>'; } ?></div>
    <!-- CONTENT -->

    <!-- JS -->
    <script src="../assets/js/login/index.js"></script>
    <!-- JS -->
</body>

</html>
