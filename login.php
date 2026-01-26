<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim((string)(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''));
  $pass  = (string)(filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '');

  $stmt = $pdo->prepare("SELECT id_usuario, nombre, email, password, rol, activo FROM usuario WHERE email = ?");
  $stmt->execute([$email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$u || (int)$u['activo'] !== 1 || !password_verify($pass, $u['password'])) {
    $error = 'Credenciales incorrectas';
  } else {
    $_SESSION['user'] = [
      'id' => (int)$u['id_usuario'],
      'nombre' => $u['nombre'],
      'email' => $u['email'],
      'rol' => $u['rol']
    ];
    $redirect = $_POST['redirect'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
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
            <h1 class="h4 fw-bold mb-3">Acceso a clientes</h1>
            <p class="text-muted small mb-4">Inicia sesión para gestionar tus pedidos.</p>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <?php if (!empty($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
              <?php endif; ?>

              <button type="submit" class="btn btn-dark w-100">Entrar</button>
            </form>

            <div class="text-center mt-3 small">
              ¿No tienes cuenta?
              <a href="<?= BASE_URL ?>register.php">Crear cuenta</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
