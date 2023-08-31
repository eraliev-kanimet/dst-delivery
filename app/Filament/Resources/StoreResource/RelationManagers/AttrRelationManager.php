<?php

namespace App\Filament\Resources\StoreResource\RelationManagers;

use App\Helpers\FilamentHelper;
use App\Models\AttrKey;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttrRelationManager extends RelationManager
{
    protected static string $relationship = 'attr';

    public Model|Store $ownerRecord;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('common.attributes_label');
    }

    public static function getModelLabel(): ?string
    {
        return __('common.attribute');
    }

    public function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        return $form
            ->schema([
                $helper->input('slug')
                    ->regex('/^[a-zA-Z]+$/')
                    ->required(),
                $helper->tabsInput(
                    'name',
                    $this->ownerRecord->locales,
                    true,
                    __('common.name')
                ),
                $helper->toggle('translatable')
                    ->label(__('common.translatable'))
                    ->default(true),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->state(fn(AttrKey $record) => $record->name[$locale] ?? $record->name[$this->ownerRecord->fallback_locale])
                    ->label(__('common.name')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
