CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_url VARCHAR(1000) NOT NULL,
    canonical_url VARCHAR(1000) NULL,
    slug VARCHAR(255) NOT NULL,
    name VARCHAR(500) NOT NULL,
    brand VARCHAR(255) NULL,
    sku VARCHAR(255) NULL,
    product_type VARCHAR(255) NULL,
    stock_status VARCHAR(50) NULL,
    price BIGINT UNSIGNED NULL,
    compare_at_price BIGINT UNSIGNED NULL,
    currency CHAR(3) NOT NULL DEFAULT 'VND',
    meta_description TEXT NULL,
    description_html LONGTEXT NULL,
    scraped_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY products_source_url_unique (source_url(255)),
    UNIQUE KEY products_slug_unique (slug)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    source_url VARCHAR(1000) NOT NULL,
    local_path VARCHAR(1000) NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT product_images_product_id_foreign
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE,
    INDEX product_images_product_id_index (product_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
