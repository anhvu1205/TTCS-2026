<?php
session_start();
require_once 'includes/db.php';

// --- KIỂM TRA QUYỀN ADMIN ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header("Location: index.php");
    exit();
}

// --- XỬ LÝ FORM ---
if(isset($_POST['save'])){
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];
    $soluong = $_POST['soluong'];
    $danhmuc = $_POST['danhmuc'];
    $thuonghieu = $_POST['thuonghieu']; // MỚI
    $kichthuoc = $_POST['kichthuoc'];   // MỚI
    $mausac = $_POST['mausac'];         // MỚI
    $chatlieu = $_POST['chatlieu'];     // MỚI
    $mota = $_POST['mota'];
    $hinhanh_link = $_POST['hinhanh'];
    $hinhanh_file = $_FILES['hinhanh_file'];

    for($i=0; $i<count($ten); $i++){
        if($ten[$i] != ""){
            // XỬ LÝ ẢNH UPLOAD
            $img_path = '';
            if(!empty($hinhanh_file['name'][$i])){
                $tmp_name = $hinhanh_file['tmp_name'][$i];
                $filename = time() . '_' . basename($hinhanh_file['name'][$i]);
                $target_dir = "uploads/";
                if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $target_file = $target_dir . $filename;
                if(move_uploaded_file($tmp_name, $target_file)){
                    $img_path = $target_file;
                }
            } elseif(!empty($hinhanh_link[$i])){
                $img_path = $hinhanh_link[$i];
            }

            $ten_sp = mysqli_real_escape_string($conn, $ten[$i]);
            $gia_sp = floatval($gia[$i]);
            $soluong_sp = intval($soluong[$i]);
            $dm_sp = intval($danhmuc[$i]);
            $th_sp = intval($thuonghieu[$i]); // MỚI
            $kt_sp = mysqli_real_escape_string($conn, $kichthuoc[$i]); // MỚI
            $ms_sp = mysqli_real_escape_string($conn, $mausac[$i]);     // MỚI
            $cl_sp = mysqli_real_escape_string($conn, $chatlieu[$i]);   // MỚI
            $mota_sp = mysqli_real_escape_string($conn, $mota[$i]);

            $sql = "INSERT INTO SanPham(ten, gia, soLuong, maDM, maTH, kichCo, mauSac, chatLieu, hinhAnh, moTa)
                    VALUES('$ten_sp', '$gia_sp', '$soluong_sp', '$dm_sp', '$th_sp', '$kt_sp', '$ms_sp', '$cl_sp', '$img_path', '$mota_sp')";
            mysqli_query($conn, $sql);
        }
    }

    header("Location: admin.php?tab=products");
    exit();
}

// Chuẩn bị dữ liệu danh mục và thương hiệu để dùng cho cả HTML và Javascript
$dm_options = "";
$dm_query = mysqli_query($conn, "SELECT * FROM DanhMuc");
while($d = mysqli_fetch_assoc($dm_query)) {
    $dm_options .= "<option value='".$d['maDM']."'>".$d['ten']."</option>";
}

$th_options = "";
$th_query = mysqli_query($conn, "SELECT * FROM ThuongHieu");
while($t = mysqli_fetch_assoc($th_query)) {
    $th_options .= "<option value='".$t['maTH']."'>".$t['ten']."</option>";
}

include 'includes/header.php';
?>

