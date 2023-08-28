<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('common.orders');
    }

    public array|Collection $stores = [];

    public function mount(): void
    {
        $this->stores = getQueryFilamentStore(Store::query())->pluck('name', 'id');

        parent::mount();
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['store', 'customer']);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $statuses = OrderStatus::getSelect();

        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('store.name')
                    ->label(__('common.store_name')),
                TextColumn::make('customer.phone')
                    ->label(__('common.customer_phone_number'))
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('common.total')),
                TextColumn::make('status')
                    ->label(__('common.status'))
                    ->formatStateUsing(fn(Order $order) => $statuses[$order->status])
                    ->color(function (Order $record) {
                        return match ($record->status) {
                            0 => 'warning',
                            1, 2, 4 => 'secondary',
                            5 => 'primary',
                            7 => 'danger',
                            default => 'success'
                        };
                    }),
                TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('common.status'))
                    ->options(OrderStatus::getSelect()),
                SelectFilter::make('store')
                    ->label(__('common.store'))
                    ->attribute('store_id')
                    ->options($this->stores),
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
        return [
            CreateAction::make()
                ->label(__('common.create_order')),
        ];
    }
}
