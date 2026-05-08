<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/discount_functions.php';

// --- LOGIC THÊM SẢN PHẨM ---
if (isset($_POST['add_to_cart'])) {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user'])) {
        echo "not_logged_in";
        exit();
    }
    // CHẶN ADMIN
    if ($_SESSION['user']['role'] === 'ADMIN') {
        echo "admin_block";
        exit();
    }
    $id = $_POST['id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'M';
    $color = isset($_POST['color']) ? $_POST['color'] : 'Trắng';

    $sql = "SELECT maSP, ten, gia, hinhAnh, soLuong FROM SanPham WHERE maSP = '$id'";
    $res = mysqli_query($conn, $sql);
    $p = mysqli_fetch_assoc($res);

    if ($p && $p['soLuong'] > 0) {
        $cart_key = $id . "_" . $size . "_" . $color;

        $current_in_cart = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                if ($item['id'] == $id) $current_in_cart += $item['quantity'];
            }
        }

        if (($current_in_cart + $qty) <= $p['soLuong']) {
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
                    'color' => $color,
                    'stock' => $p['soLuong']
                ];
            }
        }
    }
    unset($_SESSION['discount']);

    header("Location: cart.php");
    exit();
}

// Logic xóa sản phẩm
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_remove]);
    unset($_SESSION['discount']);

    header("Location: cart.php");
    exit();
}

// Logic cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
    unset($_SESSION['discount']);

    header("Location: cart.php");
    exit();
}

// Logic mã giảm giá
$discountMessage = '';
$discountMessageType = '';

if (isset($_POST['apply_discount'])) {
    $result = applyDiscountCode($conn, $_POST['discount_code'] ?? '');
    $discountMessage = $result['message'];
    $discountMessageType = $result['success'] ? 'success' : 'danger';
}

