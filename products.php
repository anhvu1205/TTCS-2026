<?php 
session_start(); 
require_once 'includes/db.php';

// 1. KHỞI TẠO THAM SỐ (Tương đương requestScope trong JSP)
$limit = 12; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, trim($_GET['keyword'])) : '';
$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$brand_id = isset($_GET['brandId']) ? (int)$_GET['brandId'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// 2. XÂY DỰNG TRUY VẤN LỌC
$where_clauses = ["1=1"];
if ($keyword != '') $where_clauses[] = "ten LIKE '%$keyword%'";
if ($cat_id > 0) $where_clauses[] = "maDM = $cat_id";
if ($brand_id > 0) $where_clauses[] = "maTH = $brand_id";
$where_sql = implode(" AND ", $where_clauses);

// Tính tổng số trang
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM SanPham WHERE $where_sql");
$total_data = mysqli_fetch_assoc($total_query);
$total_products = $total_data['total'];
$endPage = ceil($total_products / $limit);

// 3. LOGIC SẮP XẾP
$order_sql = " ORDER BY maSP DESC";
if ($sort === 'price_asc') $order_sql = " ORDER BY gia ASC";
if ($sort === 'price_desc') $order_sql = " ORDER BY gia DESC";

$sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image, soLuong FROM SanPham 
        WHERE $where_sql $order_sql LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Lấy danh sách danh mục và thương hiệu cho Sidebar
$categories = mysqli_query($conn, "SELECT maDM as id, ten as name FROM DanhMuc");
$brands = mysqli_query($conn, "SELECT maTH as id, ten as name FROM ThuongHieu");
include 'includes/header.php'; 
?>

<main class="min-h-screen pt-20" style="background-color: #FAFAF9;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">
                Sản phẩm <span style="color: #BFA77F;">SIMPLE FIT</span>
            </h1>
            <p class="text-muted">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
        </div>

        <div class="row g-4">
            <aside class="col-lg-3">
                <form action="products.php" method="GET" class="space-y-6 bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light">
                    <h2 class="h5 fw-bold border-bottom pb-3 mb-4">Bộ lọc</h2>

                    <div class="mb-4">
                        <label class="filter-label-ptit">TÌM KIẾM</label>
                        <input type="text" name="keyword" class="form-control custom-filter-input" 
                               placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="filter-label-ptit">DANH MỤC</label>
                        <select name="cat" class="form-select custom-filter-input">
                            <option value="0">Tất cả danh mục</option>
                            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if($cat_id == $c['id']) echo 'selected'; ?>>
                                    <?php echo $c['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="filter-label-ptit">THƯƠNG HIỆU</label>
                        <select name="brandId" class="form-select custom-filter-input">
                            <option value="0">Tất cả thương hiệu</option>
                            <?php while($b = mysqli_fetch_assoc($brands)): ?>
                                <option value="<?php echo $b['id']; ?>" <?php if($brand_id == $b['id']) echo 'selected'; ?>>
                                    <?php echo $b['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="filter-label-ptit">SẮP XẾP</label>
                        <select name="sort" class="form-select custom-filter-input">
                            <option value="">Mặc định</option>
                            <option value="price_asc" <?php if($sort == 'price_asc') echo 'selected'; ?>>Giá: Thấp đến cao</option>
                            <option value="price_desc" <?php if($sort == 'price_desc') echo 'selected'; ?>>Giá: Cao đến thấp</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark py-3 rounded-3 fw-bold">LỌC SẢN PHẨM</button>
                        <a href="products.php" class="btn btn-outline-secondary py-3 rounded-3 fw-bold">XÓA BỘ LỌC</a>
                    </div>
                </form>
            </aside>

            <div class="col-lg-9">
                <div class="row g-4">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : 
                            // KIỂM TRA TRẠNG THÁI YÊU THÍCH
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
                            <div class="col-6 col-md-4 col-lg-3"> 
                                <div class="product-card-v3 h-100">
                                    <div class="product-img-wrapper-v3 small-card">
                                        <a href="detail.php?id=<?php echo $row['id']; ?>">
                                            <img src="<?php echo $row['image']; ?>" class="product-img-main-v3" alt="<?php echo $row['name']; ?>">
                                        </a>
                                        
                                        <button class="btn-wishlist-v3 add-to-wishlist" data-id="<?php echo $row['id']; ?>" title="Thêm vào yêu thích">
                                            <i class="<?php echo $is_wishlisted ? 'fa-solid text-danger' : 'fa-regular'; ?> fa-heart"></i>
                                        </button>
                                        
                                        <form action="cart.php" method="POST" class="quick-add-form-v32">
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
                                    
                                    <div class="product-info-v3 mt-3 text-center">
                                        <h6 class="product-name-v3 mb-1">
                                            <a href="detail.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                        </h6>
                                        <p class="product-price-v3 mb-0"><?php echo number_format($row['price']); ?>₫</p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 bg-white rounded-4 border border-dashed">
                            <h3 class="h4 fw-bold text-muted">Không tìm thấy sản phẩm</h3>
                            <p class="text-muted small">Vui lòng điều chỉnh lại bộ lọc của bạn.</p>
                            <a href="products.php" class="btn btn-dark rounded-pill px-4 mt-3">Quay lại cửa hàng</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($endPage > 1): ?>
                <nav class="d-flex justify-content-center mt-5 pt-4">
                    <ul class="pagination gap-2">
                        <?php 
                        $query_params = $_GET;
                        for ($i = 1; $i <= $endPage; $i++): 
                            $query_params['page'] = $i;
                            $page_url = "products.php?" . http_build_query($query_params);
                        ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 fw-bold <?php echo ($i == $page) ? 'bg-black text-white' : 'bg-white text-dark'; ?>" 
                                   href="<?php echo $page_url; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>