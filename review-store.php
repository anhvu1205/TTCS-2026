<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$content = trim($_POST['content'] ?? '');

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

if ($product_id <= 0 || $content === '') {
    header("Location: detail.php?id=" . $product_id . "#product-reviews");
    exit();
}

$user_id = (int)($_SESSION['user']['id'] ?? $_SESSION['user']['maND'] ?? 0);
$user_name = mysqli_real_escape_string($conn, $_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Khách hàng');
$content_safe = mysqli_real_escape_string($conn, $content);

// Kiểm tra khách đã từng mua sản phẩm này chưa.
$is_purchased = 0;
$res_kh = mysqli_query($conn, "SELECT maKH FROM KhachHang WHERE maND = $user_id LIMIT 1");
if ($res_kh && mysqli_num_rows($res_kh) > 0) {
    $kh = mysqli_fetch_assoc($res_kh);
    $maKH = (int)$kh['maKH'];

    $check = mysqli_query($conn, "
        SELECT dh.maDH
        FROM DonHang dh
        JOIN ChiTietDonHang ct ON dh.maDH = ct.maDH
        WHERE dh.maKH = $maKH
          AND ct.maSP = $product_id
          AND dh.trangThai IN ('HOAN_TAT', 'DA_GIAO_HANG')
        LIMIT 1
    ");

    if ($check && mysqli_num_rows($check) > 0) {
        $is_purchased = 1;
    }
}

// KHÔNG cho phép review nếu chưa mua hàng
if ($is_purchased == 0) {
    header("Location: detail.php?id=" . $product_id . "&error=not_purchased");
    exit();
}

// Check user đã review chưa
$check_review = mysqli_query($conn, "
    SELECT id FROM ProductReviews 
    WHERE product_id = $product_id AND user_id = $user_id 
    LIMIT 1
");

if ($check_review && mysqli_num_rows($check_review) > 0) {
    // đã review rồi → không cho thêm
    header("Location: detail.php?id=" . $product_id . "&error=already_reviewed");
    exit();
}

mysqli_query($conn, "
    INSERT INTO ProductReviews (product_id, user_id, user_name, rating, content, is_purchased, status)
    VALUES ($product_id, $user_id, '$user_name', $rating, '$content_safe', $is_purchased, 'visible')
");

header("Location: detail.php?id=" . $product_id . "#product-reviews");
exit();
