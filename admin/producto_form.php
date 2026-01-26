<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$idProducto = (int)($_GET['id'] ?? 0);
$errores = [];

// Cargar categorías activas para select
$stmt = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$producto = [
  'sku' => '',
  'nombre' => '',
  'descripcion' => '',
  'precio' => '',
  'stock' => '',
  'id_categoria' => '',
  'imagen_path' => '',
  'activo' => 1
];

if ($idProducto > 0) {
  $stmt = $pdo->prepare("
    SELECT id_producto, sku, nombre, descripcion, precio, stock, id_categoria, imagen_path, activo
    FROM producto
    WHERE id_producto = ?
    LIMIT 1
  ");
  $stmt->execute([$idProducto]);
  $producto = $stmt->fetch(PDO::FETCH_ASSOC) ?: $producto;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $producto['sku'] = trim($_POST['sku'] ?? '');
  $producto['nombre'] = trim($_POST['nombre'] ?? '');
  $producto['descripcion'] = trim($_POST['descripcion'] ?? '');
  $producto['precio'] = $_POST['precio'] ?? '';
  $producto['stock'] = $_POST['stock'] ?? '';
  $producto['id_categoria'] = (int)($_POST['id_categoria'] ?? 0);
  $producto['activo'] = isset($_POST['activo']) ? 1 : 0;

  if ($producto['sku'] !== '' && !preg_match('/^[A-Za-z0-9._-]{2,50}$/', $producto['sku'])) {
    $errores[] = 'El SKU no es valido. Usa letras, numeros, guion, punto o guion bajo.';
  }
  if ($producto['nombre'] === '') {
    $errores[] = 'El nombre es obligatorio.';
  }
  if (!is_numeric($producto['precio']) || (float)$producto['precio'] < 0) {
    $errores[] = 'El precio debe ser un número válido.';
  }
  if (!is_numeric($producto['stock']) || (int)$producto['stock'] < 0) {
    $errores[] = 'El stock debe ser un número válido.';
  }
  if ($producto['id_categoria'] <= 0) {
    $errores[] = 'Selecciona una categoría.';
  }

  if (!empty($_FILES['imagen']['name'])) {
    if (!is_dir(__DIR__ . '/../uploads')) {
      mkdir(__DIR__ . '/../uploads', 0755, true);
    }

    $tmpPath = $_FILES['imagen']['tmp_name'] ?? '';
    $error = (int)($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK || $tmpPath === '') {
      $errores[] = 'Error al subir la imagen.';
    } else {
      $info = @getimagesize($tmpPath);
      $mime = $info['mime'] ?? '';
      $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
      ];
      if (!isset($allowed[$mime])) {
        $errores[] = 'La imagen debe ser JPG, PNG o WEBP.';
      } else {
        $ext = $allowed[$mime];
        $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = __DIR__ . '/../uploads/' . $filename;
        if (!move_uploaded_file($tmpPath, $destPath)) {
          $errores[] = 'No se pudo guardar la imagen.';
        } else {
          $producto['imagen_path'] = 'uploads/' . $filename;
        }
      }
    }
  }

  if (!$errores) {
    if ($idProducto > 0) {
      $stmt = $pdo->prepare("
        UPDATE producto
        SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, imagen_path = ?, activo = ?
        WHERE id_producto = ?
      ");
      $stmt->execute([
        $producto['sku'] !== '' ? $producto['sku'] : null,
        $producto['nombre'],
        $producto['descripcion'],
        $producto['precio'],
        (int)$producto['stock'],
        $producto['id_categoria'],
        $producto['imagen_path'] !== '' ? $producto['imagen_path'] : null,
        $producto['activo'],
        $idProducto
      ]);
    } else {
      $stmt = $pdo->prepare("
        INSERT INTO producto (sku, nombre, descripcion, precio, stock, id_categoria, imagen_path, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->execute([
        $producto['sku'] !== '' ? $producto['sku'] : null,
        $producto['nombre'],
        $producto['descripcion'],
        $producto['precio'],
        (int)$producto['stock'],
        $producto['id_categoria'],
        $producto['imagen_path'] !== '' ? $producto['imagen_path'] : null,
        $producto['activo']
      ]);
    }

    header('Location: ' . BASE_URL . 'admin/productos.php');
    exit;
  }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0"><?= $idProducto > 0 ? 'Editar producto' : 'Nuevo producto' ?></h1>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/productos.php">Volver</a>
    </div>

    <?php if ($errores): ?>
      <div class="alert alert-danger">
        <?php foreach ($errores as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="card card-body shadow-sm" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($producto['sku'] ?? '') ?>" placeholder="Ej: TP-001">
      </div>
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Precio</label>
          <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars((string)$producto['precio']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Stock</label>
          <input type="number" min="0" name="stock" class="form-control" value="<?= htmlspecialchars((string)$producto['stock']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Categoría</label>
          <select name="id_categoria" class="form-select" required>
            <option value="">Selecciona</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)$producto['id_categoria'] === (int)$c['id_categoria']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="mt-3">
        <label class="form-label">Imagen del producto</label>
        <?php if (!empty($producto['imagen_path'])): ?>
          <div class="mb-2">
            <img src="<?= BASE_URL . $producto['imagen_path'] ?>" alt="Imagen actual" style="max-height: 120px;">
          </div>
        <?php endif; ?>
        <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
        <div class="form-text">Formatos permitidos: JPG, PNG, WEBP.</div>
      </div>
      <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="activo" id="activo" <?= ((int)$producto['activo'] === 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="activo">Activo</label>
      </div>
      <div class="mt-4">
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

