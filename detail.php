<?php
session_start();
include 'includes/header.php';
require_once 'includes/db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT p.*, d.ten as ten_danhmuc 
            FROM SanPham p 
            LEFT JOIN DanhMuc d ON p.maDM = d.maDM 
            WHERE p.maSP = '$id'";
    
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);

        $category_name = $product['ten_danhmuc'] ?: "Sản phẩm";
        $product_images = [$product['hinhAnh']];
        $colors = $product['mauSac'] ? explode(',', $product['mauSac']) : [];
        $sizes = $product['kichCo'] ? explode(',', $product['kichCo']) : [];
        $stock = $product['soLuong'] ?? 0;

        // --- Trừ số lượng đã có trong giỏ hàng (theo biến thể) ---
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $c_item) {
                if ($c_item['id'] == $id) {
                    $stock -= $c_item['quantity']; // trừ chung cho tất cả biến thể
                }
            }
        }

        $stock = max(0, $stock); // không âm

    } else {
        header("Location: products.php");
        exit();
    }
} else {
    header("Location: products.php");
    exit();
}
?>

<main class="product-detail-page pb-5" style="background-color: #FAFAF9;">
    <div class="container-fluid px-lg-5 max-w-7xl mx-auto">
        <nav class="breadcrumb-nav py-4">
            <a href="shop.php" class="text-muted text-decoration-none">Trang chủ</a> 
            <span class="mx-2 text-muted">/</span>
            <a href="products.php" class="text-muted text-decoration-none">Cửa hàng</a> 
            <span class="mx-2 text-muted">/</span>
            <span class="current" style="color: #1A1A1A; font-weight: 500;"><?php echo htmlspecialchars($product['ten']); ?></span>
        </nav>

        <div class="row g-5">
            <div class="col-lg-7">
                <div class="d-flex gap-3">
                    <div class="main-image-wrapper flex-grow-1 position-relative overflow-hidden" style="border-radius: 1rem; background: #fff;">
                        <img src="<?php echo $product['hinhAnh']; ?>" class="img-fluid w-100" id="mainImage" style="object-fit: cover;">
                        <?php if($product['daBan'] > 100): ?>
                            <span class="badge bg-black text-white position-absolute top-0 left-0 m-3 px-3 py-2 rounded-pill" style="font-size: 10px; letter-spacing: 1px;">BÁN CHẠY</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="product-info-sticky">
                    <p class="mb-1" style="color: #BFA77F; font-size: 12px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">
                        <?php echo $category_name; ?>
                    </p>
                    <h1 class="display-6 fw-bold mb-2" style="color: #1A1A1A;"><?php echo htmlspecialchars($product['ten']); ?></h1>
                    
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="h3 fw-bold m-0"><?php echo number_format($product['gia']); ?>₫</span>
                    </div>

                    <p class="text-muted mb-4"><?php echo nl2br(htmlspecialchars($product['moTa'])); ?></p>

                    <!-- FORM SẢN PHẨM -->
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $product['maSP']; ?>">

                        <?php if(!empty($sizes)): ?>
                        <p class="small text-muted mb-4"><strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['chatLieu'] ?: 'Cotton'); ?></p>
                        <div class="mb-4">
                            <label class="fw-bold small mb-3 d-block">CHỌN SIZE:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach($sizes as $index => $s): ?>
                                    <input type="radio" name="size" value="<?php echo trim($s); ?>" id="size-<?php echo $index; ?>" class="btn-check" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-dark rounded-3 px-4 py-2 fw-medium" for="size-<?php echo $index; ?>"><?php echo trim($s); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($colors)): ?>
                        <div class="mb-4">
                            <label class="fw-bold small mb-3 d-block">CHỌN MÀU SẮC:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach($colors as $index => $c): ?>
                                    <input type="radio" name="color" value="<?php echo trim($c); ?>" id="color-<?php echo $index; ?>" class="btn-check" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-dark rounded-3 px-3 py-2 fw-medium" for="color-<?php echo $index; ?>"><?php echo trim($c); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- SỐ LƯỢNG + NÚT THÊM GIỎ -->
                        <div class="mb-4">
                            <label class="fw-bold small mb-3 d-block">SỐ LƯỢNG:</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="input-group" style="width: 140px; border: 1px solid #dee2e6; border-radius: 0.75rem; overflow: hidden;">
                                    <button class="btn btn-link text-dark border-0 px-3" type="button" id="qtyMinus">
                                        <i class="fa-solid fa-minus small"></i>
                                    </button>
                                    <input type="text" name="quantity" id="productQty" value="1" 
                                        data-stock="<?php echo $stock; ?>" 
                                        class="form-control border-0 text-center fw-bold" readonly>
                                    <button class="btn btn-link text-dark border-0 px-3" type="button" id="qtyPlus">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>
                                <span class="text-muted small" id="stockText"><?php echo $stock; ?> sản phẩm có sẵn</span>
                            </div>
                        </div>

                        <button type="submit" name="add_to_cart" class="btn btn-dark w-100 py-3 rounded-3 fw-bold <?php echo ($stock <= 0) ? 'disabled' : ''; ?>" id="addCartBtn">
                            <i class="fa-solid fa-bag-shopping me-2"></i> <?php echo ($stock <= 0) ? 'HẾT HÀNG' : 'THÊM VÀO GIỎ'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM TƯƠNG TỰ -->
        <?php
        $cat_id = $product['maDM'];
        $sql_related = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong 
                FROM SanPham 
                WHERE maDM = '$cat_id' AND maSP != '$id' 
                LIMIT 4";
        $res_related = mysqli_query($conn, $sql_related);
        if (mysqli_num_rows($res_related) > 0):
        ?>
        <div class="mt-5 pt-5 border-top">
            <h3 class="fw-light mb-5">Sản phẩm <span style="color: #BFA77F;">tương tự</span></h3>
            <div class="row g-4">
                <?php while($rel = mysqli_fetch_assoc($res_related)): ?>
                <div class="col-6 col-md-3">
                    <div class="product-card-v3 h-100">
                        <div class="product-img-wrapper-v3">
                            <a href="detail.php?id=<?php echo $rel['id']; ?>">
                                <img src="<?php echo $rel['image']; ?>" class="product-img-main-v3" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                            </a>
                            
                            <button class="btn-wishlist-v3 add-to-wishlist" data-id="<?php echo $rel['id']; ?>" title="Thêm vào yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            
                            <form action="cart.php" method="POST" class="quick-add-form-v3">
                                <input type="hidden" name="id" value="<?php echo $rel['id']; ?>">
                                
                                <?php if ($rel['soLuong'] > 0): ?>
                                    <button type="submit" name="add_to_cart" class="btn-quick-add-v3">
                                        <i class="fa-solid fa-cart-plus me-2"></i>THÊM VÀO GIỎ HÀNG
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-quick-add-v3 disabled" disabled style="background-color: #999; opacity: 0.8;">
                                        <i class="fa-solid fa-xmark me-2"></i>HẾT HÀNG
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="product-info-v3 mt-3 text-center">
                            <h6 class="product-name-v3 mb-1">
                                <a href="detail.php?id=<?php echo $rel['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars($rel['name']); ?>
                                </a>
                            </h6>
                            <p class="product-price-v3 mb-0"><?php echo number_format($rel['price']); ?>₫</p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>



<?php include 'includes/footer.php'; ?>