<?php
ob_start();
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

if (isset($_POST['confirm_order'])) {
    $user_id = $_SESSION['user_id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $payment_method = trim($_POST['payment_method']);

    $sql_order = "INSERT INTO orders (user_id, fullname, phone, address, total_amount, payment_method) 
                  VALUES ('$user_id', '$fullname', '$phone', '$address', '$total', '$payment_method')";

    if (mysqli_query($conn, $sql_order)) {
        $order_id = mysqli_insert_id($conn);
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $price = $item['price'];
            $qty = $item['quantity'];
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                 VALUES ('$order_id', '$product_id', '$qty', '$price')");
        }

        unset($_SESSION['cart']);

        if ($payment_method === 'Chuyển khoản QR') {
            header("Location: payment-qr.php?order_id=$order_id&total=$total");
            exit();
        } else {
            echo "<script>alert('Đặt hàng thành công!'); window.location.href='shop.php';</script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=26">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="cart-card shadow-sm">
                    <h4 class="fw-bold mb-4 text-danger text-center">XÁC NHẬN THANH TOÁN</h4>
                    <form action="checkout.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Họ và tên người nhận</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo $_SESSION['username']; ?>" required>
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
                        <div class="payment-methods mb-4">
                            <label class="w-100">
                                <input type="radio" name="payment_method" value="Tiền mặt" checked>
                                <div class="method-item">
                                    <i class="fa-solid fa-money-bill-1-wave method-icon text-success"></i>
                                    <span class="method-text">Thanh toán tiền mặt (COD)</span>
                                </div>
                            </label>

                            <label class="w-100">
                                <input type="radio" name="payment_method" value="Thẻ">
                                <div class="method-item">
                                    <i class="fa-solid fa-credit-card method-icon text-primary"></i>
                                    <span class="method-text">Thẻ tín dụng / Ghi nợ</span>
                                </div>
                            </label>

                            <label class="w-100">
                                <input type="radio" name="payment_method" value="Chuyển khoản QR">
                                <div class="method-item">
                                    <i class="fa-solid fa-qrcode method-icon text-dark"></i>
                                    <span class="method-text">Chuyển khoản ngân hàng (QR Code)</span>
                                </div>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-4">
                            <span class="fw-bold fs-5">Tổng cộng:</span>
                            <span class="fw-bold text-danger fs-3"><?php echo number_format($total); ?>₫</span>
                        </div>
                        <button type="submit" name="confirm_order" class="btn btn-checkout-red py-3">XÁC NHẬN ĐẶT HÀNG</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>