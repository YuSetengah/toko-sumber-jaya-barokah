<footer class="footer">
    <div class="footer_box">
        <div class="footer_brand">
            <h3><?php echo htmlspecialchars($title_name); ?></h3>
            <p>Belanja mudah, aman, dan terpercaya.</p>
        </div>
        <div class="footer_social">
            <?php
            $footer_social = $server->query("SELECT * FROM `setting_footer` WHERE `status_social` IN ('active','aktif')");
            while ($sosmed = mysqli_fetch_assoc($footer_social)) {
                // hanya tautan http/https yang ditampilkan (aman dari javascript:)
                if (!preg_match('#^https?://#i', $sosmed['link_social'])) {
                    continue;
                }
            ?>
                <a href="<?php echo htmlspecialchars($sosmed['link_social']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo htmlspecialchars($sosmed['name_social']); ?>">
                    <?php echo $sosmed['icon_social']; // berisi tag ikon, diisi admin lewat halaman Pengaturan ?>
                </a>
            <?php
            }
            ?>
        </div>
    </div>
    <p class="footer_copy">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($title_name); ?>. All rights reserved.</p>
</footer>
