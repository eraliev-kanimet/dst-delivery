<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationLabel(): string
    {
        return __('common.categories');
    }

    public static function getPluralLabel(): string
    {
        return __('common.categories');
    }

    public static function getEloquentQuery(): Builder
    {
        return getQueryFilamentQuery(parent::getEloquentQuery());
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->hasRole('store_owner');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
