<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/auth.php';
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TRAILPEAK | Equipamiento para Trail Running</title>

    <!-- Bootstrap 5 -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- CSS propio mínimo -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    />
  </head>

  <body>
    <!-- CABECERA + MENÚ SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 shadow-sm border-bottom border-secondary">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php">
          <img src="<?= BASE_URL ?>img/ui/logo_trailpeak.png" alt="TrailPeak logo" height="40" />
          <span class="fw-bold text-uppercase">TRAILPEAK</span>
        </a>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#menu"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
          <!-- MENÚ SUPERIOR (lo que pide el enunciado) -->
          <ul class="navbar-nav ms-auto align-items-lg-center gap-2 fw-semibold pt-2 pt-lg-0">
            <li class="nav-item">
              <a class="nav-link active px-2" href="<?= BASE_URL ?>index.php">Inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="#proximos">Próximos artículos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="#ofertas">Ofertas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="#outlet">Outlet</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="<?= BASE_URL ?>quienes_somos.php">Quiénes somos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="<?= BASE_URL ?>contacto.php">Contacto</a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-2" href="<?= BASE_URL ?>envio.php">Envío</a>
            </li>

            <!-- LOGIN / USUARIO / LOGOUT -->
            <?php if (!is_logged_in()): ?>
              <li class="nav-item ms-lg-2">
                <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>login.php">
                  Inicia sesión
                </a>
              </li>
            <?php else: ?>
              <li class="nav-item ms-lg-2">
                <a class="nav-link px-2" href="<?= BASE_URL ?>mis_datos.php">Mis datos</a>
              </li>
              <?php if (current_user()['rol'] === 'cliente'): ?>
                <li class="nav-item ms-lg-2">
                  <a class="nav-link px-2" href="<?= BASE_URL ?>mis_pedidos.php">Pedidos</a>
                </li>
              <?php endif; ?>
              <li class="nav-item ms-lg-2">
                <span class="navbar-text text-white small">
                  Hola, <?= htmlspecialchars(current_user()['nombre']) ?>
                  <span class="opacity-75">(<?= htmlspecialchars(current_user()['rol']) ?>)</span>
                </span>
              </li>

              <?php if (in_array(current_user()['rol'], ['admin','empleado'], true)): ?>
                <li class="nav-item ms-lg-2">
                  <a class="btn btn-outline-warning btn-sm" href="<?= BASE_URL ?>admin/index.php">
                    Panel
                  </a>
                </li>
              <?php endif; ?>

              <li class="nav-item ms-lg-2">
                <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>logout.php">
                  Salir
                </a>
              </li>
            <?php endif; ?>

            <!-- CARRITO -->
            <li class="nav-item ms-lg-2">
              <a class="nav-link position-relative" href="<?= BASE_URL ?>cart.php" title="Ver carrito">
                <i class="bi bi-cart3 fs-5"></i>

                <?php if (!empty($_SESSION['cart'])): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= array_sum($_SESSION['cart']) ?>
                  </span>
                <?php endif; ?>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

