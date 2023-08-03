<?php

namespace App\Filament\Resources\ProductResource;

use App\Helpers\FilamentHelper;
use App\Service\ProductService;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Tabs;
use Nuhel\FilamentCroppie\Components\Croppie;

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
                        1 => 'Normal',
                        2 => 'Large'
                    ])->inline()->default(1),
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
                    ]),
                    $this->attributesTab(false),
                    $this->helper->toggle('is_available')
                        ->default(true),
                ])
                    ->relationship('selections')
                    ->required()
                    ->label('')
                    ->createItemButtonLabel('Add selection')
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['properties'] = removeEmptyElements($data['properties']);

                        return $data;
                    })
            ]);
        }

        $tabs[] = $this->helper->tab('Images', [
            Croppie::make('images')
                ->imageResizeTargetHeight(500)
                ->imageResizeTargetWidth(500)
                ->modalSize('xl')
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
                $this->helper->keyValue("properties.$locale")
                    ->label('')
                    ->keyLabel('Attribute')
                    ->valueLabel('Value')
                    ->required($required),
            ]);
        }

        return $this->helper->tabs($tabs);
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
                ->visible(function (Closure $get, Closure $set) use ($productService) {
                    if ($productService->isAttributeType1($get('attribute'))) {
                        $set('type', 1);

                        return true;
                    }

                    return false;
                });
            $schema[] = $this->helper->input('value2')
                ->visible(function (Closure $get, Closure $set) use ($productService) {
                    if ($productService->isAttributeType2($get('attribute'))) {
                        $set('type', 2);

                        return true;
                    }

                    return false;
                })->label('Value');
        } else {
            $schema[] = $this->helper->tabsInput('value1', $this->locales, true, 'Value')
                ->visible(fn(Closure $get) => $productService->isAttributeType1($get('attribute')));
            $schema[] = $this->helper->input('value2')
                ->visible(fn(Closure $get) => $productService->isAttributeType2($get('attribute')))
                ->label('Value');
        }

        $repeater = $this->helper->repeater('attributes', $schema)
            ->label('')
            ->required()
            ->createItemButtonLabel('Add attribute');

        if ($this->edit) {
            $repeater->relationship('productAttributes');
        }

        return $this->helper->tab('Attributes', [$repeater]);
    }
}

