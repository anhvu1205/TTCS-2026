<?php
session_start();
require_once 'includes/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$cat = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';

if ($search != '') {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong FROM SanPham 
            WHERE ten LIKE '%$search%' ORDER BY maSP DESC";
} elseif ($cat != '') {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong FROM SanPham 
            WHERE maDM = '$cat' ORDER BY maSP DESC";
} else {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong FROM SanPham 
            ORDER BY maSP DESC LIMIT 8";
}

$result = mysqli_query($conn, $sql);
include 'includes/header.php';
?>

<main>
    <section class="hero-minimalist">
        <div class="container-fluid p-0">
            <div class="d-flex flex-wrap align-items-center">
                <div class="hero-text-area-custom">
                    <div class="hero-inner-content">
                        <div class="hero-collection-tag">
                            <span class="line"></span>
                            <span class="tag-text">BỘ SƯU TẬP 2026</span>
                        </div>
                        <h1 class="hero-main-title">
                            Simple <br>
                            <span class="italic-gold">Fit</span> & <br>
                            Simple Life
                        </h1>
                        <p class="hero-sub-desc">Mặc đẹp không cần phức tạp. Thời trang tối giản dành riêng cho bạn.</p>

                        <div class="hero-cta-group">
                            <a href="products.php" class="btn btn-gold-filled">Khám phá ngay <i class="fa-solid fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>

                <div class="hero-image-area-custom">
                    <div class="image-wrapper">
                        <img src="https://images.unsplash.com/photo-1485968579580-b6d095142e6e?w=1600" alt="Hero Image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="category-grid-section">
        <div class="container-fluid px-lg-5 max-w-7xl mx-auto">
            <div class="d-flex align-items-end justify-content-between mb-5">
                <div>
                    <p class="category-sub-title">Danh mục</p>
                    <h2 class="category-main-title">Danh mục nổi bật</h2>
                </div>
                <a href="products.php" class="view-all-link d-none d-md-flex align-items-center gap-2">
                    Xem tất cả <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>

            <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-4 row-cols-lg-7 justify-content-center">
                <?php
                $categories = [
                    ['id' => 1, 'name' => 'Áo Thun',   'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500', 'color' => '#E8DDD0'],
                    ['id' => 2, 'name' => 'Quần Jeans', 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500', 'color' => '#D6DDE8'],
                    ['id' => 3, 'name' => 'Áo Khoác',  'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500', 'color' => '#E8D9D6'],
                    ['id' => 4, 'name' => 'Đầm/Váy',   'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500', 'color' => '#F2E8DA'],
                    ['id' => 5, 'name' => 'Áo Sơ Mi',  'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500', 'color' => '#D9E5D6'],
                    ['id' => 6, 'name' => 'Quần Short', 'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500', 'color' => '#E5E0D8'],
                    ['id' => 7, 'name' => 'Áo Len',    'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500', 'color' => '#DDDDE8'],
                ];

                foreach ($categories as $cat) :
                ?>
                    <div class="col">
                        <a href="products.php?cat=<?php echo $cat['id']; ?>" class="category-card group">
                            <div class="category-img-wrapper" style="background-color: <?php echo $cat['color']; ?>;">
                                <img src="<?php echo $cat['image']; ?>" alt="<?php echo $cat['name']; ?>" class="category-img">
                                <div class="category-overlay"></div>
                                <div class="category-arrow">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </div>
                            </div>
                            <p class="category-name text-center"><?php echo $cat['name']; ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="new-arrivals-section">
        <div class="container-fluid px-lg-5 max-w-7xl mx-auto">
            <div class="d-flex align-items-end justify-content-between mb-5">
                <div>
                    <p class="new-in-tag">New In</p>
                    <h2 class="new-arrivals-title">Bộ sưu tập mới nhất</h2>
                </div>
                <a href="products.php" class="btn-view-all-dark d-none d-md-flex align-items-center gap-2">
                    Xem tất cả <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="row g-4 lg-g-5">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) :
                        $is_wishlisted = false;
                        if (isset($_SESSION['user'])) {
                            $u_id = $_SESSION['user']['id'];
                            $p_id = $row['id'];
                            $check_heart = mysqli_query($conn, "SELECT * FROM YeuThich WHERE maND = '$u_id' AND maSP = '$p_id'");
                            if ($check_heart && mysqli_num_rows($check_heart) > 0) {
                                $is_wishlisted = true;
                            }
                        }
                    ?>
                        <div class="col-6 col-md-4 col-xl-3">
                            <div class="product-card-v3 h-100">
                                <div class="product-img-wrapper-v3 small-card">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>">
                                        <img src="<?php echo $row['image']; ?>" class="product-img-main-v3" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    </a>

                                    <button class="btn-wishlist-v3 add-to-wishlist" data-id="<?php echo $row['id']; ?>" title="Thêm vào yêu thích">
                                        <i class="<?php echo $is_wishlisted ? 'fa-solid text-danger' : 'fa-regular'; ?> fa-heart"></i>
                                    </button>

                                    <form action="cart.php" method="POST" class="quick-add-form-v3">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <?php if ($row['soLuong'] > 0): ?>
                                            <button type="submit" name="add_to_cart" class="btn-quick-add-v3">
                                                <i class="fa-solid fa-cart-plus me-2"></i>THÊM VÀO GIỎ HÀNG
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-quick-add-v3 disabled" style="background: #999; cursor: not-allowed;">
                                                <i class="fa-solid fa-xmark me-2"></i>HẾT HÀNG
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>

                                <div class="product-info-v3 text-center">
                                    <h6 class="product-name-v3 mb-1">
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </a>
                                    </h6>
                                    <p class="product-price-v3 mb-0"><?php echo number_format($row['price']); ?>₫</p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                else : ?>
                    <div class="col-12 text-center py-5">
                        <p style="color: #8C8279; font-family: 'DM Sans', sans-serif;">Sắp ra mắt...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SẢN PHẨM BÁN CHẠY -->
    <section class="best-sellers-section">
        <div class="container-fluid max-w-7xl mx-auto">
            <div class="d-flex align-items-end justify-content-between mb-5">
                <div>
                    <p class="best-seller-tag">Best Sellers</p>
                    <h2 class="best-seller-title">Sản phẩm bán chạy</h2>
                </div>
                <div class="d-none d-md-flex gap-3">
                    <button onclick="scrollSlider(-1)" class="btn-scroll-nav"><i class="fa-solid fa-chevron-left"></i></button>
                    <button onclick="scrollSlider(1)" class="btn-scroll-nav"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

            <div id="bestSellerSlider" class="best-seller-slider-wrapper">
                <?php
                // Sắp xếp theo cột 'daBan' giảm dần
                $sql_best = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong, daBan 
                             FROM SanPham 
                             ORDER BY daBan DESC 
                             LIMIT 10";
                
                $res_best = mysqli_query($conn, $sql_best);
                if (mysqli_num_rows($res_best) > 0) :
                    while ($row = mysqli_fetch_assoc($res_best)) :
                        $is_wishlisted = false;
                        if (isset($_SESSION['user'])) {
                            $u_id = $_SESSION['user']['id'];
                            $p_id = $row['id'];
                            $check_heart = mysqli_query($conn, "SELECT * FROM YeuThich WHERE maND = '$u_id' AND maSP = '$p_id'");
                            if ($check_heart && mysqli_num_rows($check_heart) > 0) {
                                $is_wishlisted = true;
                            }
                        }
                ?>
                        <div class="best-seller-item">
                            <div class="product-card-v3">
                                <div class="product-img-wrapper-v3">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>">
                                        <img src="<?php echo $row['image']; ?>" class="product-img-main-v3" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    </a>

                                    <button class="btn-wishlist-v3 add-to-wishlist" data-id="<?php echo $row['id']; ?>" title="Thêm vào yêu thích">
                                        <i class="<?php echo $is_wishlisted ? 'fa-solid text-danger' : 'fa-regular'; ?> fa-heart"></i>
                                    </button>

                                    <!-- FORM THÊM VÀO GIỎ HÀNG -->
                                    <form action="cart.php" method="POST" class="quick-add-form-v3">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <?php if ($row['soLuong'] > 0): ?>
                                            <button type="submit" name="add_to_cart" class="btn-quick-add-v3">
                                                <i class="fa-solid fa-cart-plus me-2"></i>THÊM VÀO GIỎ HÀNG
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-quick-add-v3 disabled" style="background: #999; cursor: not-allowed;">
                                                <i class="fa-solid fa-xmark me-2"></i>HẾT HÀNG
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <!-- Hiển thị nhãn số lượng đã bán -->
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge rounded-pill bg-white text-dark small fw-bold px-2 py-1 shadow-sm" style="font-size: 9px; opacity: 0.9;">
                                            Đã bán <?php echo number_format($row['daBan']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="product-info-v3 text-center mt-3">
                                    <h6 class="product-name-v3 mb-1">
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;" class="fw-bold">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </a>
                                    </h6>
                                    <p class="product-price-v3 mb-0"><?php echo number_format($row['price']); ?>₫</p>
                                </div>
                            </div>
                        </div>
                <?php endwhile;
                endif; ?>
            </div>
        </div>
    </section>

    <section class="review-slider-section">
        <?php
        $sql_cmt = "
    SELECT 
        pr.user_name,
        pr.content,
        pr.rating,
        sp.ten as tenSanPham
    FROM productreviews pr
    LEFT JOIN sanpham sp ON pr.product_id = sp.maSP
    WHERE pr.status = 'visible' AND pr.rating >= 4
    ORDER BY pr.created_at DESC
    LIMIT 6
";

        $res_cmt = mysqli_query($conn, $sql_cmt);
        ?>
        <section class="review-slider-section">
            <div class="container max-w-3xl mx-auto text-center">
                <p class="review-tag">Reviews</p>
                <h2 class="review-main-title">Khách hàng nói gì về chúng tôi</h2>

                <div class="review-container position-relative mt-4">

                    <div id="review-slider">
                        <?php if ($res_cmt && mysqli_num_rows($res_cmt) > 0): ?>

                            <?php $i = 0; ?>
                            <?php while ($cmt = mysqli_fetch_assoc($res_cmt)): ?>
                                <?php $rating = max(1, min(5, (int)$cmt['rating'])); ?>

                                <div class="review-item <?php echo $i === 0 ? 'active' : ''; ?>">

                                    <!-- Star -->
                                    <div class="mb-3">
                                        <?php for ($j = 0; $j < 5; $j++): ?>
                                            <i class="fa-solid fa-star"
                                                style="color: <?php echo $j < $rating ? '#C4622D' : '#ccc'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>

                                    <!-- Content -->
                                    <p class="review-comment">
                                        "<?php echo htmlspecialchars($cmt['content']); ?>"
                                    </p>

                                    <!-- User -->
                                    <p class="review-customer mt-3">
                                        — <?php echo htmlspecialchars($cmt['user_name']); ?>
                                        <br>
                                        <small style="color:#999;">
                                            <?php echo htmlspecialchars($cmt['tenSanPham']); ?>
                                        </small>
                                    </p>

                                </div>

                                <?php $i++; ?>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <p>Chưa có đánh giá nào</p>
                        <?php endif; ?>
                    </div>

                    <!-- NAV -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button onclick="changeReview(-1)" class="btn-review-nav">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button onclick="changeReview(1)" class="btn-review-nav">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                </div>
            </div>
        </section>
</main>

<script>
    // Slider sản phẩm bán chạy
    function scrollSlider(direction) {
        const slider = document.getElementById('bestSellerSlider');
        if (slider) {
            slider.scrollBy({
                left: direction * 320,
                behavior: 'smooth'
            });
        }
    }

    // Review slider
    let currentReview = 0;

    function changeReview(dir) {
        const items = document.querySelectorAll('.review-item');
        if (items.length > 0) {
            items[currentReview].classList.remove('active');
            currentReview = (currentReview + dir + items.length) % items.length;
            items[currentReview].classList.add('active');
        }
    }

    // Wishlist không dùng
    document.addEventListener('DOMContentLoaded', function() {

        const newsletterForm = document.getElementById('newsletter-form');
        const newsletterArea = document.getElementById('newsletter-area');

        if (newsletterForm && newsletterArea) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                newsletterArea.innerHTML = `
                    <div class="success-message">
                        <i class="fa-solid fa-check-circle me-2"></i>
                        <span>Cảm ơn bạn đã đăng ký!</span>
                    </div>
                `;
            });
        }
    });
</script>

<?php include 'chat-widget.php'; ?>
<?php include 'includes/footer.php'; ?>