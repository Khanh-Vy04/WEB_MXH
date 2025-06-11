<?php
require_once 'includes/session.php';

// Đăng xuất
logoutUser();

// Chuyển hướng về trang user
header("Location: user/index.php");
exit();
?> 