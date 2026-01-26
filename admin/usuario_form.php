<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if ((current_user()['rol'] ?? '') !== 'admin') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$idUsuario = (int)($_GET['id'] ?? 0);
$idActual = (int)(current_user()['id'] ?? 0);
$errores = [];
$rolesPermitidos = ['admin', 'empleado', 'cliente'];

$usuario = [
  'nombre' => '',
  'email' => '',
  'rol' => 'cliente',
  'activo' => 1
];

if ($idUsuario > 0) {
  $stmt = $pdo->prepare("
    SELECT id_usuario, nombre, email, rol, activo
    FROM usuario
    WHERE id_usuario = ?
    LIMIT 1
  ");
  $stmt->execute([$idUsuario]);
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC) ?: $usuario;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario['nombre'] = trim($_POST['nombre'] ?? '');
  $usuario['email'] = trim($_POST['email'] ?? '');
  $usuario['rol'] = $_POST['rol'] ?? $usuario['rol'];
  $usuario['activo'] = isset($_POST['activo']) ? 1 : 0;
  $password = $_POST['password'] ?? '';

  if ($usuario['nombre'] === '') {
    $errores[] = 'El nombre es obligatorio.';
  }
  if ($usuario['email'] === '' || !filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es válido.';
  }
  if (!in_array($usuario['rol'], $rolesPermitidos, true)) {
    $errores[] = 'El rol no es válido.';
  }
  if ($idUsuario <= 0 && $password === '') {
    $errores[] = 'La contraseña es obligatoria para crear el usuario.';
  }

  if (!$errores) {
    if ($idUsuario > 0) {
      if ($idUsuario === $idActual) {
        $usuario['rol'] = $usuario['rol'] ?? 'admin';
        $stmt = $pdo->prepare("SELECT rol FROM usuario WHERE id_usuario = ? LIMIT 1");
        $stmt->execute([$idUsuario]);
        $usuario['rol'] = $stmt->fetchColumn() ?: $usuario['rol'];
      }

      if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
          UPDATE usuario
          SET nombre = ?, email = ?, rol = ?, activo = ?, password = ?
          WHERE id_usuario = ?
        ");
        $stmt->execute([
          $usuario['nombre'],
          $usuario['email'],
          $usuario['rol'],
          $usuario['activo'],
          $hash,
          $idUsuario
        ]);
      } else {
        $stmt = $pdo->prepare("
          UPDATE usuario
          SET nombre = ?, email = ?, rol = ?, activo = ?
          WHERE id_usuario = ?
        ");
        $stmt->execute([
          $usuario['nombre'],
          $usuario['email'],
          $usuario['rol'],
          $usuario['activo'],
          $idUsuario
        ]);
      }
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("
        INSERT INTO usuario (nombre, email, password, rol, activo)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->execute([
        $usuario['nombre'],
        $usuario['email'],
        $hash,
        $usuario['rol'],
        $usuario['activo']
      ]);
    }

    header('Location: ' . BASE_URL . 'admin/usuarios.php');
    exit;
  }
}

$bloquearRol = ($idUsuario > 0 && $idUsuario === $idActual);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0"><?= $idUsuario > 0 ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/usuarios.php">Volver</a>
    </div>

    <?php if ($errores): ?>
      <div class="alert alert-danger">
        <?php foreach ($errores as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="card card-body shadow-sm">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Rol</label>
        <?php if ($bloquearRol): ?>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['rol'] ?? 'admin') ?>" disabled>
          <div class="form-text">No puedes cambiar tu propio rol.</div>
        <?php else: ?>
          <select name="rol" class="form-select" required>
            <?php foreach ($rolesPermitidos as $rol): ?>
              <option value="<?= $rol ?>" <?= ($usuario['rol'] ?? 'cliente') === $rol ? 'selected' : '' ?>>
                <?= ucfirst($rol) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña <?= $idUsuario > 0 ? '(solo si quieres cambiarla)' : '' ?></label>
        <input type="password" name="password" class="form-control" <?= $idUsuario > 0 ? '' : 'required' ?>>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="activo" id="activo" <?= ((int)($usuario['activo'] ?? 1) === 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="activo">Activo</label>
      </div>
      <div>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

