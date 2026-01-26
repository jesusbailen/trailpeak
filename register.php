<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Si ya está logueado, fuera
if (!empty($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$errors = [];
$nombre = trim((string)(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? ''));
$email  = trim((string)(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password  = (string)(filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '');
  $password2 = (string)(filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW) ?? '');

  // Validaciones
  if ($nombre === '' || mb_strlen($nombre) < 2) {
    $errors[] = 'El nombre es obligatorio.';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email no válido.';
  }

  if (mb_strlen($password) < 6) {
    $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
  }

  if ($password !== $password2) {
    $errors[] = 'Las contraseñas no coinciden.';
  }

  if (!$errors) {
    // ¿Existe ya el email?
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $errors[] = 'Ese email ya está registrado.';
    } else {
      // Hash seguro
      $hash = password_hash($password, PASSWORD_DEFAULT);

      // Insertar usuario
      $stmt = $pdo->prepare("
        INSERT INTO usuario (nombre, email, password, rol, activo)
        VALUES (?, ?, ?, 'cliente', 1)
      ");
      $stmt->execute([$nombre, $email, $hash]);

      // Login automático
      $_SESSION['user'] = [
        'id_usuario' => $pdo->lastInsertId(),
        'nombre' => $nombre,
        'email' => $email,
        'rol' => 'cliente'
      ];

      $_SESSION['flash_success'] = 'Cuenta creada correctamente. ¡Bienvenido!';
      header('Location: index.php');
      exit;
    }
  }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 fw-bold mb-3">Crear cuenta</h1>
            <p class="text-muted small mb-4">Regístrate para hacer tus pedidos y ver tu historial.</p>

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
                <input name="nombre" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input name="password" type="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Repite contraseña</label>
                <input name="password2" type="password" class="form-control" required>
              </div>

              <button class="btn btn-dark w-100">Crear cuenta</button>

              <div class="mt-3 text-center small">
                ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>login.php">Inicia sesión</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
