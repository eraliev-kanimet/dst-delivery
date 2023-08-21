<?php

namespace App\Filament\Resources\BannerResource;

use App\Helpers\FilamentHelper;
use App\Models\Category;
use App\Models\Store;
use App\Service\ProductService;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

class BannerResourceForm
{
    public static function form(Collection|array $stores): array
    {
        $helper = new FilamentHelper;

        $types = [
            'url' => __('common.url'),
            'product' => __('common.product'),
            'category' => __('common.category'),
        ];

        return [
            $helper->grid([
                $helper->input('name')
                    ->label(__('common.name'))
                    ->required()
                    ->columnSpan(2),
                $helper->input('sorted')
                    ->label(__('common.sorted'))
                    ->numeric()
                    ->minValue(0),
            ], 3),
            $helper->select('store_id')
                ->options($stores)
                ->required()
                ->label(__('common.store'))
                ->reactive(),
            $helper->select('type')
                ->options($types)
                ->label(__('common.type'))
                ->required()
                ->reactive(),
            $helper->input('type_url')
                ->required()
                ->visible(fn(Get $get) => $get('type') == 'url')
                ->columnSpan(2)
                ->label('URL'),
            $helper->select('type_product')
                ->options(function (Get $get) {
                    $store_id = $get('store_id');

                    if ($store_id) {
                        return ProductService::new()->getSelectProduct($store_id);
                    }

                    return [];
                })
                ->required()
                ->visible(fn(Get $get) => $get('type') == 'product')
                ->columnSpan(2)
                ->label(__('common.product')),
            $helper->select('type_category')
                ->options(function (Get $get) {
                    $store_id = $get('store_id');

                    if ($store_id) {
                        $store = Store::find($store_id);

                        return Category::whereIn('id', $store->categories)
                            ->get(['id', 'name'])
                            ->pluck('name.' . config('app.locale'), 'id');
                    }

                    return [];
                })
                ->required()
                ->visible(fn(Get $get) => $get('type') == 'category')
                ->columnSpan(2)
                ->label(__('common.category')),
            $helper->dateTime('start_date')
                ->label(__('common.start_date'))
                ->required()
                ->reactive()
                ->minDate(now()),
            $helper->dateTime('end_date')
                ->label(__('common.end_date'))
                ->required()
                ->hidden(fn(Get $get) => is_null($get('start_date')))
                ->minDate(fn ($get) => $get('start_date')),
            $helper->tabs(function (Get $get) use ($helper) {
                $store_id = $get('store_id');

                if ($store_id) {
                    $tabs = [];

                    foreach (filterAvailableLocales(Store::find($store_id)->locales) as $locale => $name) {
                        $tabs[] = $helper->tab(__('common.image')  .' ' . $name, [
                            $helper->image("image.$locale")
                                ->imageEditor()
                                ->label('')
                                ->required()
                        ]);
                    }

                    return $tabs;
                }

                return [];
            })
                ->columnSpan(2)
                ->hidden(fn(Get $get) => is_null($get('store_id'))),
            $helper->toggle('active')
                ->label(__('common.active'))
                ->columnSpan(2)
                ->default(true),
        ];
    }
}
