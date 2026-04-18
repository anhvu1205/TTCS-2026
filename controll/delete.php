<?php
require_once '../includes/db.php';

$id = $_GET['id'];

$sql = "DELETE FROM SanPham WHERE maSP = $id";
mysqli_query($conn, $sql);

header("Location: admin.php?tab=products");
exit();
?>