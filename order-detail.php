<?php
session_start();
require_once 'includes/db.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user']['maND'] ?? $_SESSION['user']['id'];
$is_admin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'ADMIN');

// 2. XỬ LÝ TÁC VỤ (ADMIN & USER)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($is_admin) {
        if(isset($_POST['set_pending_id'])) mysqli_query($conn, "UPDATE DonHang SET trangThai='CHO_XAC_NHAN' WHERE maDH='$order_id'");
        if(isset($_POST['set_shipping_id'])) mysqli_query($conn, "UPDATE DonHang SET trangThai='DA_GIAO_HANG' WHERE maDH='$order_id'");
        if(isset($_POST['set_complete_id'])) mysqli_query($conn, "UPDATE DonHang SET trangThai='HOAN_TAT' WHERE maDH='$order_id'");
        if(isset($_POST['confirm_refund_id'])) {
            $res_items = mysqli_query($conn, "SELECT maSP, soLuong FROM ChiTietDonHang WHERE maDH='$order_id'");
            while($item = mysqli_fetch_assoc($res_items)){
                $p_id = $item['maSP']; $qty = $item['soLuong'];
                mysqli_query($conn, "UPDATE SanPham SET soLuong = soLuong + $qty WHERE maSP='$p_id'");
            }
            mysqli_query($conn, "UPDATE DonHang SET trangThai='DA_HUY' WHERE maDH='$order_id'");
        }
    }
    if (isset($_POST['action_cancel_order'])) {
        $id_to_cancel = intval($_POST['order_id_hidden']);
        mysqli_query($conn, "UPDATE DonHang SET trangThai = 'CHO_HOAN_TIEN' WHERE maDH = '$id_to_cancel'");
    }
    header("Location: order-detail.php?id=" . $order_id);
    exit();
}

// 3. TRUY CẬP DỮ LIỆU
if ($is_admin) {
    $sql_order = "SELECT * FROM DonHang WHERE maDH = '$order_id'";
} else {
    $sql_order = "SELECT d.*, k.maND FROM DonHang d JOIN KhachHang k ON d.maKH = k.maKH WHERE d.maDH = '$order_id' AND k.maND = '$user_id'";
}
$res_order = mysqli_query($conn, $sql_order);
$order = mysqli_fetch_assoc($res_order);

if (!$order) die("Đơn hàng không tồn tại hoặc không có quyền.");

include 'includes/header.php';
?>

