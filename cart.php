<?php
session_start();
require_once 'includes/db.php';

// --- THÊM SẢN PHẨM VÀO GIỎ ---
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'M';
    $color = isset($_POST['color']) ? $_POST['color'] : 'Trắng';

    $sql = "SELECT maSP, ten, gia, hinhAnh, soLuong FROM SanPham WHERE maSP = '$id'";
    $res = mysqli_query($conn, $sql);
    $p = mysqli_fetch_assoc($res);

    if ($p) {
        $stock = $p['soLuong'];

        // Tính số lượng đã có trong giỏ
        $cart_qty = 0;
        if(isset($_SESSION['cart'])){
            foreach($_SESSION['cart'] as $item){
                if($item['id'] == $id){
                    $cart_qty += $item['quantity'];
                }
            }
        }

        $remain = $stock - $cart_qty;
        if($remain < 0) $remain = 0;
        if($qty > $remain) $qty = $remain;
        if($qty <= 0){
            header("Location: cart.php");
            exit();
        }

        $cart_key = $id . "_" . $size . "_" . $color;

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'id' => $id,
                'name' => $p['ten'],
                'price' => $p['gia'],
                'image' => $p['hinhAnh'],
                'quantity' => $qty,
                'size' => $size,
                'color' => $color
            ];
        }
    }
    header("Location: cart.php");
    exit();
}

// --- XÓA SẢN PHẨM ---
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    if(isset($_SESSION['cart'][$id_remove])){
        unset($_SESSION['cart'][$id_remove]);
    }
    header("Location: cart.php");
    exit();
}

// --- CẬP NHẬT SỐ LƯỢNG ---
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $key => $qty) {
        if(!isset($_SESSION['cart'][$key])) continue;
        if ($qty <= 0) unset($_SESSION['cart'][$key]);
        else $_SESSION['cart'][$key]['quantity'] = $qty; // giữ nguyên số lượng user chọn
    }
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = ($subtotal >= 500000 || $subtotal == 0) ? 0 : 30000;
$total = $subtotal + $shipping;
?>

<main class="cart-page-wrapper pb-5">
<div class="container-fluid px-lg-5 max-w-6xl mx-auto min-h-screen">
<div class="py-10 border-bottom mb-5">
<h1 class="display-6 fw-light">
Giỏ hàng <span class="text-muted h4">(<?php echo count($cartItems); ?>)</span>
</h1>
</div>

<?php if(empty($cartItems)): ?>
<div class="text-center py-5">
<i class="fa-solid fa-bag-shopping display-1 text-light mb-4"></i>
<h2 class="h4 fw-light mb-3">Giỏ hàng của bạn đang trống</h2>
<p class="text-muted mb-5">Hãy khám phá và chọn sản phẩm yêu thích!</p>
<a href="products.php" class="btn btn-dark rounded-0 px-5 py-3 tracking-widest small">
MUA SẮM NGAY <i class="fa-solid fa-arrow-right ms-2"></i>
</a>
</div>
<?php else: ?>
<div class="row g-5">
<div class="col-lg-7">
<form action="cart.php" method="POST" id="cartForm">
<?php foreach($cartItems as $cart_key => $item): 
    $sql_stock = "SELECT soLuong FROM SanPham WHERE maSP='".$item['id']."'";
    $res_stock = mysqli_query($conn,$sql_stock);
    $row_stock = mysqli_fetch_assoc($res_stock);
    $stock = $row_stock['soLuong'];
