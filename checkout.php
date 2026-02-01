<?php
declare(strict_types=1);

// DEBUG (temporal)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/lib/auth.php';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/stripe.php';

// Flujo compra: si carrito vacio, no se puede pagar

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  exit("Carrito vacío: añade un producto antes de pagar.");
}

// Compra como invitado
if (!is_logged_in()) { // si no está logueado -> entra en flujo invitado
  $errors = [];
  if ($_SERVER['REQUEST_METHOD'] === 'POST') { (()) // si viene un post -> valida nombre y email 
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nombre === '') {
      $errors[] = 'El nombre es obligatorio.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'El email no es válido.';
    }

    // si no hay errores -> guarda datos del invitado en sesión y recarga checkout
    if (!$errors) {
      // guardamos con guest la sesion del invitado
      $_SESSION['guest'] = ['nombre' => $nombre, 'email' => $email];
      header('Location: checkout.php');
      exit;
    }
  }
// Si NO hay guest en sesión → muestra el formulario y corta ejecución

  if (empty($_SESSION['guest'])) {
    // Flujo compra: pedir nombre/email al invitado antes de crear session de Stripe
    require_once __DIR__ . '/includes/header.php';
    ?>
    <main class="py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm">
              <div class="card-body p-4">
                <h1 class="h4 fw-bold mb-3">Compra como invitado</h1>
                <p class="text-muted small mb-4">Introduce tus datos para continuar con el pago.</p>

                <?php if ($errors): ?>
                  <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                      <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <form method="post">
                  <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Continuar a pago</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
  }
}

/**
 * Devuelve la URL base absoluta de la aplicación.
 * Detecta automáticamente http/https, el host y el subdirectorio del proyecto
 * para evitar rutas hardcodeadas y funcionar en local y hosting
 */

function getBaseUrl(): string {
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
  $scheme = $isHttps ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $basePath = rtrim(BASE_URL ?? '/', '/');
  return $scheme . '://' . $host . $basePath;
}
// Obtenemos la URL base absoluta de la aplicación (local o producción)
$baseUrl = getBaseUrl();



// IDs del carrito (claves) => deben ser id_producto

// Saneo IDs para evitar basura y asegurar que la query solo usa enteros.
$ids = array_map('intval', array_keys($cart));
$ids = array_values(array_filter($ids, fn($v) => $v > 0));

//Si no hay IDs válidos, paramos
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

// Flujo compra: crear line_items con productos del carrito
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

  // Flujo compra: crear Stripe Checkout Session y redirigir al pago
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
