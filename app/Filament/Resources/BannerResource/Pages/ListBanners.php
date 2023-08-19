<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

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
                TextColumn::make('name'),
                TextColumn::make('store.name'),
                TextColumn::make('type')
                    ->formatStateUsing(function (Banner $banner) {
                        return Banner::$types[$banner->type];
                    }),
                IconColumn::make('active')->boolean(),
                TextColumn::make('start_date')->date('Y-m-d'),
                TextColumn::make('end_date')->date('Y-m-d'),
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
