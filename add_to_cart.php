<?php
session_start();

// GUARDAR EN SESION LO QUE HAYA EN EL CARRTIO SEAS QUIEN SEAS

// Flujo compra: anadir producto -> guardar en carrito de sesion
$productId = (int)(filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) ?? 0);
$qty = (int)(filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT) ?? 1);
if ($productId <= 0) {
  header("Location: index.php");
  exit;
}

if ($qty < 1) {
  $qty = 1;
}
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;

header("Location: cart.php");
exit;
