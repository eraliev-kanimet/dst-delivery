<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Events\CustomerOrder;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use App\Models\Order;
use App\Service\ProductSelectionService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('common.edit_order');
    }

    public string|int|null|Model|Order $record;

    public array $products = [];
    public array $deleted_products = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->products = ProductSelectionService::new()->getSelectSelection(
            $this->record->store_id,
            $this->record->store->fallback_locale
        );

        $this->setDeletedProducts(config('app.locale'), $this->record->store->fallback_locale);

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function setDeletedProducts(string $locale, string $fallback_locale): void
    {
        $products = [];

        $service = ProductSelectionService::new();

        foreach ($this->record->orderItems as $orderItem) {
            $product = $orderItem->product;

            if (isset($this->products[$product['selection_id']])) {
                continue;
            }

            $name = '';

            if (isset($product["content_$locale"])) {
                $name = $product["content_$locale"]['name'];
            } else {
                if (isset($product["content_$fallback_locale"])) {
                    $name = $product["content_$fallback_locale"]['name'];
                }
            }

            $name = $name . ': ' . __('common.price') . ' ' . $orderItem->price;
            $name .= ', ' . $service->getPropertiesToString($product['attributes'], $locale, $fallback_locale, 5);

            $products[$product['selection_id']] = $name;
        }

        $this->deleted_products = $products;
    }

    public function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm(
            true,
            products: $this->products,
            deleted_products: $this->deleted_products
        );

        return parent::form(
            $form->schema($resourceForm->form())
                ->disabled(in_array($this->record->status, [5, 6, 7]))
        )->columns(1);
    }

    public function afterSave(): void
    {
        if ($this->record->status == OrderStatus::inactive->value) {
            $this->record->actionTotalCostRecalculation([
                'status' => OrderStatus::pending_payment->value
            ]);
        } else {
            $this->record->actionTotalCostRecalculation();
        }

        $this->callBroadcast();
    }

    public function callBroadcast(bool $reload = true): void
    {
        broadcast(new CustomerOrder($this->record->uuid, $this->record->customer_id));

        if ($reload) {
            redirect()->route('filament.admin.resources.orders.edit', ['record' => $this->record->uuid]);
        }
    }

    /**
     * @throws Exception
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('Cancel')
                ->color('warning')
                ->action(function () {
                    $this->record->actionCancel();

                    $this->callBroadcast();
                })
                ->requiresConfirmation()
                ->label(__('common.cancel'))
                ->modalHeading(__('common.order_admin.text1'))
                ->modalDescription(__('common.order_admin.text2'))
                ->modalSubmitActionLabel(__('common.order_admin.text3'))
                ->modalCancelActionLabel(__('common.cancel'))
                ->hidden(in_array($this->record->status, [0, 5, 6, 7])),
            Action::make('Confirmed')
                ->color('success')
                ->action(function () {
                    $this->record->actionConfirmed();

                    $this->callBroadcast();
                })
                ->requiresConfirmation()
                ->label(__('common.confirmed'))
                ->modalHeading(__('common.order_admin.text4'))
                ->modalDescription(__('common.order_admin.text5'))
                ->modalSubmitActionLabel(__('common.order_admin.text3'))
                ->modalCancelActionLabel(__('common.cancel'))
                ->visible($this->record->status == 1),
            DeleteAction::make(),
        ];
    }
}
