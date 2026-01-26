<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ((current_user()['rol'] ?? '') !== 'admin') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$productos = [
  ['nombre' => 'Camiseta Trail', 'categoria' => 'Ropa técnica', 'precio' => 29.90, 'activo' => 1],
  ['nombre' => 'Chaleco', 'categoria' => 'Accesorios', 'precio' => 49.90, 'activo' => 1],
  ['nombre' => 'Mochila 5L', 'categoria' => 'Accesorios', 'precio' => 89.90, 'activo' => 1],
  ['nombre' => 'Short técnico', 'categoria' => 'Ropa técnica', 'precio' => 34.90, 'activo' => 1],
  ['nombre' => 'Zapatilla Mountain Pro', 'categoria' => 'Zapatillas', 'precio' => 129.90, 'activo' => 1],
];

$resultados = [];

try {
  $pdo->beginTransaction();

  $stmtCat = $pdo->prepare("SELECT id_categoria FROM categoria WHERE nombre = ? LIMIT 1");
  $stmtCatIns = $pdo->prepare("INSERT INTO categoria (nombre, activo) VALUES (?, 1)");
  $stmtProdSel = $pdo->prepare("SELECT id_producto FROM producto WHERE nombre = ? LIMIT 1");
  $stmtProdUpd = $pdo->prepare("
    UPDATE producto
    SET precio = ?, id_categoria = ?, activo = ?
    WHERE id_producto = ?
  ");
  $stmtProdIns = $pdo->prepare("
    INSERT INTO producto (nombre, descripcion, precio, stock, id_categoria, activo)
    VALUES (?, ?, ?, ?, ?, ?)
  ");

  foreach ($productos as $p) {
    $stmtCat->execute([$p['categoria']]);
    $idCategoria = (int)$stmtCat->fetchColumn();

    if ($idCategoria <= 0) {
      $stmtCatIns->execute([$p['categoria']]);
      $idCategoria = (int)$pdo->lastInsertId();
    }

    $stmtProdSel->execute([$p['nombre']]);
    $idProducto = (int)$stmtProdSel->fetchColumn();

    if ($idProducto > 0) {
      $stmtProdUpd->execute([$p['precio'], $idCategoria, $p['activo'], $idProducto]);
      $resultados[] = $p['nombre'] . ' actualizado.';
    } else {
      $stmtProdIns->execute([
        $p['nombre'],
        $p['nombre'],
        $p['precio'],
        50,
        $idCategoria,
        $p['activo']
      ]);
      $resultados[] = $p['nombre'] . ' creado.';
    }
  }

  $pdo->commit();
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  $resultados[] = 'Error: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Seed productos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h1 class="h4 mb-3">Resultado seed productos</h1>
    <div class="alert alert-info">
      <?php foreach ($resultados as $r): ?>
        <div><?= htmlspecialchars($r) ?></div>
      <?php endforeach; ?>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>admin/productos.php">Ir a productos</a>
  </div>
</body>
</html>
