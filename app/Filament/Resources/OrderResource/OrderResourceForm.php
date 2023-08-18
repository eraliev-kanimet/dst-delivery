<?php

namespace App\Filament\Resources\OrderResource;

use App\Enums\DeliveryType;
use App\Enums\PaymentType;
use App\Helpers\FilamentHelper;
use App\Models\Customer;
use App\Models\Selection;
use Closure;
use Illuminate\Support\Collection;

class OrderResourceForm
{
    protected FilamentHelper $helper;

    public function __construct(
        protected bool $edited = false,
        protected array|Collection $stores = [],
        protected array $products = [],
        protected array $items = [],
    )
    {
        $this->helper = new FilamentHelper;
    }

    public function form(): array
    {
        $tabs = [
            $this->helper->tab('Basic', $this->basic()),
        ];

        if ($this->edited) {
            $tabs[] = $this->helper->tab('Products', [
                $this->helper->repeater('orderItems', [
                    $this->helper->hidden('product'),
                    $this->helper->select('product.selection_id')
                        ->options($this->products)
                        ->label('Product')
                        ->required()
                        ->reactive()
                        ->disabled(function (Closure $get) {
                            return isset($this->items[$get('product.selection_id')]);
                        })
                        ->searchable(),
                    $this->helper->input('quantity')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(function (Closure $get) {
                            $selection_id = $get('product.selection_id');

                            if (is_null($selection_id)) {
                                return 0;
                            }

                            $selection = Selection::find($selection_id);

                            if ($selection) {
                                return $selection->quantity;
                            }

                            return $get('quantity');
                        })
                        ->disabled(function (Closure $get) {
                            return isset($this->items[$get('product.selection_id')]);
                        })
                        ->required()
                ])->relationship()
                    ->label('')
                    ->createItemButtonLabel('Add product')
                    ->required()
            ]);
        } else {
            return $this->basic();
        }

        return [$this->helper->tabs($tabs)];
    }

    protected function basic(): array
    {
        $array = [
            $this->helper->grid([
                $this->helper->select('payment_type')
                    ->options(PaymentType::getSelect())
                    ->required()
                    ->columnSpan($this->edited ? 1 : 2),
                $this->helper->input('total')
                    ->disabled()
                    ->visible($this->edited),
                $this->helper->select('delivery_type')
                    ->options(DeliveryType::getSelect()),
                $this->helper->dateTime('delivery_date')
                    ->minDate(now())
                    ->required(),
                $this->helper->input('delivery_address.first_name')
                    ->required(),
                $this->helper->input('delivery_address.last_name')
                    ->required(),
                $this->helper->input('delivery_address.email')
                    ->required()
                    ->email()
                    ->columnSpan(2),
                $this->helper->input('delivery_address.country')
                    ->required(),
                $this->helper->input('delivery_address.city')
                    ->required(),
                $this->helper->input('delivery_address.address')
                    ->required(),
                $this->helper->input('delivery_address.zip')
                    ->label('Zip code')
                    ->required(),
            ]),
        ];

        if (!$this->edited) {
            array_unshift($array, $this->helper->grid([
                $this->helper->select('store_id')
                    ->options($this->stores)
                    ->label('Store')
                    ->reactive()
                    ->required(),
                $this->helper->select('customer_id')
                    ->hidden(fn(Closure $get) => is_null($get('store_id')))
                    ->options(function (Closure $get) {
                        $store_id = $get('store_id');

                        if ($store_id) {
                            return Customer::whereStoreId($store_id)
                                ->get(['id', 'phone'])
                                ->pluck('phone', 'id');
                        }

                        return [];
                    })
                    ->label('Customer')
                    ->required(),
            ], 1));
        }

        return $array;
    }
}
