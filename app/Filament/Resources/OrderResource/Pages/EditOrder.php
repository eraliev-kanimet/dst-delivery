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
use Illuminate\Support\Collection;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @var Order
     */
    public $record;

    public array|Collection $products = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->products = ProductSelectionService::new()->getSelectSelection(
            $this->record->store_id,
            $this->record->store->fallback_locale
        );

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm(true, $this->products);

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
