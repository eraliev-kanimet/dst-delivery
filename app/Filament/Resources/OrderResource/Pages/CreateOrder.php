<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use App\Models\Order;
use App\Service\Admin\NotificationService;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('common.create_order');
    }

    public Model|Order|null $record;

    public array|Collection $stores = [];

    public function mount(): void
    {
        $this->stores = getQueryFilamentStore();

        parent::mount();
    }

    public function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm(stores: $this->stores);

        return parent::form($form->schema($resourceForm->form()));
    }

    public function afterCreate(): void
    {
        $service = NotificationService::new();

        $service->sendToOwner($this->record->store, __('notifications.orders.new2', [
            'order_id' => "#{$this->record->uuid}",
        ]));

        $service->send($this->record->store, __('notifications.orders.new3', [
            'order_id' => "#{$this->record->uuid}",
            'store' => $this->record->store->name,
        ]));
    }
}
