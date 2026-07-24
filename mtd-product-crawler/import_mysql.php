<?php

declare(strict_types=1);

/*
Ví dụ:
DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=test DB_USERNAME=root DB_PASSWORD= \
php import_mysql.php
*/

$jsonPath = __DIR__ . '/storage/products.json';

if (!is_file($jsonPath)) {
    fwrite(STDERR, "Chưa có storage/products.json. Hãy chạy run.php trước.\n");
    exit(1);
}

$products = json_decode((string) file_get_contents($jsonPath), true);

if (!is_array($products)) {
    fwrite(STDERR, "products.json không hợp lệ.\n");
    exit(1);
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'test';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$productSql = <<<'SQL'
INSERT INTO products (
    source_url, canonical_url, slug, name, brand, sku, product_type,
    stock_status, price, compare_at_price, currency, meta_description,
    description_html, scraped_at
) VALUES (
    :source_url, :canonical_url, :slug, :name, :brand, :sku, :product_type,
    :stock_status, :price, :compare_at_price, :currency, :meta_description,
    :description_html, :scraped_at
)
ON DUPLICATE KEY UPDATE
    canonical_url = VALUES(canonical_url),
    name = VALUES(name),
    brand = VALUES(brand),
    sku = VALUES(sku),
    product_type = VALUES(product_type),
    stock_status = VALUES(stock_status),
    price = VALUES(price),
    compare_at_price = VALUES(compare_at_price),
    currency = VALUES(currency),
    meta_description = VALUES(meta_description),
    description_html = VALUES(description_html),
    scraped_at = VALUES(scraped_at)
SQL;

$imageSql = <<<'SQL'
INSERT INTO product_images (product_id, source_url, local_path, position)
VALUES (:product_id, :source_url, :local_path, :position)
SQL;

$productStatement = $pdo->prepare($productSql);
$imageStatement = $pdo->prepare($imageSql);

$pdo->beginTransaction();

try {
    foreach ($products as $product) {
        $scrapedAt = null;

        if (!empty($product['scraped_at'])) {
            $timestamp = strtotime($product['scraped_at']);
            $scrapedAt = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        $productStatement->execute([
            'source_url' => $product['source_url'],
            'canonical_url' => $product['canonical_url'] ?? null,
            'slug' => $product['slug'],
            'name' => $product['name'],
            'brand' => $product['brand'] ?? null,
            'sku' => $product['sku'] ?? null,
            'product_type' => $product['product_type'] ?? null,
            'stock_status' => $product['stock_status'] ?? null,
            'price' => $product['price'] ?? null,
            'compare_at_price' => $product['compare_at_price'] ?? null,
            'currency' => $product['currency'] ?? 'VND',
            'meta_description' => $product['meta_description'] ?? null,
            'description_html' => $product['description_html'] ?? null,
            'scraped_at' => $scrapedAt,
        ]);

        $idStatement = $pdo->prepare('SELECT id FROM products WHERE source_url = ? LIMIT 1');
        $idStatement->execute([$product['source_url']]);
        $productId = (int) $idStatement->fetchColumn();

        $pdo->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$productId]);

        $images = $product['images'] ?? [];
        $localImages = $product['local_images'] ?? [];

        foreach ($images as $position => $sourceUrl) {
            $imageStatement->execute([
                'product_id' => $productId,
                'source_url' => $sourceUrl,
                'local_path' => $localImages[$position] ?? null,
                'position' => $position,
            ]);
        }
    }

    $pdo->commit();
    echo 'Đã nhập ' . count($products) . " sản phẩm vào MySQL.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Import lỗi: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
