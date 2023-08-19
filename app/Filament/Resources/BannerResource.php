<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            return parent::getEloquentQuery()->whereIn('store_id', $user->stores_permission);
        } else if ($user->hasRole('store_owner')) {
            return parent::getEloquentQuery()->whereRelation('store', 'user_id', $user->id);
        }

        return parent::getEloquentQuery();
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
