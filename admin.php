<?php
session_start();
require_once 'includes/db.php';

function getStatusClass($status) {
    $map = [
        'Chưa thanh toán' => 'status-chua_thanh_toan',
        'Chờ xác nhận'    => 'status-cho_xac_nhan',
        'Đang giao hàng'  => 'status-dang_giao_hang',
        'Đã giao hàng'    => 'status-da_giao_hang',
        'Hoàn tất'        => 'status-hoan_tat',
        'Đã hủy'          => 'status-da_huy',
        'Chờ kiểm tra hoàn tiền'   => 'status-cho_hoan_tien',
        'Đã hoàn tiền'    => 'status-da_hoan_tien'
    ];
    return $map[$status] ?? 'status-default';
}

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header("Location: shop.php");
    exit();
}

$tab = $_GET['tab'] ?? 'overview';

// 2. XỬ LÝ ĐÁNH DẤU CHAT ĐÃ XỬ LÝ
if (isset($_GET['mark_chat_answered'])) {
    $id = (int)$_GET['mark_chat_answered'];
    mysqli_query($conn, "UPDATE ChatbotRequests SET status = 'answered' WHERE id = $id");
    header("Location: admin.php?tab=chatbot");
    exit();
}

// Xử lý quản lý đánh giá sản phẩm
if (isset($_GET['hide_review'])) {
    $id = (int)$_GET['hide_review'];
    mysqli_query($conn, "UPDATE ProductReviews SET status = 'hidden' WHERE id = $id");
    header("Location: admin.php?tab=reviews");
    exit();
}

if (isset($_GET['show_review'])) {
    $id = (int)$_GET['show_review'];
    mysqli_query($conn, "UPDATE ProductReviews SET status = 'visible' WHERE id = $id");
    header("Location: admin.php?tab=reviews");
    exit();
}

if (isset($_GET['delete_review'])) {
    $id = (int)$_GET['delete_review'];
    mysqli_query($conn, "DELETE FROM ProductReviews WHERE id = $id");
    header("Location: admin.php?tab=reviews");
    exit();
}

if (isset($_GET['toggle_home_review'])) {
    $id = (int)$_GET['toggle_home_review'];
    mysqli_query($conn, "UPDATE HomeReviews SET status = IF(status = 1, 0, 1) WHERE id = $id");
    header("Location: admin.php?tab=reviews");
    exit();
}

if (isset($_GET['delete_home_review'])) {
    $id = (int)$_GET['delete_home_review'];
    mysqli_query($conn, "DELETE FROM HomeReviews WHERE id = $id");
    header("Location: admin.php?tab=reviews");
    exit();
}

