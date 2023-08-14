<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['store', 'category', 'content_' . config('app.locale')]);
    }

    /**
     * @throws Exception
     */
    protected function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(function (Product $record) use ($locale) {
                        $name = $record->{"content_$locale"}?->name;

                        if ($name) {
                            return $name;
                        }

                        return $record->{"content_{$record->store->fallback_locale}"}?->name;
                    })
                    ->label('Name'),
                TextColumn::make("category.name.$locale")
                    ->label('Category'),
                IconColumn::make('is_available')->boolean(),
                TextColumn::make('store.name'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [];
    }
}
