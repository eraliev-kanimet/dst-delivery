<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
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
use Illuminate\Support\Facades\Auth;

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
    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            $stores = Store::whereIn('id', $user->stores_permission)->get(['id', 'name']);
        } else if ($user->hasRole('store_owner')) {
            $stores = Store::whereUserId($user->id)->get(['id', 'name']);
        } else {
            $stores = Store::get(['id', 'name']);
        }

        $stores = $stores->map(function (Store $store) {
            return Action::make('create_product_' . $store->id)
                ->label($store->name)
                ->url(route('filament.admin.resources.products.create', [
                    'store_id' => $store->id
                ]));
        })->toArray();

        return [
            ActionGroup::make($stores)
                ->icon('heroicon-o-building-storefront')
                ->label('Create a product')
                ->button()
        ];
    }
}