<main class="py-5" style="background-color: #FAF7F2; min-height: 100vh; padding-top: 100px !important;">
    <div class="container" style="max-width: 850px;">
        <!-- Nút quay lại linh hoạt -->
        <a href="<?php echo $is_admin ? 'admin.php?tab=orders' : 'profile.php?tab=orders'; ?>" class="text-decoration-none text-muted small mb-4 d-inline-block">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-md-5">
                
                <div class="d-flex justify-content-between align-items-start mb-5">
                    <div>
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Cormorant Garamond', serif;">Chi tiết đơn hàng #<?php echo $order_id; ?></h2>
                        <p class="extra-small text-muted mb-0">Hệ thống quản lý đơn hàng.</p>
                    </div>
                    
                    <div class="status-box text-end">
                        <?php 
                        $st = $order['trangThai'];
                        
                        // NẾU LÀ ADMIN: HIỆN NÚT BẤM TÁC VỤ (GIỮ NGUYÊN STYLE BADGE CỦA USER)
                        if ($is_admin): ?>
                            <form method="POST" class="d-flex flex-column align-items-end gap-2">
                                <?php if($st == 'CHUA_THANH_TOAN'): ?>
                                    <button type="submit" name="set_pending_id" class="badge-status border-0" style="background-color:#6B7280; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Chưa thanh toán</button>
                                <?php elseif($st == 'CHO_XAC_NHAN'): ?>
                                    <button type="submit" name="set_shipping_id" class="badge-status border-0" style="background-color:#F59E0B; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Chờ xử lý</button>
                                <?php elseif($st == 'DA_GIAO_HANG'): ?>
                                    <button type="submit" name="set_complete_id" class="badge-status border-0" style="background-color:#3B82F6; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Đang giao hàng</button>
                                <?php elseif($st == 'CHO_HOAN_TIEN'): ?>
                                    <button type="submit" name="confirm_refund_id" class="badge-status border-0" style="background-color:#F59E0B; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Hoàn tiền</button>
                                <?php elseif($st == 'HOAN_TAT'): ?>
                                    <span class="badge-status" style="background-color:#10B981; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600;">Hoàn tất</span>                                
                                <?php elseif($st == 'DA_HUY'): ?>
                                    <span class="badge-status" style="background-color:#9CA3AF; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600;">Đã hoàn tiền</span>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <!-- NẾU LÀ USER: CHỈ HIỂN THỊ CHỮ (GIỮ NGUYÊN CODE CỦA BẠN) -->
                            <?php if($st == 'CHUA_THANH_TOAN'): ?>
                                <span class="badge-status" style="background-color:#6B7280; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Chưa thanh toán</span>
                            <?php elseif($st == 'CHO_XAC_NHAN'): ?>
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <span class="badge-status" style="background-color:#10B981; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Đã thanh toán</span>
                                    <span class="badge-status" style="background-color:#F59E0B; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Chờ xử lý</span>
                                </div>
                            <?php elseif($st == 'DA_GIAO_HANG'): ?>
                                <span class="badge-status" style="background-color:#3B82F6; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Đang giao hàng</span>
                            <?php elseif($st == 'CHO_HOAN_TIEN'): ?>
                                <span class="badge-status" style="background-color:#F59E0B; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Chờ hoàn tiền</span>
                            <?php elseif($st == 'DA_HUY'): ?>
                                <span class="badge-status" style="background-color:#9CA3AF; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Đã hoàn tiền</span>
                            <?php else: ?>
                                <span class="badge-status" style="background-color:#10B981; color:white; padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight:600; text-transform:uppercase;">Hoàn tất</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CÁC PHẦN THÔNG TIN KHÁCH HÀNG & SẢN PHẨM GIỮ NGUYÊN 100% -->
                <div class="row g-4 mb-5 pb-4 border-bottom" style="border-color: #EDE8DF !important;">
                    <div class="col-md-7">
                        <p class="extra-small text-muted text-uppercase fw-bold mb-3 tracking-wider">Thông tin giao hàng</p>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-user text-muted me-3" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block" style="font-size: 10px;">Người nhận</span><span class="small fw-bold"><?php echo htmlspecialchars($order['hoTen']); ?></span></div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-phone text-muted me-3" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block" style="font-size: 10px;">Số điện thoại</span><span class="small fw-bold"><?php echo htmlspecialchars($order['soDienThoai'] ?? $order['sdt']); ?></span></div>
                        </div>
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-location-dot text-muted me-3 mt-1" style="width: 15px;"></i>
                            <div><span class="extra-small text-muted d-block" style="font-size: 10px;">Địa chỉ</span><span class="small fw-bold"><?php echo htmlspecialchars($order['diaChi']); ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <p class="extra-small text-muted text-uppercase fw-bold mb-3 tracking-wider">Thời gian & Thanh toán</p>
                        <div class="mb-3"><span class="extra-small text-muted d-block" style="font-size: 10px;">Ngày đặt</span><span class="small fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?></span></div>
                        <div><span class="extra-small text-muted d-block" style="font-size: 10px;">Phương thức</span><span class="small fw-bold text-uppercase" style="color: #C4622D;"><?php echo $order['phuongThucThanhToan']; ?></span></div>
                    </div>
                </div>

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
                                <p class="extra-small text-muted mb-0">Size: <?php echo $item['kichCo'] ?? 'Free'; ?> | SL: x<?php echo $item['soLuong']; ?></p>
                            </div>
                            <div class="text-end fw-bold small"><?php echo number_format($item['thanhTien']); ?>₫</div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="p-4 rounded-4" style="background-color: #f8f9fa; border: 1px solid #eee;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase">Tổng thanh toán:</span>
                        <span class="h3 fw-bold mb-0" style="color: #C4622D;"><?php echo number_format($order['tongTien']); ?>₫</span>
                    </div>
                </div>

                <!-- NÚT YÊU CẦU HỦY CỦA USER -->
                <?php if (!$is_admin && ($order['trangThai'] == 'CHO_XAC_NHAN' || $order['trangThai'] == 'CHUA_THAN_TOAN')): ?>
                    <div class="mt-4 pt-4 border-top text-center" style="border-color: #EDE8DF !important;">
                        <form method="POST" onsubmit="return confirm('Xác nhận yêu cầu hủy?');">
                            <input type="hidden" name="order_id_hidden" value="<?php echo $order_id; ?>">
                            <button type="submit" name="action_cancel_order" class="btn btn-outline-danger rounded-pill px-5 py-2 fw-bold small">YÊU CẦU HỦY & HOÀN TIỀN</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>