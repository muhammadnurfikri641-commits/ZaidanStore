<?php
function storeEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah($value) {
    return $value === null ? 'Tanya admin' : 'Rp ' . number_format((int)$value, 0, ',', '.');
}

function activePromo(mysqli $conn) {
    $sql = "SELECT title, message FROM promos
            WHERE active = 1 AND (starts_at IS NULL OR starts_at <= NOW())
              AND (ends_at IS NULL OR ends_at >= NOW())
            ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function storeOrderCode() {
    return 'ZS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
}
