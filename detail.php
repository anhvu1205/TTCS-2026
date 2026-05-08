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
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $c_item) {
                if ($c_item['id'] == $id) {
                    $stock -= $c_item['quantity']; 
                }
            }
        }

        $stock = max(0, $stock); 

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
                        <?php if ($product['daBan'] > 100): ?>
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

                        <?php if (!empty($sizes)): ?>
                            <p class="small text-muted mb-4"><strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['chatLieu'] ?: 'Cotton'); ?></p>
                            <div class="mb-4">
                                <label class="fw-bold small mb-3 d-block">CHỌN SIZE:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($sizes as $index => $s): ?>
                                        <input type="radio" name="size" value="<?php echo trim($s); ?>" id="size-<?php echo $index; ?>" class="btn-check" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-dark rounded-3 px-4 py-2 fw-medium" for="size-<?php echo $index; ?>"><?php echo trim($s); ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($colors)): ?>
                            <div class="mb-4">
                                <label class="fw-bold small mb-3 d-block">CHỌN MÀU SẮC:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($colors as $index => $c): ?>
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
                            <i class="fa-solid fa-bag-shopping me-2"></i> <?php echo ($stock <= 0) ? 'HẾT HÀNG' : 'THÊM VÀO GIỎ HÀNG'; ?>
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
                    <?php while ($rel = mysqli_fetch_assoc($res_related)): ?>
                        <div class="col-6 col-md-3">
                            <div class="product-card-v3 h-100">
                                <div class="product-img-wrapper-v3">
                                    <a href="detail.php?id=<?php echo $rel['id']; ?>">
                                        <img src="<?php echo $rel['image']; ?>" class="product-img-main-v3" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                                    </a>

                                    <button class="btn-wishlist-v3 add-to-wishlist" data-id="<?php echo $row['id']; ?>" title="Thêm vào yêu thích">
                                        <i class="<?php echo $is_wishlisted ? 'fa-solid text-danger' : 'fa-regular'; ?> fa-heart"></i>
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


        <!-- ĐÁNH GIÁ SẢN PHẨM -->
        <?php
        $product_id = (int)$product['maSP'];
        $review_summary = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) AS total_reviews, AVG(rating) AS avg_rating
            FROM ProductReviews
            WHERE product_id = $product_id AND status = 'visible'
        "));
        $total_reviews = (int)($review_summary['total_reviews'] ?? 0);
        $avg_rating = $total_reviews > 0 ? round((float)$review_summary['avg_rating'], 1) : 0;

        $can_review = isset($_SESSION['user']);
        $current_user_id = $can_review ? (int)($_SESSION['user']['id'] ?? $_SESSION['user']['maND'] ?? 0) : 0;
        $has_purchased_current_product = 0;
        $has_reviewed_current_product = false;

        if ($current_user_id > 0) {
            $res_kh_current = mysqli_query($conn, "SELECT maKH FROM KhachHang WHERE maND = $current_user_id LIMIT 1");
            if ($res_kh_current && mysqli_num_rows($res_kh_current) > 0) {
                $kh_current = mysqli_fetch_assoc($res_kh_current);
                $maKH_current = (int)$kh_current['maKH'];
                $res_bought = mysqli_query($conn, "
                    SELECT dh.maDH
                    FROM DonHang dh
                    JOIN ChiTietDonHang ct ON dh.maDH = ct.maDH
                    WHERE dh.maKH = $maKH_current
                      AND ct.maSP = $product_id
                      AND dh.trangThai IN ('Hoàn tất', 'Đã giao hàng')
                    LIMIT 1
                ");
                $has_purchased_current_product = ($res_bought && mysqli_num_rows($res_bought) > 0) ? 1 : 0;
            }

            $res_reviewed = mysqli_query($conn, "SELECT id FROM ProductReviews WHERE product_id = $product_id AND user_id = $current_user_id LIMIT 1");
            $has_reviewed_current_product = ($res_reviewed && mysqli_num_rows($res_reviewed) > 0);
        }
        ?>

        <section id="product-reviews" class="mt-5 pt-5 border-top">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="p-4 bg-white shadow-sm" style="border-radius: 1rem;">
                        <h3 class="h5 fw-bold mb-3">Đánh giá sản phẩm</h3>

                        <div class="d-flex align-items-end gap-2 mb-2">
                            <span class="display-6 fw-bold" style="color:#C4622D;"><?php echo $avg_rating ?: '0'; ?></span>
                            <span class="text-muted mb-2">/ 5</span>
                        </div>

                        <div class="mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star" style="color: <?php echo $i <= round($avg_rating) ? '#C4622D' : '#D4CEBE'; ?>;"></i>
                            <?php endfor; ?>
                        </div>

                        <p class="text-muted small mb-0"><?php echo $total_reviews; ?> lượt đánh giá</p>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="p-4 bg-white shadow-sm" style="border-radius: 1rem;">
                        <?php
                        if (isset($_GET['error'])) {
                            $error_msg = '';
                            switch ($_GET['error']) {
                                case 'not_purchased':
                                    $error_msg = 'Bạn cần mua sản phẩm này trước khi có thể đánh giá!';
                                    break;
                                case 'already_reviewed':
                                    $error_msg = 'Bạn đã đánh giá sản phẩm này rồi!';
                                    break;
                            }
                            if ($error_msg) {
                                echo '<div class="alert alert-danger mb-4">' . htmlspecialchars($error_msg) . '</div>';
                            }
                        }
                        ?>

                        <?php if ($can_review && $has_purchased_current_product && !$has_reviewed_current_product): ?>
                            <form method="POST" action="review-store.php" class="mb-4">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                                <div class="mb-3">
                                    <label class="fw-bold small mb-2 d-block">Chọn số sao</label>
                                    <select name="rating" class="form-control" style="max-width:180px;" required>
                                        <option value="5">5 sao - Rất hài lòng</option>
                                        <option value="4">4 sao - Hài lòng</option>
                                        <option value="3">3 sao - Bình thường</option>
                                        <option value="2">2 sao - Chưa hài lòng</option>
                                        <option value="1">1 sao - Không hài lòng</option>
                                    </select>
                                </div>

                                <textarea name="content" class="form-control mb-3" rows="3" placeholder="Viết đánh giá của bạn về sản phẩm..." required></textarea>

                                <div class="small text-success mb-3"><i class="fa-solid fa-check-circle me-1"></i>Tài khoản của bạn đã mua sản phẩm này.</div>

                                <button type="submit" class="btn btn-dark rounded-pill px-4">Gửi đánh giá</button>
                            </form>
                        <?php elseif ($can_review && $has_reviewed_current_product): ?>
                            <div class="alert alert-info mb-4">
                                Bạn đã đánh giá sản phẩm này rồi. Bạn chỉ được sửa đánh giá một lần.
                            </div>
                        <?php elseif ($can_review && !$has_purchased_current_product): ?>
                            <div class="alert alert-danger mb-4">
                                Bạn chưa thể đánh giá sản phẩm này vì chưa mua hàng.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-4">
                                Bạn cần <a href="login.php">đăng nhập</a> để đánh giá sản phẩm.
                            </div>
                        <?php endif; ?>

                        <?php
                        $reviews = mysqli_query($conn, "
                            SELECT * FROM ProductReviews
                            WHERE product_id = $product_id AND status = 'visible'
                            ORDER BY created_at DESC
                        ");
                        ?>

                        <?php if ($reviews && mysqli_num_rows($reviews) > 0): ?>
                            <?php while ($rv = mysqli_fetch_assoc($reviews)): ?>
                                <div class="border-top py-3">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <strong><?php echo htmlspecialchars($rv['user_name']); ?></strong>
                                            <?php if ((int)$rv['is_purchased'] === 1): ?>
                                                <span class="badge bg-success ms-2">Đã mua hàng</span>
                                            <?php endif; ?>
                                            <div class="small mt-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fa-solid fa-star" style="color: <?php echo $i <= (int)$rv['rating'] ? '#C4622D' : '#D4CEBE'; ?>;"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($rv['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($rv['content'])); ?></p>

                                    <?php
                                    // Hiển thị nút sửa nếu là review của user hiện tại và chưa sửa quá 1 lần
                                    $is_current_user_review = isset($_SESSION['user']) && ((int)$_SESSION['user']['id'] ?? (int)$_SESSION['user']['maND'] ?? 0) === (int)$rv['user_id'];
                                    $can_edit = $is_current_user_review && (!isset($rv['edit_count']) || (int)$rv['edit_count'] < 1);
                                    ?>

                                    <?php if ($can_edit): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary edit-review-btn"
                                                data-review-id="<?php echo $rv['id']; ?>"
                                                data-current-content="<?php echo htmlspecialchars($rv['content']); ?>">
                                                <i class="fa-solid fa-edit me-1"></i>Sửa đánh giá
                                            </button>
                                        </div>

                                        <!-- Form sửa review -->
                                        <div class="edit-review-form mt-3" id="edit-form-<?php echo $rv['id']; ?>" style="display: none;">
                                            <form method="POST" action="review-edit.php">
                                                <input type="hidden" name="review_id" value="<?php echo $rv['id']; ?>">
                                                <div class="mb-2">
                                                    <textarea name="content" class="form-control" rows="3" required><?php echo htmlspecialchars($rv['content']); ?></textarea>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fa-solid fa-save me-1"></i>Lưu thay đổi
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary cancel-edit-btn" data-review-id="<?php echo $rv['id']; ?>">
                                                        <i class="fa-solid fa-times me-1"></i>Hủy
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($rv['admin_reply'])): ?>
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <strong class="text-primary">Phản hồi từ Shop:</strong>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($rv['admin_reply_at'])); ?></small>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($rv['admin_reply'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Chưa có đánh giá nào cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-review-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                const form = document.getElementById('edit-form-' + reviewId);
                if (form) {
                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                }
            });
        });
        document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                const form = document.getElementById('edit-form-' + reviewId);
                if (form) {
                    form.style.display = 'none';
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
