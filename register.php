<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    // 1. Kiểm tra mật khẩu trùng khớp
    if ($password !== $repassword) {
        $error = 'Mật khẩu nhập lại không trùng khớp.';
    } else {
        // 2. Kiểm tra tên đăng nhập đã tồn tại chưa
        $check_sql = "SELECT maND FROM NguoiDung WHERE tenDangNhap = '$username'";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $error = 'Tên đăng nhập này đã được sử dụng.';
        } else {
            // 3. Thực hiện thêm vào bảng NguoiDung
            // Lưu ý: Trong đồ án thật nên dùng password_hash($password, PASSWORD_DEFAULT)
            $sql_nd = "INSERT INTO NguoiDung (tenDangNhap, matKhau, ten, soDienThoai, vaiTro, trangThai) 
                       VALUES ('$username', '$password', '$fullName', '$phone', 'USER', 'ACTIVE')";
            
            if (mysqli_query($conn, $sql_nd)) {
                $new_maND = mysqli_insert_id($conn);
                
                // 4. Đồng thời tạo bản ghi trong bảng KhachHang
                $sql_kh = "INSERT INTO KhachHang (maND, diemTichLuy) VALUES ('$new_maND', 0)";
                mysqli_query($conn, $sql_kh);

                $success = 'Đăng ký tài khoản thành công! Đang chuyển hướng...';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
            }
        }
    }
}

include 'includes/header.php';
?>

<main class="register-page-wrapper py-5" style="background-color: #FAFAF9; min-height: 90vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="register-card p-4 p-sm-5 bg-white shadow-lg border-0" style="border-radius: 2rem;">
                    
                    <div class="text-center mb-5">
                        <h1 class="fw-bold h2 mb-2">Tạo tài khoản</h1>
                        <p class="text-muted small">Bắt đầu mua sắm cùng SIMPLE FIT ngay hôm nay!</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4" style="border-radius: 10px; background-color: #fff5f5; color: #e53e3e;">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 small mb-4" style="border-radius: 10px; background-color: #f0fff4; color: #38a169;">
                            <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" class="register-form space-y-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">HỌ VÀ TÊN</label>
                            <input type="text" name="fullName" class="form-control custom-input" placeholder="Họ và tên của bạn..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">TÊN ĐĂNG NHẬP</label>
                            <input type="text" name="username" class="form-control custom-input" placeholder="Tên đăng nhập..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">SỐ ĐIỆN THOẠI</label>
                            <input type="tel" name="phone" class="form-control custom-input" placeholder="Số điện thoại..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">MẬT KHẨU</label>
                            <input type="password" name="password" class="form-control custom-input" placeholder="Nhập mật khẩu..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">NHẬP LẠI MẬT KHẨU</label>
                            <input type="password" name="repassword" class="form-control custom-input" placeholder="Xác nhận mật khẩu..." required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 fw-bold mb-4 shadow-sm" style="border-radius: 12px; letter-spacing: 1px;">
                            ĐĂNG KÝ NGAY
                        </button>
                    </form>

                    <p class="text-center text-muted small mb-0">
                        Đã có tài khoản? 
                        <a href="login.php" class="fw-bold text-dark text-decoration-none border-bottom border-dark pb-1">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>