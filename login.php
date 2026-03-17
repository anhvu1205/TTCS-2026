<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Trong thực tế nên dùng password_hash

    // Truy vấn kiểm tra người dùng trong bảng NguoiDung
    $sql = "SELECT * FROM NguoiDung WHERE tenDangNhap = '$username' AND matKhau = '$password' AND trangThai = 'ACTIVE'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Lưu thông tin vào session
        $_SESSION['user'] = [
            'id' => $user['maND'],
            'username' => $user['tenDangNhap'],
            'name' => $user['ten'],
            'role' => $user['vaiTro']
        ];

        // Chuyển hướng về trang chủ sau khi đăng nhập thành công
        header("Location: profile.php");
        exit();
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
    }
}

include 'includes/header.php';
?>

<main class="login-page-wrapper py-5" style="background-color: #FAFAF9; min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card p-4 p-sm-5 bg-white shadow-lg border-0" style="border-radius: 2rem;">
                    
                    <div class="text-center mb-5">
                        <h1 class="fw-bold h2 mb-2">Đăng nhập</h1>
                        <p class="text-muted small">Chào mừng bạn trở lại</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4" style="border-radius: 10px; background-color: #fff5f5; color: #e53e3e;">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="login-form">
                        <div class="mb-4">
                            <label for="username" class="form-label small fw-bold text-secondary">TÊN ĐĂNG NHẬP</label>
                            <input type="text" name="username" id="username" class="form-control custom-input" placeholder="Nhập tài khoản..." required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-bold text-secondary">MẬT KHẨU</label>
                            <input type="password" name="password" id="password" class="form-control custom-input" placeholder="Nhập mật khẩu..." required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label small text-muted" for="remember">Ghi nhớ tôi</label>
                            </div>
                            <a href="#" class="small text-muted text-decoration-none hover-gold">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 fw-bold mb-4 shadow-sm" style="border-radius: 12px; letter-spacing: 1px;">
                            ĐĂNG NHẬP
                        </button>
                    </form>

                    <p class="text-center text-muted small mb-0">
                        Chưa có tài khoản? 
                        <a href="register.php" class="fw-bold text-dark text-decoration-none border-bottom border-dark pb-1">Đăng ký ngay</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>