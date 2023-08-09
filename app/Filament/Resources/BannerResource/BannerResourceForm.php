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
        $now = now();
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
                ->minDate($now),
            $helper->dateTime('end_date')
                ->required()
                ->minDate($now),
            $helper->image('image')
                ->columnSpan(2)
                ->required(),
            $helper->toggle('active')
                ->default(true),
        ];
    }
}
