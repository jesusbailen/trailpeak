<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ((current_user()['rol'] ?? '') !== 'admin') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$idActual = (int)(current_user()['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idUsuario = (int)($_POST['id_usuario'] ?? 0);
  $activar = isset($_POST['activar']) ? (int)$_POST['activar'] : null;

  if ($idUsuario > 0 && ($activar === 0 || $activar === 1)) {
    if ($idUsuario !== $idActual) {
      $stmt = $pdo->prepare("UPDATE usuario SET activo = ? WHERE id_usuario = ?");
      $stmt->execute([$activar, $idUsuario]);
    }
  }

  header('Location: ' . BASE_URL . 'admin/usuarios.php');
  exit;
}

$q = trim($_GET['q'] ?? '');
$order = $_GET['order'] ?? 'nombre_asc';

$where = [];
$params = [];
if ($q !== '') {
  $where[] = '(nombre LIKE ? OR email LIKE ? OR rol LIKE ?)';
  $like = '%' . $q . '%';
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

$orderSql = 'nombre ASC';
if ($order === 'nombre_desc') {
  $orderSql = 'nombre DESC';
} elseif ($order === 'email_asc') {
  $orderSql = 'email ASC';
} elseif ($order === 'email_desc') {
  $orderSql = 'email DESC';
} elseif ($order === 'rol_asc') {
  $orderSql = 'rol ASC';
} elseif ($order === 'rol_desc') {
  $orderSql = 'rol DESC';
}

$sql = "SELECT id_usuario, nombre, email, rol, activo FROM usuario";
if ($where) {
  $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY $orderSql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Usuarios</h1>
      <div class="d-flex gap-2">
        <a class="btn btn-primary" href="<?= BASE_URL ?>admin/usuario_form.php">Nuevo usuario</a>
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/index.php">Volver</a>
      </div>
    </div>

    <form class="row g-2 align-items-end mb-3" method="get" action="usuarios.php">
      <div class="col-md-6">
        <label class="form-label small">Busqueda</label>
        <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Nombre, email o rol">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Orden</label>
        <select name="order" class="form-select">
          <option value="nombre_asc" <?= ($order === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
          <option value="nombre_desc" <?= ($order === 'nombre_desc') ? 'selected' : '' ?>>Nombre (Z-A)</option>
          <option value="email_asc" <?= ($order === 'email_asc') ? 'selected' : '' ?>>Email (A-Z)</option>
          <option value="email_desc" <?= ($order === 'email_desc') ? 'selected' : '' ?>>Email (Z-A)</option>
          <option value="rol_asc" <?= ($order === 'rol_asc') ? 'selected' : '' ?>>Rol (A-Z)</option>
          <option value="rol_desc" <?= ($order === 'rol_desc') ? 'selected' : '' ?>>Rol (Z-A)</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-dark w-100" type="submit">Filtrar</button>
        <a class="btn btn-outline-secondary w-100" href="usuarios.php">Limpiar</a>
      </div>
    </form>

    <?php if (!$usuarios): ?>
      <div class="alert alert-info">No hay usuarios registrados.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><?= (int)$u['id_usuario'] ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td><?= ((int)$u['activo'] === 1) ? 'Activo' : 'Inactivo' ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>admin/usuario_form.php?id=<?= (int)$u['id_usuario'] ?>">Editar</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que quieres cambiar el estado de este usuario?');">
                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                    <input type="hidden" name="activar" value="<?= ((int)$u['activo'] === 1) ? 0 : 1 ?>">
                    <button class="btn btn-sm btn-outline-warning" type="submit" <?= ((int)$u['id_usuario'] === $idActual) ? 'disabled' : '' ?>>
                      <?= ((int)$u['activo'] === 1) ? 'Desactivar' : 'Activar' ?>
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

