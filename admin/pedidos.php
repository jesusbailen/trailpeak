<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$estadosPermitidos = ['pendiente', 'enviado', 'entregado'];

// Asegurar columna estado en pedido si falta.
$colExiste = $pdo->prepare("
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'estado'
");
$colExiste->execute([DB_NAME]);
$tieneEstado = (int)$colExiste->fetchColumn() > 0;

if (!$tieneEstado) {
  $pdo->exec("ALTER TABLE pedido ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
}
$pdo->exec("UPDATE pedido SET estado = 'pendiente' WHERE estado IS NULL OR estado = ''");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idPedido = (int)($_POST['id_pedido'] ?? 0);
  $estado = $_POST['estado'] ?? '';

  if ($idPedido > 0 && in_array($estado, $estadosPermitidos, true)) {
    $stmt = $pdo->prepare("UPDATE pedido SET estado = ? WHERE id_pedido = ?");
    $stmt->execute([$estado, $idPedido]);
    $_SESSION['flash_success'] = 'Estado actualizado';
  }

  header('Location: ' . BASE_URL . 'admin/pedidos.php');
  exit;
}

$stmt = $pdo->query("
  SELECT p.id_pedido, p.fecha, p.total, p.estado, u.email
  FROM pedido p
  INNER JOIN usuario u ON u.id_usuario = p.id_usuario
  ORDER BY p.fecha DESC
");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Pedidos</h1>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/index.php">Volver</a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
      </div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!$pedidos): ?>
      <div class="alert alert-info">No hay pedidos registrados.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Email</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pedidos as $p): ?>
              <tr>
                <td><?= (int)$p['id_pedido'] ?></td>
                <td><?= htmlspecialchars($p['fecha']) ?></td>
                <td><?= number_format((float)$p['total'], 2) ?> €</td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td style="min-width: 180px;">
                  <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="id_pedido" value="<?= (int)$p['id_pedido'] ?>">
                    <select name="estado" class="form-select form-select-sm">
                      <?php foreach ($estadosPermitidos as $estado): ?>
                        <option value="<?= $estado ?>" <?= ($p['estado'] ?? 'pendiente') === $estado ? 'selected' : '' ?>>
                          <?= ucfirst($estado) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit">Guardar</button>
                  </form>
                </td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary"
                     href="<?= BASE_URL ?>pedido.php?id=<?= (int)$p['id_pedido'] ?>">
                    Ver detalle
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

