<?php
session_start();
require_once 'includes/db.php';

if (isset($_POST['add_to_cart'])) {
    $id = $_POST['id'];
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['name' => $_POST['name'], 'price' => $_POST['price'], 'image' => $_POST['image'], 'quantity' => 1];
    } else {
        $_SESSION['cart'][$id]['quantity']++;
    }
    header("Location: cart.php");
    exit();
}

if (isset($_POST['update_quantity'])) {
    $id = $_POST['update_id'];
    $qty = (int)$_POST['new_quantity'];
    if ($qty > 0) $_SESSION['cart'][$id]['quantity'] = $qty;
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=21">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="shop.php"><img src="assets/img/logo.jpg" class="logo-brand"></a>
            <a href="products.php" class="btn btn-outline-dark btn-sm">Quay lại mua sắm</a>
        </div>
    </nav>
    <div class="container mt-4 mb-5">
        <h3 class="fw-bold text-danger mb-4">Giỏ hàng</h3>
        <div class="row">
            <div class="col-lg-8">
                <div class="cart-card">
                    <?php
                    $total = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $id => $item) {
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                    ?>
                            <div class="row align-items-center mb-3 border-bottom pb-3">
                                <div class="col-md-2 col-4">
                                    <img src="assets/img/<?php echo $item['image']; ?>" class="cart-item-img w-100">
                                </div>
                                <div class="col-md-5 col-8">
                                    <h6 class="fw-bold mb-0"><?php echo $item['name']; ?></h6>
                                    <p class="small text-muted mb-0"><?php echo number_format($item['price']); ?>₫</p>
                                </div>
                                <div class="col-md-3 col-6 text-center">
                                    <form action="cart.php" method="POST" class="d-flex justify-content-center align-items-center">
                                        <input type="hidden" name="update_id" value="<?php echo $id; ?>">
                                        <input type="hidden" name="update_quantity" value="1">
                                        <button name="new_quantity" value="<?php echo $item['quantity'] - 1; ?>" class="btn btn-sm btn-light border">-</button>
                                        <span class="mx-2 fw-bold"><?php echo $item['quantity']; ?></span>
                                        <button name="new_quantity" value="<?php echo $item['quantity'] + 1; ?>" class="btn btn-sm btn-light border">+</button>
                                    </form>
                                </div>
                                <div class="col-md-2 col-6 text-end">
                                    <a href="cart.php?remove=<?php echo $id; ?>" class="text-danger"><i class="fa-solid fa-trash-can"></i></a>
                                </div>
                            </div>
                    <?php }
                    } else {
                        echo '<p class="text-center">Giỏ hàng của bạn đang trống.</p>';
                    } ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cart-card">
                    <h5 class="fw-bold mb-3">Tạm tính</h5>
                    <div class="d-flex justify-content-between mb-4">
                        <span>Tổng tiền:</span>
                        <span class="fw-bold text-danger fs-4"><?php echo number_format($total); ?>₫</span>
                    </div>
                    <a href="checkout.php" class="btn btn-checkout-red py-3 w-100">TIẾN HÀNH THANH TOÁN</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>