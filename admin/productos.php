<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idProducto = (int)($_POST['id_producto'] ?? 0);
  $activar = isset($_POST['activar']) ? (int)$_POST['activar'] : null;

  if ($idProducto > 0 && ($activar === 0 || $activar === 1)) {
    $stmt = $pdo->prepare("UPDATE producto SET activo = ? WHERE id_producto = ?");
    $stmt->execute([$activar, $idProducto]);
  }

  header('Location: ' . BASE_URL . 'admin/productos.php');
  exit;
}

$q = trim($_GET['q'] ?? '');
$order = $_GET['order'] ?? 'nombre_asc';

$where = [];
$params = [];
if ($q !== '') {
  $where[] = '(p.nombre LIKE ? OR p.descripcion LIKE ? OR p.sku LIKE ?)';
  $like = '%' . $q . '%';
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

$orderSql = 'p.nombre ASC';
if ($order === 'nombre_desc') {
  $orderSql = 'p.nombre DESC';
} elseif ($order === 'precio_asc') {
  $orderSql = 'p.precio ASC';
} elseif ($order === 'precio_desc') {
  $orderSql = 'p.precio DESC';
}

$sql = "
  SELECT p.id_producto, p.sku, p.nombre, p.precio, p.activo, c.nombre AS categoria
  FROM producto p
  INNER JOIN categoria c ON c.id_categoria = p.id_categoria
";
if ($where) {
  $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY $orderSql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Productos</h1>
      <div class="d-flex gap-2">
        <a class="btn btn-primary" href="<?= BASE_URL ?>admin/producto_form.php">Nuevo producto</a>
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/index.php">Volver</a>
      </div>
    </div>

    <form class="row g-2 align-items-end mb-3" method="get" action="productos.php">
      <div class="col-md-6">
        <label class="form-label small">Búsqueda</label>
        <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Nombre, descripción o SKU">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Orden</label>
        <select name="order" class="form-select">
          <option value="nombre_asc" <?= ($order === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
          <option value="nombre_desc" <?= ($order === 'nombre_desc') ? 'selected' : '' ?>>Nombre (Z-A)</option>
          <option value="precio_asc" <?= ($order === 'precio_asc') ? 'selected' : '' ?>>Precio (menor)</option>
          <option value="precio_desc" <?= ($order === 'precio_desc') ? 'selected' : '' ?>>Precio (mayor)</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-dark w-100" type="submit">Filtrar</button>
        <a class="btn btn-outline-secondary w-100" href="productos.php">Limpiar</a>
      </div>
    </form>

    <?php if (!$productos): ?>
      <div class="alert alert-info">No hay productos registrados.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Categoría</th>
              <th class="text-end">Precio</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['sku'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td class="text-end"><?= number_format((float)$p['precio'], 2) ?> €</td>
                <td><?= ((int)$p['activo'] === 1) ? 'Activo' : 'Inactivo' ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>admin/producto_form.php?id=<?= (int)$p['id_producto'] ?>">Editar</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que quieres cambiar el estado de este producto?');">
                    <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                    <input type="hidden" name="activar" value="<?= ((int)$p['activo'] === 1) ? 0 : 1 ?>">
                    <button class="btn btn-sm btn-outline-warning" type="submit">
                      <?= ((int)$p['activo'] === 1) ? 'Desactivar' : 'Activar' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

