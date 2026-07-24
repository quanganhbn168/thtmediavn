<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class HttpClient
{
    public function __construct(
        private readonly string $userAgent,
        private readonly int $timeoutSeconds = 30,
        private readonly int $retryTimes = 3,
        private readonly int $delayMs = 1000,
    ) {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('PHP extension cURL chưa được bật.');
        }
    }

    public function get(string $url): string
    {
        $lastError = 'Unknown error';

        for ($attempt = 1; $attempt <= $this->retryTimes; $attempt++) {
            if ($this->delayMs > 0) {
                usleep($this->delayMs * 1000);
            }

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => $this->timeoutSeconds,
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8',
                    'Accept-Language: vi-VN,vi;q=0.9,en;q=0.7',
                    'Cache-Control: no-cache',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if (is_string($body) && $body !== '' && $status >= 200 && $status < 400) {
                return $body;
            }

            $lastError = sprintf(
                'HTTP %d; cURL: %s; lần thử %d/%d',
                $status,
                $error ?: 'không có nội dung',
                $attempt,
                $this->retryTimes
            );

            sleep($attempt);
        }

        throw new RuntimeException("Không tải được {$url}. {$lastError}");
    }

    public function download(string $url, string $destination): void
    {
        $directory = dirname($destination);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Không tạo được thư mục {$directory}");
        }

        $tempFile = $destination . '.part';
        $fp = fopen($tempFile, 'wb');

        if ($fp === false) {
            throw new RuntimeException("Không mở được file tạm {$tempFile}");
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => max($this->timeoutSeconds, 60),
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => [
                'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Referer: https://mtd-global.com/',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($ok !== true || $status < 200 || $status >= 400) {
            @unlink($tempFile);
            throw new RuntimeException(
                "Tải ảnh thất bại HTTP {$status}: {$url}. {$error}"
            );
        }

        if ($contentType !== '' && !str_starts_with(strtolower($contentType), 'image/')) {
            @unlink($tempFile);
            throw new RuntimeException("URL không trả về ảnh: {$url} ({$contentType})");
        }

        if (!rename($tempFile, $destination)) {
            @unlink($tempFile);
            throw new RuntimeException("Không lưu được ảnh {$destination}");
        }
    }
}
