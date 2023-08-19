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
use Illuminate\Support\Facades\Auth;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public array|Collection $stores = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            $this->stores = Store::whereIn('id', $user->stores_permission)->pluck('name', 'id');
        } else if ($user->hasRole('store_owner')) {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        } else {
            $this->stores = Store::pluck('name', 'id');
        }

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
                TextColumn::make('store.name'),
                TextColumn::make('customer.phone')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('total'),
                TextColumn::make('status')
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
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::getSelect()),
                SelectFilter::make('store')
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
            CreateAction::make(),
        ];
    }
}
