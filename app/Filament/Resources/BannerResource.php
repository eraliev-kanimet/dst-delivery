<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Exception;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photograph';

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('store.name'),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\TextColumn::make('start_date')->date('Y-m-d'),
                Tables\Columns\TextColumn::make('end_date')->date('Y-m-d'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->id == $record->store->user_id;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->id == $record->store->user_id;
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
