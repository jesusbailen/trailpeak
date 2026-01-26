<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Solo clientes logueados
if (empty($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$idUsuario = (int)($_SESSION['user']['id_usuario'] ?? $_SESSION['user']['id'] ?? 0);

if ($idUsuario <= 0) {
  header('Location: login.php');
  exit;
}

// Obtener pedidos del usuario
$stmt = $pdo->prepare("
  SELECT id_pedido, total, fecha
  FROM pedido
  WHERE id_usuario = ?
  ORDER BY fecha DESC
");
$stmt->execute([$idUsuario]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Mis pedidos</h1>
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>index.php">Volver</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6 fw-bold mb-3">Historial de pedidos</h2>
      <?php if (!$pedidos): ?>
        <div class="alert alert-info mb-0">Aun no has realizado ningun pedido.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pedidos as $p): ?>
                <tr>
                  <td><?= (int)$p['id_pedido'] ?></td>
                  <td><?= htmlspecialchars($p['fecha']) ?></td>
                  <td><?= number_format((float)$p['total'], 2, ',', '.') ?> &euro;</td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>pedido.php?id=<?= (int)$p['id_pedido'] ?>">Ver detalle</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
