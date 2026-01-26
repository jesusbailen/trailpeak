<?php
$IS_LOCAL = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);

if ($IS_LOCAL) {
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'trailpeak_local');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('STRIPE_SECRET', 'sk_testEXAMPLE');
} else {
  define('DB_HOST', 'sql100.infinityfree.com');
  define('DB_NAME', 'YOUR_DB_NAME');
  define('DB_USER', 'YOUR_DB_USER');
  define('DB_PASS', 'YOUR_DB_PASS');
  define('STRIPE_SECRET', 'sk_testEXAMPLE');
}

if ($IS_LOCAL) {
  define('BASE_URL', '/ud6/ud6/Tienda_Trailpeak_FINAL/');
} else {
  define('BASE_URL', '/final/Tienda_Trailpeak_FINAL/');
}

if ($IS_LOCAL) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  error_reporting(E_ALL);
  ini_set('log_errors', 1);
}
