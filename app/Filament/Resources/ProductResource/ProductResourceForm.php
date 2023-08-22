<?php

namespace App\Filament\Resources\ProductResource;

use App\Helpers\FilamentHelper;
use App\Service\ProductService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Get;
use Filament\Forms\Set;

final class ProductResourceForm
{
    protected array $categories = [];
    protected array $locales = [];

    protected bool $edit = true;
    protected bool $category_disabled = false;

    public function __construct(protected FilamentHelper $helper)
    {
    }

    public static function create(): ProductResourceForm
    {
        return new self(new FilamentHelper);
    }

    public function form(): array
    {
        $tabs = [
            $this->helper->tab(__('common.basic'), [
                $this->helper->grid([
                    $this->helper->select('category_id')
                        ->label(__('common.category'))
                        ->required()
                        ->disabled($this->category_disabled)
                        ->options($this->categories)
                        ->searchable()
                        ->columnSpan(2),
                    $this->helper->input('sorted')
                        ->label(__('common.sorted'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(10000),
                ], 3),
                $this->helper->tabsInput('name', $this->locales, true, __('common.name')),
                $this->helper->tabsTextarea('description', $this->locales, true, __('common.description')),
                $this->helper->grid([
                    $this->helper->toggle('is_available')
                        ->label(__('common.is_available'))
                        ->default(true),
                    $this->helper->radio('preview', [
                        2 => 'Normal',
                        1 => 'Large'
                    ])
                        ->label(__('common.preview'))->inline()->default(2),
                ])
            ]),
            $this->tabAttributes()
        ];

        if ($this->edit) {
            $tabs[] = $this->helper->tab(__('common.selections'), [
                $this->helper->repeater('selections', [
                    $this->helper->grid([
                        $this->helper->input('quantity')
                            ->label(__('common.quantity'))
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        $this->helper->input('price')
                            ->label(__('common.price'))
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        $this->helper->toggle('is_available')
                            ->label(__('common.is_available'))
                            ->default(true),
                    ]),
                    $this->helper->image('images')
                        ->label(__('common.images'))
                        ->multiple()
                        ->imageEditor(),
                    $this->attributesRepeater('properties'),
                ])
                    ->relationship('selections')
                    ->required()
                    ->label('')
                    ->addActionLabel(__('common.add_selection'))
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['properties'] = removeEmptyElements($data['properties']);

                        return $data;
                    })
            ]);
        }

        $tabs[] = $this->helper->tab(__('common.images'), [
            $this->helper->image('images')
                ->imageEditor()
                ->multiple()
                ->required()
        ]);

        return [$this->helper->tabs($tabs)];
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function setCategoryDisabled(bool $category_disabled): void
    {
        $this->category_disabled = $category_disabled;
    }

    public function setEdit(bool $edit): void
    {
        $this->edit = $edit;
    }

    protected function tabAttributes(): Tabs\Tab
    {
        $repeater = $this->attributesRepeater();

        if ($this->edit) {
            $repeater->relationship('productAttributes');
        }

        return $this->helper->tab(__('common.properties'), [$repeater]);
    }

    protected function attributesRepeater(string $model = 'productAttributes'): Repeater
    {
        $productService = ProductService::new();

        $schema = [
            $this->helper->select('attribute')
                ->label(__('common.attribute'))
                ->options($productService->getAttributesName())
                ->required()
                ->reactive(),
            Hidden::make('type'),
            $this->helper->tabsInput('value1', $this->locales, true, __('common.value'))
                ->visible(function (Get $get, Set $set) use ($productService) {
                    if ($productService->isAttributeType1($get('attribute'))) {
                        $set('type', 1);

                        return true;
                    }

                    return false;
                }),
            $this->helper->input('value2')
                ->visible(function (Get $get, Set $set) use ($productService) {
                    if ($productService->isAttributeType2($get('attribute'))) {
                        $set('type', 2);

                        return true;
                    }

                    return false;
                })->label(__('common.value'))
        ];

        return $this->helper->repeater($model, $schema)
            ->label('')
            ->required()
            ->addActionLabel(__('common.add_property'));
    }
}
