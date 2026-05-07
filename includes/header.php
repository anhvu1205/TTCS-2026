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
</div>
<nav class="navbar navbar-expand-lg nav-custom-ptit sticky-top">
    <div class="container-fluid px-lg-5">
        
        <a class="navbar-brand py-0" href="shop.php">
            <div class="logo-text-wrapper">
                <span class="logo-main">SIMPLE</span>
                <span class="logo-sub">FIT</span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPTIT">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navPTIT">
            <ul class="navbar-nav gap-3">
                <li class="nav-item">
                    <a class="nav-link nav-link-ptit active" href="shop.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-ptit" href="products.php">Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-ptit" href="about.php">Về chúng tôi</a>
                </li>
            </ul>
        </div>

        <div class="nav-icons-ptit d-flex align-items-center gap-3">
            <!-- Tìm kiếm -->
            <a href="#" id="open-search-trigger" class="icon-link">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>

            
            
            <?php if(isset($_SESSION['user'])): ?>
                <?php if($_SESSION['user']['role'] === 'ADMIN'): ?>
                    <a href="admin.php" class="icon-link"><i class="fa-solid fa-user-gear"></i></a>
                <?php else: ?>
                    <a href="profile.php" class="icon-link"><i class="fa-regular fa-user"></i></a>
                    
                    <a href="cart.php" class="icon-link position-relative">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge-ptit">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="icon-link"><i class="fa-regular fa-user"></i></a>
                <a href="cart.php" class="icon-link position-relative">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge-ptit">0</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div id="search-overlay-fullscreen" class="search-overlay">
    <div class="search-modal-container">
        <div class="d-flex gap-2 mb-4">
            <button class="btn-search-tab active"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm</button>
        </div>

        <div class="search-input-group">
            <i class="fa-solid fa-magnifying-glass search-icon-left"></i>
            <input type="text" id="full-search-input" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            <button type="button" class="close-search-btn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="suggested-categories">
            <p class="text-uppercase text-muted extra-small fw-bold mb-3 tracking-widest" style="font-size: 11px;">DANH MỤC</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="products.php?cat=1" class="cat-pill">Áo Thun</a>
                <a href="products.php?cat=2" class="cat-pill">Quần Jeans</a>
                <a href="products.php?cat=3" class="cat-pill">Áo Khoác</a>
                <a href="products.php?cat=4" class="cat-pill">Đầm/Váy</a>
                <a href="products.php?cat=5" class="cat-pill">Áo Sơ Mi</a>
                <a href="products.php?cat=6" class="cat-pill">Quần Short</a>
                <a href="products.php?cat=7" class="cat-pill">Áo Len</a>
            </div>
        </div>
        <div id="live-search-results" class="mt-4 custom-scrollbar"></div>
    </div>
</div>