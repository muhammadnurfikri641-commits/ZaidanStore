<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/store.php';

$catalog = [
    'Mobile Legends' => ['image' => 'MLBB.png', 'category' => 'MOBA', 'description' => 'Diamond dan pass untuk menemani push rank kamu.', 'icon' => '✦', 'fields' => 'ID Game dan Server / Zone'],
    'Free Fire' => ['image' => 'FF.png', 'category' => 'Battle Royale', 'description' => 'Diamond dan membership Free Fire, praktis dan cepat.', 'icon' => '◈', 'fields' => 'ID Game dan nama karakter (opsional)'],
    'Genshin Impact' => ['image' => 'Genshin.png', 'category' => 'RPG', 'description' => 'Genesis Crystal dan Blessing of the Welkin Moon.', 'icon' => '✧', 'fields' => 'UID dan server'],
    'PUBG Mobile' => ['image' => 'PUBG.png', 'category' => 'Battle Royale', 'description' => 'UC dan Royale Pass untuk melengkapi loadout-mu.', 'icon' => '◎', 'fields' => 'Character ID'],
    'Call of Duty Mobile' => ['image' => 'COD.png', 'category' => 'FPS', 'description' => 'CP, Battle Pass, dan item COD Mobile lainnya.', 'icon' => '⌖', 'fields' => 'Player ID'],
];

