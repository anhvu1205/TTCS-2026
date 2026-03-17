<?php
session_start();
include 'includes/header.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <h1 class="fw-black italic mb-4" style="letter-spacing: -1px; font-size: 3rem;">OUR STORY</h1>
                <p class="lead text-muted mb-5">
                    Chào mừng bạn đến với <strong>Simple Fit Store</strong> - Nơi định hình phong cách Streetwear tối giản nhưng đầy cá tính cho thế hệ mới.
                </p>

                <div class="map-container mb-4 shadow-sm rounded-4 overflow-hidden">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.292513222131!2d105.78500247510406!3d20.980912980655246!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135acce762c2bb9%3A0x2357473b0d28d0e9!2zSOG7YIHZp4buHbiBDw7RuZyBuZ2jhu4cgQsawdSBjaMOtbmggdmnhu4VuIHRow7RuZw!5e0!3m2!1svi!2s!4v1709545000000!5m2!1svi!2s"
                        width="100%"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

                <div class="row text-start mb-5 g-4">
                    <div class="col-md-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase">Địa Chỉ</h6>
                        <p class="text-muted small"><i class="fa-solid fa-location-dot me-2 text-danger"></i> 96A Đ. Trần Phú, Hà Đông, Hà Nội</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase">Liên Hệ</h6>
                        <p class="text-muted small"><i class="fa-solid fa-phone me-2 text-danger"></i> Hotline: 09xx xxx xxx</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase">Giờ Mở Cửa</h6>
                        <p class="text-muted small"><i class="fa-solid fa-clock me-2 text-danger"></i> 09:00 - 21:00 (Hàng ngày)</p>
                    </div>
                </div>

                <div class="text-start mt-4">
                    <h4 class="fw-bold text-dark border-bottom pb-2 mb-3">TẦM NHÌN CỦA CHÚNG TÔI</h4>
                    <p>Tại Simple Fit Store, chúng tôi tin rằng thời trang không chỉ là trang phục, mà là ngôn ngữ để bạn khẳng định bản thân mà không cần lên tiếng. Chúng tôi tập trung vào những thiết kế <strong>Minimalist</strong>, chú trọng vào chất liệu cotton cao cấp và form dáng <strong>Oversize</strong> chuẩn Streetwear.</p>

                    <h4 class="fw-bold text-dark border-bottom pb-2 mt-5 mb-3">CHẤT LƯỢNG LÀM NÊN THƯƠNG HIỆU</h4>
                    <p>Mỗi sản phẩm tại shop đều trải qua quy trình kiểm duyệt nghiêm ngặt từ khâu chọn vải đến kỹ thuật in ấn, đảm bảo mang đến trải nghiệm tốt nhất cho người mặc.</p>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <p class="small text-muted mb-2">THEO DÕI CHÚNG TÔI TẠI</p>
                    <div class="d-flex justify-content-center gap-4">
                        <a href="#" class="text-dark fs-4"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="text-dark fs-4"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-dark fs-4"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
