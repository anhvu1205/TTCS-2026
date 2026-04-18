<?php
session_start();
require_once 'includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Query an toàn hơn
$sql_blog = "SELECT * FROM BaiViet ORDER BY maBV DESC LIMIT 20";
$result_blog = mysqli_query($conn, $sql_blog);

if (!$result_blog) {
    die("Lỗi truy vấn bài viết: " . mysqli_error($conn));
}

include 'includes/header.php';
?>

<main class="blog-page-container pb-5" style="background-color: #ffffff; min-height: 100vh;">
    <div class="container max-w-5xl mx-auto px-4 lg:px-5 pt-24 lg:pt-28">

        <div class="mb-14">
            <p class="text-xs tracking-[0.3em] uppercase mb-3" style="color: #C4622D; font-weight: 700;">BLOG</p>
            <h1 class="display-6 fw-light tracking-wide">Tips phối đồ & xu hướng mới</h1>
        </div>

        <?php if (mysqli_num_rows($result_blog) > 0): ?>
            <div class="row g-5">
                <?php while ($post = mysqli_fetch_assoc($result_blog)): ?>
                    <div class="col-md-6">
                        <article class="blog-card">
                            <a href="blog-detail.php?id=<?php echo $post['maBV']; ?>" class="text-decoration-none">
                                <div class="blog-img-wrapper aspect-ratio-16-10 overflow-hidden mb-4" style="background-color: #f5f5f5; border-radius: 4px;">
                                    <?php if (!empty($post['hinhAnh'])): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($post['hinhAnh']); ?>"
                                            alt="<?php echo htmlspecialchars($post['tieuDe'] ?? 'Bài viết'); ?>"
                                            class="img-fluid w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                            <span class="text-muted small">SIMPLE FIT</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="blog-content">
                                    <h2 class="h5 fw-medium tracking-wide text-dark mb-2 post-title">
                                        <?php echo htmlspecialchars($post['tieuDe'] ?? 'Chưa có tiêu đề'); ?>
                                    </h2>

                                    <p class="text-muted small leading-relaxed line-clamp-2">
                                        <?php
                                        if (isset($post['tomTat']) && $post['tomTat'] !== null && $post['tomTat'] !== '') {
                                            echo htmlspecialchars($post['tomTat']);
                                        } elseif (isset($post['moTaNgan']) && $post['moTaNgan'] !== null && $post['moTaNgan'] !== '') {
                                            echo htmlspecialchars($post['moTaNgan']);
                                        } else {
                                            echo 'Bài viết đang được cập nhật.';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted small italic">Không có bài viết nào trong bảng BaiViet.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>