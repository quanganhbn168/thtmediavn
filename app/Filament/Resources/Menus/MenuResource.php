<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Models\Menu;
use App\Support\Menus\MenuSources;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static ?string $navigationLabel = 'Quản lý menu';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Cài đặt website';
    }

    public static function getModelLabel(): string
    {
        return 'menu';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Quản lý menu';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Section::make('Thêm vào menu')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->description('Chọn nội dung có sẵn ở hệ thống hoặc thêm một liên kết riêng.')
                    ->schema([
                        ViewField::make('menu_source_picker')
                            ->label(null)
                            ->view('filament.resources.menus.menu-source-picker')
                            ->viewData(fn ($livewire): array => [
                                'sourceGroups' => self::sourceGroups($livewire->menuSourceSearch ?? ''),
                            ])
                            ->dehydrated(false),
                    ])
                    ->columnSpan(1),
                Group::make([
                    Section::make('Thông tin menu')
                        ->icon(Heroicon::OutlinedBars3)
                        ->schema([
                            TextInput::make('name')->formatStateUsing(fn (mixed $state): string => is_array($state) ? (string) ($state[app()->getLocale()] ?? $state['vi'] ?? '') : (string) $state)
                                ->label('Tên menu')
                                ->required()
                                ->maxLength(255),
                            Select::make('location')
                                ->label('Vị trí hiển thị')
                                ->helperText('Chỉ chọn Header hoặc Footer.')
                                ->options([
                                    'header' => 'Header',
                                    'footer' => 'Footer',
                                ])
                                ->required()
                                ->native(false)
                                ->rule('in:header,footer'),
                            Toggle::make('is_active')
                                ->label('Kích hoạt menu')
                                ->default(true),
                        ])
                        ->columns(2),
                    Section::make('Cấu trúc menu')
                        ->icon(Heroicon::OutlinedListBullet)
                        ->description('Kéo thả để sắp xếp; dùng thao tác vào trong / ra ngoài để thay đổi cấp menu. Các mục đóng mặc định để dễ quản lý.')
                        ->schema([
                            Repeater::make('topLevelItems')
                                ->label('Danh sách menu item')
                                ->relationship()
                                ->defaultItems(0)
                                ->orderColumn('sort_order')
                                ->schema([
                                    ...self::menuItemFields(),
                                    Repeater::make('children')
                                        ->label('Menu item con')
                                        ->relationship()
                                        ->defaultItems(0)
                                        ->orderColumn('sort_order')
                                        ->schema([...self::menuItemFields(), self::descendantItems(3)])
                                        ->columns(1)
                                        ->addActionLabel('Thêm menu item con')
                                        ->reorderable()

                                        ->reorderableWithDragAndDrop()
                                        ->collapsible()
                                        ->collapsed()
                                        ->extraItemActions([
                                            Action::make('moveOutside')
                                                ->label('Ra ngoài một cấp')
                                                ->icon(Heroicon::ArrowLeft)
                                                ->action(function (array $arguments, Repeater $component): void {
                                                    self::moveItemOutside($component, (string) $arguments['item']);
                                                }),
                                        ])
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Menu item mới')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->addActionLabel('Thêm menu item')
                                ->reorderable()

                                ->reorderableWithDragAndDrop()
                                ->collapsible()
                                ->collapsed()
                                ->cloneable()
                                ->extraItemActions([
                                    Action::make('moveInside')
                                        ->label('Vào trong làm con')
                                        ->icon(Heroicon::ArrowRight)
                                        ->action(function (array $arguments, Repeater $component): void {
                                            self::moveItemInside($component, (string) $arguments['item']);
                                        }),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Menu item mới')
                                ->columnSpanFull(),
                        ])
                        ->collapsible(false),
                ])->columnSpan(2),
            ]);
    }

    /** @return array<int, mixed> */
    private static function descendantItems(int $depth): Repeater
    {
        return Repeater::make('children')->label('Menu con')->relationship()->defaultItems(0)
            ->orderColumn('sort_order')->schema($depth > 1 ? [...self::menuItemFields(), self::descendantItems($depth - 1)] : self::menuItemFields())
            ->reorderableWithDragAndDrop()->collapsible()->collapsed()->addActionLabel('Thêm menu con')
            ->itemLabel(fn (array $state): string => (string) ($state['title'] ?? 'Menu mới'))
            ->columnSpanFull();
    }

    private static function menuItemFields(): array
    {
        return [
            TextInput::make('title')->formatStateUsing(fn (mixed $state): string => is_array($state) ? (string) ($state[app()->getLocale()] ?? $state['vi'] ?? '') : (string) $state)
                ->label('Nhãn hiển thị')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('url')->label('URL')->visible(fn (Get $get): bool => blank($get('linked_source_type')))->maxLength(2048)->regex('~^(?:https?://[^\s]+|/(?!/)[^\s]*|#[^\s]*)$~i'),
            Hidden::make('linked_source_id'),
            Select::make('target')
                ->label('Cách mở liên kết')
                ->options([
                    '_self' => 'Cùng tab',
                    '_blank' => 'Tab mới',
                ])
                ->default('_self')
                ->columnSpanFull(),
            Hidden::make('linked_source_type'),
            Hidden::make('route'),
            Hidden::make('icon'),
            Toggle::make('is_active')->label('Hiển thị')->default(true),
        ];
    }

    public static function sourceGroups(string $search = ''): array
    {
        return MenuSources::groups($search);
    }

    private static function moveItemInside(Repeater $component, string $itemKey): void
    {
        $state = $component->getRawState();

        if (! is_array($state) || ! array_key_exists($itemKey, $state)) {
            return;
        }

        $keys = array_keys($state);
        $index = array_search($itemKey, array_map('strval', $keys), true);

        if ($index === false || $index === 0) {
            return;
        }

        $previousKey = $keys[$index - 1];
        $item = $state[$itemKey];
        unset($state[$itemKey]);

        $children = is_array($state[$previousKey]['children'] ?? null)
            ? $state[$previousKey]['children']
            : [];
        $children[$itemKey] = $item;
        $state[$previousKey]['children'] = $children;

        $component->rawState($state);
        $component->callAfterStateUpdated();
        $component->partiallyRender();
    }

    private static function moveItemOutside(Repeater $component, string $itemKey): void
    {
        $parentRepeater = $component->getParentRepeater();
        $parentItem = $component->getParentRepeaterItem();

        if (! $parentRepeater || ! $parentItem) {
            return;
        }

        $parentKey = (string) $parentItem->getStatePath(isAbsolute: false);
        $state = $parentRepeater->getRawState();
        $children = $state[$parentKey]['children'] ?? null;

        if (! is_array($children) || ! array_key_exists($itemKey, $children)) {
            return;
        }

        $item = $children[$itemKey];
        unset($children[$itemKey]);
        $state[$parentKey]['children'] = $children;

        $newState = [];

        foreach ($state as $key => $data) {
            $newState[$key] = $data;

            if ((string) $key === $parentKey) {
                $newState[$itemKey] = $item;
            }
        }

        $parentRepeater->rawState($newState);
        $parentRepeater->callAfterStateUpdated();
        $parentRepeater->partiallyRender();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Menu')->searchable()->sortable(),
                TextColumn::make('location')->label('Vị trí')->badge()->searchable()->sortable(),
                TextColumn::make('items_count')->label('Menu item')->counts('items')->sortable(),
                ToggleColumn::make('is_active')->label('Kích hoạt'),
                TextColumn::make('updated_at')->label('Cập nhật')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'edit' => EditMenu::route('/{record}/edit'),
        ];
    }
}
