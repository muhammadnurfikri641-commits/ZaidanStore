-- Jalankan setelah memilih database yang dipakai di config.php.
-- Contoh: USE ygywxhgiig_servers;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game VARCHAR(80) NOT NULL,
    item_name VARCHAR(120) NOT NULL,
    price BIGINT UNSIGNED NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_game_item (game, item_name),
    KEY idx_products_game_active (game, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    message VARCHAR(500) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_promos_active_dates (active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS store_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    game VARCHAR(80) NOT NULL,
    item_name VARCHAR(120) NOT NULL,
    game_id VARCHAR(40) NOT NULL,
    server VARCHAR(100) NULL,
    price BIGINT UNSIGNED NULL,
    status ENUM('pending','paid','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    note VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_orders_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO products (game, item_name, price) VALUES
('Mobile Legends','11 + 1 Diamonds',NULL),
('Mobile Legends','86 Diamonds',NULL),
('Mobile Legends','172 Diamonds',NULL),
('Mobile Legends','257 Diamonds',NULL),
('Mobile Legends','514 Diamonds',NULL),
('Mobile Legends','1050 Diamonds',NULL),
('Mobile Legends','2195 Diamonds',NULL),
('Mobile Legends','Weekly Diamond Pass',NULL),
('Mobile Legends','Twilight Pass',NULL),
('Free Fire','50 Diamonds',NULL),
('Free Fire','70 Diamonds',NULL),
('Free Fire','140 Diamonds',NULL),
('Free Fire','210 Diamonds',NULL),
('Free Fire','355 Diamonds',NULL),
('Free Fire','500 Diamonds',NULL),
('Free Fire','720 Diamonds',NULL),
('Free Fire','1000 Diamonds',NULL),
('Free Fire','Weekly Pass',NULL),
('Free Fire','Monthly Pass',NULL),
('Genshin Impact','60 Genesis Crystals',NULL),
('Genshin Impact','300 + 30 Genesis Crystals',NULL),
('Genshin Impact','980 + 110 Genesis Crystals',NULL),
('Genshin Impact','1980 + 260 Genesis Crystals',NULL),
('Genshin Impact','3280 + 600 Genesis Crystals',NULL),
('Genshin Impact','6480 + 1600 Genesis Crystals',NULL),
('Genshin Impact','Welkin Moon',NULL),
('PUBG Mobile','60 UC',NULL),
('PUBG Mobile','120 + 20 UC',NULL),
('PUBG Mobile','325 + 65 UC',NULL),
('PUBG Mobile','660 + 133 UC',NULL),
('PUBG Mobile','1800 + 453 UC',NULL),
('PUBG Mobile','3850 + 1155 UC',NULL),
('PUBG Mobile','Weekly Pass',NULL),
('PUBG Mobile','Monthly Ultimate Pass',NULL),
('Call of Duty Mobile','80 CP',NULL),
('Call of Duty Mobile','200 CP',NULL),
('Call of Duty Mobile','400 + 40 CP',NULL),
('Call of Duty Mobile','800 + 80 CP',NULL),
('Call of Duty Mobile','2000 + 200 CP',NULL),
('Call of Duty Mobile','Battle Pass Season',NULL),
('Call of Duty Mobile','Battle Pass Bundle',NULL),
('Call of Duty Mobile','Seasonal Token',NULL);

INSERT INTO promos (title, message)
SELECT 'Top up makin hemat', 'Chat admin untuk cek promo dan harga terbaru.'
WHERE NOT EXISTS (SELECT 1 FROM promos);
