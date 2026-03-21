<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán Thẻ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .payment-container {
            max-width: 450px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
        }
        .form-control { border-radius: 8px; }
        .btn-pay {
            background-color: #007bff;
            color: #fff;
            border-radius: 8px;
        }
        .info-text { font-size: 0.9rem; color: #555; margin-top: 15px; }
    </style>
</head>
<body>
<div class="payment-container">
    <h4 class="mb-3">Thanh toán Thẻ tín dụng / Ghi nợ</h4>
    <p>Tổng tiền: <strong>1.500.000₫</strong></p>
    <form action="shop.php" method="get">
        <div class="mb-3 text-start">
            <label class="form-label">Số thẻ</label>
            <input type="text" class="form-control" placeholder="XXXX XXXX XXXX XXXX">
        </div>
        <div class="mb-3 row">
            <div class="col">
                <label class="form-label">Ngày hết hạn</label>
                <input type="text" class="form-control" placeholder="MM/YY">
            </div>
            <div class="col">
                <label class="form-label">CVV</label>
                <input type="text" class="form-control" placeholder="XXX">
            </div>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label">Tên chủ thẻ</label>
            <input type="text" class="form-control" placeholder="Nguyen Van A">
        </div>
        <button type="submit" class="btn btn-pay w-100 py-2">Thanh toán</button>
    </form>
</div>
</body>
</html>