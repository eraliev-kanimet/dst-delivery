<?php

namespace App\Filament\Resources\ProductResource;

use App\Helpers\FilamentFormHelper;
use App\Models\Category;
use App\Models\Store;
use Filament\Forms\Components\Tabs;
use Nuhel\FilamentCroppie\Components\Croppie;

final class ProductResourceForm
{
    public function __construct(
        protected FilamentFormHelper $helper,
        protected array              $locales,
    )
    {
    }

    public static function getForm(Store $store, bool $edit = true): array
    {
        $self = new self(new FilamentFormHelper, $store->locales);

        return $self->form($store, $edit);
    }

    protected function form(Store $store, bool $edit = true): array
    {
        $tabs = [
            $this->helper->tab('Basic', [
                $this->helper->grid([
                    $this->helper->select('category_id')
                        ->label('Category')
                        ->required()
                        ->options($this->categories($store->categories))
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

    protected function categories(array $categories): array
    {
        $locale = config('app.locale');

        $categories = Category::whereIn('id', $categories)
            ->with(['categories'])
            ->get();

        $array = [];

        foreach ($categories as $category) {
            $array = array_replace_recursive($array, $this->getCategoryArray($category, $locale));
        }

        return $array;
    }

    protected function getCategoryArray(Category $category, string $locale): array
    {
        $array[$category->id] = $category->name[$locale];

        foreach ($category->categories as $childCategory) {
            $array = array_replace_recursive($array, $this->getCategoryArray($childCategory, $locale));
        }

        return $array;
    }
}

