<?php
// Flujo compra: pago cancelado, mostrar aviso en inicio
session_start();
$_SESSION['flash_warning'] = "⚠️ Pago cancelado. No se ha realizado ningún cargo.";
header("Location: index.php");
exit;
