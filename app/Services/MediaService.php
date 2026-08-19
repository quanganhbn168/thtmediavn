<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function syncSingle(HasMedia $model, string $collection, mixed $temporaryPath, bool $remove = false): ?Media
    {
        if ($remove) {
            $model->clearMediaCollection($collection);

            return null;
        }

        if (blank($temporaryPath)) {
            return null;
        }

        if ($temporaryPath instanceof UploadedFile) {
            return $model->addMedia($temporaryPath->getRealPath())->toMediaCollection($collection, 'public_media');
        }

        $fullPath = $this->validatedTemporaryPath($temporaryPath, $collection);

        return $model->addMedia($fullPath)->toMediaCollection($collection, 'public_media');
    }

    public function syncMultiple(HasMedia $model, string $collection, mixed $temporaryPaths, bool $remove = false): void
    {
        if ($remove) {
            $model->clearMediaCollection($collection);
        }

        $paths = is_array($temporaryPaths)
            ? collect($temporaryPaths)
            : collect(explode('|', (string) $temporaryPaths));

        $paths
            ->map(fn (mixed $path): mixed => is_string($path) ? trim($path) : $path)
            ->filter(fn (mixed $path): bool => $path instanceof UploadedFile || filled($path))
            ->each(function (mixed $path) use ($model, $collection): void {
                if ($path instanceof UploadedFile) {
                    $model->addMedia($path->getRealPath())->toMediaCollection($collection, 'public_media');

                    return;
                }

                $model->addMedia($this->validatedTemporaryPath($path, $collection))
                    ->toMediaCollection($collection, 'public_media');
            });
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
