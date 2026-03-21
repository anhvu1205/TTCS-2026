<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    unset($_SESSION['cart']);
    header("Location: shop.php");
    exit();
}

$total = 0;
$subtotal = 0;
$shipping = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }

    $shipping = ($subtotal >= 500000 || $subtotal == 0) ? 0 : 30000;
    $total = $subtotal + $shipping;
}

$order_id = time();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh toán thẻ</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f8f9fa;
}

.payment-container{
max-width:500px;
margin:40px auto;
}

.payment-card{
border-radius:20px;
border:none;
overflow:hidden;
box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

.payment-summary{
background:#f8d7da;
border:1px solid #f5c2c7;
border-radius:10px;
padding:15px;
margin-bottom:20px;
text-align:center;
}

.payment-summary strong{
font-size:24px;
color:#dc3545;
}

.form-control{
border-radius:8px;
}

.btn-pay{
background:#1976d2;
color:white;
border:none;
border-radius:8px;
font-weight:bold;
}

.btn-pay:hover{
background:#0d6efd;
}

.info-box{
background:#f1f3f5;
border-radius:12px;
padding:15px;
margin-top:20px;
text-align:center;
}

</style>

</head>

<body>

<div class="container payment-container">

<div class="card payment-card">

<div class="card-body p-4 text-center">

<h4 class="fw-bold text-danger mb-1">
THANH TOÁN THẺ
</h4>

<p class="text-muted small mb-4">
Mã đơn hàng: #<?php echo $order_id; ?>
</p>

<div class="payment-summary">

<span class="small d-block">
Số tiền cần thanh toán
</span>

<strong>
<?php echo number_format($total); ?>₫
</strong>

</div>

<form method="post" class="text-start">

<div class="mb-3">
<label class="form-label">Số thẻ</label>
<input type="text" name="card_number" class="form-control" placeholder="XXXX XXXX XXXX XXXX" required>
</div>

<div class="row mb-3">

<div class="col">
<label class="form-label">Ngày hết hạn</label>
<input type="text" name="expiry" class="form-control" placeholder="MM/YY" required>
</div>

<div class="col">
<label class="form-label">CVV</label>
<input type="text" name="cvv" class="form-control" placeholder="XXX" required>
</div>

</div>

<div class="mb-3">
<label class="form-label">Tên chủ thẻ</label>
<input type="text" name="card_name" class="form-control" placeholder="Nguyen Van A" required>
</div>

<button type="submit" class="btn btn-pay w-100 py-3">
XÁC NHẬN THANH TOÁN
</button>

</form>

<div class="info-box">

<p class="small text-muted">
Hệ thống sẽ kiểm tra và xác nhận đơn hàng sau khi thanh toán.
</p>

</div>

</div>

</div>

</div>

</body>
</html>
```
