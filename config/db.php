<?php
require_once __DIR__ . '/env.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    // Alinear la zona horaria de MySQL con Europe/Madrid (sin depender del servidor).
    $tz = new DateTimeZone('Europe/Madrid');
    $offset = $tz->getOffset(new DateTime('now', $tz));
    $sign = $offset >= 0 ? '+' : '-';
    $offset = abs($offset);
    $hours = str_pad((string)floor($offset / 3600), 2, '0', STR_PAD_LEFT);
    $mins = str_pad((string)floor(($offset % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $pdo->exec("SET time_zone = '{$sign}{$hours}:{$mins}'");
} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}
