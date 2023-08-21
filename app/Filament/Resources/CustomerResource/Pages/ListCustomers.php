<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function getTitle(): string
    {
        return __('common.customers');
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('store');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label(__('common.store_name')),
                TextColumn::make('phone')
                    ->label(__('common.customer_phone_number')),
                IconColumn::make('active')
                    ->label(__('common.active'))->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('common.updated_at')),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @throws Exception
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('common.create_customer')),
        ];
    }
}
