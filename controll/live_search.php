<?php
require_once '../includes/db.php';

if (isset($_POST['query'])) {
    $key = mysqli_real_escape_string($conn, trim($_POST['query']));
    // Tăng giới hạn lên 6 sản phẩm cho bảng to
    $sql = "SELECT maSP, ten, gia, hinhAnh, soLuong FROM SanPham WHERE ten LIKE '%$key%' LIMIT 6";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<p class="text-uppercase text-muted extra-small fw-bold mb-3 tracking-widest mt-4">Sản phẩm gợi ý</p>';
        echo '<div class="row g-3">'; // Dùng grid để dàn hàng ngang
        
        while ($row = mysqli_fetch_assoc($result)) {
            $is_out_of_stock = ($row['soLuong'] <= 0);
            ?>
            <div class="col-md-6">
                <a href="detail.php?id=<?php echo $row['maSP']; ?>" class="live-search-item d-flex align-items-center p-3 text-decoration-none rounded-4 mb-2">
                    <div class="position-relative me-3">
                        <img src="<?php echo $row['hinhAnh']; ?>" width="60" height="70" class="rounded-3 object-fit-cover">
                        <?php if ($is_out_of_stock): ?>
                            <span class="badge bg-danger position-absolute top-50 start-50 translate-middle" style="font-size: 8px;">HẾT</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($row['ten']); ?></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold" style="color: #C4622D; font-size: 13px;"><?php echo number_format($row['gia']); ?>₫</span>
                            <?php if ($is_out_of_stock): ?>
                                <small class="text-muted" style="font-size: 10px;">(Hết hàng)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-light-gray ms-2" style="font-size: 12px;"></i>
                </a>
            </div>
            <?php
        }
        echo '</div>';
        // Nút xem tất cả dẫn sang trang products.php
        echo '<div class="text-center mt-4">
                <a href="products.php?keyword='.urlencode($key).'" class="btn btn-sm btn-dark rounded-pill px-4">Xem tất cả kết quả</a>
            </div>';
    } else {
        echo '<div class="text-center py-5"><p class="text-muted">Không tìm thấy sản phẩm...</p></div>';
    }
}
?>