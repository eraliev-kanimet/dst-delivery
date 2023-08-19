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
            $this->helper->tab('Basic', [
                $this->helper->grid([
                    $this->helper->select('category_id')
                        ->label('Category')
                        ->required()
                        ->disabled($this->category_disabled)
                        ->options($this->categories)
                        ->searchable()
                        ->columnSpan(2),
                    $this->helper->input('sorted')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(10000),
                ], 3),
                $this->helper->tabsInput('name', $this->locales, true),
                $this->helper->tabsTextarea('description', $this->locales, true),
                $this->helper->grid([
                    $this->helper->toggle('is_available')
                        ->default(true),
                    $this->helper->radio('preview', [
                        2 => 'Normal',
                        1 => 'Large'
                    ])->inline()->default(2),
                ])
            ]),
            $this->tabAttributes()
        ];

        if ($this->edit) {
            $tabs[] = $this->helper->tab('Selections', [
                $this->helper->repeater('selections', [
                    $this->helper->grid([
                        $this->helper->input('quantity')
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        $this->helper->input('price')
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        $this->helper->toggle('is_available')
                            ->default(true),
                    ]),
                    $this->helper->image('images')
                        ->multiple()
                        ->imageEditor(),
                    $this->attributesRepeater('properties'),
                ])
                    ->relationship('selections')
                    ->required()
                    ->label('')
                    ->addActionLabel('Add selection')
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['properties'] = removeEmptyElements($data['properties']);

                        return $data;
                    })
            ]);
        }

        $tabs[] = $this->helper->tab('Images', [
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

        return $this->helper->tab('Attributes', [$repeater]);
    }

    protected function attributesRepeater(string $model = 'productAttributes'): Repeater
    {
        $productService = ProductService::new();

        $schema = [
            $this->helper->select('attribute')
                ->options($productService->getAttributesName())
                ->required()
                ->reactive()
        ];

        if ($this->edit) {
            $schema[] = Hidden::make('type');
            $schema[] = $this->helper->tabsInput('value1', $this->locales, true, 'Value')
                ->visible(function (Get $get, Set $set) use ($productService) {
                    if ($productService->isAttributeType1($get('attribute'))) {
                        $set('type', 1);

                        return true;
                    }

                    return false;
                });
            $schema[] = $this->helper->input('value2')
                ->visible(function (Get $get, Set $set) use ($productService) {
                    if ($productService->isAttributeType2($get('attribute'))) {
                        $set('type', 2);

                        return true;
                    }

                    return false;
                })->label('Value');
        } else {
            $schema[] = $this->helper->tabsInput('value1', $this->locales, true, 'Value')
                ->visible(fn(Get $get) => $productService->isAttributeType1($get('attribute')));
            $schema[] = $this->helper->input('value2')
                ->visible(fn(Get $get) => $productService->isAttributeType2($get('attribute')))
                ->label('Value');
        }

        return $this->helper->repeater($model, $schema)
            ->label('')
            ->required()
            ->addActionLabel('Add attribute');
    }
}
