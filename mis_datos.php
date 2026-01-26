<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/auth.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$idUsuario = (int)(current_user()['id'] ?? current_user()['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
  header('Location: login.php');
  exit;
}

$stmt = $pdo->prepare("SELECT id_usuario, nombre, email, password, activo FROM usuario WHERE id_usuario = ? LIMIT 1");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || (int)$usuario['activo'] !== 1) {
  $_SESSION = [];
  if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
  }
  header('Location: login.php');
  exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim((string)(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? ''));
  $email = trim((string)(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''));
  $passwordActual = (string)(filter_input(INPUT_POST, 'password_actual', FILTER_UNSAFE_RAW) ?? '');
  $nuevaPassword = (string)(filter_input(INPUT_POST, 'nueva_password', FILTER_UNSAFE_RAW) ?? '');
  $repetirPassword = (string)(filter_input(INPUT_POST, 'repetir_nueva_password', FILTER_UNSAFE_RAW) ?? '');

  if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
  }

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es válido.';
  }

  if (!$errores) {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? AND id_usuario <> ? LIMIT 1");
    $stmt->execute([$email, $idUsuario]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
      $_SESSION['flash_error'] = 'Email ya existe';
      header('Location: mis_datos.php');
      exit;
    }
  }

  $quiereCambiarPassword = ($passwordActual !== '' || $nuevaPassword !== '' || $repetirPassword !== '');
  if ($quiereCambiarPassword) {
    if ($passwordActual === '' || !password_verify($passwordActual, $usuario['password'])) {
      $_SESSION['flash_error'] = 'Contraseña actual incorrecta';
      header('Location: mis_datos.php');
      exit;
    }
    if (strlen($nuevaPassword) < 6) {
      $_SESSION['flash_error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
      header('Location: mis_datos.php');
      exit;
    }
    if ($nuevaPassword !== $repetirPassword) {
      $_SESSION['flash_error'] = 'Contraseñas no coinciden';
      header('Location: mis_datos.php');
      exit;
    }
  }

  if (!$errores) {
    if ($quiereCambiarPassword) {
      $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("
        UPDATE usuario
        SET nombre = ?, email = ?, password = ?
        WHERE id_usuario = ?
      ");
      $stmt->execute([$nombre, $email, $hash, $idUsuario]);
    } else {
      $stmt = $pdo->prepare("
        UPDATE usuario
        SET nombre = ?, email = ?
        WHERE id_usuario = ?
      ");
      $stmt->execute([$nombre, $email, $idUsuario]);
    }

    $_SESSION['user']['nombre'] = $nombre;
    $_SESSION['user']['email'] = $email;
    $_SESSION['flash_success'] = 'Datos actualizados';
    header('Location: mis_datos.php');
    exit;
  }
}

if (!empty($_SESSION['flash_error'])) {
  $errores[] = $_SESSION['flash_error'];
  unset($_SESSION['flash_error']);
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Mis datos</h1>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>index.php">Volver</a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
      </div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

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
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
      </div>

      <hr class="my-4">
      <h2 class="h6 mb-3">Cambiar contraseña</h2>

      <div class="mb-3">
        <label class="form-label">Contraseña actual</label>
        <input type="password" name="password_actual" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Nueva contraseña</label>
        <input type="password" name="nueva_password" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Repetir nueva contraseña</label>
        <input type="password" name="repetir_nueva_password" class="form-control">
      </div>

      <div>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>

  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