if (isset($_POST['remove_discount'])) {
    removeDiscountCode();
    $discountMessage = 'Đã xóa mã giảm giá.';
    $discountMessageType = 'secondary';
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = getCartSubtotal();
$discountAmount = getAppliedDiscountAmount();

// Logic vận chuyển
$shipping = ($subtotal >= 500000 || $subtotal == 0) ? 0 : 30000;

// tổng sau giảm + ship
$total = max(($subtotal - $discountAmount), 0) + $shipping;
$progress = min(($subtotal / 500000) * 100, 100);

include 'includes/header.php';
?>

<main class="cart-main-container pb-5" style="background-color: #FAF7F2; min-height: 100vh;">
    <div class="container max-w-6xl mx-auto px-4 lg:px-8">
        <div class="py-10 border-bottom mb-5">
            <h1 class="text-3xl lg:text-4xl font-light" style="font-family: 'DM Sans', serif; color: #1A1A1A;">
                Giỏ hàng <span class="text-lg ms-2" style="color: #8C8279;">(<?php echo count($cartItems); ?> sản phẩm)</span>
            </h1>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <div class="w-24 h-24 rounded-circle d-flex align-items-center justify-content-center mb-4" style="background-color: #EDE8DF; width: 100px; height: 100px;">
                    <i class="fa-solid fa-cart-shopping fs-2" style="color: #C4622D;"></i>
                </div>
                <h2 class="h4 fw-light mb-2">Giỏ hàng trống</h2>
                <p class="text-muted mb-4">Hãy khám phá và chọn sản phẩm yêu thích!</p>
                <a href="products.php" class="btn text-white px-5 py-3 rounded-pill fw-bold" style="background-color: #C4622D;">
                    Mua sắm ngay <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4 lg-g-5">
                <div class="col-lg-7">
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($cartItems as $id => $item): ?>
                            <div class="cart-item-modern d-flex gap-4 p-4 rounded-4" style="background-color: #EDE8DF;">
                                <div class="cart-img-wrapper" style="width: 90px; height: 115px; border-radius: 12px; overflow: hidden; background-color: #FAF7F2;">
                                    <img src="<?php echo $item['image']; ?>" class="w-100 h-100 object-fit-cover">
                                </div>

                                <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="min-w-0">
                                            <a href="detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none fw-bold small text-dark d-block mb-2">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                            <div class="d-flex gap-2">
                                                <span class="badge-modern">Size <?php echo htmlspecialchars($item['size']); ?></span>
                                                <span class="badge-modern"><?php echo htmlspecialchars($item['color']); ?></span>
                                            </div>
                                        </div>
                                        <a href="cart.php?remove=<?php echo urlencode($id); ?>" class="text-danger opacity-50 hover-opacity-100"><i class="fa-solid fa-trash-can"></i></a>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="qty-pill-modern d-flex align-items-center rounded-3 overflow-hidden" style="background-color: #FAF7F2;">
                                            <button type="button" onclick="updateQty('<?php echo $id; ?>', -1)" class="btn btn-sm px-3">-</button>
                                            <span class="px-2 small fw-bold" id="qty-<?php echo $id; ?>"><?php echo $item['quantity']; ?></span>
                                            <button type="button" onclick="updateQty('<?php echo $id; ?>', 1)" class="btn btn-sm px-3">+</button>
                                        </div>
                                        <span class="fw-bold" style="color: #C4622D;"><?php echo number_format($item['price'] * $item['quantity']); ?>₫</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="progress-card p-4 rounded-4 mt-2" style="background-color: #EDE8DF;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-truck" style="color: <?php echo $subtotal >= 500000 ? '#5C6650' : '#C4622D'; ?>"></i>
                                    <span class="small fw-bold">
                                        <?php echo ($subtotal >= 500000) ? "🎉 Bạn được miễn phí vận chuyển!" : "Thêm " . number_format(500000 - $subtotal) . "₫ để miễn phí ship"; ?>
                                    </span>
                                </div>
                                <span class="small fw-bold" style="color: #C4622D;"><?php echo round($progress); ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #D4CEBE; border-radius: 10px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?php echo $progress; ?>%; background-color: <?php echo $subtotal >= 500000 ? '#5C6650' : '#C4622D'; ?>; border-radius: 10px; transition: width 0.6s ease;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="summary-card p-5 rounded-4 sticky-top" style="top: 100px; background-color: #EDE8DF;">
                        <h3 class="small fw-bold tracking-widest text-uppercase mb-5">Tóm tắt đơn hàng</h3>

                        <div class="d-flex justify-content-between mb-3 small">
                            <span style="color: #8C8279;">Tạm tính</span>
                            <span class="fw-bold"><?php echo number_format($subtotal); ?>₫</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 small">
                            <span style="color: #8C8279;">Giảm giá</span>
                            <span class="fw-bold" style="color: #5C6650;">
                                -<?php echo number_format($discountAmount); ?>₫
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-4 small border-bottom pb-4">
                            <span style="color: #8C8279;">Vận chuyển</span>
                            <span class="fw-bold <?php echo $shipping == 0 ? 'text-success' : ''; ?>">
                                <?php echo $shipping == 0 ? 'Miễn phí' : number_format($shipping) . '₫'; ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <form method="POST">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="discount_code"
                                        class="form-control border-0 rounded-start-3 p-3 small"
                                        placeholder="Mã giảm giá"
                                        value="<?php echo isset($_SESSION['discount']['code']) ? htmlspecialchars($_SESSION['discount']['code']) : ''; ?>"
                                        style="background-color: #FAF7F2;">
                                    <button type="submit" name="apply_discount" class="btn btn-dark rounded-end-3 px-4 fw-bold small">
                                        ÁP DỤNG
                                    </button>
                                </div>
                            </form>
                        </div>

                        <?php if (!empty($_SESSION['discount']['code'])): ?>
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="small">
                                    Đã áp mã: <strong style="color:#C4622D;"><?php echo htmlspecialchars($_SESSION['discount']['code']); ?></strong>
                                </span>
                                <form method="POST" class="m-0">
                                    <button type="submit" name="remove_discount" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                        Xóa mã
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($discountMessage)): ?>
                            <div class="alert alert-<?php echo $discountMessageType; ?> py-2 px-3 small rounded-3 mb-4">
                                <?php echo htmlspecialchars($discountMessage); ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="h4 fw-bold mb-0" style="color: #C4622D; font-family: 'DM Sans', serif;">
                                <?php echo number_format($total); ?>₫
                            </span>
                        </div>

                        <a href="checkout.php" class="btn w-100 py-4 rounded-3 fw-bold text-white d-flex align-items-center justify-content-center gap-2 transition-all hover-scale"
                            style="background-color: #C4622D;">
                            TIẾN HÀNH THANH TOÁN <i class="fa-solid fa-arrow-right"></i>
                        </a>

                        <div class="d-flex justify-content-center gap-4 text-muted mt-4" style="font-size: 11px;">
                            <span><i class="fa-solid fa-rotate-left me-1"></i> Đổi trả 7 ngày</span>
                            <span style="color: #D4CEBE;">|</span>
                            <span><i class="fa-solid fa-truck-fast me-1"></i> Giao hàng nhanh</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>