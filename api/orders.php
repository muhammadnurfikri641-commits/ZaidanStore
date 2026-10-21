<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/store.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond($status, array $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Metode permintaan tidak didukung.']);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRF($csrf)) {
    respond(419, ['error' => 'Sesi berakhir. Muat ulang halaman lalu coba lagi.']);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    respond(400, ['error' => 'Data pesanan tidak valid.']);
}

$productId = filter_var($payload['productId'] ?? null, FILTER_VALIDATE_INT);
$gameId = is_string($payload['gameId'] ?? null) ? trim($payload['gameId']) : '';
$server = is_string($payload['server'] ?? null) ? trim($payload['server']) : '';
if (!$productId || !preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $gameId)) {
    respond(422, ['error' => 'Periksa kembali ID game dan pilihan item.']);
}
if (strlen($server) > 100) {
    respond(422, ['error' => 'Server atau Zone terlalu panjang.']);
}

$stmt = $conn->prepare('SELECT game, item_name, price FROM products WHERE id = ? AND active = 1 LIMIT 1');
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$product) {
    respond(404, ['error' => 'Item tidak tersedia. Silakan muat ulang katalog.']);
}

try {
    $code = storeOrderCode();
    $price = $product['price'] === null ? null : (int)$product['price'];
    $stmt = $conn->prepare('INSERT INTO store_orders (code, game, item_name, game_id, server, price) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $code, $product['game'], $product['item_name'], $gameId, $server, $price);
    $stmt->execute();
    $stmt->close();
} catch (Throwable $e) {
    respond(500, ['error' => 'Pesanan belum tersimpan. Coba lagi beberapa saat.']);
}

respond(201, [
    'code' => $code,
    'status' => 'pending',
    'game' => $product['game'],
    'item' => $product['item_name'],
    'price' => $price,
    'whatsappNumber' => WHATSAPP_NUMBER
]);
