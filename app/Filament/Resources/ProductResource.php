<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\SelectionsRelationManager;
use App\Models\Product;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    public static function getNavigationLabel(): string
    {
        return __('common.products');
    }

    public static function getPluralLabel(): string
    {
        return __('common.products');
    }

    public static function getEloquentQuery(): Builder
    {
        return getQueryFilamentQuery(parent::getEloquentQuery());
    }

    public static function getRelations(): array
    {
        return [
            SelectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
