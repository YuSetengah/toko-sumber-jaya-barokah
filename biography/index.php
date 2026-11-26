<?php
include 'bio_init.php';
header("Location: " . (isset($_SESSION['bio_user']) ? "dashboard.php" : "login.php"));
exit;
