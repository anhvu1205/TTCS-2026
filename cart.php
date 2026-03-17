<?php
session_start();
require_once 'includes/db.php';

// --- LOGIC THÊM SẢN PHẨM (MỚI BỔ SUNG) ---
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'M'; // Mặc định nếu không chọn
    $color = isset($_POST['color']) ? $_POST['color'] : 'Trắng';

    // Truy vấn lại DB để lấy thông tin chính xác (tên, giá, ảnh)
    $sql = "SELECT maSP, ten, gia, hinhAnh FROM SanPham WHERE maSP = '$id'";
    $res = mysqli_query($conn, $sql);
    $p = mysqli_fetch_assoc($res);

    if ($p) {
        // Tạo một key duy nhất cho sản phẩm kèm size/màu để không bị ghi đè nếu cùng ID nhưng khác size
        $cart_key = $id . "_" . $size . "_" . $color;

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'id' => $id,
                'name' => $p['ten'],
                'price' => $p['gia'],
                'image' => $p['hinhAnh'],
                'quantity' => $qty,
                'size' => $size,
                'color' => $color
            ];
        }
    }
    // Sau khi thêm xong, chuyển hướng để tránh việc F5 gây thêm trùng lặp
    header("Location: cart.php");
    exit();
}

// Logic xóa sản phẩm
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_remove]);
    header("Location: cart.php");
    exit();
}

// Logic cập nhật số lượng (AJAX hoặc Form submit)
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id]['quantity'] = $qty;
    }
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';

// Khởi tạo các giá trị tính toán
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Logic vận chuyển giống bản React
$shipping = ($subtotal >= 500000 || $subtotal == 0) ? 0 : 30000;
$total = $subtotal + $shipping;
?>

<main class="cart-page-wrapper pb-5">
    <div class="container-fluid px-lg-5 max-w-6xl mx-auto min-h-screen">
        <div class="py-10 border-bottom mb-5">
            <h1 class="display-6 fw-light">
                Giỏ hàng <span class="text-muted h4">(<?php echo count($cartItems); ?>)</span>
            </h1>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-bag-shopping display-1 text-light mb-4"></i>
                <h2 class="h4 fw-light mb-3">Giỏ hàng của bạn đang trống</h2>
                <p class="text-muted mb-5">Hãy khám phá và chọn sản phẩm yêu thích!</p>
                <a href="products.php" class="btn btn-dark rounded-0 px-5 py-3 tracking-widest small">
                    MUA SẮM NGAY <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="row g-5">
                <div class="col-lg-7">
                    <form action="cart.php" method="POST" id="cartForm">
                        <?php foreach ($cartItems as $id => $item): ?>
                            <div class="cart-item-row d-flex gap-4 py-4 border-bottom position-relative">
                                <div class="cart-img-box bg-light overflow-hidden" style="width: 100px; height: 130px;">
                                    <img src="<?php echo $item['image']; ?>" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div class="cart-info-box flex-grow-1 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between">
                                            <a href="detail.php?id=<?php echo $id; ?>" class="text-dark text-decoration-none fw-bold small">
                                                <?php echo $item['name']; ?>
                                            </a>
                                            <a href="cart.php?remove=<?php echo $id; ?>" class="text-muted hover-red"><i class="fa-solid fa-trash-can small"></i></a>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <?php if(isset($item['size'])): ?>
                                                <span class="badge-outline">Size <?php echo $item['size']; ?></span>
                                            <?php endif; ?>
                                            <?php if(isset($item['color'])): ?>
                                                <span class="badge-outline"><?php echo $item['color']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="qty-control-minimal d-flex align-items-center border">
                                            <button type="button" onclick="updateQty('<?php echo $id; ?>', -1)" class="btn btn-sm">-</button>
                                            <input type="number" name="quantity[<?php echo $id; ?>]" id="qty-<?php echo $id; ?>" 
                                                   value="<?php echo $item['quantity']; ?>" class="text-center border-0 small fw-bold" style="width: 40px;">
                                            <button type="button" onclick="updateQty('<?php echo $id; ?>', 1)" class="btn btn-sm">+</button>
                                        </div>
                                        <span class="fw-bold small"><?php echo number_format($item['price'] * $item['quantity']); ?>₫</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" name="update_cart" class="btn btn-outline-dark btn-sm rounded-0 mt-3 d-none">Cập nhật giỏ hàng</button>
                    </form>

                    <?php if ($subtotal < 500000): ?>
                        <div class="shipping-notice mt-4 p-3 bg-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-truck text-gold"></i>
                            <span class="small text-muted">Thêm <strong><?php echo number_format(500000 - $subtotal); ?>₫</strong> để được miễn phí vận chuyển!</span>
                        </div>
                    <?php else: ?>
                        <div class="shipping-notice mt-4 p-3 bg-success-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-truck text-success"></i>
                            <span class="small text-success fw-bold">Bạn được miễn phí vận chuyển! 🎉</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-5">
                    <div class="summary-card p-4 p-md-5 bg-light sticky-top" style="top: 100px;">
                        <h3 class="small fw-bold tracking-widest text-uppercase mb-4">Tóm tắt đơn hàng</h3>
                        
                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-muted">Tạm tính</span>
                            <span><?php echo number_format($subtotal); ?>₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 small">
                            <span class="text-muted">Vận chuyển</span>
                            <span class="<?php echo $shipping == 0 ? 'text-success fw-bold' : ''; ?>">
                                <?php echo $shipping == 0 ? 'Miễn phí' : number_format($shipping).'₫'; ?>
                            </span>
                        </div>

                        <div class="input-group mb-4">
                            <input type="text" class="form-control rounded-0 small border-secondary" placeholder="Mã giảm giá">
                            <button class="btn btn-outline-dark rounded-0 small px-4">ÁP DỤNG</button>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between mb-4">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="h5 fw-bold"><?php echo number_format($total); ?>₫</span>
                        </div>

                        <a href="checkout.php" class="btn btn-dark w-100 py-3 rounded-0 fw-bold tracking-widest small mb-4">
                            TIẾN HÀNH THANH TOÁN <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>

                        <div class="d-flex justify-content-center gap-4 text-muted" style="font-size: 10px;">
                            <span><i class="fa-solid fa-rotate-left me-1"></i> Đổi trả 7 ngày</span>
                            <span>|</span>
                            <span><i class="fa-solid fa-shield-check me-1"></i> Giao hàng nhanh</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function updateQty(id, delta) {
    const input = document.getElementById('qty-' + id);
    let newVal = parseInt(input.value) + delta;
    if (newVal < 1) newVal = 1;
    input.value = newVal;
    // Tự động submit form để cập nhật giá tiền (Giống layout React)
    document.getElementById('cartForm').submit();
}
</script>

<?php include 'includes/footer.php'; ?>