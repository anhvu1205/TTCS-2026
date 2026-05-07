<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) exit();

$user_id = (int)($_SESSION['user']['id'] ?? $_SESSION['user']['maND'] ?? 0);
$review_id = (int)$_POST['review_id'];
$content = trim($_POST['content'] ?? '');

if ($review_id <= 0 || $content === '') {
    header('Location: detail.php?id=' . ($_POST['product_id'] ?? ''));
    exit();
}

$content_safe = mysqli_real_escape_string($conn, $content);

// Lấy review
$res = mysqli_query($conn, "
    SELECT * FROM ProductReviews
    WHERE id = $review_id AND user_id = $user_id
    LIMIT 1
");

if (!$res || mysqli_num_rows($res) == 0) exit();

$review = mysqli_fetch_assoc($res);

// Check số lần edit (chỉ được sửa 1 lần)
if ($review['edit_count'] >= 1) {
    die("Bạn chỉ được sửa đánh giá 1 lần duy nhất!");
}

// Update
mysqli_query($conn, "
    UPDATE ProductReviews
    SET content = '$content_safe', edit_count = edit_count + 1
    WHERE id = $review_id
");

header("Location: detail.php?id=" . $review['product_id']);
