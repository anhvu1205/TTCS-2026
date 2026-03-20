<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPLE FIT - Thời trang tối giản</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg nav-custom-ptit sticky-top">
    <div class="container-fluid px-lg-5">
        
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo.jpg" alt="SIMPLE FIT" class="logo-ptit">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPTIT">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navPTIT">
            <?php $page = basename($_SERVER['PHP_SELF']); ?>

<ul class="navbar-nav gap-3">
    <li class="nav-item">
        <a class="nav-link nav-link-ptit <?= ($page == 'index.php' || $page == 'shop.php') ? 'active' : '' ?>" href="shop.php">
            Trang chủ
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link nav-link-ptit <?= $page == 'products.php' ? 'active' : '' ?>" href="products.php">
            Sản phẩm
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link nav-link-ptit <?= $page == 'about.php' ? 'active' : '' ?>" href="about.php">
            Về chúng tôi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link nav-link-ptit <?= $page == 'blog.php' ? 'active' : '' ?>" href="blog.php">
            Blog
        </a>
    </li>
</ul>
        </div>

        <div class="nav-icons-ptit d-flex align-items-center gap-3">
            <div class="search-wrapper position-relative">
        <form id="searchForm" action="products.php" method="GET" class="d-flex">
            <input type="text" id="searchInput" name="query" placeholder="Tìm kiếm" class="form-control search-input">
        </form>
    </div>
            <a href="#" class="icon-link"><i class="fa-solid fa-magnifying-glass"></i></a>
            
            <?php if(isset($_SESSION['user'])): ?>
                <a href="profile.php" class="icon-link"><i class="fa-regular fa-user"></i></a>
            <?php else: ?>
                <a href="login.php" class="icon-link"><i class="fa-regular fa-user"></i></a>
            <?php endif; ?>
            
            <?php 
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; 
?>
<a href="cart.php" class="icon-link position-relative">
    <i class="fa-solid fa-bag-shopping"></i>
    <?php if($cartCount > 0): ?>
        <span class="cart-badge-ptit"><?= $cartCount ?></span>
    <?php endif; ?>
</a>
        </div>
    </div>
</nav>