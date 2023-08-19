<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\Category;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    public array|Collection $categories = [];
    public array|Collection $users = [];

    public function mount(): void
    {
        $this->categories = Category::all()->pluck('name.' . config('app.locale'), 'id');
        $this->users = User::where('role_id', 2)->pluck('name', 'id');

        parent::mount();
    }

    public function form(Form $form): Form
    {
        $resourceForm = new StoreResource\StoreResourceForm(
            $this->categories,
            $this->users,
        );

        return parent::form($resourceForm->form($form));
    }
}
