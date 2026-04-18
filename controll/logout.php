<?php
session_start();

// 1. Xóa toàn bộ dữ liệu session
session_unset();

// 2. Hủy session
session_destroy();

// 3. Chuyển hướng ra thư mục gốc để vào shop.php
header("Location: ../shop.php");
exit();
?>