<style>
.add-card{ background:#EDE8DF; padding:30px; border-radius:16px; }
.preview-img{ max-width: 100%; max-height: 80px; display:block; margin-top:5px; border-radius:8px; }
.remove-product{ background-color:#DC2626; color:white; border:none; width:28px; height:28px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; margin-top:5px; }
.product-item{ padding-top:10px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
</style>

<main class="py-5" style="padding-top:120px !important;">
<div class="container-fluid px-4">
<h3 class="mb-4">Thêm sản phẩm</h3>
<div class="add-card shadow-sm">
<form method="POST" enctype="multipart/form-data">
<div id="product-list">

<div class="row product-item mb-3">
    <div class="col-md-2">
        <label>Tên & Thương hiệu</label>
        <input type="text" name="ten[]" class="form-control mb-1" placeholder="Tên SP" required>
        <select name="thuonghieu[]" class="form-control">
            <?php echo $th_options; ?>
        </select>
        <button type="button" class="remove-product" title="Xóa dòng"><i class="fa-solid fa-trash"></i></button>
    </div>

    <div class="col-md-2">
        <label>Giá & Số lượng</label>
        <input type="number" name="gia[]" class="form-control mb-1" placeholder="Giá">
        <input type="number" name="soluong[]" class="form-control" placeholder="Số lượng">
    </div>

    <div class="col-md-2">
        <label>Size & Màu sắc</label>
        <input type="text" name="kichthuoc[]" class="form-control mb-1" placeholder="Size (S,M,L...)">
        <input type="text" name="mausac[]" class="form-control" placeholder="Màu sắc">
    </div>

    <div class="col-md-2">
        <label>Danh mục & Chất liệu</label>
        <select name="danhmuc[]" class="form-control mb-1">
            <?php echo $dm_options; ?>
        </select>
        <input type="text" name="chatlieu[]" class="form-control" placeholder="Chất liệu">
    </div>

    <div class="col-md-4">
        <label>Mô tả & Ảnh</label>
        <textarea name="mota[]" class="form-control mb-1" placeholder="Mô tả sản phẩm" rows="1"></textarea>
        <input type="text" name="hinhanh[]" class="form-control mb-1" placeholder="Link ảnh" oninput="previewImg(this)">
        <input type="file" name="hinhanh_file[]" class="form-control" onchange="previewImg(this)">
        <img class="preview-img" src="" alt="" />
    </div>
</div>

</div>

<button type="button" class="btn mb-3" style="background-color: #C06B3E; color: white;" onclick="addProduct()">+ Thêm sản phẩm</button>
<br>
<button type="submit" name="save" class="btn" style="background-color: #C06B3E; color: white;">Lưu sản phẩm</button>
<a href="admin.php?tab=products" class="btn" style="background-color: #C06B3E; color: white;">Quay lại</a>
</form>
</div>
</div>
</main>

<script>
function addProduct(){
    var div = document.createElement("div");
    div.classList.add("row","product-item","mb-3");
    div.innerHTML = `
    <div class="col-md-2">
        <input type="text" name="ten[]" class="form-control mb-1" placeholder="Tên SP">
        <select name="thuonghieu[]" class="form-control"><?php echo $th_options; ?></select>
        <button type="button" class="remove-product" title="Xóa dòng"><i class="fa-solid fa-trash"></i></button>
    </div>
    <div class="col-md-2">
        <input type="number" name="gia[]" class="form-control mb-1" placeholder="Giá">
        <input type="number" name="soluong[]" class="form-control" placeholder="Số lượng">
    </div>
    <div class="col-md-2">
        <input type="text" name="kichthuoc[]" class="form-control mb-1" placeholder="Size">
        <input type="text" name="mausac[]" class="form-control" placeholder="Màu sắc">
    </div>
    <div class="col-md-2">
        <select name="danhmuc[]" class="form-control mb-1"><?php echo $dm_options; ?></select>
        <input type="text" name="chatlieu[]" class="form-control" placeholder="Chất liệu">
    </div>
    <div class="col-md-4">
        <textarea name="mota[]" class="form-control mb-1" placeholder="Mô tả" rows="1"></textarea>
        <input type="text" name="hinhanh[]" class="form-control mb-1" placeholder="Link ảnh" oninput="previewImg(this)">
        <input type="file" name="hinhanh_file[]" class="form-control" onchange="previewImg(this)">
        <img class="preview-img" src="" alt="" />
    </div>`;

    document.getElementById("product-list").appendChild(div);
    div.querySelector(".remove-product").addEventListener("click", function(){ div.remove(); });
}

function previewImg(input){
    let row = input.closest('.col-md-4');
    let imgTag = row.querySelector('.preview-img');
    if(input.type === 'file' && input.files && input.files[0]){
        let reader = new FileReader();
        reader.onload = function(e){ imgTag.src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    } else if(input.type === 'text'){
        imgTag.src = input.value;
    }
}

document.querySelectorAll('.remove-product').forEach(btn=>{
    btn.addEventListener('click', function(){ btn.closest('.product-item').remove(); });
});
</script>

<?php include 'includes/footer.php'; ?>
