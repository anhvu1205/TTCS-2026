<?php
session_start();

if (isset($_POST['cart_key']) && isset($_POST['new_qty'])) {
    $key = $_POST['cart_key'];
    $qty = (int)$_POST['new_qty'];

    if (isset($_SESSION['cart'][$key])) {
        if ($qty > 0) {
            $_SESSION['cart'][$key]['quantity'] = $qty;
            echo json_encode(['status' => 'success']);
        } else {
            unset($_SESSION['cart'][$key]);
            echo json_encode(['status' => 'success']);
        }
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>