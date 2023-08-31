<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use App\Models\Order;
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

    public function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm(true);

        return parent::form(
            $form->schema($resourceForm->form())->disabled(in_array($this->record->status, [5, 6, 7]))
        )->columns(1);
    }

    public function afterSave(): void
    {
        if ($this->record->status == OrderStatus::inactive->value) {
            $this->record->update([
                'status' => OrderStatus::pending_payment->value
            ]);
        }

        $this->callCustomOrderUpdateEvent();
    }

    public function callCustomOrderUpdateEvent(bool $reload = true): void
    {
        $this->record->callCustomOrderUpdateEvent();

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

                    $this->callCustomOrderUpdateEvent();
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

                    $this->callCustomOrderUpdateEvent();
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
