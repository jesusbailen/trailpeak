<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ((current_user()['rol'] ?? '') !== 'admin') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$stmt = $pdo->query("
  SELECT COUNT(*) AS total_pedidos, COALESCE(SUM(total), 0) AS suma_total
  FROM pedido
");
$ventasTotales = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_pedidos' => 0, 'suma_total' => 0];

$stmt = $pdo->query("
  SELECT COALESCE(SUM(total), 0) AS ventas_30_dias
  FROM pedido
  WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$ventas30 = $stmt->fetchColumn();

$stmt = $pdo->query("
  SELECT p.nombre AS producto, SUM(dp.cantidad) AS unidades_vendidas,
         SUM(dp.cantidad * dp.precio_unitario) AS ingresos_estimados
  FROM detalle_pedido dp
  INNER JOIN producto p ON p.id_producto = dp.id_producto
  INNER JOIN pedido pe ON pe.id_pedido = dp.id_pedido
  GROUP BY dp.id_producto, p.nombre
  ORDER BY unidades_vendidas DESC
  LIMIT 10
");
$topProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
  SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(total) AS total_ingresos
  FROM pedido
  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY mes
  ORDER BY mes DESC
");
$ingresosMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ingresosMesLabels = [];
$ingresosMesValues = [];
foreach ($ingresosMes as $m) {
  $ingresosMesLabels[] = $m['mes'];
  $ingresosMesValues[] = (float)$m['total_ingresos'];
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Informes</h1>
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/index.php">Volver</a>
  </div>

  <div class="card card-body shadow-sm mb-4">
    <h2 class="h6 mb-3">Ventas totales</h2>
    <table class="table table-sm mb-0">
      <tr>
        <th>Total pedidos</th>
        <td><?= (int)$ventasTotales['total_pedidos'] ?></td>
      </tr>
      <tr>
        <th>Suma total ventas</th>
        <td><?= number_format((float)$ventasTotales['suma_total'], 2) ?> &euro;</td>
      </tr>
      <tr>
        <th>Ventas ultimos 30 dias</th>
        <td><?= number_format((float)$ventas30, 2) ?> &euro;</td>
      </tr>
    </table>
    <div class="mt-4">
      <canvas id="ventasTotalesChart" height="110"></canvas>
    </div>
  </div>

  <div class="card card-body shadow-sm mb-4">
    <h2 class="h6 mb-3">Top 10 productos mas vendidos</h2>
    <?php if (!$topProductos): ?>
      <div class="alert alert-info mb-0">Sin datos de ventas.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Producto</th>
              <th class="text-end">Unidades</th>
              <th class="text-end">Ingresos estimados</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topProductos as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['producto']) ?></td>
                <td class="text-end"><?= (int)$p['unidades_vendidas'] ?></td>
                <td class="text-end"><?= number_format((float)$p['ingresos_estimados'], 2) ?> &euro;</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card card-body shadow-sm">
    <h2 class="h6 mb-3">Ingresos por mes</h2>
    <?php if (!$ingresosMes): ?>
      <div class="alert alert-info mb-0">Sin datos para este periodo.</div>
    <?php else: ?>
      <div class="mb-4">
        <canvas id="ingresosMesChart" height="140"></canvas>
      </div>
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Mes</th>
              <th class="text-end">Total ingresos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ingresosMes as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['mes']) ?></td>
                <td class="text-end"><?= number_format((float)$m['total_ingresos'], 2) ?> &euro;</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const ventasTotalesCtx = document.getElementById('ventasTotalesChart');
  if (ventasTotalesCtx) {
    new Chart(ventasTotalesCtx, {
      type: 'bar',
      data: {
        labels: ['Total ventas', 'Ultimos 30 dias'],
        datasets: [{
          label: 'Euros',
          data: [<?= (float)$ventasTotales['suma_total'] ?>, <?= (float)$ventas30 ?>],
          backgroundColor: ['#2b2d2f', '#c65d2e']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (value) => value + ' €' }
          }
        }
      }
    });
  }

  const ingresosMesCtx = document.getElementById('ingresosMesChart');
  if (ingresosMesCtx) {
    new Chart(ingresosMesCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_reverse($ingresosMesLabels)) ?>,
        datasets: [{
          label: 'Ingresos mensuales',
          data: <?= json_encode(array_reverse($ingresosMesValues)) ?>,
          borderColor: '#2b2d2f',
          backgroundColor: 'rgba(198, 93, 46, 0.18)',
          fill: true,
          tension: 0.35
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (value) => value + ' €' }
          }
        }
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
