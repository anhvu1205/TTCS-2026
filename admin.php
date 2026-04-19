<?php
session_start();
require_once 'includes/db.php';

// 1. XỬ LÝ CÁC HÀNH ĐỘNG POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Xử lý đơn hàng
    if (isset($_POST['set_pending_id'])) {
        $id = $_POST['set_pending_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='CHO_XAC_NHAN' WHERE maDH='$id'");
    } elseif (isset($_POST['set_shipping_id'])) {
        $id = $_POST['set_shipping_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='DA_GIAO_HANG' WHERE maDH='$id'");
    } elseif (isset($_POST['set_complete_id'])) {
        $id = $_POST['set_complete_id'];
        mysqli_query($conn, "UPDATE DonHang SET trangThai='HOAN_TAT' WHERE maDH='$id'");
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
    // Xử lý Mã giảm giá theo discount_codes
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
    exit();
}

// 2. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header("Location: shop.php");
    exit();
}

$tab = $_GET['tab'] ?? 'overview';
include 'includes/header.php';

// 3. LẤY DỮ LIỆU THỐNG KÊ
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
                                    <span class="badge-status <?php echo $stt_class; ?>"><?php echo $o['trangThai']; ?></span>
                                </div>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <p class="text-center text-muted py-4 small">Chưa có đơn hàng nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($tab == 'products'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <input type="text" class="form-control border-0 rounded-pill shadow-sm w-50" style="background-color:var(--admin-card);" placeholder="Tìm sản phẩm...">
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
                <div class="stat-card-modern shadow-sm p-0 overflow-hidden">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color:#D4CEBE;">
                            <tr class="extra-small text-muted">
                                <th class="ps-4">ĐƠN HÀNG</th>
                                <th>TỔNG CỘNG</th>
                                <th>TRẠNG THÁI</th>
                                <th class="text-end pe-4">HÀNH ĐỘNG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res_o = mysqli_query($conn, "SELECT * FROM DonHang ORDER BY ngayTao DESC");
                            while ($o = mysqli_fetch_assoc($res_o)):
                            ?>
                                <tr class="small border-bottom" style="border-color:#D4CEBE !important;">
                                    <td class="ps-4 py-3 fw-bold">#<?php echo substr($o['maDH'], -8); ?> - <?php echo $o['hoTen']; ?></td>
                                    <td><?php echo number_format($o['tongTien']); ?>₫</td>
                                    <td><span class="badge-status <?php echo strtolower($o['trangThai']); ?>"><?php echo $o['trangThai']; ?></span></td>
                                    <td class="text-end pe-4">
                                        <form method="POST" class="d-inline">
                                            <?php if ($o['trangThai'] == 'CHUA_THANH_TOAN'): ?>
                                                <button type="submit" name="set_pending_id" value="<?php echo $o['maDH']; ?>" class="btn btn-sm btn-dark">Xác nhận thanh toán</button>
                                            <?php elseif ($o['trangThai'] == 'CHO_XAC_NHAN'): ?>
                                                <button type="submit" name="set_shipping_id" value="<?php echo $o['maDH']; ?>" class="btn btn-sm btn-primary">Giao hàng</button>
                                            <?php elseif ($o['trangThai'] == 'DA_GIAO_HANG'): ?>
                                                <button type="submit" name="set_complete_id" value="<?php echo $o['maDH']; ?>" class="btn btn-sm btn-success">Hoàn tất</button>
                                            <?php endif; ?>
                                        </form>
                                        <a href="order-detail.php?id=<?php echo $o['maDH']; ?>" class="btn btn-sm btn-light border ms-1"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
                            <?php
                            endwhile;
                        else:
                            ?>
                            <p class="text-center text-muted py-5 small">Chưa có mã giảm giá nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>