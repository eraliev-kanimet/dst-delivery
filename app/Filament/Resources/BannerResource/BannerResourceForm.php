<?php

namespace App\Filament\Resources\BannerResource;

use App\Helpers\FilamentHelper;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Store;
use App\Service\ProductService;
use Closure;
use Illuminate\Support\Collection;

class BannerResourceForm
{
    public static function form(Collection|array $stores): array
    {
        $helper = new FilamentHelper;

        return [
            $helper->grid([
                $helper->input('name')
                    ->required()
                    ->columnSpan(2),
                $helper->input('sorted')
                    ->numeric()
                    ->minValue(0),
            ], 3),
            $helper->select('store_id')
                ->options($stores)
                ->required()
                ->label('Store')
                ->reactive(),
            $helper->select('type')
                ->options(Banner::$types)
                ->required()
                ->reactive(),
            $helper->input('type_url')
                ->required()
                ->visible(fn(Closure $get) => $get('type') == 'url')
                ->columnSpan(2)
                ->label('URL'),
            $helper->select('type_product')
                ->options(function (Closure $get) {
                    $store_id = $get('store_id');

                    if ($store_id) {
                        return ProductService::new()->getSelectProduct($store_id);
                    }

                    return [];
                })
                ->required()
                ->visible(fn(Closure $get) => $get('type') == 'product')
                ->columnSpan(2)
                ->label('Product'),
            $helper->select('type_category')
                ->options(function (Closure $get) {
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
                ->visible(fn(Closure $get) => $get('type') == 'category')
                ->columnSpan(2)
                ->label('Category'),
            $helper->dateTime('start_date')
                ->required()
                ->reactive()
                ->minDate(now()),
            $helper->dateTime('end_date')
                ->required()
                ->hidden(fn(Closure $get) => is_null($get('start_date')))
                ->minDate(fn ($get) => $get('start_date')),
            $helper->tabs(function (Closure $get) use ($helper) {
                $store_id = $get('store_id');

                if ($store_id) {
                    $tabs = [];

                    foreach (filterAvailableLocales(Store::find($store_id)->locales) as $locale => $name) {
                        $tabs[] = $helper->tab('Image ' . $name, [
                            $helper->image("image.$locale")
                                ->label('')
                                ->required()
                        ]);
                    }

                    return $tabs;
                }

                return [];
            })
                ->columnSpan(2)
                ->hidden(fn(Closure $get) => is_null($get('store_id'))),
            $helper->toggle('active')
                ->columnSpan(2)
                ->default(true),
        ];
    }
}
