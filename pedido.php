<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/auth.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$idUsuario = (int)($_SESSION['user']['id_usuario'] ?? $_SESSION['user']['id'] ?? 0);
$idPedido  = (int)($_GET['id'] ?? 0);
$rol = $_SESSION['user']['rol'] ?? 'cliente';
$esStaff = in_array($rol, ['admin', 'empleado'], true);

// Comprobar si existe columna estado
$colExiste = $pdo->prepare("
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'estado'
");
$colExiste->execute([DB_NAME]);
$tieneEstado = (int)$colExiste->fetchColumn() > 0;

if ($idPedido <= 0 || (!$esStaff && $idUsuario <= 0)) {
  header('Location: ' . ($esStaff ? 'admin/pedidos.php' : 'mis_pedidos.php'));
  exit;
}

// 1) Verificar que el pedido pertenece al usuario
$sql = "SELECT id_pedido, total, fecha";
if ($tieneEstado) {
  $sql .= ", estado";
}
$sql .= " FROM pedido WHERE id_pedido = ?";
$params = [$idPedido];
if (!$esStaff) {
  $sql .= " AND id_usuario = ?";
  $params[] = $idUsuario;
}
$sql .= " LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
  http_response_code(404);
  echo "Pedido no encontrado.";
  exit;
}

// 2) Traer líneas del pedido
$stmt = $pdo->prepare("
  SELECT dp.id_producto, dp.cantidad, dp.precio_unitario, p.nombre
  FROM detalle_pedido dp
  INNER JOIN producto p ON p.id_producto = dp.id_producto
  WHERE dp.id_pedido = ?
  ORDER BY p.nombre ASC
");
$stmt->execute([$idPedido]);
$lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedido #<?= (int)$pedido['id_pedido'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark py-2">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php">
      <img src="<?= BASE_URL ?>img/ui/logo_trailpeak.png" alt="TrailPeak logo" height="32" />
      <span class="fw-bold">TRAILPEAK</span>
    </a>
  </div>
</nav>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Pedido <?= (int)$pedido['id_pedido'] ?></h1>
    <?php if ($esStaff): ?>
      <a class="btn btn-outline-secondary" href="admin/pedidos.php">Volver</a>
    <?php else: ?>
      <a class="btn btn-outline-secondary" href="mis_pedidos.php">Volver</a>
    <?php endif; ?>
  </div>

  <div class="card card-body mb-4">
    <div><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha']) ?></div>
    <div><strong>Total:</strong> <?= number_format((float)$pedido['total'], 2) ?> €</div>
    <?php if ($tieneEstado): ?>
      <div><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado'] ?? 'pendiente') ?></div>
    <?php endif; ?>
  </div>

  <?php if (!$lineas): ?>
    <div class="alert alert-warning">Este pedido no tiene líneas asociadas.</div>
  <?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="text-end">Precio</th>
          <th class="text-end">Cantidad</th>
          <th class="text-end">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lineas as $l): 
          $precio = (float)$l['precio_unitario'];
          $cant   = (int)$l['cantidad'];
          $sub    = $precio * $cant;
        ?>
          <tr>
            <td><?= htmlspecialchars($l['nombre']) ?></td>
            <td class="text-end"><?= number_format($precio, 2) ?> €</td>
            <td class="text-end"><?= $cant ?></td>
            <td class="text-end"><?= number_format($sub, 2) ?> €</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>

