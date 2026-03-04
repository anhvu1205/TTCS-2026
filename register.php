<?php
require_once 'includes/db.php';
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, PASSWORD) VALUES ('$username', '$email', '$password')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=12">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <a href="shop.php">
                <img src="assets/img/logo.jpg" alt="Logo" class="logo-auth mb-3">
            </a>
        </div>
        <h2>Đăng Ký</h2>
        <form action="register.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Tên người dùng</label>
                <input type="text" name="username" class="form-control" placeholder="Tên của bạn" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Email đăng ký" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu tối thiểu 6 ký tự" required>
            </div>
            <button type="submit" name="register" class="btn-auth-red">Đăng ký</button>
        </form>
        <p class="text-center mt-4 small text-muted">
            Đã có tài khoản? <a href="login.php" class="text-danger fw-bold text-decoration-none">Đăng nhập</a>
        </p>
    </div>
</body>
</html>