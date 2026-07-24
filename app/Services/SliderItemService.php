<?php

namespace App\Services;

use App\Models\SliderItem;

class SliderItemService
{
    public function __construct(private readonly MediaService $mediaService) {}

    /**
     * Tạo mới ảnh slide
     */
    public function create(array $data): SliderItem
    {
        $item = SliderItem::create($this->payload($data, true));
        $this->mediaService->syncSingle($item, 'slide_image', $data['image'] ?? null);

        return $item;
    }

    /**
     * Cập nhật ảnh slide
     */
    public function update(SliderItem $item, array $data): void
    {
        $item->update($this->payload($data));
        $this->mediaService->syncSingle(
            $item,
            'slide_image',
            $data['image'] ?? null,
            (bool) ($data['image_remove'] ?? false),
        );
    }

    /**
     * Xóa ảnh slide
     */
    public function delete(SliderItem $item): void
    {
        $item->clearMediaCollection('slide_image');
        $item->delete();
    }

    private function payload(array $data, bool $includeSlider = false): array
    {
        $payload = [
            'title' => $data['title'] ?? [],
            'sub_title' => $data['sub_title'] ?? [],
            'buttons' => [
                [
                    'text' => $data['button_text_1'] ?? [],
                    'link' => $data['button_link_1'] ?? null,
                ],
                [
                    'text' => $data['button_text_2'] ?? [],
                    'link' => $data['button_link_2'] ?? null,
                ],
            ],
            'sort_order' => (int) $data['sort_order'],
            'is_active' => (bool) $data['is_active'],
        ];

        if ($includeSlider) {
            $payload['slider_id'] = $data['slider_id'];
        }

        return $payload;
    }

}
