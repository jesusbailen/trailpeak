<?php
session_start();

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
  unset($_SESSION['cart'][$productId]);
}

header("Location: cart.php");
exit;
