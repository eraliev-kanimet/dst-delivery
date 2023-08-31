<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
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
    public function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                TextColumn::make('name')
                    ->state(function (Product $record) use ($locale) {
                        $name = $record->{"content_$locale"}?->name;

                        if ($name) {
                            return $name;
                        }

                        return $record->{"content_{$record->store->fallback_locale}"}?->name;
                    })
                    ->label(__('common.name')),
                TextColumn::make("category.name.$locale")
                    ->label(__('common.category')),
                IconColumn::make('is_available')
                    ->label(__('common.is_available'))
                    ->boolean(),
                TextColumn::make('store.name')
                    ->label(__('common.store_name')),
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
    protected function getHeaderActions(): array
    {
        $stores = [];

        foreach (getQueryFilamentStore() as $id => $name) {
            $stores[] = Action::make('create_product_' . $id)
                ->label($name)
                ->url(route('filament.admin.resources.products.create', [
                    'store_id' => $id
                ]));
        }

        return [
            ActionGroup::make($stores)
                ->icon('heroicon-o-building-storefront')
                ->label(__('common.create_product'))
                ->button()
        ];
    }
}
