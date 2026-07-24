<?php

declare(strict_types=1);

namespace App;

use DOMElement;
use DOMNode;
use RuntimeException;
use Throwable;

final class ProductScraper
{
    private array $excludedRootSlugs = [
        '',
        'collections',
        'pages',
        'blogs',
        'account',
        'cart',
        'search',
        'contact',
        'lien-he',
        'gioi-thieu',
        'tin-tuc',
        'san-pham',
        'all',
        'apps',
        'admin',
        'checkout',
        'checkouts',
        'policies',
        'wishlist',
        'he-thong-cua-hang',
        'dang-ky',
        'dang-nhap',
    ];

    public function __construct(
        private readonly array $config,
        private readonly HttpClient $http,
    ) {
    }

    public function crawl(
        int $startPage,
        int $endPage,
        bool $downloadImages = false,
        bool $resume = true,
        bool $refreshExisting = false,
    ): array {
        $storagePath = $this->config['storage_path'];
        $jsonPath = $storagePath . '/products.json';

        $products = [];

        if ($resume && is_file($jsonPath)) {
            $existing = json_decode((string) file_get_contents($jsonPath), true);

            if (is_array($existing)) {
                foreach ($existing as $product) {
                    if (!empty($product['source_url'])) {
                        $products[$product['source_url']] = $product;
                    }
                }
            }
        }

        $productUrls = $this->collectProductUrls($startPage, $endPage);

        echo PHP_EOL . 'Tổng URL sản phẩm phát hiện: ' . count($productUrls) . PHP_EOL;

        $index = 0;

        foreach ($productUrls as $productUrl) {
            $index++;

            if (isset($products[$productUrl]) && !$refreshExisting) {
                echo sprintf("[%d/%d] Bỏ qua, đã có: %s\n", $index, count($productUrls), $productUrl);
                continue;
            }

            echo sprintf("[%d/%d] Đọc: %s\n", $index, count($productUrls), $productUrl);

            try {
                $product = $this->scrapeProduct($productUrl);

                if ($product === null) {
                    echo "  -> Không nhận diện là trang sản phẩm.\n";
                    unset($products[$productUrl]);
                    continue;
                }

                if ($downloadImages) {
                    $product['local_images'] = $this->downloadProductImages($product);
                } elseif (isset($products[$productUrl]['local_images'])) {
                    // Refresh nội dung không làm mất liên kết tới ảnh đã tải trước đó.
                    $product['local_images'] = $products[$productUrl]['local_images'];
                }

                $products[$productUrl] = $product;
                $this->saveProducts(array_values($products));
            } catch (Throwable $e) {
                echo '  -> Lỗi: ' . $e->getMessage() . PHP_EOL;
                $this->appendError($productUrl, $e->getMessage());
            }
        }

        $result = array_values($products);
        $this->saveProducts($result);
        $this->saveCsv($result);

        return $result;
    }

    private function collectProductUrls(int $startPage, int $endPage): array
    {
        $urls = [];
        $consecutiveEmptyPages = 0;

        for ($page = $startPage; $page <= $endPage; $page++) {
            $url = rtrim($this->config['base_url'], '/')
                . $this->config['collection_path']
                . '?page=' . $page;

            echo "Quét danh sách trang {$page}: {$url}\n";

            try {
                $html = $this->http->get($url);
            } catch (Throwable $e) {
                echo "  -> Không tải được: {$e->getMessage()}\n";
                $consecutiveEmptyPages++;

                if ($consecutiveEmptyPages >= 3) {
                    break;
                }

                continue;
            }

            $document = new HtmlDocument($html);
            $pageUrls = $this->extractProductCandidates($document);

            $before = count($urls);

            foreach ($pageUrls as $candidate) {
                $urls[$candidate] = $candidate;
            }

            $added = count($urls) - $before;
            echo "  -> Phát hiện thêm {$added} URL mới.\n";

            if ($added === 0) {
                $consecutiveEmptyPages++;
            } else {
                $consecutiveEmptyPages = 0;
            }

            // Hai trang liên tiếp không có URL mới thường là đã hết phân trang.
            if ($consecutiveEmptyPages >= 2) {
                break;
            }
        }

        return array_values($urls);
    }

