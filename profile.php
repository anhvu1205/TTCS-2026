<?php 
session_start();
require_once 'includes/db.php';

// HÀM HỖ TRỢ LẤY CLASS CSS (Đồng bộ với Admin)
function getStatusClass($status) {
    $map = [
        'Chờ xác nhận'   => 'status-cho_xac_nhan',
        'Đang giao hàng' => 'status-dang_giao_hang',
        'Đã giao'        => 'status-da_giao',
        'Hoàn tất'       => 'status-hoan_tat',
        'Đã hủy'         => 'status-da_huy',
        'Chờ kiểm tra hoàn tiền'  => 'status-cho_hoan_tien',
        'Đã hoàn tiền'    => 'status-da_hoan_tien'
    ];
    return $map[$status] ?? 'status-default';
}

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); 
    exit();
}

// 2. CHẶN ADMIN TRUY CẬP
if ($_SESSION['user']['role'] === 'ADMIN') {
    header("Location: admin.php"); 
    exit();
}

$user_id = $_SESSION['user']['id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
$is_editing = isset($_GET['edit']) && $_GET['edit'] == 1;

// --- XỬ LÝ CÁC HÀNH ĐỘNG POST ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // A. CẬP NHẬT THÔNG TIN TÀI KHOẢN
    if (isset($_POST['update_profile'])) {
        $ten = mysqli_real_escape_string($conn, $_POST['ten']);
        $sdt = mysqli_real_escape_string($conn, $_POST['soDienThoai']);
        $diachi = mysqli_real_escape_string($conn, $_POST['diaChi']);

        $sql_update = "UPDATE NguoiDung SET ten='$ten', soDienThoai='$sdt', diaChi='$diachi' WHERE maND='$user_id'";
        if (mysqli_query($conn, $sql_update)) {
            $_SESSION['user']['ten'] = $ten;
            header("Location: profile.php?tab=profile&success=1");
            exit();
        }
    }

    // B. XÓA KHỎI DANH SÁCH YÊU THÍCH
    if (isset($_POST['remove_wishlist_id'])) {
        $product_id = mysqli_real_escape_string($conn, $_POST['remove_wishlist_id']);
        mysqli_query($conn, "DELETE FROM YeuThich WHERE maND = '$user_id' AND maSP = '$product_id'");
        header("Location: profile.php?tab=wishlist");
        exit();
    }

    // C. LOGIC HỦY ĐƠN HÀNG (ĐÃ SỬA)
    if (isset($_POST['cancel_order_id'])) {
        $order_id = mysqli_real_escape_string($conn, $_POST['cancel_order_id']);
        
        // Lấy phương thức thanh toán của đơn hàng này
        $check_sql = "SELECT phuongThucThanhToan FROM DonHang WHERE maDH = '$order_id'";
        $check_res = mysqli_query($conn, $check_sql);
        $order_info = mysqli_fetch_assoc($check_res);

        // Nếu là COD thì chuyển thẳng sang 'Đã hủy', nếu là QR/Chuyển khoản thì chuyển sang 'Chờ kiểm tra hoàn tiền'
        $new_status = ($order_info['phuongThucThanhToan'] == 'COD') ? 'Đã hủy' : 'Chờ kiểm tra hoàn tiền';

        mysqli_query($conn, "UPDATE DonHang SET trangThai='$new_status' 
                           WHERE maDH='$order_id' AND maKH = (SELECT maKH FROM KhachHang WHERE maND = '$user_id')");
        
        header("Location: profile.php?tab=orders");
        exit();
    }
}

// LẤY THÔNG TIN NGƯỜI DÙNG
$user_res = mysqli_query($conn, "SELECT * FROM NguoiDung WHERE maND = '$user_id'");
$user = mysqli_fetch_assoc($user_res);

include 'includes/header.php'; 
?>

<link rel="stylesheet" href="assets/css/auth-account.css">

<style>
    /* Đồng bộ CSS với Admin */
    .status-dang_giao_hang { background-color: #8B5CF6 !important; color: white; }
    .status-da_giao { background-color: #0D9488 !important; color: white; }
    .status-cho_xac_nhan { background-color: #F59E0B !important; color: white; }
    .status-hoan_tat { background-color: #10B981 !important; color: white; }
    .status-da_huy { background-color: #EF4444 !important; color: white; }
    .status-cho_hoan_tien { background-color: #6366F1 !important; color: white; }
    .status-da_hoan_tien { background-color: #7be0ff !important; color: white; }
</style>

<main class="account-page-wrapper py-5">
    <div class="container max-w-4xl mx-auto px-4 lg:px-5">
        
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div class="d-flex align-items-center gap-4">
                <div class="avatar-circle-large" style="background-color: #C4622D;">
                    <?php echo strtoupper(substr($user['ten'] ?? 'U', 0, 1)); ?>
                </div>
                <div>
                    <h1 class="h4 fw-light mb-1" style="font-family: 'Cormorant Garamond', serif;">
                        <?php echo htmlspecialchars($user['ten']); ?>
                    </h1>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['tenDangNhap']); ?></p>
                </div>
            </div>
            <a href="controll/logout.php" class="btn-logout-minimal">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
            </a>
        </div>

        <div class="account-tabs-nav mb-5">
            <a href="profile.php?tab=orders" class="tab-item <?php echo $tab == 'orders' ? 'active' : ''; ?>">Đơn hàng</a>
            <a href="profile.php?tab=wishlist" class="tab-item <?php echo $tab == 'wishlist' ? 'active' : ''; ?>">Yêu thích</a>
            <a href="profile.php?tab=profile" class="tab-item <?php echo $tab == 'profile' ? 'active' : ''; ?>">Tài khoản</a>
        </div>

        <div class="account-content-card p-4 p-md-5">
            
            <?php if ($tab == 'orders'): ?>
                <div class="order-history">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Lịch sử đơn hàng</h5>
                        <form method="GET">
                            <input type="hidden" name="tab" value="orders">
                            <select name="status" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()" style="background-color: #FAF7F2; border: 1px solid #EDE8DF; min-width: 160px;">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ xác nhận" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Chờ xác nhận') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                <option value="Đang giao hàng" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang giao hàng') ? 'selected' : ''; ?>>Đang giao hàng</option>
                                <option value="Đã giao" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã giao') ? 'selected' : ''; ?>>Đã giao</option>
                                <option value="Hoàn tất" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Hoàn tất') ? 'selected' : ''; ?>>Hoàn tất</option>
                                <option value="Đã hủy" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
                                <option value="Chờ kiểm tra hoàn tiền" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Chờ kiểm tra hoàn tiền') ? 'selected' : ''; ?>>Chờ kiểm tra hoàn tiền</option>
                                <option value="Đã hoàn tiền" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã hoàn tiền') ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                            </select>
                        </form>
                    </div>

                    <?php
                    $f_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                    $where_status = ($f_status != '') ? " AND trangThai = '$f_status' " : "";
                    
                    $sql_orders = "SELECT *, ROW_NUMBER() OVER (ORDER BY ngayTao ASC) as order_number 
                                   FROM DonHang 
                                   WHERE maKH = (SELECT maKH FROM KhachHang WHERE maND = '$user_id') 
                                   $where_status ORDER BY ngayTao DESC";
                    $res_orders = mysqli_query($conn, $sql_orders);

                    if (mysqli_num_rows($res_orders) > 0):
                        while ($order = mysqli_fetch_assoc($res_orders)):
                            $st = $order['trangThai'];
                    ?>
                        <div class="mb-4 p-4 border rounded-4 shadow-sm" style="background-color: #ffffff;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-bold d-block mb-1">ĐƠN #<?php echo $order['order_number']; ?></span>
                                    <span class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="badge-status <?php echo getStatusClass($st); ?>">
                                        <?php echo $st; ?>
                                    </span>
                                    
                                    <!-- PHẦN SỬA LỖI BIẾN $o THÀNH $order VÀ $curr_st THÀNH $st -->
                                    <div class="mt-1">
                                        <?php 
                                        // 1. Đơn COD chưa xong -> CHƯA THANH TOÁN (Màu đỏ)
                                        if ($order['phuongThucThanhToan'] == 'COD' && !in_array($st, ['Hoàn tất', 'Đã hủy', 'Đã giao'])): ?>
                                            <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                        
                                        <?php 
                                        // 2. Đơn QR đã gửi ảnh -> ĐANG KIỂM TRA (Màu xanh dương)
                                        elseif ($order['phuongThucThanhToan'] == 'QR' && $st == 'Chờ xác nhận' && !empty($order['minhChungThanhToan'])): ?>
                                            <small class="text-primary fw-bold" style="font-size: 9px;">[ ĐANG KIỂM TRA ]</small>
                                        
                                        <?php 
                                        // 3. Đơn QR chưa gửi ảnh -> CHƯA THANH TOÁN
                                        elseif ($order['phuongThucThanhToan'] == 'QR' && $st == 'Chưa thanh toán'): ?>
                                            <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nút Hủy đơn cho khách (Giữ nguyên) -->
                                    <?php if($st == 'Chờ xác nhận' || $st == 'Chưa thanh toán'): ?>
                                        <form method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                                            <input type="hidden" name="cancel_order_id" value="<?php echo $order['maDH']; ?>">
                                            <button type="submit" class="btn btn-link text-muted p-0 small text-decoration-none" style="font-size: 11px;">Hủy đơn hàng</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <a href="order-detail.php?id=<?php echo $order['maDH']; ?>" class="text-dark small fw-bold text-decoration-none">
                                    <i class="fa-solid fa-circle-info me-1"></i> Xem chi tiết
                                </a>
                                <span class="fw-bold" style="font-size: 1.1rem; color: #C4622D;"><?php echo number_format($order['tongTien']); ?>₫</span>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-box-open display-4 text-light mb-3"></i>
                            <p class="text-muted">Danh sách trống.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- --- TAB YÊU THÍCH --- -->
            <?php elseif ($tab == 'wishlist'): ?>
                <div class="wishlist-container">
                    <?php
                    // Lấy danh sách sản phẩm yêu thích (Join với bảng SanPham để lấy soLuong, giá, ảnh...)
                    $sql_wishlist = "SELECT p.maSP as id, p.ten as name, p.gia as price, p.hinhAnh as image, p.soLuong 
                                    FROM YeuThich y JOIN SanPham p ON y.maSP = p.maSP 
                                    WHERE y.maND = '$user_id' ORDER BY y.ngayTao DESC";
                    $res_wishlist = mysqli_query($conn, $sql_wishlist);
                    
                    if (mysqli_num_rows($res_wishlist) > 0): ?>
                        <div class="row g-4" id="wishlist-grid">
                            <?php while ($item = mysqli_fetch_assoc($res_wishlist)): ?>
                                <div class="col-6 col-md-4 col-lg-3 wishlist-item-wrapper" id="wishlist-item-<?php echo $item['id']; ?>">
                                    <div class="product-card-v3 h-100">
                                        <!-- PHẦN HÌNH ẢNH & NÚT TÁC VỤ (Giống Shop.php) -->
                                        <div class="product-img-wrapper-v3">
                                            <a href="detail.php?id=<?php echo $item['id']; ?>">
                                                <img src="<?php echo $item['image']; ?>" class="product-img-main-v3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            </a>

                                            <!-- Nút trái tim đỏ (Xóa yêu thích bằng AJAX trong main.js) -->
                                            <button class="btn-wishlist-v3 remove-from-wishlist" data-id="<?php echo $item['id']; ?>" title="Xóa khỏi yêu thích">
                                                <i class="fa-solid text-danger fa-heart"></i>
                                            </button>

                                            <!-- FORM THÊM VÀO GIỎ HÀNG (Lấy y hệt cấu trúc shop.php) -->
                                            <form action="cart.php" method="POST" class="quick-add-form-v3">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <?php if ($item['soLuong'] > 0): ?>
                                                    <button type="submit" name="add_to_cart" class="btn-quick-add-v3">
                                                        <i class="fa-solid fa-cart-plus me-2"></i>THÊM VÀO GIỎ HÀNG
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn-quick-add-v3 disabled" style="background: #999; cursor: not-allowed;">
                                                        <i class="fa-solid fa-xmark me-2"></i>HẾT HÀNG
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </div>

                                        <!-- THÔNG TIN SẢN PHẨM (Căn giữa giống Shop.php) -->
                                        <div class="product-info-v3 text-center mt-3">
                                            <h6 class="product-name-v3 mb-1">
                                                <a href="detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark fw-bold">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h6>
                                            <p class="product-price-v3 mb-0"><?php echo number_format($item['price']); ?>₫</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-regular fa-heart display-1 text-light mb-4"></i>
                            <h5 class="text-muted fw-light">Danh sách yêu thích của bạn đang trống</h5>
                            <a href="products.php" class="btn btn-dark rounded-pill px-5 py-3 mt-3 fw-bold">KHÁM PHÁ NGAY</a>
                        </div>
                    <?php endif; ?>
                </div>
            <!-- --- TAB TÀI KHOẢN (Sử dụng label-minimal & input-mimic của bạn) --- -->
            <?php elseif ($tab == 'profile'): ?>
                <div class="profile-details max-w-md">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Thông tin cá nhân</h5>
                        <?php if (!$is_editing): ?>
                            <a href="profile.php?tab=profile&edit=1" class="btn btn-outline-dark btn-sm rounded-pill px-3">Chỉnh sửa</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_editing): ?>
                        <!-- GIAO DIỆN CHỈNH SỬA -->
                        <form action="" method="POST">
                            <div class="mb-4">
                                <label class="label-minimal">HỌ VÀ TÊN</label>
                                <input type="text" name="ten" class="form-control" style="background-color: #FAF7F2; border-radius:12px; border:1px solid #EDE8DF;" value="<?php echo htmlspecialchars($user['ten']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="label-minimal">SỐ ĐIỆN THOẠI</label>
                                <input type="text" name="soDienThoai" class="form-control" style="background-color: #FAF7F2; border-radius:12px; border:1px solid #EDE8DF;" value="<?php echo htmlspecialchars($user['soDienThoai']); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="label-minimal">ĐỊA CHỈ</label>
                                <textarea name="diaChi" class="form-control" style="background-color: #FAF7F2; border-radius:12px; border:1px solid #EDE8DF;" rows="3"><?php echo htmlspecialchars($user['diaChi']); ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="update_profile" class="btn btn-dark rounded-pill px-4">Lưu thay đổi</button>
                                <a href="profile.php?tab=profile" class="btn btn-light rounded-pill px-4">Hủy</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- GIAO DIỆN HIỂN THỊ (DÙNG CLASS CSS CỦA BẠN) -->
                        <div class="mb-4">
                            <label class="label-minimal">HỌ VÀ TÊN</label>
                            <p class="input-mimic"><?php echo htmlspecialchars($user['ten']); ?></p>
                        </div>
                        <div class="mb-4">
                            <label class="label-minimal">SỐ ĐIỆN THOẠI</label>
                            <p class="input-mimic"><?php echo htmlspecialchars($user['soDienThoai'] ?: '—'); ?></p>
                        </div>
                        <div class="mb-4">
                            <label class="label-minimal">ĐỊA CHỈ</label>
                            <p class="input-mimic"><?php echo htmlspecialchars($user['diaChi'] ?: '—'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>