CREATE TABLE IF NOT EXISTS products
(
    id INT AUTO_INCREMET PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL COMMENT 'UUID товара',
    category VARCHAR(255) NOT NULL COMMENT 'Категория товара',
    is_active TINYINT(1) DEFAULT 1 NOT NULL COMMENT 'Флаг активности',
    name TEXT DEFAULT '' NOT NULL COMMENT 'Тип услуги',
    description TEXT NULL COMMENT 'Описание товара',
    thumbnail VARCHAR(255) NULL COMMENT 'Ссылка на картинку',
    price FLOAT NOT NULL COMMENT 'Цена'
) COMMENT 'Товары';

CREATE UNIQUE INDEX idx_product_uuid ON products (uuid);