?>
<div class="cart-item-row d-flex gap-4 py-4 border-bottom position-relative">
<div class="cart-img-box bg-light overflow-hidden" style="width: 100px; height: 130px;">
<img src="<?php echo $item['image']; ?>" class="w-100 h-100 object-fit-cover">
</div>
<div class="cart-info-box flex-grow-1 d-flex flex-column justify-content-between">
<div>
<div class="d-flex justify-content-between">
<a href="detail.php?id=<?php echo $item['id']; ?>" class="text-dark text-decoration-none fw-bold small">
<?php echo $item['name']; ?>
</a>
<a href="cart.php?remove=<?php echo $cart_key; ?>" class="text-muted hover-red">
<i class="fa-solid fa-trash-can small"></i>
</a>
</div>
<div class="d-flex gap-2 mt-2">
<?php if(isset($item['size'])): ?><span class="badge-outline">Size <?php echo $item['size']; ?></span><?php endif; ?>
<?php if(isset($item['color'])): ?><span class="badge-outline"><?php echo $item['color']; ?></span><?php endif; ?>
</div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
<div class="qty-control-minimal d-flex align-items-center border">
<button type="button" onclick="changeQty('<?php echo $cart_key; ?>', -1, <?php echo $stock; ?>)" class="btn btn-sm">-</button>
<input type="text" id="qty-<?php echo $cart_key; ?>" value="<?php echo $item['quantity']; ?>" 
readonly class="text-center border-0 small fw-bold" style="width:40px;">
<button type="button" onclick="changeQty('<?php echo $cart_key; ?>', 1, <?php echo $stock; ?>)" class="btn btn-sm">+</button>
</div>
<span class="fw-bold small" id="total-<?php echo $cart_key; ?>"><?php echo number_format($item['price'] * $item['quantity']); ?>₫</span>
<input type="hidden" name="quantity[<?php echo $cart_key; ?>]" id="hidden-<?php echo $cart_key; ?>" value="<?php echo $item['quantity']; ?>">
<input type="hidden" id="price-<?php echo $cart_key; ?>" value="<?php echo $item['price']; ?>">
</div>
</div>
</div>
<?php endforeach; ?>
<button type="submit" name="update_cart" class="btn btn-outline-dark btn-sm rounded-0 mt-3">Cập nhật giỏ hàng</button>
</form>
</div>

<div class="col-lg-5">
<div class="summary-card p-4 p-md-5 bg-light sticky-top" style="top: 100px;">
<h3 class="small fw-bold tracking-widest text-uppercase mb-4">Tóm tắt đơn hàng</h3>
<div class="d-flex justify-content-between mb-3 small">
<span class="text-muted">Tạm tính</span>
<span id="subtotal"><?php echo number_format($subtotal); ?>₫</span>
</div>
<div class="d-flex justify-content-between mb-4 small">
<span class="text-muted">Vận chuyển</span>
<span><?php echo $shipping==0?'Miễn phí':number_format($shipping).'₫'; ?></span>
</div>
<a href="checkout.php" class="btn btn-dark w-100 py-3 rounded-0 fw-bold tracking-widest small mb-4">
TIẾN HÀNH THANH TOÁN <i class="fa-solid fa-arrow-right ms-2"></i>
</a>
</div>
</div>
</div>
<?php endif; ?>
</div>
</main>

<script>
function changeQty(cart_key, delta, max){
    const input = document.getElementById('qty-' + cart_key);
    const hidden = document.getElementById('hidden-' + cart_key);
    const price = parseInt(document.getElementById('price-' + cart_key).value);
    let val = parseInt(input.value) + delta;
    if(val < 1) val = 1;
    if(val > max) val = max;
    input.value = val;
    hidden.value = val;

    // Cập nhật giá tạm tính
    document.getElementById('total-' + cart_key).innerText = (price * val).toLocaleString() + '₫';

    // Cập nhật subtotal tạm thời
    let subtotal = 0;
    document.querySelectorAll('input[id^="hidden-"]').forEach(function(h){
        let pid = h.id.replace('hidden-','');
        let p = parseInt(document.getElementById('price-'+pid).value);
        subtotal += p * parseInt(h.value);
    });
    document.getElementById('subtotal').innerText = subtotal.toLocaleString() + '₫';
}
</script>

<?php include 'includes/footer.php'; ?>