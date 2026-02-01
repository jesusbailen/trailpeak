<?php
declare(strict_types=1);

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost','127.0.0.1'], true);
if ($isLocal) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
}


require_once __DIR__ . '/lib/auth.php';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/stripe.php';

$sessionId = $_GET['session_id'] ?? '';
if ($sessionId === '') {
  exit("Falta session_id.");
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  exit("Carrito vacío (no hay nada que guardar).");
}

if (!is_logged_in() && empty($_SESSION['guest'])) {
  exit("No hay usuario ni invitado.");
}

try {
  // Flujo compra: verificar pago en Stripe y registrar pedido en BD
  // 1) Verificar en Stripe
  $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);

  // Stripe suele devolver payment_status = 'paid' cuando ya está pagado
  if (($stripeSession->payment_status ?? '') !== 'paid') {
    exit("Pago no confirmado. Estado: " . ($stripeSession->payment_status ?? 'desconocido'));
  }

  // 2) Evitar duplicados
  // Flujo compra: evitar duplicados si se refresca la pagina de exito
  $check = $pdo->prepare("SELECT id_pedido FROM pedido WHERE stripe_session_id = ?");
  $check->execute([$sessionId]);
  $existing = $check->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    // Ya estaba guardado: limpia carrito por si el usuario refrescó
    unset($_SESSION['cart']);
    if (!empty($_SESSION['guest'])) {
      unset($_SESSION['guest']);
    }
    $_SESSION['flash_success'] = "✅ Pago realizado. Tu pedido se ha registrado correctamente.";
    header("Location: index.php");
    exit;
  }



  // 3) Calcular total desde Stripe (es la fuente más fiable)
  // Flujo compra: total desde Stripe (fuente fiable)
  $total = ((int)($stripeSession->amount_total ?? 0)) / 100;

  // 4) Insertar pedido + detalles en transacción
  // Flujo compra: insertar pedido + detalles en transaccion
  $pdo->beginTransaction();

  $esInvitado = false;
  $u = current_user();
  if ($u) {
    $idUsuario = (int)($u['id'] ?? $u['id_usuario'] ?? 0);
    if ($idUsuario <= 0) {
      throw new RuntimeException("Usuario no vÃ¡lido en sesiÃ³n.");
    }
  } else {
    $esInvitado = true;
    $guest = $_SESSION['guest'] ?? [];
    $nombreGuest = trim($guest['nombre'] ?? '');
    $emailGuest = trim($guest['email'] ?? '');

    if ($nombreGuest === '' || $emailGuest === '' || !filter_var($emailGuest, FILTER_VALIDATE_EMAIL)) {
      throw new RuntimeException("Datos de invitado inválidos.");
    }

    $stmtUser = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
    $stmtUser->execute([$emailGuest]);
    $idUsuario = (int)$stmtUser->fetchColumn();

    if ($idUsuario <= 0) {
      $randomPass = bin2hex(random_bytes(8));
      $hash = password_hash($randomPass, PASSWORD_DEFAULT);
      $stmtIns = $pdo->prepare("
        INSERT INTO usuario (nombre, email, password, rol, activo)
        VALUES (?, ?, ?, 'cliente', 1)
      ");
      $stmtIns->execute([$nombreGuest, $emailGuest, $hash]);
      $idUsuario = (int)$pdo->lastInsertId();
    }
  }

  $stmtPedido = $pdo->prepare("
    INSERT INTO pedido (fecha, total, id_usuario, activo, stripe_session_id)
    VALUES (NOW(), ?, ?, 1, ?)
  ");
  $stmtPedido->execute([$total, $idUsuario, $sessionId]);

  $idPedido = (int)$pdo->lastInsertId();

  // Traer productos actuales para precio_unitario
  $ids = array_map('intval', array_keys($cart));
  $ids = array_values(array_filter($ids, fn($v) => $v > 0));

  if (empty($ids)) {
    throw new RuntimeException("IDs del carrito inválidos.");
  }

  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $stmtProd = $pdo->prepare("
    SELECT id_producto, precio
    FROM producto
    WHERE id_producto IN ($placeholders) AND activo = 1
  ");
  $stmtProd->execute($ids);
  $rows = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

  $precioPorId = [];
  foreach ($rows as $r) {
    $precioPorId[(int)$r['id_producto']] = (float)$r['precio'];
  }

  $stmtDet = $pdo->prepare("
    INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, activo)
    VALUES (?, ?, ?, ?, 1)
  ");

  foreach ($cart as $idProd => $qty) {
    $idProd = (int)$idProd;
    $qty = (int)$qty;
    if ($qty <= 0) continue;

    if (!isset($precioPorId[$idProd])) {
      throw new RuntimeException("Producto $idProd no encontrado/activo para guardar pedido.");
    }

    $precioUnit = $precioPorId[$idProd];
    $stmtDet->execute([$idPedido, $idProd, $qty, $precioUnit]);
  }

  $pdo->commit();

  // 5) Vaciar carrito
  // Flujo compra: vaciar carrito y datos de invitado
  unset($_SESSION['cart']);
  if (!empty($_SESSION['guest'])) {
    unset($_SESSION['guest']);
  }

  // 6) Flash + Redirigir a index
  if ($esInvitado) {
    $_SESSION['flash_success'] = "✅ Pago realizado. Pedido registrado. Si ya tienes cuenta, inicia sesión para ver tus pedidos.";
  } else {
    $_SESSION['flash_success'] = "✅ Pago realizado. Tu pedido se ha registrado correctamente.";
  }
  header("Location: index.php");
  exit;


} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  exit("Error al guardar pedido: " . $e->getMessage());
}
