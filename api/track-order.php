<?php
require_once dirname(__DIR__) . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$rawCode = $_GET['code'] ?? '';
$code = is_string($rawCode) ? strtoupper(trim($rawCode)) : '';
if (!preg_match('/^ZS-\d{8}-[A-F0-9]{10}$/', $code)) {
    http_response_code(422);
    echo json_encode(['error' => 'Format kode order belum benar.']);
    exit;
}

$stmt = $conn->prepare('SELECT code, game, item_name, status, created_at FROM store_orders WHERE code = ? LIMIT 1');
$stmt->bind_param('s', $code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order tidak ditemukan. Periksa kembali kode order kamu.']);
    exit;
}

echo json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
