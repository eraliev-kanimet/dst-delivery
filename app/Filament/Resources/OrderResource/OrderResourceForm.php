<?php

namespace App\Filament\Resources\OrderResource;

use App\Enums\DeliveryType;
use App\Enums\PaymentType;
use App\Helpers\FilamentHelper;
use App\Models\Customer;
use App\Models\Selection;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

class OrderResourceForm
{
    protected FilamentHelper $helper;

    public function __construct(
        protected bool             $edited = false,
        protected array|Collection $stores = [],
        protected array            $products = [],
        protected array            $deleted_products = [],
    )
    {
        $this->helper = new FilamentHelper;
    }

    public function form(): array
    {
        $tabs = [
            $this->helper->tab(__('common.basic'), $this->basic()),
        ];

        if ($this->edited) {
            $tabs[] = $this->helper->tab(__('common.products'), [
                $this->helper->repeater('orderItems', [
                    $this->helper->hidden('product'),
                    $this->helper->select('product.selection_id')
                        ->options(function (Get $get) {
                            if (isset($this->deleted_products[$get('product.selection_id')])) {
                                return $this->deleted_products;
                            }

                            return $this->products;
                        })
                        ->label(__('common.product'))
                        ->required()
                        ->reactive()
                        ->disabled(function (Get $get) {
                            return isset($this->deleted_products[$get('product.selection_id')]);
                        })
                        ->searchable(),
                    $this->helper->input('quantity')
                        ->label(__('common.quantity'))
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(function (Get $get) {
                            $selection_id = $get('product.selection_id');

                            if (is_null($selection_id)) {
                                return 1;
                            }

                            $selection = Selection::find($selection_id);

                            if ($selection) {
                                return $selection->quantity;
                            }

                            return 1;
                        })
                        ->disabled(function (Get $get) {
                            return isset($this->deleted_products[$get('product.selection_id')]);
                        })
                        ->required()
                ])->relationship()
                    ->label('')
                    ->addActionLabel(__('common.add_product'))
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
                    ->label(__('common.payment_type'))
                    ->options(PaymentType::getSelect())
                    ->required()
                    ->columnSpan($this->edited ? 1 : 2),
                $this->helper->input('total')
                    ->label(__('common.total'))
                    ->disabled()
                    ->visible($this->edited),
                $this->helper->select('delivery_type')
                    ->label(__('common.delivery_type'))
                    ->options(DeliveryType::getSelect()),
                $this->helper->dateTime('delivery_date')
                    ->label(__('common.delivery_date'))
                    ->minDate(now())
                    ->required(),
                $this->helper->input('delivery_address.first_name')
                    ->label(__('common.first_name'))
                    ->required(),
                $this->helper->input('delivery_address.last_name')
                    ->label(__('common.last_name'))
                    ->required(),
                $this->helper->input('delivery_address.email')
                    ->label(__('common.email'))
                    ->required()
                    ->email()
                    ->columnSpan(2),
                $this->helper->input('delivery_address.country')
                    ->label(__('common.country'))
                    ->required(),
                $this->helper->input('delivery_address.city')
                    ->label(__('common.city'))
                    ->required(),
                $this->helper->input('delivery_address.address')
                    ->label(__('common.address'))
                    ->required(),
                $this->helper->input('delivery_address.zip')
                    ->label(__('common.zip'))
                    ->required(),
            ]),
        ];

        if (!$this->edited) {
            array_unshift($array, $this->helper->grid([
                $this->helper->select('store_id')
                    ->options($this->stores)
                    ->label(__('common.store'))
                    ->reactive()
                    ->required(),
                $this->helper->select('customer_id')
                    ->label(__('common.customer'))
                    ->hidden(fn(Get $get) => is_null($get('store_id')))
                    ->options(function (Get $get) {
                        $store_id = $get('store_id');

                        if ($store_id) {
                            return Customer::whereStoreId($store_id)
                                ->get(['id', 'phone'])
                                ->pluck('phone', 'id');
                        }

                        return [];
                    })
                    ->required(),
            ], 1));
        }

        return $array;
    }
}
