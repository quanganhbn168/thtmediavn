<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Components\SlugInput;
use App\Models\PricingPlan;
use App\Models\Service;
use App\Models\ServiceCategory;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])
                ->columnSpanFull()
                ->schema([
                    Group::make([
                        Section::make('Thông tin dịch vụ')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('name.vi')
                                    ->label('Tên dịch vụ')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, $set, $get): void {
                                        if ((bool) $get('slug_editable')) {
                                            return;
                                        }

                                        $set('slug', Str::slug((string) $state));
                                    }),
                                Hidden::make('slug_editable')
                                    ->default(false)
                                    ->dehydrated(false),
                                SlugInput::make('slug'),
                                TextInput::make('summary.vi')
                                    ->label('Mô tả ngắn')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                RichEditor::make('intro.vi')
                                    ->label('Giới thiệu chi tiết')
                                    ->columnSpanFull(),
                                Textarea::make('problems.vi')
                                    ->label('Bài toán khách hàng')
                                    ->rows(5),
                                Textarea::make('audiences.vi')
                                    ->label('Đối tượng phù hợp')
                                    ->rows(5),
                                Textarea::make('work_items.vi')
                                    ->label('Hạng mục triển khai')
                                    ->rows(5),
                                Textarea::make('deliverables.vi')
                                    ->label('Đầu ra bàn giao')
                                    ->rows(5),
                                Textarea::make('benefits.vi')
                                    ->label('Lợi ích')
                                    ->rows(5),
                                Textarea::make('process_steps.vi')
                                    ->label('Các bước quy trình')
                                    ->rows(5),
                                Textarea::make('faqs.vi')
                                    ->label('Câu hỏi thường gặp')
                                    ->rows(5),
                            ])
                            ->columns(2),
                        Section::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('seo_title.vi')
                                    ->label('SEO title')
                                    ->maxLength(255),
                                Textarea::make('seo_description.vi')
                                    ->label('SEO description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                CuratorPicker::make('share_image_id')
                                    ->label('Ảnh chia sẻ')
                                    ->relationship('shareImage', 'id'),
                            ])
                            ->columns(1),
                    ])
                        ->columnSpan(['lg' => 2]),
                    Group::make([
                        Section::make('Hình ảnh')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                CuratorPicker::make('thumbnail_id')
                                    ->label('Ảnh đại diện')
                                    ->relationship('thumbnail', 'id'),
                                CuratorPicker::make('banner_id')
                                    ->label('Ảnh banner')
                                    ->relationship('banner', 'id'),
                            ])
                            ->columns(1),
                        Section::make('Phân loại và liên kết')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Select::make('service_category_id')
                                    ->label('Danh mục dịch vụ')
                                    ->options(fn (): array => ServiceCategory::query()
                                        ->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (ServiceCategory $category): array => [
                                            $category->getKey() => $category->getTranslation('name', 'vi'),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('related_service_ids')
                                    ->label('Dịch vụ liên quan')
                                    ->options(fn (): array => Service::query()
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (Service $service): array => [
                                            $service->getKey() => $service->getTranslation('name', 'vi'),
                                        ])
                                        ->all())
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                                Select::make('pricing_plan_ids')
                                    ->label('Bảng giá áp dụng')
                                    ->options(fn (): array => PricingPlan::query()
                                        ->orderByDesc('is_featured')
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (PricingPlan $plan): array => [
                                            $plan->getKey() => $plan->name.' — '.$plan->display_price.($plan->is_active ? '' : ' (đang ẩn)'),
                                        ])
                                        ->all())
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('video_url')
                                    ->label('Video giới thiệu')
                                    ->url()
                                    ->maxLength(2048),
                                TextInput::make('sort_order')
                                    ->label('Thứ tự')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(1),
                        Section::make('Trạng thái')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Đang hiển thị')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Nổi bật')
                                    ->default(false),
                            ])
                            ->columns(1),
                    ])
                        ->columnSpan(['lg' => 1]),
                ]),
        ]);
    }
}
