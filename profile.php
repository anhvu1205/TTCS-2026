<?php 
session_start();
require_once 'includes/db.php';

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); exit();
}

// CHẶN ADMIN TRUY CẬP TRANG CÁ NHÂN CỦA USER
if ($_SESSION['user']['role'] === 'ADMIN') {
    header("Location: admin.php"); exit();
}

// --- KHÁCH BẤM XÓA SẢN PHẨM YÊU THÍCH ---
if (isset($_POST['remove_wishlist_id'])) {
    $product_id = $_POST['remove_wishlist_id'];
    $u_id = $_SESSION['user']['id'];
    
    // Xóa bản ghi trong bảng YeuThich khớp với User và Sản phẩm
    mysqli_query($conn, "DELETE FROM YeuThich WHERE maND = '$u_id' AND maSP = '$product_id'");
    
    header("Location: profile.php?tab=wishlist");
    exit();
}
$user_id = $_SESSION['user']['id'];

$user_id = $_SESSION['user']['id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

$user_res = mysqli_query($conn, "SELECT * FROM NguoiDung WHERE maND = '$user_id'");
$user = mysqli_fetch_assoc($user_res);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- KHÁCH BẤM HỦY ĐƠN: CHUYỂN SANG CHỜ HOÀN TIỀN ---
    if (isset($_POST['cancel_order_id'])) {
        $order_id = $_POST['cancel_order_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='CHO_HOAN_TIEN' WHERE maDH='$order_id'");
    }

    // --- XỬ LÝ ĐƠN HOÀN TẤT ---
    if (isset($_POST['process_order_id'])) {
        $order_id = $_POST['process_order_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='DA_XU_LY' WHERE maDH='$order_id'");
    }

    header("Location: profile.php?tab=orders");
    exit();
}

include 'includes/header.php'; 
?>

<main class="account-page-wrapper py-5" style="background-color: #FAF7F2; min-height: 100vh;">
    <div class="container max-w-4xl mx-auto px-4 lg:px-5">
        
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div class="d-flex align-items-center gap-4">
                <div class="avatar-circle-large"><?php echo strtoupper(substr($user['ten'], 0, 1)); ?></div>
                <div>
                    <h1 class="h4 fw-light mb-1" style="font-family: 'Cormorant Garamond', serif;"><?php echo htmlspecialchars($user['ten']); ?></h1>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['tenDangNhap']); ?></p>
                </div>
            </div>
            <a href="controll/logout.php" class="btn-logout-minimal"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất</a>
        </div>

        <div class="account-tabs-nav mb-5">
            <a href="profile.php?tab=orders" class="tab-item <?php echo $tab == 'orders' ? 'active' : ''; ?>"><i class="fa-solid fa-box me-2"></i> Đơn hàng</a>
            <a href="profile.php?tab=wishlist" class="tab-item <?php echo $tab == 'wishlist' ? 'active' : ''; ?>"><i class="fa-solid fa-heart me-2"></i> Yêu thích</a>
            <a href="profile.php?tab=profile" class="tab-item <?php echo $tab == 'profile' ? 'active' : ''; ?>"><i class="fa-solid fa-user me-2"></i> Tài khoản</a>
        </div>

        <div class="account-content-card p-4 p-md-5">
    <?php if ($tab == 'orders'): ?>
        <div class="order-history">
            <!-- 1. THÊM GIAO DIỆN BỘ LỌC TRẠNG THÁI -->
            <div class="mb-4">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="orders">
                    <select name="status" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()" style="max-width: 200px; background-color: #f8f9fa;">
                        <option value="">Tất cả đơn hàng</option>
                        <option value="CHUA_THANH_TOAN" <?php echo (isset($_GET['status']) && $_GET['status'] == 'CHUA_THANH_TOAN') ? 'selected' : ''; ?>>Chưa thanh toán</option>
                        <option value="CHO_XAC_NHAN" <?php echo (isset($_GET['status']) && $_GET['status'] == 'CHO_XAC_NHAN') ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="DA_GIAO_HANG" <?php echo (isset($_GET['status']) && $_GET['status'] == 'DA_GIAO_HANG') ? 'selected' : ''; ?>>Đang giao hàng</option>
                        <option value="HOAN_TAT" <?php echo (isset($_GET['status']) && $_GET['status'] == 'HOAN_TAT') ? 'selected' : ''; ?>>Hoàn tất</option>
                        <option value="CHO_HOAN_TIEN" <?php echo (isset($_GET['status']) && $_GET['status'] == 'CHO_HOAN_TIEN') ? 'selected' : ''; ?>>Chờ hoàn tiền</option>
                        <option value="DA_HUY" <?php echo (isset($_GET['status']) && $_GET['status'] == 'DA_HUY') ? 'selected' : ''; ?>>Đã hủy/Hoàn tiền</option>
                    </select>
                    <?php if(isset($_GET['status']) && $_GET['status'] != ''): ?>
                        <a href="profile.php?tab=orders" class="btn btn-sm btn-link text-muted text-decoration-none pt-1">Xóa lọc</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php
            // 2. SỬA CÂU LỆNH SQL ĐỂ LỌC THEO TRẠNG THÁI
            $f_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
            $where_status = ($f_status != '') ? " AND trangThai = '$f_status' " : "";

            $sql_orders = "SELECT *, ROW_NUMBER() OVER (ORDER BY ngayTao ASC) as order_number 
                           FROM DonHang 
                           WHERE maKH = (SELECT maKH FROM KhachHang WHERE maND = '$user_id') 
                           $where_status 
                           ORDER BY ngayTao DESC";
            
            $res_orders = mysqli_query($conn, $sql_orders);
            if (mysqli_num_rows($res_orders) > 0):
                while ($order = mysqli_fetch_assoc($res_orders)):
            ?>
    <div class="order-card-item mb-4 p-3 shadow-sm" style="background-color:white; border-radius:12px; border: 1px solid #eee;">
        
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="fw-bold d-block mb-1" style="font-size: 14px;">ĐƠN #<?php echo $order['order_number']; ?></span>
                <a href="order-detail.php?id=<?php echo $order['maDH']; ?>" 
                   class="text-decoration-none px-2 py-1 rounded-pill border" 
                   style="font-size: 10px; color: #1A1A1A; border-color: #D4CEBE !important; background: #FAF7F2;">
                   Xem chi tiết
                </a>
            </div>

            <div class="text-end d-flex flex-column align-items-end gap-1">
                <?php 
                $st = $order['trangThai'];
                if($st == 'CHUA_THANH_TOAN'): ?>
                    <span class="badge-status" style="background-color:#6B7280; color:white;">Chưa thanh toán</span>
                
                <?php elseif($st == 'CHO_XAC_NHAN'): ?>
                    <span class="badge-status mb-1" style="background-color:#10B981; color:white;">Đã thanh toán</span>
                    <div class="d-flex gap-2">
                        <span class="badge-status status-processing">Chờ xử lý</span>
                        <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy?');">
                            <input type="hidden" name="cancel_order_id" value="<?php echo $order['maDH']; ?>">
                            <button type="submit" class="badge-status status-cancel border-0">Hủy</button>
                        </form>
                    </div>

                <?php elseif($st == 'DA_GIAO_HANG'): ?>
                    <span class="badge-status" style="background-color:#3B82F6; color:white;">Đang giao hàng</span>
                
                <?php elseif($st == 'CHO_HOAN_TIEN'): ?>
                    <span class="badge-status status-processing">Chờ hoàn tiền</span>
                
                <?php elseif($st == 'DA_HUY'): ?>
                    <span class="badge-status status-canceled">Đã hoàn tiền</span>
                
                <?php else: ?>
                    <span class="badge-status status-complete">Hoàn tất</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-end pt-3 border-top" style="border-color:#f5f5f5 !important;">
                <span class="text-muted" style="font-size: 11px;"><?php echo date('d/m/Y', strtotime($order['ngayTao'])); ?></span>
                <span class="fw-bold" style="color: #1A1A1A; font-size: 16px;"><?php echo number_format($order['tongTien']); ?>₫</span>
            </div>
        </div>
            <?php endwhile; else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-box-open display-4 text-light mb-3"></i>
                    <p class="text-muted">Không tìm thấy đơn hàng nào phù hợp.</p>
                    <a href="products.php" class="btn btn-dark rounded-pill px-4 mt-2">Mua sắm ngay</a>
                </div>
            <?php endif; ?>
        </div>
            
            <?php elseif ($tab == 'wishlist'): ?>
                <div class="wishlist-container">
                    <?php
                    // 1. SỬA SQL: Lấy thêm cột soLuong từ bảng SanPham p
                    $sql_wishlist = "SELECT p.maSP as id, p.ten as name, p.gia as price, p.hinhAnh as image, p.soLuong 
                                    FROM YeuThich y JOIN SanPham p ON y.maSP = p.maSP 
                                    WHERE y.maND = '$user_id' ORDER BY y.ngayTao DESC";
                    $res_wishlist = mysqli_query($conn, $sql_wishlist);
                    
                    if ($res_wishlist && mysqli_num_rows($res_wishlist) > 0): ?>
                        <div class="row g-4" id="wishlist-grid">
                            <?php while ($item = mysqli_fetch_assoc($res_wishlist)): ?>
                                <div class="col-6 col-md-4 col-lg-3 wishlist-item-wrapper" id="wishlist-item-<?php echo $item['id']; ?>">
                                    <div class="product-card-v3 h-100 position-relative">
                                        <div class="product-img-wrapper-v3 small-card position-relative">
                                            <a href="detail.php?id=<?php echo $item['id']; ?>">
                                                <img src="<?php echo $item['image']; ?>" class="product-img-main-v3" alt="<?php echo $item['name']; ?>">
                                            </a>
                                            
                                            <?php if ($item['soLuong'] <= 0): ?>
                                                <button type="button" class="btn-quick-add-v32 disabled" style="background: #999; cursor: not-allowed;">
                                                    <i class="fa-solid fa-xmark me-2"></i>HẾT HÀNG
                                                </button>
                                            <?php endif; ?>

                                            <button class="btn-wishlist-v3 remove-from-wishlist" 
                                                    data-id="<?php echo $item['id']; ?>" 
                                                    title="Xóa khỏi yêu thích" 
                                                    style="z-index: 3;">
                                                <i class="fa-solid text-danger fa-heart"></i>
                                            </button>
                                            
                                            <?php if ($item['soLuong'] > 0): ?>
                                                <form action="cart.php" method="POST" class="quick-add-form-v32" style="z-index: 3;">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="add_to_cart" class="btn-quick-add-v32">
                                                        <i class="fa-solid fa-cart-plus me-2"></i>THÊM VÀO GIỎ HÀNG 
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-info-v3 text-center mt-2">
                                            <h6 class="product-name-v3 mb-1 small fw-bold">
                                                <a href="detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h6>
                                            <p class="product-price-v3 mb-0 small"><?php echo number_format($item['price']); ?>₫</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fa-regular fa-heart display-1 text-light"></i>
                            </div>
                            <h5 class="fw-light text-muted mb-4">Danh sách yêu thích của bạn đang trống</h5>
                            <a href="products.php" class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow-sm transition-all" 
                            style="letter-spacing: 1px; font-size: 0.85rem;">
                                KHÁM PHÁ SẢN PHẨM NGAY
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab == 'profile'): ?>
                <div class="profile-details max-w-md">
                    <h3 class="h6 fw-bold mb-4">Thông tin cá nhân</h3>
                    <div class="mb-4"><label class="label-minimal">HỌ VÀ TÊN</label><p class="input-mimic"><?php echo htmlspecialchars($user['ten']); ?></p></div>
                    <div class="mb-4"><label class="label-minimal">SỐ ĐIỆN THOẠI</label><p class="input-mimic"><?php echo htmlspecialchars($user['soDienThoai'] ?: '—'); ?></p></div>
                    <div class="mb-4"><label class="label-minimal">ĐỊA CHỈ</label><p class="input-mimic"><?php echo htmlspecialchars($user['diaChi'] ?: '—'); ?></p></div>
                    <button class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">Chỉnh sửa</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
