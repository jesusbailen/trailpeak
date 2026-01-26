<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/auth.php';

if (!is_logged_in()) {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$rol = current_user()['rol'] ?? 'cliente';
if (!in_array($rol, ['admin', 'empleado'], true)) {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}
