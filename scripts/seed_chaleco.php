<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ((current_user()['rol'] ?? '') !== 'admin') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$mensaje = '';

$stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE nombre = ? LIMIT 1");
$stmt->execute(['Accesorios']);
$idCategoria = (int)$stmt->fetchColumn();

if ($idCategoria <= 0) {
  $mensaje = 'No existe la categoria Accesorios.';
} else {
  $stmt = $pdo->prepare("SELECT id_producto FROM producto WHERE nombre = ? LIMIT 1");
  $stmt->execute(['Chaleco']);
  $existe = (int)$stmt->fetchColumn();

  if ($existe > 0) {
    $mensaje = 'El producto Chaleco ya existe.';
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO producto (nombre, descripcion, precio, stock, id_categoria, activo)
      VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
      'Chaleco',
      'Chaleco tecnico para trail con ajuste ligero.',
      39.90,
      20,
      $idCategoria
    ]);
    $mensaje = 'Producto Chaleco creado correctamente.';
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear chaleco</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>admin/productos.php">Ir a productos</a>
  </div>
</body>
</html>
