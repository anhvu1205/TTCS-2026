<?php
session_start();
require_once '../includes/db.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    echo "not_logged_in";
    exit(); // Dừng ngay lập tức
}

// 2. KIỂM TRA QUYỀN ADMIN (Phải làm trước khi xử lý DB)
if ($_SESSION['user']['role'] === 'ADMIN') {
    echo "admin_block";
    exit(); // Dừng ngay lập tức, không cho phép thêm vào DB
}

// 3. XỬ LÝ DATABASE (Chỉ chạy khi đã qua được 2 bước kiểm tra trên)
if (isset($_POST['product_id'])) {
    $p_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $u_id = $_SESSION['user']['id'];

    $check = mysqli_query($conn, "SELECT * FROM YeuThich WHERE maSP = '$p_id' AND maND = '$u_id'");
    
    if (mysqli_num_rows($check) > 0) {
        // Nếu đã có thì XÓA (Toggle off)
        mysqli_query($conn, "DELETE FROM YeuThich WHERE maSP = '$p_id' AND maND = '$u_id'");
        echo "removed";
    } else {
        // Nếu chưa có thì THÊM (Toggle on)
        mysqli_query($conn, "INSERT INTO YeuThich (maSP, maND) VALUES ('$p_id', '$u_id')");
        echo "added";
    }
}
?>