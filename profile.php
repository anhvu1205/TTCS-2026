<?php
session_start();
require_once 'includes/db.php';

// Kiểm tra nếu chưa đăng nhập thì đá về trang login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Lấy thông tin chi tiết người dùng từ database
$sql = "SELECT * FROM NguoiDung WHERE maND = '$user_id'";
$result = mysqli_query($conn, $sql);
$user_info = mysqli_fetch_assoc($result);

include 'includes/header.php';
?>

<main class="profile-page py-5" style="background-color: #FAFAF9; min-height: 80vh;">
    <div class="container max-w-7xl mx-auto px-lg-5">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="bg-white p-4 rounded-4 shadow-sm border-0">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder mx-auto mb-3">
                            <?php echo strtoupper(substr($user_info['ten'], 0, 1)); ?>
                        </div>
                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($user_info['ten']); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($user_info['tenDangNhap']); ?></p>
                    </div>
                    <div class="list-group list-group-flush small fw-bold">
                        <a href="profile.php" class="list-group-item list-group-item-action border-0 active-custom">Thông tin cá nhân</a>
                        <a href="#" class="list-group-item list-group-item-action border-0">Đơn hàng của tôi</a>
                        <a href="logout.php" class="list-group-item list-group-item-action border-0 text-danger">Đăng xuất</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border-0">
                    <h4 class="fw-bold mb-4">Thông tin cá nhân</h4>
                    
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">HỌ VÀ TÊN</label>
                                <input type="text" class="form-control custom-input" value="<?php echo htmlspecialchars($user_info['ten']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">SỐ ĐIỆN THOẠI</label>
                                <input type="text" class="form-control custom-input" value="<?php echo htmlspecialchars($user_info['soDienThoai'] ?: 'Chưa cập nhật'); ?>" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">ĐỊA CHỈ</label>
                                <textarea class="form-control custom-input" rows="3" readonly><?php echo htmlspecialchars($user_info['diaChi'] ?: 'Chưa cập nhật'); ?></textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark rounded-pill px-4 mt-4 small fw-bold">CHỈNH SỬA THÔNG TIN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.avatar-placeholder {
    width: 60px;
    height: 60px;
    background-color: #EDE8DF;
    color: #C4622D;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 24px;
    font-weight: bold;
}
.active-custom {
    background-color: #FAF7F2 !important;
    color: #C4622D !important;
    border-radius: 8px;
}
</style>

<?php include 'includes/footer.php'; ?>