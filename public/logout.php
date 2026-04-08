<?php
// public/logout.php — Đăng xuất
session_start();
session_destroy();
header('Location: /barber-spa-website/public/index.php');
exit;
