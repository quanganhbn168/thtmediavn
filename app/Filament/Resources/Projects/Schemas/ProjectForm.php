<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Components\SlugInput;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\ProjectCategory;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
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

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])
                ->columnSpanFull()
                ->schema([
                    Group::make([
                        Section::make('Thông tin dự án')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('name.vi')
                                    ->label('Tên dự án')
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
                                RichEditor::make('context.vi')
                                    ->label('Bối cảnh')
                                    ->columnSpanFull(),
                                RichEditor::make('solution.vi')
                                    ->label('Giải pháp')
                                    ->columnSpanFull(),
                                Textarea::make('work_items.vi')
                                    ->label('Hạng mục triển khai')
                                    ->rows(6),
                                Textarea::make('results.vi')
                                    ->label('Kết quả')
                                    ->rows(6),
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
                                CuratorPicker::make('cover_id')
                                    ->label('Ảnh cover')
                                    ->relationship('cover', 'id'),
                                CuratorPicker::make('gallery_media')
                                    ->label('Thư viện ảnh')
                                    ->multiple()
                                    ->relationship('galleryMedia', 'id')
                                    ->orderColumn('sort_order'),
                            ])
                            ->columns(1),
                        Section::make('Phân loại và liên kết')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Select::make('client_id')
                                    ->label('Khách hàng')
                                    ->options(fn (): array => Client::query()
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (Client $client): array => [
                                            $client->getKey() => $client->getTranslation('name', 'vi'),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('project_category_id')
                                    ->label('Danh mục dự án')
                                    ->options(fn (): array => ProjectCategory::query()
                                        ->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (ProjectCategory $category): array => [
                                            $category->getKey() => $category->getTranslation('name', 'vi'),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('service_ids')
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
                                TextInput::make('industry')
                                    ->label('Lĩnh vực')
                                    ->maxLength(255),
                                TextInput::make('completed_year')
                                    ->label('Năm hoàn thành')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2200),
                                TextInput::make('video_url')
                                    ->label('Video dự án')
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
                        Section::make('Xuất bản')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Đang hiển thị')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Nổi bật')
                                    ->default(false),
                                DateTimePicker::make('published_at')
                                    ->label('Xuất bản từ')
                                    ->native(false)
                                    ->seconds(false),
                            ])
                            ->columns(1),
                    ])
                        ->columnSpan(['lg' => 1]),
                ]),
        ]);
    }
}
