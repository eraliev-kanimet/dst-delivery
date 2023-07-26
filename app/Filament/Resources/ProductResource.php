<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Exception;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-list';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery()->with(['store', 'category']);
        }

        return parent::getEloquentQuery()
            ->with(['store', 'category'])
            ->whereRelation('store', 'user_id', $user->id);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name.$locale"),
                Tables\Columns\TextColumn::make("category.name.$locale")
                    ->label('Category'),
                Tables\Columns\TextColumn::make('store.name'),
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
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
