<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  echo "<div class=\"container py-5\"><div class=\"alert alert-warning\">Producto no encontrado.</div></div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

$stmt = $pdo->prepare("
  SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_path, c.nombre AS categoria
  FROM producto p
  JOIN categoria c ON c.id_categoria = p.id_categoria
  WHERE p.id_producto = ? AND p.activo = 1
  LIMIT 1
");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
  http_response_code(404);
  echo "<div class=\"container py-5\"><div class=\"alert alert-warning\">Producto no encontrado.</div></div>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

function slugify(string $text): string {
  $text = trim($text);
  $text = strtolower($text);
  $map = [
    'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
    'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
    'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
    'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
    'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
    'ñ' => 'n', 'ç' => 'c'
  ];
  $text = strtr($text, $map);
  $text = preg_replace('/[^a-z0-9\\s-]/', '', $text);
  $text = preg_replace('/[\\s-]+/', '-', $text);
  return trim($text, '-');
}

$imagenesPorSlug = [
  'mochila-5l' => 'img/productos/mochila5L.png',
  'bolsa-hidratacion' => 'img/productos/bolsahidratacion.png',
  'bolsa-de-hidratacion' => 'img/productos/bolsahidratacion.png',
  'chaleco' => 'img/productos/chaleco.png',
  'short-tecnico' => 'img/productos/short.png',
  'camiseta-trail' => 'img/productos/camiseta.png',
  'zapatilla-mountain-pro' => 'img/productos/zapatillas.png',
  'zapatillas-speed-trail-pro' => 'img/productos/zapatillas2.png',
  'gorra' => 'img/ofertas/gorra.png',
  'cortavientos' => 'img/ofertas/cortavientos.png',
  'guantes' => 'img/ofertas/guantes.png',
];

$slugProd = slugify($producto['nombre'] ?? '');
$nombreLower = strtolower($producto['nombre'] ?? '');
$descripcionFallbacks = [
  'camiseta-trail' => 'Camiseta tecnica ligera y transpirable para entrenos y competicion, con secado rapido y tacto suave.',
  'short-tecnico' => 'Short tecnico con ajuste comodo y tejido ligero para mantener libertad de movimiento en rutas largas.',
  'zapatilla-mountain-pro' => 'Zapatilla con buena traccion y estabilidad para terreno mixto, pensada para salidas exigentes.',
  'mochila-5l' => 'Mochila de 5L para llevar lo esencial en tus salidas, con ajuste seguro y acceso rapido al material.',
  'chaleco' => 'Chaleco ligero y estable para entrenos intensos, con reparto equilibrado de carga.',
  'zapatillas-speed-trail-pro' => 'Zapatilla rapida y reactiva para ritmos altos, con buena respuesta en subidas y bajadas.',
  'bolsa-de-hidratacion' => 'Bolsa de hidratacion compacta para mantener el ritmo sin parar, ideal para entrenos cortos.',
  'bolsa-hidratacion' => 'Bolsa de hidratacion compacta para mantener el ritmo sin parar, ideal para entrenos cortos.',
  'guantes' => 'Guantes ligeros para dias frios, con buen agarre y tacto para uso continuo.',
  'cortavientos' => 'Cortavientos plegable con proteccion frente a rachas y cambio de clima en ruta.',
  'gorra' => 'Gorra tecnica ligera con visera flexible, perfecta para sol y sudor en carrera.',
];

$imgProd = $producto['imagen_path'] ?? '';
if ($imgProd === '') {
  $imgProd = $imagenesPorSlug[$slugProd] ?? '';
  if ($imgProd === '' && (strpos($slugProd, 'mochila') !== false || strpos($nombreLower, 'mochila') !== false)) {
    $imgProd = 'img/productos/mochila5L.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'bolsa-hidratacion') !== false || strpos($slugProd, 'bolsa-de-hidratacion') !== false || strpos($nombreLower, 'bolsa hidratacion') !== false || strpos($nombreLower, 'bolsa de hidratacion') !== false || strpos($nombreLower, 'bolsa de hidratación') !== false)) {
    $imgProd = 'img/productos/bolsahidratacion.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'chaleco') !== false || strpos($nombreLower, 'chaleco') !== false)) {
    $imgProd = 'img/productos/chaleco.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'short') !== false || strpos($nombreLower, 'short') !== false)) {
    $imgProd = 'img/productos/short.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'camiseta') !== false || strpos($nombreLower, 'camiseta') !== false)) {
    $imgProd = 'img/productos/camiseta.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'speed-trail-pro') !== false || strpos($nombreLower, 'speed trail pro') !== false)) {
    $imgProd = 'img/productos/zapatillas2.png';
  } elseif ($imgProd === '' && (strpos($slugProd, 'zapatilla') !== false || strpos($nombreLower, 'zapatilla') !== false)) {
    $imgProd = 'img/productos/zapatillas.png';
  } elseif ($imgProd === '' && $nombreLower === 'gorra') {
    $imgProd = 'img/ofertas/gorra.png';
  } elseif ($imgProd === '' && $nombreLower === 'cortavientos') {
    $imgProd = 'img/ofertas/cortavientos.png';
  } elseif ($imgProd === '' && $nombreLower === 'guantes') {
    $imgProd = 'img/ofertas/guantes.png';
  }
}
?>

<main>
  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($producto['nombre']) ?></h1>
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>index.php">Volver</a>
      </div>

      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-lg-5">
          <div class="row g-4 align-items-start">
            <div class="col-lg-5">
              <?php if ($imgProd !== ''): ?>
                <img
                  src="<?= BASE_URL . $imgProd ?>"
                  class="img-fluid rounded-4 shadow-sm"
                  alt="<?= htmlspecialchars($producto['nombre']) ?>"
                >
              <?php else: ?>
                <div class="product-placeholder rounded-4">
                  <i class="bi bi-image fs-1"></i>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-lg-7">
              <div class="mb-3">
                <span class="badge text-bg-light"><?= htmlspecialchars($producto['categoria']) ?></span>
              </div>
              <p class="text-muted mb-3">
                <?php
                  $desc = $producto['descripcion'] ?? '';
                  if ($desc === '') {
                    $desc = $descripcionFallbacks[$slugProd] ?? 'Producto tecnico seleccionado para rendimiento y comodidad en trail.';
                  }
                ?>
                <?= htmlspecialchars($desc) ?>
              </p>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-4"><?= number_format((float)$producto['precio'], 2, ',', '.') ?> &euro;</span>
                <span class="text-muted small">Stock: <?= (int)$producto['stock'] ?></span>
              </div>
              <!-- Flujo compra: anadir producto al carrito -->
              <form method="post" action="<?= BASE_URL ?>add_to_cart.php" class="d-flex align-items-center gap-2">
                <input type="hidden" name="product_id" value="<?= (int)$producto['id_producto'] ?>">
                <div class="input-group" style="max-width: 140px;">
                  <button class="btn btn-outline-secondary" type="button" id="qtyMinus">-</button>
                  <input type="number" class="form-control text-center" name="qty" id="qtyInput" value="1" min="1">
                  <button class="btn btn-outline-secondary" type="button" id="qtyPlus">+</button>
                </div>
                <button type="submit" class="btn btn-primary">Anadir al carrito</button>
              </form>

              <hr class="my-4">

              <div class="row g-3">
                <div class="col-md-4">
                  <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                      <h2 class="h6 fw-bold mb-2">Envio</h2>
                      <p class="text-muted small mb-2">Consulta plazos y condiciones.</p>
                      <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>envio.php">Ver envio</a>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                      <h2 class="h6 fw-bold mb-2">Devoluciones</h2>
                      <p class="text-muted small mb-2">Informacion sobre cambios.</p>
                      <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>politica.php">Ver politica</a>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                      <h2 class="h6 fw-bold mb-2">Condiciones</h2>
                      <p class="text-muted small mb-2">Terminos de compra.</p>
                      <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>condiciones.php">Ver condiciones</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
  (function () {
    const input = document.getElementById('qtyInput');
    const minus = document.getElementById('qtyMinus');
    const plus = document.getElementById('qtyPlus');
    if (!input || !minus || !plus) return;
    minus.addEventListener('click', function () {
      const current = parseInt(input.value || '1', 10);
      input.value = Math.max(1, current - 1);
    });
    plus.addEventListener('click', function () {
      const current = parseInt(input.value || '1', 10);
      input.value = current + 1;
    });
  })();
</script>
