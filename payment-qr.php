<?php 
session_start();
require_once 'includes/db.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$total = isset($_GET['total']) ? intval($_GET['total']) : 0;
$user = $_SESSION['user'];
$maND = $user['maND'] ?? $user['id'];

// Tạo mã hiển thị chuyên nghiệp (Ví dụ: #17741001)
$display_order_id = "17741" . str_pad($order_id, 3, "0", STR_PAD_LEFT);

// 2. KIỂM TRA QUYỀN TRUY CẬP ĐƠN HÀNG
$check_order = mysqli_query($conn, "SELECT hoTen, soDienThoai FROM DonHang h 
                                    JOIN KhachHang k ON h.maKH = k.maKH 
                                    WHERE h.maDH = '$order_id' AND k.maND = '$maND'");

if (mysqli_num_rows($check_order) == 0) {
    die("Đơn hàng không hợp lệ hoặc bạn không có quyền truy cập.");
}

$order_data = mysqli_fetch_assoc($check_order);

// 3. THÔNG TIN NGÂN HÀNG & QR CODE
$bank = "TCB"; 
$account_no = "19072115990014";
$account_name = "TRAN LE ANH VU";
$description = "Thanh toan don hang " . $display_order_id;
$qr_url = "https://img.vietqr.io/image/{$bank}-{$account_no}-compact2.jpg?amount={$total}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($account_name);

// 4. XỬ LÝ KHI KHÁCH NHẤN "TÔI ĐÃ CHUYỂN KHOẢN"
if (isset($_POST['confirm_payment'])) {
    // Xử lý upload ảnh
    $proof_path = "";
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $target_dir = "assets/img/proofs/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION);
        $file_name = "proof_" . $order_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
            $proof_path = $target_file;
        }
    }

    // CẬP NHẬT TRẠNG THÁI VÀ LƯU ĐƯỜNG DẪN ẢNH
    mysqli_query($conn, "UPDATE DonHang SET trangThai = 'Chờ xác nhận', minhChungThanhToan = '$proof_path' WHERE maDH = '$order_id'");

    $_SESSION['order_success'] = ['name' => $order_data['hoTen'], 'phone' => $order_data['soDienThoai']];
    unset($_SESSION['cart']); 
    header("Location: checkout.php?step=success");
    exit();
}

include 'includes/header.php';
?>

<main class="py-5" style="background-color: #FAF7F2; min-height: 100vh; padding-top: 120px !important;">
    <div class="container" style="max-width: 500px;">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 text-center" style="background-color: #EDE8DF;">
                <h4 class="fw-bold mb-1" style="color: #1A1A1A; font-family: 'Cormorant Garamond', serif;">XÁC NHẬN CHUYỂN KHOẢN</h4>
                <p class="text-muted small mb-4">Mã đơn hàng: #<?php echo $display_order_id; ?></p>

                <!-- Vùng hiển thị mã QR -->
                <div class="mb-4 bg-white d-inline-block p-3 rounded-4 shadow-sm">
                    <img src="<?php echo $qr_url; ?>" alt="QR Thanh Toán" class="img-fluid" style="max-width: 280px;">
                    <p class="mt-2 mb-0 small text-muted"><i class="fa-solid fa-qrcode me-1"></i> Quét mã để thanh toán</p>
                </div>

                <!-- Vùng hiển thị số tiền -->
                <div class="p-3 mb-4 rounded-3" style="background-color: #D9E5D6; color: #5C6650;">
                    <span class="small d-block fw-medium">Số tiền cần thanh toán</span>
                    <strong class="fs-3"><?php echo number_format($total); ?>₫</strong>
                </div>

                <!-- Chi tiết tài khoản -->
                <div class="text-start p-4 rounded-4 mb-4 bg-white shadow-sm">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Chủ TK:</span>
                        <span class="fw-bold"><?php echo $account_name; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Ngân hàng:</span>
                        <span class="fw-bold">Techcombank</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">Số TK:</span>
                        <span class="fw-bold" style="color: #1976D2;"><?php echo $account_no; ?></span>
                    </div>
                    
                    <hr style="border-top: 1px dashed #D4CEBE;">

                    <div class="text-center mt-3">
                        <span class="text-muted small d-block mb-1">Nội dung chuyển khoản:</span>
                        <span class="info-value" style="color: #C4622D; font-weight: 700; font-size: 1.1rem; display: block;">
                            <?php echo $description; ?>
                        </span>
                    </div>
                </div>

                <!-- Thêm enctype vào form để gửi được file -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4 text-start">
                        <label class="small fw-bold mb-2 text-muted">TẢI ẢNH XÁC NHẬN CHUYỂN KHOẢN *</label>
                        <input type="file" name="payment_proof" class="form-control border-0 p-2" accept="image/*" required style="background-color: #fff;">
                    </div>
                    
                    <button type="submit" name="confirm_payment" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow-sm">
                        <i class="fa-solid fa-check-circle me-2"></i>TÔI ĐÃ CHUYỂN KHOẢN
                    </button>
                </form>
                
                <p class="extra-small text-muted mt-3 mb-0" style="font-size: 0.8rem;">
                    Hệ thống sẽ kiểm tra và xác nhận đơn hàng sau khi nhận được tiền.
                </p>
            </div>
        </div>
    </div>
</main>
<script>
function validateForm() {
    const file = document.getElementById('payment_proof').files[0];
    if (!file) {
        alert("Vui lòng tải ảnh minh chứng chuyển khoản!");
        return false;
    }
    return true;
}
</script>
<?php include 'includes/footer.php'; ?>