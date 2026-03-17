<?php session_start();include 'includes/header.php'; ?>
<?php
require_once 'includes/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$cat = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';

$search_lower = mb_strtolower($search, 'UTF-8');

if ($search != '') {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image FROM SanPham 
            WHERE ten LIKE '%$search%' ORDER BY maSP DESC";
} elseif ($cat != '') {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image FROM SanPham 
            WHERE maDM = '$cat' ORDER BY maSP DESC";
} else {
    $sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image FROM SanPham 
            ORDER BY maSP DESC LIMIT 8";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Fit - Simple Life</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300..700&family=DM+Sans:ital,opsz,wght@0,9..40,300..700;1,9..40,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,600;1,300&family=DM+Sans:wght@400;500&family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Hello banner -->
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
    <!-- DANH MỤC SẢN PHẨM -->
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

            <div class="row g-3 g-lg-4">
                <?php
                // Định nghĩa danh mục (có thể lấy từ database bảng DanhMuc nếu muốn)
                $categories = [
                    ['name' => 'Áo thun', 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600', 'color' => '#E8DDD0', 'id' => 1],
                    ['name' => 'Áo sơ mi', 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600', 'color' => '#D9E5D6', 'id' => 5],
                    ['name' => 'Quần jean', 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600', 'color' => '#D6DDE8', 'id' => 2],
                    ['name' => 'Áo khoác', 'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600', 'color' => '#E8D9D6', 'id' => 3],
                    ['name' => 'Unisex', 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600', 'color' => '#E5E0D8', 'id' => 6],
                ];

                foreach ($categories as $cat) :
                ?>
                    <div class="col-6 col-md-4 col-lg">
                        <a href="products.php?cat=<?php echo $cat['id']; ?>" class="category-card group">
                            <div class="category-img-wrapper" style="background-color: <?php echo $cat['color']; ?>;">
                                <img src="<?php echo $cat['image']; ?>" alt="<?php echo $cat['name']; ?>" class="category-img">
                                <div class="category-overlay"></div>
                                <div class="category-arrow">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </div>
                            </div>
                            <p class="category-name"><?php echo $cat['name']; ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- BỘ SƯU TẬP MỚI NHẤT -->
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
                <?php if (mysqli_num_rows($result) > 0) : while ($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="col-6 col-md-4">
                        <div class="product-card-v2">
                            <div class="product-img-wrapper">
                                <a href="detail.php?id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $row['image']; ?>" class="product-img-main" alt="<?php echo $row['name']; ?>">
                                </a>
                                <form action="cart.php" method="POST" class="quick-add-form">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn-quick-add">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="product-info-v2">
                                <h6 class="product-name-v2">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                </h6>
                                <p class="product-price-v2"><?php echo number_format($row['price']); ?>₫</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else : ?>
                    <div class="col-12">
                        <p class="text-center py-5" style="color: #8C8279; font-family: 'DM Sans', sans-serif;">Sắp ra mắt...</p>
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
                    <button onclick="scrollSlider(-1)" class="btn-scroll-nav">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button onclick="scrollSlider(1)" class="btn-scroll-nav">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div id="bestSellerSlider" class="best-seller-slider-wrapper">
                <?php
                // Tái sử dụng query hoặc tạo query mới cho Best Seller
                $sql_best = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image FROM SanPham ORDER BY maSP ASC LIMIT 10";
                $res_best = mysqli_query($conn, $sql_best);
                
                if (mysqli_num_rows($res_best) > 0) : 
                    while ($row = mysqli_fetch_assoc($res_best)) : ?>
                    <div class="best-seller-item">
                        <div class="product-card-v2">
                            <div class="product-img-wrapper">
                                <a href="detail.php?id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $row['image']; ?>" class="product-img-main" alt="<?php echo $row['name']; ?>">
                                </a>
                            </div>
                            <div class="product-info-v2">
                                <h6 class="product-name-v2">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                </h6>
                                <p class="product-price-v2"><?php echo number_format($row['price']); ?>₫</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else : ?>
                    <p class="text-center w-100 py-5">Sắp ra mắt...</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
    function scrollSlider(direction) {
        const slider = document.getElementById('bestSellerSlider');
        const scrollAmount = 320; // Độ rộng của một item + gap
        slider.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
    </script>

    <!-- ĐÁNH GIÁ -->

    <section class="review-slider-section">
        <div class="container max-w-3xl mx-auto text-center">
            <p class="review-tag">Reviews</p>
            <h2 class="review-main-title">Khách hàng nói gì về chúng tôi</h2>

            <div class="review-container position-relative">
                <?php
                // Giả lập dữ liệu Review 
                $reviews = [
                    ['name' => 'Nguyễn An', 'rating' => 5, 'comment' => 'Chất vải rất mịn, form áo cực kỳ tôn dáng. Sẽ ủng hộ shop dài dài!'],
                    ['name' => 'Trần Bình', 'rating' => 5, 'comment' => 'Giao hàng nhanh, đóng gói cẩn thận. Phong cách tối giản đúng ý mình.'],
                    ['name' => 'Lê Chi', 'rating' => 4, 'comment' => 'Áo sơ mi mặc đi làm rất lịch sự, ít nhăn. Rất hài lòng.'],
                ];
                ?>

                <div id="review-content">
                    <?php foreach ($reviews as $index => $rev) : ?>
                        <div class="review-item <?php echo $index === 0 ? 'active' : ''; ?>" id="rev-<?php echo $index; ?>">
                            <div class="star-rating mb-4">
                                <?php for ($i = 0; $i < 5; $i++) : ?>
                                    <i class="fa-solid fa-star" style="color: <?php echo $i < $rev['rating'] ? '#C4622D' : '#333'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="review-comment">"<?php echo $rev['comment']; ?>"</p>
                            <p class="review-customer">— <?php echo $rev['name']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5">
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

    <script>
        let currentReview = 0;
        const totalReviews = <?php echo count($reviews); ?>;

        function changeReview(dir) {
            const items = document.querySelectorAll('.review-item');
            items[currentReview].classList.remove('active');
            
            currentReview = (currentReview + dir + totalReviews) % totalReviews;
            
            items[currentReview].classList.add('active');
        }
    </script>

    <!-- ƯU ĐÃI -->

    <section class="newsletter-section">
        <div class="container max-w-7xl mx-auto px-lg-5">
            <div class="newsletter-card position-relative overflow-hidden">
                <div class="deco-circle circle-top"></div>
                <div class="deco-circle circle-bottom"></div>

                <div class="newsletter-content position-relative" id="newsletter-area">
                    <div class="newsletter-badge mb-4">
                        <i class="fa-solid fa-sparkles me-2"></i>
                        <span>Ưu đãi độc quyền</span>
                    </div>

                    <h2 class="newsletter-title">Nhận ưu đãi độc quyền</h2>
                    <p class="newsletter-desc">Đăng ký để nhận thông báo về bộ sưu tập mới và mã giảm giá đặc biệt.</p>

                    <form id="newsletter-form" class="newsletter-form-ui d-flex gap-2 mx-auto">
                        <input 
                            type="email" 
                            id="newsletter-email"
                            placeholder="Email của bạn..." 
                            required 
                            class="newsletter-input"
                        >
                        <button type="submit" class="btn-newsletter-submit">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('newsletter-email').value;
            if(email) {
                const area = document.getElementById('newsletter-area');
                // Hiệu ứng biến mất và hiện thông báo thành công
                area.innerHTML = `
                    <div class="success-message">
                        <i class="fa-solid fa-check-circle me-2"></i>
                        <span>Cảm ơn bạn đã đăng ký!</span>
                    </div>
                `;
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>