<?php
include '../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami</title>
    <link rel="icon" href="../assets/icons/<?php echo htmlspecialchars($logo); ?>" type="image/svg">
    <link rel="stylesheet" href="../assets/css/help/articles.css">
</head>

<body>
    <!-- HEADER -->
    <?php include '../partials/header.php'; ?>
    <!-- /HEADER -->

    <!-- CONTENT -->
    <div class="width">
        <div class="articles">
            <h1 class="t_articles">Hubungi Kami</h1>
            <p class="p_articles">Hubungi kami melalui DM di social media.</p>
            <div class="box_sosmed_hk">
                <?php
                $select_social_hk = $server->query("SELECT link_social, icon_social, name_social FROM `setting_footer` WHERE link_social != ''");
                while ($data_social_hk = mysqli_fetch_assoc($select_social_hk)) {
                    // Hanya izinkan link http/https supaya aman dari javascript:
                    $link_social = $data_social_hk['link_social'];
                    if (!preg_match('#^https?://#i', $link_social)) {
                        continue;
                    }
                ?>
                    <a href="<?php echo htmlspecialchars($link_social); ?>" class="link" target="_blank" rel="noopener noreferrer">
                        <div class="isi_box_sosmed_hk">
                            <?php echo $data_social_hk['icon_social']; // berisi HTML icon, pastikan data dari admin tepercaya ?>
                            <p><?php echo htmlspecialchars($data_social_hk['name_social']); ?></p>
                        </div>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- /CONTENT -->

    <!-- FOOTER -->
    <?php include '../partials/footer.php'; ?>
    <!-- /FOOTER -->
</body>

</html>