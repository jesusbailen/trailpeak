<?php
session_start();

// Flujo compra: ajustar cantidades del carrito en sesion
$productId = (int)(filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) ?? 0);
$action = (string)(filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW) ?? '');

if ($productId <= 0) {
  header("Location: cart.php");
  exit;
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$current = (int)($_SESSION['cart'][$productId] ?? 0);

if ($action === 'inc') {
  $current++;
} elseif ($action === 'dec') {
  $current--;
}

if ($current <= 0) {
  unset($_SESSION['cart'][$productId]);
} else {
  $_SESSION['cart'][$productId] = $current;
}

header("Location: cart.php");
exit;
