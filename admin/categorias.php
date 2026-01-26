<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$idCategoria = (int)($_GET['id'] ?? 0);
$q = trim($_GET['q'] ?? '');
$order = $_GET['order'] ?? 'nombre_asc';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';
  $idCategoriaPost = (int)($_POST['id_categoria'] ?? $idCategoria ?? 0);

  if ($accion === 'toggle' && $idCategoriaPost > 0) {
    $activar = isset($_POST['activar']) ? (int)$_POST['activar'] : 0;
    $stmt = $pdo->prepare("UPDATE categoria SET activo = ? WHERE id_categoria = ?");
    $stmt->execute([$activar, $idCategoriaPost]);
    header('Location: ' . BASE_URL . 'admin/categorias.php');
    exit;
  }

  if ($accion === 'save') {
    $nombre = trim($_POST['nombre'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    $idPadre = (int)($_POST['id_padre'] ?? 0);
    $idPadre = $idPadre > 0 ? $idPadre : null;

    if ($nombre === '') {
      $errores[] = 'El nombre es obligatorio.';
    }
    if ($idCategoriaPost > 0 && $idPadre === $idCategoriaPost) {
      $errores[] = 'Una categoria no puede ser su propio padre.';
    }

    if (!$errores) {
      if ($idCategoriaPost > 0) {
        $stmt = $pdo->prepare("UPDATE categoria SET nombre = ?, id_padre = ?, activo = ? WHERE id_categoria = ?");
        $stmt->execute([$nombre, $idPadre, $activo, $idCategoriaPost]);
      } else {
        $stmt = $pdo->prepare("INSERT INTO categoria (nombre, id_padre, activo) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $idPadre, $activo]);
      }
      header('Location: ' . BASE_URL . 'admin/categorias.php');
      exit;
    }
  }
}

$categoriaEditar = ['id_categoria' => 0, 'nombre' => '', 'id_padre' => null, 'activo' => 1];
if ($idCategoria > 0) {
  $stmt = $pdo->prepare("SELECT id_categoria, nombre, id_padre, activo FROM categoria WHERE id_categoria = ? LIMIT 1");
  $stmt->execute([$idCategoria]);
  $categoriaEditar = $stmt->fetch(PDO::FETCH_ASSOC) ?: $categoriaEditar;
}

$where = [];
$params = [];
if ($q !== '') {
  $where[] = 'c.nombre LIKE ?';
  $params[] = '%' . $q . '%';
}

$orderSql = 'c.nombre ASC';
if ($order === 'nombre_desc') {
  $orderSql = 'c.nombre DESC';
}

$sql = "
  SELECT c.id_categoria, c.nombre, c.id_padre, c.activo, p.nombre AS padre_nombre
  FROM categoria c
  LEFT JOIN categoria p ON p.id_categoria = c.id_padre
";
if ($where) {
  $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY $orderSql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre");
$categoriasPadre = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Categorías</h1>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/index.php">Volver</a>
    </div>

    <div class="card card-body shadow-sm mb-4">
      <h2 class="h6 mb-3"><?= $categoriaEditar['id_categoria'] ? 'Editar categoría' : 'Nueva categoría' ?></h2>

      <?php if ($errores): ?>
        <div class="alert alert-danger">
          <?php foreach ($errores as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>admin/categorias.php">
        <input type="hidden" name="accion" value="save">
        <input type="hidden" name="id_categoria" value="<?= (int)$categoriaEditar['id_categoria'] ?>">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($categoriaEditar['nombre']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoria padre</label>
            <select name="id_padre" class="form-select">
              <option value="">Sin padre</option>
              <?php foreach ($categoriasPadre as $c): ?>
                <?php if ((int)$c['id_categoria'] === (int)$categoriaEditar['id_categoria']) continue; ?>
                <option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)($categoriaEditar['id_padre'] ?? 0) === (int)$c['id_categoria']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" name="activo" id="activo" <?= ((int)$categoriaEditar['activo'] === 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="activo">Activa</label>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </form>
    </div>

    <form class="row g-2 align-items-end mb-3" method="get" action="categorias.php">
      <div class="col-md-8">
        <label class="form-label small">Busqueda</label>
        <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Nombre de categoria">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Orden</label>
        <select name="order" class="form-select">
          <option value="nombre_asc" <?= ($order === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
          <option value="nombre_desc" <?= ($order === 'nombre_desc') ? 'selected' : '' ?>>Nombre (Z-A)</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-dark w-100" type="submit">Filtrar</button>
        <a class="btn btn-outline-secondary w-100" href="categorias.php">Limpiar</a>
      </div>
    </form>

    <?php if (!$categorias): ?>
      <div class="alert alert-info">No hay categorías registradas.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Padre</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categorias as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['padre_nombre'] ?? '-') ?></td>
                <td><?= ((int)$c['activo'] === 1) ? 'Activa' : 'Inactiva' ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>admin/categorias.php?id=<?= (int)$c['id_categoria'] ?>">Editar</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que quieres cambiar el estado de esta categoría?');">
                    <input type="hidden" name="accion" value="toggle">
                    <input type="hidden" name="id_categoria" value="<?= (int)$c['id_categoria'] ?>">
                    <input type="hidden" name="activar" value="<?= ((int)$c['activo'] === 1) ? 0 : 1 ?>">
                    <button class="btn btn-sm btn-outline-warning" type="submit">
                      <?= ((int)$c['activo'] === 1) ? 'Desactivar' : 'Activar' ?>
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

