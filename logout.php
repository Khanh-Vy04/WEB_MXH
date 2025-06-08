<?php
require_once 'includes/session.php';

// Đăng xuất
logoutUser();

// Chuyển hướng về trang đăng nhập
header("Location: index.php");
exit();
?> 