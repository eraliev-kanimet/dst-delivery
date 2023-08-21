<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\Store;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    public function getTitle(): string
    {
        return __('common.stores');
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name')
                ->label(__('common.name')),
        ];

        if (Auth::user()->hasRole('admin')) {
            $columns[] = TextColumn::make('uuid')
                ->copyable()
                ->label('UUID');
            $columns[] = TextColumn::make('user.name')
                ->label(__('common.owner'));
        }

        $columns[] = TextColumn::make('updated_at')
            ->label(__('common.updated_at'));

        return $table
            ->columns($columns)
            ->actions([
                Action::make('create_product')
                    ->label(__('common.add_product'))
                    ->url(fn(Store $record) => route('filament.admin.resources.products.create', [
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
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('common.create_store')),
        ];
    }
}
