<header class="header">
    <div class="header_box">
        <a href="<?php echo $url; ?>" class="header_logo">
            <img src="<?php echo $url; ?>assets/icons/<?php echo $logo; ?>" alt="logo">
            <span><?php echo htmlspecialchars($title_name); ?></span>
        </a>

        <form class="header_search" action="<?php echo $url; ?>search.php" method="get">
            <input type="search" name="q" placeholder="<?php echo htmlspecialchars($placeholder_search); ?>">
            <button type="submit"><i class="ri-search-line"></i></button>
        </form>

        <div class="header_menu">
            <a href="<?php echo $url; ?>cart" class="header_icon">
                <i class="ri-shopping-cart-2-line"></i>
                <?php if (isset($cek_cart_header) && $cek_cart_header > 0) { ?>
                    <span class="badge"><?php echo $cek_cart_header; ?></span>
                <?php } ?>
            </a>
            <a href="<?php echo $url; ?>notification" class="header_icon">
                <i class="ri-notification-3-line"></i>
                <?php if (isset($cek_notif_header) && $cek_notif_header > 0) { ?>
                    <span class="badge"><?php echo $cek_notif_header; ?></span>
                <?php } ?>
            </a>
            <?php if (isset($profile) && $profile) { ?>
                <a href="<?php echo $url; ?>profile" class="header_user">
                    <i class="ri-user-3-line"></i>
                    <span><?php echo htmlspecialchars($profile['nama_lengkap']); ?></span>
                </a>
            <?php } else { ?>
                <a href="<?php echo $url; ?>login" class="header_user">
                    <i class="ri-login-box-line"></i>
                    <span>Masuk</span>
                </a>
            <?php } ?>
        </div>
    </div>
</header>
