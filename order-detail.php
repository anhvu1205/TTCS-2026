<?php
session_start();
require_once 'includes/db.php';

function getStatusClass($status) {
    $map = [
        'Chưa thanh toán'         => 'status-chua_thanh_toan',
        'Chờ xác nhận'            => 'status-cho_xac_nhan',
        'Đang giao hàng'          => 'status-dang_giao_hang',
        'Đã giao hàng'            => 'status-da_giao_hang',
        'Hoàn tất'                => 'status-hoan_tat',
        'Đã hủy'                  => 'status-da_huy',
        'Chờ kiểm tra hoàn tiền'  => 'status-cho_hoan_tien' 
    ];
    return $map[$status] ?? 'status-default';
}

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user']['id'];
$is_admin = ($_SESSION['user']['role'] === 'ADMIN');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($is_admin && isset($_POST['admin_update_status'])) {
        $new_st = mysqli_real_escape_string($conn, $_POST['new_status']);
        if ($new_st == 'Đã hủy') {
            $res_items = mysqli_query($conn, "SELECT maSP, soLuong FROM ChiTietDonHang WHERE maDH='$order_id'");
            while($item = mysqli_fetch_assoc($res_items)){
                $p_id = $item['maSP']; $qty = $item['soLuong'];
                mysqli_query($conn, "UPDATE SanPham SET soLuong = soLuong + $qty WHERE maSP='$p_id'");
            }
        }
        mysqli_query($conn, "UPDATE DonHang SET trangThai='$new_st' WHERE maDH='$order_id'");
    }
    if (!$is_admin && isset($_POST['action_cancel_order'])) {
        $check_sql = mysqli_query($conn, "SELECT phuongThucThanhToan FROM DonHang WHERE maDH = '$order_id'");
        $order_info = mysqli_fetch_assoc($check_sql);
        $new_status = ($order_info['phuongThucThanhToan'] == 'COD') ? 'Đã hủy' : 'Chờ kiểm tra hoàn tiền';
        
        mysqli_query($conn, "UPDATE DonHang SET trangThai = '$new_status' 
                           WHERE maDH = '$order_id' AND maKH = (SELECT maKH FROM KhachHang WHERE maND = '$user_id')");
    }

    header("Location: order-detail.php?id=" . $order_id);
    exit();
}

// TRUY VẤN DỮ LIỆU ĐƠN HÀNG
if ($is_admin) {
    $sql_order = "SELECT * FROM DonHang WHERE maDH = '$order_id'";
} else {
    $sql_order = "SELECT d.* FROM DonHang d JOIN KhachHang k ON d.maKH = k.maKH 
                  WHERE d.maDH = '$order_id' AND k.maND = '$user_id'";
}
$res_order = mysqli_query($conn, $sql_order);
$order = mysqli_fetch_assoc($res_order);

if (!$order) die("<div class='text-center py-5'>Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.</div>");

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/auth-account.css">

