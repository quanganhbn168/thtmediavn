<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function syncSingle(HasMedia $model, string $collection, ?string $temporaryPath, bool $remove = false): ?Media
    {
        if ($remove) {
            $model->clearMediaCollection($collection);
            return null;
        }

        if (blank($temporaryPath)) {
            return null;
        }

        $fullPath = $this->validatedTemporaryPath($temporaryPath, $collection);
        return $model->addMedia($fullPath)->toMediaCollection($collection, 'public_media');
    }

    private function validatedTemporaryPath(string $path, string $field): string
    {
        $normalized = str_replace('\\', '/', $path);
        if (! str_starts_with($normalized, 'uploads/tmp/') || basename($normalized) !== substr($normalized, strlen('uploads/tmp/'))) {
            throw ValidationException::withMessages([$field => 'Đường dẫn tệp tạm không hợp lệ.']);
        }

        $root = realpath(public_path('uploads/tmp'));
        $fullPath = realpath(public_path($normalized));
        if ($root === false || $fullPath === false || ! File::isFile($fullPath)) {
            throw ValidationException::withMessages([$field => 'Tệp tạm không tồn tại hoặc đã hết hạn.']);
        }

        $root = rtrim(str_replace('\\', '/', $root), '/').'/';
        $candidate = str_replace('\\', '/', $fullPath);
        if (! str_starts_with($candidate, $root)) {
            throw ValidationException::withMessages([$field => 'Đường dẫn tệp tạm không hợp lệ.']);
        }

        return $fullPath;
    }
}
