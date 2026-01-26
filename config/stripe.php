<?php
require_once __DIR__ . '/../vendor/autoload.php';

// CLAVE SECRETA DE STRIPE (modo test)
if (!defined('STRIPE_SECRET') || STRIPE_SECRET === '') {
  throw new RuntimeException('Falta la clave STRIPE_SECRET en config/env.php');
}
\Stripe\Stripe::setApiKey(STRIPE_SECRET);