    private function extractProductCandidates(HtmlDocument $document): array
    {
        $urls = [];
        $nodes = $document->xpath->query('//a[@href]');

        if ($nodes === false) {
            return [];
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $href = trim($node->getAttribute('href'));

            if ($href === '' || str_starts_with($href, '#')) {
                continue;
            }

            $hrefScheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
            if ($hrefScheme !== '' && !in_array($hrefScheme, ['http', 'https'], true)) {
                continue;
            }

            $absolute = HtmlDocument::absoluteUrl($href, $this->config['base_url']);

            if ($absolute === '') {
                continue;
            }

            $parts = parse_url($absolute);

            if (!is_array($parts)) {
                continue;
            }

            $baseHost = parse_url($this->config['base_url'], PHP_URL_HOST);
            $host = $parts['host'] ?? '';

            if ($host !== $baseHost) {
                continue;
            }

            $path = trim($parts['path'] ?? '', '/');

            // Sản phẩm của website này nằm trực tiếp ở root: /ten-san-pham.
            if ($path === '' || str_contains($path, '/')) {
                continue;
            }

            $slug = strtolower($path);

            if (in_array($slug, $this->excludedRootSlugs, true)) {
                continue;
            }

            // Ưu tiên link nằm trong card có class chứa "product".
            $insideProductCard = $document->xpath->query(
                'ancestor::*[contains(translate(@class, "PRODUCT", "product"), "product")]',
                $node
            );

            $anchorText = trim((string) $node->textContent);
            $hasImage = $document->xpath->query('.//img', $node);

            $looksLikeProductCard =
                ($insideProductCard !== false && $insideProductCard->length > 0)
                || ($hasImage !== false && $hasImage->length > 0)
                || mb_strlen($anchorText) >= 12;

            if (!$looksLikeProductCard) {
                continue;
            }

            $cleanUrl = rtrim($this->config['base_url'], '/') . '/' . $path;
            $urls[$cleanUrl] = $cleanUrl;
        }

        return array_values($urls);
    }

