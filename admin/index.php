<?php
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Panel de administracion</h1>
      <div class="text-muted">
        Bienvenido, <?= htmlspecialchars(current_user()['nombre'] ?? 'Usuario') ?>
        (<?= htmlspecialchars(current_user()['rol'] ?? 'rol') ?>)
      </div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>index.php">Volver a la tienda</a>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
          <h2 class="h6 fw-bold mb-3">Gestion</h2>
          <div class="list-group">
            <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/pedidos.php">Pedidos</a>
            <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/productos.php">Productos</a>
            <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/categorias.php">Categorias</a>
            <?php if (current_user()['rol'] === 'admin'): ?>
              <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/usuarios.php">Usuarios</a>
              <a class="list-group-item list-group-item-action" href="<?= BASE_URL ?>admin/informes.php">Informes</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
          <h2 class="h6 fw-bold mb-2">Acciones rapidas</h2>
          <div class="d-grid gap-2">
            <a class="btn btn-outline-dark" href="<?= BASE_URL ?>admin/producto_form.php">Nuevo producto</a>
            <a class="btn btn-outline-dark" href="<?= BASE_URL ?>admin/categorias.php">Nueva categoria</a>
            <?php if (current_user()['rol'] === 'admin'): ?>
              <a class="btn btn-outline-dark" href="<?= BASE_URL ?>admin/usuario_form.php">Nuevo usuario</a>
            <?php endif; ?>
            <a class="btn btn-outline-danger" href="<?= BASE_URL ?>logout.php">Salir</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
