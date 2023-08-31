<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    public function getTitle(): string
    {
        return __('common.create_store');
    }

    public array|Collection $users = [];

    public function mount(): void
    {
        $this->users = User::whereRoleId(2)->pluck('name', 'id');

        parent::mount();
    }

    public function form(Form $form): Form
    {
        $resourceForm = new StoreResource\StoreResourceForm(
            $this->users,
        );

        return parent::form($resourceForm->form($form));
    }
}
