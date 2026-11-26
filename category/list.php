<?php
include '../config.php';

// id kategori dari URL: category/list/ID  (diteruskan oleh .htaccess sebagai ?idkategori=ID)
$idkategori = (int) ($_GET['idkategori'] ?? 0);
$kategori = $server->query("SELECT * FROM `kategori` WHERE `id`='$idkategori'");
$kategori_data = mysqli_fetch_assoc($kategori);

if (!$kategori_data) {
    http_response_code(404);
    die('Kategori tidak ditemukan. <a href="' . $url . '">Kembali ke beranda</a>');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
    <title><?php echo htmlspecialchars($kategori_data['nama']); ?></title>
    <link rel="icon" href="<?php echo $url; ?>assets/icons/<?php echo $logo; ?>" type="image/svg">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $url; ?>assets/css/category/list.css">
</head>

<body>
    <!-- HEADER -->
    <?php include '../partials/header.php'; ?>
    <!-- HEADER -->

    <!-- CONTENT -->
    <div class="width">
        <div class="title_kategori">
            <p>Kategori <span><?php echo htmlspecialchars($kategori_data['nama']); ?></span></p>
        </div>
        <div class="grid_produk">
            <?php
            $produk = $server->query("SELECT * FROM `iklan` WHERE `id_kategori`='$idkategori' ORDER BY `iklan`.`id` DESC");
            while ($produk_data = mysqli_fetch_assoc($produk)) {
                $exp_gambar_pd = explode(',', $produk_data['gambar']);
                if ($produk_data['status'] == '') {
            ?>
                    <div class="list_produk">
                        <a href="<?php echo $url; ?>product/view/<?php echo $produk_data['id']; ?>">
                            <img src="<?php echo $url; ?>assets/image/product/<?php echo htmlspecialchars($exp_gambar_pd[0]); ?>">
                            <div class="text_list_produk">
                                <div class="box_judul_list_produk">
                                    <p><?php echo htmlspecialchars($produk_data['judul']); ?></p>
                                </div>
                                <div class="box_harga_list_produk">
                                    <h1><span>Rp</span> <?php echo number_format($produk_data['harga'], 0, ".", "."); ?></h1>
                                    <p><?php echo $produk_data['terjual']; ?> Terjual</p>
                                </div>
                            </div>
                        </a>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
    <!-- CONTENT -->

    <!-- FOOTER -->
    <?php include '../partials/footer.php'; ?>
    <!-- FOOTER -->
</body>

</html>
