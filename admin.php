<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/store.php';

$adminHash = storeConfigValue('STORE_ADMIN_PASSWORD_HASH');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($adminHash === '') {
        $error = 'Admin belum diaktifkan. Atur STORE_ADMIN_PASSWORD_HASH pada konfigurasi server.';
    } elseif (isset($_POST['admin_password'])) {
        if (password_verify((string)$_POST['admin_password'], $adminHash)) {
            session_regenerate_id(true);
            $_SESSION['store_admin'] = true;
            header('Location: admin.php');
            exit;
        }
        $error = 'Kata sandi tidak cocok.';
    } elseif (!empty($_SESSION['store_admin']) && isset($_POST['logout'])) {
        unset($_SESSION['store_admin']);
        header('Location: admin.php');
        exit;
    } elseif (!empty($_SESSION['store_admin']) && isset($_POST['product_id'], $_POST['price'])) {
        $priceValue = trim((string)$_POST['price']);
        $productId = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        if (!$productId || ($priceValue !== '' && (!ctype_digit($priceValue) || (float)$priceValue > 999999999999))) {
            $error = 'Harga harus berupa angka rupiah yang valid.';
        } else {
            $price = $priceValue === '' ? null : (int)$priceValue;
            $stmt = $conn->prepare('UPDATE products SET price = ? WHERE id = ?');
            $stmt->bind_param('ii', $price, $productId);
            $stmt->execute();
            $stmt->close();
            header('Location: admin.php?saved=product');
            exit;
        }
    } elseif (!empty($_SESSION['store_admin']) && isset($_POST['order_id'], $_POST['status'])) {
        $allowed = ['pending', 'paid', 'processing', 'completed', 'cancelled'];
        $orderId = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
        $status = (string)$_POST['status'];
        if (!$orderId || !in_array($status, $allowed, true)) {
            $error = 'Status order tidak valid.';
        } else {
            $stmt = $conn->prepare('UPDATE store_orders SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $orderId);
            $stmt->execute();
            $stmt->close();
            header('Location: admin.php?saved=order');
            exit;
        }
    }
}

