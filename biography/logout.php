<?php
session_start();
unset($_SESSION['bio_user']);
header("Location: login.php");
exit;
