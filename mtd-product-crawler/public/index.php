<?php

declare(strict_types=1);

$jsonPath = dirname(__DIR__) . '/storage/products.json';
$products = [];

if (is_file($jsonPath)) {
    $decoded = json_decode((string) file_get_contents($jsonPath), true);

    if (is_array($decoded)) {
        $products = $decoded;
    }
}

function money(?int $value): string
{
    return $value ? number_format($value, 0, ',', '.') . ' ₫' : 'Liên hệ';
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dữ liệu sản phẩm đã thu thập</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div>
            <p class="eyebrow">PRODUCT MIGRATION</p>
            <h1>Dữ liệu sản phẩm</h1>
        </div>
        <div class="count-box">
            <strong id="resultCount"><?= count($products) ?></strong>
            <span>sản phẩm</span>
        </div>
    </div>
</header>

<main class="container">
    <section class="toolbar" aria-label="Bộ lọc">
        <label class="search-field">
            <span>Tìm kiếm</span>
            <input id="searchInput" type="search" placeholder="Tên, thương hiệu, SKU...">
        </label>

        <label>
            <span>Thương hiệu</span>
            <select id="brandFilter">
                <option value="">Tất cả</option>
            </select>
        </label>

        <label>
            <span>Tình trạng</span>
            <select id="stockFilter">
                <option value="">Tất cả</option>
                <option value="in_stock">Còn hàng</option>
                <option value="out_of_stock">Hết hàng</option>
            </select>
        </label>
    </section>

    <?php if ($products === []): ?>
        <section class="empty">
            <h2>Chưa có dữ liệu</h2>
            <p>Chạy <code>php run.php --start=1 --end=41</code> trước.</p>
        </section>
    <?php else: ?>
        <section class="product-grid" id="productGrid">
            <?php foreach ($products as $product): ?>
                <?php
                $images = $product['local_images'] ?: ($product['images'] ?? []);
                $image = $images[0] ?? '';
                if ($image !== '' && str_starts_with($image, 'storage/')) {
                    $image = '../' . $image;
                }
                ?>
                <article
                    class="product-card"
                    data-name="<?= e(mb_strtolower($product['name'] ?? '')) ?>"
                    data-brand="<?= e($product['brand'] ?? '') ?>"
                    data-stock="<?= e($product['stock_status'] ?? '') ?>"
                    data-search="<?= e(mb_strtolower(
                        ($product['name'] ?? '') . ' '
                        . ($product['brand'] ?? '') . ' '
                        . ($product['sku'] ?? '')
                    )) ?>"
                >
                    <a class="product-image" href="<?= e($product['source_url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                        <?php if ($image !== ''): ?>
                            <img src="<?= e($image) ?>" alt="<?= e($product['name'] ?? '') ?>" loading="lazy">
                        <?php else: ?>
                            <div class="image-placeholder">Không có ảnh</div>
                        <?php endif; ?>
                    </a>

                    <div class="product-body">
                        <div class="meta-row">
                            <span><?= e($product['brand'] ?? 'Chưa có hãng') ?></span>
                            <span class="stock <?= e($product['stock_status'] ?? '') ?>">
                                <?= ($product['stock_status'] ?? '') === 'out_of_stock' ? 'Hết hàng' : 'Còn hàng' ?>
                            </span>
                        </div>

                        <h2>
                            <a href="<?= e($product['source_url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                                <?= e($product['name'] ?? '') ?>
                            </a>
                        </h2>

                        <p class="sku">SKU: <?= e($product['sku'] ?? '—') ?></p>

                        <div class="price-row">
                            <strong><?= money(isset($product['price']) ? (int) $product['price'] : null) ?></strong>
                            <?php if (!empty($product['compare_at_price'])): ?>
                                <del><?= money((int) $product['compare_at_price']) ?></del>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <p id="noResults" class="empty hidden">Không có sản phẩm phù hợp.</p>
    <?php endif; ?>
</main>

<script src="assets/app.js"></script>
</body>
</html>
