<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Helpers\FilamentHelper;
use App\Models\Product;
use App\Models\Selection;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'selections';

    public Model|Product $ownerRecord;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('common.selections');
    }

    public static function getPluralRecordLabel(): string
    {
        return __('common.selections');
    }

    public static function getModelLabel(): ?string
    {
        return __('common.selections');
    }

    public array $attr = [];
    public array $locales = [];

    public function form(Form $form): Form
    {
        $helper = new FilamentHelper;
        $resourceForm = new ProductResourceForm($helper);

        if (!count($this->attr)) {
            $this->locales = $this->ownerRecord->store->locales;

            $locale = config('app.locale');
            $fallback_locale = $this->ownerRecord->store->fallback_locale;

            $attributes = [];

            foreach ($this->ownerRecord->store->attr as $item) {
                $attributes[$item->id] = $item->name[$locale] ?? $item->name[$fallback_locale];
            }

            $this->attr = $attributes;
        }

        $resourceForm->setLocales($this->locales);
        $resourceForm->setAttributes($this->attr);

        return $form->schema([
            $helper->tabs([
                $helper->tab(__('common.basic'), [
                    $helper->grid([
                        $helper->input('quantity')
                            ->label(__('common.quantity'))
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        $helper->input('price')
                            ->label(__('common.price'))
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                    ]),
                    $helper->toggle('is_available')
                        ->label(__('common.is_available'))
                        ->default(true),
                ]),
                $helper->tab(__('common.properties'), [
                    $resourceForm->attributesRepeater(),
                ]),
                $helper->tab(__('common.images'), [
                    $helper->image('images')
                        ->label(__('common.images'))
                        ->multiple()
                        ->imageEditor(),
                ]),
            ]),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $locale = config('app.locale');
        $fallback_locale = $this->ownerRecord->store->fallback_locale;

        return $table
            ->columns([
                TextColumn::make('properties')
                    ->label(__('common.properties'))
                    ->state(function (Selection $selection) use ($locale, $fallback_locale) {
                        $string = '';

                        foreach ($selection->attr->slice(0, 10) as $attribute) {
                            $key = $attribute->attrKey->name[$locale] ?? $attribute->attrKey->name[$fallback_locale];

                            if ($attribute->attrKey->translatable) {
                                $value = $attribute->value[$locale] ?? $attribute->value[$fallback_locale];
                            } else {
                                $value = $attribute->value['default'];
                            }

                            $string .= $key . ': ' . $value . ', ';
                        }

                        return trim($string, ', ');
                    }),
                TextColumn::make('price')
                    ->label(__('common.price')),
                TextColumn::make('quantity')
                    ->label(__('common.quantity')),
                IconColumn::make('is_available')
                    ->label(__('common.is_available'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('6xl'),
            ])
            ->actions([
                EditAction::make()->modalWidth('6xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make()->modalWidth('6xl'),
            ]);
    }
}