<style>
    .status-dang_giao_hang { background-color: #8B5CF6 !important; color: white; }
    .status-da_giao { background-color: #0D9488 !important; color: white; }
    .status-cho_xac_nhan { background-color: #F59E0B !important; color: white; }
    .status-hoan_tat { background-color: #10B981 !important; color: white; }
    .status-da_huy { background-color: #EF4444 !important; color: white; }
    .status-cho_hoan_tien { background-color: #6366F1 !important; color: white; }
    .status-chua_thanh_toan { background-color: #6B7280 !important; color: white; }

    .admin-select-status {
        border: none;
        border-radius: 50px;
        padding: 8px 25px 8px 15px;
        font-size: 11px;
        font-weight: 700;
        color: white;
        appearance: none;
        cursor: pointer;
        text-transform: uppercase;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white' %3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 12px;
    }
</style>

<main class="py-5" style="background-color: #FAF7F2; min-height: 100vh; padding-top: 100px !important;">
    <div class="container" style="max-width: 850px;">
        
        <a href="<?php echo $is_admin ? 'admin.php?tab=orders' : 'profile.php?tab=orders'; ?>" class="text-decoration-none text-muted small mb-4 d-inline-block">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách
        </a>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-md-5">
                
                <!-- HEADER CHI TIẾT -->
                <div class="d-flex justify-content-between align-items-start mb-5">
                    <div>
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Cormorant Garamond', serif;">Đơn hàng #<?php echo $order_id; ?></h2>
                        <p class="extra-small text-muted mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?></p>
                    </div>
                    
                    <div class="text-end">
                        <?php if ($is_admin): ?>
                            <!-- ADMIN CẬP NHẬT TRẠNG THÁI -->
                            <form method="POST">
                                <select name="new_status" onchange="this.form.submit()" class="admin-select-status <?php echo getStatusClass($order['trangThai']); ?>">
                                    <?php 
                                    $all_sts = ['Chưa thanh toán', 'Chờ xác nhận', 'Đang giao hàng', 'Đã giao', 'Hoàn tất', 'Chờ kiểm tra hoàn tiền', 'Đã hủy'];
                                    foreach($all_sts as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo ($order['trangThai'] == $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="admin_update_status" value="1">
                            </form>
                        <?php else: ?>
                            <!-- KHÁCH HÀNG XEM TRẠNG THÁI -->
                            <span class="badge-status <?php echo getStatusClass($order['trangThai']); ?>" style="padding: 8px 20px; border-radius: 50px; font-size: 11px; font-weight: 700; color: white; text-transform: uppercase;">
                                <?php echo $order['trangThai']; ?>
                            </span>
                        <?php endif; ?>
                        <div class="mt-2">
                            <?php 
                            $st = $order['trangThai'];
                            $pttt = $order['phuongThucThanhToan'];
                            $proof = $order['minhChungThanhToan'] ?? '';

                            // 1. Đơn COD chưa xong -> CHƯA THANH TOÁN
                            if ($pttt == 'COD' && !in_array($st, ['Hoàn tất', 'Đã hủy', 'Đã giao'])): ?>
                                <small class="text-danger fw-bold" style="font-size: 10px;">[ CHƯA THANH TOÁN ]</small>
                            
                            <?php 
                            // 2. Đơn QR đã gửi ảnh -> ĐANG KIỂM TRA
                            elseif ($pttt == 'QR' && $st == 'Chờ xác nhận' && !empty($proof)): ?>
                                <small class="text-primary fw-bold" style="font-size: 10px;">[ ĐANG KIỂM TRA ]</small>
                            
                            <?php 
                            // 3. Đơn QR chưa gửi ảnh -> CHƯA THANH TOÁN
                            elseif ($pttt == 'QR' && $st == 'Chưa thanh toán'): ?>
                                <small class="text-danger fw-bold" style="font-size: 10px;">[ CHƯA THANH TOÁN ]</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- THÔNG TIN GIAO HÀNG -->
                <div class="row g-4 mb-5 pb-4 border-bottom" style="border-color: #EDE8DF !important;">
                    <div class="col-md-7">
                        <p class="extra-small text-muted text-uppercase fw-bold mb-3 tracking-wider">Thông tin giao hàng</p>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-user text-muted me-3" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block">Người nhận</span><span class="small fw-bold"><?php echo htmlspecialchars($order['hoTen']); ?></span></div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-phone text-muted me-3" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block">Số điện thoại</span><span class="small fw-bold"><?php echo htmlspecialchars($order['soDienThoai']); ?></span></div>
                        </div>
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-location-dot text-muted me-3 mt-1" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block">Địa chỉ</span><span class="small fw-bold"><?php echo htmlspecialchars($order['diaChi']); ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <p class="extra-small text-muted text-uppercase fw-bold mb-3 tracking-wider">Thanh toán</p>
                        <div class="mb-3"><span class="extra-small text-muted d-block">Phương thức</span><span class="small fw-bold text-uppercase" style="color: #C4622D;"><?php echo $order['phuongThucThanhToan']; ?></span></div>
                        <div><span class="extra-small text-muted d-block">Phí vận chuyển</span><span class="small fw-bold"><?php echo number_format($order['phiShip']); ?>₫</span></div>
                    </div>
                </div>

                <!-- Hiển thị ảnh minh chứng cho Admin -->
                <?php if ($is_admin && !empty($order['minhChungThanhToan'])): ?>
                    <div class="mb-5 p-4 rounded-4 shadow-sm" style="background-color: #f0f7ff; border: 1px solid #cce3ff;">
                        <p class="small fw-bold text-primary mb-3 text-uppercase tracking-wider">
                            <i class="fa-solid fa-image me-2"></i>Minh chứng thanh toán (Khách gửi)
                        </p>
                        <a href="<?php echo $order['minhChungThanhToan']; ?>" target="_blank">
                            <img src="<?php echo $order['minhChungThanhToan']; ?>" class="rounded-3 border shadow-sm" style="max-width: 100%; max-height: 350px; cursor: zoom-in;">
                        </a>
                        <p class="extra-small text-muted mt-2 italic">Bấm vào ảnh để xem kích thước gốc.</p>
                    </div>
                <?php endif; ?>

                <!-- DANH SÁCH SẢN PHẨM -->
                <div class="order-items mb-5">
                    <?php
                    $sql_items = "SELECT ct.*, s.ten, s.hinhAnh FROM ChiTietDonHang ct JOIN SanPham s ON ct.maSP = s.maSP WHERE ct.maDH = '$order_id'";
                    $res_items = mysqli_query($conn, $sql_items);
                    while ($item = mysqli_fetch_assoc($res_items)):
                    ?>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom border-light align-items-center">
                            <img src="<?php echo $item['hinhAnh']; ?>" class="rounded-3 shadow-sm" style="width: 65px; height: 85px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="small fw-bold mb-1"><?php echo htmlspecialchars($item['ten']); ?></h6>
                                <p class="extra-small text-muted mb-0">Size: <?php echo $item['kichCo']; ?> | SL: x<?php echo $item['soLuong']; ?></p>
                            </div>
                            <div class="text-end fw-bold small"><?php echo number_format($item['thanhTien']); ?>₫</div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- TỔNG CỘNG -->
                <div class="p-4 rounded-4" style="background-color: #EDE8DF; border: 1px solid #D4CEBE;">

                    <?php 
                        $coupon_code = $order['maGiamGia'] ?? '';
                        $discount_val = (int)($order['soTienGiam'] ?? 0);
                        
                        if ($discount_val > 0): 
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">
                            <i class="fa-solid fa-ticket me-1 text-danger"></i> Giảm giá (<?php echo htmlspecialchars($coupon_code); ?>):
                        </span>
                        <span class="fw-bold small text-danger">- <?php echo number_format($discount_val); ?>₫</span>
                    </div>
                    <?php endif; ?>

                    <hr style="border-top: 1px dashed #D4CEBE; margin: 15px 0;">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase">Tổng thanh toán:</span>
                        <span class="h3 fw-bold mb-0" style="color: #C4622D; font-family: 'DM Sans', serif;">
                            <?php echo number_format($order['tongTien']); ?>₫
                        </span>
                    </div>
                </div>

                <!-- NÚT HỦY DÀNH CHO USER -->
                <?php if (!$is_admin && in_array($order['trangThai'], ['Chờ xác nhận', 'Chưa thanh toán'])): ?>
                    <div class="mt-4 pt-4 border-top text-center" style="border-color: #EDE8DF !important;">
                        <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                            <button type="submit" name="action_cancel_order" class="btn btn-outline-danger rounded-pill px-5 py-2 fw-bold small">HỦY ĐƠN HÀNG</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>