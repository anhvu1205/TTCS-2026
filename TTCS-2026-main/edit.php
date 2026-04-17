<?php
session_start();
require_once 'includes/db.php';

// --- KIỂM TRA QUYỀN ADMIN ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header("Location: index.php");
    exit();
}

// --- KIỂM TRA ID SẢN PHẨM ---
if (!isset($_GET['id'])) {
    header("Location: admin.php?tab=products");
    exit();
}

$id = $_GET['id'];

// --- LẤY SẢN PHẨM ---
$result = mysqli_query($conn, "SELECT * FROM SanPham WHERE maSP = '$id'");
$product = mysqli_fetch_assoc($result);

// --- XỬ LÝ FORM ---
if(isset($_POST['update'])){

    $ten = $_POST['ten'];
    $gia = floatval($_POST['gia']);
    $soluong = intval($_POST['soluong']);
    $danhmuc = intval($_POST['danhmuc']);
    $thuonghieu = intval($_POST['thuonghieu']); // MỚI
    $kichco = $_POST['kichco'];                 // MỚI
    $mausac = $_POST['mausac'];                 // MỚI
    $chatlieu = $_POST['chatlieu'];             // MỚI
    $mota = $_POST['mota'];
    $hinhanh_link = $_POST['hinhanh'];
    $hinhanh_file = $_FILES['hinhanh_file'];

    // XỬ LÝ ẢNH UPLOAD
    $img_path = $product['hinhAnh']; // giữ ảnh cũ nếu ko đổi
    if(!empty($hinhanh_file['name'][0])){
        $tmp_name = $hinhanh_file['tmp_name'][0];
        $filename = time() . '_' . basename($hinhanh_file['name'][0]);
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $filename;
        if(move_uploaded_file($tmp_name, $target_file)){
            $img_path = $target_file;
        }
    } elseif(!empty($hinhanh_link[0])){
        $img_path = $hinhanh_link[0];
    }

    $ten_sp = mysqli_real_escape_string($conn, $ten);
    $mota_sp = mysqli_real_escape_string($conn, $mota);
    $kc_sp = mysqli_real_escape_string($conn, $kichco); // MỚI
    $ms_sp = mysqli_real_escape_string($conn, $mausac); // MỚI
    $cl_sp = mysqli_real_escape_string($conn, $chatlieu); // MỚI

    $sql = "UPDATE SanPham 
            SET ten='$ten_sp', gia='$gia', soLuong='$soluong', maDM='$danhmuc', 
                maTH='$thuonghieu', kichCo='$kc_sp', mauSac='$ms_sp', chatLieu='$cl_sp',
                hinhAnh='$img_path', moTa='$mota_sp'
            WHERE maSP='$id'";

    mysqli_query($conn,$sql);

    header("Location: admin.php?tab=products");
    exit();
}

include 'includes/header.php';
?>

<style>
.preview-img{
    max-width: 100%;
    max-height: 150px;
    display:block;
    margin-top:5px;
    border-radius:8px;
    border:1px solid #ddd;
}
</style>

<div class="container mt-5" style="max-width:900px">

<h3 class="mb-4">Sửa sản phẩm</h3>
<div style="background:#EDE8DF; padding:30px; border-radius:16px;" class="shadow-sm">
<form method="POST" enctype="multipart/form-data">

<div class="row g-3">

    <!-- Tên sản phẩm -->
    <div class="col-md-6">
        <label class="form-label">Tên sản phẩm</label>
        <input type="text" name="ten" class="form-control" value="<?php echo $product['ten']; ?>" required>
    </div>

    <!-- Thương hiệu (MỚI) -->
    <div class="col-md-6">
        <label class="form-label">Thương hiệu</label>
        <select name="thuonghieu" class="form-control">
            <?php
            $th = mysqli_query($conn,"SELECT * FROM ThuongHieu");
            while($t = mysqli_fetch_assoc($th)){
                $sel = $product['maTH'] == $t['maTH'] ? 'selected' : '';
                echo "<option value='{$t['maTH']}' $sel>{$t['ten']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Giá -->
    <div class="col-md-3">
        <label class="form-label">Giá</label>
        <input type="number" name="gia" class="form-control" value="<?php echo $product['gia']; ?>" required>
    </div>

    <!-- Số lượng -->
    <div class="col-md-3">
        <label class="form-label">Số lượng</label>
        <input type="number" name="soluong" class="form-control" value="<?php echo $product['soLuong']; ?>" required>
    </div>

    <!-- Danh mục -->
    <div class="col-md-6">
        <label class="form-label">Danh mục</label>
        <select name="danhmuc" class="form-control">
            <?php
            $dm = mysqli_query($conn,"SELECT * FROM DanhMuc");
            while($d = mysqli_fetch_assoc($dm)){
                $sel = $product['maDM'] == $d['maDM'] ? 'selected' : '';
                echo "<option value='{$d['maDM']}' $sel>{$d['ten']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Thông số chi tiết (MỚI) -->
    <div class="col-md-4">
        <label class="form-label">Kích cỡ</label>
        <input type="text" name="kichco" class="form-control" value="<?php echo $product['kichCo']; ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Màu sắc</label>
        <input type="text" name="mausac" class="form-control" value="<?php echo $product['mauSac']; ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Chất liệu</label>
        <input type="text" name="chatlieu" class="form-control" value="<?php echo $product['chatLieu']; ?>">
    </div>

    <!-- Mô tả -->
    <div class="col-md-6">
        <label class="form-label">Mô tả sản phẩm</label>
        <textarea name="mota" class="form-control" rows="3"><?php echo htmlspecialchars($product['moTa']); ?></textarea>
    </div>

    <!-- Ảnh -->
    <div class="col-md-6">
        <label class="form-label">Link ảnh</label>
        <input type="text" name="hinhanh[]" id="hinhanh" class="form-control mb-2" value="<?php echo $product['hinhAnh']; ?>" oninput="previewImg(this)">

        <label>Tải ảnh</label>
        <input type="file" name="hinhanh_file[]" class="form-control" onchange="previewImg(this)">

        <img id="preview" class="preview-img" src="<?php echo $product['hinhAnh']; ?>" alt="Preview ảnh">
    </div>

</div>

<div class="mt-4">
    <button type="submit" name="update" class="btn" style="background-color:#C06B3E; color:white; border-radius:8px;">
        Cập nhật sản phẩm
    </button>

    <a href="admin.php?tab=products" class="btn" style="background-color:#C06B3E; color:white; border-radius:8px; padding:6px 18px;">
        Quay lại
    </a>
</div>

</form>

</div>
</div>
<script>
function previewImg(input){
    let imgTag = document.getElementById('preview');
    if(input.type === 'file' && input.files && input.files[0]){
        let reader = new FileReader();
        reader.onload = function(e){
            imgTag.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    } else if(input.type === 'text'){
        imgTag.src = input.value;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
