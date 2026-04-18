<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop_ptit";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
