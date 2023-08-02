<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Exception;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-grid';

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('admin');
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
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->hidden(fn(Model $record) => $record->category?->category)
                    ->url(fn(Model $record) => route('filament.resources.categories.index', ['category_id' => $record->id])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
