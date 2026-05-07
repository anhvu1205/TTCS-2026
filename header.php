<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPLE FIT - Thời trang tối giản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/auth-account.css">
    <link rel="stylesheet" href="assets/css/detail.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/cart-blog.css">
</head>

<body>

    <div id="announcement-bar" class="announcement-bar-container">
        <div class="announcement-content">
            <span id="announcement-text">🌿 Miễn phí vận chuyển cho đơn từ 500.000₫</span>
        </div>
        <button onclick="closeAnnouncement()" class="close-announcement">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <nav class="navbar navbar-expand-lg nav-custom-ptit sticky-top">
        <div class="container-fluid px-lg-5">

            <a class="navbar-brand" href="shop.php">
                <img src="assets/img/logo.jpg" alt="SIMPLE FIT" class="logo-ptit">
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPTIT">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navPTIT">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>
                <ul class="navbar-nav gap-3">
                    <li class="nav-item">
                        <a class="nav-link nav-link-ptit <?php echo ($current_page == 'shop.php' || $current_page == 'shop.php') ? 'active' : ''; ?>" href="shop.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-ptit <?php echo ($current_page == 'products.php') ? 'active' : ''; ?>" href="products.php">Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-ptit <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">Về chúng tôi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-ptit <?php echo ($current_page == 'blog.php') ? 'active' : ''; ?>" href="blog.php">Blog</a>
                    </li>
                </ul>
            </div>

            <div class="nav-icons-ptit d-flex align-items-center gap-3">
                <a href="#" class="icon-link"><i class="fa-solid fa-magnifying-glass"></i></a>

                <?php
                if (!isset($_SESSION['user'])) {
                    $user_link = "login.php";
                } else {
                    $user_link = ($_SESSION['user']['role'] == 'ADMIN') ? "admin.php" : "profile.php";
                }
                ?>
                <a href="<?php echo $user_link; ?>" class="icon-link"><i class="fa-regular fa-user"></i></a>

                <?php
                // Hiện giỏ hàng nếu chưa đăng nhập hoặc đã login mà là USER
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] == 'USER'):
                ?>
                    <a href="cart.php" class="icon-link position-relative">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge-ptit">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>