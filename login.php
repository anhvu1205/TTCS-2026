<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; 

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['PASSWORD'])) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: shop.php"); 
                exit();
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Email không tồn tại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=22">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card shadow-lg">
            <div class="auth-header text-center">
                <a href="shop.php">
                    <img src="assets/img/logo.jpg" alt="Logo" class="logo-auth-fixed">
                </a>
                <h2 class="auth-title">ĐĂNG NHẬP</h2>
            </div>

            <?php if(isset($error)) echo "<div class='alert alert-danger py-2 small text-center'>$error</div>"; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">EMAIL</label>
                    <input type="email" name="email" class="form-control auth-input" placeholder="Nhập email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">MẬT KHẨU</label>
                    <input type="password" name="password" class="form-control auth-input" placeholder="Nhập mật khẩu" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Ghi nhớ</label>
                    </div>
                    <a href="#" class="text-decoration-none small text-danger fw-bold">Quên mật khẩu?</a>
                </div>
                <button type="submit" name="login" class="btn-auth-red w-100">ĐĂNG NHẬP</button>
            </form>

            <p class="text-center mt-4 small text-muted">
                Bạn chưa có tài khoản? <a href="register.php" class="text-danger fw-bold text-decoration-none">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</body>
</html>