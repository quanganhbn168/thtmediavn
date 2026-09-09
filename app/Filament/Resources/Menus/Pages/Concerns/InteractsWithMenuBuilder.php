<?php

namespace App\Filament\Resources\Menus\Pages\Concerns;

use App\Support\Menus\MenuSources;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

trait InteractsWithMenuBuilder
{
    public string $menuSourceSearch = '';

    public string $customMenuLabel = '';

    public string $customMenuUrl = '';

    public function addMenuItemFromSource(string $sourceKey): void
    {
        [$type, $sourceId] = array_pad(explode(':', $sourceKey, 2), 2, null);

        if (blank($type) || blank($sourceId)) {
            return;
        }

        $item = $this->sourceItemState($type, $sourceId);

        if ($item === null) {
            Notification::make()
                ->danger()
                ->title('Không tìm thấy nội dung')
                ->body('Mục này có thể đã bị xoá hoặc không còn được xuất bản.')
                ->send();

            return;
        }

        $this->appendMenuItem($item);

        Notification::make()
            ->success()
            ->title('Đã thêm mục vào menu')
            ->body('Nhấn Lưu thay đổi để ghi cấu trúc mới.')
            ->send();
    }

    public function addCustomMenuItem(): void
    {
        $label = trim($this->customMenuLabel);
        $url = trim($this->customMenuUrl);

        if ($label === '' || $url === '' || $url === '#') {
            Notification::make()
                ->danger()
                ->title('Thiếu thông tin liên kết')
                ->body('Vui lòng nhập nhãn hiển thị và URL hợp lệ.')
                ->send();

            return;
        }

        if (! preg_match('~^(?:https?://[^\s]+|/(?!/)[^\s]*)$~i', $url)) {
            Notification::make()
                ->danger()
                ->title('URL chưa hợp lệ')
                ->body('Dùng URL đầy đủ hoặc đường dẫn nội bộ bắt đầu bằng /.')
                ->send();

            return;
        }

        $this->appendMenuItem([
            'title' => $label,
            'linked_source_type' => null,
            'linked_source_id' => null,
            'url' => $url,
            'target' => '_self',
            'route' => null, 'is_active' => true,
            'children' => [],
        ]);

        $this->reset('customMenuLabel', 'customMenuUrl');

        Notification::make()
            ->success()
            ->title('Đã thêm link custom')
            ->body('Nhấn Lưu thay đổi để ghi cấu trúc mới.')
            ->send();
    }

    /** @return array<string, mixed> | null */
    private function sourceItemState(string $type, string $sourceId): ?array
    {
        return MenuSources::item($type, $sourceId);
    }

    /** @param array<string, mixed> $item */
    private function appendMenuItem(array $item): void
    {
        $component = $this->form->getComponent('topLevelItems', withHidden: true);
        $key = (string) Str::uuid();

        if (! $component instanceof Repeater) {
            $this->data['topLevelItems'][$key] = $item;

            return;
        }

        $state = $component->getRawState();
        $state[$key] = $item;
        $component->rawState($state);
        $component->callAfterStateUpdated();
        $component->partiallyRender();
    }
}
