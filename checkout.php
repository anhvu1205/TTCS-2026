<?php
ob_start();
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$fullname = $_SESSION['user']['name'];

$total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

if (isset($_POST['confirm_order'])) {
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Tiền mặt';
    $order_id = time();

    if ($payment_method === 'Chuyển khoản QR') {
        header("Location: payment-qr.php?order_id=$order_id&total=$total");
        exit();
    } elseif ($payment_method === 'Tiền mặt') {
        echo "<div style='max-width:500px;margin:50px auto;padding:20px;border:1px solid #ccc;border-radius:10px;text-align:center;'>";
        echo "<h3>Đặt hàng thành công!</h3>";
        echo "<p style='font-weight:bold;color:#28a745;'>Thanh toán khi nhận hàng (COD)</p>";
        echo "<p>Tổng tiền: <strong>".number_format($total)."₫</strong></p>";
        echo "<a href='shop.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#28a745;color:white;border-radius:5px;text-decoration:none;'>Quay lại cửa hàng</a>";
        echo "</div>";
        exit();
    } else {
        header("Location: payment-the.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="cart-card shadow-sm p-4 bg-white rounded">
                <h4 class="fw-bold mb-4 text-danger text-center">XÁC NHẬN THANH TOÁN</h4>
                <form action="checkout.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Họ và tên người nhận</label>
                        <input type="text" name="fullname" class="form-control" 
                               value="<?php echo isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : ''; ?>" 
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Địa chỉ chi tiết</label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>

                    <h6 class="fw-bold mb-3">Phương thức thanh toán</h6>
                    <div class="mb-4">
                        <label><input type="radio" name="payment_method" value="Tiền mặt" checked> Thanh toán tiền mặt (COD)</label><br>
                        <label><input type="radio" name="payment_method" value="Thẻ"> Thẻ tín dụng / Ghi nợ</label><br>
                        <label><input type="radio" name="payment_method" value="Chuyển khoản QR"> Chuyển khoản ngân hàng (QR Code)</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-4">
                        <span class="fw-bold fs-5">Tổng cộng:</span>
                        <span class="fw-bold text-danger fs-3"><?php echo number_format($total); ?>₫</span>
                    </div>
                    <button type="submit" name="confirm_order" class="btn btn-danger w-100 py-3">XÁC NHẬN ĐẶT HÀNG</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>