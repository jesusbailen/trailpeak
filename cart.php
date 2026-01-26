<?php
session_start();
require_once __DIR__ . '/config/db.php';

// DEBUG temporal (luego lo quitamos)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrito en sesión: [id_producto => cantidad]
$cart = $_SESSION['cart'] ?? [];

require_once __DIR__ . '/includes/header.php';
?>

<main class="container py-5">
  <h1 class="h3 mb-4">Carrito</h1>

  <?php if (empty($cart)): ?>
    <div class="alert alert-light border">Tu carrito está vacío.</div>
    <a class="btn btn-primary" href="index.php">Volver a la tienda</a>
  <?php else: ?>

    <?php
      $ids = array_keys($cart);
      $placeholders = implode(',', array_fill(0, count($ids), '?'));

      $stmt = $pdo->prepare("
        SELECT id_producto, nombre, precio
        FROM producto
        WHERE id_producto IN ($placeholders)
      ");
      $stmt->execute($ids);
      $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Map por id para encontrar rápido
      $map = [];
      foreach ($productos as $p) $map[(int)$p['id_producto']] = $p;

      $total = 0.0;
    ?>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-end">Precio</th>
            <th class="text-center">Cantidad</th>
            <th class="text-end">Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart as $id => $qty): ?>
            <?php
              $id = (int)$id;
              $qty = (int)$qty;
              if (!isset($map[$id])) continue;

              $precio = (float)$map[$id]['precio'];
              $subtotal = $precio * $qty;
              $total += $subtotal;
            ?>
            <tr>
              <td><?= htmlspecialchars($map[$id]['nombre']) ?></td>
              <td class="text-end"><?= number_format($precio, 2, ',', '.') ?> &euro;</td>
              <td class="text-center">
                <div class="d-inline-flex align-items-center gap-1">
                  <form method="post" action="update_cart.php">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="dec">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">-</button>
                  </form>
                  <span class="px-2"><?= $qty ?></span>
                  <form method="post" action="update_cart.php">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="inc">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">+</button>
                  </form>
                </div>
              </td>
              <td class="text-end"><?= number_format($subtotal, 2, ',', '.') ?> &euro;</td>
              <td class="text-end">
                <form method="post" action="remove_from_cart.php" class="d-inline">
                  <input type="hidden" name="product_id" value="<?= $id ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Total</th>
            <th class="text-end"><?= number_format($total, 2, ',', '.') ?> &euro;</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="index.php">Seguir comprando</a>
      <a class="btn btn-primary" href="checkout.php">Finalizar compra</a>
    </div>

  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
