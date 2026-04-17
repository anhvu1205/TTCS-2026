<?php
ob_start();
session_start();
require_once 'includes/db.php';
require_once 'includes/discount_functions.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cartItems) && !isset($_GET['step'])) {
    header("Location: cart.php");
    exit();
}

// 2. TÍNH TOÁN TIỀN HÀNG
$subtotal = getCartSubtotal();
$discountAmount = getAppliedDiscountAmount();
$discountCode = $_SESSION['discount']['code'] ?? null;

$shipping = ($subtotal >= 500000 || $subtotal == 0) ? 0 : 30000;
$total = max(($subtotal - $discountAmount), 0) + $shipping;

// 3. XỬ LÝ KHI ẤN NÚT XÁC NHẬN ĐẶT HÀNG
if (isset($_POST['confirm_order'])) {
    $maND = $user['maND'] ?? $user['id'];

    $res_kh = mysqli_query($conn, "SELECT maKH FROM KhachHang WHERE maND = '$maND'");
    if (mysqli_num_rows($res_kh) > 0) {
        $kh = mysqli_fetch_assoc($res_kh);
        $maKH = $kh['maKH'];
    } else {
        mysqli_query($conn, "INSERT INTO KhachHang (maND, diemTichLuy) VALUES ('$maND', 0)");
        $maKH = mysqli_insert_id($conn);
    }

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    $payment_method = $_POST['payment_method'];

    $trangThai = ($payment_method === 'QR') ? 'CHUA_THANH_TOAN' : 'CHO_XAC_NHAN';

    $sql_order = "INSERT INTO DonHang (maKH, hoTen, soDienThoai, diaChi, tongTien, phiShip, phuongThucThanhToan, trangThai) 
                  VALUES ('$maKH', '$fullname', '$phone', '$address', '$total', '$shipping', '$payment_method', '$trangThai')";

    if (mysqli_query($conn, $sql_order)) {
        $maDH = mysqli_insert_id($conn);

        foreach ($cartItems as $item) {
            $maSP = $item['id'];
            $soLuong = $item['quantity'];
            $donGia = $item['price'];
            $thanhTien = $donGia * $soLuong;
            $kichCo = $item['size'] ?? 'M';
            $mauSac = $item['color'] ?? 'Default';

            mysqli_query($conn, "INSERT INTO ChiTietDonHang (maDH, maSP, soLuong, donGia, thanhTien, kichCo, mauSac) 
                                 VALUES ('$maDH', '$maSP', '$soLuong', '$donGia', '$thanhTien', '$kichCo', '$mauSac')");

            mysqli_query($conn, "UPDATE SanPham SET soLuong = soLuong - $soLuong WHERE maSP = '$maSP'");
        }

        // tăng lượt dùng mã giảm giá nếu có
        if (!empty($_SESSION['discount']['id'])) {
            increaseDiscountUsedCount($conn, (int)$_SESSION['discount']['id']);
        }

        // xóa giỏ hàng và mã giảm giá sau khi tạo đơn
        unset($_SESSION['cart']);
        unset($_SESSION['discount']);

        if ($payment_method === 'QR') {
            header("Location: payment-qr.php?order_id=$maDH&total=$total");
        } else {
            $_SESSION['order_success'] = ['name' => $fullname, 'phone' => $phone];
            header("Location: checkout.php?step=success");
        }
        exit();
    } else {
        die("Lỗi Database: " . mysqli_error($conn));
    }
}

include 'includes/header.php';
?>

<main class="py-5" style="background-color: #FAF7F2; min-height: 100vh; padding-top: 100px !important;">
    <div class="container max-w-6xl mx-auto px-4">

        <?php if (isset($_GET['step']) && $_GET['step'] == 'success' && isset($_SESSION['order_success'])): ?>
            <div class="text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="background-color: #D9E5D6; width: 80px; height: 80px;">
                    <i class="fa-solid fa-check fs-1" style="color: #5C6650;"></i>
                </div>
                <h2 class="display-6 mb-3" style="font-family: 'Cormorant Garamond', serif;">Đặt hàng thành công!</h2>
                <p class="text-muted">Cảm ơn <strong><?php echo $_SESSION['order_success']['name']; ?></strong>!</p>
                <p class="text-muted mb-5">Chúng tôi sẽ liên hệ qua số <strong><?php echo $_SESSION['order_success']['phone']; ?></strong> để xác nhận.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="profile.php" class="btn px-4 py-2 rounded-pill border border-dark" style="color: #1A1A1A; font-weight: 500;">Xem đơn hàng</a>
                    <a href="shop.php" class="btn btn-dark rounded-pill px-4">Tiếp tục mua sắm</a>
                </div>
            </div>
            <?php unset($_SESSION['order_success']); ?>

        <?php else: ?>
            <div class="mb-5">
                <a href="cart.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại giỏ hàng</a>
                <h1 class="display-5 mt-3" style="font-family: 'Cormorant Garamond', serif;">Thanh toán</h1>
            </div>

            <form action="checkout.php" method="POST" class="row g-4">
                <div class="col-lg-7">
                    <div class="p-4 rounded-4 mb-4" style="background-color: #EDE8DF;">
                        <h5 class="mb-4 small fw-bold text-uppercase tracking-widest"><i class="fa-solid fa-user me-2" style="color: #C4622D;"></i>Thông tin người nhận</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="small fw-bold mb-2">Họ và tên *</label>
                                <input type="text" name="fullname"
                                    value="<?php echo $user['ten'] ?? $user['fullname'] ?? ''; ?>"
                                    class="form-control" placeholder="Nguyễn Văn A" required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold mb-2">Số điện thoại *</label>
                                <input type="tel" name="phone" value="<?php echo $user['soDienThoai'] ?? ''; ?>" class="form-control border-0 p-3 rounded-3" placeholder="090..." required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold mb-2">Địa chỉ giao hàng *</label>
                                <textarea name="address" class="form-control border-0 p-3 rounded-3" rows="3" placeholder="Số nhà, tên đường, phường/xã..." required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold mb-2">Ghi chú</label>
                                <input type="text" name="note" class="form-control border-0 p-3 rounded-3" placeholder="Ghi chú cho đơn hàng (nếu có)">
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-4" style="background-color: #EDE8DF;">
                        <h5 class="mb-4 small fw-bold text-uppercase tracking-widest"><i class="fa-solid fa-credit-card me-2" style="color: #C4622D;"></i>Phương thức thanh toán</h5>
                        <div class="payment-options">
                            <label class="d-block p-3 mb-2 rounded-3 bg-white border cursor-pointer">
                                <input type="radio" name="payment_method" value="COD" checked>
                                <span class="ms-2">Thanh toán khi nhận hàng (COD)</span>
                            </label>
                            <label class="d-block p-3 rounded-3 bg-white border cursor-pointer">
                                <input type="radio" name="payment_method" value="QR">
                                <span class="ms-2">Chuyển khoản ngân hàng (QR Code)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="p-4 rounded-4 sticky-top" style="top: 120px; background-color: #EDE8DF;">
                        <h5 class="mb-4 small fw-bold text-uppercase tracking-widest">Đơn hàng (<?php echo count($cartItems); ?>)</h5>

                        <div class="order-items-list mb-4">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex gap-3 mb-3 pb-3 border-bottom" style="border-color: #D4CEBE !important;">
                                    <img src="<?php echo $item['image']; ?>" class="rounded-3" style="width: 60px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <p class="small fw-bold mb-1"><?php echo $item['name']; ?></p>
                                        <p class="text-muted extra-small mb-0">Size <?php echo $item['size'] ?? 'M'; ?> · × <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <span class="small fw-bold"><?php echo number_format($item['price'] * $item['quantity']); ?>₫</span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="price-summary extra-small border-bottom pb-3 mb-3" style="border-color: #D4CEBE !important;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span><?php echo number_format($subtotal); ?>₫</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Giảm giá<?php echo $discountCode ? " ($discountCode)" : ""; ?></span>
                                <span style="color:#5C6650;">-<?php echo number_format($discountAmount); ?>₫</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Vận chuyển</span>
                                <span><?php echo ($shipping == 0) ? 'Miễn phí' : number_format($shipping) . '₫'; ?></span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="h4 fw-bold mb-0" style="color: #C4622D;"><?php echo number_format($total); ?>₫</span>
                        </div>

                        <button type="submit" name="confirm_order" class="btn w-100 py-3 rounded-pill text-white fw-bold" style="background-color: #C4622D;">
                            XÁC NHẬN ĐẶT HÀNG
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>