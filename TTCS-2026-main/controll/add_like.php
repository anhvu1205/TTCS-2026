<?php
session_start();
require_once '../includes/db.php';

if (isset($_POST['product_id']) && isset($_SESSION['user'])) {
    $p_id = $_POST['product_id'];
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
}else {
    echo "not_logged_in";
}
?>