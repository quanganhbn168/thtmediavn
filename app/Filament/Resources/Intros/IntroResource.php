<?php

namespace App\Filament\Resources\Intros;

use App\Filament\Resources\Intros\Pages\CreateIntro;
use App\Filament\Resources\Intros\Pages\EditIntro;
use App\Filament\Resources\Intros\Pages\ListIntros;
use App\Filament\Resources\Intros\Schemas\IntroForm;
use App\Filament\Resources\Intros\Tables\IntrosTable;
use App\Models\Intro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IntroResource extends Resource
{
    protected static ?string $model = Intro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Bài giới thiệu';

    protected static ?string $modelLabel = 'bài giới thiệu';

    protected static ?string $pluralModelLabel = 'Bài giới thiệu';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung website';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return IntroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntrosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntros::route('/'),
            'create' => CreateIntro::route('/create'),
            'edit' => EditIntro::route('/{record}/edit'),
        ];
    }
}
