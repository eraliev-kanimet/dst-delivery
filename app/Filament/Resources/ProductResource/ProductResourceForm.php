<?php

namespace App\Filament\Resources\ProductResource;

use App\Helpers\FilamentHelper;
use Filament\Forms\Components\Tabs;
use Nuhel\FilamentCroppie\Components\Croppie;

final class ProductResourceForm
{
    public function __construct(
        protected FilamentHelper $helper,
        protected array          $locales,
    )
    {
    }

    public static function getForm(array $locales, array $categories, bool $edit = true): array
    {
        $self = new self(new FilamentHelper, $locales);

        return $self->form($edit, $categories);
    }

    protected function form(bool $edit, array $categories): array
    {
        $tabs = [
            $this->helper->tab('Basic', [
                $this->helper->grid([
                    $this->helper->select('category_id')
                        ->label('Category')
                        ->required()
                        ->options($categories)
                        ->searchable()
                        ->columnSpan(2),
                    $this->helper->numericInput('sorted')
                        ->minValue(0),
                ], 3),
                $this->helper->tabsTextInput('name', $this->locales, true),
                $this->helper->tabsTextarea('description', $this->locales, true),
                $this->helper->toggle('is_available')
                    ->default(true),
            ]),
            $this->helper->tab('Attributes', [
                $this->helper->repeater('attributes', [
                    $this->helper->tabsTextInput('name', $this->locales, true),
                    $this->attributesTab(true),
                ])
                    ->label('')
                    ->required()
            ]),
        ];

        if ($edit) {
            $tabs[] = $this->helper->tab('Selections', [
                $this->helper->repeater('selections', [
                    $this->helper->grid([
                        $this->helper->numericInput('quantity')
                            ->minValue(0)
                            ->required(),
                        $this->helper->numericInput('price')
                            ->minValue(0)
                            ->required(),
                    ]),
                    $this->attributesTab(false),
                    $this->helper->toggle('is_available')
                        ->default(true),
                ])
                    ->relationship('selections')
                    ->required()
                    ->label('')
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['attributes'] = removeEmptyElements($data['attributes']);

                        return $data;
                    })
            ]);
        }

        $tabs[] = $this->helper->tab('Images', [
            Croppie::make('images')
                ->imageResizeTargetHeight(600)
                ->imageResizeTargetWidth(600)
                ->multiple()
                ->required()
        ]);

        return [$this->helper->tabs($tabs)];
    }

    protected function attributesTab(bool $required): Tabs
    {
        $tabs = [];

        foreach (filterAvailableLocales($this->locales) as $locale => $name) {
            $tabs[] = $this->helper->tab($name, [
                $this->helper->keyValue("attributes.$locale")
                    ->label('')
                    ->keyLabel('Attribute')
                    ->valueLabel('Value')
                    ->required($required),
            ]);
        }

        return $this->helper->tabs($tabs);
    }
}

