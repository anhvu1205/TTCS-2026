<?php
session_start();
require_once 'includes/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$cat = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';

$search_lower = mb_strtolower($search, 'UTF-8');

if ($search_lower == 'áo' || $search_lower == 'áo thun' || $search_lower == 'tee') {
    $sql = "SELECT * FROM products WHERE category = 'tee' ORDER BY id DESC";
} elseif ($search_lower == 'quần' || $search_lower == 'pants' || $search_lower == 'short') {
    $sql = "SELECT * FROM products WHERE category = 'pant' OR category = 'pants' ORDER BY id DESC";
} elseif ($search_lower == 'hoodie' || $search_lower == 'áo khoác') {
    $sql = "SELECT * FROM products WHERE category = 'hoodie' ORDER BY id DESC";
} elseif ($search != '') {
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' ORDER BY id DESC";
} elseif ($cat != '') {
    $sql = "SELECT * FROM products WHERE category = '$cat' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cửa Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top nav-custom">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand flex-1" href="shop.php">
                <img src="assets/img/logo.jpg" alt="Logo" class="logo-brand">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="shop.php">TRANG CHỦ</a></li>
                    <li class="nav-item dropdown px-lg-3">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="catDropdown" data-bs-toggle="dropdown">DANH MỤC</a>
                        <ul class="dropdown-menu custom-dropdown-menu border-0 shadow-lg">
                            <li><a class="dropdown-item" href="products.php?cat=tee">T-SHIRT / ÁO THUN</a></li>
                            <li><a class="dropdown-item" href="products.php?cat=hoodie">HOODIE / SWEATER</a></li>
                            <li><a class="dropdown-item" href="products.php?cat=short">PANTS / QUẦN ĐÀI</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="products.php">TẤT CẢ</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="about.php">GIỚI THIỆU</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom active" href="products.php">CỬA HÀNG</a></li>
                </ul>
            </div>
            <div class="nav-icons-group d-flex align-items-center justify-content-end flex-1">
                <div class="search-container position-relative me-3">
                    <form action="shop.php" method="GET" class="d-flex align-items-center">
                        <input type="text" name="search" class="search-input-minimal" placeholder="TÌM KIẾM...">
                        <button type="submit" class="btn-icon"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                </div>
                <a href="login.php" class="btn-icon me-3"><i class="fa-regular fa-user"></i></a>
                <a href="cart.php" class="btn-icon"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h2 class="fw-bold text-center mb-4">
    <?php 
        $search_l = mb_strtolower($search, 'UTF-8');
        if($cat == 'tee' || $search_l == 'áo' || $search_l == 'tee') echo 'T-SHIRT / ÁO THUN';
        elseif($cat == 'hoodie' || $search_l == 'hoodie') echo 'HOODIE / SWEATER';
        elseif($cat == 'pant' || $search_l == 'quần') echo 'PANTS / QUẦN';
        elseif($search != '') echo 'KẾT QUẢ TÌM KIẾM: ' . htmlspecialchars($search);
        else echo 'SẢN PHẨM MỚI';
    ?>
</h2>>
        <div class="row g-4">
            <?php
            if(mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-6 col-md-3 d-flex">
                    <div class="card shadow-sm w-100 border-0">
                        <a href="detail.php?id=<?php echo $row['id']; ?>">
                            <img src="assets/img/<?php echo $row['image']; ?>" class="card-img-top product-img">
                        </a>
                        <div class="card-body">
                            <h6 class="card-title fw-bold"><?php echo $row['name']; ?></h6>
                            <p class="price-text"><?php echo number_format($row['price']); ?>₫</p>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="name" value="<?php echo $row['name']; ?>">
                                <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="image" value="<?php echo $row['image']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-dark w-100 rounded-0">MUA NGAY</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo '<div class="col-12 text-center py-5"><h5>Không tìm thấy sản phẩm nào.</h5></div>';
            } 
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>