// 3. XỬ LÝ POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Admin trả lời khách trong chatbot
    if (isset($_POST['admin_reply_chat'])) {
        $session_id = mysqli_real_escape_string($conn, $_POST['session_id']);
        $reply_message = mysqli_real_escape_string($conn, trim($_POST['reply_message']));

        if ($session_id !== '' && $reply_message !== '') {
            mysqli_query($conn, "
                INSERT INTO ChatbotMessages (session_id, maND, sender, message)
                VALUES ('$session_id', NULL, 'admin', '$reply_message')
            ");

            mysqli_query($conn, "
                INSERT INTO ChatbotSessions (session_id, mode)
                VALUES ('$session_id', 'admin')
                ON DUPLICATE KEY UPDATE mode='admin'
            ");

            mysqli_query($conn, "
                UPDATE ChatbotRequests
                SET status='answered'
                WHERE session_id='$session_id'
            ");
        }

        header("Location: admin.php?tab=chatbot");
        exit();
    }

    // Trả session về bot
    if (isset($_POST['return_bot_chat'])) {
        $session_id = mysqli_real_escape_string($conn, $_POST['session_id']);

        mysqli_query($conn, "
            INSERT INTO ChatbotSessions (session_id, mode)
            VALUES ('$session_id', 'bot')
            ON DUPLICATE KEY UPDATE mode='bot'
        ");

        header("Location: admin.php?tab=chatbot");
        exit();
    }

    // Thêm review khách hàng hiển thị random ở Home
    if (isset($_POST['add_home_review'])) {
        $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
        $content = mysqli_real_escape_string($conn, trim($_POST['content']));
        $rating = (int)($_POST['rating'] ?? 5);
        $rating = max(1, min(5, $rating));

        if ($customer_name !== '' && $content !== '') {
            mysqli_query($conn, "
                INSERT INTO HomeReviews (customer_name, content, rating, status)
                VALUES ('$customer_name', '$content', $rating, 1)
            ");
        }

        header("Location: admin.php?tab=reviews");
        exit();
    }

    // Xử lý đơn hàng
    if (isset($_POST['set_pending_id'])) {
        $id = $_POST['set_pending_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='Chờ xác nhận' WHERE maDH='$id'");
    } elseif (isset($_POST['set_shipping_id'])) {
        $id = $_POST['set_shipping_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='Đang giao hàng' WHERE maDH='$id'");
    } elseif (isset($_POST['set_complete_id'])) {
        $id = $_POST['set_complete_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='Hoàn tất' WHERE maDH='$id'");
    } elseif (isset($_POST['confirm_refund_id'])) {
        $order_id = $_POST['confirm_refund_id'];
        $res_items = mysqli_query($conn, "SELECT maSP, soLuong FROM ChiTietDonHang WHERE maDH='$order_id'");
        while ($item = mysqli_fetch_assoc($res_items)) {
            $p_id = $item['maSP'];
            $qty = $item['soLuong'];
            mysqli_query($conn, "UPDATE SanPham SET soLuong = soLuong + $qty WHERE maSP='$p_id'");
        }
        mysqli_query($conn, "UPDATE DonHang SET trangThai='DA_HUY' WHERE maDH='$order_id'");
    }

    // Xử lý người dùng
    elseif (isset($_POST['user_action'])) {
        $user_id = $_POST['target_user_id'];
        $action = $_POST['user_action'];

        if ($action == 'delete') {
            mysqli_query($conn, "DELETE FROM NguoiDung WHERE maND='$user_id' AND vaiTro != 'ADMIN'");
        } elseif ($action == 'lock') {
            mysqli_query($conn, "UPDATE NguoiDung SET trangThai='LOCKED' WHERE maND='$user_id'");
        } elseif ($action == 'unlock') {
            mysqli_query($conn, "UPDATE NguoiDung SET trangThai='ACTIVE' WHERE maND='$user_id'");
        }
    }

    // Xử lý admin reply review
    elseif (isset($_POST['admin_reply_review'])) {
        $review_id = (int)$_POST['review_id'];
        $admin_reply = trim($_POST['admin_reply']);

        if ($review_id > 0 && !empty($admin_reply)) {
            $admin_reply_safe = mysqli_real_escape_string($conn, $admin_reply);
            mysqli_query($conn, "
                UPDATE ProductReviews
                SET admin_reply = '$admin_reply_safe', admin_reply_at = NOW()
                WHERE id = $review_id
            ");
        }

        header("Location: admin.php?tab=reviews");
        exit();
    }

    // Xử lý mã giảm giá
    elseif (isset($_POST['add_discount'])) {
        $code = strtoupper(mysqli_real_escape_string($conn, trim($_POST['code'])));
        $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
        $discount_value = (float)($_POST['discount_value'] ?? 0);
        $min_order_value = (float)($_POST['min_order_value'] ?? 0);

        $max_discount = ($_POST['max_discount'] !== '') ? (float)$_POST['max_discount'] : null;
        $usage_limit = ($_POST['usage_limit'] !== '') ? (int)$_POST['usage_limit'] : null;

        $start_date = !empty($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : null;
        $end_date = !empty($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : null;

        $check_code = mysqli_query($conn, "SELECT id FROM discount_codes WHERE code = '$code' LIMIT 1");

        if ($check_code && mysqli_num_rows($check_code) == 0) {
            $max_discount_sql = is_null($max_discount) ? "NULL" : $max_discount;
            $usage_limit_sql = is_null($usage_limit) ? "NULL" : $usage_limit;
            $start_date_sql = is_null($start_date) ? "NULL" : "'$start_date'";
            $end_date_sql = is_null($end_date) ? "NULL" : "'$end_date'";

            mysqli_query($conn, "
                INSERT INTO discount_codes
                (code, discount_type, discount_value, min_order_value, max_discount, usage_limit, used_count, start_date, end_date, is_active)
                VALUES
                ('$code', '$discount_type', $discount_value, $min_order_value, $max_discount_sql, $usage_limit_sql, 0, $start_date_sql, $end_date_sql, 1)
            ");
        }
    } elseif (isset($_POST['toggle_discount'])) {
        $id = (int)$_POST['discount_id'];
        $current_status = (int)$_POST['current_status'];
        $new_status = ($current_status === 1) ? 0 : 1;

        mysqli_query($conn, "UPDATE discount_codes SET is_active = $new_status WHERE id = $id");
    } elseif (isset($_POST['delete_discount'])) {
        $id = (int)$_POST['discount_id'];
        mysqli_query($conn, "DELETE FROM discount_codes WHERE id = $id");
    }

    $redirect_tab = $_GET['tab'] ?? 'overview';
    header("Location: admin.php?tab=$redirect_tab");
    if (isset($_POST['update_order_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        
        // Cập nhật trạng thái mới vào Database
        $sql = "UPDATE DonHang SET trangThai = '$new_status' WHERE maDH = '$order_id'";
        mysqli_query($conn, $sql);
        
        // Chuyển hướng về tab đơn hàng để thấy kết quả
        header("Location: admin.php?tab=orders");
        exit();
    }
    exit();
}

include 'includes/header.php';

// 4. LẤY DỮ LIỆU THỐNG KÊ
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(tongTien) as total FROM DonHang WHERE trangThai != 'DA_HUY'"))['total'] ?: 0;
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT maDH FROM DonHang"));
$pending_orders = mysqli_num_rows(mysqli_query($conn, "SELECT maDH FROM DonHang WHERE trangThai = 'CHO_XAC_NHAN'"));
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT maSP FROM SanPham"));
?>

<main class="py-5 admin-page" style="padding-top:120px !important;">
    <div class="container max-w-7xl mx-auto px-4">

        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h1 class="h3 fw-light mb-1" style="font-family:'Cormorant Garamond', serif;">Admin Dashboard</h1>
                <p class="text-muted small mb-0">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong></p>
            </div>
            <a href="controll/logout.php" class="btn-logout-minimal shadow-sm">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
            </a>
        </div>

        <div class="nav nav-tabs-admin mb-5 shadow-sm">
            <a href="admin.php?tab=overview" class="nav-link <?php echo $tab == 'overview' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line me-2"></i>Tổng quan
            </a>

            <a href="admin.php?tab=products" class="nav-link <?php echo $tab == 'products' ? 'active' : ''; ?>">
                <i class="fa-solid fa-box me-2"></i>Sản phẩm
            </a>

            <a href="admin.php?tab=orders" class="nav-link <?php echo $tab == 'orders' ? 'active' : ''; ?>">
                <i class="fa-solid fa-shopping-bag me-2"></i>Đơn hàng
            </a>

            <a href="admin.php?tab=users" class="nav-link <?php echo $tab == 'users' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users me-2"></i>Tài khoản
            </a>

            <a href="admin.php?tab=discounts" class="nav-link <?php echo $tab == 'discounts' ? 'active' : ''; ?>">
                <i class="fa-solid fa-tag me-2"></i>Mã giảm giá
            </a>

            <a href="admin.php?tab=reviews" class="nav-link <?php echo $tab == 'reviews' ? 'active' : ''; ?>">
                <i class="fa-solid fa-star me-2"></i>Đánh giá
            </a>

            <a href="admin.php?tab=chatbot" class="nav-link <?php echo $tab == 'chatbot' ? 'active' : ''; ?>">
                <i class="fa-solid fa-comments me-2"></i>Chatbot
            </a>
        </div>

        <div class="tab-content">

            <?php if ($tab == 'overview'): ?>

                <div class="row g-4 mb-5">
                    <div class="col-6 col-md-3">
                        <div class="stat-card-modern shadow-sm">
                            <div class="icon-wrapper-v2" style="background: rgba(196, 98, 45, 0.15); color: #C4622D;"><i class="fa-solid fa-arrow-trend-up"></i></div>
                            <p class="label-v2">Tổng doanh thu</p>
                            <h2 class="value-v2"><?php echo number_format($revenue); ?>₫</h2>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="stat-card-modern shadow-sm">
                            <div class="icon-wrapper-v2" style="background: rgba(92, 102, 80, 0.15); color: #5C6650;"><i class="fa-solid fa-file-invoice"></i></div>
                            <p class="label-v2">Tổng đơn hàng</p>
                            <h2 class="value-v2"><?php echo $total_orders; ?></h2>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="stat-card-modern shadow-sm">
                            <div class="icon-wrapper-v2" style="background: rgba(245, 158, 11, 0.15); color: #F59E0B;"><i class="fa-solid fa-box-archive"></i></div>
                            <p class="label-v2">Đơn chờ xử lý</p>
                            <h2 class="value-v2"><?php echo $pending_orders; ?></h2>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="stat-card-modern shadow-sm">
                            <div class="icon-wrapper-v2" style="background: rgba(59, 130, 246, 0.15); color: #3B82F6;"><i class="fa-solid fa-cubes"></i></div>
                            <p class="label-v2">Sản phẩm</p>
                            <h2 class="value-v2"><?php echo $total_products; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="admin-panel-v2 shadow-sm mb-5">
                    <h3 class="h6 fw-bold mb-4" style="color: #1A1A1A;">Doanh thu 7 ngày qua</h3>
                    <div style="height: 250px;">
                        <canvas id="revenueChart"
                            data-labels='<?php
                                            $labels = [];
                                            for ($i = 6; $i >= 0; $i--) {
                                                $labels[] = date('d/m', strtotime("-$i days"));
                                            }
                                            echo json_encode($labels);
                                            ?>'
                            data-revenue='<?php
                                            $data = [];
                                            for ($i = 6; $i >= 0; $i--) {
                                                $d = date('Y-m-d', strtotime("-$i days"));
                                                $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(tongTien) as t FROM DonHang WHERE DATE(ngayTao) = '$d' AND trangThai != 'DA_HUY'"))['t'] ?: 0;
                                                $data[] = (int)$rev;
                                            }
                                            echo json_encode($data);
                                            ?>'>
                        </canvas>
                    </div>
                </div>

                <div class="admin-panel-v2 shadow-sm">
                    <h3 class="h6 fw-bold mb-4" style="color: #1A1A1A;">Đơn hàng gần đây</h3>
                    <div class="recent-orders-list">
                        <?php
                        $res_recent = mysqli_query($conn, "SELECT * FROM DonHang ORDER BY ngayTao DESC LIMIT 5");
                        if ($res_recent && mysqli_num_rows($res_recent) > 0):
                            while ($o = mysqli_fetch_assoc($res_recent)):
                                $stt_class = 'status-' . strtolower($o['trangThai']);
                        ?>
                                <div class="recent-order-item d-flex align-items-center justify-content-between py-3 border-bottom last:border-0">
                                    <div>
                                        <p class="mb-0 fw-bold small text-dark">#<?php echo substr($o['maDH'], -8); ?> — <?php echo htmlspecialchars($o['hoTen']); ?></p>
                                        <p class="mb-0 extra-small text-muted"><?php echo number_format($o['tongTien']); ?>₫ · <?php echo date('d/m/Y', strtotime($o['ngayTao'])); ?></p>
                                    </div>
                                    <div class="text-end d-flex flex-column align-items-end">
                                        <!-- Nhóm Badge và Ghi chú vào đây để chúng xếp chồng lên nhau ở bên phải -->
                                        <span class="badge-status <?php echo getStatusClass($o['trangThai']); ?>">
                                            <?php echo $o['trangThai']; ?>
                                        </span>

                                        <!-- Ghi chú chưa thanh toán -->
                                        <div class="mt-1">
                                            <?php 
                                            $st = $o['trangThai'] ?? $curr_st; // Tùy file đang dùng biến nào
                                            
                                            // Đơn COD chưa xong -> CHƯA THANH TOÁN (Màu đỏ)
                                            if ($o['phuongThucThanhToan'] == 'COD' && !in_array($st, ['Hoàn tất', 'Đã hủy', 'Đã giao'])): ?>
                                                <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                            
                                            <?php 
                                            // Đơn QR đã gửi ảnh -> ĐANG KIỂM TRA (Màu xanh dương/tím)
                                            elseif ($o['phuongThucThanhToan'] == 'QR' && $st == 'Chờ xác nhận' && !empty($o['minhChungThanhToan'])): ?>
                                                <small class="text-primary fw-bold" style="font-size: 9px;">[ ĐANG KIỂM TRA ]</small>
                                            
                                            <?php 
                                            // Đơn QR chưa gửi ảnh -> CHƯA THANH TOÁN
                                            elseif ($o['phuongThucThanhToan'] == 'QR' && $st == 'Chưa thanh toán'): ?>
                                                <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <p class="text-center text-muted py-4 small">Chưa có đơn hàng nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($tab == 'products'): ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="add.php" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color:var(--admin-primary);">+ Thêm sản phẩm</a>
                </div>

                <div class="stat-card-modern shadow-sm overflow-hidden p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color:#D4CEBE;">
                            <tr class="extra-small text-muted">
                                <th class="ps-4">SẢN PHẨM</th>
                                <th>GIÁ</th>
                                <th>KHO</th>
                                <th class="text-end pe-4">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res_p = mysqli_query($conn, "SELECT * FROM SanPham ORDER BY maSP DESC");
                            while ($p = mysqli_fetch_assoc($res_p)):
                            ?>
                                <tr class="small border-bottom" style="border-color:#D4CEBE !important;">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo $p['hinhAnh']; ?>" width="40" height="50" style="object-fit:cover; border-radius:8px;">
                                            <span><?php echo $p['ten']; ?></span>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger"><?php echo number_format($p['gia']); ?>₫</td>
                                    <td><?php echo $p['soLuong']; ?></td>
                                    <td class="text-end pe-4">
                                        <a href="edit.php?id=<?php echo $p['maSP']; ?>" class="btn btn-sm btn-light border me-1"><i class="fa-solid fa-pencil"></i></a>
                                        <a href="controll/delete.php?id=<?php echo $p['maSP']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Xóa?')"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab == 'orders'): ?>
                <!-- 1. Thanh tìm kiếm và Bộ lọc giống React -->
                <div class="d-flex align-items-center gap-3 mb-5 flex-wrap">
                    <select id="statusFilter" class="rounded-xl px-4 py-2 text-sm border-0 focus:outline-none" 
                            style="background-color: #EDE8DF; color: #1A1A1A;">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="Chờ xác nhận">Chờ xác nhận</option>
                        <option value="Đang giao hàng">Đang giao hàng</option>
                        <option value="Đã giao hàng">Đã giao hàng</option>
                        <option value="Hoàn tất">Hoàn tất</option>
                        <option value="Đã hủy">Đã hủy</option>
                        <option value="Chờ kiểm tra hoàn tiền">Chờ kiểm tra hoàn tiền</option>
                        <option value="Đã hoàn tiền">Đã hoàn tiền</option>
                    </select>
                </div>

                <!-- 2. Danh sách Đơn hàng dạng Card -->
                <div id="orderContainer" class="space-y-3">
                    <?php
                    $res_o = mysqli_query($conn, "SELECT * FROM DonHang ORDER BY ngayTao DESC");
                    if (mysqli_num_rows($res_o) > 0):
                        while ($o = mysqli_fetch_assoc($res_o)):
                            $curr_st = $o['trangThai'];
                            // Mapping màu sắc dựa trên file css bạn có
                            $st_color = '#6B7280'; // mặc định
                            if($curr_st == 'Chờ xác nhận') $st_color = '#F59E0B';
                            if($curr_st == 'Đã giao hàng') $st_color = '#3B82F6';
                            if($curr_st == 'Hoàn tất') $st_color = '#10B981';
                            if($curr_st == 'Đã hủy') $st_color = '#EF4444';
                            if($curr_st == 'Đang giao hàng') $st_color = '#e678d7';
                            if($curr_st == 'Chờ kiểm tra hoàn tiền') $st_color = '#6366F1';
                            if($curr_st == 'Đã hoàn tiền') $st_color = '#7be0ff';
                    ?>
                        <div class="order-card-v2 shadow-sm order-item-node" data-status="<?php echo $curr_st; ?>">
                            <div class="d-flex items-start justify-content-between gap-4 mb-3">
                                <div>
                                    <p class="text-xs font-medium mb-0" style="color: #8C8279;">#<?php echo strtoupper(substr($o['maDH'], -8)); ?></p>
                                    <p class="text-sm font-semibold mt-1 mb-0" style="color: #1A1A1A;"><?php echo htmlspecialchars($o['hoTen']); ?></p>
                                    <p class="text-xs mt-1 mb-0" style="color: #8C8279;">
                                        <i class="fa-solid fa-phone me-1"></i> <?php echo $o['soDienThoai']; ?> · 
                                        <i class="fa-solid fa-location-dot me-1"></i> <?php echo $o['diaChi']; ?>
                                    </p>
                                </div>
                                
                                <div class="flex-shrink-0 text-end">
                                    <!-- Select trạng thái -->
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $o['maDH']; ?>">
                                        <select name="new_status" onchange="this.form.submit()" 
                                                class="status-select-v2" 
                                                style="background-color: <?php echo $st_color; ?>;">
                                            <option value="Chờ xác nhận" <?php echo $curr_st == 'Chờ xác nhận' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                            <option value="Đang giao hàng" <?php echo $curr_st == 'Đang giao hàng' ? 'selected' : ''; ?>>Đang giao hàng</option>
                                            <option value="Đã giao hàng" <?php echo $curr_st == 'Đã giao hàng' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                            <option value="Hoàn tất" <?php echo $curr_st == 'Hoàn tất' ? 'selected' : ''; ?>>Hoàn tất</option>
                                            <option value="Chờ kiểm tra hoàn tiền" <?php echo $curr_st == 'Chờ kiểm tra hoàn tiền' ? 'selected' : ''; ?>>Chờ kiểm tra hoàn tiền</option>
                                            <option value="Đã hoàn tiền" <?php echo $curr_st == 'Đã hoàn tiền' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                                            <option value="Đã hủy" <?php echo $curr_st == 'Đã hủy' ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                        <input type="hidden" name="update_order_status" value="1">
                                    </form>

                                    <!-- Ghi chú chưa thanh toán -->
                                    <div class="mt-1">
                                        <?php 
                                        $st = $o['trangThai'] ?? $curr_st; // Tùy file đang dùng biến nào
                                        
                                        // Đơn COD chưa xong -> CHƯA THANH TOÁN (Màu đỏ)
                                        if ($o['phuongThucThanhToan'] == 'COD' && !in_array($st, ['Hoàn tất', 'Đã hủy', 'Đã giao'])): ?>
                                            <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                        
                                        <?php 
                                        // Đơn QR đã gửi ảnh -> ĐANG KIỂM TRA (Màu xanh dương/tím)
                                        elseif ($o['phuongThucThanhToan'] == 'QR' && $st == 'Chờ xác nhận' && !empty($o['minhChungThanhToan'])): ?>
                                            <small class="text-primary fw-bold" style="font-size: 9px;">[ ĐANG KIỂM TRA ]</small>
                                        
                                        <?php 
                                        // Đơn QR chưa gửi ảnh -> CHƯA THANH TOÁN
                                        elseif ($o['phuongThucThanhToan'] == 'QR' && $st == 'Chưa thanh toán'): ?>
                                            <small class="text-danger fw-bold" style="font-size: 9px;">[ CHƯA THANH TOÁN ]</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Danh sách sản phẩm con -->
                            <div class="py-2 border-top border-bottom" style="border-color: #D4CEBE;">
                                <?php
                                $dh_id = $o['maDH'];
                                $res_items = mysqli_query($conn, "SELECT c.*, s.ten FROM ChiTietDonHang c JOIN SanPham s ON c.maSP = s.maSP WHERE c.maDH = '$dh_id'");
                                while($it = mysqli_fetch_assoc($res_items)):
                                ?>
                                    <p class="product-line-item">
                                        • <?php echo $it['ten']; ?> × <?php echo $it['soLuong']; ?> 
                                        <?php if($it['kichCo']) echo "({$it['kichCo']})"; ?> 
                                        — <span class="text-dark fw-medium"><?php echo number_format($it['thanhTien']); ?>₫</span>
                                    </p>
                                <?php endwhile; ?>
                            </div>

                            <!-- Footer của Card -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-xs text-muted"><?php echo date('d/m/Y H:i', strtotime($o['ngayTao'])); ?></span>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-sm font-bold" style="color: #C4622D;">TỔNG: <?php echo number_format($o['tongTien']); ?>₫</span>
                                    <!-- Nút xem chi tiết giữ lại theo yêu cầu -->
                                    <a href="order-detail.php?id=<?php echo $o['maDH']; ?>" class="btn btn-sm btn-white bg-white rounded-lg shadow-sm border-0" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye" style="color: #5C5049;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <p class="text-center text-muted py-5">Không tìm thấy đơn hàng nào.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab == 'users'): ?>

                <div class="stat-card-modern shadow-sm p-0 overflow-hidden">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color:#D4CEBE;">
                            <tr class="extra-small text-muted">
                                <th class="ps-4">TÀI KHOẢN</th>
                                <th>TRẠNG THÁI</th>
                                <th class="text-end pe-4">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res_u = mysqli_query($conn, "SELECT * FROM NguoiDung WHERE vaiTro != 'ADMIN' ORDER BY maND DESC");
                            while ($u = mysqli_fetch_assoc($res_u)):
                            ?>
                                <tr class="small border-bottom" style="border-color:#D4CEBE !important;">
                                    <td class="ps-4 py-3">
                                        <strong><?php echo $u['ten']; ?></strong><br>
                                        <small class="text-muted"><?php echo $u['tenDangNhap']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge-status" style="background-color:<?php echo $u['trangThai'] == 'LOCKED' ? '#EF4444' : '#10B981'; ?>">
                                            <?php echo $u['trangThai']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['maND']; ?>">
                                            <button type="submit" name="user_action" value="<?php echo $u['trangThai'] == 'LOCKED' ? 'unlock' : 'lock'; ?>" class="btn btn-sm btn-light border">
                                                <i class="fa-solid <?php echo $u['trangThai'] == 'LOCKED' ? 'fa-unlock' : 'fa-lock'; ?>"></i>
                                            </button>
                                            <button type="submit" name="user_action" value="delete" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Xóa?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab == 'discounts'): ?>

                <div class="admin-panel-v2 shadow-sm mb-4">
                    <h3 class="h6 fw-bold mb-4"><i class="fa-solid fa-tag me-2"></i>Tạo mã giảm giá mới</h3>

                    <form method="POST" class="row g-3">
                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">MÃ CODE *</label>
                            <input type="text" name="code" class="form-control rounded-xl border-0 bg-white text-uppercase" placeholder="SALE20" required>
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">LOẠI *</label>
                            <select name="discount_type" class="form-control rounded-xl border-0 bg-white" required>
                                <option value="percent">Phần trăm</option>
                                <option value="fixed">Tiền cố định</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">GIÁ TRỊ *</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control rounded-xl border-0 bg-white" placeholder="10 hoặc 50000" required>
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">ĐƠN TỐI THIỂU</label>
                            <input type="number" step="0.01" name="min_order_value" class="form-control rounded-xl border-0 bg-white" placeholder="0">
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">GIẢM TỐI ĐA</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control rounded-xl border-0 bg-white" placeholder="chỉ cho %">
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">GIỚI HẠN</label>
                            <input type="number" name="usage_limit" class="form-control rounded-xl border-0 bg-white" placeholder="trống = không giới hạn">
                        </div>

                        <div class="col-md-3">
                            <label class="extra-small fw-bold text-muted mb-2">BẮT ĐẦU</label>
                            <input type="datetime-local" name="start_date" class="form-control rounded-xl border-0 bg-white">
                        </div>

                        <div class="col-md-3">
                            <label class="extra-small fw-bold text-muted mb-2">KẾT THÚC</label>
                            <input type="datetime-local" name="end_date" class="form-control rounded-xl border-0 bg-white">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" name="add_discount" class="btn btn-primary-v2 w-100 rounded-xl py-2">
                                <i class="fa-solid fa-plus me-2"></i>Tạo mã
                            </button>
                        </div>
                    </form>
                </div>

                <div class="admin-panel-v2 shadow-sm p-0 overflow-hidden">
                    <div class="px-4 py-3 border-bottom" style="border-color:#D4CEBE !important;">
                        <h3 class="h6 fw-bold mb-0">Danh sách mã giảm giá</h3>
                    </div>

                    <div class="divide-y">
                        <?php
                        $res_gg = mysqli_query($conn, "SELECT * FROM discount_codes ORDER BY created_at DESC");

                        if ($res_gg && mysqli_num_rows($res_gg) > 0):
                            while ($c = mysqli_fetch_assoc($res_gg)):
                                $is_active = ((int)$c['is_active'] === 1);
                        ?>
                                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom last:border-0" style="border-color:#D4CEBE !important;">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="discount-code-badge <?php echo $is_active ? 'active' : 'hidden'; ?>">
                                            <?php echo htmlspecialchars($c['code']); ?>
                                        </div>

                                        <div>
                                            <p class="mb-0 fw-bold small">
                                                <?php if ($c['discount_type'] === 'fixed'): ?>
                                                    Giảm <?php echo number_format($c['discount_value']); ?>₫
                                                <?php else: ?>
                                                    Giảm <?php echo number_format($c['discount_value']); ?>%
                                                    <?php if (!empty($c['max_discount'])): ?>
                                                        (tối đa <?php echo number_format($c['max_discount']); ?>₫)
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </p>

                                            <p class="mb-0 extra-small text-muted">
                                                Đơn tối thiểu <?php echo number_format($c['min_order_value']); ?>₫
                                                <?php if (!empty($c['usage_limit'])): ?>
                                                    • Giới hạn <?php echo (int)$c['usage_limit']; ?> lượt
                                                <?php endif; ?>
                                                • Đã dùng <?php echo (int)$c['used_count']; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge-status <?php echo $is_active ? 'status-hoan_tat' : 'status-chua_thanh_toan'; ?>" style="font-size: 9px;">
                                            <?php echo $is_active ? 'Đang hoạt động' : 'Đã tắt'; ?>
                                        </span>

                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="discount_id" value="<?php echo $c['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo (int)$c['is_active']; ?>">

                                            <button type="submit" name="toggle_discount" class="btn-icon-v2 border-0 bg-transparent">
                                                <i class="fa-solid <?php echo $is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted'; ?> fs-5"></i>
                                            </button>

                                            <button type="submit" name="delete_discount" class="btn-icon-v2 border-0 bg-transparent text-danger" onclick="return confirm('Xóa mã này?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <p class="text-center text-muted py-5 small">Chưa có mã giảm giá nào.</p>
                        <?php endif; ?>
                    </div>
                </div>


            <?php elseif ($tab == 'reviews'): ?>

                <div class="admin-panel-v2 shadow-sm mb-4">
                    <h3 class="h6 fw-bold mb-4"><i class="fa-solid fa-star me-2"></i>Đánh giá khách hàng ở Home</h3>

                    <form method="POST" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="extra-small fw-bold text-muted mb-2">TÊN KHÁCH</label>
                            <input type="text" name="customer_name" class="form-control rounded-xl border-0 bg-white" placeholder="Nguyễn An" required>
                        </div>

                        <div class="col-md-2">
                            <label class="extra-small fw-bold text-muted mb-2">SỐ SAO</label>
                            <select name="rating" class="form-control rounded-xl border-0 bg-white">
                                <option value="5">5 sao</option>
                                <option value="4">4 sao</option>
                                <option value="3">3 sao</option>
                                <option value="2">2 sao</option>
                                <option value="1">1 sao</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="extra-small fw-bold text-muted mb-2">NỘI DUNG</label>
                            <input type="text" name="content" class="form-control rounded-xl border-0 bg-white" placeholder="Chất vải rất mịn, form áo đẹp..." required>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="add_home_review" class="btn btn-primary-v2 w-100 rounded-xl py-2">
                                <i class="fa-solid fa-plus me-2"></i>Thêm
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive mb-5">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color:#D4CEBE;">
                                <tr class="extra-small text-muted">
                                    <th>ID</th>
                                    <th>KHÁCH</th>
                                    <th>NỘI DUNG</th>
                                    <th>SAO</th>
                                    <th>TRẠNG THÁI</th>
                                    <th class="text-end">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $home_reviews = mysqli_query($conn, "SELECT * FROM HomeReviews ORDER BY id DESC");
                                if ($home_reviews && mysqli_num_rows($home_reviews) > 0):
                                    while ($hr = mysqli_fetch_assoc($home_reviews)):
                                ?>
                                        <tr class="small">
                                            <td><?php echo $hr['id']; ?></td>
                                            <td><?php echo htmlspecialchars($hr['customer_name']); ?></td>
                                            <td style="max-width:420px;"><?php echo nl2br(htmlspecialchars($hr['content'])); ?></td>
                                            <td><?php echo (int)$hr['rating']; ?> ★</td>
                                            <td>
                                                <?php if ((int)$hr['status'] === 1): ?>
                                                    <span class="badge bg-success">Đang hiện</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Đang ẩn</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="admin.php?tab=reviews&toggle_home_review=<?php echo $hr['id']; ?>" class="btn btn-sm btn-warning">
                                                    <?php echo ((int)$hr['status'] === 1) ? 'Ẩn' : 'Hiện'; ?>
                                                </a>
                                                <a href="admin.php?tab=reviews&delete_home_review=<?php echo $hr['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa review home này?')">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4 small">Chưa có review Home nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="h6 fw-bold mb-4"><i class="fa-solid fa-comment-dots me-2"></i>Đánh giá sản phẩm</h3>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color:#D4CEBE;">
                                <tr class="extra-small text-muted">
                                    <th>ID</th>
                                    <th>SẢN PHẨM</th>
                                    <th>NGƯỜI ĐÁNH GIÁ</th>
                                    <th>SAO</th>
                                    <th>NỘI DUNG</th>
                                    <th>PHẢN HỒI ADMIN</th>
                                    <th>TRẠNG THÁI</th>
                                    <th>THỜI GIAN</th>
                                    <th class="text-end">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $product_reviews = mysqli_query($conn, "
                                    SELECT r.*, p.ten AS product_name
                                    FROM ProductReviews r
                                    LEFT JOIN SanPham p ON r.product_id = p.maSP
                                    ORDER BY r.created_at DESC
                                ");
                                if ($product_reviews && mysqli_num_rows($product_reviews) > 0):
                                    while ($rv = mysqli_fetch_assoc($product_reviews)):
                                ?>
                                        <tr class="small">
                                            <td><?php echo $rv['id']; ?></td>
                                            <td style="max-width:180px;"><?php echo htmlspecialchars($rv['product_name'] ?? 'Sản phẩm đã xóa'); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($rv['user_name']); ?>
                                                <?php if ((int)$rv['is_purchased'] === 1): ?>
                                                    <br><span class="badge bg-success mt-1">Đã mua hàng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo (int)$rv['rating']; ?> ★</td>
                                            <td style="max-width:300px;"><?php echo nl2br(htmlspecialchars($rv['content'])); ?></td>
                                            <td style="max-width:300px;">
                                                <?php if (!empty($rv['admin_reply'])): ?>
                                                    <div class="bg-light p-2 rounded small">
                                                        <strong>Admin:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($rv['admin_reply'])); ?>
                                                        <br><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($rv['admin_reply_at'])); ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <form method="POST" class="mt-2">
                                                        <input type="hidden" name="review_id" value="<?php echo $rv['id']; ?>">
                                                        <textarea name="admin_reply" class="form-control form-control-sm mb-2" rows="2" placeholder="Nhập phản hồi..."></textarea>
                                                        <button type="submit" name="admin_reply_review" class="btn btn-sm btn-primary">Reply</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rv['status'] === 'visible'): ?>
                                                    <span class="badge bg-success">Hiển thị</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Đã ẩn</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($rv['created_at'])); ?></td>
                                            <td class="text-end">
                                                <?php if ($rv['status'] === 'visible'): ?>
                                                    <a href="admin.php?tab=reviews&hide_review=<?php echo $rv['id']; ?>" class="btn btn-sm btn-warning">Ẩn</a>
                                                <?php else: ?>
                                                    <a href="admin.php?tab=reviews&show_review=<?php echo $rv['id']; ?>" class="btn btn-sm btn-success">Hiện</a>
                                                <?php endif; ?>
                                                <a href="admin.php?tab=reviews&delete_review=<?php echo $rv['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa đánh giá này?')">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4 small">Chưa có đánh giá sản phẩm nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($tab == 'chatbot'): ?>

                <div class="admin-panel-v2 shadow-sm mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h3 class="h6 fw-bold mb-1">
                                <i class="fa-solid fa-comments me-2"></i>Quản lý Chatbot
                            </h3>
                            <p class="text-muted small mb-0">Admin trả lời khách trực tiếp. Khi admin trả lời, bot sẽ tắt cho phiên chat đó.</p>
                        </div>
                    </div>

                    <h4 class="h6 fw-bold mb-3">Câu hỏi khách cần nhân viên xử lý</h4>

                    <div class="table-responsive mb-5">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color:#D4CEBE;">
                                <tr class="extra-small text-muted">
                                    <th>ID</th>
                                    <th>SESSION</th>
                                    <th>USER</th>
                                    <th>NỘI DUNG</th>
                                    <th>TRẠNG THÁI</th>
                                    <th>THỜI GIAN</th>
                                    <th class="text-end">TRẢ LỜI</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $requests = mysqli_query($conn, "SELECT * FROM ChatbotRequests ORDER BY id DESC LIMIT 100");

                                if ($requests && mysqli_num_rows($requests) > 0):
                                    while ($r = mysqli_fetch_assoc($requests)):
                                        $session_safe = mysqli_real_escape_string($conn, $r['session_id']);
                                        $modeRes = mysqli_query($conn, "SELECT mode FROM ChatbotSessions WHERE session_id='$session_safe' LIMIT 1");
                                        $mode = 'bot';
                                        if ($modeRes && mysqli_num_rows($modeRes) > 0) {
                                            $mode = mysqli_fetch_assoc($modeRes)['mode'];
                                        }
                                ?>
                                        <tr class="small">
                                            <td><?php echo $r['id']; ?></td>

                                            <td style="max-width:180px; word-break:break-all;">
                                                <?php echo htmlspecialchars($r['session_id']); ?>
                                                <br>
                                                <?php if ($mode == 'admin'): ?>
                                                    <span class="badge bg-danger mt-1">Admin đang chat</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary mt-1">Bot đang hoạt động</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?php echo $r['maND'] ?: 'Guest'; ?></td>

                                            <td style="max-width:360px;">
                                                <?php echo nl2br(htmlspecialchars($r['customer_message'])); ?>
                                            </td>

                                            <td>
                                                <?php if ($r['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Đã xử lý</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?php echo $r['created_at']; ?></td>

                                            <td class="text-end" style="min-width:360px;">
                                                <form method="POST" class="d-flex gap-2 justify-content-end mb-2">
                                                    <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($r['session_id']); ?>">

                                                    <input
                                                        type="text"
                                                        name="reply_message"
                                                        class="form-control form-control-sm"
                                                        placeholder="Nhập phản hồi cho khách..."
                                                        required>

                                                    <button type="submit" name="admin_reply_chat" class="btn btn-sm btn-success">
                                                        Gửi
                                                    </button>
                                                </form>

                                                <?php if ($mode == 'admin'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($r['session_id']); ?>">
                                                        <button type="submit" name="return_bot_chat" class="btn btn-sm btn-warning">
                                                            Trả về bot
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($r['status'] == 'pending'): ?>
                                                    <a href="admin.php?tab=chatbot&mark_chat_answered=<?php echo $r['id']; ?>" class="btn btn-sm btn-light border">
                                                        Đánh dấu xử lý
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4 small">
                                            Chưa có câu hỏi khó nào.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="h6 fw-bold mb-3">Lịch sử chat gần đây</h4>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color:#D4CEBE;">
                                <tr class="extra-small text-muted">
                                    <th>ID</th>
                                    <th>SESSION</th>
                                    <th>USER</th>
                                    <th>NGƯỜI GỬI</th>
                                    <th>NỘI DUNG</th>
                                    <th>THỜI GIAN</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $logs = mysqli_query($conn, "SELECT * FROM ChatbotMessages ORDER BY id DESC LIMIT 150");

                                if ($logs && mysqli_num_rows($logs) > 0):
                                    while ($log = mysqli_fetch_assoc($logs)):
                                ?>
                                        <tr class="small">
                                            <td><?php echo $log['id']; ?></td>

                                            <td style="max-width:180px; word-break:break-all;">
                                                <?php echo htmlspecialchars($log['session_id']); ?>
                                            </td>

                                            <td><?php echo $log['maND'] ?: 'Guest'; ?></td>

                                            <td>
                                                <?php if ($log['sender'] == 'user'): ?>
                                                    <span class="badge bg-primary">Khách</span>
                                                <?php elseif ($log['sender'] == 'admin'): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Bot</span>
                                                <?php endif; ?>
                                            </td>

                                            <td style="max-width:520px;">
                                                <?php echo nl2br(htmlspecialchars($log['message'])); ?>
                                            </td>

                                            <td><?php echo $log['created_at']; ?></td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4 small">
                                            Chưa có lịch sử chat.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>