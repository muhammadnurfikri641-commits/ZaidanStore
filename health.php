<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$result = $conn->query('SELECT 1 AS database_ok');
echo json_encode([
    'status' => $result ? 'ok' : 'error',
    'database' => $result ? 'connected' : 'unavailable',
], JSON_UNESCAPED_SLASHES);
