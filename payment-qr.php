<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user']['id'];


$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : time();
$total = isset($_GET['total']) ? (float)$_GET['total'] : 0;

$bank = "TCB"; 
$account_no = "19072115990014";
$account_name = "TRAN LE ANH VU";
$description = "Thanh toan don hang " . $order_id;

$qr_url = "https://img.vietqr.io/image/{$bank}-{$account_no}-compact2.jpg?amount={$total}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($account_name);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .qr-container { max-width: 500px; margin: 40px auto; }
        .qr-card { border-radius: 20px; border: none; overflow: hidden; }
        .qr-img { width: 100%; max-width: 250px; border: 1px solid #eee; padding: 10px; border-radius: 10px; }
        .info-box { background-color: #f1f3f5; border-radius: 12px; padding: 15px; text-align: left; }
    </style>
</head>
<body>
<div class="container qr-container">
    <div class="card qr-card shadow-lg">
        <div class="card-body p-4 text-center">
            <h4 class="fw-bold text-danger mb-1">XÁC NHẬN CHUYỂN KHOẢN</h4>
            <p class="text-muted small mb-4">Mã đơn hàng: #<?php echo $order_id; ?></p>

            <div class="mb-4 bg-white d-inline-block">
                <img src="<?php echo $qr_url; ?>" alt="QR Thanh Toán" class="qr-img shadow-sm">
            </div>

            <div class="alert alert-danger py-2 mb-4">
                <span class="small d-block">Số tiền cần thanh toán</span>
                <strong class="fs-4"><?php echo number_format($total); ?>₫</strong>
            </div>

            <div class="info-box mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Chủ TK:</span>
                    <span class="fw-bold"><?php echo $account_name; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Ngân hàng:</span>
                    <span class="fw-bold">Techcombank</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Số TK:</span>
                    <span class="fw-bold text-primary"><?php echo $account_no; ?></span>
                </div>
                <hr>
                <div class="text-center">
                    <span class="text-muted small d-block">Nội dung chuyển khoản:</span>
                    <span class="fw-bold text-danger"><?php echo "Thanh toan don hang " . $order_id; ?></span>
                </div>
            </div>

            <a href="shop.php" class="btn btn-dark w-100 py-3 fw-bold mb-2">TÔI ĐÃ CHUYỂN KHOẢN</a>
            <p class="small text-muted mt-3">
                Hệ thống sẽ kiểm tra và xác nhận đơn hàng sau khi nhận được tiền.
            </p>
        </div>
    </div>
</div>
</body>
</html>