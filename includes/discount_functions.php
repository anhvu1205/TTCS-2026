<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getCartSubtotal(): float
{
    $subtotal = 0;

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    foreach ($_SESSION['cart'] as $item) {
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
        $subtotal += $price * $qty;
    }

    return $subtotal;
}

function findDiscountCode(mysqli $conn, string $code): ?array
{
    $code = strtoupper(trim($code));

    $sql = "SELECT * FROM discount_codes WHERE code = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }

    return null;
}

function validateDiscountCode(array $discount, float $subtotal): array
{
    $now = date('Y-m-d H:i:s');

    if ((int)$discount['is_active'] !== 1) {
        return ['valid' => false, 'message' => 'Mã giảm giá hiện không hoạt động.'];
    }

    if (!empty($discount['start_date']) && $now < $discount['start_date']) {
        return ['valid' => false, 'message' => 'Mã giảm giá chưa đến thời gian sử dụng.'];
    }

    if (!empty($discount['end_date']) && $now > $discount['end_date']) {
        return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
    }

    if ($discount['usage_limit'] !== null && (int)$discount['used_count'] >= (int)$discount['usage_limit']) {
        return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
    }

    if ($subtotal < (float)$discount['min_order_value']) {
        return ['valid' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp mã.'];
    }

    return ['valid' => true, 'message' => 'Mã hợp lệ.'];
}

function calculateDiscountAmount(array $discount, float $subtotal): float
{
    $amount = 0;
    $type = strtolower((string)$discount['discount_type']);

    if ($type === 'percent') {
        $amount = $subtotal * ((float)$discount['discount_value'] / 100);

        if ($discount['max_discount'] !== null && $discount['max_discount'] !== '') {
            $amount = min($amount, (float)$discount['max_discount']);
        }
    } elseif ($type === 'fixed') {
        $amount = (float)$discount['discount_value'];
    }

    if ($amount > $subtotal) {
        $amount = $subtotal;
    }

    if ($amount < 0) {
        $amount = 0;
    }

    return $amount;
}

function applyDiscountCode(mysqli $conn, string $code): array
{
    $code = strtoupper(trim($code));
    $subtotal = getCartSubtotal();

    if ($code === '') {
        return ['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.'];
    }

    $discount = findDiscountCode($conn, $code);

    if (!$discount) {
        return ['success' => false, 'message' => 'Mã giảm giá không tồn tại.'];
    }

    $check = validateDiscountCode($discount, $subtotal);

    if (!$check['valid']) {
        return ['success' => false, 'message' => $check['message']];
    }

    $discountAmount = calculateDiscountAmount($discount, $subtotal);

    $_SESSION['discount'] = [
        'id' => (int)$discount['id'],
        'code' => $discount['code'],
        'type' => $discount['discount_type'],
        'value' => (float)$discount['discount_value'],
        'amount' => $discountAmount
    ];

    return [
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công.',
        'discount_amount' => $discountAmount
    ];
}

function removeDiscountCode(): void
{
    unset($_SESSION['discount']);
}

function getAppliedDiscountAmount(): float
{
    return isset($_SESSION['discount']['amount']) ? (float)$_SESSION['discount']['amount'] : 0;
}

function getAppliedDiscountCode(): ?string
{
    return isset($_SESSION['discount']['code']) ? (string)$_SESSION['discount']['code'] : null;
}

function getCartTotalAfterDiscount(): float
{
    $subtotal = getCartSubtotal();
    $discount = getAppliedDiscountAmount();

    return max($subtotal - $discount, 0);
}

function increaseDiscountUsedCount(mysqli $conn, int $discountId): void
{
    $sql = "UPDATE discount_codes SET used_count = used_count + 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, "i", $discountId);
    mysqli_stmt_execute($stmt);
}