$products = [];
$result = $conn->query('SELECT id, game, item_name, price FROM products WHERE active = 1 ORDER BY game, id');
if ($result) {
    while ($product = $result->fetch_assoc()) {
        $products[$product['game']][] = $product;
    }
}
$promo = activePromo($conn);
$orderCountResult = $conn->query("SELECT COUNT(*) AS total FROM store_orders WHERE status = 'completed'");
$completedOrders = $orderCountResult ? (int)$orderCountResult->fetch_assoc()['total'] : 0;
$catalogCount = count(array_filter(array_keys($catalog), static fn($game) => !empty($products[$game])));
$csrfToken = storeEscape($_SESSION['csrf_token']);
$waLink = 'https://wa.me/' . rawurlencode(WHATSAPP_NUMBER) . '?text=' . rawurlencode('Halo ZaidanStore, saya ingin bertanya tentang top up game.');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f8f7ff">
  <meta name="description" content="Top up Mobile Legends, Free Fire, Genshin Impact, PUBG Mobile, dan COD Mobile dengan proses mudah lewat ZaidanStore.">
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <title>Top Up Game Mudah & Cepat — ZaidanStore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/store.css?v=6">
</head>
<body>
  <div class="announcement"><span class="announcement-dot"></span><span><?= $promo ? storeEscape($promo['title']) . ' — ' . storeEscape($promo['message']) : 'Promo top up game minggu ini'; ?></span><a href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Tanya promo <span aria-hidden="true">↗</span></a></div>
  <header class="site-header" id="top">
    <nav class="nav shell" aria-label="Navigasi utama">
      <a class="brand" href="#top" aria-label="ZaidanStore Beranda"><span class="brand-mark">Z</span><span>zaidan<span class="brand-accent">store</span><small>TOP UP GAME & DIGITAL</small></span></a>
      <div class="nav-links"><a href="#games">Produk</a><a href="#cara-order">Cara order</a><a href="#lacak-order">Lacak order</a><a href="#faq">FAQ</a><a class="nav-contact" href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Hubungi admin <span aria-hidden="true">↗</span></a></div>
      <button class="theme-toggle" type="button" aria-label="Aktifkan mode malam" aria-pressed="false" title="Mode malam">
        <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
      </button>
      <button class="menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false"><span></span><span></span></button>
    </nav>
  </header>

  <main>
    <section class="hero shell">
      <div class="hero-copy">
        <div class="eyebrow"><span class="spark">✦</span> Teman top up andalanmu</div>
        <h1>Waktunya main.<br><span><span id="typed">Murah &amp; Terpercaya</span><span class="cursor" aria-hidden="true"></span></span></h1>
        <p>Diamond, UC, Genesis Crystal, dan item favoritmu siap dipesan. Pilih game, isi detail akun, lalu lanjut konfirmasi bersama admin.</p>
        <div class="hero-actions"><a class="button button-primary" href="#games">Cari top up <span aria-hidden="true">↘</span></a><a class="button button-quiet" href="#cara-order"><span class="play-icon">▶</span> Cara order</a></div>
        <div class="hero-proof"><div class="avatar-stack"><i>Z</i><i>G</i><i>+</i></div><div><strong>Order praktis, dibantu admin</strong><span>Konfirmasi dan bantuan lewat WhatsApp</span></div></div>
      </div>
      <aside class="hero-aside" aria-label="Game populer">
        <div class="aside-heading"><span>GAME POPULER</span><a href="#games">Semua game <span aria-hidden="true">→</span></a></div>
        <a class="popular-game" href="#product-mobile-legends"><img src="assets/images/MLBB.png" alt=""><span><strong>Mobile Legends</strong><small>Diamond &amp; pass</small></span><span class="popular-arrow">↗</span></a>
        <a class="popular-game" href="#product-free-fire"><img src="assets/images/FF.png" alt=""><span><strong>Free Fire</strong><small>Diamond &amp; membership</small></span><span class="popular-arrow">↗</span></a>
        <a class="popular-game" href="#product-pubg-mobile"><img src="assets/images/PUBG.png" alt=""><span><strong>PUBG Mobile</strong><small>UC &amp; Royale Pass</small></span><span class="popular-arrow">↗</span></a>
        <div class="aside-note"><span class="pulse-dot"></span> Konfirmasi pesanan langsung dengan admin</div>
      </aside>
      <div class="hero-bottom"><span>ISI GAME-MU</span><div class="mini-logos"><b>MLBB</b><b>FREE FIRE</b><b>GENSHIN</b><b>PUBG MOBILE</b><b>COD MOBILE</b></div><a href="#games">Lihat katalog <span aria-hidden="true">↓</span></a></div>
    </section>

    <section class="trust-strip"><div class="shell trust-inner"><div><span class="trust-icon">↯</span><span><strong>Pesan dalam beberapa langkah</strong><small>Pilih item dan kirim detailmu</small></span></div><div><span class="trust-icon">♡</span><span><strong>Dibantu admin langsung</strong><small>Konfirmasi order via WhatsApp</small></span></div><div><span class="trust-icon">⌕</span><span><strong>Status order bisa dilacak</strong><small>Simpan kode order setelah pesan</small></span></div><div class="trust-number"><strong data-count="<?= $completedOrders ?>">0</strong><small>Order selesai</small></div></div></section>

    <section class="catalog-section section shell" id="games">
      <div class="section-heading"><div><span class="section-kicker">PILIHAN BUAT KAMU</span><h2>Game apa yang mau<br><span>kamu top up?</span></h2><p>Pilih game dan nominal. Semua pilihan katalog tersimpan dan dikelola melalui database.</p></div><div class="catalog-count"><strong data-count="<?= (int)$catalogCount ?>">0</strong><span>game<br>tersedia</span></div></div>
      <div class="catalog-toolbar"><div class="filter-list" role="group" aria-label="Filter kategori game"><button class="filter-chip is-active" data-filter="all" type="button">Semua game</button><button class="filter-chip" data-filter="moba" type="button">MOBA</button><button class="filter-chip" data-filter="battle-royale" type="button">Battle royale</button><button class="filter-chip" data-filter="rpg" type="button">RPG</button><button class="filter-chip" data-filter="fps" type="button">FPS</button></div><span class="catalog-hint"><span class="pulse-dot"></span> Katalog aktif</span></div>
      <div class="game-grid">
      <?php foreach ($catalog as $game => $details): if (empty($products[$game])) continue; $categorySlug = strtolower(str_replace(' ', '-', $details['category'])); ?>
        <article class="game-card" data-category="<?= storeEscape($categorySlug) ?>">
          <div class="game-cover"><img src="assets/images/<?= storeEscape($details['image']) ?>" alt="Ilustrasi <?= storeEscape($game) ?>" loading="lazy"><span class="game-category"><?= storeEscape($details['category']) ?></span><span class="cover-icon" aria-hidden="true"><?= storeEscape($details['icon']) ?></span></div>
          <div class="game-content"><div class="game-title-row"><div><h3><?= storeEscape($game) ?></h3><p><?= storeEscape($details['description']) ?></p></div><span class="game-arrow" aria-hidden="true">↗</span></div>
            <div class="form-field"><label for="product-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>">Pilih item</label><select id="product-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>" class="product-select" data-product-select><option value="">Pilih nominal atau item</option><?php foreach ($products[$game] as $product): ?><option value="<?= (int)$product['id'] ?>" data-price="<?= $product['price'] === null ? '' : (int)$product['price'] ?>"><?= storeEscape($product['item_name']) ?> · <?= storeEscape(rupiah($product['price'] === null ? null : (int)$product['price'])) ?></option><?php endforeach; ?></select></div>
            <div class="form-field"><label for="gameid-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>">ID akun</label><input id="gameid-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>" class="game-id" type="text" maxlength="32" autocomplete="off" placeholder="Contoh: 123456789"><small><?= storeEscape($details['fields']) ?></small></div>
            <?php if (in_array($game, ['Mobile Legends', 'Free Fire', 'Genshin Impact'], true)): ?><div class="form-field"><label for="server-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>">Server / Zone <span class="optional">opsional</span></label><input id="server-<?= storeEscape(strtolower(str_replace(' ', '-', $game))) ?>" class="game-server" type="text" maxlength="100" autocomplete="off" placeholder="Masukkan server atau zone"></div><?php endif; ?>
            <div class="game-card-foot"><span class="price-label">Harga item<strong data-price-label>Pilih item dulu</strong></span><button class="order-button" type="button" data-order-game="<?= storeEscape($game) ?>">Pesan via WhatsApp <span aria-hidden="true">↗</span></button></div>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if ($catalogCount === 0): ?><div class="empty-catalog"><span>✦</span><h3>Katalog sedang diperbarui</h3><p>Hubungi admin untuk menanyakan pilihan top up yang tersedia.</p><a class="button button-primary" href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Chat admin ↗</a></div><?php endif; ?>
      </div>
      <p class="catalog-footnote"><span>ⓘ</span> Harga yang belum tercantum akan dikonfirmasi admin sebelum pembayaran. Pastikan ID akunmu benar sebelum order.</p>
    </section>

    <section class="steps-section" id="cara-order"><div class="shell steps-layout"><div class="steps-intro"><span class="section-kicker">GAMPANG BANGET</span><h2>Top up santai,<br><span>tanpa ribet.</span></h2><p>Mulai dari pilih nominal sampai konfirmasi, tim kami siap nemenin prosesnya.</p><a class="text-link" href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Tanya admin dulu <span>↗</span></a></div><div class="steps-list"><article><span class="step-number">01</span><div><h3>Pilih game dan item</h3><p>Temukan game favoritmu lalu tentukan nominal top up.</p></div><span class="step-symbol">⌕</span></article><article><span class="step-number">02</span><div><h3>Isi detail akun</h3><p>Masukkan ID akun dengan benar. Tambahkan server jika diminta.</p></div><span class="step-symbol">↗</span></article><article><span class="step-number">03</span><div><h3>Konfirmasi lewat WhatsApp</h3><p>Order tersimpan dan kode pelacakan langsung dibuat.</p></div><span class="step-symbol">♡</span></article><article><span class="step-number">04</span><div><h3>Selesaikan pesananmu</h3><p>Admin bantu cek harga, pembayaran, dan proses top up.</p></div><span class="step-symbol">✦</span></article></div></div></section>

    <section class="tracking-section section shell" id="lacak-order"><div class="tracking-card"><div class="tracking-graphic"><div class="tracking-orbit"></div><span class="tracking-icon">⌕</span><span class="tracking-spark spark-a">✦</span><span class="tracking-spark spark-b">✧</span></div><div class="tracking-copy"><span class="section-kicker">PESANANMU</span><h2>Sudah order?<br><span>Cek statusnya di sini.</span></h2><p>Ketik kode yang kamu terima setelah pesanan dibuat.</p><form id="track-form" class="track-form"><label class="sr-only" for="track-code">Kode order</label><input id="track-code" name="code" placeholder="Contoh: ZS-20260927-ABC1234567" autocomplete="off" required><button type="submit">Lacak order <span>→</span></button></form><div class="track-result" id="track-result" aria-live="polite"></div></div></div></section>

    <section class="website-service shell"><div class="service-copy"><span class="section-kicker">LAYANAN DIGITAL</span><h2>Bisnis kamu juga<br><span>butuh website keren?</span></h2><p>Bangun kehadiran online yang rapi, cepat, dan disesuaikan dengan kebutuhan bisnismu.</p><a class="button button-primary" href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Konsultasi gratis <span>↗</span></a></div><div class="service-points"><div><i>✓</i><span><strong>Landing page</strong><small>Perkenalkan bisnis dengan lebih meyakinkan</small></span></div><div><i>✓</i><span><strong>Toko online</strong><small>Tampilkan produk dan terima pesanan</small></span></div><div><i>✓</i><span><strong>Integrasi database</strong><small>Kelola konten dan data dengan mudah</small></span></div></div><span class="service-decoration">Z.</span></section>

    <section class="faq-section section shell" id="faq"><div class="faq-heading"><span class="section-kicker">ADA YANG INGIN DITANYAKAN?</span><h2>Info singkat, biar<br><span>makin yakin.</span></h2><a class="text-link" href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener">Masih ada pertanyaan? <span>↗</span></a></div><div class="faq-list"><details><summary>Bagaimana cara memesan top up?<span>+</span></summary><p>Pilih game dan item, masukkan ID akun, lalu tekan tombol pesan. Pesanan akan tersimpan dan WhatsApp terbuka untuk konfirmasi bersama admin.</p></details><details><summary>Berapa lama proses top up berlangsung?<span>+</span></summary><p>Admin akan menyampaikan estimasi setelah mengecek pesanan dan pembayaran. Lama proses dapat berbeda tergantung game dan antrean.</p></details><details><summary>Bagaimana cara melacak pesanan?<span>+</span></summary><p>Simpan kode order yang muncul setelah pemesanan. Masukkan kode tersebut ke kolom pelacakan di halaman ini.</p></details><details><summary>Apakah harga sudah termasuk biaya lain?<span>+</span></summary><p>Harga akan ditampilkan jika sudah ditetapkan di katalog. Jika tertulis “Tanya admin”, minta konfirmasi harga sebelum menyelesaikan pembayaran.</p></details></div></section>

    <section class="closing-cta"><div class="shell closing-inner"><div><span class="section-kicker">SIAP LANJUT MAIN?</span><h2>Yuk, isi kebutuhan<br>game kamu hari ini.</h2></div><a class="button button-light" href="#games">Lihat pilihan top up <span>↗</span></a><div class="cta-decoration">✦</div></div></section>
  </main>
  <footer class="footer"><div class="shell footer-main"><a class="brand" href="#top"><span class="brand-mark">Z</span><span>zaidan<span class="brand-accent">store</span><small>TOP UP GAME & DIGITAL</small></span></a><p>Teman top up game andalanmu.<br>Pesan mudah, konfirmasi langsung bersama admin.</p><a href="<?= storeEscape($waLink) ?>" target="_blank" rel="noopener" class="footer-wa">WhatsApp admin <span>↗</span></a></div><div class="shell footer-bottom"><span>© <?= date('Y') ?> ZaidanStore. Dibuat buat para gamer.</span></div></footer>
  <a class="help-fab" id="chat-fab" href="https://wa.me/<?= storeEscape(WHATSAPP_NUMBER) ?>?text=<?= rawurlencode('Halo ZaidanStore, saya mengalami kendala dan butuh bantuan.') ?>" target="_blank" rel="noopener" aria-label="Chat bantuan ZaidanStore di WhatsApp">
    <svg width="21" height="21" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
  </a>
  <span class="help-tooltip" role="tooltip">Ada kendala? Chat admin</span>
  <div class="quick-scroll" aria-label="Navigasi cepat">
    <button class="quick-scroll-button" id="scroll-top" type="button" aria-label="Kembali ke atas" title="Kembali ke atas"><span aria-hidden="true">↑</span><svg class="scroll-progress" width="44" height="44" viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="18" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="113.1" stroke-dashoffset="113.1"/></svg></button>
  </div>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>
  <script src="assets/js/store.js?v=5" defer></script>
</body>
</html>
