<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    public static function getNavigationLabel(): string
    {
        return __('common.banners');
    }

    public static function getPluralLabel(): string
    {
        return __('common.banners');
    }

    protected static ?string $navigationIcon = 'heroicon-o-film';

    public static function getEloquentQuery(): Builder
    {
        return getQueryFilamentQuery(parent::getEloquentQuery());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
