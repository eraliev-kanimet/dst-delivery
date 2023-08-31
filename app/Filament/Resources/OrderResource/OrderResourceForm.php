<?php

namespace App\Filament\Resources\OrderResource;

use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use App\Helpers\FilamentHelper;
use App\Models\Customer;
use Closure;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

class OrderResourceForm
{
    protected FilamentHelper $helper;

    public function __construct(
        protected bool             $edited = false,
        protected array|Collection $stores = [],
    )
    {
        $this->helper = new FilamentHelper;
    }

    public function form(): array
    {
        return $this->basic();
    }

    protected function basic(): array
    {
        $array = [
            $this->helper->grid([
                $this->helper->select('payment_type')
                    ->label(__('common.payment_method'))
                    ->options(PaymentMethod::getSelect())
                    ->required()
                    ->columnSpan($this->edited ? 1 : 2),
                $this->helper->input('total')
                    ->label(__('common.total'))
                    ->disabled()
                    ->visible($this->edited),
                $this->helper->select('delivery_type')
                    ->label(__('common.delivery_type'))
                    ->reactive()
                    ->options(DeliveryType::getSelect())
                    ->columnSpan(fn(Get $get) => $get('delivery_type') == DeliveryType::courier->value ? 1 : 2),
                $this->helper->dateTime('delivery_date')
                    ->label(__('common.delivery_date'))
                    ->minDate(now())
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.first_name')
                    ->label(__('common.first_name'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.last_name')
                    ->label(__('common.last_name'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.email')
                    ->label(__('common.email'))
                    ->required()
                    ->email()
                    ->columnSpan(2)
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.country')
                    ->label(__('common.country'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.city')
                    ->label(__('common.city'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.address')
                    ->label(__('common.address'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
                $this->helper->input('delivery_address.zip')
                    ->label(__('common.zip'))
                    ->required()
                    ->visible($this->ifTypeDeliveryIsCourier()),
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

    public function ifTypeDeliveryIsCourier(): Closure
    {
        return function (Get $get) {
            return $get('delivery_type') == DeliveryType::courier->value;
        };
    }
}
