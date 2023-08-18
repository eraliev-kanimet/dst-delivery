<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use App\Models\Order;
use App\Service\ProductSelectionService;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @var Order
     */
    public $record;

    public array $products = [];
    public array $items = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $products = ProductSelectionService::new()->getSelectSelection(
            $this->record->store_id,
            $this->record->store->fallback_locale
        );

        $items = [];

        foreach ($this->record->orderItems as $item) {
            $selection_id = $item->product['selection_id'];

            if (!isset($products[$selection_id])) {
                $items[$selection_id] = true;
            }
        }

        $this->items = $items;

        $this->products = $this->getSelectionFromOrderItems($products, config('app.locale'), $this->record->store->fallback_locale);

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function getSelectionFromOrderItems(array $products, string $locale, string $fallback_locale): array
    {
        $service = ProductSelectionService::new();

        foreach ($this->record->orderItems as $orderItem) {
            $product = $orderItem->product;

            if (isset($products[$product['selection_id']])) {
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
            $name .= ', ' . __('common.quantity') . ' ' . $orderItem->quantity;
            $name .= ', ' . $service->getPropertiesToString($product['attributes'], $locale, $fallback_locale, 5);

            $products[$product['selection_id']] = $name;
        }

        return $products;
    }

    protected function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm(true, products: $this->products, items: $this->items);

        return $form->schema($resourceForm->form())
            ->columns(1)
            ->disabled(in_array($this->record->status, [5, 6, 7]));
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
    }

    public function customActionCancel(): void
    {
        $this->record->actionCancel();
    }

    public function customActionConfirmed(): void
    {
        $this->record->actionConfirmed();
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\Action::make('Cancel')
                ->color('warning')
                ->action('customActionCancel')
                ->hidden(in_array($this->record->status, [0, 5, 6, 7])),
            Actions\Action::make('Confirmed')
                ->color('success')
                ->action('customActionConfirmed')
                ->visible($this->record->status == 1),
            Actions\DeleteAction::make(),
        ];
    }
}
