<?php
session_start();
include 'includes/header.php';
require_once 'includes/db.php';

// 1. LẤY DANH SÁCH BÀI VIẾT (Giả định bạn có bảng BaiViet hoặc Blog)
// Nếu chưa có bảng này, bạn có thể tạo thủ công mảng dữ liệu để test giao diện
$sql_blog = "SELECT * FROM BaiViet ORDER BY ngayTao DESC LIMIT 20";
$result_blog = mysqli_query($conn, $sql_blog);
?>

<main class="blog-page-container pb-5" style="background-color: #ffffff; min-height: 100vh;">
    <div class="container max-w-5xl mx-auto px-4 lg:px-5 pt-24 lg:pt-28">
        
        <div class="mb-14">
            <p class="text-xs tracking-[0.3em] uppercase mb-3" style="color: #C4622D; font-weight: 700;">BLOG</p>
            <h1 class="display-6 fw-light tracking-wide">Tips phối đồ & xu hướng mới</h1>
        </div>

        <?php if ($result_blog && mysqli_num_rows($result_blog) > 0): ?>
            <div class="row g-5">
                <?php while ($post = mysqli_fetch_assoc($result_blog)): ?>
                    <div class="col-md-6">
                        <article class="blog-card group cursor-pointer">
                            <a href="blog-detail.php?id=<?php echo $post['maBV']; ?>" class="text-decoration-none">
                                <div class="blog-img-wrapper aspect-ratio-16-10 overflow-hidden mb-4" style="background-color: #f5f5f5; border-radius: 4px;">
                                    <?php if (!empty($post['hinhAnh'])): ?>
                                        <img src="<?php echo $post['hinhAnh']; ?>" 
                                             alt="<?php echo htmlspecialchars($post['tieuDe']); ?>" 
                                             class="img-fluid w-100 h-100 object-fit-cover transition-all duration-700">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                            <span class="text-muted small">SIMPLE FIT</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="blog-content">
                                    <?php if (!empty($post['tag'])): ?>
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="text-[10px] tracking-[0.2em] uppercase" style="color: #C4622D; font-size: 10px;">
                                                <?php echo htmlspecialchars($post['tag']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h2 class="h5 fw-medium tracking-wide text-dark mb-2 transition-colors post-title">
                                        <?php echo htmlspecialchars($post['tieuDe']); ?>
                                    </h2>
                                    
                                    <p class="text-muted small leading-relaxed line-clamp-2">
                                        <?php echo htmlspecialchars($post['moTaNgan']); ?>
                                    </p>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted small italic">Sắp có bài viết mới...</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>