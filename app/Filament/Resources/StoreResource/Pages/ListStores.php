<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\Store;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user');
    }

    /**
     * @throws Exception
     */
    protected function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name'),
        ];

        if (Auth::user()->hasRole('admin')) {
            $columns[] = TextColumn::make('uuid')
                ->copyable();
            $columns[] = TextColumn::make('user.name');
        }

        $columns[] = TextColumn::make('updated_at');

        return $table
            ->columns($columns)
            ->actions([
                Action::make('Add product')
                    ->url(fn(Store $record) => route('filament.resources.products.create', [
                        'store_id' => $record->id
                    ]))
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->color('success'),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
