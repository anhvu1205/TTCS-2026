<?php
require_once 'includes/db.php'; // 1. Mở cửa kho

// 2. Kiểm tra xem trên đường dẫn có ID không? (Ví dụ: detail.php?id=5)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 3. Lấy đúng sản phẩm đó ra
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);

    // Nếu không tìm thấy sản phẩm (Ví dụ id linh tinh) thì quay về trang chủ
    if (!$product) {
        header("Location: shop.php");
        exit();
    }
} else {
    // Không có ID thì cũng quay về
    header("Location: shop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title><?php echo $product['name']; ?> - AnhVuShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css"> </head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">SHOP</a>
            <a href="shop.php" class="btn btn-outline-dark">Quay lại Shop</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            
            <div class="col-md-6 mb-4">
                <img src="assets/img/<?php echo $product['image']; ?>" class="img-fluid w-100 shadow-sm" alt="">
            </div>

            <div class="col-md-6">
                <h2 class="fw-bold"><?php echo $product['name']; ?></h2>
                <h4 class="text-danger fw-bold mb-3"><?php echo number_format($product['price']); ?>₫</h4>
                
                <p>Mô tả: Chất liệu cotton thoáng mát, form rộng chuẩn streetwear. Hàng chính hãng EngDzu$$Shop.</p>
                
                <div class="mb-3">
                    <label class="fw-bold">Chọn Size:</label>
                    <select class="form-select w-50 mt-1">
                        <option>Size M</option>
                        <option>Size L</option>
                        <option>Size XL</option>
                    </select>
                </div>

                <form action="cart.php" method="POST">
    
    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
    <input type="hidden" name="name" value="<?php echo $product['name']; ?>">
    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
    <input type="hidden" name="image" value="<?php echo $product['image']; ?>">

    <button type="submit" name="add_to_cart" class="btn btn-dark btn-lg w-100 rounded-0 mt-3">
        THÊM VÀO GIỎ HÀNG
    </button>

</form>
            </div>

        </div>
    </div>

</body>
</html>