<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\ReorderRecordsRequest;
use App\Http\Requests\Admin\ToggleFieldRequest;
use App\Http\Requests\Admin\UploadEditorImageRequest;
use App\Http\Requests\Admin\UploadTempMediaRequest;
use App\Services\BulkActionService;
use App\Services\ImageService;
use App\Services\ReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CommonController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly BulkActionService $bulkActionService,
        private readonly ReorderService $reorderService,
    ) {}

    /**
     * Xử lý các thao tác hàng loạt dùng chung đã được whitelist.
     */
    public function bulkAction(BulkActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $message = $this->bulkActionService->execute(
            $data['resource'],
            $data['action'],
            $data['ids'],
        );

        return back()->with('success', $message);
    }

    public function reorder(ReorderRecordsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->reorderService->execute($data['resource'], $data['items']);

        return response()->json(['message' => 'Đã cập nhật thứ tự hiển thị.']);
    }

    /**
     * Toggle giá trị của một trường boolean trong CSDL qua AJAX.
     */
    public function toggleField(ToggleFieldRequest $request)
    {
        $data = $request->validated();
        $modelName = $data['model'];
        $id = $data['id'];
        $field = $data['field'];

        $modelClass = 'App\\Models\\'.$modelName;
        if (! class_exists($modelClass)) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy model {$modelName} trên hệ thống.",
            ], 404);
        }

        $record = $modelClass::find($id);
        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi tương ứng.',
            ], 404);
        }

        $newValue = ! (bool) $record->{$field};
        $record->$field = $newValue;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!',
            'value' => $newValue,
        ]);
    }

    /**
     * Upload ảnh tạm thời từ Dropzone trước khi submit form chính.
     */
    public function uploadTempMedia(UploadTempMediaRequest $request)
    {
        $file = $request->file('file');

        // Xác thực tệp tin dựa trên MediaSettings thông qua ImageService
        $validationError = $this->imageService->validateFile($file);
        if ($validationError) {
            return response()->json([
                'success' => false,
                'message' => $validationError,
            ], 400);
        }

        $this->imageService->cleanupTemporaryFiles();

        try {
            $options = array_filter([
                'convert_to_webp' => $request->has('convert_to_webp') ? $request->boolean('convert_to_webp') : null,
                'width' => $request->integer('width') ?: null,
                'height' => $request->integer('height') ?: null,
            ], static fn ($value) => $value !== null);
            // Tối ưu hóa và chuyển đổi sang WebP (nếu bật) ngay trong thư mục tạm
            $path = $this->imageService->uploadAndOptimize($file, 'tmp', $options);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset($path),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý tệp. Vui lòng thử lại.',
            ], 500);
        }
    }

    /**
     * Upload ảnh từ editor TinyMCE
     */
    public function uploadEditorImage(UploadEditorImageRequest $request)
    {
        $file = $request->file('file');

        // Xác thực tệp tin ảnh dựa trên cấu hình MediaSettings
        $validationError = $this->imageService->validateFile($file, true);
        if ($validationError) {
            return response()->json(['error' => $validationError], 400);
        }

        try {
            // Lưu và tối ưu hóa ảnh editor sang thư mục uploads/editor
            $path = $this->imageService->uploadAndOptimize($file, 'editor');

            return response()->json([
                'location' => asset($path),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => 'Không thể lưu ảnh. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Lấy danh sách hình ảnh phương tiện để hiển thị trong Trình quản lý thư viện phương tiện TinyMCE.
     */
    public function listMedia(Request $request)
    {
        // 1. Lấy ảnh từ Spatie Media Library
        $spatieMedia = Media::orderBy('id', 'desc')->get();
        $mediaList = [];
        foreach ($spatieMedia as $media) {
            if ($media->mime_type && strpos($media->mime_type, 'image/') === 0) {
                $mediaList[] = [
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'size' => round($media->size / 1024, 1).' KB',
                    'created_at' => $media->created_at ? $media->created_at->format('Y-m-d H:i') : '',
                ];
            }
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];

        // 2. Lấy ảnh từ public/uploads/editor/ (ảnh TinyMCE)
        $editorDir = public_path('uploads/editor');
        $editorFiles = [];
        if (is_dir($editorDir)) {
            $files = File::files($editorDir);
            usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($extension, $imageExtensions)) {
                    $editorFiles[] = [
                        'name' => $filename,
                        'url' => asset('uploads/editor/'.$filename),
                        'size' => round($file->getSize() / 1024, 1).' KB',
                        'created_at' => date('Y-m-d H:i', filemtime($file)),
                    ];
                }
            }
        }

        // 3. Lấy ảnh từ public/uploads/settings/ (ảnh cài đặt logo, favicon, banner)
        $settingsDir = public_path('uploads/settings');
        $settingsFiles = [];
        if (is_dir($settingsDir)) {
            $files = File::files($settingsDir);
            usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($extension, $imageExtensions)) {
                    $settingsFiles[] = [
                        'name' => $filename,
                        'url' => asset('uploads/settings/'.$filename),
                        'size' => round($file->getSize() / 1024, 1).' KB',
                        'created_at' => date('Y-m-d H:i', filemtime($file)),
                    ];
                }
            }
        }

        return response()->json([
            'media_library' => $mediaList,
            'editor' => $editorFiles,
            'settings' => $settingsFiles,
        ]);
    }

}
