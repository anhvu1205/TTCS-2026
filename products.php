<?php 
session_start();
include 'includes/header.php'; 
require_once 'includes/db.php';

// 1. KHỞI TẠO THAM SỐ (Tương đương useState trong React)
$limit = 12; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$cat = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : 'Tất cả';
$p_range = isset($_GET['price_range']) ? $_GET['price_range'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$cols = isset($_GET['cols']) ? (int)$_GET['cols'] : 4; // Mặc định 4 cột

// 2. XÂY DỰNG TRUY VẤN WHERE (Logic Filter từ React)
$where_clauses = ["1=1"];
if ($cat !== 'Tất cả') $where_clauses[] = "maDM = '$cat'";
if ($p_range !== 'all') {
    $prices = explode('-', $p_range);
    $where_clauses[] = "gia BETWEEN {$prices[0]} AND {$prices[1]}";
}
$where_sql = implode(" AND ", $where_clauses);

// Tính tổng số trang
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM SanPham WHERE $where_sql");
$total_data = mysqli_fetch_assoc($total_query);
$total_pages = ceil($total_data['total'] / $limit);

// 3. LOGIC SẮP XẾP (Logic Sort từ React)
$order_sql = " ORDER BY maSP DESC";
if ($sort === 'price-asc') $order_sql = " ORDER BY gia ASC";
if ($sort === 'price-desc') $order_sql = " ORDER BY gia DESC";

$sql = "SELECT maSP as id, ten as name, gia as price, hinhAnh as image FROM SanPham 
        WHERE $where_sql $order_sql LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Xác định Class cho Grid (Logic gridClass từ React)
$grid_class = ($cols === 4) ? "col-lg-2" : "col-lg-4"; 
?>

<main class="shop-page-container pb-5">
    <div class="container-fluid px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="py-10 border-bottom mb-4">
            <p class="text-xs tracking-widest uppercase mb-2" style="color: #C4622D;">Bộ sưu tập</p>
            <h1 class="display-6 fw-light">Cửa hàng</h1>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-5">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-sliders text-muted"></i>
                <form action="products.php" method="GET" id="filterForm" class="d-flex gap-2">
                    <select name="cat" class="filter-select-minimal" onchange="this.form.submit()">
                        <option value="Tất cả">Loại: Tất cả</option>
                        <option value="1" <?php if($cat == '1') echo 'selected'; ?>>Áo thun</option>
                        <option value="5" <?php if($cat == '5') echo 'selected'; ?>>Áo sơ mi</option>
                        <option value="2" <?php if($cat == '2') echo 'selected'; ?>>Quần jean</option>
                        <option value="3" <?php if($cat == '3') echo 'selected'; ?>>Áo khoác</option>
                        <option value="4" <?php if($cat == '4') echo 'selected'; ?>>Unisex</option>
                        <option value="6" <?php if($cat == '6') echo 'selected'; ?>>Quần short</option>
                        <option value="7" <?php if($cat == '7') echo 'selected'; ?>>Áo len</option>
                    </select>

                    <select name="price_range" class="filter-select-minimal" onchange="this.form.submit()">
                        <option value="all" <?php if($p_range == 'all') echo 'selected'; ?>>Giá: Tất cả</option>
                        <option value="200000-500000" <?php if($p_range == '200000-500000') echo 'selected'; ?>>200k - 500k</option>
                        <option value="500000-1000000" <?php if($p_range == '500000-1000000') echo 'selected'; ?>>500k - 1tr</option>
                        <option value="1000000-1500000" <?php if($p_range == '1000000-1500000') echo 'selected'; ?>>1tr - 1tr500</option>
                    </select>
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <input type="hidden" name="cols" value="<?php echo $cols; ?>">
                </form>
            </div>

            <div class="d-flex align-items-center gap-4">
                <span class="text-xs text-muted"><?php echo $total_data['total']; ?> sản phẩm</span>
                
                <form method="GET" id="sortForm" class="d-flex align-items-center gap-3">
    <!-- Giữ nguyên các filter hiện tại -->
    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat); ?>">
    <input type="hidden" name="price_range" value="<?php echo htmlspecialchars($p_range); ?>">
    <input type="hidden" name="cols" value="<?php echo $cols; ?>">

    <!-- Dropdown sort -->
    <select name="sort" class="sort-select-minimal" onchange="this.form.submit()">
        <option value="newest" <?php if($sort=='newest') echo 'selected'; ?>>Mới nhất</option>
        <option value="price-asc" <?php if($sort=='price-asc') echo 'selected'; ?>>Giá tăng dần</option>
        <option value="price-desc" <?php if($sort=='price-desc') echo 'selected'; ?>>Giá giảm dần</option>
    </select>
</form>

                <div class="d-none d-md-flex gap-2">
                    <a href="products.php?<?php echo http_build_query(array_merge($_GET, ['cols' => 4])); ?>" class="icon-btn <?php if($cols==4) echo 'active'; ?>"><i class="fa-solid fa-grid-4"></i></a>
                    <a href="products.php?<?php echo http_build_query(array_merge($_GET, ['cols' => 3])); ?>" class="icon-btn <?php if($cols==3) echo 'active'; ?>"><i class="fa-solid fa-grid-2"></i></a>
                </div>
            </div>
        </div>

        <div class="row g-4 lg:g-5">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-6 col-md-4 <?php echo $grid_class; ?>">
                        <div class="product-card-v2">
                            <div class="product-img-wrapper">
                                <a href="detail.php?id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $row['image']; ?>" class="product-img-main" alt="<?php echo $row['name']; ?>">
                                </a>
                                <form action="cart.php" method="POST" class="quick-add-form">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn-quick-add"><i class="fa-solid fa-plus"></i></button>
                                </form>
                            </div>
                            <div class="product-info-v2 mt-3 text-center">
                                <h6 class="product-name-v2 mb-1">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                </h6>
                                <p class="product-price-v2 mb-0"><?php echo number_format($row['price']); ?>₫</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="fs-1 text-muted opacity-25">¯\_(ツ)_/¯</p>
                    <p class="text-muted">Không tìm thấy sản phẩm phù hợp.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center ptit-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="products.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>