    private function scrapeProduct(string $url): ?array
    {
        $html = $this->http->get($url);
        $document = new HtmlDocument($html);

        $titleNodeList = $document->xpath->query(
            '//section[contains(@itemtype, "schema.org/Product")]//h1[1] | //h1[1]'
        );

        if ($titleNodeList === false || $titleNodeList->length === 0) {
            return null;
        }

        $titleNode = $titleNodeList->item(0);
        $title = $document->cleanText((string) $titleNode?->textContent);

        if ($title === '') {
            return null;
        }

        $detailContainer = $this->findProductDetailContainer($document, $titleNode);
        $detailText = $detailContainer
            ? $document->cleanText((string) $detailContainer->textContent)
            : $document->cleanText((string) $document->dom->textContent);

        // Xác thực trang sản phẩm, tránh nhập nhầm trang chính sách/tin tức.
        if (
            $document->xpath->query(
                '//section[contains(@itemtype, "schema.org/Product")]'
            )?->length === 0
            && !str_contains($detailText, 'Thêm vào giỏ hàng')
            && !preg_match('/\bVNĐ\b/u', $detailText)
        ) {
            return null;
        }

        $canonical = $document->firstAttribute(
            '//link[translate(@rel, "CANONICAL", "canonical")="canonical"]',
            'href'
        ) ?? $url;

        $metaDescription = $document->firstAttribute(
            '//meta[translate(@name, "DESCRIPTION", "description")="description"]',
            'content'
        );

        [$price, $compareAtPrice] = $this->extractPricesFromDocument($document);

        $brand = $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]'
            . '//*[@itemprop="brand"]//meta[@itemprop="name"][1]',
            'content'
        ) ?? $this->matchField(
            $detailText,
            'Thương hiệu',
            ['Mã', 'Chất liệu', 'Tình trạng', '|']
        );

        $sku = $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]//meta[@itemprop="sku"][1]',
            'content'
        ) ?? $this->matchField(
            $detailText,
            'Mã',
            ['Chất liệu', 'Thương hiệu', 'Tình trạng', '|']
        );

        $productType = $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]'
            . '//*[@itemprop="type"]//meta[@itemprop="name"][1]',
            'content'
        ) ?? $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]//meta[@itemprop="category"][1]',
            'content'
        ) ?? $this->matchField(
            $detailText,
            'Chất liệu',
            ['Thương hiệu', 'Tình trạng', '|']
        );

        $availability = strtolower((string) $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]//*[@itemprop="availability"][1]',
            'href'
        ));

        $stockStatus = match (true) {
            str_contains($availability, 'outofstock') => 'out_of_stock',
            str_contains($availability, 'instock') => 'in_stock',
            preg_match('/\bHết hàng\b/u', $detailText) === 1 => 'out_of_stock',
            preg_match('/\bCòn hàng\b/u', $detailText) === 1 => 'in_stock',
            default => null,
        };

        $descriptionHtml = $this->extractDescriptionHtml($document);
        $images = $this->extractImages($document);
        $variants = $this->extractVariants(
            $document,
            $price,
            $sku,
            $stockStatus
        );

        // Một số trang danh mục nằm trực tiếp ở root và có h1 giống trang chi tiết.
        // Chỉ giữ trang có ít nhất một tín hiệu dữ liệu sản phẩm thực tế.
        if (
            $price === null
            && trim((string) $sku) === ''
            && trim((string) $brand) === ''
            && $images === []
        ) {
            return null;
        }

        return [
            'source_url' => $url,
            'canonical_url' => HtmlDocument::absoluteUrl($canonical, $this->config['base_url']),
            'slug' => basename(parse_url($url, PHP_URL_PATH) ?: ''),
            'name' => $title,
            'brand' => $brand,
            'sku' => $sku,
            'product_type' => $productType,
            'stock_status' => $stockStatus,
            'price' => $price,
            'compare_at_price' => $compareAtPrice,
            'currency' => 'VND',
            'meta_description' => $metaDescription,
            'description_html' => $descriptionHtml,
            'images' => $images,
            'local_images' => [],
            'variants' => $variants,
            'scraped_at' => date(DATE_ATOM),
        ];
    }

    private function findProductDetailContainer(HtmlDocument $document, ?DOMNode $titleNode): ?DOMNode
    {
        if ($titleNode === null) {
            return null;
        }

        $current = $titleNode;

        for ($level = 0; $level < 8 && $current !== null; $level++) {
            $text = $document->cleanText((string) $current->textContent);

            if (
                mb_strlen($text) < 10000
                && (
                    str_contains($text, 'Thêm vào giỏ hàng')
                    || str_contains($text, 'Số lượng')
                    || str_contains($text, 'Tình trạng')
                )
            ) {
                return $current;
            }

            $current = $current->parentNode;
        }

        return $titleNode->parentNode;
    }

    private function extractPrices(string $text): array
    {
        preg_match_all('/([0-9][0-9\.\,\s]*)\s*VNĐ/iu', $text, $matches);

        if (empty($matches[1])) {
            return [null, null];
        }

        $values = [];

        foreach ($matches[1] as $raw) {
            $number = preg_replace('/\D+/', '', $raw);

            if ($number !== '' && (int) $number > 0) {
                $values[] = (int) $number;
            }
        }

        $values = array_values(array_unique($values));

        if ($values === []) {
            return [null, null];
        }

        // Trong vùng chi tiết, giá bán thường xuất hiện trước giá gốc.
        $price = $values[0];
        $compare = null;

        foreach (array_slice($values, 1) as $candidate) {
            if ($candidate > $price) {
                $compare = $candidate;
                break;
            }
        }

        return [$price, $compare];
    }

    private function matchField(string $text, string $field, array $stoppers): ?string
    {
        $escapedStoppers = array_map(
            static fn (string $value): string => preg_quote($value, '/'),
            $stoppers
        );

        $stopPattern = implode('|', $escapedStoppers);
        $pattern = '/'
            . preg_quote($field, '/')
            . '\s*:\s*(.*?)\s*(?=(?:'
            . $stopPattern
            . ')\s*:|(?:'
            . $stopPattern
            . ')|$)/iu';

        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }

        $value = trim($matches[1]);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        if ($value === '' || mb_strlen($value) > 200) {
            return null;
        }

        return $value;
    }

    private function extractPricesFromDocument(HtmlDocument $document): array
    {
        $price = $this->extractIntegerAttribute(
            $document,
            '//section[contains(@itemtype, "schema.org/Product")]'
            . '//*[@itemprop="offers"]//meta[@itemprop="price"][1]',
            'content'
        );

        if ($price === null) {
            $price = $this->extractMoneyText(
                $document->firstString(
                    '//form[@id="add-to-cart-form"]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " product-price ")]'
                    . '[not(ancestor::*[contains(concat(" ", normalize-space(@class), " "), " old-price ")])][1]'
                )
            );
        }

        $compareAtPrice = $this->extractIntegerAttribute(
            $document,
            '//form[@id="add-to-cart-form"]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " old-price ")]'
            . '//meta[@itemprop="price"][1]',
            'content'
        );

        if ($compareAtPrice === null) {
            $compareAtPrice = $this->extractMoneyText(
                $document->firstString(
                    '//form[@id="add-to-cart-form"]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " product-price-old ")][1]'
                )
            );
        }

        if ($compareAtPrice !== null && $price !== null && $compareAtPrice <= $price) {
            $compareAtPrice = null;
        }

        return [$price, $compareAtPrice];
    }

    private function extractIntegerAttribute(
        HtmlDocument $document,
        string $query,
        string $attribute
    ): ?int {
        $raw = $document->firstAttribute($query, $attribute);

        if ($raw === null) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $raw);

        if ($number === '' || (int) $number <= 0) {
            return null;
        }

        return (int) $number;
    }

    private function extractMoneyText(?string $text): ?int
    {
        if ($text === null) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $text);

        if ($number === '' || (int) $number <= 0) {
            return null;
        }

        return (int) $number;
    }

    private function extractDescriptionHtml(HtmlDocument $document): ?string
    {
        $queries = [
            '//*[@id="tab-1"]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " rte ")][1]',
            '//*[@id="tab-1"][1]',
            '//section[contains(@itemtype, "schema.org/Product")]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " product-tab ")]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " tab-content ")][1]',
        ];

        foreach ($queries as $query) {
            $nodes = $document->xpath->query($query);

            if ($nodes === false || $nodes->length === 0) {
                continue;
            }

            $node = $nodes->item(0);

            if ($node === null) {
                continue;
            }

            $html = $document->innerHtml($node);
            $html = preg_replace(
                '#<(script|style|form|button)\b[^>]*>.*?</\1>#isu',
                '',
                $html
            ) ?? $html;
            $html = trim($html);

            $plainText = $document->cleanText(strip_tags($html));

            if ($html !== '' && mb_strlen($plainText) >= 10) {
                return $html;
            }
        }

        // Sapo thường nhúng toàn bộ mô tả dạng text vào schema Product.
        $descriptionText = $document->firstAttribute(
            '//section[contains(@itemtype, "schema.org/Product")]'
            . '//meta[@itemprop="description"][1]',
            'content'
        );

        if ($descriptionText === null || trim($descriptionText) === '') {
            return null;
        }

        $descriptionText = str_replace(["\r\n", "\r"], "\n", trim($descriptionText));
        $blocks = preg_split('/\n{2,}/u', $descriptionText) ?: [];
        $htmlBlocks = [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            $escaped = htmlspecialchars(
                $block,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $htmlBlocks[] = '<p>' . nl2br($escaped, false) . '</p>';
        }

        return $htmlBlocks !== [] ? implode("\n", $htmlBlocks) : null;
    }

    private function extractImages(HtmlDocument $document): array
    {
        $urls = [];

        // Chỉ đọc gallery chính của sản phẩm. Không quét toàn trang vì phía dưới
        // còn có "sản phẩm liên quan", "sản phẩm nổi bật" và ảnh thumbnail.
        $queries = [
            [
                'query' => '//section[contains(@itemtype, "schema.org/Product")]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " product-image-block ")]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " gallery-top ")]'
                    . '//img[@data-image]',
                'attributes' => ['data-image'],
            ],
            [
                'query' => '//section[contains(@itemtype, "schema.org/Product")]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " product-image-block ")]'
                    . '//*[contains(concat(" ", normalize-space(@class), " "), " gallery-top ")]'
                    . '//a[@href]',
                'attributes' => ['href'],
            ],
            [
                'query' => '//section[contains(@itemtype, "schema.org/Product")]'
                    . '//meta[@itemprop="image"][1]',
                'attributes' => ['content'],
            ],
        ];

        foreach ($queries as $group) {
            $nodes = $document->xpath->query($group['query']);

            if ($nodes === false || $nodes->length === 0) {
                continue;
            }

            foreach ($nodes as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }

                foreach ($group['attributes'] as $attribute) {
                    if (!$node->hasAttribute($attribute)) {
                        continue;
                    }

                    $absolute = HtmlDocument::absoluteUrl(
                        $node->getAttribute($attribute),
                        $this->config['base_url']
                    );

                    $normalized = $this->normalizeProductImageUrl($absolute);

                    if ($normalized === null) {
                        continue;
                    }

                    $key = $this->productImageKey($normalized);
                    $urls[$key] = $normalized;
                    break;
                }
            }

            // data-image trong gallery-top là nguồn chính xác nhất.
            if ($urls !== []) {
                break;
            }
        }

        return array_values($urls);
    }

    private function normalizeProductImageUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (
            !str_contains($host, 'bizweb.dktcdn.net')
            || !str_contains(strtolower($path), '/products/')
        ) {
            return null;
        }

        $url = preg_replace('#/thumb/[^/]+/#i', '/', $url) ?? $url;
        $url = preg_replace('#^http://#i', 'https://', $url) ?? $url;

        return $url;
    }

    private function productImageKey(string $url): string
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $query = (string) parse_url($url, PHP_URL_QUERY);

        return $path . ($query !== '' ? '?' . $query : '');
    }

    private function extractVariants(
        HtmlDocument $document,
        ?int $defaultPrice,
        ?string $defaultSku,
        ?string $stockStatus
    ): array {
        $variants = [];
        $availabilityMap = [];

        $swatches = $document->xpath->query(
            '//form[@id="add-to-cart-form"]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " swatch-element ")]'
            . '[@data-value]'
        );

        if ($swatches !== false) {
            foreach ($swatches as $swatch) {
                if (!$swatch instanceof DOMElement) {
                    continue;
                }

                $name = trim($swatch->getAttribute('data-value'));
                $classes = ' ' . strtolower($swatch->getAttribute('class')) . ' ';

                if ($name !== '') {
                    $availabilityMap[$name] = !str_contains($classes, ' soldout ');
                }
            }
        }

        $options = $document->xpath->query(
            '//form[@id="add-to-cart-form"]//select[@id="product-selectors"]/option'
        );

        if ($options !== false && $options->length > 0) {
            foreach ($options as $option) {
                if (!$option instanceof DOMElement) {
                    continue;
                }

                $text = $document->cleanText((string) $option->textContent);
                $name = $text;
                $price = null;

                if (
                    preg_match(
                        '/^(.*?)\s*-\s*([0-9][0-9\.\,\s]*)\s*VNĐ$/iu',
                        $text,
                        $matches
                    )
                ) {
                    $name = trim($matches[1]);
                    $price = $this->extractMoneyText($matches[2]);
                }

                $variants[] = [
                    'source_variant_id' => $option->getAttribute('value') ?: null,
                    'name' => $name !== '' ? $name : 'Default Title',
                    'sku' => null,
                    'price' => $price ?? $defaultPrice,
                    'compare_at_price' => null,
                    'available' => $availabilityMap[$name]
                        ?? ($stockStatus !== 'out_of_stock'),
                    'is_default' => $option->hasAttribute('selected'),
                ];
            }
        }

        if ($variants !== []) {
            return $variants;
        }

        $sourceVariantId = $document->firstAttribute(
            '//form[@id="add-to-cart-form"]//input[@id="one_variant"][1]',
            'value'
        );

        return [[
            'source_variant_id' => $sourceVariantId,
            'name' => 'Default Title',
            'sku' => $defaultSku,
            'price' => $defaultPrice,
            'compare_at_price' => null,
            'available' => $stockStatus !== 'out_of_stock',
            'is_default' => true,
        ]];
    }

    private function downloadProductImages(array $product): array
    {
        $localFiles = [];
        $slug = $this->safeFilename($product['slug'] ?: $product['name']);
        $directory = rtrim($this->config['image_path'], '/') . '/' . $slug;

        // Khi quét lại, xóa toàn bộ ảnh cũ của riêng sản phẩm để không còn
        // các file 03.jpg, 04.jpg... từng bị lấy nhầm từ sản phẩm liên quan.
        $this->deleteDirectory($directory);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Không tạo được thư mục ảnh {$directory}");
        }

        foreach ($product['images'] as $index => $imageUrl) {
            $path = parse_url($imageUrl, PHP_URL_PATH) ?: '';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)) {
                $extension = 'jpg';
            }

            $filename = sprintf('%02d.%s', $index + 1, $extension);
            $destination = $directory . '/' . $filename;

            try {
                $this->http->download($imageUrl, $destination);
                $localFiles[] = 'storage/images/' . $slug . '/' . $filename;
                echo "    Đã tải ảnh: {$filename}\n";
            } catch (Throwable $e) {
                $localFiles[] = null;
                echo "    Lỗi ảnh: {$e->getMessage()}\n";
            }
        }

        return $localFiles;
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

    private function saveProducts(array $products): void
    {
        $storage = $this->config['storage_path'];

        if (!is_dir($storage) && !mkdir($storage, 0775, true) && !is_dir($storage)) {
            throw new RuntimeException("Không tạo được thư mục {$storage}");
        }

        $destination = $storage . '/products.json';
        $temporary = $destination . '.tmp';
        $json = json_encode(
            $products,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        if (file_put_contents($temporary, $json, LOCK_EX) === false) {
            throw new RuntimeException("Không ghi được file tạm {$temporary}");
        }

        if (!@rename($temporary, $destination)) {
            if (!copy($temporary, $destination)) {
                @unlink($temporary);
                throw new RuntimeException("Không thay thế được {$destination}");
            }
            @unlink($temporary);
        }
    }

    private function saveCsv(array $products): void
    {
        $path = $this->config['storage_path'] . '/products.csv';
        $fp = fopen($path, 'wb');

        if ($fp === false) {
            throw new RuntimeException("Không tạo được {$path}");
        }

        // UTF-8 BOM để Excel Windows đọc đúng tiếng Việt.
        fwrite($fp, "\xEF\xBB\xBF");

        fputcsv($fp, [
            'source_url',
            'slug',
            'name',
            'brand',
            'sku',
            'product_type',
            'stock_status',
            'price',
            'compare_at_price',
            'currency',
            'meta_description',
            'images',
            'local_images',
            'scraped_at',
        ]);

        foreach ($products as $product) {
            fputcsv($fp, [
                $product['source_url'] ?? '',
                $product['slug'] ?? '',
                $product['name'] ?? '',
                $product['brand'] ?? '',
                $product['sku'] ?? '',
                $product['product_type'] ?? '',
                $product['stock_status'] ?? '',
                $product['price'] ?? '',
                $product['compare_at_price'] ?? '',
                $product['currency'] ?? 'VND',
                $product['meta_description'] ?? '',
                implode('|', $product['images'] ?? []),
                implode('|', $product['local_images'] ?? []),
                $product['scraped_at'] ?? '',
            ]);
        }

        fclose($fp);
    }

    private function appendError(string $url, string $message): void
    {
        $line = json_encode([
            'url' => $url,
            'message' => $message,
            'time' => date(DATE_ATOM),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents(
            $this->config['storage_path'] . '/errors.log',
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private function safeFilename(string $value): string
    {
        $value = mb_strtolower($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        return $value !== '' ? mb_substr($value, 0, 120) : 'product-' . time();
    }
}