$isAdmin = !empty($_SESSION['store_admin']);
$products = $isAdmin ? $conn->query('SELECT id, game, item_name, price, active FROM products ORDER BY game, id') : false;
$orders = $isAdmin ? $conn->query('SELECT id, code, game, item_name, game_id, server, status, created_at FROM store_orders ORDER BY created_at DESC LIMIT 100') : false;
$csrf = storeEscape($_SESSION['csrf_token']);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin toko — ZaidanStore</title><link rel="stylesheet" href="assets/css/store.css?v=3"><style>
body{background:#f6f4fa}.admin-shell{max-width:1050px;margin:48px auto;padding:0 22px}.admin-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}.admin-top h1{font:800 28px var(--display);margin:0}.admin-card{background:#fff;border:1px solid #ece9f3;border-radius:16px;padding:22px;margin-bottom:20px;overflow:auto}.admin-card h2{font:800 17px var(--display);margin:0 0 16px}.admin-table{width:100%;border-collapse:collapse;font-size:12px}.admin-table th,.admin-table td{text-align:left;padding:11px 8px;border-bottom:1px solid #efedf3;vertical-align:middle}.admin-table th{font-size:10px;color:#807b91}.admin-table input,.admin-table select,.admin-login input{height:36px;border:1px solid #e7e4ed;border-radius:8px;padding:0 9px}.admin-table input{width:130px}.admin-table button,.admin-login button,.logout-button{border:0;border-radius:8px;padding:9px 12px;background:#7557e8;color:white;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap}.admin-alert{padding:12px;border-radius:9px;background:#fff0ef;color:#ad3434;font-size:12px;margin-bottom:15px}.admin-success{background:#edfff7;color:#287656}.admin-login{max-width:420px;margin:80px auto;background:#fff;border:1px solid #ece9f3;border-radius:18px;padding:28px}.admin-login h1{font:800 24px var(--display)}.admin-login label{font-size:12px;font-weight:700;display:block;margin:20px 0 7px}.admin-login input{width:100%}.admin-login button{margin-top:14px;width:100%;height:42px}.admin-meta{font-size:10px;color:#807b91}.logout-button{background:#f0edfa;color:#5f42ce}@media(max-width:640px){.admin-shell{margin:25px auto}.admin-top h1{font-size:22px}.admin-card{padding:14px}.admin-table th,.admin-table td{padding:9px 5px}}
</style></head><body>
<?php if (!$isAdmin): ?><main class="admin-login"><a class="brand" href="index.php"><span class="brand-mark">Z</span><span>zaidan<span class="brand-accent">store</span><small>ADMIN TOKO</small></span></a><h1>Masuk ke dashboard</h1><p class="admin-meta">Atur harga katalog dan perbarui status pesanan.</p><?php if ($error !== ''): ?><div class="admin-alert"><?= storeEscape($error) ?></div><?php endif; ?><?php if (isset($_GET['saved'])): ?><div class="admin-alert admin-success">Perubahan tersimpan.</div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><label for="admin-password">Kata sandi admin</label><input id="admin-password" name="admin_password" type="password" autocomplete="current-password" required><button type="submit">Masuk</button></form></main>
<?php else: ?><main class="admin-shell"><div class="admin-top"><div><span class="section-kicker">PENGELOLAAN TOKO</span><h1>Dashboard admin</h1></div><form method="post"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><button class="logout-button" name="logout" value="1">Keluar</button></form></div><?php if ($error !== ''): ?><div class="admin-alert"><?= storeEscape($error) ?></div><?php endif; ?>
<section class="admin-card"><h2>Harga produk <span class="admin-meta">· kosongkan untuk menampilkan “Tanya admin”</span></h2><table class="admin-table"><thead><tr><th>Game</th><th>Item</th><th>Harga (Rp)</th><th></th></tr></thead><tbody><?php while ($product = $products->fetch_assoc()): $formId = 'product-form-' . (int)$product['id']; ?><tr><td><?= storeEscape($product['game']) ?></td><td><?= storeEscape($product['item_name']) ?></td><td><form id="<?= $formId ?>" method="post"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input name="price" inputmode="numeric" aria-label="Harga <?= storeEscape($product['item_name']) ?>" value="<?= $product['price'] === null ? '' : (int)$product['price'] ?>" placeholder="Harga"></form></td><td><button type="submit" form="<?= $formId ?>">Simpan</button></td></tr><?php endwhile; ?></tbody></table></section>
<section class="admin-card"><h2>Pesanan terbaru <span class="admin-meta">· maksimal 100 pesanan terbaru</span></h2><table class="admin-table"><thead><tr><th>Kode</th><th>Detail</th><th>ID akun</th><th>Status</th><th>Dibuat</th><th></th></tr></thead><tbody><?php if ($orders && $orders->num_rows): while ($order = $orders->fetch_assoc()): $formId = 'order-form-' . (int)$order['id']; ?><tr><td><strong><?= storeEscape($order['code']) ?></strong></td><td><?= storeEscape($order['game']) ?><br><?= storeEscape($order['item_name']) ?></td><td><?= storeEscape($order['game_id']) ?><?= $order['server'] ? '<br>' . storeEscape($order['server']) : '' ?></td><td><form id="<?= $formId ?>" method="post"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><select name="status"><?php foreach (['pending','paid','processing','completed','cancelled'] as $status): ?><option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></form></td><td><?= storeEscape(date('d M Y H:i', strtotime($order['created_at']))) ?></td><td><button type="submit" form="<?= $formId ?>">Perbarui</button></td></tr><?php endwhile; else: ?><tr><td colspan="6">Belum ada pesanan.</td></tr><?php endif; ?></tbody></table></section></main><?php endif; ?></body></html>
