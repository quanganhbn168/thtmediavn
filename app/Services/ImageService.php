<?php

namespace App\Services;

use App\Settings\MediaSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Image\Image;

class ImageService
{
    public const SAFE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'ico', 'pdf', 'doc', 'docx', 'mp4', 'webm', 'mov',
    ];

    private const MIME_TYPES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
        'avif' => ['image/avif'],
        'ico' => ['image/x-icon', 'image/vnd.microsoft.icon', 'application/octet-stream'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'mp4' => ['video/mp4', 'application/mp4'],
        'webm' => ['video/webm'],
        'mov' => ['video/quicktime'],
    ];

    protected MediaSettings $mediaSettings;

    public function __construct(MediaSettings $mediaSettings)
    {
        $this->mediaSettings = $mediaSettings;
    }

    /**
     * Xác thực tệp tin tải lên dựa trên cấu hình MediaSettings.
     * Trả về null nếu hợp lệ, hoặc chuỗi thông báo lỗi nếu không hợp lệ.
     */
    public function validateFile(UploadedFile $file, bool $onlyImages = false): ?string
    {
        if (! $file->isValid()) {
            return 'Tệp tin không hợp lệ hoặc đã bị hỏng.';
        }

        // Lấy danh sách định dạng cho phép từ cấu hình
        $allowedStr = $this->mediaSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,webm,mov';
        $allowedExtensions = array_values(array_intersect(
            array_map('trim', explode(',', strtolower($allowedStr))),
            self::SAFE_EXTENSIONS,
        ));

        if ($onlyImages) {
            // Chỉ lọc các định dạng ảnh phổ biến
            $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'ico'];
            $allowedExtensions = array_intersect($allowedExtensions, $imageExts);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return 'Định dạng tệp tin không được phép. Định dạng cho phép: '.implode(', ', $allowedExtensions);
        }

        $mimeType = strtolower((string) $file->getMimeType());
        if (! in_array($mimeType, self::MIME_TYPES[$extension] ?? [], true)) {
            return 'Nội dung tệp không khớp với định dạng '.$extension.'.';
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'ico'];
        if (in_array($extension, $imageExtensions, true) && @getimagesize($file->getPathname()) === false) {
            return 'Tệp tải lên không chứa dữ liệu hình ảnh hợp lệ.';
        }

        // Kiểm tra dung lượng (MB đổi sang KB)
        $maxSizeMB = $this->mediaSettings->media_max_size ?? 10;
        $maxSizeKB = $maxSizeMB * 1024;
        $fileSizeKB = $file->getSize() / 1024;

        if ($fileSizeKB > $maxSizeKB) {
            return 'Dung lượng tệp tin vượt quá giới hạn cho phép: '.$maxSizeMB.'MB.';
        }

        return null;
    }

    /**
     * Dọn các tệp tải lên tạm đã quá hạn mà không làm gián đoạn request hiện tại.
     */
    public function cleanupTemporaryFiles(int $olderThanSeconds = 21600): void
    {
        $directory = public_path('uploads/tmp');

        if (! File::isDirectory($directory)) {
            return;
        }

        try {
            foreach (File::files($directory) as $file) {
                if (time() - File::lastModified($file) > $olderThanSeconds) {
                    File::delete($file);
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Tải lên và tối ưu hóa ảnh (xử lý kích thước, chất lượng nén và chuyển đổi WebP).
     * Trả về đường dẫn tương đối để lưu vào cơ sở dữ liệu.
     */
    public function uploadAndOptimize(UploadedFile $file, string $folder, array $options = []): string
    {
        $convertToWebp = $options['convert_to_webp'] ?? $this->mediaSettings->media_webp_conversion;
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        $quality = $options['quality'] ?? $this->mediaSettings->media_quality ?? 100;

        $originalExt = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(40);

        $destDir = public_path('uploads/'.$folder);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Các định dạng ảnh có thể xử lý bằng Spatie Image
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

        // Với file không phải ảnh thông thường (pdf, docx) hoặc các file đặc biệt (ico, svg, gif)
        if (! in_array($originalExt, $imageExtensions) || in_array($originalExt, ['ico', 'svg', 'gif'])) {
            $fullFilename = $filename.'.'.$originalExt;
            $file->move($destDir, $fullFilename);

            return 'uploads/'.$folder.'/'.$fullFilename;
        }

        // Xác định định dạng đích và phần mở rộng file
        if ($convertToWebp) {
            $fullFilename = $filename.'.webp';
            $destPath = $destDir.'/'.$fullFilename;
            $format = 'webp';
        } else {
            $fullFilename = $filename.'.'.$originalExt;
            $destPath = $destDir.'/'.$fullFilename;
            // Thư viện Spatie Image v3 nhận format 'jpeg' thay vì 'jpg'
            $format = $originalExt === 'jpg' ? 'jpeg' : $originalExt;
        }

        try {
            $image = Image::load($file->getPathname());

            if ($width) {
                $image->width($width);
            }
            if ($height) {
                $image->height($height);
            }

            // Gọi format và quality qua các tham số chuỗi/số hợp lệ của Spatie v3
            $image->quality($quality)
                ->format($format)
                ->save($destPath);

            return 'uploads/'.$folder.'/'.$fullFilename;
        } catch (\Exception $e) {
            // Dự phòng (fallback): Di chuyển tệp tin thô nếu thư viện gặp lỗi xử lý
            $fullFilename = $filename.'.'.$originalExt;
            $file->move($destDir, $fullFilename);

            return 'uploads/'.$folder.'/'.$fullFilename;
        }
    }

    /**
     * Di chuyển tệp tin tạm thời từ Dropzone sang thư mục lưu trữ lâu dài.
     * Trả về đường dẫn tương đối mới.
     */
    public function moveToPermanent(string $tempPath, string $folder): string
    {
        $fullTempPath = public_path($tempPath);
        if (! file_exists($fullTempPath)) {
            return $tempPath;
        }

        $filename = basename($tempPath);
        $destDir = public_path('uploads/'.$folder);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $newRelativePath = 'uploads/'.$folder.'/'.$filename;
        $fullDestPath = public_path($newRelativePath);

        rename($fullTempPath, $fullDestPath);

        return $newRelativePath;
    }
}
