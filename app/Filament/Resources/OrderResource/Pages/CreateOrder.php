<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('common.create_order');
    }

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
}
