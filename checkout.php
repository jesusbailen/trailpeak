<?php
declare(strict_types=1);

// DEBUG (temporal)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/lib/auth.php';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/stripe.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  exit("Carrito vacío: añade un producto antes de pagar.");
}

// Compra como invitado
if (!is_logged_in()) {
  $errors = [];
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nombre === '') {
      $errors[] = 'El nombre es obligatorio.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'El email no es válido.';
    }

    if (!$errors) {
      $_SESSION['guest'] = ['nombre' => $nombre, 'email' => $email];
      header('Location: checkout.php');
      exit;
    }
  }

  if (empty($_SESSION['guest'])) {
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Checkout invitado</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
      <div class="container py-5">
        <h1 class="h4 mb-3">Compra como invitado</h1>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
              <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" class="card card-body shadow-sm">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Continuar a pago</button>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
  }
}

/**
 * Construye la base URL automáticamente (local/hosting)
 * Ejemplos:
 *  - https://trailpeak.ct.ws/ud6/TiendaTrailpeak_FINAL
 *  - http://localhost/ud6/ud6/Tienda_Trailpeak_FINAL
 */
function getBaseUrl(): string {
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
  $scheme = $isHttps ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

  $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $scheme . '://' . $host . $dir;
}

$baseUrl = getBaseUrl();

// IDs del carrito (claves) => deben ser id_producto
$ids = array_map('intval', array_keys($cart));
$ids = array_values(array_filter($ids, fn($v) => $v > 0));

if (empty($ids)) {
  exit("Carrito inválido: no hay IDs de producto válidos.");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
  SELECT id_producto, nombre, precio
  FROM producto
  WHERE id_producto IN ($placeholders) AND activo = 1
");
$stmt->execute($ids);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$productos) {
  exit("No se han encontrado productos en BD para los IDs del carrito.");
}

$lineItems = [];
foreach ($productos as $p) {
  $idProducto = (int)$p['id_producto'];
  $qty = (int)($cart[$idProducto] ?? 0);
  if ($qty <= 0) continue;

  $lineItems[] = [
    'price_data' => [
      'currency' => 'eur',
      'product_data' => [
        'name' => (string)$p['nombre'],
      ],
      'unit_amount' => (int) round(((float)$p['precio']) * 100),
    ],
    'quantity' => $qty,
  ];
}

if (empty($lineItems)) {
  exit("Line items vacío. Revisa cantidades del carrito.");
}

try {
  $session = \Stripe\Checkout\Session::create([
    'mode' => 'payment',
    'line_items' => $lineItems,
    'success_url' => $baseUrl . "/success.php?session_id={CHECKOUT_SESSION_ID}",
    'cancel_url'  => $baseUrl . "/cancel.php",
  ]);

  header("Location: " . $session->url);
  exit;

} catch (Exception $e) {
  exit("Error Stripe: " . $e->getMessage());
}
