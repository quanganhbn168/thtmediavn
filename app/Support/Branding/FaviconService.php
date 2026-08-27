<?php

namespace App\Support\Branding;

use Awcodes\Curator\Facades\Curator;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickPixel;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class FaviconService
{
    private const GENERATED_DIRECTORY = 'favicon-assets';

    /** @var array<string, int> */
    private const PNG_FILES = [
        'favicon-16x16.png' => 16,
        'favicon-32x32.png' => 32,
        'favicon-48x48.png' => 48,
        'favicon-96x96.png' => 96,
        'apple-touch-icon.png' => 180,
        'android-chrome-192x192.png' => 192,
        'android-chrome-512x512.png' => 512,
    ];

    /** @return array<int, array{rel: string, type: string, href: string, sizes?: string, color?: string}> */
    public function links(?Media $customMedia = null): array
    {
        if (! $customMedia || ! $this->hasGeneratedPack($customMedia)) {
            $href = $customMedia?->getUrl() ?: asset('favicon.ico');
            $type = $customMedia?->mime_type ?: 'image/x-icon';

            return [
                ['rel' => 'icon', 'type' => $type, 'href' => $href],
                ['rel' => 'shortcut icon', 'type' => $type, 'href' => $href],
                ['rel' => 'apple-touch-icon', 'type' => $type, 'href' => $href],
            ];
        }

        $asset = fn (string $filename): string => asset(self::GENERATED_DIRECTORY.'/'.$filename);

        return [
            ['rel' => 'icon', 'type' => 'image/svg+xml', 'href' => $asset('favicon.svg')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '16x16', 'href' => $asset('favicon-16x16.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '32x32', 'href' => $asset('favicon-32x32.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '48x48', 'href' => $asset('favicon-48x48.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '96x96', 'href' => $asset('favicon-96x96.png')],
            ['rel' => 'icon', 'type' => 'image/x-icon', 'href' => $asset('favicon.ico')],
            ['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $asset('favicon.ico')],
            ['rel' => 'apple-touch-icon', 'type' => 'image/png', 'sizes' => '180x180', 'href' => $asset('apple-touch-icon.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '192x192', 'href' => $asset('android-chrome-192x192.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '512x512', 'href' => $asset('android-chrome-512x512.png')],
            ['rel' => 'mask-icon', 'type' => 'image/svg+xml', 'color' => '#ee6b2d', 'href' => $asset('favicon.svg')],
            ['rel' => 'manifest', 'type' => 'application/manifest+json', 'href' => $asset('site.webmanifest')],
        ];
    }

    public function primaryUrl(?Media $customMedia = null): string
    {
        if ($customMedia && ! $this->hasGeneratedPack($customMedia)) {
            return $customMedia->getUrl();
        }

        return asset(($customMedia ? self::GENERATED_DIRECTORY.'/' : '').'favicon.ico');
    }

    public function primaryPath(?Media $customMedia = null): string
    {
        if ($customMedia && $this->hasGeneratedPack($customMedia)) {
            return public_path(self::GENERATED_DIRECTORY.'/favicon.ico');
        }

        if ($customMedia && is_file($customMedia->getPath())) {
            return $customMedia->getPath();
        }

        $defaultPath = public_path('favicon.ico');
        if (is_file($defaultPath) && filesize($defaultPath) > 0) {
            return $defaultPath;
        }

        throw new RuntimeException('Không tìm thấy file favicon để phục vụ.');
    }

    public function primaryMimeType(?Media $customMedia = null): string
    {
        if ($customMedia && $this->hasGeneratedPack($customMedia)) {
            return 'image/x-icon';
        }

        return $customMedia?->mime_type ?: 'image/x-icon';
    }

    /** Tạo bộ favicon tĩnh từ media đang được chọn trong SiteAsset. */
    public function sync(?Media $customMedia): void
    {
        if (! $customMedia) {
            return;
        }

        $source = $this->sourceContents($customMedia);
        $directory = public_path(self::GENERATED_DIRECTORY);
        $this->ensureDirectory($directory);

        $pngs = [];
        $extension = strtolower(pathinfo($customMedia->file_name, PATHINFO_EXTENSION));

        foreach (self::PNG_FILES as $filename => $size) {
            $pngs[$size] = $this->renderPng($source, $extension, $size);
            $this->writeFile($directory.DIRECTORY_SEPARATOR.$filename, $pngs[$size]);
        }

        $this->writeFile($directory.DIRECTORY_SEPARATOR.'favicon.svg', $this->svgFromPng($pngs[512]));
        $this->writeFile(
            $directory.DIRECTORY_SEPARATOR.'favicon.ico',
            $this->icoFromPngs([16 => $pngs[16], 32 => $pngs[32], 48 => $pngs[48]]),
        );
        $this->writeFile($directory.DIRECTORY_SEPARATOR.'site.webmanifest', $this->manifest());
        $this->writeFile($directory.DIRECTORY_SEPARATOR.'.source', $this->sourceSignature($customMedia));
    }

    private function sourceContents(Media $media): string
    {
        $path = $media->getPath();
        if (is_file($path)) {
            $source = file_get_contents($path);

            if ($source !== false) {
                return $source;
            }
        }

        $storage = Storage::disk($media->disk);
        $relativePath = $media->getPathRelativeToRoot();

        if (! $storage->exists($relativePath)) {
            throw new RuntimeException('Không tìm thấy file favicon đã chọn trong kho media.');
        }

        return (string) $storage->get($relativePath);
    }

    private function hasGeneratedPack(Media $customMedia): bool
    {
        $directory = public_path(self::GENERATED_DIRECTORY);
        $marker = $directory.DIRECTORY_SEPARATOR.'.source';

        if (! is_file($marker) || trim((string) file_get_contents($marker)) !== $this->sourceSignature($customMedia)) {
            return false;
        }

        foreach (array_keys(self::PNG_FILES) as $filename) {
            if (! is_file($directory.DIRECTORY_SEPARATOR.$filename)) {
                return false;
            }
        }

        return is_file($directory.DIRECTORY_SEPARATOR.'favicon.svg')
            && is_file($directory.DIRECTORY_SEPARATOR.'favicon.ico')
            && is_file($directory.DIRECTORY_SEPARATOR.'site.webmanifest');
    }

    private function sourceSignature(Media $media): string
    {
        return implode('|', [
            (string) $media->getKey(),
            (string) ($media->updated_at?->getTimestamp() ?? 0),
            $media->disk,
            $media->getPathRelativeToRoot(),
        ]);
    }

    private function renderPng(string $source, string $extension, int $size): string
    {
        if (! class_exists(Imagick::class)) {
            throw new RuntimeException('Để xử lý favicon cần bật PHP Imagick trên máy chủ.');
        }

        $image = new Imagick;
        $canvas = new Imagick;

        try {
            if ($extension === 'svg') {
                $source = Curator::sanitizeSvg($source);
            }

            $image->setBackgroundColor(new ImagickPixel('transparent'));
            $image->readImageBlob($source);
            $image->setIteratorIndex(0);
            $image->setImageFormat('png');
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
            $image->thumbnailImage($size, $size, true, true);
            $image->setImagePage(0, 0, 0, 0);

            $canvas->newImage($size, $size, new ImagickPixel('transparent'), 'png');
            $x = (int) floor(($size - $image->getImageWidth()) / 2);
            $y = (int) floor(($size - $image->getImageHeight()) / 2);
            $canvas->compositeImage($image, Imagick::COMPOSITE_OVER, $x, $y);
            $canvas->setImageFormat('png');

            return $canvas->getImageBlob();
        } finally {
            $image->clear();
            $image->destroy();
            $canvas->clear();
            $canvas->destroy();
        }
    }

    private function svgFromPng(string $png): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">'
            .'<image href="data:image/png;base64,'.base64_encode($png).'" width="512" height="512" preserveAspectRatio="none"/>'
            .'</svg>';
    }

    /** @param array<int, string> $pngs */
    private function icoFromPngs(array $pngs): string
    {
        $header = pack('vvv', 0, 1, count($pngs));
        $entries = '';
        $images = '';
        $offset = 6 + (16 * count($pngs));

        foreach ($pngs as $size => $png) {
            $entries .= pack(
                'CCCCvvVV',
                $size >= 256 ? 0 : $size,
                $size >= 256 ? 0 : $size,
                0,
                0,
                1,
                32,
                strlen($png),
                $offset,
            );
            $images .= $png;
            $offset += strlen($png);
        }

        return $header.$entries.$images;
    }

    private function manifest(): string
    {
        return (string) json_encode([
            'name' => config('app.name', 'THT Media VN'),
            'short_name' => 'THT Media VN',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#10233e',
            'theme_color' => '#ee6b2d',
            'icons' => [
                ['src' => 'android-chrome-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => 'android-chrome-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục favicon tĩnh trong public.');
        }
    }

    private function writeFile(string $path, string $contents): void
    {
        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            throw new RuntimeException("Không thể ghi file favicon tĩnh: {$path}");
        }
    }
}
