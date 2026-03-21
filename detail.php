<?php
session_start();
include 'includes/header.php';
require_once 'includes/db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    // Truy vấn lấy sản phẩm và JOIN lấy tên danh mục
    $sql = "SELECT p.*, d.ten as ten_danhmuc 
            FROM SanPham p 
            LEFT JOIN DanhMuc d ON p.maDM = d.maDM 
            WHERE p.maSP = '$id'";
    
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        // KHỞI TẠO CÁC BIẾN ĐỂ TRÁNH LỖI WARNING TRONG ẢNH
        $category_name = $product['ten_danhmuc'] ?: "Sản phẩm";
        $product_images = [ $product['hinhAnh'] ]; // Có thể thêm ảnh nếu DB có nhiều cột ảnh
        $colors = $product['mauSac'] ? explode(',', $product['mauSac']) : [];
        $sizes = $product['kichCo'] ? explode(',', $product['kichCo']) : [];
        $stock = $product['soLuong'] ?? 0;
        $cart_qty = 0;

    if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        if($item['id'] == $id){
            $cart_qty += $item['quantity'];
        }
    }
}

$stock = $stock - $cart_qty;
if($stock < 0) $stock = 0;
        
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

                    <form action="cart.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $product['maSP']; ?>">

                        <?php if(!empty($sizes)): ?>
                        <p class="small text-muted mb-4"><strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['chatLieu'] ?: 'Cotton'); ?></p>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="fw-bold small">CHỌN SIZE:</label>
                            </div>
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

                        <div class="mb-4">
                            <label class="fw-bold small mb-3 d-block">SỐ LƯỢNG:</label>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:140px;border:1px solid #dee2e6;border-radius:12px;display:flex;align-items:center;justify-content:space-between;">
                        <button type="button" onclick="updateQty(-1)" style="border:none;background:none;padding:8px 12px;">
                        <i class="fa-solid fa-minus"></i>
                        </button>

                        <input type="text" name="quantity" id="productQty" value="0" readonly max="<?php echo $stock; ?>"
                               style="width:40px;text-align:center;border:none;font-weight:bold;background:transparent;outline:none;">

                        <button type="button" onclick="updateQty(1)" style="border:none;background:none;padding:8px 12px;">
                        <i class="fa-solid fa-plus"></i>
                        </button>
                        </div>
                                <span class="text-muted small"><?php echo $stock; ?> sản phẩm có sẵn</span>
                            </div>
                        </div>

                        <button type="submit" name="add_to_cart" class="btn btn-dark w-100 py-3 rounded-3 fw-bold shadow-lg mb-5 <?php echo ($stock <= 0) ? 'disabled' : ''; ?>" style="background: #000; transition: transform 0.2s;">
                            <i class="fa-solid fa-bag-shopping me-2"></i> <?php echo ($stock <= 0) ? 'HẾT HÀNG' : 'THÊM VÀO GIỎ'; ?>
                        </button>
                    </form>

                    <div class="row g-2 pt-4 border-top">
                        <div class="col-4 text-center">
                            <div class="p-2">
                                <i class="fa-solid fa-truck text-muted mb-2 h5"></i>
                                <p class="small fw-bold mb-0">Giao hàng nhanh</p>
                                <span class="text-muted" style="font-size: 10px;">2-3 ngày</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2">
                                <i class="fa-solid fa-rotate-left text-muted mb-2 h5"></i>
                                <p class="small fw-bold mb-0">Đổi trả 7 ngày</p>
                                <span class="text-muted" style="font-size: 10px;">Miễn phí</span>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2">
                                <i class="fa-solid fa-shield-check text-muted mb-2 h5"></i>
                                <p class="small fw-bold mb-0">Bảo hành</p>
                                <span class="text-muted" style="font-size: 10px;">Chính hãng</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $cat_id = $product['maDM'];
        $sql_related = "SELECT * FROM SanPham WHERE maDM = '$cat_id' AND maSP != '$id' LIMIT 4";
        $res_related = mysqli_query($conn, $sql_related);
        if (mysqli_num_rows($res_related) > 0):
        ?>
        <div class="mt-5 pt-5">
            <h3 class="fw-light mb-4">Sản phẩm <span style="color: #BFA77F;">tương tự</span></h3>
            <div class="row g-4">
                <?php while($rel = mysqli_fetch_assoc($res_related)): ?>
                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-transparent h-100">
                        <a href="detail.php?id=<?php echo $rel['maSP']; ?>" class="text-decoration-none">
                            <div class="rounded-4 overflow-hidden mb-3 aspect-square bg-white shadow-sm hover-lift">
                                <img src="<?php echo $rel['hinhAnh']; ?>" class="img-fluid w-100 h-100 object-cover">
                            </div>
                            <h6 class="text-dark fw-bold mb-1"><?php echo $rel['ten']; ?></h6>
                            <p class="text-muted small"><?php echo number_format($rel['gia']); ?>₫</p>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function updateQty(amt) {
    let input = document.getElementById('productQty');
    let max = parseInt(input.getAttribute('max'));
    let val = parseInt(input.value) + amt;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}

function changeMainImage(src, thumb) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumb-item').forEach(i => i.classList.remove('active'));
    thumb.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>