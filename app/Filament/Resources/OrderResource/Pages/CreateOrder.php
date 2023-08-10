<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\OrderResourceForm;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function form(Form $form): Form
    {
        $resourceForm = new OrderResourceForm;

        return $form->schema($resourceForm->form())->columns(1);
    }
}
